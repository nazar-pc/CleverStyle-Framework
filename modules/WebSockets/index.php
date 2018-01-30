<?php
/**
 * @package  WebSockets
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\WebSockets;
use
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\Request;

if (php_sapi_name() == 'cli') {
	Server::instance()->run(isset($_GET['address']) ? $_GET['address'] : null);
} else {
	Page::instance()->interface = false;
	$Config                     = Config::instance();
	$module_data                = $Config->module('WebSockets');
	$rc                         = Request::instance()->route;
	if ($module_data->security_key !== @$rc[0]) {
		throw new ExitException(400);
	}
	if (is_server_running()) {
		echo 'Server already running';
		return;
	}
	if (is_exec_available()) {
		cross_platform_server_in_background();
		echo 'Server started';
	} else {
		set_time_limit(0);
		ignore_user_abort(1);
		if (!isset($rc[1])) {
			// Supervising for the case if server will go down
			while (true) {
				if (is_server_running()) {
					sleep(10);
					continue;
				}
				file_get_contents(
					$Config->base_url().'/WebSockets/'.$Config->module('WebSockets')->security_key.'/supervised',
					null,
					stream_context_create(
						[
							'http' => [
								'timeout' => 0
							]
						]
					)
				);
			}
		} else {
			Server::instance()->run();
		}
	}
}
