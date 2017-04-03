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
<nav is="cs-nav-button-group">
	<cs-button>
		<button primary type="button">
			Section 1 <cs-icon icon="caret-down"></cs-icon>
		</button>
	</cs-button>
	<cs-button>
		<button type="button">
			Section 2 <cs-icon icon="caret-down"></cs-icon>
		</button>
	</cs-button>
	<cs-button>
		<button type="button">
			Section 3 <cs-icon icon="caret-down"></cs-icon>
		</button>
	</cs-button>
</nav>
