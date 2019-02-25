<?php
// Amazon Web Services (AWS) Simple Storage Service (S3) Object-Based Storage Dashboard
// v1, 2/22/2019, Steve D. Bowman

include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit('Permission denied.');
    }
include "../../include/header.php";
require_once "../../include/aws_sdk.php";

global $aws_s3, $originals_separate_storage, $aws_bucket;
?>
<div class="BasicsBox">
<h1><?php echo $lang["aws_s3_dashboard"] ?></h1>
<table class="InfoTable">
<?php

// AWS S3 storage check.
$result = text_convert($aws_s3);
?><tr><td colspan="2"><?php echo $lang["aws_s3_text"] ?></td><td><b><?php echo $result?></b></td></tr><?php

// AWS key pair set check?
$result = text_convert(isset($aws_key));
$result1 = text_convert(isset($aws_secret));
?><tr><td colspan="2"><?php echo $lang["aws_s3_keypair"] ?></td><td><b><?php echo $result . " / " . $result1;?></b></td></tr><?php

// Separated filestore check.
$result = text_convert($originals_separate_storage);
?><tr><td colspan="2"><?php echo $lang["filestore_type2"] . " (\$originals_separate_storage = true)?"?></td><td><b><?php echo $result?></b></td></tr><?php

// Storage directory (filestore) check.
$result = isset($storagedir);
$result = ($result == true) ? $lang["status-ok"] : $lang["status-fail"];
?><tr><td><?php echo $lang['setup-storage_directory'] . " set (\$storagedir)?"; ?></td><td><?php echo $storagedir?></td><td><b><?php echo $result?></b></td></tr><?php

// Purge filestore tmp folder age check.
$result = isset($purge_temp_folder_age);
$result = ($result == true) ? $lang["status-ok"] : $lang["status-fail"];
$result1 = ($purge_temp_folder_age == 1) ? $lang['expire_day'] : $lang['expire_days'];
?><tr><td><?php echo $lang['purge_temp_folder_age'] . " (\$purge_temp_folder_age):"; ?></td><td><?php echo $purge_temp_folder_age . " " . $result1?></td><td><b><?php echo $result?></b></td></tr><?php

// ExifTool metadata writing check.
$result = ($exiftool_write == true) ? $lang["status-ok"] : $lang["status-fail"];
$result1 = text_convert($exiftool_write);
?><tr><td><?php echo $lang['exiftool_write']; ?></td><td><?php echo $result1?></td><td><b><?php echo $result?></b></td></tr><?php

$result = ($exiftool_write_option == true) ? $lang["status-ok"] : $lang["status-fail"];
$result1 = text_convert($exiftool_write_option);
?><tr><td><?php echo $lang['exiftool_write_option']; ?></td><td><?php echo $result1?></td><td><b><?php echo $result?></b></td></tr><?php

// AWS S3 bucket accessibility check.
try
    {
    $result = $s3Client->headBucket([
       'Bucket' => $aws_bucket,
    ]);
    $result = $lang["status-ok"];
    }
catch (Aws\S3\Exception\S3Exception $e)
    {
    $result = $lang["status-fail"];
    debug("SYSTEM_AWS_S3/AWS S3 STORAGE CHECK: " . $e->getMessage());
    }
?><tr><td><?php echo $lang['aws_s3_bucket_access']; ?></td><td><?php echo $aws_bucket?></td><td><b><?php echo $result?></b></td></tr><?php

// Get AWS S3 bucket location.
try
    {
    $result = $s3Client->getBucketLocation([
        'Bucket' => $aws_bucket,
    ]);
    $result = $result['LocationConstraint'];
    $result1 = $lang["status-ok"];
    }
catch (Aws\S3\Exception\S3Exception $e)
    {
    $result1 = $lang["status-fail"];
    debug("SYSTEM_AWS_S3/getBucketLocation: " . $e->getMessage());
    }
?><tr><td><?php echo $lang['aws_s3_region']; ?></td><td><?php echo $result?></td><td><b><?php echo $result1?></b></td></tr><?php

// Get AWS S3 bucket owner.
try
    {
    $result = $s3Client->getBucketAcl([
       'Bucket' => $aws_bucket,
    ]);
    $result1 = $result['Owner']['DisplayName'];
    $result2 = $result['Owner']['ID'];
    $result = $lang["status-ok"];
    }
catch (Aws\S3\Exception\S3Exception $e)
    {
    $result = $lang["status-fail"];
    debug("SYSTEM_AWS_S3/getBucketAcl: " . $e->getMessage());
    }
?><tr><td><?php echo $lang['aws_s3_owner']; ?></td><td><?php echo $result1?></td><td><b><?php echo $result?></b></td></tr><?php
?><tr><td colspan="2"><?php echo $lang["aws_s3_id"] ?></td><td><b><?php echo $result2?></b></td></tr><?php

