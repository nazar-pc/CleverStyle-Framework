<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

function do_request () {
	try {
		Request::instance()->init_from_globals();
		Response::instance()->init_with_typical_default_settings();
		App::instance()->execute();
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson());
		}
	}
}
