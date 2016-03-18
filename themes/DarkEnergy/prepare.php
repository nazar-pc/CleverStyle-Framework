<?php
/**
 * @package    CleverStyle CMS
 * @subpackage DarkEnergy theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
$Page = Page::instance();
if (preg_match('/msie|trident/i', Request::instance()->header('user-agent'))) {
	Page::instance()->Head .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
}
$Page->Head .= '<meta name="viewport" content="width=device-width">';
