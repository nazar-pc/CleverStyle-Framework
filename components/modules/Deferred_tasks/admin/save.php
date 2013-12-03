<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
if (isset($_POST['general'])) {
	Config::instance()->module('Deferred_tasks')->set($_POST['general']);
	Index::instance()->save(true);
}