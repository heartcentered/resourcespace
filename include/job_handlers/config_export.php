<?php
/*
Create a zip file with system configuration and selected data
Requires the following:-

$job_data["exporttables"] - Array of table information to export
$job_data["obfuscate"] -  Whether table data should be obfuscated or not
*/
global $baseurl, $userref, $offline_job_delete_completed, $lang,$mysql_bin_path, $mysql_server, $mysql_db,$mysql_username,$mysql_password,$scramble_key;
$exporttables   = $job_data["exporttables"];
$obfuscate      = $job_data["obfuscate"];
$userref        = $job_data["userref"];
$path           = $mysql_bin_path . "/mysqldump";


$jobsuccess = false;

$randstring=md5(rand() . microtime());
$dumppath = get_temp_dir(false,md5($userref . $randstring . $scramble_key)) . "/mysql";
$zippath = get_temp_dir(false,'user_downloads');
mkdir($dumppath,0777,true);
//mkdir($zippath,0777,true);

$zipfile = $zippath . "/" . $userref . "_" . md5($userref . $randstring . $scramble_key) . ".zip";

$zip = new ZipArchive();
$zip->open($zipfile, ZIPARCHIVE::CREATE);

$zip->addFile("../../include/config.php", "config.php");

$dumpfile = $dumppath . "/resourcespace.sql";

foreach($exporttables as $exporttable=>$exportoptions)
    {
    echo "Exporting table " . $exporttable . "\n";
 
    // Add the 'CREATE TABLE' command
    $dumpcmd = $path . " -h " . $mysql_server . " -u " . $mysql_username . ($mysql_password == "" ? "" : " -p" . $mysql_password) . " " . $mysql_db . " --no-data " . $exporttable . " >> " . $dumpfile;
    run_command($dumpcmd);
    
    // Get data 
    $exportcondition = isset($exportoptions["exportcondition"]) ? $exportoptions["exportcondition"] : "";
    $datarows = sql_query("SELECT * FROM " . $exporttable . " " . $exportcondition); 
    
    if(count($datarows) > 0)
        {
        // Call function to scramble the data based on per table configuration
        array_walk($datarows, 'alter_data',(isset($exportoptions["scramble"]) && $obfuscate) ? $exportoptions["scramble"] : array());
        
        // Get columns to insert
        $columns = array_map("escape_check",array_keys($datarows[0]));

        $sql = "";
        foreach($datarows as $datarow)
            {
            $datarow = array_map("safe_export",$datarow);
            $sql .= "INSERT INTO " . $exporttable . " (" . implode(",",$columns) . ") VALUES (" . implode(",",$datarow) . ");\n";
            }

        $output = fopen($dumpfile,'a');
        fwrite($output,$sql);
        fclose($output);
        $zip->addFile($dumpfile, "mysql/" . $exporttable . ".sql");
        }        
    }

$zip->close();

if(file_exists($zipfile))
    {
    $download_url = $baseurl . "/pages/download.php?userfile=" . $userref . "_" . $randstring . ".zip";
    $message = $lang["exportcomplete"];;
    message_add($job["user"],$message,$download_url,0);
    if($offline_job_delete_completed)
        {
        job_queue_delete($jobref);
        }
    else
        {
        job_queue_update($jobref,$job_data,STATUS_COMPLETE);
        }
    
    $delete_job_data=array();
    $delete_job_data["file"]=$zipfile;
    $delete_date = date('Y-m-d H:i:s',time()+(60*60*24)); // Delete these after 1 day
    $job_code=md5($zipfile); 
    job_queue_add("delete_file",$delete_job_data,"",$delete_date,"","",$job_code);
    $jobsuccess = true;
    }
        
if(!$jobsuccess)
	{
	// Job failed, update job queue
	job_queue_update($jobref,$job_data,STATUS_ERROR);
    $message=$lang["exportfailed"];
    message_add($job["user"],$message,"",0);
    }
    
unlink($dumpfile);
