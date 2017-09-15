<?php
include_once "../../../include/db.php";
include_once "../../../include/general.php";
include_once "../../../include/search_functions.php";
include_once "../config/config.php";

$search=getvalescaped("search","");
$sign=getvalescaped("sign","");
$offset=getvalescaped("offset",0);
$pagesize=getvalescaped("pagesize",$resourceconnect_pagesize);
$affiliatename=getvalescaped("affiliatename","");

$per_page=getvalescaped("per_page","");if (is_numeric($per_page)) {$pagesize=$per_page;} # Manual setting of page size.
$sort=getvalescaped("sort","");
$order_by=getvalescaped("order_by","");

# Authenticate as 'resourceconnect' user.
global $resourceconnect_user; # Which user to use for remote access?
$userdata=validate_user("u.ref='$resourceconnect_user'");
setup_user($userdata[0]);

$restypes="";
# Resolve resource types
$resource_types=get_resource_types();
$rtx=explode(",",getvalescaped("restypes",""));
foreach ($rtx as $rt)
	{
	# Locate the resource type name in the local list.	
	# We have to handle resource type names because the resource type numeric IDs could be different from system to system.
	foreach ($resource_types as $resource_type)
		{
		if ($resource_type["name"]==$rt)
			{
			if ($restypes!="") {$restypes.=",";}	
			$restypes.=$resource_type["ref"];
			}
		}
	}

$restypes="";
$results=do_search($search,$restypes,$order_by,0,$pagesize+$offset,$sort,false,"",true); # Search, ignoring filters (as fields are unlikely to match).

# The access key is used to sign all inbound queries, the remote system must therefore know the access key.
$access_key=md5("resourceconnect" . $scramble_key);

# Check the search query against the signature.
$expected_sign=md5($access_key . $search);
if ($sign!=$expected_sign) {exit("<p>" . $lang["resourceconnect_error-not_signed_with_correct_key"] . "</p>");}

if ($offset>count($results)) {while ($offset>count($results)) {$offset-=$pagesize;}}
if ($offset<0) {$offset=0;}
?><div class="BasicsBox"><?php

if (!is_array($results))
	{
	?>
	<h1><?php echo $affiliatename ?></h1>
	<p><?php echo $lang["nomatchingresources"] ?></p>
	<?php
	}
