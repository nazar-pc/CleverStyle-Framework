<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
if (isset($_POST['save'])) {
	$module_data                    = Config::instance()->module('Uploader');
	$module_data->max_file_size     = xap($_POST['max_file_size']);
	$module_data->confirmation_time = (int)$_POST['confirmation_time'];
	Index::instance()->save(true);
}
