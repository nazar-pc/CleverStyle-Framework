--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
$L	= new Language\Prefix('system_');
echo $L->system_home."\n";
echo $L->home."\n";
echo $L->admin_users_permissions_for_user('Lil Wayne')."\n";
echo $L->system_admin_users_permissions_for_user('Lil Wayne')."\n";
echo $L->time(20, 's')."\n";
?>
--EXPECT--
Home
Home
Permissions for the user Lil Wayne
Permissions for the user Lil Wayne
20 seconds