else
	{
	?>
	<div class="TopInpageNav">
	<div class="InpageNavLeftBlock"><?php echo $lang["youfound"] ?>:<br><span class="Selected"><?php echo count($results) . " " ?></span><?php if (count($results)==1){echo $lang["youfoundresource"];} else {echo $lang["youfoundresources"];}?></div>

	<div class="InpageNavLeftBlock"><?php echo $lang["resourceconnect_affiliate"] ?>:<br><span class="Selected"><?php echo $affiliatename ?></span></div>	

    
    
    <div id="searchSortOrderContainer" class="InpageNavLeftBlock ">
		Sort order:<br>
        <select id="rc_order_by" onChange="ResourceConnect_Repage(0);">
            <option value="relevance"><?php echo $lang["relevance"] ?></option>
            <option value="popularity"><?php echo $lang["popularity"] ?></option>
            <option value="colour"><?php echo $lang["colour"] ?></option>
            <option value="date"><?php echo $lang["date"] ?></option> 
    </select>
    <select id="rc_sort" onChange="ResourceConnect_Repage(0);">
        <option value="ASC">ASC</option>
        <option value="DESC" selected="">DESC</option>
    </select>
    </div>
    
    
    <div class="InpageNavLeftBlock">Per page:
    <br>
        <select id="rc_per_page" class="medcomplementwidth ListDropdown" style="width:auto" name="resultsdisplay" onChange="ResourceConnect_Repage(0);">
            <option>24</option>
            <option>48</option>
            <option>72</option>
            <option>120</option>
            <option>240</option>
        </select>
    </div>
    
    
	
	<div class="InpageNavRightBlock">
        <span class="TopInpageNavRight"><br />
	<a href="#" 
	
	<?php if ($offset-$pagesize>=0) { ?>onClick="ResourceConnect_Repage(-<?php echo $pagesize ?>);return false;"<?php } ?>
	><i aria-hidden="true" class="fa fa-arrow-left"></i></a>
	&nbsp;
	<?php echo $lang["page"] . " " .  (floor($offset/$pagesize)+1) . " " . $lang["of"] . " " . (floor(count($results)/$pagesize)+1) ?>
	&nbsp;
	<a href="#" 
	<?php if ($offset+$pagesize<=count($results)) { ?>onClick="ResourceConnect_Repage(<?php echo $pagesize ?>);return false;"<?php } ?>
	
	><i aria-hidden="true" class="fa fa-arrow-right"></i></a>
        </span>
	</div>
    
    </div>
    
	<div class="clearerleft"></div>
	<!--<h1><?php echo $affiliatename ?></h1>-->
	<?php
	
	for ($n=$offset;$n<count($results) && $n<($offset+$pagesize);$n++)
		{
		$result=$results[$n];
		$ref=$result["ref"];
		$url=$baseurl . "/pages/view.php?modal=true&ref=" . $ref . "&k=" . substr(md5($access_key . $ref),0,10) . "&language_set=" . urlencode($language) . "&search=" . urlencode($search) . "&offset=" . $offset . "&resourceconnect_source=" . urlencode(getval("resourceconnect_source",""));
		
		# Wrap with local page that includes header/footer/sidebar
		$link_url="../plugins/resourceconnect/pages/view.php?search=" . urlencode($search) . "&url=" . urlencode($url);
		
		$title=str_replace(array("\"","'"),"",htmlspecialchars(i18n_get_translated($result["field".$view_title_field])));
		
		# Add to collections link.
		$add_url=getval("resourceconnect_source","") . "/plugins/resourceconnect/pages/add_collection.php?nc=" . time();
		$add_url.="&title=" . urlencode(get_data_by_field($ref,$view_title_field));
		$add_url.="&url=" . urlencode(str_replace("&search","&source_search",$url)); # Move the search so it doesn't get set, and therefore the nav is hidden when viewing the resource
		$add_url.="&back=" . urlencode($baseurl . "/pages/view.php?" . $_SERVER["QUERY_STRING"]);
		# Add image 
		if ($result["has_image"]==1)
			{ 
			$add_url.="&thumb=" . urlencode(get_resource_path($ref,false,"col",false,"jpg"));
			$add_url.="&large_thumb=" . urlencode(get_resource_path($ref,false,"thm",false,"jpg"));
			$add_url.="&xl_thumb=" . urlencode(get_resource_path($ref,false,"pre",false,"jpg"));
			}	
		else
			{
			$add_url.="&thumb=" . urlencode($baseurl . "/gfx/" . get_nopreview_icon($result["resource_type"],$result["file_extension"],true));
			$add_url.="&large_thumb=" . urlencode($baseurl . "/gfx/" . get_nopreview_icon($result["resource_type"],$result["file_extension"],false));
			$add_url.="&xl_thumb=" . urlencode($baseurl . "/gfx/" . get_nopreview_icon($result["resource_type"],$result["file_extension"],false));
			}
		
		?>
		<div class="ResourcePanel" style="height: 208px;">
		
	
		<a class="ImageWrapper" href="<?php echo $link_url?>" title="<?php echo $title ?>" onClick="return ModalLoad(this,true);"><?php if ($result["has_image"]==1) { ?><img  src="<?php echo get_resource_path($ref,false,"thm",false,$result["preview_extension"],-1,1,false,$result["file_modified"])?>" 
		        style="padding-top:<?php echo floor((150-$result["thumb_height"])/2) ?>px;" /><?php } else { ?><img border=0 src="<?php echo $baseurl ?>/gfx/<?php echo get_nopreview_icon($result["resource_type"],$result["file_extension"],false,false,true) ?>"

		/><?php } ?></a>
	
		<div class="ResourcePanelInfo"><?php echo tidy_trim($title,25) ?>&nbsp;</div>

        <div class="clearer"></div>
                
		<div class="ResourcePanelIcons">		
		<a class="addToCollection fa fa-plus-circle" target="collections" href="<?php echo $add_url ?>" onClick="return CollectionDivLoad(this,true);"></a>
		</div>

		
		</div>

		
		<?php
		
		
		}
	}
    ?>
    </div><!-- End of BasicsBox -->
    <script>
    ResourceConnect_SetPageOptions();
    
    </script>