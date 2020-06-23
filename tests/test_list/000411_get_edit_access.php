<?php
if('cli' != PHP_SAPI)
    {
    exit('This utility is command line only.');
    }

// Setup test
$original_user_data = $userdata;

$get_user_by_username = function($n) {
    $username = escape_check($n);
    sql_value("SELECT ref AS `value` FROM user WHERE username = '$username'", 0);
};
$user_admin = new_user("test_000411_admin", 3);
if($user_admin === false)
    {
    $user_admin = $get_user_by_username("test_000411_admin");
    }
$user_general = new_user("test_000411_general", 2);
if($user_general === false)
    {
    $user_general = $get_user_by_username("test_000411_general");
    }
if($user_admin === 0 || $user_general === 0)
    {
    echo "Setup test: users - ";
    return false;
    }
$user_admin_data = get_user($user_admin);
$user_general_data = get_user($user_general);

$resource_contributed_by_admin = create_resource(1, 0, $user_admin);
$resource_contributed_by_general = create_resource(1, 0, $user_general);
if($resource_contributed_by_admin === false || $resource_contributed_by_general === false)
    {
    echo "Setup test: resources - ";
    return false;
    }

setup_user($user_general_data);

// A user can always edit its own user resource template
if(!get_edit_access(0 - $user_general))
    {
    echo "User template - ";
    return false;
    }

// Check defaults - a general user normally has edit access to Pending submission
if(!get_edit_access($resource_contributed_by_general, -2))
    {
    echo "Pending submission - ";
    return false;
    }

// Check defaults - a general user normally has edit access to Pending review
if(!get_edit_access($resource_contributed_by_general, -1))
    {
    echo "Pending review - ";
    return false;
    }

// Check defaults - a general user normally doesn't have edit access to Active state
if(get_edit_access($resource_contributed_by_general, 0))
    {
    echo "Active - ";
    return false;
    }

// Check defaults - a general user normally doesn't have edit access to a resource that is not contributed by the user
// honouring the default access to Pending submission/review.
if(get_edit_access($resource_contributed_by_admin, -2))
    {
    echo "Pending submission (resource contributed by admin) - ";
    return false;
    }

// Use case - ability for general users to edit their contributions when not having access to Active state (e0 perm)
$edit_access_for_contributor = true;
if(!get_edit_access($resource_contributed_by_general, 0))
    {
    echo "Edit access for contributor (no e0 perm) - ";
    return false;
    }
// Sub use case - user should not be able to edit someone else's resource without e0 perm
if(get_edit_access($resource_contributed_by_admin, 0))
    {
    echo "Edit access for contributor (no e0 perm) on someone else's resource - ";
    return false;
    }

// Use case - ability for general users to edit only their contributions when having access to Active state (e0 perm)
$userpermissions[] = "e0";
$edit_access_for_contributor = true;
$force_edit_access_for_contributor = true;
if(!get_edit_access($resource_contributed_by_general, 0))
    {
    echo "Edit access for contributor (e0 perm) - ";
    return false;
    }
// Sub use case - user should not be able to edit someone else's resource even with e0 perm
if(get_edit_access($resource_contributed_by_admin, 0))
    {
    echo "Edit access for contributor (e0 perm) on someone else's resource - ";
    return false;
    }
array_pop($userpermissions);
$edit_access_for_contributor = false;
$force_edit_access_for_contributor = false;







// Tear down
setup_user($original_user_data);
unset($user_admin);
unset($user_general);
unset($user_admin_data);
unset($user_general_data);
unset($get_user_by_username);
unset($resource_contributed_by_admin);
unset($resource_contributed_by_general);
$edit_access_for_contributor = false;
$force_edit_access_for_contributor = false;

// echo "userpermissions = " . json_encode($userpermissions) . PHP_EOL;

return true;