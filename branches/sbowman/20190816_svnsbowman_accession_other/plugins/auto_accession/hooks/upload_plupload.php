<?php
// auto_accession v1.0 ../pages/upload_plupload.php Hook File

function HookAuto_accessionUpload_pluploadAfterpluploadfile($ref, $extension, $resource_type)
    {
    global $sysvars, $auto_accession_prefix, $auto_accession_suffix, $auto_accession_restypes, $auto_accession_field;

    // Get the current, maximum accession ID numeric value.
    $max_id = get_sysvar(SYSVAR_MAX_ACCESSION_ID);
    
    // Create the accession ID string and update the new resource in the database.
    $accession = $auto_accession_prefix . ++$max_id . $auto_accession_suffix;
    $result = update_field($ref, $auto_accession_field, $accession);

    // Update the new maximum accession ID numeric value.
    set_sysvar(SYSVAR_MAX_ACCESSION_ID, $max_id);

    return $accession;
    }
