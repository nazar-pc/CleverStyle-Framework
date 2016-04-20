--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
$_SERVER['HTTP_X_FACEBOOK_LOCALE'] = 'uk_UA';
Request::instance()->init_server($_SERVER);
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
