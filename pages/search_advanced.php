<?php
include_once "../include/db.php";
include_once RS_ROOT . "/include/general.php";
include RS_ROOT . "/include/authenticate.php"; if (!checkperm("s")) {exit ("Permission denied.");}
include_once RS_ROOT . "/include/search_functions.php";
include_once RS_ROOT . "/include/resource_functions.php";
include_once RS_ROOT . "/include/collections_functions.php";
include_once RS_ROOT . '/include/render_functions.php';

$filter_bar_reload = trim(getval('filter_bar_reload', '')) !== 'false' ? true : false;
if(!$filter_bar_reload)
    {
    http_response_code(204);
    exit();
    }

function get_search_default_restypes()
	{
	global $search_includes_resources, $collection_search_includes_resource_metadata, $search_includes_user_collections,
           $search_includes_public_collections, $search_includes_themes;
	$defaultrestypes=array();
	if($search_includes_resources)
		{
		$defaultrestypes[] = "Global";
		}
	  else
		{
		$defaultrestypes[] = "Collections";
		if($search_includes_user_collections){$defaultrestypes[] = "mycol";}
		if($search_includes_public_collections){$defaultrestypes[] = "pubcol";}
		if($search_includes_themes){$defaultrestypes[] = "themes";}
		}	
	return $defaultrestypes;
	}

function get_search_open_sections()
    {
    global $search_includes_resources, $collection_search_includes_resource_metadata;

    $advanced_search_section = getvalescaped('advancedsearchsection', '');

    if('' != $advanced_search_section || '' != getval('resetform', ''))
        {
        if (isset($default_advanced_search_mode)) 
            {
            $opensections = $default_advanced_search_mode;
            }
        else
            {
            if($search_includes_resources)
                {
                $opensections = array('Global', 'Media');
                }
            else
                {
                $opensections=array('Collections');
                }
            }
        }
    else
        {
        $opensections = explode(',', $advanced_search_section);
        }

    return $opensections;
    }

$selected_archive_states=array();


$archivechoices=getvalescaped("archive",getvalescaped("saved_archive",array(0)));
if(!is_array($archivechoices)){$archivechoices=explode(",",$archivechoices);}
foreach($archivechoices as $archivechoice)
    {
    if(is_numeric($archivechoice)) {$selected_archive_states[] = $archivechoice;}  
    }

$archive = implode(",", $selected_archive_states);
$archiveonly=count(array_intersect($selected_archive_states,array(1,2)))>0;

$starsearch=getvalescaped("starsearch","");	
rs_setcookie('starsearch', $starsearch,0,"","",false,false);

$opensections=get_search_open_sections();

# Disable auto-save function, only applicable to edit form. Some fields pick up on this value when rendering then fail to work.
$edit_autosave=false;
$reset_form = trim(getval("resetform", "")) !== "";

if (getval("submitted","")=="yes" && !$reset_form)
	{
	$restypes="";
	reset($_POST);foreach ($_POST as $key=>$value)
		{
		if (substr($key,0,12)=="resourcetype") {if ($restypes!="") {$restypes.=",";} $restypes.=substr($key,12);}
		if ($key=="hiddenfields") 
		    {
		    $hiddenfields=$value;
		    }
		}
	rs_setcookie('restypes', $restypes,0,"","",false,false);
		
	# advanced search - build a search query and redirect
	$fields=array_merge(get_advanced_search_fields(false, $hiddenfields ),get_advanced_search_collection_fields(false, $hiddenfields ));
  
	# Build a search query from the search form
	$search=search_form_to_search_query($fields);
	$search=refine_searchstring($search);
	hook("moresearchcriteria");

    $search_url = generateURL(
        "{$baseurl}/pages/search.php",
        array(
            'search'            => $search,
            'archive'           => $archive,
            'restypes'          => $restypes,
            'filter_bar_reload' => 'false',
            'source'            => getval("source", ""),
        ));
    ?>
    <html>
    <script>
    jQuery(document).ready(function ()
        {
        CentralSpaceLoad("<?php echo $search_url; ?>");
        UpdateActiveFilters({search: "<?php echo $search; ?>"});
        });
    </script>
    </html>
    <?php
    exit();
	}



