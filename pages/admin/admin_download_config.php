<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php'; if(!checkperm('a')) { exit('Permission denied.');}
include_once '../../include/config_functions.php';

if(!extension_loaded("zip"))
    {
    error_alert(str_replace("%%MODULE%%","php-zip",$lang["error_server_missing_module"]),true,200);
    exit();
    }

function random_char()
    {        
    $hex_code = dechex(mt_rand(195, 202));    
    $hex_code .= dechex(mt_rand(128, 175));
    return pack('H*', $hex_code);
    }

function random_date($fromdate)
    {
    if(trim($fromdate==""))
        {
        $tstamp = time();
        }
    else
        {
        $date = new DateTime($fromdate);
        $tstamp = strtotime($fromdate);
        }

    $dateshift = 60*60*24*30; // How much should dates be moved
    $newstamp = $tstamp + (mt_rand(-$dateshift,$dateshift));
    $newdate = date('Y-m-d H:i:s',$newstamp);
    //echo "converting date " . $fromdate . " to " . $newdate  ."<br/>\n";
    return $newdate;
    }
 
function mix_text($string)
    {    
    $numbers = '0123456789';
    $uppercons = 'BCDFGHJKLMNPQRSTVWXZ';
    $uppervowels = 'AEIOUY';
    $lowercons = 'bcdfghjklmnpqrstvwxz';
    $lowervowels = 'aeiouy';
    $noreplace = "'\".,<>#-_&\$£^?!+()*% \n";

   // echo "Converting string<br/>" . $string . "<br/><br/>";
    $newstring = "";
    $bytelength = strlen($string);
    $mbytelength = mb_strlen($string);
    //echo $bytelength . " characters<br/>";
   // echo $mbytelength . " multibyte characters<br/>";

    // Simple conversion if numbers
    if($bytelength == $mbytelength && (string)(int)$string == $string)
        {
        //return substr(str_shuffle($numbers), 0, $bytelength);
        $newstring =  mt_rand(0,(int)$string);
        }
    else
        {
        // Process each character
        for($i=0;$i<$mbytelength;$i++)
            {
            if($i > 3)
                {
                // Randomly add or remove character after first
                $randaction = mt_rand(0,10);
                if($randaction == 0)
                    {
                    // Skip a character
                    $i++;
                    }
                elseif($randaction == 1)
                    {
                    // Add a character
                    $i--;
                    }
                }
          
            $oldchar = mb_substr($string,$i,1);
            //echo "</br/>..Converting character #$i '" . $oldchar . "'<br/>"; 
            if($i >= $mbytelength || $oldchar == "")
                {
                $newstring .=  substr(str_shuffle($lowervowels . $lowercons), 0,1);                    
                //echo "newstring: " . $newstring . "<br/>";
                }
            elseif(strpos($noreplace,$oldchar) !== false)
                {
                $newstring .= $oldchar;
                }
            elseif(strlen($oldchar)==1)
                {
                // Non- multibyte
                //echo "..Converting standard character " . $oldchar . "<br/>";
                if(strpos($lowercons,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($lowercons), 0,1);
                    }
                elseif(strpos($uppercons,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($uppercons), 0,1);
                    }
                elseif(strpos($lowervowels,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($lowervowels), 0,1);
                    }
                elseif(strpos($uppervowels,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($uppervowels), 0,1);
                    }                    
                elseif(strpos($numbers,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($numbers), 0,1);
                    }
                else
                    {
                    $newchar = substr(str_shuffle($noreplace), 0,1);
                    }
                $newstring .= $newchar;        
                }                         
            else
                {
                $newchar = random_char();
                $newstring .= $newchar;        
                } // End of multibyte conversion
            }
        }

   return $newstring;
   }
function alter_data(&$row,$key,$jumblecolumns)
    {
    global $datetime_fields;
    foreach($jumblecolumns as $jumblecolumn=>$jumbletype)
        {
        $row[$jumblecolumn] = call_user_func($jumbletype , $row[$jumblecolumn]);
        // $row[$jumblecolumn] = mix_text($row[$jumblecolumn]);
        }
    }

function replace_nulls($value)
    {
    return trim($value)=="" ? "NULL" : "'" . escape_check($value) . "'";
    }

// Dump MySQL tables
$exporttables = array();
$exporttables["sysvars"] = array();
$exporttables["preview_size"] = array();

