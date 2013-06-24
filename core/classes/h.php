<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Class for HTML code rendering in accordance with the standards of HTML5, and with useful syntax extensions for simpler usage
 *
 * If constant "XHTML_TAGS_STYLE" is true - tags will be generated according to rules of xhtml
 */
defined('XHTML_TAGS_STYLE') || define('XHTML_TAGS_STYLE', false);
class h extends h_internal {}