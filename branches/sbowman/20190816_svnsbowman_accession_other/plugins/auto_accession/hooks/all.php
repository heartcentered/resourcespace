<?php
// auto_accession Plugin v1.0 All Files Hook File

function HookAuto_accessionAllResourcecreate($ref, $resource_type)
    {
    global $sysvars, $auto_accession_restypes, $auto_accession_prefix, $auto_accession_suffix, $auto_accession_field;

    if (in_array($resource_type, $auto_accession_restypes))
        {
        // Get the current, maximum accession ID numeric value.
        $max_id = get_sysvar(SYSVAR_MAX_ACCESSION_ID);

        // Create the accession ID string and update the new resource in the database.
        $accession = $auto_accession_prefix . ++$max_id . $auto_accession_suffix;
        $result = update_field($ref, $auto_accession_field, $accession);

        // Update the new maximum accession ID numeric value.
        set_sysvar(SYSVAR_MAX_ACCESSION_ID, $max_id);
        }
    else
        {
        return false;
        }

    return $accession;
    }

function HookAuto_accessionAllAdd_alternative_file_extra($resource, $alt_ref)
    {
    global $sysvars, $auto_accession_alt_accession, $auto_accession_alt_prefix, $auto_accession_alt_suffix;

    if ($auto_accession_alt_accession)
        {
        // Get the current, maximum accession ID numeric value.
        $max_id = get_sysvar(SYSVAR_MAX_ACCESSION_ID);
        debug("HOOK ALT SYSVAR Max ID: " . $max_id);

        // Create the accession ID string and update the new alternative resource in the database.
        $alt_accession = $auto_accession_alt_prefix . ++$max_id . $auto_accession_alt_suffix;
        debug("EXTRA Accession: " . $alt_accession);
        $result = sql_query("UPDATE resource_alt_files SET alt_accession = '" . escape_check($alt_accession) . "' WHERE ref = '$alt_ref' AND resource = '$resource'");
        debug("EXTRA: " . print_r($result, true));

        // Update the new maximum accession ID numeric value.
        set_sysvar(SYSVAR_MAX_ACCESSION_ID, $max_id);
        }

    return true;
    }

function HookAuto_accessionAllView_altfiles_table($altfiles)
    { 
    global $auto_accession_alt_accession, $lang;

    if ($auto_accession_alt_accession && $altfiles["alt_accession"] != "")
        { ?>
        <p><?php echo $lang['auto_accession_list_heading'] . ": " . htmlspecialchars($altfiles["alt_accession"]) ?></p> <?php
        }
    }
