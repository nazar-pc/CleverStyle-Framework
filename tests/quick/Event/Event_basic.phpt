--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', __DIR__);
define('MODULES', __DIR__.'/modules');
define('PLUGINS', __DIR__.'/plugins');
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
  array(2) {
    [0]=>
    string(15) "module_xyz_test"
    [1]=>
    string(15) "plugin_xyz_test"
  }
}
array(1) {
  ["xyz"]=>
  array(2) {
    [0]=>
    string(15) "module_xyz_test"
    [1]=>
    string(15) "plugin_xyz_test"
  }
}
string(78) "Callbacks and cache after resetting callbacks and ensuring they are registered"
array(1) {
  ["xyz"]=>
  array(2) {
    [0]=>
    string(15) "module_xyz_test"
    [1]=>
    string(15) "plugin_xyz_test"
  }
}
array(1) {
  ["xyz"]=>
  array(2) {
    [0]=>
    string(15) "module_xyz_test"
    [1]=>
    string(15) "plugin_xyz_test"
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
