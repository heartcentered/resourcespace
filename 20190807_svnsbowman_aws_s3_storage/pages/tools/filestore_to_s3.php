<?php
// Convert existing filestore original/alternative files to Amazon Web Services (AWS) Simple Storage Service (S3).
// v1.0, 8/16/2019, Steve D. Bowman

// SCRIPT USER NOTES
// If original files fail to verify they are in the AWS S3 bucket, not enough time since upload may cause eventual consistency to have not been met yet that is a part of object-based storage systems.  Suggest rerunning the script to verify again.

$start = time();

include_once dirname(__FILE__) . "/../../include/db.php";
include_once dirname(__FILE__) . "/../../include/general.php";
include_once dirname(__FILE__) . "/../../include/resource_functions.php";
include_once dirname(__FILE__) . "/../../include/aws_sdk.php";

$start_text = date(format_datetime());
global $aws_s3, $s3Client, $aws_region, $aws_key, $aws_secret, $storagedir, $aws_bucket, $aws_storage_class;
$aws_mpupload_size = '50 MB';

// Setup script logging to a text file named by the date and time the script is run.
$temp_dir = get_temp_dir() . "/aws_s3/";
if (!is_dir($temp_dir))
    {
    mkdir(get_temp_dir() . "/aws_s3/", 0777);
    }
$out_file = $temp_dir . "FilestoreToS3_" . str_replace(array("/", ":"), "-", str_replace(" ", "_", $start_text)) . ".txt";
$text_file = fopen($out_file, 'w+');

