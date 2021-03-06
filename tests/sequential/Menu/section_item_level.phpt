--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
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
<cs-group>
	<cs-button icon-after="caret-down" primary>
		<button type="button">Section 1</button>
	</cs-button>
	<cs-dropdown>
		<cs-group vertical>
			<cs-link-button primary>
				<a href="System/section1/item1">Section 1 item 1</a>
			</cs-link-button>
			<cs-link-button>
				<a href="System/section1/item2">Section 1 item 2</a>
			</cs-link-button>
			<cs-link-button primary>
				<a href="System/section1/item3">Section 1 item 3</a>
			</cs-link-button>
		</cs-group>
	</cs-dropdown>
	<cs-button icon-after="caret-down">
		<button type="button">Section 2</button>
	</cs-button>
	<cs-button icon-after="caret-down">
		<button type="button">Section 3</button>
	</cs-button>
</cs-group>
