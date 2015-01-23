<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\WebSockets;
use
	h,
	cs\Config,
	cs\Index;
$Index               = Index::instance();
$Index->apply_button = false;
$module_data         = Config::instance()->module('WebSockets');
if (isset($_POST['external_port'], $_POST['external_port_secure'])) {
	Config::instance()->module('WebSockets')->set([
		'external_port'        => (int)$_POST['external_port'],
		'external_port_secure' => (int)$_POST['external_port_secure']
	]);
	$Index->save(true);
}
$Index->content(
	h::{'cs-table[right-left] cs-table-row cs-table-cell'}(
		[
			h::info('websockets_external_port'),
			h::input([
				'name'  => 'external_port',
				'value' => $module_data->external_port ?: 80
			])
		],
		[
			h::info('websockets_external_port_secure'),
			h::input([
				'name'  => 'external_port_secure',
				'value' => $module_data->external_port_secure ?: 443
			])
		]
	)
);
