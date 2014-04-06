<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\h;
use			nazarpc\BananaHTML,
			cs\Config,
			cs\Language,
			cs\Page,
			cs\User;
/**
 * Class for HTML code rendering in accordance with the standards of HTML5, and with useful syntax extensions for simpler usage
 *
 * If constant "XHTML_TAGS_STYLE" is true - tags will be generated according to rules of xhtml
 */
defined('XHTML_TAGS_STYLE') || define('XHTML_TAGS_STYLE', false);
abstract class Base extends BananaHTML {
	/**
	 * Special processing for URLs with hash
	 *
	 * @static
	 *
	 * @param string	$url
	 *
	 * @return string
	 */
	protected static function url_with_hash ($url) {
		return $_SERVER['REQUEST_URI'].$url;
	}
	/**
	 * Convert relative URL to absolute
	 *
	 * @static
	 *
	 * @param string	$url
	 *
	 * @return string
	 */
	protected static function absolute_url ($url) {
		if ($Config = Config::instance(true)) {
			return $Config->base_url()."/$url";
		}
		return "/$url";
	}
	/**
	 * Allows to add something to inner of form, for example, hidden session input to prevent CSRF
	 *
	 * @static
	 *
	 * @return string
	 */
	protected static function form_csrf () {
		if (
			class_exists('\\cs\\User', false) &&
			$User = User::instance(true)
		) {
			return static::input([
				'value'	=> $User->get_session(),
				'type'	=> 'hidden',
				'name'	=> 'session'
			]);
		}
		return '';
	}
	/**
	 * Sometimes HTML code can be intended
	 *
	 * This function allows to store inner text of tags, that are sensitive to this operation (textarea, pre, code), and return some identifier.
	 * Later, at page generation, this identifier will be replaced by original text again.
	 *
	 * @param string	$text
	 *
	 * @return string
	 */
	protected static function indentation_protection ($text) {
		$uniqid	= uniqid('html_replace_');
		Page::instance()->replace($uniqid, $text);
		return $uniqid;
	}
	/**
	 * Pseudo tag for labels with tooltips, specified <i>input</i> is translation item of <b>$L</b> object,
	 * <i>input</i>_into item of <b>$L</b> is content of tooltip
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return mixed
	 */
	static function info ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		$L		= Language::instance();
		if (Config::instance(true)->core['show_tooltips']) {
			return static::span($L->$in, array_merge(['data-title' => $L->{$in.'_info'}], $data));
		} else {
			return static::span($L->$in, $data);
		}
	}
	/**
	 * Pseudo tag for inserting of icons
	 *
	 * @static
	 *
	 * @param string	$class	Icon name in jQuery UI CSS Framework, fow example, <b>gear</b>, <b>note</b>
	 * @param array		$data
	 *
	 * @return mixed
	 */
	static function icon ($class, $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($class === false) {
			return '';
		}
		if (!isset($data['class'])) {
			$data['class'] = "uk-icon-$class";
		} else {
			$data['class'] .= " uk-icon-$class";
		}
		$data['level']	= 0;
		return static::span($data).' ';
	}
}
