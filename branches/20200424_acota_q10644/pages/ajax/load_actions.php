<?php
include '../../include/db.php';
include_once '../../include/general.php';
include '../../include/authenticate.php';
include_once "../../include/render_functions.php";
include_once "../../include/collections_functions.php";
include_once "../../include/search_functions.php";
include_once "../../include/resource_functions.php";


$forpage=getvalescaped('page', '');
$type=getvalescaped('actiontype', '');
$ref=getvalescaped('ref', '',true);

switch ($type)
    {
    case "collection":
        hook('render_themes_list_tools', '', $ref);
        $collection_data = get_collection($ref);
        render_actions($collection_data,false,false,$ref,array(),true, $forpage);
    break;
    
    case "selection_collection":
        render_selected_collection_actions();
        break;

    case "resource":
    break;
    }
    

