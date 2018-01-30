<?php
/**
 * @package  Deferred tasks
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Deferred_tasks;
use
	cs\ExitException,
	cs\Config,
	cs\Request;

$Config      = Config::instance();
$module_data = $Config->module('Deferred_tasks');
$rc          = Request::instance()->route;
if ($module_data->security_key !== $rc[0]) {
	throw new ExitException(400);
}
$Deferred_tasks = Deferred_tasks::instance();
if (isset($rc[1])) {
	$Deferred_tasks->run_task($rc[1]);
} else {
	$Deferred_tasks->run_tasks();
}
