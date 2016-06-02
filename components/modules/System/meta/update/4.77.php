<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

$config = file(DIR.'/config/main.json');
foreach ($config as $i => $c) {
	if (strpos($c, '"db_charset"') !== false) {
		unset($config[$i]);
		break;
	}
}
file_put_contents(
	DIR.'/config/main.json',
	implode('', $config)
);