# Reconstruct a values array based on the search keyword, so we can pre-populate the form from the current search
$search=@$_COOKIE["search"];
$keywords=split_keywords($search,false,false,false,false,true);
$allwords="";$found_year="";$found_month="";$found_day="";$found_start_date="";$found_end_date="";
$searched_nodes = array();

foreach($advanced_search_properties as $advanced_search_property=>$code)
  {$$advanced_search_property="";}
 
$values=array();
	
if($reset_form)
  { 
  $found_year="";$found_month="";$found_day="";$found_start_date="";$found_end_date="";$allwords="";$starsearch="";
  $restypes=get_search_default_restypes();
  $selected_archive_states=array(0);
  rs_setcookie("search","",0,"","",false,false);
  rs_setcookie("saved_archive","",0,"","",false,false);
  rs_setcookie("restypes", implode(",", $restypes), 0, "", "", false, false);

  if($header_search)
    {
    $search_url = generateURL(
        "{$baseurl}/pages/search.php",
        array(
            'search'   => '',
            'archive'  => implode(",", $selected_archive_states),
            'restypes' => implode(",", $restypes),
        ));
    ?>
    <html>
    <script>
    jQuery(document).ready(function ()
        {
        CentralSpaceLoad("<?php echo $search_url; ?>");
        });
    </script>
    </html>
    <?php
    exit();
    }
  }
else
  {
  if(getval("restypes","")=="")
	{$restypes=get_search_default_restypes();}
  else
		{$restypes=explode(",",getvalescaped("restypes",""));}

  for ($n=0;$n<count($keywords);$n++)
	  {
	  $keyword=trim($keywords[$n]);
	  if (strpos($keyword,":")!==false && substr($keyword,0,1)!="!")
		  {
            
          if(substr($keyword,0,1) =="\"" && substr($keyword,-1,1) == "\"")
            {
            $nk=explode(":",substr($keyword,1,-1));
            $name=trim($nk[0]);
            $keyword = "\"" . trim($nk[1]) . "\"";
            }
		  else
            {
            $nk=explode(":",$keyword);
            $name=trim($nk[0]);
            $keyword=trim($nk[1]);
            }
		  if ($name=="basicday") {$found_day=$keyword;}
		  if ($name=="basicmonth") {$found_month=$keyword;}
		  if ($name=="basicyear") {$found_year=$keyword;}
		  if ($name=="startdate") {$found_start_date=$keyword;}
		  if ($name=="enddate") {$found_end_date=$keyword;}
		  if (isset($values[$name])){$values[$name].=" ".$keyword;}
		  else
			 {
			 $values[$name]=$keyword;
			 }
		  }
	  elseif (substr($keyword,0,11)=="!properties")
		  {
		  $properties = explode(";",substr($keyword,11));
		  $propertyfields = array_flip($advanced_search_properties);
		  foreach($properties as $property)
			  {
			  $propertycheck=explode(":",$property);
			  $propertyname=$propertycheck[0];
			  $propertyval=escape_check($propertycheck[1]);
			  if($propertyval!="")
				{
				$fieldname=$propertyfields[$propertyname];
				$$fieldname=$propertyval;
				}
			  }
		  }
        // Nodes search
        else if(strpos($keyword, NODE_TOKEN_PREFIX) !== false)
            {
            $nodes = resolve_nodes_from_string($keyword);

            foreach($nodes as $node)
                {
                $searched_nodes[] = $node;
                }
            }
	  else
		  {
		  if ($allwords=="") {$allwords=$keyword;} else {$allwords.=", " . $keyword;}
		  }
	  }

    $allwords = str_replace(', ', ' ', $allwords);
  }
