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
// include_once "{$webroot}/include/collections_functions.php";

$get_file_handler = function($file_path)
    {
    $file_handler = fopen($file_path, "w+b");
    if($file_handler === false)
        {
        logScript("ERROR: Unable to open output file '{$file_path}'!");
        exit(1);
        }

    return $file_handler;
    };

if($dry_run)
    {
    logScript("#################################################################");
    logScript("##### WARNING - Script running with DRY-RUN option enabled! #####");
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
    # USER GROUPS
    #############
    logScript("");
    logScript("Exporting user groups...");

    $usergroups_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "usergroups_export.json");
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

    $users_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "users_export.json");
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

    $archive_states_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "archive_states_export.json");
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

    $resource_types_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_types_export.json");
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

    $resource_type_fields_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_type_fields_export.json");
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
    $nodes_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "nodes_export.json");
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
    $resources_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resources_export.json");
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
    $resource_data_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_data_export.json");
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
    $resource_nodes_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_nodes_export.json");
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
    $resource_dimensions_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_dimensions_export.json");
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
    $resource_related_export_fh = $get_file_handler($folder_path . DIRECTORY_SEPARATOR . "resource_related_export.json");
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

if($import)
    {
    if(!isset($spec_file_path) || trim($spec_file_path) == "")
        {
        logScript("ERROR: Specification file not provided or empty!");
        exit(1);
        }
    include_once $spec_file_path;

    /*
    // @todo: create local anon function for checking specs or just a simple loop if needed
    if(!isset($usergroups_spec) || empty($usergroups_spec))
        {
        logScript("ERROR: Spec missing 'usergroups_spec'");
        exit(1);
        }
    */
    }