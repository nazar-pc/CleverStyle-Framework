<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

function do_request () {
	try {
		Response::instance()->init_with_typical_default_settings();
		Request::instance()->init_from_globals();
		App::instance()->execute();
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson());
		}
	}
}

function do_api_request ($method, $path, $data = [], $query = [], $cookie = []) {
	$_SERVER['REQUEST_URI']           = "/$path";
	$_SERVER['REQUEST_METHOD']        = strtoupper($method);
	$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
	$_POST                            = $data;
	$_GET                             = $query;
	$_COOKIE                          = $cookie;
	$Response                         = Response::instance();
	try {
		$Response->init();
		Request::instance()->init_from_globals();
		App::instance()->execute();
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson());
		}
	}
	var_dump($Response->code, $Response->headers, $Response->body);
}

/**
 * Create temporary directory and return path to it
 *
 * Directory will be removed after script execution
 *
 * @return string
 */
function make_tmp_dir () {
	$tmp = __DIR__.'/'.uniqid('.tmp', true);
	/** @noinspection MkdirRaceConditionInspection */
	@mkdir($tmp);
	register_shutdown_function(
		function () use ($tmp) {
			exec("rm -rf ".escapeshellarg($tmp));
		}
	);
	return $tmp;
}

/**
 * Clean contents of temporary directory without removing directory itself
 *
 * @param string $tmp_dir
 */
function clean_tmp_dir ($tmp_dir) {
	if (strpos($tmp_dir, __DIR__.'/.tmp') === 0) {
		exec("rm -rf ".escapeshellarg($tmp_dir).'/*');
	}
}
