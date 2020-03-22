<?php
include "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include "../include/resource_functions.php";

$ref=getvalescaped("ref","",true);

if ((isset($allow_resource_deletion) and !$allow_resource_deletion) or (checkperm('D') and !hook('check_single_delete'))){
	include "../include/header.php";
	echo "Error: Resource deletion is disabled.";
	exit;
} else {
$resource=get_resource_data($ref);

# fetch the current search 
$search=getvalescaped("search","");
$order_by=getvalescaped("order_by","relevance");
$offset=getvalescaped("offset",0,true);
$restypes=getvalescaped("restypes","");
if (strpos($search,"!")!==false) {$restypes="";}
$archive=getvalescaped("archive","");

$modal=(getval("modal","")=="true");
$default_sort_direction="DESC";
if (substr($order_by,0,5)=="field"){$default_sort_direction="ASC";}
$sort=getval("sort",$default_sort_direction);

$error="";

// Determine resource file directory path for later check after deleting resource.
$ref_path = get_resource_path($ref, true, '', false);
$ref_path = pathinfo($ref_path);
$resource_path = $ref_path['dirname'] . "\n";
$resource_path = substr_replace($resource_path, "", -1);

// Determine AWS S3 file delete parameters.
global $aws_s3, $delete_check, $delete_folder_check;
if($aws_s3)
    {
    include_once '../include/aws_sdk.php';
    global $s3Client, $aws_bucket, $storagedir, $resource_deletion_state;
    $resource = get_resource_data($ref);

    // Strip $storagedir and leading slash from path for S3.
    $s3_path = ltrim(str_replace($storagedir, "", $resource_path), "/") . "/";

    // Return list of objects (files) in the AWS S3 bucket.
    $list_result = $s3Client->listObjectsV2([
        'Bucket' => $aws_bucket,
        'Prefix' => $s3_path,
    ]);
    }

# Not allowed to edit this resource? They shouldn't have been able to get here.
if (!get_edit_access($ref,$resource["archive"],false,$resource)) {exit ("Permission denied.");}

hook("pageevaluation");

if (getval("save","")!="" && enforcePostRequest(getval("ajax", false)))
	{
	if ($delete_requires_password && hash('sha256', md5('RS' . $username . getvalescaped('password', ''))) != $userpassword)
		{
		$error=$lang["wrongpassword"];
		}
	else
		{
		hook("custompredeleteresource");

		delete_resource($ref);

        // Delete original file in an AWS S3 bucket.
        if ($aws_s3 && $resource['archive'] == 3)
            {
            $s3filepath = get_resource_path($ref, true, '', false);
            $s3strippath = ltrim(str_replace($storagedir, "", $s3filepath), DIRECTORY_SEPARATOR);

            // Use AWS SDK doesObjectExist to verify and deleteObject to delete the file in the specified AWS S3 bucket.
            try
                {
                $s3result = $s3Client->doesObjectExist($aws_bucket, $s3strippath);
                if ($s3result)
                    {
                    $del_result = $s3Client->deleteObject([
                        'Bucket' => $aws_bucket,
                        'Key' => $s3strippath,
                    ]);
                    }
                }
            catch (Aws\S3\Exception\S3Exception $e) // Error check.
                {
                debug("PAGES/DELETE S3 Check Error: " . $e->getAwsErrorMessage());
                }
            }

		hook("custompostdeleteresource");
		
		echo "<script>
		ModalLoad('" . $baseurl_short . "pages/done.php?text=deleted&refreshcollection=true&search=" . urlencode($search) . "&offset=" . urlencode($offset) . "&order_by=" . urlencode($order_by) . "&sort=" . urlencode($sort) . "&archive=" . urlencode($archive) . "',true);
		</script>";
		exit();
		}
	}
include "../include/header.php";

if (isset($resource['is_transcoding']) && $resource['is_transcoding']==1)
	{
?>
<div class="BasicsBox"> 
  <h2>&nbsp;</h2>
  <h1><?php echo $lang["deleteresource"];render_help_link("user/deleting-resources");?></h1>
  <p class="FormIncorrect"><?php echo $lang["cantdeletewhiletranscoding"]?></p>
</div>
<?php	
	}
else
	{
		
if(!$modal)
		{
		?>
		<a onClick="return CentralSpaceLoad(this,true);" href="<?php echo $baseurl_short?>pages/view.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>"><?php echo LINK_CARET_BACK ?><?php echo $lang["backtoresourceview"]?></a>
		<?php
		}
?>

<div class="BasicsBox"> 
	
  <h1><?php echo $lang["deleteresource"];render_help_link("user/deleting-resources");?></h1>
  <p><?php if($delete_requires_password){text("introtext");}else{echo $lang["delete__nopassword"];} ?></p>
  
  <?php if ($resource["archive"]==3) { ?><p><strong><?php echo $lang["finaldeletion"] ?></strong></p><?php } ?>
  
	<form method="post" action="<?php echo $baseurl_short?>pages/delete.php?ref=<?php echo urlencode($ref) ?>&search=<?php echo urlencode($search)?>&offset=<?php echo urlencode($offset) ?>&order_by=<?php echo urlencode($order_by) ?>&sort=<?php echo urlencode($sort) ?>&archive=<?php echo urlencode($archive) ?>&amp;restypes=<?php echo urlencode($restypes); ?>">
	<input type=hidden name=ref value="<?php echo urlencode($ref) ?>">
    <?php generateFormToken("delete_resource"); ?>
	<div class="Question">
	<label><?php echo $lang["resourceid"]?></label>
	<div class="Fixed"><?php echo urlencode($ref) ?></div>
	<div class="clearerleft"> </div>
	</div>
	
	<?php if ($delete_requires_password) { ?>
	<div class="Question">
	<label for="password"><?php echo $lang["yourpassword"]?></label>
	<input type=password class="shrtwidth" name="password" id="password" />
	<div class="clearerleft"> </div>
	<?php if ($error!="") { ?><div class="FormError">!! <?php echo htmlspecialchars($error) ?> !!</div><?php } ?>
	</div>
	<?php }

    // Show list of files and folder to delete?
    if($aws_s3 || $show_files_delete)
        { ?>
        <br>
        <h2><?php echo $lang["deletefilecheck"]?></h2>
        <p><?php echo $lang["deletefilechecktext"]?></p>
        <?php
        // List normal filestore files.
        $scandir = scandir($resource_path);
        echo $lang["filestore"] . " (" . $resource_path . ")";
        ?> <br> <hr> &nbsp;&nbsp;&nbsp; <?php
        echo $resource_path;
        ?> <br> <?php
        foreach($scandir as $sdir)
            {
            if($sdir != "." && $sdir != "..")
                {
                ?> &nbsp;&nbsp;&nbsp; <?php
                echo $sdir;
                ?> <br> <?php
                }
            }
        ?> <br> <?php

        // List AWS S3 files.
        if ($aws_s3)
            {
            echo $lang["aws_s3"] . " (" . $s3_path . ")";
            ?> <br> <hr> <?php
            foreach($list_result["Contents"] as $lresult)
                {
                ?> &nbsp;&nbsp;&nbsp; <?php
                $s3_file = pathinfo($lresult["Key"]);
                $s3_file = $s3_file['basename'];
                echo $s3_file;
                ?> <br> <?php
                }
            }
        } ?> <br> <?php

	$cancelparams = array();
	$cancelparams["ref"] 		= $ref;
	$cancelparams["search"] 	= $search;
	$cancelparams["offset"] 	= $offset;
	$cancelparams["order_by"] 	= $order_by;
	$cancelparams["sort"] 		= $sort;
	$cancelparams["archive"] 	= $archive;
	
	$cancelurl = generateURL($baseurl_short . "pages/view.php",$cancelparams);
	?>
	
	<div class="QuestionSubmit">
	<input name="save" type="hidden" value="true" />
	<label for="buttons"> </label>			
	<input name="save" type="submit" value="&nbsp;&nbsp;<?php echo $lang["deleteresource"]?>&nbsp;&nbsp;"  onclick="return ModalPost(this.form,true);"/>		
	<input name="cancel" type="button" value="&nbsp;&nbsp;<?php echo $lang["cancel"]?>&nbsp;&nbsp;"  onclick='return CentralSpaceLoad("<?php echo $cancelurl ?>",true);'/>
	</div>



	</form>
	
</div>

<?php
	}

} // end of block to prevent deletion if disabled
	
include "../include/footer.php";

?>
