--FILE--
<?php
namespace cs\h {
	function uniqid ($prefix) {
		return $prefix.'random_string';
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	$Request      = Request::instance();
	$Request->uri = '/';
	Config::instance_stub(
		[],
		[
			'base_url' => 'http://cscms.travis'
		]
	);
	Session::instance_stub(
		[],
		[
			'get_id' => md5('session id')
		]
	);
	Page::instance_stub(
		[],
		[
			'replace' => function (...$arguments) {
				var_dump('cs\Page::replace() called with', $arguments);
			}
		]
	);
	Language::instance_stub(
		[
			'info_test'      => 'Info test short',
			'info_test_info' => 'Info test details'
		]
	);
	class h_test extends \h {
		static function test () {
			var_dump('Hash url');
			var_dump(self::url_with_hash('#xyz'));

			var_dump('Absolute url');
			var_dump(self::absolute_url('Module_name'));

			var_dump('Form CSRF');
			var_dump(self::form());
			Session::instance_reset();
			var_dump(self::form());

			var_dump('Tooltip');
			var_dump(self::p(['tooltip' => 'Tooltip content']));
			var_dump(self::input(['tooltip' => 'Tooltip content']));

			var_dump('Indentation protection');
			var_dump(self::pre('Pre content'));
			var_dump(self::textarea('Textarea content'));

			var_dump('Info pseudo-element');
			var_dump(self::info('info_test'));
			var_dump(self::info(['info_test']));
			var_dump(
				self::info(
					'info_test',
					[
						'data-x' => '$i[data_x]',
						'insert' => [
							[
								'data_x' => 1
							],
							[
								'data_x' => 2
							]
						]
					]
				)
			);
			var_dump(self::info(false));

			var_dump('Icon');
			var_dump(self::icon('home'));
			var_dump(
				self::icon(
					'home',
					[
						'data-x' => '$i[data_x]',
						'insert' => [
							[
								'data_x' => 1
							],
							[
								'data_x' => 2
							]
						]
					]
				)
			);
			var_dump(self::icon(false));

			var_dump('Checkbox');
			var_dump(
				self::checkbox(
					[
						'name'    => 'active',
						'checked' => 1,
						'value'   => 1,
						'in'      => 'Active'
					]
				)
			);
			var_dump(
				self::checkbox(
					[
						'name'    => ['inactive', 'active'],
						'checked' => 1,
						'value'   => [0, 1],
						'in'      => ['Inactive', 'Active']
					]
				)
			);
			var_dump(
				self::checkbox(
					[
						'name'    => '$i[name]',
						'checked' => 1,
						'value'   => '$i[value]',
						'in'      => '$i[in]',
						'insert'  => [
							[
								'name'  => 'active',
								'value' => 1,
								'in'    => 'Active'
							],
							[
								'name'  => 'inactive',
								'value' => 0,
								'in'    => 'Inactive'
							]
						]
					]
				)
			);
			var_dump(
				self::checkbox(
					[
						[
							'name'    => 'active',
							'checked' => 1,
							'value'   => 1,
							'in'      => 'Active'
						],
						[
							'name'  => 'inactive',
							'value' => 0,
							'in'    => 'Inactive'
						]
					]
				)
			);
			var_dump(self::checkbox(false));

			var_dump('Radio');
			var_dump(
				self::radio(
					[
						'name'    => 'on',
						'checked' => 1,
						'value'   => [0, 1],
						'in'      => ['Off', 'On']
					]
				)
			);
			var_dump(
				self::radio(
					[
						'name'  => 'toggle',
						'value' => [0, 1],
						'in'    => ['Off', 'On']
					]
				)
			);
			var_dump(
				self::radio(
					[
						'name'    => '$i[name]',
						'checked' => 1,
						'value'   => '$i[value]',
						'in'      => '$i[in]',
						'insert'  => [
							[
								'name'  => 'active',
								'value' => 1,
								'in'    => 'Active'
							],
							[
								'name'  => 'inactive',
								'value' => 0,
								'in'    => 'Inactive'
							]
						]
					]
				)
			);
			var_dump(
				self::radio(
					[
						[
							'name'    => 'toggle',
							'checked' => 1,
							'value'   => 1,
							'in'      => 'Active'
						],
						[
							'name'    => 'toggle',
							'checked' => 1,
							'value'   => 0,
							'in'      => 'Inactive'
						]
					]
				)
			);
			var_dump(self::radio(false));
		}
	}
	h_test::test();
}
?>
--EXPECT--
string(8) "Hash url"
string(5) "/#xyz"
string(12) "Absolute url"
string(31) "http://cscms.travis/Module_name"
string(9) "Form CSRF"
string(108) "<form method="post">
	<input name="session" type="hidden" value="7f12af5437c1c4bf6cb7a0756d9f84eb">
</form>
"
string(28) "<form method="post"></form>
"
string(7) "Tooltip"
string(62) "<p tooltip="Tooltip content">
	<cs-tooltip></cs-tooltip>
</p>
"
string(72) "<input tooltip="Tooltip content" type="text"> <cs-tooltip></cs-tooltip>
"
string(22) "Indentation protection"
string(30) "cs\Page::replace() called with"
array(2) {
  [0]=>
  string(26) "html_replace_random_string"
  [1]=>
  string(11) "Pre content"
}
string(37) "<pre>html_replace_random_string</pre>"
string(30) "cs\Page::replace() called with"
array(2) {
  [0]=>
  string(26) "html_replace_random_string"
  [1]=>
  string(16) "Textarea content"
}
string(47) "<textarea>html_replace_random_string</textarea>"
string(19) "Info pseudo-element"
string(85) "<span tooltip="Info test details">
	Info test short<cs-tooltip></cs-tooltip>
</span>
"
string(85) "<span tooltip="Info test details">
	Info test short<cs-tooltip></cs-tooltip>
</span>
"
string(192) "<span data-x="1" tooltip="Info test details">
	Info test short<cs-tooltip></cs-tooltip>
</span>
<span data-x="2" tooltip="Info test details">
	Info test short<cs-tooltip></cs-tooltip>
</span>
"
string(0) ""
string(4) "Icon"
string(33) "<cs-icon icon="home"></cs-icon>
 "
string(88) "<cs-icon data-x="1" icon="home"></cs-icon>
 <cs-icon data-x="2" icon="home"></cs-icon>
 "
string(0) ""
string(8) "Checkbox"
string(125) "<cs-label-switcher>
	<label>
		<input checked name="active" type="checkbox" value="1"> Active
	</label>
</cs-label-switcher>
"
string(246) "<cs-label-switcher>
	<label>
		<input name="inactive" type="checkbox" value="0"> Inactive
	</label>
</cs-label-switcher>
<cs-label-switcher>
	<label>
		<input checked name="active" type="checkbox" value="1"> Active
	</label>
</cs-label-switcher>
"
string(246) "<cs-label-switcher>
	<label>
		<input checked name="active" type="checkbox" value="1"> Active
	</label>
</cs-label-switcher>
<cs-label-switcher>
	<label>
		<input name="inactive" type="checkbox" value="0"> Inactive
	</label>
</cs-label-switcher>
"
string(246) "<cs-label-switcher>
	<label>
		<input checked name="active" type="checkbox" value="1"> Active
	</label>
</cs-label-switcher>
<cs-label-switcher>
	<label>
		<input name="inactive" type="checkbox" value="0"> Inactive
	</label>
</cs-label-switcher>
"
string(0) ""
string(5) "Radio"
string(213) "<cs-label-button>
	<label>
		<input name="on" type="radio" value="0"> Off
	</label>
</cs-label-button>
<cs-label-button>
	<label>
		<input checked name="on" type="radio" value="1"> On
	</label>
</cs-label-button>
"
string(221) "<cs-label-button>
	<label>
		<input checked name="toggle" type="radio" value="0"> Off
	</label>
</cs-label-button>
<cs-label-button>
	<label>
		<input name="toggle" type="radio" value="1"> On
	</label>
</cs-label-button>
"
string(232) "<cs-label-button>
	<label>
		<input checked name="active" type="radio" value="1"> Active
	</label>
</cs-label-button>
<cs-label-button>
	<label>
		<input name="inactive" type="radio" value="0"> Inactive
	</label>
</cs-label-button>
"
string(230) "<cs-label-button>
	<label>
		<input checked name="toggle" type="radio" value="1"> Active
	</label>
</cs-label-button>
<cs-label-button>
	<label>
		<input name="toggle" type="radio" value="0"> Inactive
	</label>
</cs-label-button>
"
string(0) ""
