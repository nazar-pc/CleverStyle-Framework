<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
$Index				= Index::instance();
$Index->title_auto	= false;
$L					= Language::instance();
$Page				= Page::instance();
$Page
	->title($L->administration)
	->title($L->Photo_gallery);
