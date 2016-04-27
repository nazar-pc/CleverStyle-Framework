<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\h;
use
	nazarpc\BananaHTML,
	cs\Config,
	cs\Language,
	cs\Page,
	cs\Request,
	cs\Session,
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
	 * @param string $url
	 *
	 * @return string
	 */
	protected static function url_with_hash ($url) {
		return Request::instance()->uri.$url;
	}
	/**
	 * Convert relative URL to absolute
	 *
	 * @static
	 *
	 * @param string $url
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
			class_exists('\\cs\\Session', false) &&
			$Session = Session::instance(true)
		) {
			return static::input(
				[
					'value' => $Session->get_id() ?: $Session->add(User::GUEST_ID),
					'type'  => 'hidden',
					'name'  => 'session'
				]
			);
		}
		return '';
	}
	/**
	 * CleverStyle CMS-specific processing of attributes
	 *
	 * @static
	 *
	 * @param string $tag
	 * @param array  $attributes
	 */
	protected static function pre_processing ($tag, &$attributes) {
		/**
		 * Do not apply to custom elements, they should support this by themselves
		 */
		if (
			isset($attributes['tooltip']) &&
			$attributes['tooltip'] !== false &&
			!isset($attributes['is']) &&
			strpos($tag, '-') === false
		) {
			$attributes['in'] = isset($attributes['in']) ? $attributes['in'].static::cs_tooltip() : static::cs_tooltip();
		}
	}
	/**
	 * Sometimes HTML code can be intended
	 *
	 * This function allows to store inner text of tags, that are sensitive to this operation (textarea, pre, code), and return some identifier.
	 * Later, at page generation, this identifier will be replaced by original text again.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	protected static function indentation_protection ($text) {
		$uniqid = uniqid('html_replace_', true);
		Page::instance()->replace($uniqid, $text);
		return $uniqid;
	}
	/**
	 * Pseudo tag for labels with tooltips, specified <i>input</i> is translation item of <b>$L</b> object,
	 * <i>input</i>_into item of <b>$L</b> is content of tooltip
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
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
		$L = Language::instance();
		return static::span(
			$L->$in,
			[
				'tooltip' => $L->{$in.'_info'}
			] + $data
		);
	}
	/**
	 * Pseudo tag for inserting of icons
	 *
	 * @static
	 *
	 * @param string $icon Icon name in Font Awesome
	 * @param array  $data
	 *
	 * @return mixed
	 */
	static function icon ($icon, $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($icon === false) {
			return '';
		}
		$data['icon'] = $icon;
		return static::cs_icon($data).' ';
	}
	/**
	 * Rendering of input[type=checkbox] with automatic adding labels and necessary classes
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return string
	 */
	static function checkbox ($in = [], $data = []) {
		if (self::common_checkbox_radio_pre($in, $data, __FUNCTION__, $return) !== false) {
			return $return;
		}
		if (@is_array($in['name']) || @is_array($in['id'])) {
			$items  = self::array_flip_3d($in);
			$return = '';
			foreach ($items as $item) {
				$return .= static::checkbox($item);
			}
			return $return;
		} else {
			self::common_checkbox_radio_post($in);
			return static::label(
				static::u_wrap($in, 'input'),
				[
					'is'    => 'cs-label-switcher',
					'class' => isset($item['class']) ? $item['class'] : false
				]
			);
		}
	}
	/**
	 * Rendering of input[type=radio] with automatic adding labels and necessary classes
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return string
	 */
	static function radio ($in = [], $data = []) {
		if (self::common_checkbox_radio_pre($in, $data, __FUNCTION__, $return) !== false) {
			return $return;
		}
		if (!isset($in['checked'])) {
			$in['checked'] = $in['value'][0];
		}
		$items   = self::array_flip_3d($in);
		$content = '';
		foreach ($items as $item) {
			self::common_checkbox_radio_post($item);
			$content .= static::label(
				static::u_wrap($item, 'input'),
				[
					'is'    => 'cs-label-button',
					'class' => isset($item['class']) ? $item['class'] : false
				]
			);
		}
		return $content;
	}
	/**
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 * @param string       $type
	 * @param string       $return
	 *
	 * @return bool|string
	 */
	protected static function common_checkbox_radio_pre (&$in, $data, $type, &$return) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic($type, func_get_args());
		}
		if ($in === false) {
			return '';
		}
		$in = static::input_merge($in, $data);
		/** @noinspection NotOptimalIfConditionsInspection */
		if (is_array_indexed($in) && is_array($in[0])) {
			return static::__callStatic($type, [$in, $data]);
		}
		$in['type'] = $type;
		return false;
	}
	/**
	 * @static
	 *
	 * @param array $item
	 */
	protected static function common_checkbox_radio_post (&$item) {
		if (isset($item['value'], $item['checked'])) {
			$item['checked'] = $item['value'] == $item['checked'];
		}
	}
}
