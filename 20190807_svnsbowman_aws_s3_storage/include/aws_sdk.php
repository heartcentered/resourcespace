<?php
// Amazon Web Services (AWS) PHP v3 SDK API Simple Storage Service (S3) and CloudWatch Clients Setup and Related Functions
// Last Updated 3/22/2020 by Steve D. Bowman

// Files are referred to as 'objects' in object-based storage systems and since they only store objects and have no traditional folder structure, the filename or 'key' includes the full filestore path to keep the same file structure in the filestore and AWS S3 storage.  The AWS credential 'key' and 'secret' should not be shared, as anyone with these values can access your data in AWS.

include_once 'db.php';
include_once 'general.php';

global $aws_region, $aws_key, $aws_secret, $storagedir;

// Load the AWS PHP v3 SDK and setup initial parameters.
$aws_path = str_replace("filestore", "", $storagedir);
require $aws_path . 'lib/aws_sdk_php_3.133.41/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\AwsException;
use Aws\S3\ObjectUploader;
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\CloudWatch\CloudWatchClient;

// Latest AWS S3 API version.
$aws_s3_version = '2006-03-01';

// Latest AWS CloudWatch API version.
$aws_cw_version = '2010-08-01';

// Create an AWS S3 connection client and catch errors.
try
    {
    $s3Client = new Aws\S3\S3Client([
        'version' => $aws_s3_version,
        'region' => $aws_region,
        'credentials' => [
            'key'    => $aws_key,
            'secret' => $aws_secret,
        ],
    ]);
    debug("AWS_SDK/S3 Client Setup: OK");
    }
catch(Aws\S3\Exception\S3Exception $e) // Error catch.
    {
    debug("AWS_SDK/S3 Client Error: " . $e->getMessage());
    }

// Create an AWS CloudWatch connection client and catch errors.
try
    {
    $cwClient = new Aws\CloudWatch\CloudWatchClient([
        'version' => $aws_cw_version,
        'region' => $aws_region,
        'credentials' => [
            'key'    => $aws_key,
            'secret' => $aws_secret,
        ],
    ]);
    debug("AWS_SDK/CloudWatch Client Setup: OK");
    }
catch  (Aws\Exception\AwsException $e) // Error catch.
    {
    debug("AWS_SDK/CloudWatch Client Error: " . $e->getMessage());
    }


// Get an AWS S3 bucket location by AWS region.
function aws_s3_bucket_location($aws_bucket)
    {
    global $s3Client, $lang;

    try
        {
        $result = $s3Client->getBucketLocation([
            'Bucket' => $aws_bucket,
        ]);
        $s3_result['location'] = $result['LocationConstraint'];
        $s3_result['status'] = $lang["status-ok"];
        }
    catch(Aws\S3\Exception\S3Exception $e)
        {
        $s3_result['status'] = $lang["status-fail"];
        debug("AWS_S3_BUCKET_LOCATION Error: " . $e->getMessage());
        }

    return $s3_result;
    }


// Check the accessibility of an AWS S3 bucket.
function aws_s3_bucket_check($aws_bucket)
    {
    global $s3Client, $lang;

    try
        {
        $s3_result = $s3Client->headBucket([
           'Bucket' => $aws_bucket,
        ]);
        $s3_result = $lang["status-ok"];
        }
    catch(Aws\S3\Exception\S3Exception $e)
        {
        $s3_result = $lang["status-fail"];
        debug("AWS_S3_BUCKET_CHECK Error: " . $e->getMessage());
        }

    return $s3_result;
    }


// Get the owner of an AWS S3 bucket.
function aws_s3_bucket_owner($aws_bucket)
    {
    global $s3Client, $lang;

    try
        {
        $result = $s3Client->getBucketAcl([
           'Bucket' => $aws_bucket,
        ]);
        $s3_result['name'] = $result['Owner']['DisplayName'];
        $s3_result['id'] = $result['Owner']['ID'];
        $s3_result['status'] = $lang["status-ok"];
        }
    catch(Aws\S3\Exception\S3Exception $e)
        {
        $s3_result['status'] = $lang["status-fail"];
        debug("AWS_S3_BUCKET_OWNER Error: " . $e->getMessage());
        }

    return $s3_result;
    }


