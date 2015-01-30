--TEST--
Section item level menu
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
current_module('System');
Event::instance_stub();
$Menu	= Menu::instance();
$Menu->add_section_item(
	'System',
	'Section 1',
	"System/section1",
	[
		'class'	=> 'uk-active'
	]
);
$Menu->add_item(
	'System',
	'Section 1 item 1',
	"System/section1/item1",
	[
		'class'	=> 'uk-active'
	]
);
$Menu->add_item(
	'System',
	'Section 1 item 2',
	"System/section1/item2"
);
$Menu->add_item(
	'System',
	'Section 1 item 3',
	"System/section1/item3",
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
		<a href="System/section1">
			Section 1 <span class=" uk-icon-caret-down"></span>
		</a>
		<div class="uk-dropdown uk-dropdown-small">
			<ul class="uk-nav uk-nav-dropdown">
				<li class="uk-active">
					<a href="System/section1/item1">Section 1 item 1</a>
				</li>
				<li>
					<a href="System/section1/item2">Section 1 item 2</a>
				</li>
				<li class="uk-active">
					<a href="System/section1/item3">Section 1 item 3</a>
				</li>
			</ul>
		</div>
	</li>
	<li data-uk-dropdown="">
		<a href="System/section2">Section 2</a>
	</li>
	<li data-uk-dropdown="">
		<a href="System/section3">Section 3</a>
	</li>
</ul>
