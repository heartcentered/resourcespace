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
    --dry-run           perform a trial run with no changes made
    --spec-file=FILE    read specification from FILE
    --export            export information from ResourceSpace based on the specification file (Dependent on spec-file option)
    --import            import information to ResourceSpace based on the specification file (Dependent on spec-file option)
    " . PHP_EOL;

$cli_short_options = "h";
$cli_long_options  = array(
    "help",
    "dry-run",
    "spec-file:",
    "export",
    "import",
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
        $help = true;
        break;
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
        $spec_fh = fopen($option_value, "r+b");
        if($spec_fh === false)
            {
            fwrite(STDERR, "ERROR: Unable to open input file '{$option_value}'!");
            exit(1);
            }
        }
    }

if($help)
    {
    echo $help_text;
    exit(0);
    }

$webroot = dirname(dirname(__DIR__));
include_once "{$webroot}/include/db.php";
include_once "{$webroot}/include/general.php";
include_once "{$webroot}/include/log_functions.php";
// include_once "{$webroot}/include/resource_functions.php";
// include_once "{$webroot}/include/collections_functions.php";

if($dry_run)
    {
    logScript("#################################################################");
    logScript("##### WARNING - Script running with DRY-RUN option enabled! #####");
    }

// Ensure DEST/SRC folder has been provided when exporting or importing data
if($export && $argc > 1)
    {
    $folder_path = end($argv);
    }
else if($import && $argc > 1)
    {
    $folder_path = end($argv);
    }
else
    {
    logScript("ERROR: Export/Import modes require a DEST/SRC folder path");
    exit(1);
    }
if(!file_exists($folder_path) || !is_dir($folder_path))
    {
    logScript("ERROR: DEST/SRC MUST be folder. Value provided: '{$folder_path}'");
    exit(1);
    }

// 