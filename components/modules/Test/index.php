<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use h;
Page::instance()->content(
	h::{'div.cs-dialog'}('Modal')
);