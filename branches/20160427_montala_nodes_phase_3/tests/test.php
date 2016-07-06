<?php
include_once "../include/db.php";
include_once "../include/general.php";
include_once "../include/resource_functions.php";
include_once "../include/collections_functions.php";

if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

/*
  
  Test.php
  --------
  Create a test database and storagedir, then run a series of tests in sequence
  A default database will be created but tests can create new fields, resource types, etc as part of testing, and those items
  utilised by later tests.

  */

// note that we do not include anything at this point as db.php will try and connect to a given schema and
// perform an upgrade - we do not want this to happen for this new test schema

$suppress_headers=true;

$argv=preg_replace('/^(-|--|\/)/','',$argv);    // remove leading /, -- or -

if(array_search('?',$argv)!==false || array_search('help',$argv)!==false)
    {
?>

Command line paramaters:

-nosetup        Do not setup the database, connect user in current state
-noteardown     Do not drop the database once tests have completed
-help or -?     This help information
[n]...          Specific test number(s) to run
<?php
    exit;
    }

# Create an array of tests that were passed from the command line
$specific_tests=array();
foreach($argv as $arg)
    {
    if(is_numeric($arg))
        {
        array_push($specific_tests, str_pad($arg,6,'0',STR_PAD_LEFT));
        }
    }

$mysql_db = "rs_test_db";
$test_user_name = "admin";

function create_new_db($db_name)
    {
    # Create a database for testing purposes
    echo "Creating database $db_name\n";
    include __DIR__ . '/../include/config.php';
    $db=mysqli_connect($mysql_server,$mysql_username,$mysql_password,'');
    mysqli_query($db,"drop database if exists `$db_name`");
    mysqli_query($db,"create database `$db_name`");
    mysqli_query($db,"CREATE TABLE `{$db_name}`.`sysvars`(`name` VARCHAR(50) NOT NULL, `value` TEXT NULL, PRIMARY KEY (`name`))");
    mysqli_query($db,"INSERT INTO `{$db_name}`.`sysvars`(`name`,`value`) VALUE ('upgrade_system_level',999)");
    }

if(array_search('nosetup',$argv)===false)
    {
    # this has to be done in its own function as it includes the config.php and don't want to scope those vars globally
    create_new_db($mysql_db);
    }

sql_connect();
if(array_search('nosetup',$argv)===false)
    {
    # Connect and create standard tables.
    echo "Creating default database tables...";
    ob_flush();
    check_db_structs(true);
    echo "...done\n";
    # Insert a new user and run as them.
    $u = new_user($test_user_name);
    }
else
    {
    # Try to retrieve the ref of the existing user
    $u = sql_value("SELECT `ref` AS value FROM `user` WHERE `username`='{$test_user_name}'",-1);
    if ($u==-1)
        {
        die("Could not find existing '{$test_user_name}' user");
        }
    }

# Setup user
user_set_usergroup($u, 3);
$userdata = get_user($u);
setup_user($userdata);
echo "Now running as user $userref\n";
ob_flush();

# Use an alternative filestore path
$storagedir .= "/rs_test/";
if (!file_exists($storagedir))
    {
    mkdir($storagedir);
    }
echo "Filestore is now at $storagedir\n";

# Get a list of tests
$tests = scandir("test_list");
$tests = array_filter($tests, function ($string)
    {
    global $specific_tests;
    if (strpos($string, ".php")===false)
        {
        return false;
        }
    if (count($specific_tests)==0)
        {
        return true;
        }
    foreach($specific_tests as $specific_test)
        {
        if (strpos($string, $specific_test)!==false)
            {
            return true;
            }
        }
    return false;
    }); # PHP files only
asort($tests);

# Run tests
echo "-----\n";
foreach ($tests as $test)
    {
    echo "Running test " . str_pad($test,45," ") . " ";ob_flush();
    $result = include "test_list/" . $test;
    if ($result===false)
        {
        echo "FAIL\n";
        exit();
        }
    echo "OK\n";ob_flush();
    }
echo "-----\nAll tests complete.\n";

if(array_search('noteardown',$argv)===false)
    {
    # Remove database
    sql_query("drop database `$mysql_db`");
    }