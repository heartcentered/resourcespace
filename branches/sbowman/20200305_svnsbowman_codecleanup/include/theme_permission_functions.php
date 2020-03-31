<?php
// Theme Permission Functionsa

// Start of n-level theme permissions

// Returns array of key=>value
// key = theme path, pipe delimited ("|"), e.g. Cars|German|VW
// value = boolean - TRUE(1) if permission to view or FALSE if denied
// Set via permission manager j*, (include all), j<top level include> and j-<exclude below top level> directives.
function getThemePathPerms()
    {
    global $theme_category_levels, $permissions, $current_user_collection_blacklisted_no_perms;

    $stack = array();
    $sql_theme_columns_name = "";

    // Build up list of columns depending on how many theme levels specified in setup.
    for($i = 2; $i <= $theme_category_levels; $i++) $sql_theme_columns_name .= ",theme{$i}";
    $collections = sql_query("select distinct ref, theme theme1${sql_theme_columns_name} from collection where length(theme)>0 order by theme1${sql_theme_columns_name}");  # Order by is IMPORTANT.
    foreach($collections as $collection)
        {
        $pathString = "";
        $ref = 0;
        $perm = true; # By default grant permission.
        foreach ($collection as $item)
            {
            // The first field return in query is the ref, so grab it and continue.
            if($ref == 0)
                {
                $ref = $item;
                continue;
                }

            // The current field is blank so quit field iteration.
            if(empty($item))
                {
                break;
                }

            // Only add separator if not first field.
            if($pathString != "")
                {
                $pathString .= "|";
                }

            // For top level, we just need to make sure that "jMyTheme" does not exist.
            $pathString .= $item;

            // Look for minus path to indicate that we do not have permission from here and below.
            if((substr_count ($pathString,"|") == 0 && !array_search ("j${pathString}", $permissions))
                || (array_search ("j-${pathString}", $permissions)))
                {
                $perm = false; # IMPORTANT: For this and all other sub-levels permission will not be granted, cool!
                }
            $stack[$pathString] = $perm; # Add to return stack.
            } // End field iteration.
        } // End row iteration.
    return $stack;
    }
// End of n-level theme permissions.

?>
