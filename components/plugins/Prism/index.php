<?php
/**
 * @package   Prism
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

Page::instance()->Head .= h::{'link[rel=stylesheet][shim-shadowdom]'}(['href' => 'components/plugins/Prism/includes/css/final.css']);