// Convert the AWS S3 storage class code to a name.
function aws_s3_storage_class($aws_storage_class)
    {
    switch ($aws_storage_class)
        {
        case "STANDARD":
            $result['code'] = "StandardStorage";
            $result['name'] = "Standard Storage";
            break;
        case "INTELLIGENT_TIERING":
            $result['code'] = "IntelligentTiering";
            $result['name'] = "Intelligent Tiering";
            break;
        case "STANDARD_IA":
            $result['code'] = "StandardIAStorage";
            $result['name'] = "Standard, Infrequent Access Storage";
            break;
        case "ONEZONE_IA":
            $result['code'] = "OneZoneIAStorage";
            $result['name'] = "One Zone Infrequent Access Storage";
            break;
        case "REDUCED_REDUNDANCY":
            $result['code'] = "ReducedRedundancyStorage";
            $result['name'] = "Reduced Redundancy Storage";
            break;
        default:
            $result['code'] = "AllStorageTypes";
            $result['name'] = "All Storage Types";
        }

    return $result;
    }


// Get the AWS S3 bucket metrics using AWS CloudWatch.
function aws_s3_bucket_statistics($cw_metric, $cw_statistic, $cw_unit, $cw_storage = "")
    {
    global $cwClient, $lang, $aws_storage_class, $aws_bucket;
    
    // Setup input parameters.
    $result = aws_s3_storage_class($aws_storage_class);
    
    if($cw_storage == "")
        {
        $s3_storage_type = $result['code'];
        }
    else
        {
        $s3_storage_type = $cw_storage;
        }
    
    $dimensions = array(
        array('Name' => 'BucketName', 'Value' => $aws_bucket),
        array('Name' => 'StorageType', 'Value' => $s3_storage_type),
    );
    
    $cw_input = array(
        'Namespace'  => 'AWS/S3',
        'MetricName' => $cw_metric,
        'Dimensions' => $dimensions,
        'StartTime'  => strtotime('now' . '-1 days'),
        'EndTime'    => strtotime('now'),
        'Period'     => 3600,
        'Statistics' => $cw_statistic,
        'Unit'       => $cw_unit,
    );

    try
        {
        $cw_result = $cwClient->getMetricStatistics($cw_input);
        $cw_data = end($cw_result['Datapoints']);
        $cw_data['status'] = "";
        }
    catch(Aws\Exception\AwsException $e) // Error checking.
        {
        $cw_data['status'] = $lang["status-fail"];
        debug("AWS_S3_BUCKET_STATISTICS Error: " . $e->getMessage());
        }

    return $cw_data;
    }


// Create an AWS S3 object path from the normal filestore path.
function aws_s3_object_path($path)
    {
    global $storagedir;

    // Strip the $storagedir and leading slash from path to match AWS S3 bucket path.
    $s3_path = ltrim(str_replace($storagedir, "", $path), DIRECTORY_SEPARATOR);
    debug("AWS_S3_OBJECT_PATH: " . $s3_path);

    return $s3_path;
    }


// Check if an AWS S3 object exists.
function aws_s3_object_check($path)
    {
    global $s3Client, $aws_bucket;

    // Strip the $storagedir and leading slash from path to match AWS S3 bucket path.
    $s3_path = aws_s3_object_path($path);

    // Use the AWS SDK doesObjectExist to verify the object in the specified AWS S3 bucket.
    try
        {
        $s3_result = $s3Client->doesObjectExist($aws_bucket, $s3_path);
        debug("AWS_S3_OBJECT_CHECK: " . boolean_convert($s3_result, "ok") . ", " . $s3_path);
        }
    catch(Aws\S3\Exception\S3Exception $e) // Error check.
        {
        debug("AWS_S3_OBJECT_CHECK Error: " . $e->getAwsErrorMessage());
        return false;
        }

    return $s3_result;
    }


// Upload a local file to a specified AWS S3 bucket using the SDK ObjectUploader that uses putObject or the MultipartUploader as appropriate to the file.  Files uploaded to same path structure as in ../resourcespace/filestore/.. to a specified S3 bucket.
function aws_s3_object_uploader($fs_path, $s3_path = '', $fs_delete = false)
    {
    global $s3Client, $aws_bucket, $aws_storage_class, $lang;

    // Determine the AWS S3 upload file path parameters.
    if($s3_path == '')
        {
        $s3_path = aws_s3_object_path($fs_path);
        }

    // Setup the AWS S3 ObjectUploader.
    $s3_upload_type = "PO";
    $s3_out = false;
    $fs_stream = fopen($fs_path, 'rb');
    $s3_uploader = new ObjectUploader($s3Client, $aws_bucket, $s3_path, $fs_stream, 'private', [
        'StorageClass' => $aws_storage_class
    ]);
    debug("AWS_S3_OBJECT_UPLOADER Setup: " . boolean_convert($s3_uploader, "ok"));

    // Attempt to upload the local file to a specified AWS S3 bucket.
    do
        {
        try // Use SDK putObject.
            {
            $s3_result = $s3_uploader->upload();
            if($s3_result["@metadata"]["statusCode"] == '200' && $s3_upload_type == "PO")
                {
                debug("AWS_S3_OBJECT_UPLOADER putObject: " . $lang["status-ok"] . ", " . $s3_result["ObjectURL"]);
                $s3_out = true;
                }
            elseif($s3_result["@metadata"]["statusCode"] == '200' && $s3_upload_type == "MP")
                {
                debug("AWS_S3_OBJECT_UPLOADER Multipart: " . $lang["status-ok"] . ", " . $s3_result["ObjectURL"]);
                $s3_out = true;
                }
            }
        catch(MultipartUploadException $e) // Use SDK MultipartUploader.
            {
            rewind($fs_stream);
            $s3_uploader = new MultipartUploader($s3Client, $fs_stream, [
                'state' => $e->getState(),
                'before_upload' => function(\Aws\Command $command)
                    {
                    gc_collect_cycles(); // Run PHP garbage collector to free memory for large files.
                    }
            ]);
            $s3_upload_type = "MP";
            }
        }
    while(!isset($s3_result));

    // Delete original file and create placeholder file in the local filestore if a successful AWS S3 upload.
    if ($fs_delete)
        {
        aws_s3_file_placeholder($fs_path);
        }

    return $s3_out;
    }


