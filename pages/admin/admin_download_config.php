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

function random_char()
    {
    $hex_code = dechex(mt_rand(195, 202));    
    $hex_code .= dechex(mt_rand(128, 175));
    return pack('H*', $hex_code);
    }

function validateDatetime($datestring, $format = 'Y-m-d H:i:s')
    {
    $date = DateTime::createFromFormat($format, $datestring);
    return $date && $date->format($format) == $datestring;
    }

function mix_date($fromdate)
    {
    global $mixcache;
    if(isset($mixcache[md5($fromdate)]))
        {
        return $mixcache[md5($fromdate)];
        }

    if(trim($fromdate==""))
        {
        $tstamp = time();
        }
    else
        {
        $date = new DateTime($fromdate);
        $tstamp = strtotime($fromdate);
        }

    $dateshift = 60*60*24*30; // How much should dates be moved
    $newstamp = $tstamp + (mt_rand(-$dateshift,$dateshift));
    $newdate = date('Y-m-d H:i:s',$newstamp);
    echo "Converted date " . $fromdate . " to " . $newdate  ."<br/>\n";

    // Update cache
    $mixcache[md5($fromdate)] = $newdate;

    return $newdate;
    }
 
function mix_text($string, $recurse=true)
    {
    global $mixcache;
    if(isset($mixcache[md5($string)]))
        {
        return $mixcache[md5($string)];
        }
    
    echo "Converting string<br/>" . $string . ", recurse=" . ($recurse ? "TRUE" : "FALSE") . "<br/>";

    // Check if another function is better
    if(validateDatetime($string) && $recurse)
        {
        echo "this is a date - calling mix_date()<br/>";
        return mix_date($string);
        }
    elseif(strpos($string,"http") === 0 && $recurse)
        {
        echo "this is a URL - calling mix_url()<br/>";
        return mix_url($string);
        }
    elseif(strpos($string," ") === false && strpos($string,".") != false && $recurse)
        {
        echo "this is a filename - calling mix_filename()<br/>";
        return mix_filename($string);
        }
    
    $numbers = '0123456789';
    $uppercons = 'BCDFGHJKLMNPQRSTVWXZ';
    $uppervowels = 'AEIOUY';
    $lowercons = 'bcdfghjklmnpqrstvwxz';
    $lowervowels = 'aeiouy';
    $noreplace = "'\".,<>#-_&\$£:;^?!@+()*% \n";

    $newstring = "";
    $bytelength = strlen($string);
    $mbytelength = mb_strlen($string);
    //echo $bytelength . " characters<br/>";
   // echo $mbytelength . " multibyte characters<br/>";

    // Simple conversion if numbers
    if($bytelength == $mbytelength && (string)(int)$string == $string)
        {
        //return substr(str_shuffle($numbers), 0, $bytelength);
        $newstring =  mt_rand(0,(int)$string);
        }
    else
        {
        // Process each character
        for($i=0;$i<$mbytelength;$i++)
            {
            $oldchar = mb_substr($string,$i,1);
            echo "Converting character #$i '" . $oldchar . "'.."; 

            if($i > 3 && strpos($noreplace,$oldchar) === false)
                {
                // Randomly add or remove character after first
                $randaction = mt_rand(0,10);
                if($randaction == 0)
                    {
                    // Skip a character
                    $i++;
                    }
                elseif($randaction == 1)
                    {
                    // Add a character
                    $i--;
                    }
                }
          
            if($i >= $mbytelength || $oldchar == "")
                {
                $newstring .=  substr(str_shuffle($lowervowels . $lowercons), 0,1);                    
                //echo "newstring: " . $newstring . "<br/>";
                }
            elseif(strpos($noreplace,$oldchar) !== false)
                {
                $newstring .= $oldchar;
                }
            elseif(strlen($oldchar)==1)
                {
                // Non- multibyte
                //echo "..Converting standard character " . $oldchar . "<br/>";
                if(strpos($lowercons,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($lowercons), 0,1);
                    }
                elseif(strpos($uppercons,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($uppercons), 0,1);
                    }
                elseif(strpos($lowervowels,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($lowervowels), 0,1);
                    }
                elseif(strpos($uppervowels,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($uppervowels), 0,1);
                    }                    
                elseif(strpos($numbers,$oldchar) !== false)
                    {
                    $newchar = substr(str_shuffle($numbers), 0,1);
                    }
                else
                    {
                    $newchar = substr(str_shuffle($noreplace), 0,1);
                    }
                $newstring .= $newchar;        
                }                         
            else
                {
                $newchar = random_char();
                $newstring .= $newchar;   
                echo "New random character: $newchar<br/>"     ;
                } // End of multibyte conversion
            }
        }

    // Update cache
    $mixcache[md5($string)] = $newstring;
    echo "<br/>New string: " . $newstring . "<br/>";
    return $newstring;
    }

