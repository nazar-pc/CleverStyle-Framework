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
	cs\Index,
	cs\Language\Prefix;
$Index       = Index::instance();
$L           = new Prefix('websockets_');
$Config      = Config::instance();
$module_data = $Config->module('WebSockets');
if (isset($_POST['start_server']) && !is_server_running()) {
	file_get_contents(
		$Config->base_url().'/WebSockets/'.$Config->module('WebSockets')->security_key,
		null,
		stream_context_create(
			[
				'http' => [
					'timeout' => 5
				]
			]
		)
	);
	sleep(1);
} elseif (isset($_POST['listen_port'], $_POST['listen_locally'])) {
	$module_data->set(
		[
			'security_key'   => $_POST['security_key'],
			'listen_port'    => (int)$_POST['listen_port'] ?: 8080,
			'listen_locally' => (int)$_POST['listen_locally']
		]
	);
	$Index->save(true);
}
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('websockets_security_key'),
			h::input(
				[
					'name'  => 'security_key',
					'value' => $module_data->security_key
				]
			)
		],
		[
			h::info('websockets_listen_port'),
			h::input(
				[
					'name'  => 'listen_port',
					'value' => $module_data->listen_port ?: 8080
				]
			)
		],
		[
			h::info('websockets_listen_on'),
			h::radio(
				[
					'name'    => 'listen_locally',
					'checked' => $module_data->listen_locally,
					'value'   => [1, 0],
					'in'      => ['127.0.0.1', '0.0.0.0']
				]
			)
		],
		[
			h::info('websockets_dns_server'),
			h::input(
				[
					'name'  => 'dns_server',
					'value' => $module_data->dns_server ?: '127.0.0.1'
				]
			)
		]
	).
	(is_server_running()
		? h::{'p.uk-alert.uk-alert-success'}($L->server_is_running)
		: h::{'p.uk-alert.uk-alert-warning'}("$L->server_is_not_running. ".h::{'button[is=cs-button][name=start_server][type=submit]'}($L->start_server))
	)
);