// Delete a filestore original file and create a placeholder file in its place.
function aws_s3_file_placeholder($fs_path)
    {
    if(is_file($fs_path))
        {
        // Delete the filestore original file, as it is now in AWS S3 storage.
        $s3_result = unlink($fs_path);
        debug("AWS_S3_FILE_PLACEHOLDER Original File Delete: " . boolean_convert($s3_result, "ok") . ", " . $fs_path);

        // Create a zero-byte placeholder file in the filestore with the same path and name as the original file.
        $ph_path = fopen($fs_path, 'wb');
        $s3_result = fclose($ph_path);
        debug("AWS_S3_FILE_PLACEHOLDER Original File Placeholder: " . boolean_convert($s3_result, "ok"));
        }
    else // Error, filestore original file is missing.
        {
        debug("AWS_S3_FILE_PLACEHOLDER Error: Original File Missing");
        return false;
        }

    return $s3_result;
    }


// Determine a local filestore temp filename for a given file with a random temp name.
function aws_s3_file_tempname($path, $uniqid = "")
    {
    debug("AWS_S3_FILE_TEMPNAME Original Path: " . $path);
    $file_path_info = pathinfo($path);
    $filename = md5(mt_rand()) . "_{$file_path_info['basename']}";

    $tmp_dir = get_temp_dir(false, $uniqid);
    $s3_tmpfile = "{$tmp_dir}/{$filename}";
    debug("AWS_S3_FILE_TEMPNAME Local Temp Path: " . $s3_tmpfile);
    
    return $s3_tmpfile;
    }


// Copy an AWS S3 object to another path.
function aws_s3_object_copy($old_path, $new_path, $old_delete = false)
    {
    global $s3Client, $aws_bucket, $aws_storage_class;

    // Check if the file exists in the specified AWS S3 bucket and create paths.
    $s3_path = aws_s3_object_check($old_path);
    $s3_old = aws_s3_object_path($old_path);
    $s3_new = aws_s3_object_path($new_path);

    try
        {
        if(!$s3_path)
            {
            $s3_result = $s3Client->copyObject([
                'Bucket' => $aws_bucket,
                'StorageClass' => $aws_storage_class,
                'Key' => $s3_new,
                'CopySource' => $s3_old
            ]);
            debug("AWS_S3_OBJECT_COPY: " . boolean_convert($s3_result, "ok"));

            // Delete AWS S3 old path object.
            if($old_delete && $s3_result)
                {
                $s3_result = aws_s3_object_delete(false, $old_path);
                debug("AWS_S3_OBJECT_COPY Delete Old Object: " . boolean_convert($s3_result, "ok"));
                }
            }
        else // File does not exist in the specified AWS S3 bucket.
            {
            return false;
            }
        }
    catch(Aws\S3\Exception\S3Exception $e) // Error check.
        {
        debug("AWS_S3_OBJECT_COPY Error: " . $e->getMessage());
        return false;
        }

    return $s3_result;
    }


