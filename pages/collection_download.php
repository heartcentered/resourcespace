<?php
ini_set('zlib.output_compression','off'); // disable PHP output compression since it breaks collection downloading
include "../include/db.php";
include_once "../include/general.php";
include_once "../include/collections_functions.php";
# External access support (authenticate only if no key provided, or if invalid access key provided)
$k=getvalescaped("k","");if (($k=="") || (!check_access_key_collection(getvalescaped("collection","",true),$k))) {include "../include/authenticate.php";}
include "../include/search_functions.php";
include "../include/resource_functions.php";
include_once '../include/csv_export_functions.php';
include_once '../include/pdf_functions.php';
ob_end_clean();
global $aws_s3;

$uniqid="";$id="";
$collection=getvalescaped("collection","",true);  if ($k!=""){$usercollection=$collection;}
$size=getvalescaped("size","");
$submitted=getvalescaped("submitted","");
$includetext=getvalescaped("text","false");
$useoriginal=getvalescaped("use_original","no");
$collectiondata=get_collection($collection);
$tardisabled=getvalescaped("tardownload","")=="off";
$include_csv_file = getval('include_csv_file', '');

$collection_download_tar=true;

// Has tar been disabled or is it not available
if($collection_download_tar_size==0 || $config_windows || $tardisabled)
    {
    $collection_download_tar=false;
    }
else
    {
    if(!$collection_download_tar_option)
        {
        // Set tar as default above certain collection size
        $results=do_search("!collection" . $collection,"","relevance","",-1,"",false,0,false,true,"");
        $disk_usage=$results[0]["total_disk_usage"];
        if($disk_usage >= $collection_download_tar_size*1024*1024)
            {
            $collection_download_tar_option=true;
            }
        }
    }

$settings_id=(isset($collection_download_settings) && count($collection_download_settings)>1)?getvalescaped("settings",""):0;
$uniqid=getval("id",uniqid("Col" . $collection));

$usage = getvalescaped('usage', '-1');
$usagecomment = getvalescaped('usagecomment', '');

// set the time limit to unlimited, default 300 is not sufficient here.
set_time_limit(0);

$archiver_fullpath = get_utility_path("archiver");

if (!isset($zipcommand) && !$use_zip_extension)
    {
    if (!$collection_download) {exit($lang["download-of-collections-not-enabled"]);}
    if ($archiver_fullpath==false) {exit($lang["archiver-utility-not-found"]);}
    if (!isset($collection_download_settings)) {exit($lang["collection_download_settings-not-defined"]);}
    else if (!is_array($collection_download_settings)) {exit($lang["collection_download_settings-not-an-array"]);}
    if (!isset($archiver_listfile_argument)) {exit($lang["listfile-argument-not-defined"]);}
    }

$archiver = $collection_download && ($archiver_fullpath!=false) && (isset($archiver_listfile_argument)) && (isset($collection_download_settings) ? is_array($collection_download_settings) : false);

# initiate text file
if (($zipped_collection_textfile==true)&&($includetext=="true"))
    {
    $text = i18n_get_collection_name($collectiondata) . "\r\n" .
    $lang["downloaded"] . " " . nicedate(date("Y-m-d H:i:s"), true, true) . "\r\n\r\n" .
    $lang["contents"] . ":\r\n\r\n";
    }

# get collection
$result=do_search("!collection" . $collection);

$modified_result=hook("modifycollectiondownload");
if (is_array($modified_result)){$result=$modified_result;}

#this array will store all the available downloads.
$available_sizes = array();
$count_data_only_types = 0;

#build the available sizes array
for ($n=0;$n<count($result);$n++)
    {
    $ref=$result[$n]["ref"];
    # Load access level (0,1,2) for this resource
    $access=get_resource_access($result[$n]);

    # get all possible sizes for this resource
    $sizes=get_all_image_sizes(false,$access>=1);

    #check availability of original file
    $p=get_resource_path($ref,true,"",false,$result[$n]["file_extension"]);
    if (file_exists($p) && (($access==0) || ($access==1 && $restricted_full_download)) && resource_download_allowed($ref,'',$result[$n]['resource_type']))
        {
        $available_sizes['original'][]=$ref;
        }

    $pextension = get_extension($result[$n], $size);

    # check for the availability of each size and load it to the available_sizes array
    foreach ($sizes as $sizeinfo)
        {
        $size_id=$sizeinfo['id'];
        $size_extension = get_extension($result[$n], $size_id);
        $p=get_resource_path($ref,true,$size_id,false,$size_extension);

        if (resource_download_allowed($ref,$size_id,$result[$n]['resource_type']))
            {
            if (hook('size_is_available', '', array($result[$n], $p, $size_id)) || file_exists($p))
                $available_sizes[$size_id][]=$ref;
            }
        }

    if(in_array($result[$n]['resource_type'], $data_only_resource_types))
        {
        $count_data_only_types++;
        }
    }