function alter_data(&$row,$key,$jumblecolumns)
    {
    global $datetime_fields;
    foreach($jumblecolumns as $jumblecolumn=>$jumbletype)
        {
        $row[$jumblecolumn] = call_user_func($jumbletype , $row[$jumblecolumn]);
        // $row[$jumblecolumn] = mix_text($row[$jumblecolumn]);
        }
    }

function mix_url($string)
    {
    $urlparts = explode("://", $string);
    return $urlparts[0] . "://" . mix_text($urlparts[1], false);    
    }

function mix_filename($string)
    {
    if(trim($string) == "")
        {
        return "";
        }

    echo "filename: " . $string . "<br/>";
    if(strpos($string,".") === false)
        {
        return mix_text($string, false);
        }

    $fileparts = pathinfo($string);
    $newfilename = mix_text($fileparts["filename"], false) . "." . $fileparts["extension"];

    echo "New filename: " . $newfilename . "<br/>";
    return $newfilename;
    }

function mix_email($string)
    {
    global $mixcache;
    if(isset($mixcache[md5($string)]))
        {
        return $mixcache[md5($string)];
        }

    $emailparts = explode("@",$string);
    if(count($emailparts) < 2)
        {
        return mix_text($string);
        }

    $newemail = implode("@",array_map("mix_text",$emailparts));

    // Update cache
    $mixcache[md5($string)] = $newemail;

    return $newemail;    
    }

function replace_nulls($value)
    {
    return trim($value)=="" ? "NULL" : "'" . escape_check($value) . "'";
    }


$exportcollection = getval("exportcollection",0,true);

// Dump MySQL tables
$exporttables = array();
$exporttables["sysvars"] = array();
$exporttables["preview_size"] = array();

$exporttables["user"] = array();
$exporttables["user"]["jumble"]=array("username"=>"mix_text","email"=>"mix_email","fullname"=>"mix_text","comments"=>"mix_text","created"=>"mix_date");

$exporttables["usergroup"] = array();
$exporttables["usergroup"]["jumble"]=array("name"=>"mix_text","welcome_message"=>"mix_text");

$exporttables["user_preferences"] = array();

$exporttables["resource_type"] = array();
$exporttables["resource_type_field"] = array();
$exporttables["node"] = array();

$exporttables["filter"] = array();
$exporttables["filter"]["jumble"]=array("name"=>"mix_text");
$exporttables["filter_rule"] = array();
$exporttables["filter_rule_node"] = array();

$exporttables["archive_states"] = array();
$exporttables["workflow_actions"] = array();

