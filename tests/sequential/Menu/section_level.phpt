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
	<cs-button icon-after="caret-down">
		<button type="button">Section 2</button>
	</cs-button>
	<cs-button icon-after="caret-down">
		<button type="button">Section 3</button>
	</cs-button>
</cs-group>
