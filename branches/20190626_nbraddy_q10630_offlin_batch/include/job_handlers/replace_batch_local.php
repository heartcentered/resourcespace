<?php
/*
Job handler to fetch files from a local path  for batch replacement of resources

Requires the following job data:-
$job_data['import_path'] - Folder to scan for files to import
*/

global $lang, $baseurl, $offline_job_delete_completed, $fstemplate_alt_threshold;
global $notify_on_resource_change_days;

$local_path     = $job_data['import_path'];
$minref         = $job_data['resource_min'];
$maxref         = $job_data['resource_max'];
$collectionid   = $job_data['replace_col'];
$filename_field = $job_data['filename_field'];
$noexif         = ($job_data['no_exif'] == "yes") ? true : false ;

if(!file_exists($local_path))
    {
    job_queue_update($jobref , $job_data , STATUS_ERROR);
    }

$logtext = "";

include_once __DIR__ . '/../search_functions.php';
include_once __DIR__ . '/../resource_functions.php';
include_once __DIR__ . '/../collections_functions.php';
include_once __DIR__ . '/../image_processing.php';

if (!isset($collectionid) || $collectionid == 0)
    {
    $conditions = array();
    $minref = max((int)($minref),$fstemplate_alt_threshold);
    $firstref = max($fstemplate_alt_threshold, $minref);
    
    $replace_resources = sql_array("SELECT ref value FROM resource WHERE ref > '" . $minref . "' " . (($maxref > 0) ? " AND ref <= '" . (int)$maxref . "'" : "") . " ORDER BY ref ASC",0);
    $logtext .= " - > Replacing files for resource IDs. Min ID: " . $minref  . (($maxref > 0) ? " Max ID: " . $maxref : "");
    }
else
    {
    $replace_resources = get_collection_resources($collectionid);
    $logtext .= " - > Replacing resources within collection " . $collectionid . " only";
    }

    
$replaced = array();
$errors = array();

$foldercontents = new DirectoryIterator($local_path);
foreach($foldercontents as $objectindex => $object)
    {
    if($object->isDot() || $object->isDir() || !($object->isReadable()))
        {
        continue;
        }
        
    $filename   = $object->getFilename();
    $extension  = $object->getExtension();
        
    // get resource by $filename_field
    if($filename_field != 0)
        {
        $target_resources = sql_array("select resource value from resource_data where resource_type_field='$filename_field' and value='" . escape_check($filename) . "'","");
        
        $valid_resources=array_values(array_intersect($target_resources,$replace_resources));        
        $full_path = $local_path . DIRECTORY_SEPARATOR . $filename;
        
        if(count($valid_resources) == 1)
            {
            // A single resource has been found with the same filename
            $rsfile = get_resource_path($valid_resources[0],true,'',true,$extension);
            $success = @copy($full_path,$rsfile);
            if($success)
                {
                create_previews($valid_resources[0], false, $extension);
                // Check to see if we need to notify users of this change							
                if($notify_on_resource_change_days != 0)
                    {				
                    notify_resource_change($valid_resources[0]);
                    }
                $replaced[] = $valid_resources[0];
                unlink($full_path);
                }
            else
                {
                $errors[] = "Failed to copy file from : " .  $filepath; 
                }
            }
        elseif(count($valid_resources)==0)
            {
            // No resource found with the same filename
            $errors[] = "Failed to find matching file for: " .  $filename;
            continue;
            }
        else
            {
            // Multiple resources found with the same filename
            if ($replace_batch_existing)
                {
                foreach ($valid_resources as $valid_resource)
                    {
                    $rsfile = get_resource_path($valid_resource,true,'',true,$extension);
                    $success = @copy($full_path,$rsfile);
                    if($success)
                        {
                        create_previews($valid_resource, false, $extension);
                        // Check to see if we need to notify users of this change							
                        if($notify_on_resource_change_days != 0)
                            {				
                            notify_resource_change($valid_resource);
                            }
                        $replaced[] = $valid_resource;
                        }
                    else
                        {
                        $errors[] = "Failed to copy file from : " .  $filepath;
                        continue;
                        }
                    }
                unlink($full_path);
                }
            else
                {
                // Multiple resources found with the same filename
                $resourcelist=implode(",",$valid_resources);
                $errors[] = "ERROR - multiple resources found with filename " . $filename . ". Resource IDs : " . $resourcelist;
                }
            }
        }
    else
        {
        # Overwrite an existing resource using the number from the filename.
        $targetresource = $object->getBasename(); 
        if((string)(int)($targetresource) == (string)$targetresource && !resource_file_readonly($targetresource))
            {
            $rsfile = get_resource_path($targetresource,true,'',true,$extension);
            $success = @copy($full_path,$rsfile);
            if($success)
                {
                create_previews($valid_resource, false, $extension);
                // Check to see if we need to notify users of this change							
                if($notify_on_resource_change_days != 0)
                    {				
                    notify_resource_change($targetresource);
                    }
                $replaced[] = $targetresource;
                unlink($full_path);
                }
            else
                {
                $errors[] = "Failed to copy file from : " .  $filepath; 
                }
            }
        else
            {
            // No resource found with the same filename
            $errors[] = "ERROR - no ref matching filename: " . $filename;            
            }
        }
    }



$logtext .= "\n - > Replaced " . count($replaced) . " resource files: -\n";

if(count($replaced) > 0)
    {
    $logtext .= "\n - > Replaced resource files for IDs: -\n";
    $logtext .= " - > " . implode(",",$replaced) . "\n";
    }
    
if(count($errors) > 0)
    {
    $logtext .= "\n -> ERRORS: -\n";
    $logtext .= " - >  " . implode("\n",$errors) . "\n\n";
    }

echo $logtext;

