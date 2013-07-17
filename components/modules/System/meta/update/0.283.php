<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config;
/**
 * Removing deprecated configuration options
 */
unset($Config->core['cache_sync']);
unset($Config->core['show_objects_data']);
$Config->save();