#print_r($available_sizes);
if(0 == count($available_sizes) && 0 === $count_data_only_types)
    {
    ?>
    <script type="text/javascript">
        alert('<?php echo $lang["nodownloadcollection"];?>');
        history.go(-1);
        </script>
    <?php
        exit();
    }

$used_resources=array();
$subbed_original_resources = array();
if ($submitted != "")
    {
    if($exiftool_write && !$force_exiftool_write_metadata && !$collection_download_tar)
        {
        $exiftool_write_option = false;
        if('yes' == getvalescaped('write_metadata_on_download', ''))
            {
            $exiftool_write_option = true;
            }
        }

    # Estimate the total volume of files to zip
    $totalsize=0;
    for ($n=0;$n<count($result);$n++)
        {
        $ref = $result[$n]['ref'];
        $usesize = ($size == 'original') ? "" : $usesize=$size;
        $use_watermark=check_use_watermark();

        # Find file to use
        $f=get_resource_path($ref,true,$usesize,false,$pextension,-1,1,$use_watermark);
        if (!file_exists($f) && !$aws_s3)
            {
            # Selected size does not exist, use original file.
            $f=get_resource_path($ref,true,'',false,$result[$n]['file_extension'],-1,1,$use_watermark);
            }
        if (file_exists($f) && !$aws_s3)
            {
            $totalsize+=filesize_unlimited($f);
            debug("COLLECTION_DOWNLOAD/ID: " . $ref . " Filesize = " . filesize_unlimited($f));
            }
        else // Use fize size value in existing resource table for AWS S3 stored files.
            {
            $ref_data = get_resource_data($ref);
            $totalsize+=$ref_data['file_size'];
            debug("COLLECTION_DOWNLOAD/S3 ID: " . $ref . " Filesize = " . $ref_data['file_size']);
            }
        }
    if ($totalsize>$collection_download_max_size  && !$collection_download_tar)
        {
        ?>
        <script>
        alert("<?php echo $lang["collection_download_too_large"] ?>");
        history.go(-1);
        </script>
        <?php
        exit();
        }

    $id=getvalescaped("id","");
    if(!ctype_alnum($id)){exit($lang["error"]);}
    // Get a temporary directory for this download - $id should be unique
    $usertempdir=get_temp_dir(false,"rs_" . $userref . "_" . $id);

    // Clean up old user temp directories if they exist
    $tempdirbase=get_temp_dir(false);
    $tempfoldercontents = new DirectoryIterator($tempdirbase);
    $folderstodelete=array();
    $delindex=0;
    foreach($tempfoldercontents as $objectindex => $object)
        {
        $tmpfilename = $object->getFilename();
        if ($object->isDir())
            {
            if((substr($tmpfilename,0,strlen("rs_" . $userref . "_"))=="rs_" . $userref . "_"  || substr($tmpfilename,0,3)== "Col") && time()-$object->getMTime()>24*60*60)
               {
               debug ("Collection download - found old temp directory: " . $tmpfilename .  "  age (minutes): " . (time()-$object->getMTime())/60);
               // This directory belongs to the user and is older than a day, delete it
               $folderstodelete[]=$tempdirbase . DIRECTORY_SEPARATOR . $tmpfilename;
               }
            }
        elseif($purge_temp_folder_age!=0 && time()-$object->getMTime()>$purge_temp_folder_age*24*60*60)
            {
            unlink($tempdirbase . DIRECTORY_SEPARATOR . $tmpfilename);
            }

        }
    foreach ($folderstodelete as $foldertodelete)
        {
        debug ("Collection download - deleting directory " . $foldertodelete);
        @rcRmdir($foldertodelete);
        }
    $progress_file=$usertempdir . "/progress_file.txt";

    # Define the archive file.
    if(!$collection_download_tar)
        {
        collection_download_get_archive_file($archiver, $settings_id, $usertempdir, $collection, $size, $zip, $zipfile);
        }

    $path="";
    $deletion_array=array();
    // set up an array to store the filenames as they are found (to analyze dupes)
    $filenames=array();

    if(!$collection_download_tar && $offline_job_queue)
        {
        $collection_download_job_data = array(
            'k'                     => $k,
            'collection'            => $collection,
            'result'                => $result,
            'size'                  => $size,
            'exiftool_write_option' => $exiftool_write_option,
            'usertempdir'           => $usertempdir,
            'useoriginal'           => $useoriginal,
            'archiver'              => $archiver,
            'id'                    => $id,
            'includetext'           => $includetext,
            'progress_file'         => $progress_file,
            'count_data_only_types' => $count_data_only_types,
            'usage'                 => $usage,
            'usagecomment'          => $usagecomment,
            'available_sizes'       => $available_sizes,
            'settings_id'           => $settings_id,
            'include_csv_file'      => $include_csv_file,
        );
        job_queue_add('collection_download', $collection_download_job_data, '', '', $lang["oj-collection-download-success-text"], $lang["oj-collection-download-failure-text"]);

        $url = isset($collection) ? "{$baseurl}/?c={$collection}": '';
        message_add($userref, $lang['jq_notify_user_preparing_archive'], $url, 0);
        exit();
        }

    # Build a list of files to download
    for ($n=0;$n<count($result);$n++)
        {
        resource_type_config_override($result[$n]["resource_type"]);
        $copy=false;
        $ref=$result[$n]["ref"];
        # Load access level
        $access=get_resource_access($result[$n]);
        $use_watermark=check_use_watermark();

        # Only download resources with proper access level
        if ($access==0 || $access=1)
            {
            $pextension = get_extension($result[$n], $size);
            $usesize = ($size == 'original') ? "" : $usesize=$size;
            $p=get_resource_path($ref,true,$usesize,false,$pextension,-1,1,$use_watermark);

            # Determine whether target exists
            $subbed_original = false;
            $target_exists = file_exists($p);
            $replaced_file = false;

            $new_file = hook('replacedownloadfile', '', array($result[$n], $usesize, $pextension, $target_exists));
            if (!empty($new_file) && $p != $new_file)
                {
                $p = $new_file;
                $deletion_array[] = $p;
                $replaced_file = true;
                $target_exists = file_exists($p);
                }
            else if (!$target_exists && $useoriginal == 'yes'
                    && resource_download_allowed($ref,'',$result[$n]['resource_type']))
                {
                // this size doesn't exist, so we'll try using the original instead
                $p=get_resource_path($ref,true,'',false,$result[$n]['file_extension'],-1,1,$use_watermark);
                $pextension = $result[$n]['file_extension'];
                $subbed_original_resources[] = $ref;
                $subbed_original = true;
                $target_exists = file_exists($p);
                }

            # Process the file if it exists, and (if restricted access) that the user has access to the requested size
            if ((($target_exists && $access==0) ||
                ($target_exists && $access==1 &&
                    (image_size_restricted_access($size) || ($usesize='' && $restricted_full_download)))
                    ) && resource_download_allowed($ref,$usesize,$result[$n]['resource_type']))
                {
                $used_resources[] = $ref;
                $tmpfile = false;

                if($exiftool_write_option)
                    {
                    // If using AWS S3 storage, get original files from S3 bucket instead.
                    if($aws_s3)
                        {
                        include_once '../include/aws_sdk.php';
                        global $storagedir, $exiftool_remove_existing, $exiftool_write, $exiftool_no_process, $mysql_charset, $exiftool_write_omit_utf8_conversion, $aws_bucket;

                        // Fetch file extension and resource type.
                        $resource_data = get_resource_data($ref);
                        $extension = $resource_data["file_extension"];
                        $resource_type = $resource_data["resource_type"];

                        // Check if an attempt to write the metadata shall be performed.
                        $exiftool_fullpath = get_utility_path("exiftool");
                        if(false != $exiftool_fullpath && $exiftool_write && $exiftool_write_option && !in_array($extension, $exiftool_no_process))
                            {
                            // Trust ExifTool's list of writable formats.
                            $writable_formats = run_command("{$exiftool_fullpath} -listwf");
                            $writable_formats = str_replace("\n", "", $writable_formats);
                            $writable_formats_array = explode(" ", $writable_formats);
                            if(!in_array(strtoupper($extension), $writable_formats_array))
                                {
                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA: No ExifTool writable file formats.");
                                exit(); // No writable file formats.
                                }
                            else
                                {
                                // Determine tmp directory.
                                $tmp_dir = get_temp_dir(false, $uniqid);
                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA tmp folder: " . $tmp_dir);

                                // Determine tmp filename to save as.
                                $file_path_info = pathinfo($p);
                                $filename = md5(mt_rand()) . "_{$file_path_info['basename']}";
                                $tmpfile = "{$tmp_dir}/{$filename}";
                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA tmpfile: " . $tmpfile);

                                // Strip $storagedir and trailing slash from path for original file in AWS S3 bucket.
                                $p = str_replace($storagedir . DIRECTORY_SEPARATOR, "", $p);

                                // Check AWS S3 file path for a leading slash and if present, delete it.
                                $path_check = substr($p, 0, 1);
                                if($path_check == DIRECTORY_SEPARATOR)
                                    {
                                    $p = substr($p,1); // Strip leading slash in path for AWS S3.
                                    }
                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA Download Path: " . $p);

                                // Check if file exists in the specified AWS S3 bucket.
                                try {
                                    $s3result = $s3Client2->doesObjectExist($aws_bucket, $p);
                                    if($s3result == 1)
                                        {
                                        $s3result = "Ok";
                                        }
                                    debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA doesObjectExist: " . $s3result);
                                    }
                                catch (Aws\S3\Exception\S3Exception $e)
                                    {
                                    debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA doesObjectExist Error: " . $e->getMessage());
                                    }

                                // Only proceed if file exists in the specified AWS S3 bucket.
                                if($s3result == "Ok")
                                    {
                                    // Download file from AWS S3 bucket to the tmp directory.
                                    try {
                                        $s3result2 = $s3Client->getObject([
                                            'Bucket' => $aws_bucket,
                                            'Key' => $p,
                                            'SaveAs' => $tmpfile
                                        ]);
                                        }
                                    catch (Aws\S3\Exception\S3Exception $e) // Error check.
                                        {
                                        debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA S3 Download Error: " . $e->getMessage());
                                        }

                                    // Check for successful file download from AWS S3 bucket to the tmp location.
                                    if(file_exists($tmpfile) && is_readable($tmpfile))
                                        {
                                        debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA S3 File Downloaded To: " . $tmpfile);
                                        }
                                    else
                                        {
                                        debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA S3 Download Failure");
                                        }

                                    // Add a call to ExifTool and some generic arguments to the command string.
                                    $command = $exiftool_fullpath . " -m -overwrite_original -E ";
                                    if($exiftool_remove_existing)
                                        {
                                        $command = stripMetadata(null) . ' ';
                                        }

                                    // Returns an array of ExifTool fields for the particular resource type, which are basically fields with an 'exiftool field' set.
                                    $metadata_all = get_resource_field_data($ref, false,true,-1,getval("k","")!=""); // Using get_resource_field_data and honor field permissions.

                                    $write_to = array();
                                    foreach($metadata_all as $metadata_item)
                                        {
                                        if(trim($metadata_item["exiftool_field"]) != "")
                                            {
                                            $write_to[] = $metadata_item;
                                            }
                                        }

                                        $writtenfields = array(); // Check if writing to an embedded field from more than one RS field where subsequent values need to be appended, not replaced.

                                        for($i = 0; $i<count($write_to); $i++) // Loop through all the found fields.
                                            {
                                            $fieldtype = $write_to[$i]['type'];
                                            $writevalue = $write_to[$i]['value'];

                                            // Formatting and cleaning of the value to be written depending on the RS field type.
                                            switch ($fieldtype)
                                                {
                                                case 2:
                                                case 3:
                                                case 9:
                                                case 12: // Check box list, dropdown, radio buttons, or dynamic keyword list: remove initial comma if present.
                                                    if (substr($writevalue, 0, 1) == ",")
                                                        {
                                                        $writevalue = substr($writevalue, 1);
                                                        }
                                                    break;
                                                case 4:
                                                case 6:
                                                case 10: // Date/Expiry Date: write datetype fields in ExifTool preferred format.
                                                    if($writevalue != '')
                                                        {
                                                        $writevalue_to_time = strtotime($writevalue);
                                                        if($writevalue_to_time != '')
                                                            {
                                                            $writevalue = date("Y:m:d H:i:sP", strtotime($writevalue));
                                                            }
                                                        }
                                                    break; // Other types, already set.
                                                }
                                            $filtervalue = hook("additionalmetadatafilter", "", array($write_to[$i]["exiftool_field"], $writevalue));
                                            if ($filtervalue) $writevalue = $filtervalue;

                                            # Add the tag name(s) and the value to the command string.
                                            $group_tags = explode(",", $write_to[$i]['exiftool_field']); # Each 'exiftool field' may contain more than one tag.
                                            foreach ($group_tags as $group_tag)
                                                {
                                                $group_tag = strtolower($group_tag); // IPTC:Keywords -> iptc:keywords
                                                if (strpos($group_tag,":") === false)
                                                    { // subject -> subject
                                                    $tag = $group_tag;
                                                    }
                                                else // iptc:keywords -> keywords
                                                    {
                                                    $tag = substr($group_tag, strpos($group_tag,":")+1);
                                                    }

                                                $exifappend = false; // Need to replace values by default.
                                                if(isset($writtenfields[$group_tag]))
                                                    {
                                                    // This embedded field is already being updated, we need to append values from this field.
                                                    $exifappend = true;
                                                    debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA: More than one field mapped to the tag '" . $group_tag . "'. Enabling append mode for this tag.");
                                                    }

                                                switch ($tag)
                                                    {
                                                    case "filesize": // Do nothing, no point to try to write the filesize.
                                                        break;
                                                    case "filename": // Do nothing, no point to try to write the filename either as RS controls this.
                                                        break;
                                                    case "directory": // Do nothing, we do not want metadata to control this.
                                                        break;
                                                    case "keywords": // Keywords shall be written one at a time and not all together.
                                                        if(!isset($writtenfields["keywords"]))
                                                            {
                                                            $writtenfields["keywords"] = "";
                                                            }
                                                        $keywords = explode(",", $writevalue); // "keyword1,keyword2, keyword3" (with or without spaces).
                                                        if (implode("", $keywords) != "")
                                                            {
                                                            # Only write non-empty keywords, may be more than one field mapped to keywords so we do not want to overwrite with blank.
                                                            foreach ($keywords as $keyword)
                                                                {
                                                                $keyword = trim($keyword);
                                                                if ($keyword != "")
                                                                    {
                                                                    debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA: Writing keyword: " . $keyword);
                                                                    $writtenfields[$group_tag].="," . $keyword;

                                                                    # Convert the data to UTF-8 if not already.
                                                                    if (!$exiftool_write_omit_utf8_conversion && (!isset($mysql_charset) || (isset($mysql_charset) && strtolower($mysql_charset)!="utf8")))
                                                                        {
                                                                        $keyword = mb_convert_encoding($keyword, mb_detect_encoding($keyword), 'UTF-8');
                                                                        }
                                                                    $command.= escapeshellarg("-" . $group_tag . "-=" . htmlentities($keyword, ENT_QUOTES, "UTF-8")) . " "; // In case value is already embedded, need to manually remove it to prevent duplication
                                                                    $command.= escapeshellarg("-" . $group_tag . "+=" . htmlentities($keyword, ENT_QUOTES, "UTF-8")) . " ";
                                                                    }
                                                                }
                                                            }
                                                        break;
                                                    default:
                                                        if($exifappend && ($writevalue == "" || ($writevalue != "" && strpos($writtenfields[$group_tag],$writevalue) !== false)))
                                                            {
                                                            // The new value is blank or already included in what is being written; skip to next group tag.
                                                            continue;
                                                            }

                                                        $writtenfields[$group_tag] = $writevalue;
                                                        debug ("COLLECTION_DOWNLOAD/S3 WRITE_METADATA: Updating Tag: " . $group_tag);
                                                        // Write as is, convert the data to UTF-8 if not already.

                                                        global $strip_rich_field_tags;
                                                        if (!$exiftool_write_omit_utf8_conversion && (!isset($mysql_charset) || (isset($mysql_charset) && strtolower($mysql_charset)!="utf8")))
                                                            {
                                                            $writevalue = mb_convert_encoding($writevalue, mb_detect_encoding($writevalue), 'UTF-8');
                                                            }
                                                            if ($strip_rich_field_tags)
                                                                {
                                                            $command.= escapeshellarg("-" . $group_tag . "=" . trim(strip_tags($writevalue))) . " ";
                                                                }
                                                            else
                                                                {
                                                                $command.= escapeshellarg("-" . $group_tag . "=" . htmlentities($writevalue, ENT_QUOTES, "UTF-8")) . " ";
                                                                }
                                                    }
                                                }
                                            }

                                                // Add the filename to the command string and execute.
                                                $command.= " " . escapeshellarg($tmpfile);
                                                $output = run_command($command);
                                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA Complete: " . $tmpfile);
                                                }
                                            else
                                                {
                                                debug("COLLECTION_DOWNLOAD/S3 WRITE_METADATA: Fail " . $p);
                                                }
                                }
                            }
                        }
                    else
                        {
                        $tmpfile = write_metadata($p, $ref, $id); // Copies file from normal filestore.
                        }

                    if($tmpfile !== false && file_exists($tmpfile))
                        {
                        $p = $tmpfile; // File already in tmp, just rename it.
                        }
                    else if (!$replaced_file)
                        {
                        $copy = true; // Copy the file from the filestore rather than renaming.
                        }
                    }

                # If the tmpfile is made, from here on we are working with that.

                # If using original filenames when downloading, copy the file to new location so the name is included.
                $filename = '';
                if ($original_filenames_when_downloading)
                    {
                    # Retrieve the original file name
                    $filename = get_data_by_field($ref,$filename_field);
                    collection_download_use_original_filenames_when_downloading($filename, $ref, $collection_download_tar, $filenames);
                    }

                if (hook("downloadfilenamealt")) $filename = hook("downloadfilenamealt");

                collection_download_process_text_file($ref, $collection, $filename);

                hook('modifydownloadfile');

                $path.=$p . "\r\n";

                if($collection_download_tar)
                    {
                    $ln_link_name = $usertempdir . DIRECTORY_SEPARATOR . $filename;

                    /*
                    There is unexpected behaviour when a folder contains more than 70,000 symbolic links/ files in it.
                    By splitting result set in batches of 1000, this should address that problem. Up to 10,000 resources
                    in a collection proved to work ok and relatively faster when not splitting in subfolders.
                    */
                    if(count($result) > 10000)
                        {
                        // Generate folder name
                        $low_limit      = (floor($ref / 1000) * 1000) + 1;
                        $high_limit     = (ceil($ref / 1000) * 1000);
                        $symlink_folder = "{$usertempdir}" . DIRECTORY_SEPARATOR . "RS_{$low_limit}_to_{$high_limit}";

                        if(!is_dir($symlink_folder))
                            {
                            mkdir($symlink_folder, 0777, true);
                            }

                        $ln_link_name = $symlink_folder . DIRECTORY_SEPARATOR . $filename;
                        }

                    // Add link to file for use by tar to prevent full paths being included.
                    debug("collection_download adding symlink: {$p} - {$ln_link_name}");
                    @symlink($p, $ln_link_name);
                    }
                elseif ($use_zip_extension)
                    {
                    $zip->addFile($p,$filename);
                    update_zip_progress_file("file ".$zip->numFiles);
                    }
                else
                    {
                    update_zip_progress_file("file ".$n);
                    }

                collection_download_log_resource_ready($tmpfile, $deletion_array, $ref);
                }
            }

        }
    // Collection contains data_only resource types
    if(0 < $count_data_only_types)
        {
        collection_download_process_data_only_types($result, $id, $collection_download_tar, $usertempdir, $zip, $path, $deletion_array);
        }
    else if('' == $path)
        {
        exit($lang['nothing_to_download']);
        }

    collection_download_process_summary_notes(
        $result,
        $available_sizes,
        $text,
        $subbed_original_resources,
        $used_resources,
        $id,
        $collection,
        $collectiondata,
        $collection_download_tar,
        $usertempdir,
        $filename,
        $path,
        $deletion_array,
        $size,
        $zip);

    if($include_csv_file == 'yes')
        {
        collection_download_process_csv_metadata_file(
            $result,
            $id,
            $collection,
            $collection_download_tar,
            $use_zip_extension,
            $zip,
            $path,
            $deletion_array);
        }

    collection_download_process_command_to_file($use_zip_extension, $collection_download_tar, $id, $collection, $size, $path);

    if($collection_download_tar)
        {$suffix = '.tar';}
    elseif ($archiver)
        $suffix = '.' . $collection_download_settings[$settings_id]['extension'];
    else
        $suffix = '.zip';

    collection_download_process_collection_download_name($filename, $collection, $size, $suffix, $collectiondata);

    collection_download_process_archive_command($collection_download_tar, $zip, $filename, $usertempdir, $archiver, $settings_id, $zipfile);

    collection_download_clean_temp_files($deletion_array);

    # Get the file size of the archive.
    $filesize = @filesize_unlimited($zipfile);

    header("Content-Disposition: attachment; filename=" . $filename);
    if ($archiver) {header("Content-Type: " . $collection_download_settings[$settings_id]["mime"]);}
    else {
    header("Content-Type: application/zip");}
    if ($use_zip_extension){header("Content-Transfer-Encoding: binary");}
    header("Content-Length: " . $filesize);

    ignore_user_abort(true); // collection download has a problem with leaving junk files when this script is aborted client side. This seems to fix that by letting the process run its course.
    set_time_limit(0);

    if (!hook("replacefileoutput"))
        {
        # New method
        $sent = 0;
        $handle = fopen($zipfile, "r");

        // Now we need to loop through the file and echo out chunks of file data
        while($sent < $filesize)
            {
            echo fread($handle, $download_chunk_size);
            $sent += $download_chunk_size;
            }
        }

    # Remove archive.
    //unlink($zipfile);
    //unlink($progress_file);
    if ($use_zip_extension)
        {
        rmdir(get_temp_dir(false,$id));
        collection_log($collection,"Z","","-".$size);
        }
    hook('beforedownloadcollectionexit');
    exit();
    }