// Download an AWS S3 object in a specified bucket to a local file.
function aws_s3_object_download($path, $s3_tmpfile)
    {
    global $s3Client, $aws_bucket;

    // Check if the file exists in the specified AWS S3 bucket.
    $s3_path = aws_s3_object_check($path);

    // Download the original file from the AWS S3 bucket and save in the specified location.
    try
        {
        if(!$s3_path)
            {
            $s3_result = $s3Client->getObject([
                'Bucket' => $aws_bucket,
                'Key' => $s3_path,
                'SaveAs' => $s3_tmpfile
            ]);
            debug("AWS_S3_OBJECT_DOWNLOAD GetObject: " . boolean_convert($s3_result, "ok") . ", " . $s3_tmpfile);
            }
        else // File does not exist in the specified AWS S3 bucket.
            {
            return false;
            }
        }
    catch(Aws\S3\Exception\S3Exception $e) // Error check.
        {
        debug("AWS_S3_OBJECT_DOWNLOAD Error: " . $e->getMessage());
        return false;
        }

    // Check that downloaded file exists locally.
    if(file_exists($tmpfile) && is_readable($tmpfile))
        {
        debug("AWS_S3_OBJECT_DOWNLOAD File Ok: " . $tmpfile);
        return true;
        }
    else // AWS S3 file download failure.
        {
        debug("AWS_S3_OBJECT_DOWNLOAD Error: " . $path);
        return false;
        }

    return $s3_result;
    }


// Delete an AWS S3 object in a specified bucket, returns true on success.
function aws_s3_object_delete($ref, $fs_path = "")
    {
    global $s3Client, $aws_bucket;

    // Determine original file AWS SDK S3 parameters by $ref ID.
    if(!$ref && is_numeric($ref))
        {
        $fs_path1 = get_resource_path($ref, true, '', false);
        $s3_path = aws_s3_object_path($fs_path1);
        }
    elseif($fs_path != "") // Determine original file AWS SDK S3 parameters by filestore file path, such as for alternative files.
        {
        $s3_path = aws_s3_object_path($fs_path);
        }
    else // No function input parameters, unable to continue.
        {
        debug("AWS_S3_OBJECT_DELETE: FAIL, insufficient input parameters.");
        return false;
        }

    // Use the AWS SDK deleteObject to delete the file in the specified AWS S3 bucket.
    try
        {
        $s3_check = aws_s3_object_check($s3_path);
        if(!$s3_check)
            {
            $s3_result = $s3Client->deleteObject([
                'Bucket' => $aws_bucket,
                'Key' => $s3_path,
            ]);
            debug("AWS_S3_OBJECT_DELETE: " . boolean_convert($s3_result['DeleteMarker'], "yes") . ", " . $s3_path);
            }
        else // File does not exist in the specified AWS S3 bucket.
            {
            debug("AWS_S3_OBJECT_DELETE: " . boolean_convert($s3_check, "ok"));
            return false;
            }
        }
    catch(Aws\S3\Exception\S3Exception $e) // Error check.
        {
        debug("AWS_S3_OBJECT_DELETE Error: " . $e->getAwsErrorMessage());
        return false;
        }

    return $s3_result;
    }


// Cleanup filestore tmp folder by purging files, not subfolders, based on file age in minutes.
function filestore_temp_cleanup($min_age = 5)
    {
    global $aws_tmp_purge;

    $tmp_dir = get_temp_dir(false);
    $age = $aws_tmp_purge * 60;

    // Set the minimum file age to protect other operations.
    if($age < $min_age * 60)
        {
        $age = $min_age * 60;
        }    

    if(file_exists($tmp_dir))
        {
        $time_now = time();
        foreach(new FilesystemIterator($tmp_dir) as $file)
            {
            if(is_dir($file))
                {
                continue;
                }
            if(is_file($file) && $age == 0 || $time_now - $file->getCTime() >= $age)
                {
                $result = unlink($file->getRealPath());
                debug("FILESTORE_TEMP_CLEANUP: " . boolean_convert($result, "ok"));
                return true;
                }
            }
        }

    return false;
    }


// Folder cleanup for a specified filename search parameter.
function filestore_folder_cleanup($path, $filename_text)
    {
    $result = false;

    if(file_exists($path))
        {
        foreach(new FilesystemIterator($path) as $file)
            {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            
            if(is_dir($file))
                {
                continue;
                }
            if(is_file($file) && strpos($filename, $filename_text) !== false)
                {
                $result = unlink($file->getRealPath());
                debug("FILESTORE_FOLDER_CLEANUP: " . boolean_convert($result, "ok") . ", " . $file);
                $result = true;
                }
            }
        }

    return $result;
    }


// Convert a boolean 'true-false' value to 'Ok-Fail' or 'Yes-No' text.
function boolean_convert($input, $type)
    {
    global $lang;

    if($input && $type == "ok")
        {
        $result = $lang["status-ok"];
        }
    elseif(!$input && $type == "ok")
        {
        $result = $lang["status-fail"];
        }
    elseif($input && $type == "yes")
        {
        $result = $lang["yes"];
        }
    elseif(!$input && $type == "yes")
        {
        $result = $lang["no"];
        }
    else // Insufficient input data.
        {
        $result = "Input error.";
        }

    return $result;
    }
