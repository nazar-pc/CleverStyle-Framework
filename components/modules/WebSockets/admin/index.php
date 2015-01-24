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
if (isset($_POST['listen_port'], $_POST['listen_locally'])) {
	Config::instance()->module('WebSockets')->set([
		'listen_port'    => (int)$_POST['listen_port'] ?: 8080,
		'listen_locally' => (int)$_POST['listen_locally']
	]);
	$Index->save(true);
}
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('websockets_listen_port'),
			h::input([
				'name'  => 'listen_port',
				'value' => $module_data->listen_port ?: 8080
			])
		],
		[
			h::info('websockets_listen_on'),
			h::radio([
				'name'    => 'listen_locally',
				'checked' => $module_data->listen_locally,
				'value'   => [1, 0],
				'in'      => ['127.0.0.1', '0.0.0.0']
			])
		]
	)
);
