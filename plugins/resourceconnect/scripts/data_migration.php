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
// echo "<pre>";print_r($options);echo "</pre>";echo PHP_EOL;

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
        }
    }

if(!isset($file_h) || $file_h === false)
    {
    logScript("ERROR: Unable to open file!");
    exit(1);
    }

/*
EXPORT
======
 DONE - Will be passed a file containing list of usernames
 DONE - Will retrieve all the information for collections that belong to these users, with associated resource information
 - Will use this data along with the permissions of the ResourceConnect user to check access and generate the link data 
   needed to access the resources from the server
 - Generate an output file that can be used on the server
 */
if($export_collections && isset($input_fh))
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

    $found_crs = array();

    foreach($input_lines as $username)
        {
        logScript("");
        logScript("Checking username '{$username}'");
        if(trim($username) === "")
            {
            continue;
            }
        $username = escape_check($username);

        $user_select_sql = "AND u.username = '{$username}' AND usergroup IN (SELECT ref FROM usergroup)";
        $user_data = validate_user($user_select_sql, true);
        if(!is_array($user_data) || count($user_data) == 0)
            {
            logScript("Warning - Unable to validate user '{$username}'");
            continue;
            }
        setup_user($user_data[0]);
        logScript("Set user ID #{$userref}");

        // @todo: consider filtering user collections more - my collections should not be migrated as the user will have one
        foreach(get_user_collections($userref) as $collection_data)
            {
            logScript("Checking user collection (ID #{$collection_data["ref"]}) - found {$collection_data["count"]} resources");
            $collection_data["resources"] = array();
            $collection_resources = get_collection_resources($collection_data["ref"]);

            if(is_array($collection_resources) && !empty($collection_resources))
                {
                $collection_data["resources"] = $collection_resources;
                }

            $found_crs[] = $collection_data;
            }

        /*
        get_user_collections($user,$find="",$order_by="name",$sort="ASC",$fetchrows=-1,$auto_create=true);
        get_collection($ref);
        get_collection_resources($collection);
        */
        }

    # Authenticate as 'resourceconnect' user.
    // @todo: put this logic into a function as we now need to do this multiple times during this script
    // global $resourceconnect_user; # Which user to use for remote access?
    // $userdata=validate_user("u.ref='$resourceconnect_user'");
    // setup_user($userdata[0]);

    foreach($found_crs as $found_cr)
        {
        // @todo: Will use this data along with the permissions of the ResourceConnect user to check access and generate the link data 
        // needed to access the resources from the server
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