<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
$config = file_get_contents(DIR.'/config/main.json');
$config = str_replace("//Cache size in MB for FileSystem storage engine\n", '', $config);
$config = preg_replace("/\s*\"cache_size\"\s*:\s*\"[^\"]+\",\n/Uims", "\n", $config);
file_put_contents(DIR.'/config/main.json', $config);
