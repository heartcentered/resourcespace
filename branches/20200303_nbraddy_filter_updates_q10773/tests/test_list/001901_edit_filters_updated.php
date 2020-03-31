<?php
include_once(__DIR__ . '/../../include/search_functions.php');
if (php_sapi_name()!=="cli") {exit("This utility is command line only.");}

// Test edit filters similar to search filters
// Save current settings
$saved_search_filter_nodes = $search_filter_nodes;
$saved_edit_filter = $usereditfilter;

// create 5 new resources
$resourcea=create_resource(1,0);
$resourceb=create_resource(1,0);
$resourcec=create_resource(2,0);
$resourced=create_resource(2,0);
$resourcee=create_resource(2,0);

// create new 'Edit Department' field
$editdepartmentfield = create_resource_type_field("Edit Department",0,FIELD_TYPE_CHECK_BOX_LIST,"editdepartment");

// create new 'Colour' field
$colourfield = create_resource_type_field("Colour",0,FIELD_TYPE_DROP_DOWN_LIST,"colour");

// Add new nodes to fields
$salesnode  = set_node(NULL, $editdepartmentfield, "Sales",'',1000);
$itnode     = set_node(NULL, $editdepartmentfield, "IT",'',1000);
$rednode    = set_node(NULL, $colourfield, "Red",'',1000);
$bluenode   = set_node(NULL, $colourfield, "Blue",'',1000);

add_resource_nodes($resourcea,array($salesnode, $rednode));
add_resource_nodes($resourceb,array($salesnode, $itnode, $rednode));
add_resource_nodes($resourcec,array($itnode, $rednode));
add_resource_nodes($resourced,array($bluenode, $itnode));
add_resource_nodes($resourcee,array($itnode,$rednode,$bluenode));

// SUBTEST A: old style edit filter
$search_filter_nodes = false;
$usereditfilter = "editdepartment=Sales;colour=Red";

// Check 'editable' search
$results = do_search('','','',0,-1,'desc',false,0,false,false,'',false,false,true,true);

if(count($results) != 2 || !isset($results[0]['ref'])
	||
    !match_values(array_column($results,'ref'),array($resourcea,$resourceb))
	)
	{
    echo "SUBTEST A";
    return false;
    }

// SUBTEST B: Check get_edit_access() for same filter
$accessa = get_edit_access($resourcea);
$accessb = get_edit_access($resourceb);
$accessc = get_edit_access($resourcec);
$accessd = get_edit_access($resourced);
$accesse = get_edit_access($resourcee);
if(!$accessa || !$accessb || $accessc || $accessd || $accesse)
	{
    echo "SUBTEST B";
    return false;
    }

// SUBTEST C: old style edit filter migrated
$search_filter_nodes = true;
$results = do_search('','','',0,-1,'desc',false,0,false,false,'',false,false,true,true);
if(count($results) != 2 || !isset($results[0]['ref'])
    ||
    !match_values(array_column($results,'ref'),array($resourcea,$resourceb))
    )
    {
    echo "SUBTEST C";
    return false;
    }

// SUBTEST D: Check get_edit_access() after migration
$accessa = get_edit_access($resourcea);
$accessb = get_edit_access($resourceb);
$accessc = get_edit_access($resourcec);
$accessd = get_edit_access($resourced);
$accesse = get_edit_access($resourcee);
if(!$accessa || !$accessb || $accessc || $accessd || $accesse)
	{
    echo "SUBTEST D";
    return false;
    }
    

// SUBTEST E: old style edit filter with resource_type
$search_filter_nodes = false;
$usereditfilter = "editdepartment=IT;resource_type=1";
$results = do_search('','','',0,-1,'desc',false,0,false,false,'',false,false,true,true);
if(count($results) != 1 || !isset($results[0]['ref'])
	||
    !match_values(array_column($results,'ref'),array($resourceb))
	)
	{
    echo "SUBTEST E";
    return false;
    }

// SUBTEST F: Check get_edit_access() 
$accessa = get_edit_access($resourcea);
$accessb = get_edit_access($resourceb);
$accessc = get_edit_access($resourcec);
$accessd = get_edit_access($resourced);
$accesse = get_edit_access($resourcee);
if($accessa || !$accessb || $accessc || $accessd || $accesse)
	{
    echo "SUBTEST F";
    return false;
    }

// SUBTEST G: old style edit filter with resource_type - migrated to new code
global $usereditfilter;
$search_filter_nodes = true;
$results = do_search('','','',0,-1,'desc',false,0,false,false,'',false,false,false,true);
if(count($results) != 1 || !isset($results[0]['ref'])
	||
    !match_values(array_column($results,'ref'),array($resourceb))
	)
	{
    echo "SUBTEST G";
    return false;
    }

// SUBTEST H: Check get_edit_access() 
$accessa = get_edit_access($resourcea);
$accessb = get_edit_access($resourceb);
$accessc = get_edit_access($resourcec);
$accessd = get_edit_access($resourced);
$accesse = get_edit_access($resourcee);
if($accessa || !$accessb || $accessc || $accessd || $accesse)
	{
    echo "SUBTEST H";
    return false;
    }

// Reset saved settings
$search_filter_nodes = $saved_search_filter_nodes;
$usereditfilter = $saved_edit_filter;

return true;