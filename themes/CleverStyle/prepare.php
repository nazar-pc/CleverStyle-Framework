<?php
/**
 * @package    CleverStyle CMS
 * @subpackage CleverStyle theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
if (preg_match('/msie|trident/i', Request::instance()->header('user-agent'))) {
	Page::instance()->Head .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
}
