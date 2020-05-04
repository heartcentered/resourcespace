<?php
/**
 * CSV upload * 
 * @package ResourceSpace
 */

include dirname(__FILE__)."/../../../include/db.php";
include_once dirname(__FILE__)."/../../../include/general.php";
include dirname(__FILE__)."/../../../include/authenticate.php";
include_once dirname(__FILE__)."/../../../include/resource_functions.php";
include_once dirname(__FILE__)."/../../../include/search_functions.php";
include_once dirname(__FILE__)."/../../../include/collections_functions.php";
include_once (dirname(__FILE__)."/../include/meta_functions.php");
include_once (dirname(__FILE__)."/../include/csv_functions.php");
	
$fd="user_{$userref}_uploaded_meta";			// file descriptor for uploaded file					// TODO: push these to a config file?
$override_fields=array("status","access");		// user can set if empty or override these fields
//$process_csv            = (getvalescaped("process_csv","")!="" && enforcePostRequest(false));
$allfields              = get_resource_type_fields();
$override               = getvalescaped("override","");


$csv_set_options = array();
$csv_saved_options = getval("saved_csv_options","");
if($csv_saved_options != "")
    { 
    $csv_set_options = json_decode($csv_saved_options, true);
    }

$csv_settings = array(
    "add_to_collection" => 0,
    "csv_update_col" => 0,
    "csv_update_col_id" => 0,
    "update_existing" => 0,
    "id_column" => "",
    "id_field" => 0,
    "id_column_match" => 0,
    "multiple_match" => 0,
    "resource_type_column" => "",
    "resource_type_default" => 0,
    "status_column" => "",
    "status_default" => 0,
    "access_column" => "",
    "access_default" => 0,
    "fieldmapping" => array()
    );

foreach($csv_settings as $csv_setting => $csv_setting_default)
    {
    $setoption = isset($_POST[$csv_setting]) ? $_POST[$csv_setting] : "";
    if($setoption != "")
        {
        $csv_set_options[$csv_setting] = $setoption;
        }
    elseif(!isset($csv_set_options[$csv_setting]))
        {
        $csv_set_options[$csv_setting] = $csv_setting_default;
        }
    }



$selected_columns = array();
$selected_columns[] = $csv_set_options["resource_type_column"];
$selected_columns[] = $csv_set_options["id_column"];
$selected_columns[] = $csv_set_options["status_column"];
$selected_columns[] = $csv_set_options["access_column"];
array_filter($selected_columns);

rs_setcookie("saved_csv_options",json_encode($csv_set_options));

$csvdir     = get_temp_dir() . DIRECTORY_SEPARATOR . "csv_upload" . DIRECTORY_SEPARATOR . $session_hash;
if(!file_exists($csvdir))
    {
    mkdir($csvdir,0777,true);
    }
$csvfile    = $csvdir . DIRECTORY_SEPARATOR  . "csv_upload.csv";
if(isset($_FILES[$fd]) && $_FILES[$fd]['error'] == 0)
    {
    // We have a valid CSV, save it to a temporary location	for processing
	// Create target dir if necessary
	if (!file_exists($csvdir))
        {
        mkdir($csvdir,0777,true);
        }
    $result=move_uploaded_file($_FILES[$fd]['tmp_name'], $csvfile);
    }

$csvuploaded = file_exists($csvfile);


$csvstep = $csvuploaded ? getval("csvstep",1,true) : 1;
if($csvuploaded)
    {
    $csv_info = csv_upload_get_info($csvdir . DIRECTORY_SEPARATOR  . "csv_upload.csv",$messages);
    }


include dirname(__FILE__)."/../../../include/header.php";

?>

<?php
if (!checkperm("c"))
	{	
	echo "<div class=\"BasicsBox\">" . $lang['csv_upload_error_no_permission'] . "</div>";	
	include dirname(__FILE__)."/../../../include/footer.php";
	return;
	}
?>

<div class="BasicsBox">
<h1><?php echo $lang['csv_upload_nav_link']; ?></h1>