// Introductory script output text.
output ("\nRESOURCESPACE FILESTORE TO AMAZON WEB SERVICES (AWS) S3 CONVERTER v1.0: ORIGINAL FILES ONLY\n");
output ("Start on " . $start_text . ", Running script:\n");
output ("Text output saved to " . $out_file . "\n\n");
output ("This script converts an existing ResourceSpace filestore to use AWS Simple Storage Service (S3) for original\n and original alternative files only.  Files in the S3 bucket are in the same \"folder\" structure as in the filestore.\n  The preview images are left in the filestore for high performance.  You must set up an empty AWS S3 bucket and set the\n \$aws_s3, \$storagedir, \$aws_key, \$aws_secret, \$aws_storage_class, \$aws_bucket, \$aws_region, and \$aws_tmp_purge\n parameters in the ../include/config.php file and the server date and time are correct before starting.  If the script is\n rerun, it will skip over already processed files as needed.  While this script is running, no users should use the\n system.  It is highly recommended to run a full system backup before running this script.\n
 Step 1: Configures filestore to AWS S3 bucket storage conversion parameters.
 Step 2: Uploads original resource files and alternative files to a specified AWS S3 bucket.
 Step 3: Verifies original file in AWS S3, deletes the filestore original file, and creates a placeholder file.\n\n");

// Check if run on the command line, if not, exit.
if ('cli' != php_sapi_name())
    {
    header('HTTP/1.1 401 Unauthorized');
    exit('Must use the command line to run this script.');
    }

// Start Step 1, setting up conversion parameters.
output ("STEP 1 OF 3: SETTING UP FILESTORE TO AWS S3 BUCKET CONVERSION PARAMETERS-----------\n");
output ("Filestore Location: " . $storagedir . "\n");

// Get the range of resource IDs and set up counting variables.
$ref_max = get_max_resource_ref();
$ref_range = get_resource_ref_range(0, $ref_max);
$ref_number = count($ref_range);
$count = 1;
$s3_count = 0;
$s3_error_count = 0;
$upfile_missing_count = 0;
output ($ref_number . " resources found in ResourceSpace filestore to convert to AWS S3 bucket storage.\n");

// AWS S3 information.
if (!$aws_s3 || !isset($aws_key) || !isset($aws_secret) || !isset($aws_storage_class) || !isset($aws_mpupload_size) || !isset($aws_bucket) || !isset($storagedir) || !isset($aws_region) || !isset($aws_tmp_purge))
    {
    output ("ERROR: Must set \$aws_s3, \$storagedir, \$aws_key, \$aws_secret, \$aws_storage_class, \$aws_bucket, \$aws_region, \$aws_mpupload_size, and \$aws_tmp_purge parameters, exiting.\n");
    exit();
    }
else
    {
    output ("AWS S3 Bucket Name: " . $aws_bucket . "\n");
    output ("AWS S3 Storage Class: " . $aws_storage_class . "\n");
    output ("AWS S3 Region: " . $aws_region . "\n");
    output ("AWS S3 Multipart Upload Size: " . $aws_mpupload_size . "\n");
    output ("Required variables have been set in the ../include/config.php file.\n");

    // Check that the specified AWS S3 bucket exists.
    $result = $s3Client->doesBucketExist($aws_bucket);
    output ("Checking that the AWS S3 bucket '" . $aws_bucket . "' exists...");
    done_fail($result);
    output ("\n\n");
    if (!$result)
        {
        $result = $s3Client->listBuckets([]);
        output("Available buckets:\n" . print_r($result, true) . "\n\n");
        exit();
        }
    }

// Start Step 2, upload filestore original files to AWS S3.
output("STEP 2 OF 3: UPLOADING FILESTORE ORIGINAL FILES TO AN AWS S3 BUCKET----------\n");
$file_size = "";
$upload_size = 0;

// Loop through the resource IDs and upload original files to AWS S3, files uploaded to same path structure in ../resourcespace/filestore/.. to a specified AWS S3 bucket.
foreach ($ref_range as $ref)
    {
    output ("PROCESSING RESOURCE " . $count . " OF " . $ref_number . " AS RESOURCESPACE ID " . $ref . "\n");

    // Check for resource alternative files.
    $alt_files = get_alternative_files($ref);
    $alt_files_num = count($alt_files);

    // Build array of resource original file and original alternative files to upload to AWS S3 bucket.
    $ref_original[0]['ref'] = 0;
    $ref_files = array_merge($ref_original + $alt_files);

    // Loop through resource original files and upload to S3.
    foreach ($ref_files as $file)
        {
        // Setup AWS SDK S3 putObject parameters.
        if ($file['ref'] == 0)
            {
            $s3filepath = get_resource_path($ref, true, '', false);
            $file_output = "Uploading original file (";
            $file_info = "Original file: ";
            }
        else
            {
            $s3filepath = get_resource_path($ref, true, '', false, $file['file_extension'], true, 1, false, "", $file['ref']);
            $file_output = "Uploading alternative file (";
            $file_info = "Original alternative file Ref#" . $file['ref'] . ": ";
            }

        output ($file_info . $s3filepath . "\n");
        $s3strippath = ltrim(str_replace($storagedir, "", $s3filepath), DIRECTORY_SEPARATOR);
        $original_file = filesize($s3filepath);

        // Check if file needs to be uploaded.
        if ($original_file != 0)
            {
            $file_size = str_replace("&nbsp;", " ", formatfilesize($original_file));
            output ($file_output . $file_size . ") to an AWS S3 bucket...");
            }
        elseif ($original_file == 0)
            {
            output ("Skipping file, already uploaded to an AWS S3 bucket.\n");
            continue;
            }

        // Determine AWS SDK S3 upload method based on the file size.
        if (filesize_unlimited($s3filepath) < filesize2bytes("5 MB") || filesize_unlimited($s3filepath) < filesize2bytes($aws_mpupload_size))
            {
            // Upload file <5 GB and <$aws_mpupload_size using AWS SDK putObject to S3 bucket.
            try
                {
                $s3result = $s3Client->putObject([
                    'Bucket' => $aws_bucket,
                    'Key'    => $s3strippath,
                    'Body'   => fopen($s3filepath, 'rb'),
                    'StorageClass' => $aws_storage_class,
                ]);

                if ($s3result)
                    {
                    $upload_size += $original_file;
                    $s3_count++;
                    output ("Done\n");
                    }
                }
            catch (Aws\S3\Exception\S3Exception $e) // Error catch.
                {
                $s3_error_count++;
                output ("FILESTORE_TO_S3 Upload Failed for: " . $s3filepath);
                output ("FILESTORE_TO_S3 Upload Error: " . $e->getMessage());
                }
            }
        // Upload >5 GB and <5 TB file using AWS SDK MultipartUploader to S3 bucket.
        elseif (filesize_unlimited($s3filepath) < filesize2bytes("5 TB"))
            {
            $s3source = fopen($s3filepath, 'rb');
            $uploader = new MultipartUploader($s3client, $s3source, [
                'bucket' => $aws_bucket,
                'key'    => $s3strippath,
            ]);

            // If error detected, retry uploading remaining parts.
            do
                {
                try
                    {
                    $s3result = $uploader->upload();
                    }
                catch (Aws\Exception\MultipartUploadException $e) // Error catch.
                    {
                    rewind($s3source);
                    $uploader = new MultipartUploader($s3Client, $s3source, [
                        'state' => $e->getState(),
                    ]);
                    }
                }
            while (!isset($s3result));

            if ($s3result)
                {
                $s3_count++;
                output ("Done\n");
                }

            // Multipart upload debug error information.
            if($s3result)
                {
                $s3_count++;
                output ("Done\n");
                }
            else
                {
                $s3_error_count++;
                output ("FILESTORE_TO_S3 MultipartUpload Failed for " . $s3filepath);
                output ("FILESTORE_TO_S3 MultipartUpload Error: " . $e->getMessage());
                }
            }
        // AWS S3 upload failed, attempted to upload >5 TB file to S3 bucket.
        else
            {
            output ("FILESTORE_TO_S3 Upload Failed, Attempted Upload of File >5 TB: " . $s3filepath);
            }
        }
    $count++;
    output ("\n");
    }

// Start Step 3, verify files exist in the specified S3 bucket, and if so, delete the filestore copies.
output ("STEP 3 OF 3: VERIFYING ORIGINAL FILES IN AWS S3 BUCKET AND DELETING FILESTORE COPY----------\n");
$count = 1;
$s3_verify_error = 0;
$delete_error = 0;
$placeholder_error = 0;

// Loop through the resource IDs and verify/delete original files.
foreach ($ref_range as $ref)
    {
    output ("VERIFYING RESOURCE " . $count . " OF " . $ref_number . " AS RESOURCESPACE ID " . $ref . "\n");

    // Check for resource alternative files.
    $alt_files = get_alternative_files($ref);
    $alt_files_num = count($alt_files);

    // Build array of resource original file and original alternative files in an AWS S3 bucket.
    $ref_original[0]['ref'] = 0;
    $ref_files = array_merge($ref_original + $alt_files);

    // Loop through resource original files.
    foreach ($ref_files as $file)
        {
        // Setup AWS SDK S3 doesObjectExist parameters.
        if ($file['ref'] == 0)
            {
            $s3filepath = get_resource_path($ref, true, '', false);
            $file_output = "Verifying original file is in the AWS S3 bucket...";
            $file_info = "Original file: ";
            }
        else
            {
            $s3filepath = get_resource_path($ref, true, '', false, $file['file_extension'], true, 1, false, "", $file['ref']);
            $file_output = "Verifying alternative file is in the AWS S3 bucket...";
            $file_info = "Original alternative file: ";
            }

        output ($file_info . $s3filepath . "\n");
        $s3strippath = ltrim(str_replace($storagedir, "", $s3filepath), DIRECTORY_SEPARATOR);
        output ($file_output);

        // Check if file exists in specified AWS S3 bucket before deleting.
        try
            {
            $s3result = $s3Client->doesObjectExist($aws_bucket, $s3strippath);
            done_fail($s3result);
            output ("\n");

            // Check filestore file size for zero byte placeholder file.
            if (is_file($s3filepath))
                {
                $placeholder = filesize($s3filepath);
                }
            elseif ($s3result) // Create placeholder file if missing, if original is in AWS S3 bucket.
                {
                output("Filestore placeholder file missing, creating file...");
                $placeholder_file = fopen($s3filepath, 'w');
                fclose($placeholder_file);
                $placeholder = filesize($s3filepath);
                output("Done\n");
                }
            else
                {
                output ("ERROR: Placeholder file missing, skipping.\n");
                $placeholder_error++;
                continue;
                }

            // Delete filestore file if in a S3 bucket.
            if($s3result == 1 && $placeholder != 0)
                {
                output ("Deleting original file in filestore...");
                $file_delete = unlink($s3filepath);

                if (!$file_delete)
                    {
                    $delete_error++;
                    }

                done_fail($file_delete);
                output ("\n");

                // Adding filestore placeholder file.
                if ($placeholder != 0)
                    {
                    output("Creating filestore placeholder file...");
                    $placeholder_file = fopen($s3filepath, 'w');
                    fclose($placeholder_file);
                    done_fail($placeholder_file);
                    output("\n");
                    }
                }
            elseif ($placeholder == 0)
                {
                output ("Skipping file, already processed.\n");
                }
            else // File not in a S3 bucket, do not delete.
                {
                $s3_verify_error++;
                output ("ERROR: File does not exist in the S3 bucket.\n");
                }
            }
        catch (Aws\S3\Exception\S3Exception $e) // Error catching.
            {
            output("SDK doesObjectExist Error: " . $e->getMessage());
            }
        }

    $count++;
    output ("\n");
    }

// Summarize script results and terminate.
output ("RESOURCESPACE FILESTORE TO AWS S3 BUCKET CONVERSION SUMMARY----------\n");
if ($upload_size == 0)
    {
    $upload_size = "";
    }
else
    {
    $upload_size = str_replace("&nbsp;", " ", "(" . formatfilesize($upload_size) . ") ");
    }

output ($count-1 . " files processed with " . $s3_count . " files " . $upload_size . "uploaded to the AWS S3 '" . $aws_bucket . "' bucket.\n");
//output ($upfile_missing_count . " original files missing from the filestore during the upload process.\n");
output ($s3_error_count . " original files failed to upload to the AWS S3 '" . $aws_bucket . "' bucket.\n");
output ($s3_verify_error . " original files failed to verify in the AWS S3 '" . $aws_bucket . "' bucket.\n");
output ($placeholder_error . " placeholder files missing from the filestore during the verify process.\n");
output ($delete_error . " original files failed to delete from the ResourceSpace filestore: " . $storagedir . "\n");

$end = time();
$t_unit = " minutes.\n\n";
$ltime = ($end - $start) / 60;
if ($ltime > 60)
    {
    $ltime = $ltime / 60;
    $t_unit = " hours.\n\n";
    }
output ("Script ended on " . date(format_datetime()) . " in " . number_format($ltime, 1, '.', '') . $t_unit);
fclose($text_file);

// SCRIPT CUSTOM FUNCTIONS
// Boolean return text converter.
function done_fail($boolean)
    {
    if ($boolean)
        {
        output ("Done");
        }
    else
        {
        output ("FAIL");
        }
    }

// Add "output" text to a script run text output file.
function output($text)
    {
    global $out_file;
    echo ($text);
    ob_flush();
    file_put_contents($out_file, $text, FILE_APPEND);
    }
