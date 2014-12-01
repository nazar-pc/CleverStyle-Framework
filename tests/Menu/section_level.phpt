--TEST--
Section level menu
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
current_module('System');
Trigger::instance_mock();
$Menu	= Menu::instance();
$Menu->add_section_item(
	'System',
	'Section 1',
	"System/section1",
	[
		'class'	=> 'uk-active'
	]
);
$Menu->add_section_item(
	'System',
	'Section 2',
	"System/section2"
);
$Menu->add_section_item(
	'System',
	'Section 3',
	"System/section3"
);
echo $Menu->get_menu();
?>
--EXPECT--
<ul class="uk-subnav uk-subnav-pill">
	<li class="uk-active" data-uk-dropdown="">
		<a href="System/section1">Section 1</a>
	</li>
	<li data-uk-dropdown="">
		<a href="System/section2">Section 2</a>
	</li>
	<li data-uk-dropdown="">
		<a href="System/section3">Section 3</a>
	</li>
</ul>
