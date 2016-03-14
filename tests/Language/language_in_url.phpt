--TEST--
Language in url
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Request       = Request::instance();
$Request->path = '/uk';
$Request->uri  = '/uk';
Config::instance_stub(
	[
		'core' => [
			'multilingual'     => true,
			'language'         => 'English',
			'active_languages' => array_unique(
				array_merge(
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
				)
			)
		]
	]
);
$L = Language::instance();
echo $L->clang;
?>
--EXPECT--
uk