?>
<script type="text/javascript">

var resTypes=Array();
<?php

$types=get_resource_types();

for ($n=0;$n<count($types);$n++)
	{
	echo "resTypes[" .  $n  . "]=" . $types[$n]["ref"] . ";";
	}
?>
	
jQuery(document).ready(function()
    {
    selectedtypes=['<?php echo implode("','",$opensections) ?>'];
    if(selectedtypes[0]===""){selectedtypes.shift();}

    jQuery('.SearchTypeCheckbox').change(function() 
        {
        id=(this.name).substr(12);

       	//if has been checked
        if (jQuery(this).is(":checked")) {
            if (id=="Global") {
				selectedtypes=["Global"];
				//Hide specific resource type areas
				jQuery('.ResTypeSectionHead').hide();
				jQuery('.ResTypeSection').hide();
				
				// Global has been checked, check all other checkboxes
				jQuery('.SearchTypeItemCheckbox').prop('checked',true);
				//Uncheck Collections
				jQuery('#SearchCollectionsCheckbox').prop('checked',false);	

				jQuery('#AdvancedSearchTypeSpecificSectionGlobalHead').show();
				if (getCookie('AdvancedSearchTypeSpecificSectionGlobal')!="collapsed"){jQuery("#AdvancedSearchTypeSpecificSectionGlobal").show();}				
				jQuery('#AdvancedSearchMediaSectionHead').show();
				if (getCookie('AdvancedSearchMediaSection')!="collapsed"){jQuery("#AdvancedSearchMediaSection").show();}
			}
			else if (id=="Collections") {
				//Uncheck All checkboxes
                jQuery('.SearchTypeCheckbox').prop('checked',false);		

                //Check Collections
				selectedtypes=["Collections"];
				jQuery('#SearchCollectionsCheckbox').prop('checked',true);
				jQuery('.tickboxcoll').prop('checked',true);
				

				// Show collection search sections	
				jQuery('#AdvancedSearchTypeSpecificSectionCollectionsHead').show();
				if (getCookie('advancedsearchsection')!="collapsed"){jQuery("#AdvancedSearchTypeSpecificSectionCollections").show();}
            }
            else {	
				selectedtypes = jQuery.grep(selectedtypes, function(value) {return value != "Collections";});				
				selectedtypes.push(id);	

				//Hide specific resource type areas
				jQuery('.ResTypeSectionHead').hide();
				jQuery('.ResTypeSection').hide();
				
                jQuery('#SearchGlobal').prop('checked',false);
				jQuery('#SearchCollectionsCheckbox').prop('checked',false);		
				// Show global and media search sections	
                jQuery("#AdvancedSearchTypeSpecificSectionGlobalHead").show();
                if (getCookie('AdvancedSearchTypeSpecificSectionGlobal')!="collapsed"){jQuery("#AdvancedSearchTypeSpecificSectionGlobal").show();}
				jQuery('#AdvancedSearchMediaSectionHead').show();
				if (getCookie('AdvancedSearchMediaSection')!="collapsed"){jQuery("#AdvancedSearchMediaSection").show();}						
				
				// Show resource type specific search sections	if only one checked
				if(selectedtypes.length==1){
					if (getCookie('AdvancedSearchTypeSpecificSection'+id)!="collapsed"){jQuery('#AdvancedSearchTypeSpecificSection'+id).show();}
					jQuery('#AdvancedSearchTypeSpecificSection'+id+'Head').show();				
				}
			}
        }
        else {// Box has been unchecked
			if (id=="Global") {		
				selectedtypes=[];	
	     		jQuery('.SearchTypeItemCheckbox').prop('checked',false);
			}
			else if (id=="Collections") {
				selectedtypes=[];

				// Hide collection search sections	
				jQuery('#AdvancedSearchTypeSpecificSectionCollectionsHead').hide();
            }
			else {								
                jQuery('#SearchGlobal').prop('checked',false);
				
				//Hide specific resource type areas
				jQuery('.ResTypeSectionHead').hide();
				jQuery('.ResTypeSection').hide();
				
				// If global was previously checked, make sure all other types are now checked
				selectedtypes = jQuery.grep(selectedtypes, function(value) {return value != id;});
				if(selectedtypes.length==1){
					if (getCookie('AdvancedSearchTypeSpecificSection'+selectedtypes[0])!="collapsed") jQuery('#AdvancedSearchTypeSpecificSection'+selectedtypes[0]).show();
					jQuery('#AdvancedSearchTypeSpecificSection'+selectedtypes[0]+'Head').show();				
				}
			}
			//Always Show Global and media
			jQuery("#AdvancedSearchTypeSpecificSectionGlobalHead").show();
            if (getCookie('AdvancedSearchTypeSpecificSectionGlobal')!="collapsed"){jQuery("#AdvancedSearchTypeSpecificSectionGlobal").show();}
			jQuery('#AdvancedSearchMediaSectionHead').show();
			if (getCookie('AdvancedSearchMediaSection')!="collapsed"){jQuery("#AdvancedSearchMediaSection").show();}
		}

        SetCookie("advancedsearchsection", selectedtypes);
        UpdateResultCount();
        });
  /*  jQuery('.CollapsibleSectionHead').click(function() 
            {
            cur=jQuery(this).next();
            cur_id=cur.attr("id");
            if (cur.is(':visible'))
                {
                SetCookie(cur_id, "collapsed");
                jQuery(this).removeClass('expanded');
                jQuery(this).addClass('collapsed');
                }
            else
                {
                SetCookie(cur_id, "expanded")
                jQuery(this).addClass('expanded');
                jQuery(this).removeClass('collapsed');
                }
    
            cur.slideToggle();
           
            
            return false;
            }).each(function() 
                {
                    cur_id=jQuery(this).next().attr("id"); 
                    if (getCookie(cur_id)=="collapsed")
                        {
                        jQuery(this).next().hide();
                        jQuery(this).addClass('collapsed');
                        }
                    else jQuery(this).addClass('expanded');
    
                });*/
    
    });
