<?php
// Amazon Web Services (AWS) Simple Storage Service (S3) Object-Based Storage Client Setup
// v1, 2/22/2019, Steve D. Bowman

// Load AWS v3 PHP SDK and setup parameters.
require '../lib/aws_sdk_php_3.87.16/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Aws\CloudWatch\CloudWatchClient;
use Aws\Exception\AwsException;

global $aws_region, $aws_key, $aws_secret;
$aws_s3version = '2006-03-01'; // AWS S3 API version.
$aws_cwversion = '2010-08-01'; // AWS CloudWatch API version.

// Create an AWS S3 client and catch errors.
try
    {
    $s3Client = new Aws\S3\S3Client([
        'version' => $aws_s3version,
        'region' => $aws_region,
        'credentials' => [
            'key'    => $aws_key,
            'secret' => $aws_secret,
        ],
    ]);
    debug("AWS_SDK/S3 Client Setup: OK");
    }
catch  (Aws\S3\Exception\S3Exception $e) // Error catch.
    {
    debug("AWS_SDK/S3 Client Error: " . $e->getMessage());
    }

// Create an AWS CloudWatch client and catch errors.
try
    {
    $cwClient = new Aws\CloudWatch\CloudWatchClient([
        'version' => $aws_cwversion,
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

