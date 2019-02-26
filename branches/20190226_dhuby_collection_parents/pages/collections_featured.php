<?php
include_once "../include/db.php";
include_once "../include/general.php";
include "../include/authenticate.php";
include_once "../include/collections_functions.php";
include_once "../include/resource_functions.php";
include_once "../include/render_functions.php";
include_once "../include/search_functions.php";

if(!$enable_themes)
    {
    header('HTTP/1.1 403 Forbidden');
    exit($lang['error-permissiondenied']);
    }

$parent=getvalescaped("parent",-1,true);        

if(getval("create","") != "" && enforcePostRequest(getval("ajax", false)))
	{
    if(!checkperm('h'))
        {
        http_response_code(401);
        exit($lang['error-permissiondenied']);
        }

	// Create the collection and reload the page
	$collectionname = getvalescaped("collectionname","");
	$newcategory = getvalescaped("category_name","");
    $themes = GetThemesFromRequest($theme_category_levels);
    $themecount = count($themes);
	// Add the new category to the theme array
	if($newcategory != ""){$themes[]=$newcategory;}
	$new_collection = create_collection($userref,$collectionname,0,0,0,true,$themes);
	set_user_collection($userref,$new_collection);
	refresh_collection_frame($collection="");
	}	
elseif(getval("new","")!="")
	{
	// Option to create a new featured collection at or below the current level
	new_featured_collection_form($themes);
	exit();
	}

hook("themeheader");


function DisplayCollections($parent)
	{
	global $baseurl_short, $lang, $themecount, $themes_simple_images, $collection_download_only;
	$collections=get_collections($parent);        
	foreach ($collections as $collection)
			{
			$theme_image_path="";
			
			/*
			if($themes_simple_images)
				{
				$theme_images=get_theme_image($themes,$collection["ref"]);
				if(is_array($theme_images) && count($theme_images)>0)
					{
					foreach($theme_images as $theme_image)
						{
						if(file_exists(get_resource_path($theme_image,true,"pre",false)))
							{
							$theme_image_path=get_resource_path($theme_image,false,"pre",false);
							$theme_image_detail= get_resource_data($theme_image);
							break;
							}
						}
					}
				}
				*/
				# Does this itself contain collections? Folder mode.
				$is_folder=count(get_collections($collection["ref"]))>0;
					
				$icon=($is_folder?"folder":"th-large");
				
				$url=($is_folder? $baseurl_short . "pages/collections_featured.php?parent=" . $collection["ref"]
					            : $baseurl_short . "pages/search.php?search=!collection" . $collection["ref"]);
                ?>
				<div id="FeaturedSimpleTile_<?php echo md5($collection['ref']); ?>" class="FeaturedSimplePanel HomePanel DashTile FeaturedSimpleTile<?php
					if($theme_image_path!="")
						{	
						echo " FeaturedSimpleTileImage\" style=\"background: url(" . $theme_image_path . ");background-size: cover;";
						}?> <?php echo strip_tags_and_attributes(htmlspecialchars(str_replace(" ","",i18n_get_collection_name($collection))))?>">					
					<a href="<?php echo $url ?>" onclick="return CentralSpaceLoad(this,true);" class="FeaturedSimpleLink <?php if($themes_simple_images){echo " TileContentShadow";} ?>" id="featured_tile_<?php echo $collection["ref"]; ?>">
					<div id="FeaturedSimpleTileContents_<?php echo $collection["ref"] ; ?>"  class="FeaturedSimpleTileContents">
                        <h2><span class="fa fa-<?php echo $icon ?>"></span><?php echo i18n_get_collection_name($collection); ?></h2>
					</div>
					</a>
                    <div id="FeaturedSimpleTileActions_<?php echo md5($collection['ref']); ?>" class="FeaturedSimpleTileActions"  style="display:none;">
                    <?php
                    if(checkPermission_dashmanage())
                        {
                        $display_theme_dash_tile_link = generateURL(
                            "{$baseurl_short}pages/dash_tile.php",
                            array(
                                'create'            => 'true',
                                'tltype'            => 'srch',
                                'title'             => "{$collection['name']}",
                                'freetext'          => 'true',
                                'tile_audience'     => 'false',
                                'all_users'         => 1,
                                'promoted_resource' => 'true',
                                'link'              => $url,
                            )
                        );
                        ?>
                        <div class="tool">
                            <a href="<?php echo $display_theme_dash_tile_link; ?>" onClick="return CentralSpaceLoad(this, true);">
                                <span><?php echo LINK_CARET; ?><?php echo $lang['add_to_dash']; ?></span>
                            </a>
                        </div>
                        <?php
                        }

                    if(collection_readable($collection['ref']))
                        {
                        ?>
                        <div class="tool">
                            <a href="#" onClick="return ChangeCollection(<?php echo $collection['ref']; ?>, '');">
                                <span><?php echo LINK_CARET; ?><?php echo $lang['action-select']; ?></span>
                            </a>
                        </div>
                        <?php
                        }

                    if(collection_writeable($collection['ref']))
                        {
                        $display_theme_edit_link = generateURL(
                            "{$baseurl_short}pages/collection_edit.php",
                            array('ref' => $collection['ref'])
                        );
                        ?>
                        <div class="tool">
                            <a href="<?php echo $display_theme_edit_link; ?>" onClick="return ModalLoad(this, true);">
                                <span><?php echo LINK_CARET; ?><?php echo $lang['action-edit']; ?></span>
                            </a>
                        </div>
                        <?php
                        }
                        ?>
                    </div>
				</div><!-- End of FeaturedSimpleTile_<?php echo $collection["ref"]; ?>-->
			<?php
			}
	}

