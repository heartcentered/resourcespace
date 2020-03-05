<?php

include "../../../include/db.php";
include_once "../../../include/general.php";
include "../../../include/authenticate.php";
include_once "../../../include/collections_functions.php";
include "../../../include/search_functions.php";
include "../../../include/resource_functions.php";
include "../../../include/image_processing.php";

# Fetch videos and process...
$videos = do_search("!collection" . $usercollection);
$splice_order = getval("splice_order", "");

if (getval("splice","") != "" && count($videos) > 1 && enforcePostRequest(false))
	{

	# Lets get the correct splice_order and put it into an array '$videos_reordered'
	$explode_splice = explode(",", $splice_order);
	$videos_reordered = array();

	foreach($explode_splice as $key => $each_slice)
	{
		$explode_splice[$key] = ltrim($each_slice, 'splice_');
		$this_key = $explode_splice[$key];
		$the_key_i_need = array_search($this_key, array_column($videos, 'ref'));
		$videos_reordered[] = $videos[$the_key_i_need];
	}
	
	# Reset $videos to the correct order from $videos_reordered
	$videos = $videos_reordered;

	# Below works as before
	$ref = copy_resource($videos[0]["ref"]); # Base new resource on first video (top copy metadata).

	# Set parent resource field details.
	global $videosplice_parent_field;
	$resources = "";
	for ($n = 0; $n < count($videos); $n++)
		{
		if ($n > 0) { $resources.=", "; }
		$crop_from = get_data_by_field($videos[$n]["ref"], $videosplice_parent_field);
		$resources .= $videos[$n]["ref"] . ($crop_from != "" ? " " . str_replace("%resourceinfo", $crop_from, $lang["cropped_from_resource"]) : "");
		}
	$history = str_replace("%resources", $resources, $lang["merged_from_resources"]);
	update_field($ref, $videosplice_parent_field, $history);

	# Establish FFMPEG location.
	$ffmpeg_fullpath = get_utility_path("ffmpeg");

	$vidlist = "";

	# Create FFMpeg syntax to merge all additional videos.
	for ($n = 0; $n < count($videos); $n++)
		{

		# Work out source/destination
		global $ffmpeg_preview_extension;
		
		if (file_exists(get_resource_path($videos[$n]["ref"], true, "", false, $videos[$n]["file_extension"])))
			{
			$source = get_resource_path($videos[$n]["ref"], true, "", false, $videos[$n]["file_extension"], -1, 1, false, "", -1, false);
			}
		else 
			{
			exit(str_replace(array("%resourceid", "%filetype"), array($videos[$n]["ref"], $videos[$n]["file_extension"]), $lang["error-no-ffmpegpreviewfile"]));
			}

		# Encode intermediary
		$intermediary = get_temp_dir() . "/video_splice_temp_" . $videos[$n]["ref"] . ".mpg";
		if ($config_windows) { $intermediary = str_replace("/", "\\", $intermediary); }
		$shell_exec_cmd = $ffmpeg_fullpath . " -y -i " . escapeshellarg($source);
		$shell_exec_cmd .= ($ffmpeg_use_qscale)? " -target ntsc-vcd " : " -sampleq ";
		$shell_exec_cmd .= escapeshellarg($intermediary);
		$output = exec($shell_exec_cmd);
		$vidlist .= " " . escapeshellarg($intermediary);
		}

	$vidlist = trim($vidlist);
	
	# Target is the first file.
	$targetmpg = get_resource_path($ref, true, "", true, "mpg", -1, 1, false, "", -1, false);

	# Combine all MPEGS to make one file (this doesn't work for FLV, we had to convert to MPEG first)
	if ($config_windows)
		{
		$shell_exec_cmd = "copy/b " . str_replace(array(" ", "/"), array("+", "\\"), $vidlist) . " " . escapeshellarg($targetmpg);
		}
	else
		{
		$shell_exec_cmd = "cat $vidlist > " . escapeshellarg($targetmpg);
		}
	$output = exec($shell_exec_cmd);


	# Remove the temporary files.
	for ($n = 0; $n < count($videos); $n++)
		{
		$intermediary = get_temp_dir() . "/video_splice_temp_" . $videos[$n]["ref"] . ".mpg";
		if ($config_windows) { $intermediary = str_replace("/", "\\", $intermediary); }
		unlink($intermediary);
		}

	# Update the file extension & date.
	$result = sql_query("update resource set file_extension = 'mpg', creation_date = now() where ref = '$ref' limit 1");

	# Create previews.
	create_previews($ref, false, "mpg");
	redirect("pages/view.php?ref=" . $ref);
	}

