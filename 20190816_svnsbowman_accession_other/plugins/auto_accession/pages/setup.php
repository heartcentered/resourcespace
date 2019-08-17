<?php
// auto_accession v1.0 Plugin Setup Page

include '../../../include/db.php';
include_once '../../../include/general.php';
include '../../../include/authenticate.php'; 
if (!checkperm('a'))
    {
    exit ($lang['error-permissiondenied']);
    }

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'auto_accession';
if(!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }
$plugin_page_heading = $lang['auto_accession_configuration'];
$plugin_page_introtext = $lang['auto_accession_introtext'];

$max_id = sql_value("SELECT value FROM sysvars WHERE name = 'max_accession_id'", "");
$alt_result = sql_query("SELECT r. *, r.alt_accession FROM resource_alt_files r", "");

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_section_header($lang['auto_accession_settings'], "");
$page_def[] = config_add_text_input('auto_accession_field', $lang['auto_accession_field']);
$page_def[] = config_add_text_input('auto_accession_prefix', $lang['auto_accession_prefix']);
$page_def[] = config_add_text_input('auto_accession_initial', $lang['auto_accession_initial']);
$page_def[] = config_add_text_input('auto_accession_suffix', $lang['auto_accession_suffix']);
$page_def[] = config_add_multi_rtype_select('auto_accession_restypes', $lang['auto_accession_restypes']);

$page_def[] = config_add_section_header($lang['auto_accession_alt_settings'], "");
$page_def[] = config_add_boolean_select('auto_accession_alt_accession', $lang['auto_accession_alt_accession']);
$page_def[] = config_add_text_input('auto_accession_alt_prefix', $lang['auto_accession_alt_prefix']);
$page_def[] = config_add_text_input('auto_accession_alt_suffix', $lang['auto_accession_alt_suffix']);

// Do the page generation ritual, do not change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);

include '../../../include/header.php';
config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading, $plugin_page_introtext);

include '../../../include/footer.php';

debug("ACCESSION SETUP INITIAL: " . $auto_accession_initial);
debug("ACCESSION SETUP INITIAL SETVAR 1: " . get_sysvar(SYSVAR_MAX_ACCESSION_ID));

// Set the maximum accession ID if not already set.
if (get_sysvar(SYSVAR_MAX_ACCESSION_ID) == "" || get_sysvar(SYSVAR_MAX_ACCESSION_ID) == 0)
    {
    set_sysvar(SYSVAR_MAX_ACCESSION_ID, $auto_accession_initial);
    }

debug("ACCESSION SETUP INITIAL SETVAR 2: " . get_sysvar(SYSVAR_MAX_ACCESSION_ID));
