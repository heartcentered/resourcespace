<?php

include_once('../../include/db.php');
include_once('../../include/general.php');
include_once('../../include/authenticate.php');
include_once('../../include/resource_functions.php');
include_once('../../include/collections_functions.php');
include_once('../../include/search_functions.php');

$ref        = intval(getvalescaped('ref','',true));
$related    = intval(getvalescaped('related','',true));
$add        = getvalescaped('action','add') == "add";
$collection =  getvalescaped('collection',0,true);

$success = false;

if($collection >  0)
    {
    if(allow_multi_edit($collection))
        {
        $success = relate_all_collection($collection, false);
        }
    }
else
    {
    if(get_edit_access($ref) && get_edit_access($related))
        {
        $success = update_related_resource($ref,$related,$add);
        }
    }
if($success)
    {    
    exit("SUCCESS");
    }
else
    {
    http_response_code(403);
    exit($lang["error-permissiondenied"]);
    }

