<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\h;
use
	nazarpc\BananaHTML,
	cs\Config,
	cs\Language,
	cs\Page,
	cs\User;
/**
 * Class for HTML code rendering in accordance with the standards of HTML5, and with useful syntax extensions for simpler usage
 */
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
		/**
		 * If Config not initialized yet - method will return `false`, which will be interpreted as empty string
		 */
		return Config::instance(true)->base_url()."/$url";
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
	 * CleverStyle CMS-specific processing of attributes
	 *
	 * @static
	 *
	 * @param array	$attributes
	 */
	protected static function pre_processing (&$attributes) {
		if (isset($attributes['data-title']) && $attributes['data-title'] !== false) {
			$attributes['title']	= static::prepare_attr_value($attributes['data-title']);
			unset($attributes['data-title']);
			$attributes['data-uk-tooltip']	= '{animation:true,delay:200}';
		}
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
		@$data['class']	.= " uk-icon-$class";
		$data['level']	= 0;
		return static::span($data).' ';
	}
	/**
	 * Rendering of input[type=checkbox] with automatic adding labels and necessary classes
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return string
	 */
	static function checkbox ($in = [], $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		}
		$in	= static::input_merge($in, $data);
		if (is_array_indexed($in) && is_array($in[0])) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		$in['type'] = 'checkbox';
		if (@is_array($in['name']) || @is_array($in['id'])) {
			$items	= array_flip_3d($in);
			$return	= '';
			foreach ($items as $item) {
				$return .= static::checkbox($item);
			}
			return $return;
		} else {
			if (!isset($in['id'])) {
				$in['id'] = uniqid('input_');
			}
			if (isset($in['value'], $in['checked']) && $in['value'] == $in['checked']) {
				$in[]	= 'checked';
			}
			unset($in['checked']);
			$in['tag'] = 'input';
			return static::span(
				static::label(
					static::u_wrap($in),
					[
						'for'			=> $in['id'],
						'data-title'	=> isset($in['data-title']) ? $in['data-title'] : false,
						'class'			=> 'uk-button'.(in_array('checked', $in) ? ' uk-active' : '')
					]
				),
				[
					'data-uk-button-checkbox'	=> ''
				]
			);
		}
	}
	/**
	 * Rendering of input[type=radio] with automatic adding labels and necessary classes
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return string
	 */
	static function radio ($in = [], $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		}
		$in	= static::input_merge($in, $data);
		$in['type'] = 'radio';
		if (is_array_indexed($in) && is_array($in[0])) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (!isset($in['checked'])) {
			$in['checked'] = $in['value'][0];
		}
		if (isset($in['add']) && !is_array($in['add'])) {
			$add = $in['add'];
			$in['add'] = [];
			foreach ($in['in'] as $v) {
				$in['add'][] = $add;
			}
			unset($add);
		}
		$checked		= $in['checked'];
		$in['checked']	= [];
		foreach ($in['value'] as $i => $v) {
			if ($v == $checked) {
				$in['checked'][$i] = '';
				break;
			}
		}
		unset($checked, $i, $v);
		$items = array_flip_3d($in);
		unset($in, $v, $i);
		$temp = '';
		foreach ($items as $item) {
			if (!isset($item['id'])) {
				$item['id'] = uniqid('input_');
			}
			$item['tag'] = 'input';
			if (isset($item['value'])) {
				$item['value'] = prepare_attr_value($item['value']);
			}
			$temp .= static::label(
				static::u_wrap($item),
				[
					'for'	=> $item['id'],
					'class'	=> 'uk-button'.(isset($item['checked']) ? ' uk-active' : '')
				]
			);
		}
		return static::span(
			$temp,
			[
				'class'					=> 'uk-button-group',
				'data-uk-button-radio'	=> ''
			]
		);
	}
}
