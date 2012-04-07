<?php
global $Index;
$Index->register_trigger(
	array(
		'admin' => array(
			'System' => array(
				'components' => array(
					'plugins' => array(
						'disable' => function ($data) {
							if ($data['name'] == 'TinyMCE' && _file_exists(PCACHE.DS.'plugin.'.$data['name'].'.js')) {
								_unlink(PCACHE.DS.'plugin.'.$data['name'].'.js');
							}
						}
					)
				)
			)
		)
	)
);