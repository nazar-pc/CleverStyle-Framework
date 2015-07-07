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
$config = str_replace("//Will be truncated if necessary", '//Default encryption key', $config);
file_put_contents(DIR.'/config/main.json', $config);
