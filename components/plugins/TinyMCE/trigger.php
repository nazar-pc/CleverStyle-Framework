<?php
global $Core;
$Core->register_trigger([
	'admin' => [
		'System' => [
			'components' => [
				'plugins' => [
					'disable' => function ($data) {
						$plugin = basename(__DIR__);
						if ($data['name'] == $plugin && _file_exists(PCACHE.DS.'plugin.'.$plugin.'.js')) {
							_unlink(PCACHE.DS.'plugin.'.$plugin.'.js');
						}
					}
				]
			]
		]
	]
]);