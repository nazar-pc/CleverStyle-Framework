<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
include __DIR__.'/../../custom_loader.php';
include __DIR__.'/_Fake_engine.php';
Config::instance_stub(
	[
		'core' => [
			'db_balance'     => 0,
			'db_mirror_mode' => 1
		],
		'db'   => [
			0 => [
				'mirrors' => [
					[
						'type'     => 'Fake',
						'host'     => 'localhost',
						'name'     => 'database',
						'user'     => 'user',
						'password' => 'db 0, mirror',
						'charset'  => 'utf8',
						'prefix'   => '__prefix0__'
					]
				]
			],
			2 => [
				'mirrors' => [
					[
						'type'     => 'Fake',
						'host'     => 'localhost',
						'name'     => 'database',
						'user'     => 'user',
						'password' => 'db 2, mirror',
						'charset'  => 'utf8',
						'prefix'   => '__prefix2__'
					]
				]
			]
		]
	]
);
Core::instance_stub(
	[
		'db_type'     => 'Fake',
		'db_host'     => 'localhost',
		'db_name'     => 'database',
		'db_user'     => 'user',
		'db_password' => 'db 0',
		'db_charset'  => 'utf8',
		'db_prefix'   => '__prefix__'
	]
);
Language::instance_stub();
DB\Fake::$connected_fake = function () {
	return true;
};