// Optional tables
if($exportcollection!=0)
    {    
    // Collections 
    $exporttables["collection"] = array();
    $exporttables["collection"]["exportcondition"] = "WHERE ref = '$exportcollection'";
    
    $exporttables["collection"]["jumble"]=array("name"=>"mix_text","description"=>"mix_text","keywords"=>"mix_text","theme"=>"mix_text","theme2"=>"mix_text","theme3"=>"mix_text","theme4"=>"mix_text","theme5"=>"mix_text","created"=>"mix_date");
    $exporttables["user_collection"] = array();
    $exporttables["usergroup_collection"] = array();
    $exporttables["collection_resource"] = array(); 
    //  Resources and resource metadata
    $exporttables["resource"] = array();
    $exporttables["resource"]["jumble"]=array("field8"=>"mix_text","creation_date"=>"mix_date");
    $exporttables["resource"]["exportcondition"] = " LEFT JOIN collection_resource on resource.ref=collection_resource.resource WHERE collection_resource.collection='$exportcollection'";
    $exporttables["resource_data"] = array();
    $exporttables["resource_data"]["jumble"]=array("value");
    $exporttables["resource_data"]["exportcondition"] = " LEFT JOIN collection_resource on resource_data.resource=collection_resource.resource WHERE collection_resource.collection='$exportcollection'";
    $exporttables["resource_data"]["jumble"]=array("value"=>"mix_text");

    $exporttables["resource_node"] = array();
    $exporttables["resource_custom_access"] = array();
    $exporttables["resource_dimensions"] = array();
    $exporttables["resource_related"] = array();
    $exporttables["resource_alt_files"] = array();
    $exporttables["resource_alt_files"]["jumble"]=array("name"=>"mix_text","description"=>"mix_text","file_name"=>"mix_filename");
    $exporttables["annotation"] = array();
    $exporttables["annotation_node"] = array();
    }



$path=$mysql_bin_path . "/mysqldump";


$randstring=md5(rand() . microtime());
$dumppath = get_temp_dir(false,md5($username . $randstring . $scramble_key)) . "/mysql";
mkdir($dumppath);

$export = getval("export","") != "";
if ($export!="" && enforcePostRequest(false))
	{   
    // Create  job data

    // TODO use the user's temp directory
    $zipfile = get_temp_dir() . "/system.zip";
    $zip = new ZipArchive();
    $zip->open($zipfile, ZIPARCHIVE::CREATE);

    $zip->addFile("../../include/config.php", "config.php");
 

    $dumpfile = $dumppath . "/resourcespace.sql";
    
    foreach($exporttables as $exporttable=>$exportoptions)
        {
        echo "Exporting table " . $exporttable . "<br/>";
        // TODO: remove testing code
        //if($exporttable != "resource_alt_files"){continue;}

        //$dumpfile = $dumppath . "/" . $exporttable . ".sql";

        /// TODO - only export resources in a collection or range

       
        // Add the 'CREATE TABLE' command
        $dumpcmd = $path . " -h " . $mysql_server . " -u " . $mysql_username . ($mysql_password == "" ? "" : " -p" . $mysql_password) . " " . $mysql_db . " --no-data " . $exporttable . " >> " . $dumpfile;
        run_command($dumpcmd);
        // Get data 
        $exportcondition = isset($exportoptions["exportcondition"]) ? $exportoptions["exportcondition"] : "";
        $datarows = sql_query("SELECT * FROM " . $exporttable . " " . $exportcondition); 
        
        if(count($datarows) > 0)
            {
            // Call function to scramble the data based on per table configuration
            array_walk($datarows, 'alter_data',isset($exportoptions["jumble"]) ? $exportoptions["jumble"] : array());
            
            // Get columns to insert
            $columns = array_map("escape_check",array_keys($datarows[0]));

            $sql = "";
            foreach($datarows as $datarow)
                {
                $datarow = array_map("replace_nulls",$datarow);
                $sql .= "INSERT INTO " . $exporttable . " (" . implode(",",$columns) . ") VALUES (" . implode(",",$datarow) . ");\n";
                }

            $output = fopen($dumpfile,'a');
            fwrite($output,$sql);
            fclose($output);
            $zip->addFile($dumpfile, "mysql/" . $exporttable . ".sql");
            }
        }
    echo "Created " . $dumpfile . "<br/>";
    echo $zipfile . "<br/>";
    exit();
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


// Include resource data?


// Include collection?
// Include featured collections?

?>
<div class="BasicsBox">
    <p>
        <a href="<?php echo $baseurl_short; ?>pages/admin/admin_home.php" onClick="return CentralSpaceLoad(this, true);"><?php echo LINK_CARET_BACK ?><?php echo $lang['back']; ?></a>
    </p>
    <h1><?php echo $lang['exportdata']; ?></h1>
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
