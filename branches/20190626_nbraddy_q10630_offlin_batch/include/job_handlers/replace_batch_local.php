<?php
/*
Job handler to fetch files from a local path  for batch replacement of resources

Requires the following job data:-
$job_data['import_path'] - Folder to scan for files to import
*/

global $lang, $baseurl, $offline_job_delete_completed;

$local_path     = $job_data['import_path'];
$minref         = $job_data['resource_min'];
$maxref         = $job_data['resource_max'];
$collectionid   = $job_data['replace_col'];
$filename_field = $job_data['filename_field'];

if(!file_exists($local_path))
    {
    job_queue_update($jobref , $job_data , STATUS_ERROR);
    }

    
include_once __DIR__ . '/../search_functions.php';
include_once __DIR__ . '/../resource_functions.php';
include_once __DIR__ . '/../collections_functions.php';

if (!isset($collectionid) || $collectionid === 0)
    {
    $conditions = array();
    if (isset($minref) && $minref >= 0)
        {
        $conditions[] = "ref >='" . escape_check($minref) . "'";
        }
    if (isset($maxref) && $maxref != 0)
        {
        $conditions[] = "ref <='" . escape_check($maxref) . "'";
        }
    $replace_resources = sql_array("SELECT ref value FROM resource WHERE ref > '" . $fstemplate_alt_threshold . "' " . ((count($conditions) > 0) ? " AND " . implode(" AND ", $conditions):"") . " ORDER BY ref ASC",0);
    }
else
    {
    $replace_resources = get_collection_resources($collectionid);   
    }

    
$copied = array();
$errors = array();

//exit("HERE" . print_r($replace_resources));

$foldercontents = new DirectoryIterator($local_path);
foreach($foldercontents as $objectindex => $object)
    {
    if($object->isDot() || $object->isDir())
        {
        continue;
        }
        
    $filename = $object->getFilename();
    
    // get resource by $filename_field
    if($filename_field != "")
        {
        $target_resource=sql_array("select resource value from resource_data where resource_type_field='$filename_field' and value='" . escape_check($filename) . "'","");
        if(count($target_resource)==1)
            {
            // A single resource has been found with the same filename
            daily_stat("Resource upload",$target_resource[0]);
            $status=upload_file($target_resource[0],(getval("no_exif","")=="yes" && getval("exif_override","")==""),false,(getval('autorotate','')!=''), $plupload_upload_location); # Upload to the specified ref.
            if(file_exists($plupload_processed_filepath))
                {
                unlink($plupload_processed_filepath);
                }
            // Check to see if we need to notify users of this change							
            if($notify_on_resource_change_days!=0)
                {								
                // we don't need to wait for this..
                ob_flush();flush();
                
                notify_resource_change($target_resource[0]);
                }
            die('{"jsonrpc" : "2.0", "message" : "' . $lang["upload_success"] . '", "id" : "' . htmlspecialchars($target_resource[0]) . '"}');
            }
        elseif(count($target_resource)==0)
            {
            // No resource found with the same filename
            header('Content-Type: application/json');
            unlink($plfilepath);
            if(file_exists($plupload_processed_filepath))
                {
                unlink($plupload_processed_filepath);
                }
            die('{"jsonrpc" : "2.0", "error" : {"code": 106, "message": "ERROR - no resource found with filename ' . $origuploadedfilename . '"}, "id" : "id"}');
            }
        else
            {
            // Multiple resources found with the same filename
            // but we are going to replace them because $replace_batch_existing is set to true
            $resourcelist=implode(",",$target_resource);
            if ($replace_batch_existing)
                {
                foreach ($target_resource as $replaced)
                    {
                    $status = upload_file($replaced, ('yes' == getval('no_exif', '') && '' == getval('exif_override', '')), false, ('' != getval('autorotate', '')), $plupload_upload_location);
                    }
                unlink($plfilepath);
                die('{"jsonrpc" : "2.0", "message" : "' . $lang["replacefile"] . '", "id" : "' . $resourcelist . '"}');
                }
            else
                {
                // Multiple resources found with the same filename
                header('Content-Type: application/json');
                unlink($plfilepath);
                if(file_exists($plupload_processed_filepath))
                    {
                    unlink($plupload_processed_filepath);
                    }
                die('{"jsonrpc" : "2.0", "error" : {"code": 107, "message": "ERROR - multiple resources found with filename ' . $origuploadedfilename . '. Resource IDs : ' . $resourcelist . '"}, "id" : "id" }');
                }
            }
        }
        else
            {
            # Overwrite an existing resource using the number from the filename.
            # Extract the number from the filename
            $plfilename=strtolower(str_replace(" ","_",$plfilename));
            $s=explode(".",$plfilename);
            
            # does the filename follow the format xxxxx.xxx?
            if(2 == count($s))
                {
                $ref = trim($s[0]);

                // is the first part of the filename numeric?
                if(is_numeric($ref) && !resource_file_readonly($ref))
                    {
                    daily_stat("Resource upload",$ref);

                    $status = upload_file($ref, ('yes' == getval('no_exif', '') && '' == getval('exif_override', '')), false, ('' != getval('autorotate', '')), $plupload_upload_location);

                    die('{"jsonrpc" : "2.0", "message" : "' . $lang["replacefile"] . '", "id" : "' . htmlspecialchars($ref) . '"}');
                    }
                else
                    {
                    // No resource found with the same filename
                    header('Content-Type: application/json');
                    unlink($plfilepath);
                    die('{"jsonrpc" : "2.0", "error" : {"code": 106, "message": "ERROR - no ref matching filename ' . $origuploadedfilename . '"}, "id" : "id"}');
                    }
                }

            exit();
            }
  
  
  
  
  
  
  
  
  
  
  
        $success = renme($local_path . DIRECTORY_SEPARATOR . $objectname);
       
        
    if(!$success)
        {
        debug("rcRmdir: Unable to delete " . $path . DIRECTORY_SEPARATOR . $objectname);
        return false;
        }
    }
            

foreach($resources as $resource)
    {
    $rsfile = get_resource_path($resource["ref"],true,'',true,$resource["file_extension"]);
    if (!file_exists($rsfile))
        {
        echo "Resource # " . $resource["ref"] . " is missing.\n";
        $filename = get_download_filename($resource["ref"], "", -1, $resource["file_extension"]);
        $filepath = $local_path . $filename;
        echo "Checking for presence of file at: " . $filepath . "\n";
        if(file_exists($filepath))
            {
            echo " - Found file, copying\n";
            if(!$testmode)
               {
               $success = @copy($filepath,$rsfile);
               if($success)
                   {
                   $copied[] = $resource["ref"];
                   }
               else
                   {
                   echo "Copy failed\n";
                   $errors[] = "copy failed: " .  $filepath  . " to " . $rsfile; 
		   }
               }
            }
        else
            {
            echo " - The resource file for ref: " . $resource["ref"]  . " could not be accessed\n";
            $missingfiles[] = str_pad($resource["ref"],5) . " (" . $filename . ")";
            }
        }
    ob_flush();
    flush();
    }


echo "\nCopied resource files: -\n";
echo implode(",",$copied) . "\n";

echo "\nMissing files: -\n";
echo implode("\n",$missingfiles) . "\n";
echo "TOTAL MISSING: " .  count($missingfiles) . "\n\n";

echo "\nERRORS: -\n";
echo implode("\n",$errors) . "\n\n";

