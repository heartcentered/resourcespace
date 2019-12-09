<?php
include_once __DIR__ . '/../image_processing.php';
include_once __DIR__ .  "/../resource_functions.php";
# $job_data["resource"]
# $job_data["extract"]
# $job_data["revert"]
# $job_data["autorotate"]
# $job_data["archive"] -> optional based on $upload_then_process_holding_state
# $job_data["upload_file_by_url"] -> optional. If NOT empty, means upload_file_by_url should be used instead

$upload_file_by_url = isset($job_data["upload_file_by_url"]) && is_string($job_data["upload_file_by_url"]) ? $job_data["upload_file_by_url"] : "";

// Set up the user who triggered this event - the upload should be done as them
$user_data = validate_user("AND u.ref = '" . escape_check($job['user']) . "'", true);
if(!is_array($user_data) || count($user_data) == 0)
    {
    job_queue_update($jobref, $job_data, STATUS_ERROR);
    return;
    }
setup_user($user_data[0]);

$resource=get_resource_data($job_data["resource"]);
$status=false;

if($resource!==false)
	{
    if(trim($upload_file_by_url) != "")
        {
        $status = upload_file_by_url(
            $job_data["resource"],
            $job_data["extract"],
            $job_data["revert"],
            $job_data["autorotate"],
            $job_data["upload_file_by_url"]);
        }
    else
        {
        $status=upload_file($job_data["resource"], $job_data["extract"], $job_data["revert"], $job_data["autorotate"] ,"", true);
        }
	
	# update the archive status
	if(isset($job_data['archive']) && $job_data['archive'] !== '')
		{
		update_archive_status($job_data["resource"], $job_data["archive"]);
		}
	}

global $baseurl, $offline_job_delete_completed;

$url = isset($job_data['resource']) ? $baseurl . "/?r=" . $job_data['resource']: '';

if($status===false)
    {
    # fail
    message_add($job['user'], $job_failure_text, $url, 0);
    
    job_queue_update($jobref , $job_data , STATUS_ERROR);
    }
else
    {
    # success
    message_add($job['user'], $job_success_text, $url, 0);
    
    # only delete the job if completed successfully;
    if($offline_job_delete_completed)
        {
        job_queue_delete($jobref);
        }
    else
        {
        job_queue_update($jobref,$job_data,STATUS_COMPLETE);
        }
    }
