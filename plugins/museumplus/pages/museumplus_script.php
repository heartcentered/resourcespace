<?php
if('cli' != PHP_SAPI)
    {
    http_response_code(401);
    exit('Access denied');
    }

include dirname(__FILE__) . '/../../../include/db.php';
include_once dirname(__FILE__) . '/../../../include/general.php';
include_once dirname(__FILE__) . '/../../../include/resource_functions.php';
include_once dirname(__FILE__) . '/../../../include/log_functions.php';

set_time_limit($cron_job_time_limit);

// Init script logging (if set)
global $museumplus_log_directory;
$mplus_log_file = '';
if('' != trim($museumplus_log_directory))
    {
    if(!is_dir($museumplus_log_directory))
        {
        @mkdir($museumplus_log_directory, 0755, true);

        if(!is_dir($museumplus_log_directory))
            {
            logScript("MuseumPlus: Unable to create log directory: '{$museumplus_log_directory}'");
            return false;
            }
        }

    // Cleaning up old files is up to the cron_copy_hitcount hook to do

    // New log file
    $mplus_log_file = fopen($museumplus_log_directory . DIRECTORY_SEPARATOR . 'mplus_script_log_' . date('Y_m_d-H_i') . '.log', 'ab');
    }

// Script options @see https://www.php.net/manual/en/function.getopt.php
$mplus_short_options = 'hc';
$mplus_long_options  = array(
    'help',
    'clear-lock',
);
foreach(getopt($mplus_short_options, $mplus_long_options) as $option_name => $option_value)
    {
    if(in_array($option_name, array('h', 'help')))
        {
        logScript('To clear the lock after a failed run, pass in "-c" or "--clear-lock"', $mplus_log_file);
        exit();
        }

    if(in_array($option_name, array('c', 'clear-lock')))
        {
        if(is_process_lock(MPLUS_LOCK))
            {
            clear_process_lock(MPLUS_LOCK);
            }
        }
    }

// Prepare list of users to send notifications/emails when needed
$notify_users = get_notification_users('SYSTEM_ADMIN');
$message_users = array();
foreach($notify_users as $notify_user)
    {
    get_config_option($notify_user['ref'], 'user_pref_show_notifications', $show_notifications);
    get_config_option($notify_user['ref'], 'user_pref_system_management_notifications', $sys_mgmt_notifications);

    if(!$show_notifications || !$sys_mgmt_notifications)
        {
        continue;
        }

    $message_users[] = $notify_user['ref'];
    }

// Check when this script was last run - do it now in case of permanent process locks
$museumplus_script_last_ran = '';
if(!check_script_last_ran('last_museumplus_import', $museumplus_script_failure_notify_days, $museumplus_script_last_ran))
    {
    mplus_notify(
        $message_users,
        str_replace('%script_last_ran', $museumplus_script_last_ran, $lang['museumplus_warning_script_not_completed']));
    }

// Check for a process lock
if(is_process_lock(MPLUS_LOCK)) 
    {
    logScript('MuseumPlus script lock is in place. Deferring...', $mplus_log_file);
    logScript('To clear the lock after a failed run use --clear-lock flag.', $mplus_log_file);

    mplus_notify($message_users, $lang['museumplus_error_script_failed']);

    exit(1);
    }
set_process_lock(MPLUS_LOCK);





$mplus_script_start_time = microtime(true);
$mplus_errors            = array();
$mplus_resources         = get_museumplus_resources();
$count_mplus_resources   = count($mplus_resources);

$museumplus_rs_mappings = unserialize(base64_decode($museumplus_rs_saved_mappings));




logScript('', $mplus_log_file);
foreach($mplus_resources as $mplus_resource)
    {
    logScript('Retrieving data from MuseumPlus...', $mplus_log_file);
    logScript("Checking resource #{$mplus_resource['resource']} with MpID '{$mplus_resource['mpid']}'", $mplus_log_file);
    }