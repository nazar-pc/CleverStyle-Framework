<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $User, $Index;
$Index->stop = !$User->is('admin');