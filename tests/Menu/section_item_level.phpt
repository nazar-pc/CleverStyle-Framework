--TEST--
Section item level menu
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
current_module('System');
Event::instance_stub();
$Menu = Menu::instance();
$Menu->add_section_item(
	'System',
	'Section 1',
	[
		'href'    => "System/section1",
		'primary' => 'uk-active'
	]
);
$Menu->add_item(
	'System',
	'Section 1 item 1',
	[
		'href'    => "System/section1/item1",
		'primary' => true
	]
);
$Menu->add_item(
	'System',
	'Section 1 item 2',
	[
		'href' => "System/section1/item2"
	]
);
$Menu->add_item(
	'System',
	'Section 1 item 3',
	[
		'href'    => "System/section1/item3",
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
	<button icon-after="caret-down" is="cs-button" primary="uk-active" type="button">Section 1</button>
	<nav is="cs-nav-dropdown">
		<nav vertical is="cs-nav-button-group">
			<a href="System/section1/item1" is="cs-link-button" primary>Section 1 item 1</a>
			<a href="System/section1/item2" is="cs-link-button">Section 1 item 2</a>
			<a href="System/section1/item3" is="cs-link-button" primary>Section 1 item 3</a>
		</nav>
	</nav>
	<button icon-after="caret-down" is="cs-button" type="button">Section 2</button>
	<button icon-after="caret-down" is="cs-button" type="button">Section 3</button>
</nav>
