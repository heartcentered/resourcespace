<?php
/**
 * Batch resource replace
 * 
 */
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
if (!checkperm("t"))
    {
    exit ("Permission denied.");
    }
    
$no_exif        = getval('no_exif', '');
$filename_field = getval("filename_field",0,true);
$resource_min   = getval("resource_min",0,true);
$resource_max   = getval("resource_max",0,true);
$replace_col    = getval("batch_replace_collection",0,true);
$mode           = getval("batch_replace_mode","upload");
$submitted      = getval("submit","") != "";

if($submitted)
    {    
    if($mode == "upload")
        {
        $upload_params = array();
        $upload_params["replace"]       = "true";
        $upload_params["filename_field"]= $filename_field;
        $upload_params["replace_col"]   = $replace_col;
        $upload_params["resource_min"]  = $resource_min;
        $upload_params["resource_max"]  = $resource_max;
        $upload_params["no_exif"]       = $no_exif;
        
        redirect(generateURL($baseurl_short . "pages/upload_plupload.php", $upload_params));
        exit();
        }
    elseif($mode == "fetch_local" && $offline_job_queue)
        {
        // Create offline job to retrieve files
        $replace_batch_local_data = array(
            'import_path'   => $batch_replace_local_folder,
            'filename_field'=> $filename_field,
            'replace_col'   => $replace_col,
            'resource_min'  => $resource_min,
            'resource_max'  => $resource_max,
            'no_exif'       => $no_exif
        );
        
        job_queue_add(
            'replace_batch_local',
            $replace_batch_local_data,
            '',
            '',
            $lang["oj-batch-replace-local-success-text"],
            $lang["oj-batch-replace-local-failure-text"]);

        $info_text = $lang["replacebatch_job_created"];
        }
    }

// Get list of fields to allow selection of field containing file name to folder path
$allfields=get_resource_type_fields();
//print_r($allfields);

include "../include/header.php";

if (isset($info_text))
    {?>
    <div class="PageInformal"><?php echo $info_text?></div>
    <?php
    }

?>

<h1><?php echo $lang["replaceresourcebatch"] ?></h1>

<p><?php echo $lang["batch_replace_filename_intro"] ?></p>

<form action="<?php echo $baseurl_short?>pages/upload_replace_batch.php" >

<?php generateFormToken("upload_replace_batch"); ?>
<input id="batch_replace_mode" type="hidden" name="batch_replace_mode" value="<?php echo htmlspecialchars($mode); ?>" />
<input id="submit" type="hidden" name="submit" value="true" />
    
<div class="Question">
    <label for="use_resourceid"><?php echo $lang["batch_replace_use_resourceid"]?></label>
    <input type="checkbox" value="yes" <?php if ($filename_field == 0) {echo " checked ";} ?> name="use_resourceid" id="use_resourceid" onClick="if(this.checked){jQuery('#question_filename_field').slideUp();jQuery('#filename_field').prop('disabled',true);}else{jQuery('#question_filename_field').slideDown();jQuery('#filename_field').prop('disabled',false);}" />
    <div class="clearerleft"> </div>
</div>

<div class="Question" id="question_filename_field" <?php if ($filename_field == 0) {echo "style='display:none;'";}?>>
    <label for="filename_field"><?php echo $lang["batch_replace_filename_field_select"]?></label>
    <select  class="stdwidth" name="filename_field" id="filename_field">
    <?php

    foreach ($allfields as $metadatafield)
        {
        ?>
        <option value="<?php echo $metadatafield["ref"] ?>" <?php if($metadatafield["ref"] == $filename_field){ echo " selected";} ?>>
        <?php echo i18n_get_translated($metadatafield["title"]) ?>	
        </option>    
        <?php
        }
    ?>
    </select>
    <div class="clearerleft"> </div>
</div>

<div class="Question">
    <label for="resource_min"><?php echo $lang["replacebatch_resource_min"]?></label>
    <input type="text" class="shrtwidth" value="<?php echo htmlspecialchars($resource_min); ?>" name="resource_min" id="resource_min" />
    <div class="clearerleft"> </div>
</div>

<div class="Question">
    <label for="resource_max"><?php echo $lang["replacebatch_resource_max"]?></label>
    <input type="text" class="shrtwidth" value="<?php echo htmlspecialchars($resource_max); ?>" name="resource_max" id="resource_max" />
    <div class="clearerleft"> </div>
</div>

<div class="Question">
    <label for="batch_replace_collection"><?php echo $lang["replacebatch_collection"]?></label>
    <input type="text" class="shrtwidth" value="<?php echo htmlspecialchars($replace_col); ?>" name="batch_replace_collection" id="batch_replace_collection" />
    <div class="clearerleft"> </div>
</div>

<div class="Question">
    <label for="no_exif"><?php echo $lang["no_exif"]?></label>
    <input type=checkbox <?php if ((!$metadata_read_default && !$submitted) || $no_exif == "yes"){ echo " checked "; } ?> id="no_exif" name="no_exif" value="yes">
    <div class="clearerleft"> </div>
</div>

<?php
if($offline_job_queue)
    {?>
    <div class="Question">
        <label for="replace_batch_local"><?php echo $lang["replacebatchlocalfolder"]?></label>
        <input type="checkbox" value="yes" <?php if($mode == "fetch_local") {echo " checked";} ?> name="replace_batch_local" id="replace_batch_local" onClick="if(this.checked){document.getElementById('batch_replace_mode').value = 'fetch_local';}else{document.getElementById('batch_replace_mode').value = 'upload'}" />
        <div class="clearerleft"> </div>
    </div>
    
   
    <?php
    }
    ?>

<div class="Question">
<input type="submit" value="<?php echo $lang["start"]; ?>" name="upload" id="upload_button" onClick="CentralSpacePost(this,true);" />
<div class="clearerleft"> </div>
</div>

</form>

<?php


include "../include/footer.php";
