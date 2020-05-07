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
        // @todo: make this a function as we need it on search when we already have selected resources.
        $search = "!collection{$USER_SELECTION_COLLECTION}";
        $restypes = getvalescaped("restypes", "");
        $order_by = getvalescaped("order_by", "relevance");
        $archive = getvalescaped("archive", "0");
        $per_page = getvalescaped("per_page", null, true);
        $offset = getvalescaped("offset", null, true);
        $fetchrows = (!is_null($per_page) && !is_null($offset) ? $per_page + $offset : -1);
        $sort = getvalescaped("sort", "desc");
        // $access_override = false;
        $starsearch = getvalescaped("starsearch", 0, true);
        // $ignore_filters = false;
        // $return_disk_usage = false;
        $recent_search_daylimit = getvalescaped("recent_search_daylimit", "");
        $go = getvalescaped("go", "");
        // $stats_logging = true;
        // $return_refs_only = false;
        $editable_only = getvalescaped("foredit","")=="true";

        // $result = do_search(
        //     $search,
        //     $restypes,
        //     $order_by,
        //     $archive,
        //     $fetchrows,
        //     $sort,
        //     false,
        //     $starsearch,
        //     false,
        //     false,
        //     $recent_search_daylimit,
        //     $go, 
        //     true, 
        //     false, 
        //     $editable_only);
        // $resources_count = count($result);

        $resources_count = get_collection_resources($USER_SELECTION_COLLECTION);
        $collection_data = get_collection($USER_SELECTION_COLLECTION);

        $valid_selection_collection_actions = array(
            "relate_all",
            "save_search_items_to_collection",
            "remove_selected_from_collection",
            "search_items_disk_usage",
            "csv_export_results_metadata",
            // @todo: add share selction
        );

        $callback_csrf_token = generateCSRFToken($usersession, "remove_selected_from_collection");

        $render_actions_extra_options = array(
            // @todo: might have to add this after we check there are any selected resources in the usercollection
            array(
                "value" => "remove_selected_from_collection",
                "label" => $lang["remove_selected_from_collection"],
                "data_attr" => array(
                    "callback" => "RemoveSelectedFromCollection('{$CSRF_token_identifier}', '{$callback_csrf_token}');",
                ),
                "category" => ACTIONGROUP_COLLECTION,
            ),
        );
        $render_actions_filter = function($action) use ($valid_selection_collection_actions)
            {
            return in_array($action["value"], $valid_selection_collection_actions);
            };

        // override the language for actions as it's now specific to a selection of resources
        $lang["relateallresources"] = $lang["relate_selected_resources"];
        $lang["savesearchitemstocollection"] = $lang["add_selected_to_collection"];
        $lang["searchitemsdiskusage"] = $lang["selected_items_disk_usage"];
        $lang["csvExportResultsMetadata"] = $lang["csvExportResultsMetadata"];

        render_actions($collection_data, true, false);
        break;

    case "resource":
    break;
    }
    

