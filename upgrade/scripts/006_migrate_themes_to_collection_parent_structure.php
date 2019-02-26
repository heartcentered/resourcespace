<?php
include_once __DIR__ . "/../../include/db.php";
include_once __DIR__ . "/../../include/general.php";
include_once __DIR__ . "/../../include/resource_functions.php";
include_once __DIR__ . "/../../include/search_functions.php";
include_once __DIR__ . "/../../include/migration_functions.php";

# Fetch all featured collections (does not use theme functions which will be removed at some point!)

$featured_collections=sql_query("select * from collection where public=1 and length(theme)>0");
#print_r($featured_collections);


# $theme_category_levels


foreach ($featured_collections as $collection)
    {

    # Ensure the full structure exists first to support this.
    $parent=-1;
    for ($level=1;$level<=$theme_category_levels;$level++)
        {
        $col="theme" . ($level==1?"":$level);
        echo "\nProcessing collection " . $collection["ref"] . "  column " . $col . " (" . $collection[$col] . ") - parent " . $parent;
        
        if ($collection[$col]!="")
            {
            $existing=sql_value("select ref value from collection where public=1 and name='" . escape_check($collection[$col]) . "' and parent='$parent'",0);
            if ($existing==0)
                {
                # Does not exist, create
                sql_query("insert into collection(name,public,parent) values ('" . escape_check($collection[$col]) . "',1,'$parent')");
                $existing=sql_insert_id();
                
                echo "Created $existing  ";
                }
            }
        $parent=$existing; # Set the parent to this collection as we descend down the tree.
        }
    
    # The necessary parts of the tree now exist to support this collection. Drop it into the tree.
    sql_query("update collection set parent='" . $parent . "' where ref='" . $collection["ref"] . "'");
    }

    
# TO DO - map existing j perms to new collection/user group access system
