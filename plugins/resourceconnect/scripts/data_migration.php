<?php
/**
* @package ResourceConnect
* 
* Script used to easily migrate data between two systems that are connected via ResourceConnect.
* It allows migrating collections and their resources from one system to another while keeping the ResourceConnect 
* association.
*/
if('cli' != PHP_SAPI)
    {
    http_response_code(401);
    exit('Access denied - Command line only!');
    }

// @todo: once ResourceSpace supports a higher version of PHP, replace dirname(dirname()) with the use of "levels" parameter
$webroot = dirname(dirname(dirname(__DIR__)));
include_once "{$webroot}/include/db.php";
include_once "{$webroot}/include/general.php";
include_once "{$webroot}/plugins/resourceconnect/include/resourceconnect_functions.php";
include_once "{$webroot}/include/log_functions.php";
include_once "{$webroot}/include/resource_functions.php";
include_once "{$webroot}/include/collections_functions.php";

// Script options
$cli_short_options = 'i:';
$cli_long_options  = array(
    'export-collections',
    'import-collections',
    'file:',
);
$options = getopt($cli_short_options, $cli_long_options);

$export_collections = false;
$import_collections = false;

foreach($options as $option_name => $option_value)
    {
    if($option_name == "i" && !is_array($option_value))
        {
        $input_fh = fopen($option_value, "r+b");
        if($input_fh === false)
            {
            logScript("ERROR: Unable to open input file '{$option_value}'!");
            exit(1);
            }
        }

    if(in_array($option_name, array("export-collections", "import-collections")))
        {
        $option_name = str_replace("-", "_", $option_name);
        $$option_name = true;
        }

    if($option_name == "file")
        {
        if(is_array($option_value))
            {
            logScript("ERROR: 'file' flag cannot be used more than once!");
            exit(1);
            }

        $file_h = fopen($option_value, "a+b");
        if($file_h === false)
            {
            logScript("ERROR: Unable to open output file '{$option_value}'!");
            exit(1);
            }
        }
    }

/*
EXPORT
======
 DONE - Will be passed a file containing list of usernames
 DONE - Will retrieve all the information for collections that belong to these users, with associated resource information
 DONE - Will use this data along with the permissions of the ResourceConnect user to check access and generate the link data 
   needed to access the resources from the server
 DONE - Generate an output file that can be used on the server
*/
if($export_collections && isset($input_fh) && isset($file_h))
    {
    logScript("Exporting collections for list of users...");
    $input_lines = array();
    while(($line = fgets($input_fh)) !== false)
            {
            if(trim($line) != "" &&  mb_check_encoding($line, 'UTF-8'))
                {
                $input_lines[] = trim($line);
                }
            }
    fclose($input_fh);

    $exported_data = array();

    foreach($input_lines as $username)
        {
        logScript("");
        logScript("Checking username '{$username}'");
        if(trim($username) === "")
            {
            continue;
            }
        $original_username = $username;
        $username = escape_check($username);
        $user_select_sql = "AND u.username = '{$username}' AND usergroup IN (SELECT ref FROM usergroup)";
        $user_data = validate_user($user_select_sql, true);
        if(!is_array($user_data) || count($user_data) == 0)
            {
            logScript("Warning - Unable to validate user '{$username}'");
            continue;
            }
        setup_user($user_data[0]);
        logScript("Set up user '{$username}' (ID #{$userref})");

        $user_collections = get_user_collections($userref);

        // Switch over to the ResourceConnect user (to ensure permissions are honoured) before continuing
        if(!is_numeric($resourceconnect_user) || $resourceconnect_user <= 0)
                {
                logScript("ERROR - Invalid ResourceConnect user ID #{$resourceconnect_user}!");
                exit(1);
                }
        $resourceconnect_user_escaped = escape_check($resourceconnect_user);
        $user_data = validate_user("AND u.ref = '{$resourceconnect_user_escaped}'", true);
        if(!is_array($user_data) || count($user_data) == 0)
            {
            logScript("ERROR - Unable to validate ResourceConnect user ID #{$resourceconnect_user}!");
            exit(1);
            }
        setup_user($user_data[0]);
        logScript("Set up ResourceConnect user '{$username}' (ID #{$userref})");

        foreach($user_collections as $collection_data)
            {
            logScript("Checking user collection '{$collection_data["name"]}' (ID #{$collection_data["ref"]}) - with {$collection_data["count"]} resources");
            if($collection_data["count"] == 0)
                {
                logScript("Skipping");
                continue;
                }

            if(!collection_readable($collection_data["ref"]))
                {
                logScript("Warning - no read access by ResourceConnect user!");
                continue;
                }

            $collection_resources = get_collection_resources($collection_data["ref"]);
            foreach($collection_resources as $resource_ref)
                {
                if(get_resource_access($resource_ref) !== 0)
                    {
                    logScript("Warning - no full access by ResourceConnect user! Skipping");
                    continue;
                    }

                $resource_data = get_resource_data($resource_ref);
                $thumb = "{$baseurl}/gfx/" . get_nopreview_icon($resource_data["resource_type"], $resource_data["file_extension"], true);
                $large_thumb = "{$baseurl}/gfx/" . get_nopreview_icon($resource_data["resource_type"], $resource_data["file_extension"], false);
                $xl_thumb = "{$baseurl}/gfx/" . get_nopreview_icon($resource_data["resource_type"], $resource_data["file_extension"], false);
                if((bool) $resource_data["has_image"])
                    {
                    $thumb = get_resource_path($resource_ref, false, "col", false, "jpg");
                    $large_thumb = get_resource_path($resource_ref, false, "thm", false, "jpg");
                    $xl_thumb = get_resource_path($resource_ref, false, "pre", false, "jpg");
                    }
                $url = generateURL(
                    "{$baseurl}/pages/view.php",
                    array(
                        "ref"   => $resource_ref,
                        "k"     => ResourceConnect\generate_k_value($original_username, $resource_ref, $scramble_key),
                        "modal" => "true",
                    ));

                $exported_data[] = array(
                    "username"    => $original_username,
                    "collection"  => $collection_data["ref"],
                    "thumb"       => $thumb,
                    "large_thumb" => $large_thumb,
                    "xl_thumb"    => $xl_thumb,
                    "url"         => $url,
                    "title"       => get_data_by_field($resource_ref, $view_title_field));
                }
            }
        }

    if(!empty($exported_data))
        {
        fwrite($file_h, json_encode($exported_data, JSON_NUMERIC_CHECK));
        $meta_data = stream_get_meta_data($file_h);
        logScript("Successfully exported data to '{$meta_data["uri"]}'");
        }
    fclose($file_h);
    }

 /*
IMPORT
======
 - Can be fed the path to the file created by the first script
 - Will locate the target users and create the collections with associated ResourceConnect links on the server

Notes and caveats

1. Provide a list of usernames that need to have collections copied
2. Any resources in a user's collections that are not accessible by the ResourceConnect user will not be copied
3. ResourceConnect pseudo-resources are not affected by actions on the remote system. If a resource is deleted any 
   ResourceConnect collection links will remain but will generate errors upon access
*/
if($import_collections && isset($input_fh))
    {
    // sql_query("INSERT INTO resourceconnect_collection_resources (collection,thumb,large_thumb,xl_thumb,url,title) VALUES ('$usercollection','$thumb','$large_thumb','$xl_thumb','$url','$title')");

    fclose($input_fh);
    }