include "../include/header.php";

?>
<div class="BasicsBox">
<?php if($k!=""){
    ?><p><a href="<?php echo $baseurl_short?>pages/search.php?search=!collection<?php echo $collection?>&k=<?php echo $k?>" onclick="return CentralSpaceLoad(this,true);">< <?php echo $lang['back']?></a></p><?php
}?>

<h1><?php echo $lang["downloadzip"]?></h1>
<?php
$intro=text("introtext");
if ($intro!="") { ?><p><?php echo $intro ?></p><?php }
?>
<script>

function ajax_download(download_offline)
    {
    var ifrm = document.getElementById('downloadiframe');
    ifrm.src = "<?php echo $baseurl_short?>pages/collection_download.php?submitted=true&"+jQuery('#myform').serialize();

    if(download_offline)
        {
        document.getElementById('downloadbuttondiv').style.display='none';
        return false;
        }

    document.getElementById('downloadbuttondiv').style.display='none';
    document.getElementById('progress').innerHTML='<br /><br /><?php echo $lang["collectiondownloadinprogress"];?>';
    document.getElementById('progress3').style.display='none';
    document.getElementById('progressdiv').style.display='block';

    // Disable form controls -- this needs to happen after serializing the form or else they are ignored
    jQuery('#downloadsize').prop('disabled', true);
    jQuery('#use_original').prop('disabled', true);
    jQuery('#text').prop('disabled', true);
    jQuery('#archivesettings').prop('disabled', true);


    progress= jQuery("progress3").PeriodicalUpdater("<?php echo $baseurl_short?>pages/ajax/collection_download_progress.php?id=<?php echo urlencode($uniqid) ?>&user=<?php echo urlencode($userref) ?>", {
        method: 'post',          // method; get or post
        data: '',               //  e.g. {name: "John", greeting: "hello"}
        minTimeout: 500,       // starting value for the timeout in milliseconds
        maxTimeout: 2000,       // maximum length of time between requests
        multiplier: 1.5,          // the amount to expand the timeout by if the response hasn't changed (up to maxTimeout)
        type: 'text'           // response type - text, xml, json, etc.
    }, function(remoteData, success, xhr, handle) {
         if (remoteData.indexOf("file")!=-1){
                    var numfiles=remoteData.replace("file ","");
                    if (numfiles==1){
                        var message=numfiles+' <?php echo $lang['fileaddedtozip']?>';
                    } else {
                        var message=numfiles+' <?php echo $lang['filesaddedtozip']?>';
                    }
                    var status=(numfiles/<?php echo count($result)?>*100)+"%";
                    console.log(status);
                    document.getElementById('progress2').innerHTML=message;
                }
                else if (remoteData=="complete"){
                   document.getElementById('progress2').innerHTML="<?php echo $lang['zipcomplete']?>";
                   document.getElementById('progress').style.display="none";
                   progress.stop();
                }
                else {
                    // fix zip message or allow any
                    console.log(remoteData);
                    document.getElementById('progress2').innerHTML=remoteData.replace("zipping","<?php echo $lang['zipping']?>");
                }

    });

}

