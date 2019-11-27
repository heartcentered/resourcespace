<?php
/**
* @package ResourceSpace\Tools
* 
* A script to help administrators merge two ResourceSpace systems.
*/
if('cli' != PHP_SAPI)
    {
    http_response_code(401);
    exit('Access denied - Command line only!');
    }

$help_text = "NAME
    merge_rs_systems - a script to help administrators merge two ResourceSpace systems.

SYNOPSIS
    On the system that is going to merge with the master system:
        php path/tools/merge_rs_systems.php [OPTION...] DEST

    On the master system, merging in data from the slave system:
        php path/tools/merge_rs_systems.php [OPTION...] SRC

DESCRIPTION
    A script to help administrators merge two ResourceSpace systems.

    A specification file is required for the migration to be possible. The spec file will contain:
    - A mapping between the migrating system and the master systems' metadata fields. How should we treat the case where 
      one metadata field is of a different type than the other one (this is especially important for category trees).
    - User groups mapping and how we should deal with new user groups that do not exist on the master system

OPTIONS SUMMARY
    Here is a short summary of the options available in merge_rs_systems. Please refer to the detailed description below 
    for a complete description.

    -h, --help          display this help and exit
    -f                  @todo: optional field ID where we can store the resource ID from the old system at import
    -u, --user          run script as a ResourceSpace user. Use the ID of the user
    --dry-run           perform a trial run with no changes made
    --spec-file=FILE    read specification from FILE
    --export            export information from ResourceSpace based on the specification file (Requires spec-file option)
    --import            import information to ResourceSpace based on the specification file (Requires spec-file option)
    " . PHP_EOL;


$cli_short_options = "hu:";
$cli_long_options  = array(
    "help",
    "dry-run",
    "user:",
    "spec-file:",
    "export",
    "import",
    "generate-spec-file", # @todo: implement this - might be helpful!
);
$options = getopt($cli_short_options, $cli_long_options);

$help = false;
$dry_run = false;
$export = false;
$import = false;

foreach($options as $option_name => $option_value)
    {
    if(in_array($option_name, array("h", "help")))
        {
        echo $help_text;
        exit(0);
        }

    if(in_array(
        $option_name,
        array(
            "dry-run",
            "export",
            "import",)))
        {
        $option_name = str_replace("-", "_", $option_name);
        $$option_name = true;
        }

    if($option_name == "spec-file" && !is_array($option_value))
        {
        if(!file_exists($option_value))
            {
            fwrite(STDERR, "ERROR: Unable to open input file '{$option_value}'!" . PHP_EOL);
            exit(1);
            }

        $spec_file_path = $option_value;
        }

    if(in_array($option_name, array("u", "user")) && !is_array($option_value))
        {
        if(!is_numeric($option_value) || (int) $option_value <= 0)
            {
            fwrite(STDERR, "ERROR: Invalid 'user' value provided: '{$user}' of type " . gettype($user) . PHP_EOL);
            exit(1);
            }

        $user = $option_value;
        }
    }

$webroot = dirname(dirname(__DIR__));
include_once "{$webroot}/include/db.php";
include_once "{$webroot}/include/general.php";
include_once "{$webroot}/include/log_functions.php";
// include_once "{$webroot}/include/resource_functions.php";
include_once "{$webroot}/include/collections_functions.php";

$get_file_handler = function($file_path, $mode)
    {
    $file_handler = fopen($file_path, $mode);
    if($file_handler === false)
        {
        logScript("ERROR: Unable to open output file '{$file_path}'!");
        exit(1);
        }

    return $file_handler;
    };

$json_decode_file_data = function($fh)
    {
    $input_lines = array();
    while(($line = fgets($fh)) !== false)
        {
        if(trim($line) != "" &&  mb_check_encoding($line, "UTF-8"))
            {
            $input_lines[] = trim($line);
            }
        }
    fclose($fh);

    if(empty($input_lines))
        {
        logScript("WARNING: No data to import! To be safe, double check on the source side whether this is true.");
        return array();
        }

    $json_decoded_data = array();
    foreach($input_lines as $input_line)
        {
        $value = json_decode($input_line, true);
        if(json_last_error() !== JSON_ERROR_NONE)
            {
            logScript("ERROR: Unable to decode JSON because of the following error: " . json_last_error_msg());
            exit(100);
            }
        $json_decoded_data[] = $value;
        }

    return $json_decoded_data;
    };