include "../include/header.php";
?>

<script>
jQuery(document).ready(function ()
    {
    jQuery('.FeaturedSimpleTile').hover(
    function(e)
        {
        tileid = jQuery(this).attr('id').substring(19);
        jQuery('#FeaturedSimpleTileActions_' + tileid).stop(true, true).slideDown();
        },
    function(e)
        {
        tileid=jQuery(this).attr('id').substring(19);
        jQuery('#FeaturedSimpleTileActions_' + tileid).stop(true, true).slideUp();
        });
    });
</script>


<div class="BasicsBox FeaturedSimpleLinks">

<?php
/*
if(!hook('replacethemesbacklink'))
    {
    if($enable_theme_breadcrumbs && $themes_category_split_pages && isset($themes[0]) && !$theme_direct_jump)
        {
        $links_trail_params            = array();
        $links_trail_additional_params = array();

        if($simpleview)
            {
            $links_trail_params['simpleview'] = 'true';
            }

        $links_trail = array(
            array(
                'title' => $lang['themes'],
                'href'  => generateURL("{$baseurl_short}pages/themes.php", $links_trail_params)
            )
        );

        for($x = 0; $x < count($themes); $x++)
            {
            $links_trail_additional_params['theme' . (0 == $x ? '': $x + 1)] = $themes[$x];

            $links_trail[] = array(
                'title' => str_replace('*', '', i18n_get_collection_name($themes[$x])),
                'href'  => generateURL("{$baseurl_short}pages/themes.php", $links_trail_params, $links_trail_additional_params)
                );
            }

        if($themes_show_background_image)
            {
            ?>
            <div id="" class="BreadcrumbsBox">
            <?php
            renderBreadcrumbs($links_trail);
            ?>
            </div>
            <div class="clearerleft"></div>
            <?php
            }
        else
            {
            renderBreadcrumbs($links_trail);
            }
        }
    } # end hook('replacethemesbacklink')
*/

DisplayCollections($parent);

/*
if($themes_show_background_image)
    {
    $slideshow_files = get_slideshow_files_data();

    if(!$featured_collection_static_bg)
        {
        // Overwrite background_image_url with theme specific ones
        $background_theme_images = get_theme_image(0 < count($themes) ? $themes : array(''), '', $smart_theme!=='');
    
        if(is_array($background_theme_images) && 0 < count($background_theme_images))
            {
            foreach($background_theme_images as $background_theme_image)
                {
                if(file_exists(get_resource_path($background_theme_image, true, 'scr', false)))
                    {
                    $background_image_url = get_resource_path($background_theme_image, false, 'scr', false);

                    // Reset slideshow files as we want to use the featured collection image
                    $slideshow_files = array();
                    break;
                    }
                }
            }
        }
        ?>
    <script>
    var SlideshowImages = new Array();
    var SlideshowCurrent = -1;
    var SlideshowTimer = 0;
    var big_slideshow_timer = <?php echo $slideshow_photo_delay; ?>;

<?php
foreach($slideshow_files as $slideshow_file_info)
    {
    if((bool) $slideshow_file_info['featured_collections_show'] === false)
        {
        continue;
        }

    $image_download_url = "{$baseurl_short}pages/download.php?slideshow={$slideshow_file_info['ref']}";
    $image_resource = isset($slideshow_file_info['link']) ? $slideshow_file_info['link'] : '';
    ?>
    RegisterSlideshowImage('<?php echo $image_download_url; ?>', '<?php echo $image_resource; ?>');
    <?php
    }

if(!$featured_collection_static_bg && isset($background_image_url) && $background_image_url != '')
    {
    ?>
    RegisterSlideshowImage('<?php echo $background_image_url; ?>', '', true);
    <?php
    }
    ?>
    jQuery(document).ready(function() 
        {
        ClearTimers();
        ActivateSlideshow();
        });
    </script>
    <?php
    } /* End of show background image in simpleview mode*/
	

include "../include/footer.php";