// Get AWS S3 bucket storage class.
?><tr><td colspan="2"><?php echo $lang["aws_s3_storage_class"] ?></td><td><b><?php echo $aws_storage_class?></b></td></tr><?php

// Get AWS S3 bucket metrics.
switch ($aws_storage_class)
    {
    case "STANDARD":
        $result = "StandardStorage";
        break;
    case "STANDARD_IA":
        $result = "StandardIAStorage";
        break;
    case "ONEZONE_IA":
        $result = "OneZoneIAStorage";
        break;
    case "REDUCED_REDUNDANCY":
        $result = "ReducedRedundancyStorage";
        break;
    default:
        $result = "AllStorageTypes";
    }

try
    {
    // Determine the total size of the attached S3 bucket.
    $dimensions = array(
        array('Name' => 'BucketName', 'Value' => $aws_bucket),
        array('Name' => 'StorageType', 'Value' => $result),
    );

    $result = $cwClient->getMetricStatistics(array(
        'Namespace'  => 'AWS/S3',
        'MetricName' => 'BucketSizeBytes',
        'Dimensions' => $dimensions,
        'StartTime'  => strtotime('2006-03-14'),
        'EndTime'    => strtotime('now'),
        'Period'     => 750000,
        'Statistics' => array('Average'),
        'Unit'       => 'Bytes',
    ));

    // Determine approximate monthly cost (work in progress).
    $aws_s3_storage_monthcost = 0.023;
    $result2 = formatfilesize($result["Datapoints"][1]["Average"]);
    $result3 = "$" . round(($result["Datapoints"][1]["Average"] / (1024 * 1024 * 1024)) * $aws_s3_storage_monthcost,2,PHP_ROUND_HALF_UP) . "/" . strtolower($lang["month"]);

    ?><tr><td colspan="2"><?php echo $lang["aws_s3_bucketsizebytes"] ?></td><td><b><?php echo $result2 . " at " . $result3?></b></td></tr><?php

    // Determine the total number of objects in the attached S3 bucket.
    $dimensions = array(
        array('Name' => 'BucketName', 'Value' => $aws_bucket),
        array('Name' => 'StorageType', 'Value' => "AllStorageTypes"),
    );
    $result = $cwClient->getMetricStatistics(array(
        'Namespace'  => 'AWS/S3',
        'MetricName' => 'NumberOfObjects',
        'Dimensions' => $dimensions,
        'StartTime'  => strtotime('2006-03-14'),
        'EndTime'    => strtotime('now'),
        'Period'     => 750000,
        'Statistics' => array('Average'),
        'Unit'       => 'Count',
    ));
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_object_num"] ?></td><td><b><?php echo $result["Datapoints"][1]["Average"]?></b></td></tr><?php

/*  WORK IN PROGRESS- not able to get these metrics from CloudWatch for some unknown reason.  Maybe someone else can look at.
    // List the available CloudWatch metrics.
    $dimensions = array(
        array('Name' => 'BucketName', 'Value' => $aws_bucket),
        array('Name' => 'FilterID', 'Value' => "EntireBucket"),
    );

    $result = $cwClient->listMetrics(array(
        'Dimensions' => $dimensions,
        'MetricName' => 'PutRequests',
        'Namespace'  => 'AWS/S3',
    ));
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_putrequests"] ?></td><td><b><?php echo var_dump($result)?></b></td></tr><?php

    // Determine the total number of HTTP PutRequests from S3.
    $dimensions = array(
        array('Name' => 'BucketName', 'Value' => $aws_bucket),
        //array('Name' => 'FilterID', 'Value' => "EntireBucket"),
    );
    $result = $cwClient->getMetricStatistics(array(
        'Namespace'  => 'AWS/S3',
        'MetricName' => 'PutRequests',
        //'Dimensions' => $dimensions,
        'StartTime'  => strtotime('-2 days'),
        'EndTime'    => strtotime('now'),
        'Period'     => 3600,
        'Statistics' => array('Sum'),
        'Unit'       => 'Count',
    ));
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_putrequests"] ?></td><td><b><?php echo $result["Metrics"][0]["Sum"]?></b></td></tr><?php
*/
    }
catch (Aws\Exception\AwsException $e)
    {
    $result = $lang["status-fail"];
    debug("SYSTEM_AWS_S3/Statistics: " . $e->getMessage());
    }

?>
</table>
</div>
<?php
include '../../include/footer.php';

// Convert true-false to yes-no text.
function text_convert($input)
    {
    global $lang;
    if ($input)
        {
        $result = $lang["yes"];
        }
    else
        {
        $result = $lang["no"];
        }
    return $result;
    }

