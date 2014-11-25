--TEST--
Language fixation
--FILE--
<?php
namespace cs\custom;
use cs\Language;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
class Core extends \cs\Core {
	protected function construct () {
		parent::construct();
		$this->config['fixed_language'] = true;
	}
}
$L	= Language::instance();
$L->change('Українська');
echo $L->home;
?>
--EXPECT--
Home
