<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
$config = file_get_contents(DIR.'/config/main.json');
$config = str_replace("//Default encryption key\n", '', $config);
$config = preg_replace("/\s*\"key\"\s*:\s*\"[^\"]+\",\n/Uims", "\n", $config);
file_put_contents(DIR.'/config/main.json', $config);