</script>
<div class="BasicsBox">
<form method="post" id="advancedform" action="<?php echo $baseurl ?>/pages/search_advanced.php" >
<?php generateFormToken("advancedform"); ?>
<input type="hidden" name="submitted" id="submitted" value="yes">
<input type="hidden" name="source" value="filter_bar">

<script type="text/javascript">
var updating = false;
function UpdateResultCount()
	{
    updating = false;
    CentralSpacePost(document.getElementById('advancedform'), true, false, false);
    return;
	}
	
jQuery(document).ready(function(){
    // Detect which submit input was last called so we can figure out if we need to treat it differently (e.g when 
    // resetform is clicked and we are using filter bar we want to reload filter bar clearing all fields)
    var submit_caller_element = '';
    jQuery(":submit").click(function()
        {
        submit_caller_element = this.name;
        });

	    jQuery('#advancedform').submit(function(event) {
            if(submit_caller_element == 'resetform')
                {
                event.preventDefault();
                ClearFilterBar();
                return false;
                }

            if (jQuery('#AdvancedSearchTypeSpecificSectionCollections').is(":hidden")) 
                {
                    jQuery('.tickboxcoll').prop('checked',false);
                }
	       var inputs = jQuery('#advancedform :input');
	       var hiddenfields = Array();
	       inputs.each(function() {

	           if (jQuery(this).parent().is(":hidden")) hiddenfields.push((this.name).substr(6));
	           
	       });
	      jQuery("#hiddenfields").val(hiddenfields.toString());
	    
    	    
    	    	
	    });
		jQuery('.Question').easyTooltip({
			xOffset: -50,
			yOffset: 70,
			charwidth: 70,
			tooltipId: "advancedTooltip",
			cssclass: "ListviewStyle"
			});
		});

