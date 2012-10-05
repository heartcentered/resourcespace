<?php

function HookMagictouchPreviewPreviewimage()
        {
global $baseurl,$plugins,$lang,$search,$offset,$archive,$order_by,$sort,$plugins,$download_multisize,$k,$access,$ref,$resource,$watermark;
global $magictouch_account_id;
if (getval("alternative","")!=""){return false;}
if ($magictouch_account_id==""){return false;}

// This hooks runs outside of the renderinnerresourcepreview hook,
// and if MTFAIL is defined, annotate will know not to include a Zoom link.
// annotate plugin compatibility

global $plugins;
if (in_array("annotate",$plugins)){
    global $annotate_ext_exclude;
    global $annotate_rt_exclude;
    if (in_array($resource['file_extension'],$annotate_ext_exclude)){return false;}
    if (in_array($resource['resource_type'],$annotate_rt_exclude)){return false;}  
    if (getval("annotate","")!=""){
        return false;
    }
}


// exclusions    
global $magictouch_rt_exclude;
global $magictouch_ext_exclude;
if (in_array($resource['resource_type'],$magictouch_rt_exclude)){define("MTFAIL",true); return false;}
if (in_array($resource['file_extension'],$magictouch_ext_exclude)){define("MTFAIL",true); return false;}

        $download_multisize=true;

        if ($resource["has_image"]!=1)
                {define("MTFAIL",true);
                return false;
                }


// watermark check
$access=get_resource_access($ref);
$use_watermark=check_use_watermark($ref);

// paths
$imageurl=get_resource_path($ref,false,"scr",false,$resource["preview_extension"],-1,1,$use_watermark);

global $magictouch_preview_page_sizes;
foreach ($magictouch_preview_page_sizes as $mtpreviewsize){
    $largeurl=get_resource_path($ref,false,$mtpreviewsize,false,"jpg",-1,1,$use_watermark);
    $largeurl_path=get_resource_path($ref,true,$mtpreviewsize,false,"jpg",-1,1,$use_watermark);

    if (file_exists($largeurl_path)){break;}

}

if (!file_exists($largeurl_path)) {
    define("MTFAIL",true);
    return false; # Requires an original large JPEG file.
}  ?>

<div style="float:left;">
<div class="Picture">
<a href="<?php echo $largeurl?>" class="MagicTouch"><img src="<?php echo $imageurl?>" GALLERYIMG="no" id="previewimage" /></a>
</div><br />
    
<?php
// annotate plugin compatibility
if (in_array("annotate",$plugins)){?><a style="display:inline;" href="<?php echo $baseurl?>/pages/preview.php?ref=<?php echo $ref;?>&search=<?php echo urlencode($search)?>&offset=<?php echo $offset?>&order_by=<?php echo $order_by?>&sort=<?php echo $sort?>&archive=<?php echo $archive?>&k=<?php echo $k?>&annotate=true">&gt;&nbsp;<?php echo $lang['annotations']?></a><br /><br /><?php }
?>

</div>
<script type="text/javascript">if(typeof MagicTouch=="object") {MagicTouch.refresh();} else {console.log("MagicTouch not loaded");}</script>
<?php
    return true;
}









