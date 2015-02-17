<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\general\about_server;
use
	cs\Language;

function state ($state) {
	return $state ? 'uk-alert-success' : 'uk-alert-danger';
}

/**
 * Returns server type
 *
 * @return string
 */
function server_api () {
	$phpinfo = ob_wrapper(function () {
		phpinfo();
	});
	if (preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE'])) {
		return 'Apache'.(preg_match('/mod_php/i', $phpinfo) ? ' + mod_php' : '');
	} elseif (preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'])) {
		$return = 'Nginx';
		if (preg_match('/php-fpm/i', $phpinfo)) {
			$return .= ' + PHP-FPM';
		} elseif (defined('HHVM_VERSION')) {
			$return .= ' + HHVM';
		}
		return $return;
	} elseif (isset($_SERVER['SERVER_SOFTWARE'])) {
		return $_SERVER['SERVER_SOFTWARE'];
	} else {
		return Language::instance()->indefinite;
	}
}

function apache_version () {
	preg_match(
		'/Apache[\-\/]([0-9\.\-]+)/',
		ob_wrapper(function () {
			phpinfo();
		}),
		$version
	);
	return $version[1];
}

/**
 * Returns autocompression level of zlib library
 *
 * @return bool
 */
function zlib_compression_level () {
	return ini_get('zlib.output_compression_level');
}

/**
 * Check of "display_errors" configuration of php.ini
 *
 * @return bool
 */
function display_errors () {
	return (bool)ini_get('display_errors');
}