// Resource type fields information. Can be used to link client side actions with fields (e.g clearing active filters 
// in the filter bar need to clear the actual fields as well)
var resource_type_fields_data = [];
</script>
<div id="ActiveFilters" class="Question">
    <label><?php echo $lang["active_filters"]; ?></label>
    <div class="clearerleft"></div>
    <span id="ActiveFiltersList"></span>
    <div class="clearerleft"></div>
</div>
<?php
if($search_includes_resources && !hook("advsearchrestypes"))
    {
    ?>
    <div class="Question">
    <?php
    if(!$header_search)
        {
        ?>
        <label><?php echo $lang["search-mode"]?></label>
        <?php
        }
        $wrap = $header_search ? 5 : 0;
        ?>
        <table>
            <tr>
                <td valign=middle>
                    <input type=checkbox class="SearchTypeCheckbox" id="SearchGlobal" name="resourcetypeGlobal" value="yes" <?php if (in_array("Global",$restypes)) { ?>checked<?php }?>></td><td valign=middle><?php echo $lang["resources-all-types"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            <?php
            $hiddentypes=array();
            for ($n=0;$n<count($types);$n++)
                {
                if(in_array($types[$n]['ref'], $hide_resource_types))
                    {
                    continue;
                    }

                $wrap++;

                if($wrap > 4)
                    {
                    $wrap = $header_search ? 5 : 1;
                    ?>
                    </tr>
                    <tr>
                    <?php
                    }
                    ?>
                <td valign=middle>
                    <input type=checkbox class="SearchTypeCheckbox SearchTypeItemCheckbox" name="resourcetype<?php echo $types[$n]["ref"]?>" value="yes" <?php if (in_array("Global",$restypes) || in_array($types[$n]["ref"],$restypes)) {?>checked<?php } else $hiddentypes[]=$types[$n]["ref"]; ?>></td><td valign=middle><?php echo htmlspecialchars($types[$n]["name"])?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
                <?php
                }
    
    if($search_includes_user_collections || $search_includes_public_collections ||$search_includes_themes)
                {
                ?>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            <tr>
                <td valign=middle>
                    <input type=checkbox id="SearchCollectionsCheckbox" class="SearchTypeCheckbox" name="resourcetypeCollections" value="yes" <?php if (in_array("Collections",$restypes) || in_array("mycol",$restypes) || in_array("pubcol",$restypes) || in_array("themes",$restypes)) { ?>checked<?php }?>></td><td valign=middle><?php print $lang["collections"]; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
                <?php
                }
                ?>
            </tr>
        </table>
        <div class="clearerleft"></div>
    </div>
    <?php
    }

if(!hook('advsearchallfields'))
    {
    ?>
    <!-- Search across all fields -->
    <input type="hidden" id="hiddenfields" name="hiddenfields" value="">
    <input id="allfields" type="hidden" name="allfields" value="<?php echo htmlspecialchars($allwords); ?>" onChange="UpdateResultCount();">
    <?php
    }
    ?>

<?php
if(!hook('advsearchresid'))
    {
    ?>
    <div class="Question">
        <label for="resourceids"><?php echo $lang["resourceids"]?></label>
        <input id="resourceids" class="SearchWidth"
               type=text name="resourceids"
               value="<?php echo htmlspecialchars(getval("resourceids","")); ?>"
               onChange="UpdateResultCount();">
        <div class="clearerleft"></div>
    </div>
    <?php
    }

if(!hook('advsearchdate'))
    {
    if(!$daterange_search)
        {
        ?>
        <div class="Question">
            <label><?php echo $lang["bydate"]?></label>
            <select id="basicyear" name="basicyear" class="SearchWidth" style="width:120px;" onChange="UpdateResultCount();">
                <option value=""><?php echo $lang["anyyear"]?></option>
            <?php
            $y=date("Y");
            for($n = $minyear; $n <= $y; $n++)
                {
                $selected = ($n == $found_year ? "selected" : "");
                ?>
                <option <?php echo $selected; ?>><?php echo $n; ?></option>
                <?php
                }
                ?>
            </select>
            <select id="basicmonth" name="basicmonth" class="SearchWidth" style="width:120px;" onChange="UpdateResultCount();">
                <option value=""><?php echo $lang["anymonth"]?></option>
            <?php
            for($n = 1; $n <= 12; $n++)
                {
                $m=str_pad($n,2,"0",STR_PAD_LEFT);
                ?>
                <option <?php if ($n==$found_month) { ?>selected<?php } ?> value="<?php echo $m; ?>"><?php echo $lang["months"][$n-1]?></option>
                <?php
                }
                ?>
            </select>
            <select id="basicday" name="basicday" class="SearchWidth" style="width:120px;" onChange="UpdateResultCount();">
                <option value=""><?php echo $lang["anyday"]?></option>
            <?php
            for($n = 1; $n <= 31; $n++)
                {
                $m = str_pad($n, 2, "0", STR_PAD_LEFT);
                ?>
                <option <?php if ($n==$found_day) { ?>selected<?php } ?> value="<?php echo $m; ?>"><?php echo $m; ?></option>
                <?php
                }
                ?>
            </select>
            <div class="clearerleft"> </div>
        </div><!-- End of basic date question -->
        <?php
        }
    }

hook('advsearchaddfields');

$fields = get_advanced_search_fields($archiveonly);
$rtypes = get_resource_types();
$advanced_section_rendered = false;

foreach($fields as $key => $field)
    {
    $simple_search_flag = $field["simple_search"] == 1 ? true : false;
    $advanced_search_flag = $field["advanced_search"] == 1 ? true : false;

    if(!$advanced_section_rendered && !$simple_search_flag && $advanced_search_flag)
        {
        ?>
        <h1 class="CollapsibleSectionHead collapsed"><?php echo $lang["advanced"]; ?></h1>
        <div id="FilterBarAdvancedSection" class="CollapsibleSection">
        <?php
        $advanced_section_rendered = true;
        }

    $value = "";
    if(!$reset_form && array_key_exists($field["name"], $values))
        {
        $value = $values[$field["name"]];
        }

    render_search_field($field, $value, true, 'SearchWidth', false, array(), $searched_nodes);
    ?>
    <script>
    resource_type_fields_data[<?php echo $field["ref"]; ?>] = {
        ref: "<?php echo $field["ref"]; ?>",
        name: "<?php echo $field["name"]; ?>",
        type: "<?php echo $field["type"]; ?>",
    };
    </script>
    <?php
    if(($key === count($fields) - 1) && $advanced_section_rendered)
        {
        echo "</div> <!-- End of AdvancedSection -->";
        }
    }

global $advanced_search_archive_select;
if($advanced_search_archive_select)
	{
    // Create an array for the archive states
	$available_archive_states = array();
	$all_archive_states=array_merge(range(-2,3),$additional_archive_states);
	foreach($all_archive_states as $archive_state_ref)
		{
		if(!checkperm("z" . $archive_state_ref))
			{
			$available_archive_states[$archive_state_ref] = (isset($lang["status" . $archive_state_ref]))?$lang["status" . $archive_state_ref]:$archive_state_ref;
			}
		}
	?>
    
    <div class="Question" id="question_archive" >
		<label><?php echo $lang["status"]?></label>
		<table cellpadding=2 cellspacing=0>
            
            <?php
            foreach ($available_archive_states as $archive_state=>$state_name)
                {
                ?>
                  <tr>
                    <td width="1">
                   <input type="checkbox"
                          name="archive[]"
                          value="<?php echo $archive_state; ?>"
                          onChange="UpdateResultCount();"<?php 
                       if (in_array($archive_state,$selected_archive_states))
                           {
                           ?>
                           checked
                           <?php
                           }?>
                       >
               </td>
               <td><?php echo htmlspecialchars(i18n_get_translated($state_name)); ?>&nbsp;</td>
               </tr>
                <?php  
                }
            ?>
        </table>
    </div>
    <div class="clearerleft"></div>
    <?php
	}
else
	{?>
	<input type="hidden" name="archive" value="<?php echo htmlspecialchars($archive)?>">
	<?php
	}

if($advanced_search_contributed_by)
    {
    ?>
    <div class="Question">
        <label><?php echo $lang["contributedby"]; ?></label>
        <?php
        $single_user_select_field_value=$properties_contributor;
        $single_user_select_field_id='properties_contributor';
        $single_user_select_field_onchange='UpdateResultCount();';
    	$userselectclass="searchWidth";
        include "../include/user_select.php";
    	?>
        <script>
    	jQuery('#properties_contributor').change(function(){UpdateResultCount();});
    	</script>
    	<?php
        unset($single_user_select_field_value);
        unset($single_user_select_field_id);
        unset($single_user_select_field_onchange);
        ?>
    </div>
    <?php
    }
?>

<?php if  ($search_includes_user_collections || $search_includes_public_collections || $search_includes_themes) { ?>
<h1 class="AdvancedSectionHead CollapsibleSectionHead" id="AdvancedSearchTypeSpecificSectionCollectionsHead" <?php if (!in_array("Collections",$opensections) && !$collection_search_includes_resource_metadata) {?> style="display: none;" <?php } ?>><?php echo $lang["collections"]; ?></h1>
<div class="AdvancedSection" id="AdvancedSearchTypeSpecificSectionCollections" <?php if (!in_array("Collections",$opensections) && !$collection_search_includes_resource_metadata) {?> style="display: none;" <?php } ?>>

<script type="text/javascript">	
function resetTickAllColl(){
	var checkcount=0;
	// set tickall to false, then check if it should be set to true.
	jQuery('.rttickallcoll').prop('checked',false);
	var tickboxes=jQuery('#advancedform .tickboxcoll');
		jQuery(tickboxes).each(function (elem) {
            if( tickboxes[elem].checked){checkcount=checkcount+1;}
        });
	if (checkcount==tickboxes.length){jQuery('.rttickallcoll').prop('checked',true);}	
}
</script>
<div class="Question">
<label><?php echo $lang["scope"]?></label><?php

$types=get_resource_types();
$wrap=0;
?>
<table><tr>
<td align="middle"><input type='checkbox' class="rttickallcoll" id='rttickallcoll' name='rttickallcoll' <?php if (in_array("Collections",$restypes)) {?> checked <?php } ?> onclick='jQuery("#advancedform .tickboxcoll").each (function(index,Element) {jQuery(Element).prop("checked",(jQuery(".rttickallcoll").prop("checked")));}); UpdateResultCount(); ' /><?php echo $lang['allcollectionssearchbar']?></td>

<?php
$clear_function="";
if ($search_includes_user_collections) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxMyCol" type="checkbox" name="resourcetypemycol" value="yes" <?php if ((count($restypes)==1 && $restypes[0]=="Collections") || in_array("mycol",$restypes)) {?>checked="checked"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/><?php echo $lang["mycollections"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxMyCol').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
if ($search_includes_public_collections) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxPubCol" type="checkbox" name="resourcetypepubcol" value="yes" <?php if ((count($restypes)==1 && $restypes[0]=="Collections") || in_array("pubcol",$restypes)) {?>checked="checked"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/><?php echo $lang["findpubliccollection"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxPubCol').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
if ($search_includes_themes) 
    { ?>
    <td align="middle"><?php if ($searchbar_selectall){ ?>&nbsp;&nbsp;<?php } ?><input class="tickboxcoll" id="TickBoxThemes" type="checkbox" name="resourcetypethemes" value="yes" <?php if ((count($restypes)==1 && $restypes[0]=="Collections") || in_array("themes",$restypes)) {?>checked="checked"<?php } ?>onClick="resetTickAllColl();" onChange="UpdateResultCount();"/><?php echo $lang["findcollectionthemes"]?></td><?php	
    $clear_function.="document.getElementById('TickBoxThemes').checked=true;";
    $clear_function.="resetTickAllColl();";
    }
?>
</tr></table></div>
<script type="text/javascript">resetTickAllColl();</script>
<?php
if (!$collection_search_includes_resource_metadata)
   {
 $fields=get_advanced_search_collection_fields();
 for ($n=0;$n<count($fields);$n++)
	 {
	 # Work out a default value
	 if (array_key_exists($fields[$n]["name"],$values)) {$value=$values[$fields[$n]["name"]];} else {$value="";}
	 if (getval("resetform","")!="") {$value="";}
	 # Render this field
	 render_search_field($fields[$n],$value,true,"SearchWidth",false,array(),$searched_nodes);
	 }
   }
?>
</div>

<?php
}

if($advanced_search_media_section)
    {
    ?>
    <h1 class="AdvancedSectionHead CollapsibleSectionHead" id="AdvancedSearchMediaSectionHead" ><?php echo $lang["media"]; ?></h1>
    <div class="AdvancedSection" id="AdvancedSearchMediaSection">
    <?php 
    render_split_text_question($lang["pixel_height"], array('media_heightmin'=>$lang['from'],'media_heightmax'=>$lang['to']),$lang["pixels"], true, " class=\"stdWidth\" OnChange=\"UpdateResultCount();\"", array('media_heightmin'=>$media_heightmin,'media_heightmax'=>$media_heightmax));
    render_split_text_question($lang["pixel_width"], array('media_widthmin'=>$lang['from'],'media_widthmax'=>$lang['to']),$lang["pixels"], true, " class=\"stdWidth\" OnChange=\"UpdateResultCount();\"", array('media_widthmin'=>$media_widthmin,'media_widthmax'=>$media_widthmax));
    render_split_text_question($lang["filesize"], array('media_filesizemin'=>$lang['from'],'media_filesizemax'=>$lang['to']),$lang["megabyte-symbol"], false, " class=\"stdWidth\" OnChange=\"UpdateResultCount();\"", array('media_filesizemin'=>$media_filesizemin,'media_filesizemax'=>$media_filesizemax));
    render_text_question($lang["file_extension_label"], "media_fileextension", "",false," class=\"SearchWidth\" OnChange=\"UpdateResultCount();\"",$media_fileextension);
    render_dropdown_question($lang["previewimage"], "properties_haspreviewimage", array(""=>"","1"=>$lang["yes"],"0"=>$lang["no"]), $properties_haspreviewimage, " class=\"SearchWidth\" OnChange=\"UpdateResultCount();\"");
    ?>
    </div><!-- End of AdvancedSearchMediaSection -->
    <?php
    }
    ?>
        <div class="QuestionSubmit">
            <label for="buttons"></label>
            <input class="resetform FullWidth" name="resetform" type="submit" form="advancedform" value="<?php echo $lang["clearbutton"]; ?>">
        </div>
    </form>
</div> <!-- BasicsBox -->
<script>
function ClearFilterBar()
    {
    var url = "<?php echo generateURL("{$baseurl}/pages/search_advanced.php", array('submitted' => true, 'resetform' => true)); ?>";
    jQuery("#FilterBarContainer").load(url);
    document.getElementById('ssearchbox').value='';
    }

jQuery(document).ready(function()
    {
    UpdateActiveFilters({search: "<?php echo $search; ?>"});
    jQuery("#FilterBarContainer .Question table").PutShadowOnScrollableElement();
    registerCollapsibleSections(false);
    });
</script>