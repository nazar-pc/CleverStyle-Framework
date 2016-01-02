<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
require __DIR__.'/loader_base.php';      //Inclusion of loader base
require __DIR__.'/functions_global.php'; //Inclusion of functions that work with global state
/**
 * Wrapper around default `$_SERVER` superglobal
 */
$_SERVER = new _SERVER($_SERVER);
/**
 * Including of custom files
 */
foreach (glob(CUSTOM.'/*.php') ?: [] as $custom) {
	include $custom;
}
unset($custom);
try {
	try {
		/**
		 * System running
		 */
		try {
			Language::instance();
			Index::instance();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
		try {
			shutdown_function();
		} catch (ExitException $e) {
			if ($e->getCode()) {
				throw $e;
			}
		}
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson(), $e->getCode());
		}
	}
} catch (ExitException $e) {
}