if($dry_run)
    {
    logScript("#################################################################");
    logScript("##### WARNING - Script running with DRY-RUN option enabled! #####");
    logScript("#################################################################");
    }

if(isset($user))
    {
    $user_data = validate_user("AND u.ref = '" . escape_check($user) . "'", true);
    if(!is_array($user_data) || count($user_data) == 0)
        {
        logScript("ERROR: Unable to validate user ID #{$user}!");
        exit(1);
        }
    setup_user($user_data[0]);
    logScript("Running script as user '{$username}' (ID #{$userref})");
    }

/*
For the following usage:
 - php path/tools/merge_rs_systems.php [OPTION...] --export DEST
 - php path/tools/merge_rs_systems.php [OPTION...] --import SRC
Ensure DEST/SRC folder has been provided when exporting or importing data
*/
if($export || $import)
    {
    $folder_path = end($argv);
    if(!file_exists($folder_path) || !is_dir($folder_path))
        {
        $folder_type = $export ? "DEST" : ($import ? "SRC" : "");
        logScript("ERROR: {$folder_type} MUST be folder. Value provided: '{$folder_path}'");
        exit(1);
        }
    }

if($export && isset($folder_path))
    {
    // @todo: consider having a list of tables required to be fully exported and run the same "block" of code for each one of them
    // We have a bit of repetition going on and a pattern emerged now

    # USER GROUPS
    #############
    logScript("");
    logScript("Exporting user groups...");

    $usergroups_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "usergroups_export.json", "w+b");
    foreach(get_usergroups() as $usergroup)
        {
        logScript("User group '{$usergroup["name"]}' (ID #{$usergroup["ref"]})");

        if($dry_run)
            {
            continue;
            }

        fwrite($usergroups_export_fh, json_encode($usergroup, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($usergroups_export_fh);


    # USERS & USER PREFERENCES
    ##########################
    logScript("");
    logScript("Exporting users and their preferences...");

    $users_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "users_export.json", "w+b");
    foreach(get_users(0, "", "u.ref ASC", false, -1, 1, false, "") as $user)
        {
        logScript("User: {$user["fullname"]} (ID #{$user["ref"]} | Username: {$user["username"]} | E-mail: {$user["email"]})");

        // Check user preferences and save for processing it later
        $user_preferences = array();
        if(get_config_options($user["ref"], $user_preferences))
            {
            logScript("Found user preferences");
            $user["user_preferences"] = $user_preferences;
            }

        if($dry_run)
            {
            continue;
            }

        fwrite($users_export_fh, json_encode($user, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($users_export_fh);


    # ARCHIVE STATES
    ################
    logScript("");
    logScript("Exporting archive states...");

    $archive_states_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "archive_states_export.json", "w+b");
    foreach(get_workflow_states() as $archive_state)
        {
        if(!isset($lang["status{$archive_state}"]))
            {
            logScript("Warning: language not set for archive state #{$archive_state}");
            continue;
            }
        $archive_state_text = $lang["status{$archive_state}"];

        logScript("Archive state '{$archive_state_text}' (ID #{$archive_state})");

        if($dry_run)
            {
            continue;
            }

        $exported_archive_state = array(
            "ref"  => $archive_state,
            "lang" => $archive_state_text);

        fwrite($archive_states_export_fh, json_encode($exported_archive_state, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($archive_states_export_fh);


    # RESOURCE TYPES
    ################
    logScript("");
    logScript("Exporting resource_types...");

    $resource_types_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_types_export.json", "w+b");
    foreach(get_resource_types("", false) as $resource_type)
        {
        logScript("Resource type '{$resource_type["name"]}' (ID #{$resource_type["ref"]})");

        if($dry_run)
            {
            continue;
            }

        fwrite($resource_types_export_fh, json_encode($resource_type, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_types_export_fh);


    # RESOURCE TYPE FIELDS
    ######################
    logScript("");
    logScript("Exporting resource_type fields...");

    $resource_type_fields_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_type_fields_export.json", "w+b");
    foreach(get_resource_type_fields("", "ref", "ASC", "", array()) as $resource_type_field)
        {
        logScript("Resource type field '{$resource_type_field["title"]}' (ID #{$resource_type_field["ref"]} | shortname: '{$resource_type_field["name"]}')");

        if($dry_run)
            {
            continue;
            }

        fwrite($resource_type_fields_export_fh, json_encode($resource_type_field, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_type_fields_export_fh);


    # NODES
    #######
    logScript("");
    logScript("Exporting nodes...");
    $nodes_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "nodes_export.json", "w+b");
    $nodes = sql_query("SELECT * FROM node");
    if(empty($nodes))
        {
        logScript("WARNING: unable to retrieve any nodes from the system!");
        }
    if(!$dry_run)
        {
        fwrite($nodes_export_fh, json_encode($nodes, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($nodes_export_fh);


    # RESOURCES
    ###########
    logScript("");
    logScript("Exporting resources...");
    $resources_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resources_export.json", "w+b");
    $resources = sql_query("SELECT * FROM resource WHERE ref > 0");
    if(empty($resources))
        {
        logScript("WARNING: unable to retrieve any resources from the system!");
        }
    if(!$dry_run)
        {
        fwrite($resources_export_fh, json_encode($resources, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resources_export_fh);


    # RESOURCE DATA
    ###############
    logScript("");
    logScript("Exporting resource data...");
    $resource_data_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_data_export.json", "w+b");
    $resource_data = sql_query("SELECT * FROM resource_data WHERE resource > 0");
    if(empty($resource_data))
        {
        logScript("WARNING: unable to retrieve any resource data from the system!");
        }
    if(!$dry_run)
        {
        fwrite($resource_data_export_fh, json_encode($resource_data, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_data_export_fh);


    # RESOURCE NODES
    ################
    logScript("");
    logScript("Exporting resource nodes...");
    $resource_nodes_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_nodes_export.json", "w+b");
    $resource_nodes = sql_query("SELECT * FROM resource_node WHERE resource > 0");
    if(empty($resource_nodes))
        {
        logScript("WARNING: unable to retrieve any resource nodes from the system!");
        }
    if(!$dry_run)
        {
        fwrite($resource_nodes_export_fh, json_encode($resource_nodes, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_nodes_export_fh);


    # RESOURCE DIMENSIONS
    #####################
    logScript("");
    logScript("Exporting resource dimensions...");
    $resource_dimensions_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_dimensions_export.json", "w+b");
    $resource_dimensions = sql_query("SELECT * FROM resource_dimensions");
    if(empty($resource_dimensions))
        {
        logScript("WARNING: unable to retrieve any resource dimensions from the system!");
        }
    if(!$dry_run)
        {
        fwrite($resource_dimensions_export_fh, json_encode($resource_dimensions, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_dimensions_export_fh);


    # RESOURCE RELATED
    ##################
    logScript("");
    logScript("Exporting resource related...");
    $resource_related_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_related_export.json", "w+b");
    $resource_related = sql_query("SELECT * FROM resource_related");
    if(empty($resource_related))
        {
        logScript("WARNING: unable to retrieve any related resources from the system!");
        }
    if(!$dry_run)
        {
        fwrite($resource_related_export_fh, json_encode($resource_related, JSON_NUMERIC_CHECK) . PHP_EOL);
        }
    fclose($resource_related_export_fh);
    }


// @todo: consider improving memory usage by cleaning up after each import
if($import)
    {
    if(!isset($spec_file_path) || trim($spec_file_path) == "")
        {
        logScript("ERROR: Specification file not provided or empty!");
        exit(1);
        }
    include_once $spec_file_path;

    /*
    spec_override.php is used as the name suggests to override the specification file that was provided as original input
    by keeping track of new mappings created and saving them in the override.
    */
    $spec_override_fp = $folder_path . DIRECTORY_SEPARATOR . "spec_override.php";
    $spec_override_fh = $get_file_handler($spec_override_fp, "a+b");
    $php_tag_found = false;
    while(($line = fgets($spec_override_fh)) !== false)
        {
        if(trim($line) != "" &&  mb_check_encoding($line, "UTF-8") && mb_strpos($line, "<?php") !== false)
            {
            $php_tag_found = true;
            }
        }
    if(!$php_tag_found || $dry_run)
        {
        ftruncate($spec_override_fh, 0);
        fwrite($spec_override_fh, "<?php" . PHP_EOL);
        }
    include_once $spec_override_fp;

    if(db_begin_transaction())
        {
        logScript("MySQL: Begin transaction!");
        }
    $rollback_transaction = false;

    # USER GROUPS
    #############
    logScript("");
    logScript("Importing user groups...");
    if(!isset($usergroups_spec) || empty($usergroups_spec))
        {
        logScript("ERROR: Spec missing 'usergroups_spec'");
        exit(1);
        }
    $src_usergroups = $json_decode_file_data($get_file_handler($folder_path . DIRECTORY_SEPARATOR . "usergroups_export.json", "r+b"));
    $dest_usergroups = get_usergroups(false, "", true);
    $usergroups_not_created = (isset($usergroups_not_created) ? $usergroups_not_created : array());
    foreach($src_usergroups as $src_ug)
        {
        logScript("Processing {$src_ug["name"]} (ID #{$src_ug["ref"]})...");
        if(!array_key_exists($src_ug["ref"], $usergroups_spec))
            {
            logScript("WARNING: Specification for usergroups does not contain a mapping for this group! Skipping");
            $usergroups_not_created[] = $src_ug["ref"];
            fwrite($spec_override_fh, "\$usergroups_not_created[] = {$src_ug["ref"]};" . PHP_EOL);
            continue;
            }

        $spec_cfg_value = $usergroups_spec[$src_ug["ref"]];
        if(is_numeric($spec_cfg_value) && $spec_cfg_value > 0 && array_key_exists($spec_cfg_value, $dest_usergroups))
            {
            logScript("Found direct 1:1 mapping to '{$dest_usergroups[$spec_cfg_value]}' (ID #{$spec_cfg_value})... Skipping");
            continue;
            }
        else if(is_array($spec_cfg_value))
            {
            if(!isset($spec_cfg_value["create"]))
                {
                logScript("ERROR: usergroup specification config value is invalid. Required keys: create - true/false");
                continue;
                }

            if((bool) $spec_cfg_value["create"] == false)
                {
                logScript("Skipping usergroup as per the specification record");
                $usergroups_not_created[] = $src_ug["ref"];
                fwrite($spec_override_fh, "\$usergroups_not_created[] = {$src_ug["ref"]};" . PHP_EOL);
                continue;
                }

            // create user group and save mapping in cache
            sql_query("INSERT INTO usergroup(name, request_mode) VALUES ('" . escape_check($src_ug["name"]) . "', '1')");
            $new_ug_ref = sql_insert_id();
            log_activity(null, LOG_CODE_CREATED, null, 'usergroup', null, $new_ug_ref);
            log_activity(null, LOG_CODE_CREATED, $src_ug["name"], 'usergroup', 'name', $new_ug_ref, null, '');
            log_activity(null, LOG_CODE_CREATED, '1', 'usergroup', 'request_mode', $new_ug_ref, null, '');

            logScript("Created new user group '{$src_ug["name"]}' (ID #{$new_ug_ref})");
            $usergroups_spec[$src_ug["ref"]] = $new_ug_ref;
            fwrite($spec_override_fh, "\$usergroups_spec[{$src_ug["ref"]}] = {$new_ug_ref};" . PHP_EOL);
            }
        else
            {
            logScript("ERROR: Invalid usergroup specification record for key #{$src_ug["ref"]}");
            }

        }


    # USERS & USER PREFERENCES
    ##########################
    logScript("");
    logScript("Importing users and their preferences...");
    $src_users = $json_decode_file_data($get_file_handler($folder_path . DIRECTORY_SEPARATOR . "users_export.json", "r+b"));
    fwrite($spec_override_fh, PHP_EOL . PHP_EOL);
    $usernames_mapping = (isset($usernames_mapping) ? $usernames_mapping : array());
    $users_not_created = (isset($users_not_created) ? $users_not_created : array());
    $process_user_preferences = function($user_ref, $user_data)
        {
        if(isset($user_data["user_preferences"]) && is_array($user_data["user_preferences"]) && !empty($user_data["user_preferences"]))
            {
            logScript("Processing user preferences (if no warning are showing, this is ok)");
            foreach($user_data["user_preferences"] as $user_p)
                {
                if(!set_config_option($user_ref, $user_p["parameter"], $user_p["value"]))
                    {
                    logScript("WARNING: uanble to save user preference: {$user_p["parameter"]} = '{$user_p["value"]}'");
                    }
                }
            }
        };
    foreach($src_users as $user)
        {
        if(array_key_exists($user["ref"], $usernames_mapping))
            {
            continue;
            }

        $found_uref = get_user_by_username($user["username"]);
        if($found_uref !== false)
            {
            $found_udata = get_user($found_uref);
            logScript("Username '{$user["username"]}' found in current system as '{$found_udata["username"]}', full name '{$found_udata["fullname"]}'");

            $usernames_mapping[$user["ref"]] = $found_uref;
            fwrite($spec_override_fh, "\$usernames_mapping[{$user["ref"]}] = {$found_uref};" . PHP_EOL);

            $process_user_preferences($found_uref, $user);

            continue;
            }

        if(in_array($user["usergroup"], $usergroups_not_created))
            {
            logScript("WARNING: User '{$user["username"]}' belongs to a user group that was not created as per the specification file. Skipping");
            $users_not_created[] = $user["ref"];
            fwrite($spec_override_fh, "\$users_not_created[] = {$user["ref"]};" . PHP_EOL);
            continue;
            }

        $new_uref = new_user($user["username"], $usergroups_spec[$user["usergroup"]]);
        logScript("Created new user '{$user["username"]}' (ID #{$new_uref} | User group ID: {$usergroups_spec[$user["usergroup"]]})");
        $usernames_mapping[$user["ref"]] = $new_uref;
        fwrite($spec_override_fh, "\$usernames_mapping[{$user["ref"]}] = {$new_uref};" . PHP_EOL);

        $_GET["username"] = $user["username"];
        $_GET["password"] = $user["password"];
        $_GET["fullname"] = $user["fullname"];
        $_GET["email"] = $user["email"];
        $_GET["expires"] = $user["account_expires"];
        $_GET["usergroup"] = $usergroups_spec[$user["usergroup"]];
        $_GET["ip_restrict"] = $user["ip_restrict"];
        $_GET["search_filter_override"] = $user["search_filter_override"];
        $_GET["search_filter_o_id"] = $user["search_filter_o_id"];
        $_GET["comments"] = $user["comments"];
        $_GET["suggest"] = "";
        $_GET["emailresetlink"] = $user["password_reset_hash"];
        $_GET["approved"] = $user["approved"];
        $save_user_status = save_user($new_uref);
        if($save_user_status === false)
            {
            logScript("WARNING: failed to save user '{$user["username"]}' - Username or e-mail address already exist?");
            }
        else if(is_string($save_user_status))
            {
            logScript("WARNING: failed to save user '{$user["username"]}'. Reason: '{$save_user_status}'");
            }
        else
            {
            logScript("Saved user details");
            }

        $process_user_preferences($new_uref, $user);
        }


    # ARCHIVE STATES
    ################
    logScript("");
    logScript("Importing archive states...");
    if(!isset($archive_states_spec) || empty($archive_states_spec))
        {
        logScript("ERROR: Spec missing 'archive_states_spec'");
        exit(1);
        }
    $src_archive_states = $json_decode_file_data($get_file_handler($folder_path . DIRECTORY_SEPARATOR . "archive_states_export.json", "r+b"));
    $dest_archive_states = get_workflow_states();
    foreach($src_archive_states as $archive_state)
        {
        logScript("Processing '{$archive_state["lang"]}' (ID #{$archive_state["ref"]})");
        if(
            array_key_exists($archive_state["ref"], $archive_states_spec)
            && in_array($archive_states_spec[$archive_state["ref"]], $dest_archive_states)
            && !is_null($archive_states_spec[$archive_state["ref"]]))
            {
            $lang_text = $lang["status{$archive_states_spec[$archive_state["ref"]]}"];
            logScript("Found direct 1:1 mapping to #{$archive_states_spec[$archive_state["ref"]]} - {$lang_text}");
            continue;
            }
        else if(
            array_key_exists($archive_state["ref"], $archive_states_spec)
            && !in_array($archive_states_spec[$archive_state["ref"]], $dest_archive_states))
            {
            logScript("WARNING: Incorrect mapping? Attempted to map to workflow state #{$archive_states_spec[$archive_state["ref"]]}! Skipping");
            continue;
            }

        if(array_key_exists($archive_state["ref"], $archive_states_spec) && is_null($archive_states_spec[$archive_state["ref"]]))
            {
            logScript("Updating config.php with extra workflow state:");

            $new_archive_state = end($dest_archive_states) + 1;
            $additional_archive_states[] = $new_archive_state;
            $lang["status{$new_archive_state}"] = $archive_state["lang"];
            $dest_archive_states[] = $new_archive_state;

            if($dry_run)
                {
                logScript("CONFIG.PHP: \$additional_archive_states[] = {$new_archive_state};");
                logScript("CONFIG.PHP: \$lang['status{$new_archive_state}'] = '{$archive_state["lang"]}';");

                continue;
                }

            $config_fh = fopen("{$webroot}/include/config.php", "a+b");
            if($config_fh === false)
                {
                logScript("ERROR: Unable to open output file '{$file_path}'! Please add manually to the file the following:");
                logScript("CONFIG.PHP: \$additional_archive_states[] = {$new_archive_state};");
                logScript("CONFIG.PHP: \$lang['status{$new_archive_state}'] = '{$archive_state["lang"]}';");
                }

            fwrite($config_fh, "\$additional_archive_states[] = {$new_archive_state};" . PHP_EOL);
            fwrite($config_fh, "\$lang['status{$new_archive_state}'] = '{$archive_state["lang"]}';" . PHP_EOL);
            fclose($config_fh);
            }
        }


    # RESOURCE TYPES
    ################
    logScript("");
    logScript("Importing resource types...");
    fwrite($spec_override_fh, PHP_EOL . PHP_EOL);
    if(!isset($resource_types_spec) || empty($resource_types_spec))
        {
        logScript("ERROR: Spec missing 'resource_types_spec'");
        exit(1);
        }
    $src_resource_types = $json_decode_file_data($get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_types_export.json", "r+b"));
    $dest_resource_types = get_resource_types("", false);
    foreach($src_resource_types as $resource_type)
        {
        logScript("Processing #{$resource_type["ref"]} '{$resource_type["name"]}'");

        if(!array_key_exists($resource_type["ref"], $resource_types_spec))
            {
            logScript("WARNING: resource_types_spec does not have a record for this resource type");
            continue;
            }

        if(!is_null($resource_types_spec[$resource_type["ref"]]))
            {
            if(!is_numeric($resource_types_spec[$resource_type["ref"]]))
                {
                logScript("ERROR: Invalid mapped value!");
                exit(1);
                }

            $found_rt_index = array_search($resource_types_spec[$resource_type["ref"]], array_column($dest_resource_types, "ref"));
            if($found_rt_index === false)
                {
                logScript("ERROR: Unable to find destination resource type!");
                exit(1);
                }

            $found_rt = $dest_resource_types[$found_rt_index];
            logScript("Found direct 1:1 mapping to #{$found_rt["ref"]} '{$found_rt["name"]}'");
            continue;
            }

        // New record
        sql_query(
            sprintf("INSERT INTO resource_type(`name`, config_options, allowed_extensions, tab_name, push_metadata, inherit_global_fields)
                          VALUES (%s, %s, %s, %s, %s, %s);",
            (trim($resource_type["name"]) == "" ? "'" . escape_check($resource_type["name"]) . "'" : "NULL"),
            (trim($resource_type["config_options"]) == "" ? "'" . escape_check($resource_type["config_options"]) . "'" : "NULL"),
            (trim($resource_type["allowed_extensions"]) == "" ? "'" . escape_check($resource_type["allowed_extensions"]) . "'" : "NULL"),
            (trim($resource_type["tab_name"]) == "" ? "'" . escape_check($resource_type["tab_name"]) . "'" : "NULL"),
            (trim($resource_type["push_metadata"]) == "" ? "'" . escape_check($resource_type["push_metadata"]) . "'" : "NULL"),
            (trim($resource_type["inherit_global_fields"]) == "" ? "'" . escape_check($resource_type["inherit_global_fields"]) . "'" : "NULL")
        ));
        $new_rt_ref = sql_insert_id();

        log_activity(null, LOG_CODE_EDITED, $resource_type["name"], 'resource_type', 'name', $new_rt_ref);
        log_activity(null, LOG_CODE_EDITED, $resource_type["config_options"], 'resource_type', 'config_options', $new_rt_ref);
        log_activity(null, LOG_CODE_EDITED, $resource_type["allowed_extensions"], 'resource_type', 'allowed_extensions', $new_rt_ref);
        log_activity(null, LOG_CODE_EDITED, $resource_type["tab_name"], 'resource_type', 'tab_name', $new_rt_ref);
        log_activity(null, LOG_CODE_EDITED, $resource_type["push_metadata"], 'resource_type', 'push_metadata', $new_rt_ref);
        log_activity(null, LOG_CODE_EDITED, $resource_type["inherit_global_fields"], 'resource_type', 'inherit_global_fields', $new_rt_ref);

        logScript("Created new record #{$new_rt_ref} '{$resource_type["name"]}'");
        $resource_types_spec[$resource_type["ref"]] = $new_rt_ref;
        fwrite($spec_override_fh, "\$resource_types_spec[{$resource_type["ref"]}] = {$new_rt_ref};" . PHP_EOL);
        }


    # RESOURCE TYPE FIELDS
    ######################
    logScript("");
    logScript("Importing resource_type fields...");
    fwrite($spec_override_fh, PHP_EOL . PHP_EOL);
    if(!isset($resource_type_fields_spec) || empty($resource_type_fields_spec))
        {
        logScript("ERROR: Spec missing 'resource_type_fields_spec'");
        exit(1);
        }
    $src_resource_type_fields = $json_decode_file_data($get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_type_fields_export.json", "r+b"));
    $dest_resource_type_fields = get_resource_type_fields("", "ref", "ASC", "", array());
    $resource_type_fields_not_created = (isset($resource_type_fields_not_created) ? $resource_type_fields_not_created : array());
    foreach($src_resource_type_fields as $src_rtf)
        {
        logScript("Processing #{$src_rtf["ref"]} '{$src_rtf["title"]}'");

        if(!array_key_exists($src_rtf["ref"], $resource_type_fields_spec))
            {
            logScript("WARNING: Specification missing mapping for this resource type field! Skipping");
            $resource_type_fields_not_created[] = $src_rtf["ref"];
            fwrite($spec_override_fh, "\$resource_type_fields_not_created[] = {$src_rtf["ref"]};" . PHP_EOL);
            continue;
            }

        // Check if we need to create this field
        if(!(isset($resource_type_fields_spec[$src_rtf["ref"]]["create"]) && is_bool($resource_type_fields_spec[$src_rtf["ref"]]["create"])))
            {
            logScript("ERROR: invalid mapping configuration for mapped value. Expecting array type with index 'create' of type boolean.");
            exit(1);
            }
        if(!$resource_type_fields_spec[$src_rtf["ref"]]["create"])
            {
            logScript("Mapping set to not be created. Skipping");
            $resource_type_fields_not_created[] = $src_rtf["ref"];
            fwrite($spec_override_fh, "\$resource_type_fields_not_created[] = {$src_rtf["ref"]};" . PHP_EOL);
            continue;
            }

        /* 
        Check if we have a field mapped. Expected values:
            - integer when we have a direct mapping
            - null when a new field should be created
        */
        if(
            !(
                (
                    isset($resource_type_fields_spec[$src_rtf["ref"]]["ref"])
                    && (
                            is_int($resource_type_fields_spec[$src_rtf["ref"]]["ref"])
                            && $resource_type_fields_spec[$src_rtf["ref"]]["ref"] > 0
                        )
                )
                || is_null($resource_type_fields_spec[$src_rtf["ref"]]["ref"])
            )
        )
            {
            logScript("ERROR: invalid mapping configuration for mapped value. Expecting array type with index 'ref' of type integer OR use 'null' to create new field.");
            exit(1);
            }
        $mapped_rtf_ref = $resource_type_fields_spec[$src_rtf["ref"]]["ref"];

        // This is merged as a new field
        if(is_null($mapped_rtf_ref))
            {
            $new_rtf_ref = create_resource_type_field(
                $src_rtf["title"],
                $resource_types_spec[$src_rtf["resource_type"]],
                $src_rtf["type"],
                $src_rtf["name"],
                $src_rtf["keywords_index"]);

            if($new_rt_ref === false)
                {
                logScript("ERROR: unable to create new resource type field!");
                exit(1);
                }

            // IMPORTANT: we explicitly don't escape SQL values in this case as this should be the exact value stored in the SRC DB
            $sql = "";
            foreach($src_rtf as $column => $value)
                {
                // Ignore columns that have been used for creating this field
                if(in_array($column, array("ref", "name", "title", "type", "keywords_index", "resource_type")))
                    {
                    continue;
                    }

                if(trim($sql) != "")
                    {
                    $sql .= ", ";
                    }

                $col_val = (trim($value) == "" ? "NULL" : "'{$value}'");
                $sql .= "`{$column}` = {$col_val}";
                log_activity(null, LOG_CODE_EDITED, $col_val, 'resource_type_field', $column, $new_rtf_ref);
                }
            sql_query("UPDATE resource_type_field SET {$sql} WHERE ref = '{$new_rtf_ref}'");

            logScript("Created new record #{$new_rtf_ref} '{$src_rtf["title"]}'");
            $resource_type_fields_spec[$src_rtf["ref"]] = array("create" => true, "ref" => $new_rtf_ref);
            fwrite($spec_override_fh, "\$resource_type_fields_spec[{$src_rtf["ref"]}] = array(\"create\" => true, \"ref\" => {$new_rtf_ref});" . PHP_EOL);

            $new_rtf_data = $src_rtf;
            $new_rtf_data["ref"] = $new_rtf_ref;
            $new_rtf_data["resource_type"] = $resource_types_spec[$src_rtf["resource_type"]];
            $dest_resource_type_fields[] = $new_rtf_data;

            unset($new_rtf_ref);
            unset($new_rtf_data);
            continue;
            }

        /*
        Check if specification file allows incompatible types to be migrated from one to the other.
        Default value: FALSE
            - TRUE  - Show warning to the user AND keep processing. NOTICE: this will flatten category trees after merge!
            - FALSE - Show warning to the user about incompatible types
        */
        if(!(
            isset($resource_type_fields_spec[$src_rtf["ref"]]["allow_incompatible_types"])
            && is_bool($resource_type_fields_spec[$src_rtf["ref"]]["allow_incompatible_types"])))
            {
            $resource_type_fields_spec[$src_rtf["ref"]]["allow_incompatible_types"] = false;
            }
        $mapped_rtf_allow_incompatible_types = $resource_type_fields_spec[$src_rtf["ref"]]["allow_incompatible_types"];

        // direct mapping? is mapped value valid dest rtf?
        $found_rtf_index = array_search($mapped_rtf_ref, array_column($dest_resource_type_fields, "ref"));
        if($found_rtf_index === false)
            {
            logScript("ERROR: Unable to find destination resource type field!");
            exit(1);
            }
        $found_rtf = $dest_resource_type_fields[$found_rtf_index];
        logScript("Found direct 1:1 mapping to #{$found_rtf["ref"]} '{$found_rtf["title"]}'");

        /*
        Compatible changes between types:
         - Text to Text
         - Text to Fixed Lists
         - Fixed Lists to Fixed Lists
         - - Category Tree to other Fixed Lists (if allow incompatible types => CT will flatten)
         */
        // check page/tools/migrate_data_to_fixed.php and reuse code from there (try and create functions)

        // Check compatiblity between types
        $src_text_field = in_array($src_rtf["type"], $TEXT_FIELD_TYPES);
        $src_fixed_list_field = in_array($src_rtf["type"], $FIXED_LIST_FIELD_TYPES);
        $src_cat_tree_field = ($src_rtf["type"] == FIELD_TYPE_CATEGORY_TREE);
        $dest_text_field = in_array($found_rtf["type"], $TEXT_FIELD_TYPES);
        $dest_fixed_list_field = in_array($found_rtf["type"], $FIXED_LIST_FIELD_TYPES);
        $dest_cat_tree_field = ($found_rtf["type"] == FIELD_TYPE_CATEGORY_TREE);
        if(!(
                ($src_text_field && $dest_text_field)
                || ($src_text_field && $dest_fixed_list_field)
                || ($src_fixed_list_field && $dest_fixed_list_field)
            ))
            {
            logScript("WARNING: incompatible!");
            if(!$mapped_rtf_allow_incompatible_types)
                {
                exit(1);
                }
            }












        }
    unset($src_resource_type_fields);






    // Useful snippet
    // $usernames_mapping[$user["ref"]] = $new_uref;
    // fwrite($spec_override_fh, "\$usernames_mapping[{$user["ref"]}] = {$new_uref};" . PHP_EOL);
    // fwrite($spec_override_fh, "" . PHP_EOL);

    // fwrite($spec_override_fh, "" . PHP_EOL);
    fclose($spec_override_fh);

    if(!($dry_run || $rollback_transaction) && db_end_transaction())
        {
        logScript("");
        logScript("MySQL: Commit transaction!");
        }
    else if(db_rollback_transaction())
        {
        logScript("");
        logScript("MySQL: Rollback Successful!");
        }
    }