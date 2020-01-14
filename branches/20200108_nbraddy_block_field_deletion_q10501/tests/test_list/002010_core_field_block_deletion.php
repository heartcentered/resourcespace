<?php
if('cli' != PHP_SAPI)
    {
    exit('This utility is command line only.');
    }

$newfield = create_resource_type_field("New title",0,FIELD_TYPE_TEXT_BOX_SINGLE_LINE,"newtitle",false);

// Set field to be a standard core variable and ensure that deletion is prevented
$saved_view_title_field = $view_title_field;
$view_title_field = $newfield;
$deleted = delete_resource_type_field($newfield);
$view_title_field = $saved_view_title_field;
if($deleted===true)
    {
    return false;
    }

// Set field to be a member of standard core array variable and ensure that deletion is prevented
$saved_sort_fields = $sort_fields;
$sort_fields[] = $newfield;
$deleted = delete_resource_type_field($newfield);
$sort_fields = $saved_sort_fields;
if($deleted===true)
    {
    return false;
    }