$exporttables["user"] = array();
$exporttables["user"]["jumble"]=array("username","email","fullname","comments");

$exporttables["usergroup"] = array();
$exporttables["user_preferences"] = array();

$exporttables["resource_type"] = array();
$exporttables["resource_type_field"] = array();
$exporttables["node"] = array();

$exporttables["filter"] = array();
$exporttables["filter_rule"] = array();
$exporttables["filter_rule_node"] = array();

$exporttables["archive_states"] = array();
$exporttables["workflow_actions"] = array();

// Optional fields
//  - Resources and resource metadata
$exporttables["resource"] = array();
$exporttables["resource"]["jumble"]=array("field8"=>"mix_text","creation_date"=>"random_date");
//$exporttables["resource"]["exportcondition"] = " WHERE ref>0";

$exporttables["resource_data"] = array();
$exporttables["resource_data"]["jumble"]=array("value");

$exporttables["resource_custom_access"] = array();
$exporttables["resource_node"] = array();
$exporttables["resource_dimensions"] = array();
$exporttables["resource_related"] = array();
$exporttables["resource_alt_files"] = array();
$exporttables["resource_alt_files"]["jumble"]=array("name","description","file_name");
$exporttables["annotation"] = array();
$exporttables["annotation_node"] = array();


//  - Collections
$exporttables["collection"] = array();
$exporttables["user_collection"] = array();
$exporttables["usergroup_collection"] = array();
$exporttables["collection_resource"] = array();
$exporttables["collection_savedsearch"] = array();
$exporttables["external_access_keys"] = array();


$path=$mysql_bin_path . "/mysqldump";


$randstring=md5(rand() . microtime());
$dumppath = get_temp_dir(false,md5($username . $randstring . $scramble_key)) . "/mysql";
mkdir($dumppath);

$export = getval("export","") != "";
if (TRUE || $export!="" && enforcePostRequest(false))
	{   
    // Create  job data

    // TODO use the user's temp directory
    $zipfile = get_temp_dir() . "/system.zip";
    $zip = new ZipArchive();
    $zip->open($zipfile, ZIPARCHIVE::CREATE);

    $zip->addFile("../../include/config.php", "config.php");

    
    foreach($exporttables as $exporttable=>$exportoptions)
        {
        // TODO: remove testing code
        if($exporttable != "resource"){continue;}

        /// TODO - only export resources in a collection or range

        $dumpfile = $dumppath . "/" . $exporttable . ".sql";
       
        // Add the 'CREATE TABLE' command
        $dumpcmd = $path . " -h " . $mysql_server . " -u " . $mysql_username . ($mysql_password == "" ? "" : " -p" . $mysql_password) . " " . $mysql_db . " --no-data " . $exporttable . " >> " . $dumpfile;
        run_command($dumpcmd);
        
        // Get data 
        $exportcondition = isset($exportoptions["exportcondition"]) ? $exportoptions["exportcondition"] : "";
        $datarows = sql_query("SELECT * FROM " . $exporttable . $exportcondition); 
        
        // Call function to scramble the data based on per table configuration
        array_walk($datarows, 'alter_data',isset($exportoptions["jumble"]) ? $exportoptions["jumble"] : array());
        
        // Get columns to insert
        $columns = array_map("escape_check",array_keys($datarows[0]));

        $sql = "";
        foreach($datarows as $datarow)
            {
            $datarow = array_map("replace_nulls",$datarow);
            $sql .= "INSERT INTO " . $exporttable . " (" . implode(",",$columns) . ") VALUES (" . implode(",",$datarow) . ");\n";
            }

        $output = fopen($dumpfile,'a');
        fwrite($output,$sql);
        fclose($output);
        
        $zip->addFile($dumpfile, "mysql/" . $exporttable . ".sql");
        }
    echo $zipfile . "<br/>";
    }

exit();

// This page will create an offline job that creates a zip file containing sytem configuration nformation and data
/*
- include/config.php
- sysvars table
- user_preferences table**
- user table*
- usergroup table
- resource_type table
- resource_type_field table
- resource_related table
- resource_data table*
- resource_custom_access table
- resource_alt_files table*
- preview_size table
- node table*
- filter, filter_rule and filter_rule_node tables. 
- external_access_keys table
- dash_tile table
- collection table
- archive_states table
- annotation and annotation_node tables
*/


include '../../include/header.php';


include '../../include/footer.php';
