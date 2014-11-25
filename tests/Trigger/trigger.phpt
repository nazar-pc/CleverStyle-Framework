--TEST--
Test triggers functionality
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Trigger	= Trigger::instance();
$Trigger->register('trigger/empty_return', function () {
	return;
});
if (!$Trigger->run('trigger/empty_return')) {
	die('Return without value should be considered as true');
}

$Trigger->register('trigger/false_return', function () {
	return false;
});
if ($Trigger->run('trigger/false_return')) {
	die('Return false actually returns true');
}

$Trigger->register('trigger/data_modification', function ($data) {
	$data['test']	= 'passed';
});
$test	= 'failed';
$Trigger->run('trigger/data_modification', [
	'test'	=> &$test
]);
if ($test != 'passed') {
	die('Passing data by reference not working');
}

$Trigger->register('trigger/data_modification', function ($data) {
	$data['should_be_failed']	= true;
}, true);
$test				= 'failed';
$should_be_failed	= false;
$Trigger->run('trigger/data_modification', [
	'test'				=> &$test,
	'should_be_failed'	=> &$should_be_failed
]);
if ($test != 'failed' || $should_be_failed != true) {
	die('Trigger replacement not working');
}

echo 'Done';
?>
--EXPECT--
Done