<script>
selectedcolumns = new Array(<?php echo ($selected_columns > 0) ? "'" . htmlspecialchars(implode(',',$selected_columns)) . "'" : ""; ?>);

jQuery('document').ready(function()
    {
    // Record the existing values to re-enable once deselected
    jQuery('.columnselect').each(function()
        {
        jQuery(this).attr("prev",this.value);
        });

    jQuery('.columnselect').change(function()
        {
        selcolumn  = this.value;
        prevcolumn = jQuery(this).attr("prev");
        
        console.log("selected: " + selcolumn);
        console.log("prev:      " + prevcolumn);
        
        jQuery(this).attr("prev",selcolumn);
        if(prevcolumn != selcolumn)
            {
            var index = selectedcolumns.indexOf(prevcolumn);
            if (index !== -1) 
                {
                selectedcolumns.splice(index, 1);
                }
            }
        if(!selectedcolumns.includes(selcolumn))
            {
            selectedcolumns.push(selcolumn);
            }

        // Re-enable options
        jQuery('.columnselect option').prop("disabled", false);

        // Disable selected column options in other inputs
        var colCount = selectedcolumns.length;
        for (var i = 0; i < colCount; i++) 
            {
            jQuery('.columnselect option[value="' + selectedcolumns[i] + '"]')
            .not("option:selected",this)
            .prop("disabled", true)
            }
        });
    });
</script>
<?php

// TODO set vars to use column IDs instead of names
// Add option to download/save mapping configs
// KB Article



# ----- we do not have a successfully submitted csv, so show the upload form and exit -----
echo $csvstep . "<br/>";

echo "<pre>" . print_r($csv_set_options) . "</pre>";

$restypearr = get_resource_types();
$resource_types = array();
// Sort into array with ids as keys
foreach($restypearr as $restype)
    {
    $resource_types[$restype["ref"]] = $restype;
    }

