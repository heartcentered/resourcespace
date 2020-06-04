<?php
//
// vm2rs setup page
//
include '../../../include/db.php';
include '../../../include/authenticate.php';

if (!checkperm('a'))
    {
    exit($lang['error-permissiondenied']);
    }
    

// Specify the name of this plugin and the heading to display for the page.
$plugin_name = 'vm2rs';

if(!in_array($plugin_name, $plugins))
    {
    plugin_activate_for_setup($plugin_name);
    }
    
$plugin_page_heading = $lang['vm2rs_configuration'];

// Build the $page_def array of descriptions of each configuration variable the plugin uses.
$page_def[] = config_add_text_input('vm2rs_field_id', $lang['vm2rs_field_id_l']);

// Do the page generation ritual -- don't change this section.
$upload_status = config_gen_setup_post($page_def, $plugin_name);
include '../../../include/header.php';

config_gen_setup_html($page_def, $plugin_name, $upload_status, $plugin_page_heading);
include '../../../include/footer.php';
 