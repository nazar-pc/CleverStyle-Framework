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
test_serving_path('/storage/public_cache/file.css');
test_serving_path('/storage/public_cache/file.js');
test_serving_path('/storage/public_cache/file.html');

var_dump('Static public cache (not allowed)');
test_serving_path('/storage/public_cache/file.xyz');

var_dump('Static public storage');
test_serving_path('/storage/public/file.php');
test_serving_path('/storage/public/file.xyz');

var_dump('System assets');
test_serving_path('/assets/html/file.html');
test_serving_path('/assets/html/file.js');
test_serving_path('/assets/js/file.js');
test_serving_path('/assets/html/file.css');
test_serving_path('/assets/css/file.css');
test_serving_path('/assets/img/file.jpg');

var_dump('Module assets');
test_serving_path('/modules/Module_name/assets/html/file.html');
test_serving_path('/modules/Module_name/assets/html/file.js');
test_serving_path('/modules/Module_name/assets/js/file.js');
test_serving_path('/modules/Module_name/assets/html/file.css');
test_serving_path('/modules/Module_name/assets/css/file.css');
test_serving_path('/modules/Module_name/assets/img/file.jpg');

var_dump('Theme assets');
test_serving_path('/themes/Theme_name/html/file.html');
test_serving_path('/themes/Theme_name/html/file.js');
test_serving_path('/themes/Theme_name/js/file.js');
test_serving_path('/themes/Theme_name/html/file.css');
test_serving_path('/themes/Theme_name/css/file.css');
test_serving_path('/themes/Theme_name/img/file.jpg');

var_dump('PHP files not allowed');
test_serving_path('/assets/html/file.php');
test_serving_path('/modules/assets/html/file.php');
test_serving_path('/themes/Theme_name/html/file.php');

var_dump('Other files not allowed');
test_serving_path('/.htaccess');
test_serving_path('/assets/html/.htaccess');
test_serving_path('/config/main.json');
test_serving_path('/core/fs.json');

var_dump('Non-get method');
$Request->method = 'POST';
test_serving_path('/storage/public_cache/file.css');
?>
--EXPECT--
string(19) "Static public cache"
int(200)
string(30) "/storage/public_cache/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(29) "/storage/public_cache/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(31) "/storage/public_cache/file.html"
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
string(13) "System assets"
int(200)
string(22) "/assets/html/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(20) "/assets/html/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(18) "/assets/js/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(21) "/assets/html/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(20) "/assets/css/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(20) "/assets/img/file.jpg"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(13) "Module assets"
int(200)
string(42) "/modules/Module_name/assets/html/file.html"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(40) "/modules/Module_name/assets/html/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(38) "/modules/Module_name/assets/js/file.js"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(41) "/modules/Module_name/assets/html/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(40) "/modules/Module_name/assets/css/file.css"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
int(200)
string(40) "/modules/Module_name/assets/img/file.jpg"
array(1) {
  ["cache-control"]=>
  array(1) {
    [0]=>
    string(23) "max-age=2592000, public"
  }
}
string(12) "Theme assets"
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
