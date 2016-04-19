--TEST--
Section level menu
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Request::instance()->current_module = 'System';
Event::instance_stub();
$Menu = Menu::instance();
$Menu->add_section_item(
	'System',
	'Section 1',
	[
		'href'    => "System/section1",
		'primary' => true
	]
);
$Menu->add_section_item(
	'System',
	'Section 2',
	[
		'href' => "System/section2"
	]
);
$Menu->add_section_item(
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
	<button icon-after="caret-down" is="cs-button" primary type="button">Section 1</button>
	<button icon-after="caret-down" is="cs-button" type="button">Section 2</button>
	<button icon-after="caret-down" is="cs-button" type="button">Section 3</button>
</nav>
