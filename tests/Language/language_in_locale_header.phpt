--TEST--
Language in -Locale header
--FILE--
<?php
namespace cs;

include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
$_SERVER['HTTP_X_FACEBOOK_LOCALE'] = 'uk_UA';
Config::instance_stub([
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
]);
$L = Language::instance();
echo $L->clang;
?>
--EXPECT--
uk