</script>

    <form id='myform' action="<?php echo $baseurl_short?>pages/collection_download.php?id=<?php echo urlencode($uniqid) ?>&submitted=true" method=post>
        <?php generateFormToken("myform"); ?>
<input type=hidden name="collection" value="<?php echo htmlspecialchars($collection) ?>">
<input type=hidden name="usage" value="<?php echo htmlspecialchars($usage); ?>">
<input type=hidden name="usagecomment" value="<?php echo htmlspecialchars($usagecomment); ?>">
<input type=hidden name="k" value="<?php echo htmlspecialchars($k) ?>">

    <input type=hidden name="id" value="<?php echo htmlspecialchars($uniqid) ?>">
    <iframe id="downloadiframe" <?php if (!$debug_direct_download){?>style="display:none;"<?php } ?>></iframe>

<?php
hook("collectiondownloadmessage");

if (!hook('replacesizeoptions'))
    {
    if($count_data_only_types !== count($result))
        {
        ?>
        <div class="Question">
        <label for="downloadsize"><?php echo $lang["downloadsize"]?></label>
        <div class="tickset">
    <?php
    $maxaccess=collection_max_access($collection);
    $sizes=get_all_image_sizes(false,$maxaccess>=1);

    $available_sizes=array_reverse($available_sizes,true);

    # analyze available sizes and present options
?><select name="size" class="stdwidth" id="downloadsize"<?php if (!empty($submitted)) echo ' disabled="disabled"' ?>><?php

function display_size_option($sizeID, $sizeName, $fordropdown=true)
    {
    global $available_sizes, $lang, $result;
    if(!hook('replace_display_size_option','',array($sizeID, $sizeName, $fordropdown))){
        if ($fordropdown)
            {
            ?><option value="<?php echo htmlspecialchars($sizeID) ?>"><?php
            echo $sizeName;
            }
        if(isset($available_sizes[$sizeID]))
            {
            $availableCount = count($available_sizes[$sizeID]);
            }
        else
            {
            $availableCount=0;
            }
        $resultCount = count($result);
        if ($availableCount != $resultCount)
            {
            echo " (" . $availableCount . " " . $lang["of"] . " " . $resultCount . " ";
            switch ($availableCount)
                {
                case 0:
                    echo $lang["are_available-0"];
                    break;
                case 1:
                    echo $lang["are_available-1"];
                    break;
                default:
                    echo $lang["are_available-2"];
                    break;
                }
            echo ")";
            }
             if ($fordropdown)
                {
            ?></option><?php
            }
        }
    }

if (array_key_exists('original',$available_sizes))
    display_size_option('original', $lang['original'], true);

foreach ($available_sizes as $key=>$value)
    {
    foreach($sizes as $size)
        {
        if ($size['id']==$key)
            {
            display_size_option($key, $size['name'], true);
            break;
            }
        }
    }
?></select>

<div class="clearerleft"> </div></div>
<div class="clearerleft"> </div></div><?php
       }
    }
