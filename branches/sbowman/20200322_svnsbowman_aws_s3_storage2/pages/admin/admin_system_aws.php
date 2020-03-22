<?php
// Amazon Web Services (AWS) PHP SDK Simple Storage Service (S3) Dashboard
// Last Updated 3/20/2020 by Steve D. Bowman

include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';
if(!checkperm('a'))
    {
    exit($lang["error-permissiondenied"]);
    }
include "../../include/header.php";
require_once "../../include/aws_sdk.php";

global $aws_s3, $originals_separate_storage, $aws_bucket, $cwClient;

?>
<div class="BasicsBox">
<h1><?php echo $lang["aws_s3_dashboard"] ?></h1>
<p><?php echo $lang["aws_dashboard_introtext"]; ?></p>

<table class="InfoTable">
<?php
    // AWS S3 storage check.
    $result = boolean_convert($aws_s3, "yes");
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_text"] ?></td><td><b><?php echo $result?></b></td></tr><?php

    // AWS key pair set check?
    $result = boolean_convert(isset($aws_key), "yes");
    $result1 = boolean_convert(isset($aws_secret), "yes");
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_keypair"] ?></td><td><b><?php echo $result . " / " . $result1;?></b></td></tr><?php

    // Separated filestore check.
    $result = boolean_convert($originals_separate_storage, "yes");
    ?><tr><td colspan="2"><?php echo $lang["filestore_type2"] . " (\$originals_separate_storage = true)?"?></td><td><b><?php echo $result?></b></td></tr><?php

    // RS parameters check.
    if($exiftool_write && $exiftool_write_option && $force_exiftool_write_metadata && !$replace_resource_preserve_option && !$replace_resource_preserve_default && !$replace_batch_existing && !$custompermshowfile)
        {
        $result = ($result == true) ? $lang["status-ok"] : $lang["status-fail"];
        }
    else
        {
        $result = $lang["status-fail"];
        }
    ?><tr><td colspan="2"><?php echo $lang["rs_parameters_check"] ?></td><td><b><?php echo $result?></b></td></tr><?php

    // Storage directory (filestore) check.
    $result = isset($storagedir);
    $result = ($result == true) ? $lang["status-ok"] : $lang["status-fail"];
    ?><tr><td><?php echo $lang['setup-storage_directory'] . " set (\$storagedir)?"; ?></td><td><?php echo $storagedir?></td><td><b><?php echo $result?></b></td></tr><?php

    // Purge filestore tmp folder age check.
    $result = isset($purge_temp_folder_age);
    $result = ($result == true) ? $lang["status-ok"] : $lang["status-fail"];
    $result1 = ($purge_temp_folder_age == 1) ? $lang['expire_day'] : $lang['expire_days'];
    ?><tr><td><?php echo $lang['purge_temp_folder_age'] . " (\$purge_temp_folder_age):"; ?></td><td><?php echo $purge_temp_folder_age . " " . $result1?></td><td><b><?php echo $result?></b></td></tr><?php

    // AWS S3 bucket accessibility check.
    $result = aws_s3_bucket_check($aws_bucket)
    ?><tr><td><?php echo $lang['aws_s3_bucket_access']; ?></td><td><?php echo $aws_bucket?></td><td><b><?php echo $result?></b></td></tr><?php

    // Get AWS S3 bucket location.
    $result = aws_s3_bucket_location($aws_bucket);
    ?><tr><td><?php echo $lang['aws_s3_region']; ?></td><td><?php echo $result['location']?></td><td><b><?php echo $result['status']?></b></td></tr><?php

    // Get AWS S3 bucket owner.
    $result = aws_s3_bucket_owner($aws_bucket);
    ?><tr><td><?php echo $lang['aws_s3_owner']; ?></td><td><?php echo $result['name']?></td><td><b><?php echo $result['status']?></b></td></tr><?php
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_id"] ?></td><td><b><?php echo $result['id']?></b></td></tr><?php

    // Get AWS S3 bucket storage class.
    $result = aws_s3_storage_class($aws_storage_class);
    ?><tr><td colspan="2"><?php echo $lang["aws_s3_storage_class"] ?></td><td><b><?php echo $result['name']?></b></td></tr><?php

    // Get the total size of the attached AWS S3 bucket.
    $cw_metric = 'BucketSizeBytes';
    $cw_statistic = array('Average');
    $cw_unit = 'Bytes';

    $cw_result = aws_s3_bucket_statistics($cw_metric, $cw_statistic, $cw_unit);
    if($cw_result['status'] != null)
        {
        $result = formatfilesize($cw_result['Average']);
        ?><tr><td colspan="2"><?php echo $lang["aws_s3_bucketsizebytes"] ?></td><td><b><?php echo $result . " " . $cw_result['status'] ?></b></td></tr><?php

        // Get the total number of files (objects) stored in the attached AWS S3 bucket.
        $cw_metric = 'NumberOfObjects';
        $cw_statistic = array('Average');
        $cw_unit = 'Count';
        $cw_result = aws_s3_bucket_statistics($cw_metric, $cw_statistic, $cw_unit, 'AllStorageTypes');
        $result = $cw_result['Average'];
        ?><tr><td colspan="2"><?php echo $lang["aws_s3_object_number"] ?></td><td><b><?php echo $result . " " . $cw_result['status'] ?></b></td></tr><?php
        }
?>
</table>

<br/>
<p><?php echo $lang["aws_dashboard_lowertext"]; ?></p>
</div>

<?php
include '../../include/footer.php';
