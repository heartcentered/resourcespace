<?php

/**
 * Performs the login using the global $username, and $password. Since the "externalauth" hook
 * is allowed to change the credentials later on, the $password_hash needs to be global as well.
 *
 * @return array Containing the login details ('valid' determines whether or not the login succeeded).
 */

if(!function_exists('hash'))
    {
    include dirname(__FILE__) . "/hash_functions.php";
    }

function perform_login()
    {
    global $scramble_key, $lang, $max_login_attempts_wait_minutes, $max_login_attempts_per_ip, $max_login_attempts_per_username, $global_cookies, $username, $password, $password_hash, $session_hash, $usergroup;

    if((strlen($password) == 32 || strlen($password) == 64) && getval("userkey", "") != md5($username . $scramble_key))
        {
        // Prevent MD5s being entered directly while still supporting direct entry of plain text passwords (for systems that were set up prior to MD5 password encryption was added). If a special key is sent, which is the md5 hash of the username and the secret scramble key, then allow a login using the MD5 password hash as the password. This is for the 'log in as this user' feature.
        exit("Invalid password.");
        }

    // Provided password is not a hash, so generate a hash.
    if(strlen($password) != 64)
        {
        //$password_hash=md5("RS" . $username . $password);
        $password_hash = hash('sha256', md5("RS" . $username . $password));
        }
    else
        {
        $password_hash = $password;
        }

    // Automatic migration of md5 hashed or plain text passwords to SHA256 hashed passwords.  This is necessary
    // because older systems being upgraded may still have passwords stored using md5 hashes or even possibly stored
    // in plain text.
    // Updated March 2015: select password_reset_hash to force dbstruct that will update password column varchar(100) if not already.
    $accountstoupdate = sql_query("SELECT username, password, password_reset_hash FROM user WHERE length(password)<>64");
    foreach($accountstoupdate as $account)
        {
        $oldpassword = $account["password"];
        if(strlen($oldpassword) != 32)
            {
            // Needed if we have a really old password, or if password has been manually reset in db for some reason.
            $oldpassword = md5("RS" . $account["username"] . $oldpassword);
            }
        $new_password_hash = hash('sha256', $oldpassword);
        sql_query("UPDATE user SET password='" . $new_password_hash . "' WHERE username='" . escape_check($account["username"]) . "'");
        }
    $ip = get_ip();

    // This may change the $username, $password, and $password_hash.
    $externalresult = hook("externalauth", "", array($username, $password)); # Attempt external auth if configured.

    // Generate a new session hash.
    $session_hash = generate_session_hash($password_hash);

    // Check the provided credentials.
    $valid = sql_query("SELECT ref,usergroup,account_expires,approved FROM user WHERE username='" . escape_check($username) . "' and password='" . escape_check($password_hash) . "'");

    // Prepare result array.
    $result = array();
    $result['valid'] = false;

    if(count($valid) >= 1)
        {
        // Account expiry.
        $userref = $valid[0]["ref"];
        $usergroup = $valid[0]["usergroup"];
        $expires = $valid[0]["account_expires"];
        $approved = $valid[0]["approved"];

        if($approved == 2)
            {
            $result['error']=$lang["accountdisabled"];
            return $result;
            }

        if($expires != "" && $expires != "0000-00-00 00:00:00" && strtotime($expires) <= time())
            {
            $result['error'] = $lang["accountexpired"];
            return $result;
            }

        $result['valid'] = true;
        $result['session_hash'] = $session_hash;
        $result['password_hash'] = $password_hash;
        $result['ref'] = $userref;

        // Update the user record.
        $session_hash_sql = "session='" . escape_check($session_hash) . "',";

        $language = getvalescaped("language", "");

        sql_query("
            UPDATE user
               SET {$session_hash_sql}
                   last_active = NOW(),
                   login_tries = 0,
                   lang = '{$language}'
             WHERE ref = '{$userref}'
        ");

        // Update user local time zone (if provided).
        $get_user_local_timezone = getval('user_local_timezone', null);
        set_config_option($userref, 'user_local_timezone', $get_user_local_timezone);

        // Log this.
        daily_stat("User session", $userref);
        log_activity(null,LOG_CODE_LOGGED_IN, $ip, "user", "ref", ($userref != "" ? $userref : "null"), null, '', ($userref != "" ? $userref : "null"));

        // Blank the IP address lockout counter for this IP.
        sql_query("DELETE FROM ip_lockout WHERE ip='" . escape_check($ip) . "'");

        return $result;
        }

    // Invalid login.
    if(isset($externalresult["error"]))
        {
        // We may have been given a better error to display.
        $result['error'] = $externalresult["error"];
        }
    else
        {
        $result['error'] = $lang["loginincorrect"];
        }

    hook("loginincorrect");

    // Add or increment a lockout value for this IP.
    $lockouts = sql_value("SELECT count(*) value FROM ip_lockout WHERE ip='" . escape_check($ip) . "' AND tries<'" . $max_login_attempts_per_ip . "'", "");

    if($lockouts > 0)
        {
        // Existing row with room to move.
        $tries = sql_value("SELECT tries value FROM ip_lockout WHERE ip='" . escape_check($ip) . "'", 0);
        ++$tries;

        // Show locked out message.
        if($tries == $max_login_attempts_per_ip)
            {
            $result['error'] = str_replace("?", $max_login_attempts_wait_minutes, $lang["max_login_attempts_exceeded"]);
            }

        // Increment.
        sql_query("UPDATE ip_lockout SET last_try=now(),tries=tries+1 WHERE ip='" . escape_check($ip) . "'");
        }
    else // New row.
        {
        sql_query("DELETE FROM ip_lockout WHERE ip='" . escape_check($ip) . "'");
        sql_query("INSERT INTO ip_lockout (ip,tries,last_try) values ('" . escape_check($ip) . "',1,now())");
        }

    // Increment a lockout value for any matching username.
    $ulocks = sql_query("SELECT ref,login_tries,login_last_try FROM user WHERE username='" . escape_check($username) . "'");
    if(count($ulocks) > 0)
        {
        $tries = $ulocks[0]["login_tries"];
        if($tries == "")
            {
            $tries = 1;
            }
        else
            {
            ++$tries;
            }

        if($tries > $max_login_attempts_per_username)
            {
            $tries = 1;
            }

        // Show locked out message.
        if($tries == $max_login_attempts_per_username)
            {
            $result['error'] = str_replace("?", $max_login_attempts_wait_minutes, $lang["max_login_attempts_exceeded"]);
            }
        sql_query("UPDATE user SET login_tries='$tries',login_last_try=now() WHERE username='$username'");
        }

    return $result;
    }


// Generates a unique session hash.
function generate_session_hash($password_hash)
    {
    global $randomised_session_hash, $scramble_key;

    // Completely randomised session hashes. May be more secure, but allows only one user at a time.
    if($randomised_session_hash)
        {
        // Completely randomised session hashes. May be more secure, but allows only one user at a time.
        while(true)
            {
            $session = md5(rand() . microtime());

            // Return a unique hash only.
            if(sql_value("SELECT count(*) value FROM user WHERE session='" . escape_check($session) . "'", 0) == 0)
                {
                return $session;
                }
            }
        }
    else // Session hash is based on the password hash and the date, so there is one new session hash each day. Allows two users to use the same login.
        {
        $suffix = "";
        while(true)
            {
            $session = md5($scramble_key . $password_hash . date("Ymd") . $suffix);

            // Return a unique hash only.
            if(sql_value("SELECT count(*) value FROM user WHERE session='" . escape_check($session) . "' AND password<>'" . escape_check($password_hash) . "'", 0) == 0)
                {
                return $session;
                }

            // Extremely unlikely case that this was not a unique session (hash collision), alter the string slightly and try again.
            $suffix .= ".";
            }
        }
    }


function set_login_cookies($user, $session_hash, $language = "", $user_preferences)
    {
    global $baseurl, $baseurl_short, $allow_keep_logged_in, $default_res_types, $language;
    $expires = 0;

    if($allow_keep_logged_in && getval("remember", "") != "")
        {
        $expires = 100; # remember login for 100 days.
        }

    // Store language cookie.
    if($language != "")
        {
        rs_setcookie("language", $language, 1000); # Only used if not global cookies.
        rs_setcookie("language", $language, 1000, $baseurl_short . "pages/");
        }

    // Set the session cookie. Do this for all paths that nay set the cookie as otherwise we can end up with a valid cookie at e.g. pages/team or pages/ajax.
    rs_setcookie("user", "", 0, $baseurl_short);
    rs_setcookie("user", "", 0, $baseurl_short . "pages");
    rs_setcookie("user", "", 0, $baseurl_short . "pages/team");
    rs_setcookie("user", "", 0, $baseurl_short . "pages/admin");
    rs_setcookie("user", "", 0, $baseurl_short . "pages/ajax");

    // Set user cookie, setting secure only flag if a HTTPS site, and also setting the HTTPOnly flag so this cookie cannot be probed by scripts (mitigating potential XSS vulnerability).
    rs_setcookie("user", $session_hash, $expires, $baseurl_short, "", substr($baseurl, 0, 5) == "https", true);

    // Set default resource types
    rs_setcookie('restypes', $default_res_types);

    $userpreferences = ($user_preferences) ? sql_query("SELECT user, `value` AS colour_theme FROM user_preferences WHERE user = '" . $user . "' AND parameter = 'colour_theme';") : FALSE;
    $userpreferences = ($userpreferences && isset($userpreferences[0])) ? $userpreferences[0]: FALSE;
    if($userpreferences && isset($userpreferences["colour_theme"]) && $userpreferences["colour_theme"] != "" && (!isset($_COOKIE["colour_theme"]) || $userpreferences["colour_theme"] != $_COOKIE["colour_theme"]))
        {
        rs_setcookie("colour_theme", $userpreferences["colour_theme"], 100, "/", "", substr($baseurl, 0, 5) == "https", true);
        }
    }
