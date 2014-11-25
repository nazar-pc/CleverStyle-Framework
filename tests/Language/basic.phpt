--TEST--
Basic Language functionality
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
$L	= Language::instance();
echo $L->home."\n";
echo $L->profile_of_user('Lil Wayne')."\n";
echo $L->time(20, 's')."\n";
$L->change('Українська');
echo $L->to_locale('1 January, 2000')."\n";
?>
--EXPECT--
Home
Profile of user Lil Wayne
20 seconds
1 Січня, 2000
