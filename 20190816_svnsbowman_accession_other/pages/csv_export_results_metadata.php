<?php
// Collection Resources Metadata Export (CSV, Pipe Separated CSV, GeoJSON, GPX, KML, or XLSX)

include_once '../include/db.php';
include_once '../include/general.php';
# External access support (authenticate only if no key was provided).
if(getvalescaped('k', '') == '')
    {
    include_once '../include/authenticate.php';
    }
include_once '../include/search_functions.php';
include_once '../include/collections_functions.php';
include_once '../include/csv_export_functions.php';

$search = getvalescaped('search', '');
$restypes = getvalescaped('restypes', '');
$order_by = getvalescaped('order_by', '');
$archive = getvalescaped('archive', '');
$sort = getvalescaped('sort', '');
$starsearch = getvalescaped('starsearch', '');

if(getval("submit", "") != "")
    {
    $personaldata = (getvalescaped('personaldata', '') != '');
    $allavailable = (getvalescaped('allavailable', '') != '');
    $linestrip = (getvalescaped('linestrip', '') != '');
    $exporttype = getval('exporttype', '');

    // Do the search again to get the results back.
    $search_results = do_search($search, $restypes, $order_by, $archive, -1, $sort, false, $starsearch);
    
    log_activity($lang['csvExportResultsMetadata'], LOG_CODE_DOWNLOADED, $search . ($restypes == '' ? '' : ' (' . $restypes . ')'));
    
    if (!hook('csvreplaceheader'))
        {
        // Determine the correct metadata export file extension based on the format type.
        switch($exporttype)
            {
            case "csv":
            case "csv_pipe":
                $extension = "csv";
                break;
            case "csv_txt":
            case "csv_pipe_txt":
            case "geojson":
                $extension = "txt";
                break;
            case "gpx":
                $extension = "gpx";
                break;
            case "kml":
                $extension = "kml";
                break;
            case "xlsx":
                $extension = "xlsx";
                break;
            default:
                $extension = "csv";
            }

        // Setup HTML header parameters.
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=search_results_metadata.csv");

        // Create metadata export in a specific format.
        $export = generateResourcesMetadataCSV($search_results, $personaldata, $allavailable, $linestrip, $exporttype);
        $ext_check = substr($export, -3);

        // For formats processed by GDAL, read file contents from temp file created in function and delete temp file.
        if ($use_gdal && $exporttype != "csv" && $exporttype != "csv_pipe" && $exporttype != "csv_txt" && $exporttype != "csv_pipe_txt")
            {
            readfile($export);

            // Delete GDAL export temp file, as it is no longer needed.
            chmod($export, 0777);
            unlink($export);
            }
        else // Echo data as normal from function for CSV-type formats.
            {
            echo $export;
            }
        }

    exit();    
    }
else
    {
    include "../include/header.php";
    ?>
    <div class="BasicsBox">
        <!-- Below is intentionally not an AJAX POST -->
        <form method="post" action="<?php echo $baseurl_short?>pages/csv_export_results_metadata.php" >
            <?php
            generateFormToken("csv_export_results");
            ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search) ?>" />
            <input type="hidden" name="restypes" value="<?php echo htmlspecialchars($restypes) ?>" />
            <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($order_by) ?>" />
            <input type="hidden" name="archive" value="<?php echo htmlspecialchars($archive) ?>" />
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort) ?>" />
            <input type="hidden" name="starsearch" value="<?php echo htmlspecialchars($starsearch) ?>" />
            
            <h1><?php echo $lang["csvExportResultsMetadata"]?></h1>

            <!--Check if GDAL is installed-->
            <?php if ($use_gdal && isset($ogr2ogr_path))
                { ?>
                <p><?php echo $lang["metadata_export_introtext"]?></p> <?php
                }
            else // GDAL is not installed, do not show introtext for non-CSV export formats.
                { ?>
                <p><?php echo $lang["metadata_export_introtext2"]?></p> <?php
                } ?>

            <div class="Question" id="question_exporttype">
                <label for="exporttype"><?php echo htmlspecialchars($lang['metadata_export_type']) ?></label>
                <select id="exporttype" class="stdwidth" name="exporttype">
                    <option value="csv"><?php echo $lang['csv']; ?></option>
                    <option value="csv_txt"><?php echo $lang['csv_text']; ?></option>
                    <option value="csv_pipe"><?php echo $lang['csv_pipe_delimited']; ?></option>
                    <option value="csv_pipe_txt"><?php echo $lang['csv_pipe_delimitedtext']; ?></option> 
                    <?php if ($use_gdal && isset($ogr2ogr_path)) // Display these options if GDAL is installed.
                        { ?>
                        <option value="geojson"><?php echo $lang['geojson']; ?></option>
                        <option value="gpx"><?php echo $lang['gpx']; ?></option>
                        <option value="kml"><?php echo $lang['kml']; ?></option>
                        <option value="xlsx"><?php echo $lang['xlsx']; ?></option> <?php
                        } ?>
                </select>
                <div class="clearerleft"></div></div>
            </div>

            <?php if (!$csv_hidepersonal)
                { ?>
                <div class="Question" id="question_personal">
                    <label for="personaldata"><?php echo htmlspecialchars($lang['csvExportResultsMetadataPersonal']) ?></label>
                    <input name="personaldata" id="personaldata" type="checkbox" value="true" style="margin-top:7px;"> 
                    <div class="clearerleft"> </div>
                </div> <?php
                } ?>
            
            <div class="Question" id="question_personal">
                <label for="allavailable"><?php echo htmlspecialchars($lang['csvExportResultsMetadataAll']) ?></label>
                <input name="allavailable" id="allavailable" type="checkbox" value="true" style="margin-top:7px;"> 
                <div class="clearerleft"> </div>
            </div>
            
            <div class="Question" id="question_linestrip">
                <label for="allavailable"><?php echo htmlspecialchars($lang['csvExportResultsLineStrip']) ?></label>
                <input name="linestrip" id="linestrip" type="checkbox" value="true" style="margin-top:7px;">
                <div class="clearerleft"> </div>
            </div>
    
            <div class="QuestionSubmit">
                <label for="buttons"> </label>        
                <input type="hidden" name="submit" value="true" />  
                <input name="submit" type="submit" id="submit" value="&nbsp;&nbsp;<?php echo $lang["action-download"]?>&nbsp;&nbsp;" />
            </div>
    
        </form>
    </div>
    <?php
    include "../include/footer.php";
    }