if (!hook('replaceuseoriginal'))
    {
    if($count_data_only_types !== count($result))
        {
        ?>
        <div class="Question">
        <label for="use_original"><?php echo $lang['use_original_if_size']; ?> <br /><?php

        display_size_option('original', $lang['original'], false);
        ?></label><input type=checkbox id="use_original" name="use_original" value="yes" >
        <div class="clearerleft"> </div></div>
        <?php
       }
    }

if ($zipped_collection_textfile=="true") { ?>
<div class="Question">
<label for="text"><?php echo $lang["zippedcollectiontextfile"]?></label>
<select name="text" class="shrtwidth" id="text"<?php if (!empty($submitted)) echo ' disabled="disabled"' ?>>
<?php if($zipped_collection_textfile_default_no){
    ?><option value="false"><?php echo $lang["no"]?></option>
    <option value="true"><?php echo $lang["yes"]?></option><?php
}
else{
    ?><option value="true"><?php echo $lang["yes"]?></option>
    <option value="false"><?php echo $lang["no"]?></option><?php
}
?>
</select>
<div class="clearerleft"></div>
</div>

<?php
}

# Archiver settings
if ($archiver && count($collection_download_settings)>1)
    { ?>
    <div class="Question" id="archivesettings_question" <?php if($collection_download_tar){echo "style=\"display:none\"";}?>>
    <label for="archivesettings"><?php echo $lang["archivesettings"]?></label>
    <div class="tickset">
    <select name="settings" class="stdwidth" id="archivesettings"<?php if (!empty($submitted)) echo ' disabled="disabled"' ?>><?php
    foreach ($collection_download_settings as $key=>$value)
        { ?>
        <option value="<?php echo htmlspecialchars($key) ?>"><?php echo lang_or_i18n_get_translated($value["name"],"archive-") ?></option><?php
        } ?>
    </select>
    <div class="clearerleft"></div></div><br />
    </div><?php
    }   ?>

