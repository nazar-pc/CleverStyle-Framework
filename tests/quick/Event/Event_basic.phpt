--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', __DIR__);
define('MODULES', __DIR__.'/modules');
class Event_test extends Event {
	static function test () {
		$Event = self::instance();
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
// Simulate new request
		$Event->on('reset_test', function () {
			die('Reset test failed');
		});
		Request::$id = 2;
		self::instance()->fire('reset_test');

		$Event = new self;
		Event::instance_replace($Event);
		var_dump('Initial callbacks and cache', $Event->callbacks, $Event->callbacks_cache);
		$Event->ensure_events_registered();
		var_dump('Callbacks and cache after ensuring they are registered', $Event->callbacks, $Event->callbacks_cache);
		$Event->callbacks = [];
		$Event->ensure_events_registered();
		var_dump('Callbacks and cache after resetting callbacks and ensuring they are registered', $Event->callbacks, $Event->callbacks_cache);

		self::instance_reset();
		$Event = self::instance();

		$Event->once('', function () {
			die('Empty event name not allowed');
		});
		$Event->fire('');
		var_dump('callbacks after registering empty event', $Event->callbacks);
		$Event->once('test_not_callable_1', null);
		$Event->once('test_not_callable_2', 'random_string_not_function');
		var_dump('callbacks after registering not callable', $Event->callbacks);

		$Event->once('', function () {
			die('Empty event name not allowed (once)');
		});
		$Event->fire('');
		var_dump('callbacks after registering empty event (once)', $Event->callbacks);
		$Event->on('test_not_callable_1', null);
		$Event->on('test_not_callable_2', 'random_string_not_function');
		var_dump('callbacks after registering not callable (once)', $Event->callbacks);

		// Non-existent un-registering should just return itself
		if ($Event->off('non-existent') !== $Event) {
			var_dump('Failed un-registering non-existing event');
		}
		var_dump('callbacks after registering empty event', $Event->callbacks);
		$Event->on('test_not_callable_1', null);
		$Event->on('test_not_callable_2', 'random_string_not_function');
		var_dump('callbacks after registering not callable', $Event->callbacks);

		self::instance_reset();
		$Event = self::instance();

		var_dump('test empty return value (on)');
		$Event->on('test_empty_on', function () { });
		var_dump($Event->fire('test_empty_on'));

		var_dump('test empty return value (once)');
		$Event->once('test_empty_once', function () { });
		var_dump($Event->fire('test_empty_once'));

		var_dump('test true return value (on)');
		$Event->on('test_true_on', function () { return true;});
		var_dump($Event->fire('test_true_on'));

		var_dump('test true return value (once)');
		$Event->once('test_true_once', function () { return true;});
		var_dump($Event->fire('test_true_once'));

		var_dump('test truthy return value (on)');
		$Event->on('test_truthy_on', function () { return 1;});
		var_dump($Event->fire('test_truthy_on'));

		var_dump('test truthy return value (once)');
		$Event->once('test_truthy_once', function () { return 1;});
		var_dump($Event->fire('test_truthy_once'));

		var_dump('test false return value (on)');
		$Event->on('test_false_on', function () { return false;});
		var_dump($Event->fire('test_false_on'));

		var_dump('test false return value (once)');
		$Event->once('test_false_once', function () { return false;});
		var_dump($Event->fire('test_false_once'));

		var_dump('test falsy return value (on)');
		$Event->on('test_falsy_on', function () { return 0;});
		var_dump($Event->fire('test_falsy_on'));

		var_dump('test falsy return value (once)');
		$Event->once('test_falsy_once', function () { return 0;});
		var_dump($Event->fire('test_falsy_once'));
	}
}
Event_test::test();
?>
--EXPECT--
This should be visible
string(27) "Initial callbacks and cache"
array(0) {
}
NULL
string(54) "Callbacks and cache after ensuring they are registered"
array(1) {
  ["xyz"]=>
  array(1) {
    [0]=>
    string(15) "module_xyz_test"
  }
}
array(1) {
  ["xyz"]=>
  array(1) {
    [0]=>
    string(15) "module_xyz_test"
  }
}
string(78) "Callbacks and cache after resetting callbacks and ensuring they are registered"
array(1) {
  ["xyz"]=>
  array(1) {
    [0]=>
    string(15) "module_xyz_test"
  }
}
array(1) {
  ["xyz"]=>
  array(1) {
    [0]=>
    string(15) "module_xyz_test"
  }
}
string(39) "callbacks after registering empty event"
array(0) {
}
string(40) "callbacks after registering not callable"
array(0) {
}
string(46) "callbacks after registering empty event (once)"
array(0) {
}
string(47) "callbacks after registering not callable (once)"
array(0) {
}
string(39) "callbacks after registering empty event"
array(0) {
}
string(40) "callbacks after registering not callable"
array(0) {
}
string(28) "test empty return value (on)"
bool(true)
string(30) "test empty return value (once)"
bool(true)
string(27) "test true return value (on)"
bool(true)
string(29) "test true return value (once)"
bool(true)
string(29) "test truthy return value (on)"
bool(true)
string(31) "test truthy return value (once)"
bool(true)
string(28) "test false return value (on)"
bool(false)
string(30) "test false return value (once)"
bool(false)
string(28) "test falsy return value (on)"
bool(true)
string(30) "test falsy return value (once)"
bool(true)