switch($csvstep)
    {
    case 1:
        // Step 1 - No file yet selected
        // Once selected, choose to update existing data or create new resources
        echo $lang["csv_upload_intro"];
        echo $lang["csv_upload_encoding_notice"];
        echo "<ul>";
        $condition=1;
        while(isset($lang["csv_upload_condition" . $condition]))
            {
            echo $lang["csv_upload_condition" . $condition];
            $condition++;
            }
        echo "</ul>";
        ?>
        <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data" >
            <?php generateFormToken("upload_csv_form"); ?>
            <input type="hidden" id="csvstep" name="csvstep" value="2" > 			
            <div class="Question">
                <label for="<?php echo $fd; ?>"><?php echo $lang['csv_upload_file'] ?></label>
                <input type="file" id="<?php echo $fd; ?>" name="<?php echo $fd; ?>" onchange="if(this.value==null || this.value=='') { jQuery('.file_selected').hide(); } else { jQuery('.file_selected').show(); } ">	
                <div class="clearerleft"> </div>
            </div>	
            
            <div class="file_selected Question" style="display: none;">
                <input id="update_existing" name="update_existing" type=hidden value="0">
                                       
                <label for="update_existing_option"><?php echo $lang["csv_upload_update_existing"] ?></label>
                <input type="checkbox" id="update_existing_option" name="update_existing_option" onchange="if(this.value==null || this.value=='') {jQuery('#update_existing').val('0'); } else {jQuery('#update_existing').val('1');}" >
                <div class="clearerleft"> </div>
            </div>

            <div class="file_selected Question" style="display: none;">
                <label for="submit" class="file_selected" style="display: none;"></label>
                <input type="submit" id="submit" value="<?php echo $lang["next"]; ?>" class="file_selected" style="display: none;"> 
                <div class="clearerleft"> </div>
            </div>    
        </form>
        <?php
        break;
    case 2:
        if(!$csv_set_options["update_existing"])
            {
            // Step 2(a) Create new resources
            echo "<p>Step 2 - Create new resources</p>";
            ?>
            <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data" onSubmit="return CentralSpacePost(this,true);">
                <?php generateFormToken("upload_csv_form"); ?>
                <input type="hidden" id="csvstep" name="csvstep" value="3" > 
                <div class="Question">
                    <label for="add_to_collection"><?php echo $lang['addtocollection'] ?></label>
                    <input type="checkbox" id="add_to_collection" name="add_to_collection" value="1"<?php if($csv_set_options["add_to_collection"] != ""){echo " checked ";}?>>	
                </div>

                <div class="Question" id="resource_type_question">
                    <label for="resource_type_column">CSV resource type column</label>
                    <select id="resource_type_column" name="resource_type_column"  class="stdwidth columnselect">                    
                        <option value="">Select</option>
                        <?php
                        foreach($csv_info as $csv_column => $csv_field_data)
                            {
                            echo "<option value=\"" . $csv_column . "\" " . (($csv_set_options["resource_type_column"] != "" && $csv_set_options["resource_type_column"] == $csv_column) || strtolower($csv_field_data["header"]) == strtolower($lang["resourcetype"]) ? " selected " : "") . ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            }
                            ?>
                    </select>
                </div>

                <div class="Question" id="resource_type_default_question">
                    <label for="resource_type_default">Default resource type if no column selected or no valid type found in column</label>
                    <select id="resource_type_default" name="resource_type_default" class="stdwidth" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('.override').hide();jQuery('.override').attr('disabled','disabled'); } else { jQuery('.override').removeAttr('disabled');jQuery('.override').show(); }">                                     
                            <option value="0">Select..</option>
                            <?php   
                            foreach ($resource_types as $resource_type)
                                {
                                ?><option value="<?php echo $resource_type["ref"]; ?>" <?php if($csv_set_options["resource_type_default"] == $resource_type["ref"]){echo " selected ";}?>><?php echo htmlspecialchars($resource_type["name"]); ?></option>                                   
                                <?php
                                }
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>



                <div class="Question" id="status_question">
                    <label for="status_column">CSV workflow state column</label>
                    <select id="status_column" name="status_column"  class="stdwidth columnselect">                    
                        <option value="">Select</option>
                        <?php
                        foreach($csv_info as $csv_column => $csv_field_data)
                            {
                            echo "<option value=\"" . $csv_column . "\" " . (($csv_set_options["status_column"] == $csv_column || strtolower($csv_field_data["header"]) == strtolower($lang["status"])) ? " selected " : "") . ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            }
                            ?>
                    </select>
                </div>

                <div class="Question" id="status_default_question">
                    <label for="status_default">Default workflow state if no column selected or no valid type found in column</label>
                    <select id="status_default" name="status_default" class="stdwidth" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('.override').hide();jQuery('.override').attr('disabled','disabled'); } else { jQuery('.override').removeAttr('disabled');jQuery('.override').show(); }">                                     
                            <option value="0">Select..</option>
                            <?php   
                            $workflow_states = get_editable_states($userref);
                            foreach($workflow_states as $workflow_state)
                                {
                                ?><option value="<?php echo $workflow_state["id"]; ?>" <?php if($csv_set_options["status_default"] == $workflow_state["id"]){echo " selected ";} ?>><?php echo htmlspecialchars($workflow_state["name"]); ?></option>                                   
                                <?php
                                }
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>

                <div class="Question" id="access_question">
                    <label for="access_column">CSV access column</label>
                    <select id="access_column" name="access_column" class="stdwidth columnselect">                    
                        <option value="">Select</option>
                        <?php
                        foreach($csv_info as $csv_column => $csv_field_data)
                            {
                            echo "<option value=\"" . $csv_column . "\" ";
                            if(
                                ($csv_set_options["access_column"] != "" && $csv_set_options["access_column"] == $csv_column)
                                || 
                                strtolower($csv_field_data["header"]) == strtolower($lang["access"])
                                )
                                {
                                echo " selected ";
                                }
                            echo  ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            }
                            //echo "<option value=\"" . $csv_column . "\" " . (($csv_set_options["access_column"] == $csv_column || strtolower($csv_field_data["header"]) == strtolower($lang["access"])) ? " selected " : "") . ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            ?>
                    </select>
                </div>

                <div class="Question" id="access_default_question">
                    <label for="access_default">Default workflow state if no column selected or no valid type found in column</label>
                    <select id="access_default" name="access_default" class="stdwidth" onchange="if (this.options[this.selectedIndex].value=='default') { jQuery('.override').hide();jQuery('.override').attr('disabled','disabled'); } else { jQuery('.override').removeAttr('disabled');jQuery('.override').show(); }">                                     
                            <option value="0">Select..</option>
                            <?php   
                             // Get applicable access options - custom access omitted as can be added by batch editing later
                            for($n=0;$n<3;$n++)
                                {
                                if(!checkperm("ea" . $n) || checkperm("v"))
                                    {
                                    echo "<option value=\"" . $n . "\" " . (($csv_set_options["access_default"] == $n) ? " selected " : "") . ">" . htmlspecialchars($lang["access" . $n]) . "</option>\n";
                                    }
                                }
                                ?>
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>
                

                <div class="Question">
                    <label for="submit"></label>
                    <input type="button" id="back" value="<?php echo $lang["back"]; ?>"  onClick="CentralSpaceLoad('<?php echo generateURL($_SERVER["SCRIPT_NAME"],array("csvstep"=>$csvstep-1)); ?>',true);return false;" > 
                    <input type="submit" id="submit" value="<?php echo $lang["next"]; ?>">
                <div class="clearerleft"> </div>
                </div>   
            </form>
            <?php
            }
        else
            {
            // Step 2(b) Update existing
            //          - Update collection only? (collection select if chosen)
            //          - Select resource identifier column 
            //          - Is it a resource ID or metadata field
            //              - Duplicate match handling - Update all or none and report at end
            // Step 4 - Match metadata fields to columns - option to load previously saved mappings from user preferences

            echo "<p>Step 2 - Update existing resources</p>";

            ?>
            
            
            <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data" onSubmit="return CentralSpacePost(this,true);" >
            <?php generateFormToken("upload_csv_form"); ?>
            <input type="hidden" id="csvstep" name="csvstep" value="3" > 

                <div class="Question">
                    <label for="csv_update_col"><?php echo $lang["csv_upload_update_existing_collection"] ?></label>
                    <input id="csv_update_col" name="csv_update_col" type=hidden value="<?php echo $csv_set_options["csv_update_col"]; ?>">
                    <input type="checkbox" name="csv_update_col_select" onchange="if(this.checked) { jQuery('#csv_update_col_id_select').show(); jQuery('#csv_update_col').val('1');} else { jQuery('#csv_update_col_id_select').hide(); jQuery('#csv_update_col').val('0'); }" <?php if($csv_set_options["csv_update_col"]){echo " checked"; }; ?>>	
                    
                    <div class="clearerleft"> </div>
                    
                    <div id="csv_update_col_id_select" <?php if($csv_set_options["csv_update_col"] == 0) {echo "style='display:none;' ";} ?>>
                        <label for="csv_update_col_id"></label>
                        <?php
                        render_user_collection_select("csv_update_col_id",array(),$csv_set_options["csv_update_col_id"],"stdwidth");
                        ?>
                    </div>
                    <div class="clearerleft"> </div>
                </div>

                <div class="Question" id="resource_type_question" >
                    <label for="resource_type_column">CSV resource type column (leave unset if not included)</label>
                    <select id="resource_type_column" name="resource_type_column" class="stdwidth columnselect">                    
                        <option value="">Don't change</option>
                        <?php
                        foreach($csv_info as $csv_column => $csv_field_data)
                            {
                            //echo "<option value=\"" . $csv_column . "\" " . ((($csv_set_options["resource_type_column"] != "" && $csv_set_options["resource_type_column"] == $csv_column) || strtolower($csv_field_data["header"])) == strtolower($lang["resourcetype"]) ? " selected " : "") . ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            echo "<option value=\"" . $csv_column . "\" ";
                            if(
                                ($csv_set_options["resource_type_column"] != "" && $csv_set_options["resource_type_column"] == $csv_column)
                                || 
                                strtolower($csv_field_data["header"]) == strtolower($lang["resourcetype"])
                                )
                                {
                                echo " selected ";
                                }
                            echo  ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            }
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>

                <div class="Question" id="id_column_question">
                    <label for="id_column">CSV resource identifier column</label>
                        <select id="id_column" name="id_column" class="stdwidth columnselect">                    
                        <option value="">Select</option>
                        <?php
                        foreach($csv_info as $csv_column => $csv_field_data)
                            {
                            echo "<option value=\"" . $csv_column . "\" ";
                           //echo "<option value=\"" . $csv_column . "\"  " . ($csv_set_options["id_column"] == $csv_column ? " selected " : "") . ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            if(
                                ($csv_set_options["id_column"] != "" && $csv_set_options["id_column"] == $csv_column)
                                || 
                                strtolower($csv_field_data["header"]) == strtolower($lang["resourceids"])
                                )
                                {
                                echo " selected ";
                                }
                            echo  ">" . htmlspecialchars($csv_field_data["header"]) . "</option>\n";
                            }
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>

                <div class="Question" id="id_column_match_question">
                    <label for="id_column_match">Match resource based on resource ID or metadata field value?</label>
                    <select id="id_column_match" name="id_column_match" class="stdwidth" onchange="if (this.value==0) { jQuery('#multiple_match_question').hide();} else { jQuery('#multiple_match_question').show(); }">
                            <option value="0">Resource ID</option>
                            <?php   
                            foreach($allfields as $field)
                                {
                                echo "<option value='" . $field["ref"] . "' " . ($csv_set_options["id_column_match"]  == $field["ref"] ? " selected " : "") . " >" . $field["title"] . "</option>\n";
                                }
                            ?>
                    </select>
                    <div class="clearerleft"> </div>
                </div>
                
                
                <div class="Question" id="multiple_match_question"  style="display: none;">
                    <label for="multiple_match">Action to take if multiple matching resources are found</label>
                    <select id="multiple_match" name="multiple_match" class="stdwidth">                                     
                            <option value="0" <?php if($csv_set_options["multiple_match"] == 0){echo " selected ";} ?>>Update none</option>                                    
                            <option value="1" <?php if($csv_set_options["multiple_match"] == 1){echo " selected ";} ?>>Update all matching</option>
                    </select>
                    <div class="clearerleft"> </div>
                </div>   


                <div class="Question">
                    <label for="submit"></label>
                    <input type="button" id="back" value="<?php echo $lang["back"]; ?>"  onClick="CentralSpaceLoad('<?php echo generateURL($_SERVER["SCRIPT_NAME"],array("csvstep"=>$csvstep-1)); ?>',true);return false;" > 
                    <input type="submit" id="submit" value="<?php echo $lang["next"]; ?>">
                    <div class="clearerleft"> </div>
                </div>
            </form>
            <?php
            }
        break;
    case 3:
        // Map metadata
        if(is_array($csv_info))
            {
            // Render each header with an option to map to a field
            // - resource type - (name, or number)
            // - status (name or number)
            // - archive (aname or nbumber)
            // - all fields - pre select if name matches title or shortname
            ?>
            <div class="BasicsBox">
                <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data" onSubmit="return CentralSpacePost(this,true);">
                <?php generateFormToken("upload_csv_form"); ?>
                <input type="hidden" id="csvstep" name="csvstep" value="4" > 
                <div class="Listview">
                    <table id="csv_upload_table" border="0" cellspacing="0" cellpadding="0" class="ListviewStyle">
                    <tr class="ListviewTitleStyle"> 
                        <th>Column Header</th>
                        <th>Field</th>
                        <th>Sample data</th>
                    </tr>

                    <?php
                    foreach($csv_info as $csv_column => $csv_field_data)
                        {
                        if(in_array($csv_column,$selected_columns))
                            {
                            continue;
                            }
                        echo "<tr>";
                        echo "<td><div class='fixed medwidth' >". htmlspecialchars($csv_field_data["header"]) . "</div></td>\n";
                        echo "<td><select name='fieldmapping[" . $csv_column  . "]' class='stdwidth columnselect'>";
                        echo "<option value=''>DO NOT USE</option>";
                        foreach($allfields as $field)
                            {
                            echo "<option value=\"" . $field["ref"] . "\" ";
                            if(
                                (isset($csv_set_options["fieldmapping"][$csv_column]) && $csv_set_options["fieldmapping"][$csv_column] == $field["ref"])
                                || 
                                in_array(mb_strtolower($csv_field_data["header"]), array(mb_strtolower($field["name"]),mb_strtolower($field["title"])))
                                )
                                {
                                echo " selected ";
                                }
                            echo  ">" . htmlspecialchars($field["title"]) . "</option>\n";


                           // echo "<option value='" . $field["ref"] . "' " . (in_array(mb_strtolower($csv_field_data["header"]), array(mb_strtolower($field["name"]),mb_strtolower($field["title"]))) ? " selected " : "") . " >" . $field["title"] . "</option>\n";
                            }
                        echo "</select></td>";
                        echo "<td>[" . htmlspecialchars(implode("],[",array_slice(array_filter($csv_field_data["values"]),0,5))) . "]</td>";
                        echo "</tr>";
                        }
                    ?>
                    </table>
                </div> 
                <div class="Question">
                    <label for="submit"></label>
                    <input type="button" id="back" value="<?php echo $lang["back"]; ?>"  onClick="CentralSpaceLoad('<?php echo generateURL($_SERVER["SCRIPT_NAME"],array("csvstep"=>$csvstep-1)); ?>',true);return false;" > 
                    <input type="submit" id="submit" value="<?php echo $lang["next"]; ?>">
                    <div class="clearerleft"> </div>
                </div>    
            </form>
            </div>
            <?php
            }
        else
            {
            exit("No data found");
            }
        break;
    case 4:
        // Test file processing
        $meta=meta_get_map();
        $messages=array();
        csv_upload_process($csvfile,$meta,$resource_types,$messages,100,false,$csv_set_options);
        ?>
        <div class="BasicsBox">
                <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" id="upload_csv_form" method="post" enctype="multipart/form-data" onSubmit="return CentralSpacePost(this,true);">
                <?php generateFormToken("upload_csv_form"); ?>
                <input type="hidden" id="csvstep" name="csvstep" value="5" > 
                <textarea rows="20" cols="100">
                <?php 
                foreach ($messages as $message)
                        {
                        echo $message . PHP_EOL;
                        } ?>
                </textarea>
                <div class="Question">
                    <label for="submit"></label>
                    <input type="button" id="back" value="<?php echo $lang["back"]; ?>"  onClick="CentralSpaceLoad('<?php echo generateURL($_SERVER["SCRIPT_NAME"],array("csvstep"=>$csvstep-1)); ?>',true);return false;" > 
                    <input type="submit" id="submit" value="<?php echo $lang["csv_upload_process"]; ?>">
                    <div class="clearerleft"> </div>
                </div>    
            </form>
            </div>
        <?php     

        break;

    case 5:
        // Process file
        $meta=meta_get_map();
        $messages=array();
        csv_upload_process($csvfile,$meta,$resource_types,$messages,1,true,$csv_set_options);
        ?>
        <textarea rows="20" cols="100">
        <?php 
        foreach ($messages as $message)
                {
                echo $message . PHP_EOL;
                } ?>
        </textarea>
        <?php     

        break;
    default:
    break;
	}


?>
</div><!-- end of BasicsBox -->
<?php

include dirname(__FILE__)."/../../../include/footer.php";

