<?php
$rsroot = dirname(__FILE__);
include "{$rsroot}/../../include/db.php";
include_once "{$rsroot}/../../include/general.php";
include "{$rsroot}/../../include/authenticate.php";
include_once "{$rsroot}/../../include/collections_functions.php";
include_once "{$rsroot}/../../include/ajax_functions.php";
// include_once "{$rsroot}/../../include/resource_functions.php";

if(checkperm("b"))
    {
    ajax_unauthorized();
    }

$return = array();
$action = trim(getval("action", ""));
$allowed_actions = array(
    "add_resource",
);

if($action == "" || !in_array($action, $allowed_actions))
    {
    $fail_msg = str_replace("%key", "action", $lang["error-request-missing-key"]);
    ajax_send_response(400, ajax_response_fail(ajax_build_message($fail_msg)));
    }


// todo: implement actions as needed
ajax_send_response(200, array(
        "status" => "success",
        "data" => ajax_build_message("inserted")));