# Header and splice page
include "../../../include/header.php";

?>

<h1><?php echo $lang["splice"]?></h1>
<p><?php echo $lang["intro-splice"]?></p>
<p><?php echo $lang["drag_and_drop_to_rearrange"]?></p>

<div id="splice_scroll">
	<div id="splice_reel" style="overflow: hidden; height: 65px !important; width:<?php echo ((count($videos)+2) * 105);?>px">
	<?php

		foreach ($videos as $video)
			{
			if ($video["has_image"])
				{
				$img = get_resource_path($video["ref"], false, "col", false, $video["preview_extension"], -1, 1, false, $video["file_modified"]);
				}
			else
				{
				$img = "../../../gfx/" . get_nopreview_icon($video["resource_type"], $video["file_extension"], true);
				}
				
	?>
	<img src="<?php echo $img ?>" id="splice_<?php echo $video["ref"] ?>" class="splice_item">
	<?php } ?>
	</div>
</div>

<form method="post">
<?php generateFormToken("splice"); ?>
<input type="hidden" name="splice_order" id="splice_reel_order" />
<input type="submit" onClick="CentralSpaceShowLoading();" name="splice" value="<?php echo $lang["action-splice"]?>" style="width:150px;">
</form>

<script type="text/javascript">

	function ReorderResourcesInCollectionSplice(idsInOrder)
		{
		var newOrder = [];
		
		jQuery.each(idsInOrder, function() 
			{
			newOrder.push(this.substring(7));
			});
		
		jQuery.ajax(
			{
			type: 'POST',
			url: '<?php echo $baseurl_short?>pages/collections.php?collection=<?php echo urlencode($usercollection) ?>&reorder=true',
			data: 
				{
				order:JSON.stringify(newOrder),
				<?php echo generateAjaxToken('ReorderResourcesInCollectionSplice'); ?>
				},
			success: function() 
				{
				var results = new RegExp('[\\?&amp;]' + 'search' + '=([^&amp;#]*)').exec(window.location.href);
				var ref = new RegExp('[\\?&amp;]' + 'ref' + '=([^&amp;#]*)').exec(window.location.href);
				if ((ref==null)&&(results!== null)&&('<?php echo urlencode("!collection" . $usercollection); ?>' === results[1])) CentralSpaceLoad('<?php echo $baseurl_short?>pages/search.php?search=<?php echo urlencode("!collection" . $usercollection); ?>',true);
				}
			});
		}

	/* Start splice reel sortable */
	jQuery("#splice_reel").sortable({ axis: "x" });

	/* Re-order collections */
	jQuery(document).ready(function() 
		{
		var idsInOrder = jQuery('#splice_reel').sortable("toArray");
		jQuery('#splice_reel_order').val(idsInOrder);
		var collection = <?php echo $usercollection; ?>;
		var k = <?php echo $k? $k : "''"; ?>;
		jQuery('#splice_reel').sortable(
			{
			axis: "x",
			helper:"clone",
			items: ".splice_item",
			stop: function(event, ui) 
				{
				var idsInOrder = jQuery('#splice_reel').sortable("toArray");
				jQuery('#splice_reel_order').val(idsInOrder);
				ReorderResourcesInCollectionSplice(idsInOrder);
				ChangeCollection(collection,k);
				}
			});
		jQuery('.CollectionPanelShell').disableSelection();
		jQuery("#CollectionDiv").on("click",".CollectionResourceRemove",function() 
			{
			var splice_id = "#splice_"+jQuery(this).closest(".CollectionPanelShell").attr("id").replace(/[^0-9]/gi,"");
			jQuery(splice_id).remove();
			});
		});

</script>

<?php include "../../../include/footer.php";