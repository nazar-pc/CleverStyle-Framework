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
	<a href="System/section1" is="cs-link-button" primary>Section 1</a>
	<a href="System/section2" is="cs-link-button">Section 2</a>
	<a href="System/section3" is="cs-link-button">Section 3</a>
</nav>
