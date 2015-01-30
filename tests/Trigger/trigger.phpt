--TEST--
Triggers functionality
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Event = Event::instance();
$Event->on('trigger/empty_return', function () {
	return;
});
if (!$Event->fire('trigger/empty_return')) {
	die('Return without value should be considered as true');
}

$Event->on('trigger/false_return', function () {
	return false;
});
if ($Event->fire('trigger/false_return')) {
	die('Return false actually returns true');
}

$Event->on('trigger/data_modification', function ($data) {
	$data['test'] = 'passed';
});
$test = 'failed';
$Event->fire('trigger/data_modification', [
	'test' => &$test
]);
if ($test != 'passed') {
	die('Passing data by reference not working');
}

$Event->off('trigger/data_modification');
$test = 'failed';
$Event->fire('trigger/data_modification', [
	'test' => &$test
]);
if ($test != 'failed') {
	die('Event unsubscribing not working');
}

echo 'Done';
?>
--EXPECT--
Done