<!-- Add CSV file with the metadata of all the resources found in this colleciton -->
<div class="Question">
    <label for="include_csv_file"><?php echo $lang['csvAddMetadataCSVToArchive']; ?></label>
    <input type="checkbox" id="include_csv_file" name="include_csv_file" value="yes">
    <div class="clearerleft"></div>
</div>

<?php
if($exiftool_write && !$force_exiftool_write_metadata && !$aws_s3)
    {
    ?>
    <!-- Let user say (if allowed - ie. not enforced by system admin) whether metadata should be written to the file or not -->
    <div class="Question" id="exiftool_question" <?php if($collection_download_tar_option){echo "style=\"display:none;\"";} ?>>
        <label for="write_metadata_on_download"><?php echo $lang['collection_download__write_metadata_on_download_label']; ?></label>
        <input type="checkbox" id="write_metadata_on_download" name="write_metadata_on_download" value="yes" >
        <div class="clearerleft"></div>
    </div>
    <?php
    }
?>

<div class="Question"  <?php if(!$collection_download_tar){echo "style=\"display:none;\"";} ?>>
    <label for="tardownload"><?php echo $lang["collection_download_format"]?></label>
    <div class="tickset">
    <select name="tardownload" class="stdwidth" id="tardownload" onChange="if(jQuery(this).val()=='off'){ajax_on=true;jQuery('#exiftool_question').slideDown();jQuery('#archivesettings_question').slideDown();}else{ajax_on=false;jQuery('#exiftool_question').slideUp();jQuery('#archivesettings_question').slideUp();}">
           <option value="off"><?php echo $lang["collection_download_no_tar"]; ?></option>
           <option value="on" <?php if($collection_download_tar_option) {echo "selected";} ?> ><?php echo$lang["collection_download_use_tar"]; ?></option>
    </select>

    <div class="clearerleft"></div></div><br />
    <div class="clearerleft"></div>
    <label for="tarinfo"></label>
    <div class="Fixed"><?php echo $lang["collection_download_tar_info"]  . "<br />" . $lang["collection_download_tar_applink"]?></div>

    <div class="clearerleft"></div>
</div>

<div class="QuestionSubmit" id="downloadbuttondiv">
    <label for="download"> </label>
    <script>var ajax_on=<?php echo ($collection_download_tar)?"true":"false"; ?>;</script>
    <input type="submit"
           onclick="
            if(ajax_on)
                {
                ajax_download(<?php echo ($offline_job_queue ? 'true' : 'false'); ?>);
                return false;
                }
           "
           value="&nbsp;&nbsp;<?php echo $lang["action-download"]?>&nbsp;&nbsp;" />

    <div class="clearerleft"> </div>
</div>

<div id="progress"></div>

<div class="Question" id="progressdiv" style="display:none;border-top:none;">
<label><?php echo $lang['progress']?></label>
<div class="Fixed" id="progress3" ></div>
<div class="Fixed" id="progress2" ></div>

<div class="clearerleft"></div></div>

</form>

</div>
<?php
include "../include/footer.php";
?>
