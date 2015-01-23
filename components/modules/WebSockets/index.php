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
	cs\Config;
require DIR.'/Ratchet/vendor/autoload.php';
// TODO: security check here
$Config = Config::instance();
// Try to connect to socket if exists
$socket = fsockopen(
	explode('/', $Config->base_url())[1],
	$Config->module('WebSockets')->{$_SERVER->secure ? 'external_port' : 'external_port_secure'},
	$error,
	$error,
	2
);
if ($socket) {
	fclose($socket);
	return;
}
ignore_user_abort(1);
Server::instance()->run();
