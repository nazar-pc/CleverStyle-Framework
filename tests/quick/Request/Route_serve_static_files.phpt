--FILE--
<?php
namespace cs;
use
	Exception;

include __DIR__.'/../../unit.php';
define('DIR', make_tmp_dir());
define('MODULES', make_tmp_dir());

function test_serving_path ($path) {
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir(DIR.dirname($path), 0770, true);
	file_put_contents(DIR.$path, $path);

	$Request  = Request::instance();
	$Response = Response::instance();

	try {
		$Request->path = $path;
		$Response->init();
		$Request->init_route();
	} catch (ExitException $e) {
		var_dump($e->getCode());
		if (is_resource($Response->body_stream)) {
			rewind($Response->body_stream);
			var_dump(stream_get_contents($Response->body_stream));
		}
		var_dump($Response->headers);
	} catch (Exception $e) {
	}
}

$Request = Request::instance();
$Request->init_server(['CLI' => true]);
$Request->method = 'GET';
Event::instance()->on(
	'System/Request/routing_replace/before',
	function () {
		throw new Exception;
	}
);

var_dump('Static public cache');
test_serving_path('/storage/pcache/file.css');
test_serving_path('/storage/pcache/file.js');
test_serving_path('/storage/pcache/file.html');

var_dump('Static public cache (not allowed)');
test_serving_path('/storage/pcache/file.xyz');

var_dump('Static public storage');
test_serving_path('/storage/public/file.php');
test_serving_path('/storage/public/file.xyz');

var_dump('System includes');
test_serving_path('/includes/html/file.html');
test_serving_path('/includes/html/file.js');
test_serving_path('/includes/js/file.js');
test_serving_path('/includes/html/file.css');
test_serving_path('/includes/css/file.css');
test_serving_path('/includes/img/file.jpg');

var_dump('Module includes');
test_serving_path('/modules/Module_name/includes/html/file.html');
test_serving_path('/modules/Module_name/includes/html/file.js');
test_serving_path('/modules/Module_name/includes/js/file.js');
test_serving_path('/modules/Module_name/includes/html/file.css');
test_serving_path('/modules/Module_name/includes/css/file.css');
test_serving_path('/modules/Module_name/includes/img/file.jpg');

var_dump('Theme includes');
test_serving_path('/themes/Theme_name/html/file.html');
test_serving_path('/themes/Theme_name/html/file.js');
test_serving_path('/themes/Theme_name/js/file.js');
test_serving_path('/themes/Theme_name/html/file.css');
test_serving_path('/themes/Theme_name/css/file.css');
test_serving_path('/themes/Theme_name/img/file.jpg');

var_dump('PHP files not allowed');
test_serving_path('/includes/html/file.php');
test_serving_path('/modules/includes/html/file.php');
test_serving_path('/themes/Theme_name/html/file.php');

var_dump('Other files not allowed');
test_serving_path('/.htaccess');
test_serving_path('/includes/html/.htaccess');
test_serving_path('/config/main.json');
test_serving_path('/core/fs.json');

var_dump('Non-get method');
$Request->method = 'POST';
test_serving_path('/storage/pcache/file.css');
?>
--EXPECT--
string(19) "Static public cache"
int(200)
string(24) "/storage/pcache/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(23) "/storage/pcache/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(25) "/storage/pcache/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(33) "Static public cache (not allowed)"
int(403)
array(0) {
}
string(21) "Static public storage"
int(200)
string(24) "/storage/public/file.php"
array(3) {
  ["x-frame-options"]=>
  array(1) {
    [0]=>
    string(4) "DENY"
  }
  ["content-type"]=>
  array(1) {
    [0]=>
    string(24) "application/octet-stream"
  }
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(24) "/storage/public/file.xyz"
array(3) {
  ["x-frame-options"]=>
  array(1) {
    [0]=>
    string(4) "DENY"
  }
  ["content-type"]=>
  array(1) {
    [0]=>
    string(24) "application/octet-stream"
  }
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(15) "System includes"
int(200)
string(24) "/includes/html/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(22) "/includes/html/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(20) "/includes/js/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(23) "/includes/html/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(22) "/includes/css/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(22) "/includes/img/file.jpg"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(15) "Module includes"
int(200)
string(44) "/modules/Module_name/includes/html/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(42) "/modules/Module_name/includes/html/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(40) "/modules/Module_name/includes/js/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(43) "/modules/Module_name/includes/html/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(42) "/modules/Module_name/includes/css/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(42) "/modules/Module_name/includes/img/file.jpg"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(14) "Theme includes"
int(200)
string(33) "/themes/Theme_name/html/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(31) "/themes/Theme_name/html/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(29) "/themes/Theme_name/js/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(32) "/themes/Theme_name/html/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(31) "/themes/Theme_name/css/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(31) "/themes/Theme_name/img/file.jpg"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(21) "PHP files not allowed"
int(404)
array(0) {
}
int(404)
array(0) {
}
int(404)
array(0) {
}
string(23) "Other files not allowed"
int(404)
array(0) {
}
int(404)
array(0) {
}
string(14) "Non-get method"
