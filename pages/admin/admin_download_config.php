<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php'; if(!checkperm('a')) { exit('Permission denied.');}
include_once '../../include/config_functions.php';

if(!extension_loaded("zip"))
    {
    error_alert(str_replace("%%MODULE%%","php-zip",$lang["error_server_missing_module"]),true,200);
    exit();
    }
elseif(!$offline_job_queue)
    {
    error_alert(str_replace("%%CONFIG_OPTION%%","\$offline_job_queue",$lang["oj-check-config"]),true,200);
    exit();
    }
elseif(!isset($mysql_bin_path))
    {
    error_alert(str_replace("%%CONFIG_OPTION%%","\$mysql_bin_path",$lang["oj-check-config"]),true,200);
    exit();
    }

$export = getval("export","") != "";
if ($export!="" && enforcePostRequest(false))
	{
    // Create array of tables to export    
    $obfuscate = getval("obfuscate","") !== "";

    $exporttables = array();
    $exporttables["sysvars"] = array();
    $exporttables["preview_size"] = array();
    $exporttables["archive_states"] = array();
    $exporttables["workflow_actions"] = array();

    $exporttables["user"] = array();
    $exporttables["user"]["scramble"]=array("username"=>"mix_text","email"=>"mix_email","fullname"=>"mix_text","comments"=>"mix_text","created"=>"mix_date");
    $exporttables["user_preferences"] = array();

    $exporttables["usergroup"] = array();
    $exporttables["usergroup"]["scramble"]=array("name"=>"mix_text","welcome_message"=>"mix_text","search_filter"=>"mix_text","edit_filter"=>"mix_text");


    $exporttables["dash_tile"] = array();
    $exporttables["dash_tile"]["scramble"]=array("title"=>"mix_text","txt"=>"mix_text","url"=>"mix_text");
    $exporttables["user_dash_tile"] = array();
    $exporttables["usergroup_dash_tile"] = array();

    $exporttables["resource_type"] = array();
    $exporttables["resource_type_field"] = array();
    $exporttables["node"] = array();

    $exporttables["filter"] = array();
    $exporttables["filter"]["scramble"]=array("name"=>"mix_text");
    $exporttables["filter_rule"] = array();
    $exporttables["filter_rule_node"] = array();

    // Optional tables
    $exportcollection = getval("exportcollection",0,true);
    if($exportcollection != 0)
        {
        // Collections 
        $exporttables["collection"] = array();
        $exporttables["collection"]["exportcondition"] = "WHERE ref = '$exportcollection'";    
        $exporttables["collection"]["scramble"]=array("name"=>"mix_text","description"=>"mix_text","keywords"=>"mix_text","theme"=>"mix_text","theme2"=>"mix_text","theme3"=>"mix_text","theme4"=>"mix_text","theme5"=>"mix_text","created"=>"mix_date");

        $exporttables["user_collection"] = array();
        $exporttables["usergroup_collection"] = array();
        $exporttables["collection_resource"] = array(); 
        //  Resources and resource metadata
        $exporttables["resource"] = array();
        $exporttables["resource"]["scramble"]=array("field8"=>"mix_text","creation_date"=>"mix_date");
        $exporttables["resource"]["exportcondition"] = " WHERE ref IN (SELECT resource FROM collection_resource WHERE collection='$exportcollection')";
        $exporttables["resource_data"] = array();
        $exporttables["resource_data"]["scramble"]=array("value");
        $exporttables["resource_data"]["exportcondition"] = " WHERE resource IN (SELECT resource FROM collection_resource WHERE collection='$exportcollection')";
        $exporttables["resource_data"]["scramble"]=array("value"=>"mix_text");

        $exporttables["resource_node"] = array();
        $exporttables["resource_custom_access"] = array();
        $exporttables["resource_dimensions"] = array();
        $exporttables["resource_related"] = array();
        $exporttables["resource_alt_files"] = array();
        $exporttables["resource_alt_files"]["scramble"]=array("name"=>"mix_text","description"=>"mix_text","file_name"=>"mix_filename");
        $exporttables["annotation"] = array();
        $exporttables["annotation_node"] = array();
        }

    $extra_tables = hook("export_add_tables");
    if(is_array($extra_tables))
        {
        $exporttables = array_merge($exporttables,$extra_tables);
        }
    

    // Create offline job
    $job_data=array();
    $job_data["exporttables"]   = $exporttables;
    $job_data["obfuscate"]      = $obfuscate;
    $job_data["userref"]        = $userref;
    $job_code = "system_export_" . $userref; // unique code for this job, used to prevent duplicate job creation
    $jobadded=job_queue_add("config_export",$job_data,$userref,'',$lang["exportcomplete"],$lang["exportfailed"],$job_code);
    if($jobadded!==true)
        {
        $message = $lang["oj-creation-failure-text"] . " : " . $jobadded;  
        }
    else
        {
        $message = $lang["oj-creation-success"];
        }
    }


// This page will create an offline job that creates a zip file containing sytem configuration nformation and data
/*
- include/config.php
- sysvars table
- user_preferences table**
- user table*
- usergroup table
- resource_type table
- resource_type_field table
- resource_related table
- resource_data table*
- resource_custom_access table
- resource_alt_files table*
- preview_size table
- node table*
- filter, filter_rule and filter_rule_node tables. 
- external_access_keys table
- dash_tile table
- collection table
- archive_states table
- annotation and annotation_node tables
*/


include '../../include/header.php';
?>
<div class="BasicsBox">
    <p>
        <a href="<?php echo $baseurl_short; ?>pages/admin/admin_home.php" onClick="return CentralSpaceLoad(this, true);"><?php echo LINK_CARET_BACK ?><?php echo $lang['back']; ?></a>
    </p>
    <h1><?php echo $lang['exportdata']; ?></h1>
    <?php
    if (isset($message))
        {
        echo "<div class=\"PageInformal\">" . $message . "</div>";
        }
    ?>
    <p><?php echo $lang['exportdata-instructions']; render_help_link("admin/download-config");?></p>
    
    <form method="post" action="<?php echo $baseurl_short?>pages/admin/admin_download_config.php" onSubmit="return CentralSpacePost(this,true);">
        <input type="hidden" name="export" value="true" />
        <div class="Question">
            <label><?php echo $lang['exportobfuscate']; ?></label>
            <input type="checkbox" name="obfuscate" value="1" checked />
            <div class="clearerleft"> </div>
        </div>

        <div class="Question">
            <label><?php echo $lang['exportcollection']; ?></label>
            <input type="number" name="exportcollection"></input>
            <div class="clearerleft"> </div>
        </div>


        <div class="Question">
            <label for="export"></label>
            <input type="button" name="export" value="<?php echo $lang["export"]; ?>" onClick="jQuery(this.form).submit();">
            <div class="clearerleft"> </div>
        </div>



    </form>
    
</div>
<?php


include '../../include/footer.php';
