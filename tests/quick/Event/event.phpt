--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
$Event = Event::instance();
$Event->on('event/empty_return', function () {
	return;
});
if (!$Event->fire('event/empty_return')) {
	die('Return without value should be considered as true');
}

$Event->on('event/false_return', function () {
	return false;
});
if ($Event->fire('event/false_return')) {
	die('Return false actually returns true');
}

$Event->on('event/data_modification', function ($data) {
	$data['test'] = 'passed';
});
$test = 'failed';
$Event->fire('event/data_modification', [
	'test' => &$test
]);
if ($test != 'passed') {
	die('Passing data by reference not working');
}

$Event->off('event/data_modification');
$test = 'failed';
$Event->fire('event/data_modification', [
	'test' => &$test
]);
if ($test != 'failed') {
	die('Event unsubscribing not working');
}

$callback = function () {
	echo "This should not be visible\n";
};
$Event->on('off_test', $callback);
$Event->on('off_test', function () {
	echo "This should be visible\n";
});
// Only one event unsubscribed
$Event->off('off_test', $callback);
$Event->fire('off_test');
?>
Done
--EXPECT--
This should be visible
Done
