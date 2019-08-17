<?php
// Comma Separated Value and Spatial Vector Metadata Export Functions

/**
* Generates the CSV content of the metadata for resources passed in the array
*
* @param $resources
* @return string
*/
function generateResourcesMetadataCSV(array $resources, $personal = false, $alldata = false, $linestrip = false, $exporttype = "csv")
    {
    global $lang, $csv_export_add_original_size_url_column, $disable_geocoding, $storagedir;
    $return = '';
    $csv_field_headers = array();
    $resources_fields_data = array();

    foreach($resources as $resource)
        {
        $resdata = get_resource_data($resource['ref']);

        // Add resource type.
        $restype = get_resource_type_name($resdata["resource_type"]);
        $csv_field_headers["resource_type"] = $lang["resourcetype"];
        $resources_fields_data[$resource['ref']]["resource_type"] = $restype;

        // Add resource contributor?
        $udata = get_user($resdata["created_by"]);
        if ($udata !== false)
            {
            $csv_field_headers["created_by"] = $lang["contributedby"];
            $resources_fields_data[$resource['ref']]["created_by"] = (trim($udata["fullname"]) != "" ? $udata["fullname"] : $udata["username"]);
            }

        // Add resource geomap coordinates (latitude, longitude) column values?
        if(!$disable_geocoding)
            {
            $resources_fields_data[$resource['ref']]['geo_lat'] = $resdata['geo_lat'];
            $resources_fields_data[$resource['ref']]['geo_long'] = $resdata['geo_long'];
            }

        foreach(get_resource_field_data($resource['ref'], false, true, -1, '' != getval('k', '')) as $field_data)
            {
            // If $personal = true, return personal_data fields only.
            // If $alldata = false, return only fields marked as 'Include in CSV export'.
            if ((!$personal || $field_data["personal_data"]) && ($alldata || $field_data["include_in_csv_export"]))
                {
                $csv_field_headers[$field_data['ref']] = $field_data['title'];
                $resources_fields_data[$resource['ref']][$field_data['resource_type_field']] = $field_data['value'];
                }
            }

        // Add original size URL column values?
        if(!$csv_export_add_original_size_url_column)
            {
            continue;
            }

        // Add geomap coordinates header columns?
        if(!$disable_geocoding)
            {
            $csv_field_headers['geo_lat'] = $lang['csv_geolat'];
            $csv_field_headers['geo_long'] = $lang['csv_geolong'];
            }

        // Provide the original URL only if we have access to the resource or the user group does not have restricted access to the original size.
        $access = get_resource_access($resource);
        if(0 != $access || checkperm("T{$resource['resource_type']}_"))
            {
            continue;
            }

        $filepath = get_resource_path($resource['ref'], true, '', false, $resource['file_extension'], -1, 1, false, '', -1, false);
        $original_link = get_resource_path($resource['ref'], false, '', false, $resource['file_extension'], -1, 1, false, '', -1, false);
        if(file_exists($filepath))
            {
            $resources_fields_data[$resource['ref']]['original_link'] = $original_link;
            }
        }

    // Add original size URL column.
    if($csv_export_add_original_size_url_column)
        {
        $csv_field_headers['original_link'] = $lang['collection_download_original'];
        }

    $csv_field_headers = array_unique($csv_field_headers);

    // Setup header and delimiter (standard CSV comma or the pipe character).
    if($exporttype == "csv_pipe" || $exporttype == "csv_pipe_txt") // Use pipe delimiter and modify file schema.
        {
        $delimiter = '|';
        $delimiter2 = '|';
        $delimiter3 = '|';
        $separator = '';
        $return = $lang['resourceids'] . $delimiter . implode($delimiter, $csv_field_headers) . "\n";
        }
    else // Standard comma delimiter and CSV file schema.
        {
        $delimiter = ',';
        $delimiter2 = '"",';
        $delimiter3 = '",';
        $separator = '"';
        $return = '"' . $lang['resourceids'] . '","' . implode('","', $csv_field_headers) . "\"\n";
        }

    // Output CSV results for the search.
    $csv_row = '';
    foreach($resources_fields_data as $resource_id => $resource_fields)
        {
        // First column will always be the Resource ID.
        $csv_row = $resource_id . $delimiter;

        // Field values.
        foreach($csv_field_headers as $column_header => $column_header_title)
            {
            if(!array_key_exists($column_header, $resource_fields))
                {
                $csv_row .= $delimiter2;
                continue;
                }

            foreach($resource_fields as $field_name => $field_value)
                {
                if($column_header == $field_name)
                    {
                    if(!$linestrip) // Standard output.
                        {
                        $csv_row .= $separator . str_replace(array("\n", "\r", "\""), "", tidylist(i18n_get_translated($field_value))) . $delimiter3;
                        }
                    else // Strip unexcaped '\r\n' from CSV output.
                        {
                        $csv_row .= $separator . str_replace(array("\n", "\r", '\r\n ', '\r\n', '\r', '\n', "\""), "", tidylist(i18n_get_translated($field_value))) . $delimiter3;
                        }
                    }
                }
            }

        $csv_row = rtrim($csv_row, $delimiter);
        $csv_row .= "\n";
        $return .= $csv_row;
        }

    // Spatial vector formats export processed by GDAL (GeoJSON, GPX, KML, netCDF, and XLSX).
    if ($exporttype != "csv" && $exporttype != "csv_pipe" && $exporttype != "csv_txt" && $exporttype != "csv_pipe_txt")
        {
        // Check if an metadata export temp folder exists; otherwise, create it.
        if (!is_dir($storagedir . "/tmp/export"))
            {
            mkdir($storagedir . '/tmp/export');
            chmod($storagedir . '/tmp/export', 0777);
            }
        $export_temp = $storagedir . '/tmp/export';

        // Determine GDAL ogr2ogr vector file format conversion parameters (see CLI ogr2ogr --formats for your installation to add other formats).
        $gdal_options = "";

        switch ($exporttype)
            {
            case "geojson": // GeoJSON, Geographic JavaScript Object Notation.
                $export_type = "GeoJSON";
                $extension = ".txt";
                break;
            case "gpx": // GPX, GPS Exchange Format.
                $export_type = "GPX";
                $extension = ".gpx";
                $gdal_options = " -dsco GPX_USE_EXTENSIONS=YES";
                break;
            case "kml": // KML, Keyhole Markup Language format for Google Earth.
                $export_type = "KML";
                $extension = ".kml";
                break;
            case "netcdf": // netCDF, Network Common Data Form format.
                $export_type = "netCDF";
                $extension = ".nc";
                break;
            case "xlsx": // XLSX, Microsoft Excel Open XML format.
                $export_type = "XLSX";
                $extension = ".xlsx";
                break;
            default:
                $export_type = "CSV";
                $extension = ".csv";
            }

        // Write the CSV data created above to an randomly named export temp file to prevent multiple file conflicts.
        $temp_csv_path = $export_temp . '/metadata_' . bin2hex(random_bytes(6)) . '.csv';
        $temp_csv = fopen($temp_csv_path, 'w');
        fwrite($temp_csv, $return);
        fclose($temp_csv);
        chmod($temp_csv_path, 0777);

        // Convert the intermediate CSV data to a specfic vector file format with GDAL ogr2ogr.
        $out = $export_temp . '/metadata_' . bin2hex(random_bytes(6)) . $extension;
        $command = "ogr2ogr -f " . $export_type . $gdal_options . " " . $out . " " . $temp_csv_path . " -oo X_Possible_Names=Longitude -oo Y_Possible_Names=Latitude";
        $result = run_command($command);

        // Delete temp CSV file, as it is no longer needed.
        unlink($temp_csv_path);

        // Export output as the path to the new vector metadata file.
        $return = $out;
        }

    return $return;
    }


/**
* Generates the file content when exporting nodes
* 
* @param array   $field        Array containing field information (as retrieved by get_field)
* @param boolean $send_headers If true, function sends headers used for downloading content. Default is set to false
* 
* @return mixed
*/
function generateNodesExport(array $field, $parent = null, $send_headers = false)
    {
    global $lang;

    if(0 === count($field) || !isset($field['ref']) || !isset($field['type']))
        {
        trigger_error('Field array cannot be empty. generateNodesExport() requires at least "ref" and "type" indexes!');
        }

    $return = '';
    $nodes  = get_nodes($field['ref'], $parent);

    foreach($nodes as $node)
        {
        $return .= "{$node['name']}\r\n";
        }

    log_activity("{$lang['export']} metadata field options - field {$field['ref']}", LOG_CODE_DOWNLOADED);
    
    if($send_headers)
        {
        header('Content-type: application/octet-stream');
        header("Content-disposition: attachment; filename=field{$field['ref']}_nodes_export.txt");
        echo $return;
        ob_flush();
        exit();
        }

    return $return;
    }
