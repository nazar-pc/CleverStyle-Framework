--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Request::instance()->current_module = 'System';
Event::instance_stub();
$Menu = Menu::instance();
$Menu->add_item(
	'System',
	'Section 1',
	[
		'href'    => "System/section1",
		'primary' => true
	]
);
$Menu->add_item(
	'System',
	'Section 2',
	[
		'href' => "System/section2"
	]
);
$Menu->add_item(
	'System',
	'Section 3',
	[
		'href' => "System/section3"
	]
);
echo $Menu->get_menu();
?>
--EXPECT--
<nav is="cs-nav-button-group">
	<cs-link-button primary>
		<a href="System/section1">Section 1</a>
	</cs-link-button>
	<cs-link-button>
		<a href="System/section2">Section 2</a>
	</cs-link-button>
	<cs-link-button>
		<a href="System/section3">Section 3</a>
	</cs-link-button>
</nav>
