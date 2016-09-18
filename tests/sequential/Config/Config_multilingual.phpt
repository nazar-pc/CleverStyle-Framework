--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
var_dump('Save (multilingual)');
$Config                           = Config::instance();
$Config->core['multilingual']     = 1;
$Config->core['active_languages'] = [
	'English',
	'Ukrainian'
];
$Config->save();
$site_name = $Config->core['site_name'];
var_dump($site_name);
$Config->core['site_name'] = 'en';
$Config->save();
$L = Language::instance();
var_dump($Config->core['site_name']);
$L->change('Ukrainian');
$Config->core['site_name'] = 'uk';
$Config->save();
var_dump($Config->core['site_name']);
$L->change('English');
var_dump($Config->core['site_name']);
$L->change('Ukrainian');
var_dump($Config->core['site_name']);
$L->change('English');
$Config->core['multilingual']     = 0;
$Config->core['active_languages'] = ['English'];
$Config->core['site_name']        = $site_name;
$Config->save();
?>
--EXPECT--
string(19) "Save (multilingual)"
string(8) "Web-site"
string(2) "en"
string(2) "uk"
string(2) "en"
string(2) "uk"
