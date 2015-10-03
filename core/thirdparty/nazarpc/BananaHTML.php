<?php
/**
 * @package        BananaHTML
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace nazarpc;
/**
 * BananaHTML - single class that makes HTML generating easier
 *
 * This is class for HTML code rendering in accordance with the standards of HTML5, and with useful syntax extensions for simpler usage
 */
class BananaHTML {
	/**
	 * Attributes that doesn't have closing tag
	 *
	 * @var string[]
	 */
	protected static $unpaired_tags = [
		'area',
		'base',
		'br',
		'col',
		'frame',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'param'
	];
	/**
	 * Line padding for a structured source code (adds tabs)
	 *
	 * @static
	 *
	 * @param string $in
	 * @param int    $level
	 *
	 * @return string
	 */
	static function level ($in, $level = 1) {
		if ($level < 1) {
			return $in;
		}
		return preg_replace('/^(.*)$/m', str_repeat("\t", $level).'$1', $in);
	}
	/**
	 * Preparing data for processing in tags wrappers: tags, input string data detecting, unit attributes processing
	 *
	 * @static
	 *
	 * @param array  $data
	 * @param string $tag
	 * @param string $in
	 * @param string $attributes
	 *
	 * @return bool
	 */
	protected static function data_prepare ($data, $tag, &$in, &$attributes) {
		if (isset($data['in'])) {
			if ($data['in'] === false) {
				return false;
			}
			$in = trim($data['in']);
			unset($data['in']);
		}
		if ($tag == 'img' && !isset($data['alt'])) {
			$data['alt'] = '';
		}
		if (isset($data['src'])) {
			$data['src'] = static::prepare_url($data['src']);
		}
		if (isset($data['href'])) {
			if ($tag != 'a') {
				$data['href'] = static::prepare_url($data['href']);
			} elseif (substr($data['href'], 0, 1) == '#') {
				$data['href'] = static::url_with_hash($data['href']);
			}
		}
		uksort(
			$data,
			function ($key_1, $key_2) use ($data) {
				// Next 2 `if` statements for consistent behavior under PHP5 and PHP7/HHVM
				if (is_int($key_1)) {
					$key_1 = $data[$key_1];
				}
				if (is_int($key_2)) {
					$key_2 = $data[$key_2];
				}
				return strcmp($key_1, $key_2);
			}
		);
		foreach ($data as $key => $value) {
			if ($value === false) {
				continue;
			}
			if (is_int($key)) {
				$attributes .= " $value";
			} elseif ($value === true) {
				$attributes .= " $key";
			} else {
				$value = static::prepare_attr_value($value);
				$attributes .= " $key=\"$value\"";
			}
		}
		return true;
	}
	/**
	 * Adds, if necessary, slash or domain at the beginning of the url, provides correct absolute/relative url
	 *
	 * @static
	 *
	 * @param string $url
	 * @param bool   $absolute Returns absolute url or relative
	 *
	 * @return false|string
	 */
	static function prepare_url ($url, $absolute = false) {
		if ($url === false) {
			return false;
		}
		if (substr($url, 0, 1) == '#') {
			$url = static::url_with_hash($url);
		} elseif (
			substr($url, 0, 2) != '$i' &&
			substr($url, 0, 5) != 'data:' &&
			substr($url, 0, 1) != '/' &&
			substr($url, 0, 7) != 'http://' &&
			substr($url, 0, 8) != 'https://'
		) {
			if ($absolute) {
				return static::absolute_url($url);
			}
			return "/$url";
		}
		return $url;
	}
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
		return $url;
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
		return "/$url";
	}
	/**
	 * Prepare text to be used as value for html attribute value
	 *
	 * @param string|string[] $text
	 *
	 * @return string|string[]
	 */
	static function prepare_attr_value ($text) {
		if (is_array($text)) {
			foreach ($text as &$t) {
				$t = static::prepare_attr_value($t);
			}
			return $text;
		}
		return strtr(
			$text,
			[
				'&'  => '&amp;',
				'"'  => '&quot;',
				'\'' => '&apos;',
				'<'  => '&lt;',
				'>'  => '&gt;'
			]
		);
	}
	/**
	 * Empty stub, may be redefined if needed for custom attributes processing
	 *
	 * @static
	 *
	 * @param string $tag
	 * @param array  $attributes
	 */
	protected static function pre_processing ($tag, &$attributes) { }
	/**
	 * Wrapper for paired tags rendering
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 * @param string       $tag
	 *
	 * @return false|string
	 */
	protected static function wrap ($in, $data, $tag) {
		$data  = static::smart_array_merge(is_array($in) ? $in : ['in' => $in], is_array($data) ? $data : []);
		$in    = $attributes = '';
		$level = 1;
		if (isset($data['level'])) {
			$level = $data['level'];
			unset($data['level']);
		}
		static::pre_processing($tag, $data);
		if (!static::data_prepare($data, $tag, $in, $attributes)) {
			return false;
		}
		if (
			!$in &&
			$in !== 0 &&
			$in !== '0'
		) {
			$in = '';
		}
		if (
			$in &&
			$level &&
			(
				strpos($in, "\n") !== false ||
				strpos($in, '<') !== false
			)
		) {
			$in = $level ? "\n".static::level("$in\n", $level) : "\n$in\n";;
		}
		return "<$tag$attributes>$in</$tag>".($level ? "\n" : '');
	}
	/**
	 * Wrapper for unpaired tags rendering
	 *
	 * @static
	 *
	 * @param array  $data
	 * @param string $tag
	 *
	 * @return false|string
	 */
	protected static function u_wrap ($data, $tag) {
		$in = $attributes = '';
		static::pre_processing($tag, $data);
		if (!static::data_prepare($data, $tag, $in, $attributes)) {
			return false;
		}
		return "<$tag$attributes>".($in ? " $in" : '')."\n";
	}
	/**
	 * Rendering of form tag, default method is post, if form method is post - special session key in hidden input is added for security.
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function form ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (isset($in['method'])) {
			$data['method'] = $in['method'];
		}
		if (!isset($data['method'])) {
			$data['method'] = 'post';
		}
		if (strtolower($data['method']) == 'post') {
			if (!is_array($in)) {
				$in .= static::form_csrf();
			} else {
				$in['in'] .= static::form_csrf();
			}
		}
		return static::wrap($in, $data, __FUNCTION__);
	}
	/**
	 * Allows to add something to inner of form, for example, hidden session input to prevent CSRF
	 *
	 * @static
	 *
	 * @return string
	 */
	protected static function form_csrf () {
		return '';
	}
	/**
	 * Rendering of input tag with automatic adding labels for type=radio if necessary and automatic correction if min and/or max attributes are specified
	 * and value is out of this scope
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return string
	 */
	static function input ($in = [], $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		}
		$in = static::input_merge($in, $data);
		if (static::is_array_indexed($in) && is_array($in[0])) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (isset($in['type']) && $in['type'] == 'radio') {
			return static::input_radio($in);
		}
		return static::input_other($in);
	}
	/**
	 * @param array $in
	 * @param array $data
	 *
	 * @return array
	 */
	protected static function input_merge ($in, $data) {
		return !$data ? $in : array_merge(
			static::is_array_assoc($in) ? $in : ['in' => $in],
			$data
		);
	}
	/**
	 * @param array $in
	 *
	 * @return string
	 */
	protected static function input_radio ($in) {
		if (isset($in['value'])) {
			$in['value']   = (array)$in['value'];
			$checked       = isset($in['checked']) ? $in['checked'] : $in['value'][0];
			$in['checked'] = [];
			foreach ($in['value'] as $i => $v) {
				$in['checked'][$i] = $v == $checked;
			}
			unset($checked, $i, $v);
		}
		$items  = static::array_flip_3d($in);
		$result = '';
		foreach ($items as $i => $item) {
			$result .= static::u_wrap($item, 'input');
		}
		return $result;
	}
	/**
	 * @param array $in
	 *
	 * @return string
	 */
	protected static function input_other ($in) {
		if (
			(
				isset($in['name']) && is_array($in['name'])
			) ||
			(
				isset($in['id']) && is_array($in['id'])
			)
		) {
			$items  = static::array_flip_3d($in);
			$return = '';
			foreach ($items as $item) {
				$return .= static::input($item);
			}
			return $return;
		} else {
			/** @noinspection NotOptimalIfConditionsInspection */
			if (!isset($in['type'])) {
				$in['type'] = 'text';
			}
			if ($in['type'] == 'checkbox' && isset($in['value'], $in['checked']) && $in['value'] == $in['checked']) {
				$in[] = 'checked';
			}
			unset($in['checked']);
			if (isset($in['min'], $in['value']) && $in['min'] !== false && $in['min'] > $in['value']) {
				$in['value'] = $in['min'];
			}
			if (isset($in['max'], $in['value']) && $in['max'] !== false && $in['max'] < $in['value']) {
				$in['value'] = $in['max'];
			}
			return static::u_wrap($in, 'input');
		}
	}
	/**
	 * Template 1
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 * @param string       $function
	 *
	 * @return false|string
	 */
	protected static function select_common ($in, $data, $function) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if ($in === false) {
			return '';
		}
		if (
			!is_array($in) ||
			(
				isset($in['in']) && !is_array($in['in'])
			)
		) {
			return static::wrap($in, $data, $function);
		}
		$in = static::select_common_normalize($in);
		/**
		 * Moves arrays of attributes into option tags
		 */
		foreach ($data as $attr => &$value) {
			if (is_array($value)) {
				$in[$attr] = $value;
				unset($data[$attr]);
			}
		}
		unset($value);
		$disabled = array_merge(
			isset($in['disabled']) ? (array)$in['disabled'] : [],
			isset($data['disabled']) ? (array)$data['disabled'] : []
		);
		$selected = array_merge(
			isset($in['selected']) ? (array)$in['selected'] : [],
			isset($data['selected']) ? (array)$data['selected'] : []
		) ?: [$in['value'][0]];
		unset($data['selected'], $data['disabled']);
		$in['selected'] = $in['disabled'] = [];
		foreach ($in['value'] as $i => $v) {
			$in['disabled'][$i] = in_array($v, $disabled);
			$in['selected'][$i] = !$in['disabled'][$i] && in_array($v, $selected);
		}
		unset($i, $v);
		$options = static::array_flip_3d($in);
		foreach ($options as &$option) {
			if (isset($option[1])) {
				$option = array_merge(
					[
						'in' => $option[0]
					],
					$option[1]
				);
			}
			$option['in'] = str_replace('<', '&lt;', $option['in']);
			$option       = static::option($option);
		}
		unset($option);
		return static::wrap(implode('', $options), $data, $function);
	}
	/**
	 * Ensures that both `in` and `value` elements are present in array
	 *
	 * @param array $in
	 *
	 * @return array
	 */
	protected static function select_common_normalize ($in) {
		$has_in    = isset($in['in']);
		$has_value = isset($in['value']);
		if (
			!$has_value && $has_in && is_array($in['in'])
		) {
			$in['value'] = &$in['in'];
		} elseif (
			!$has_in && $has_value && is_array($in['value'])
		) {
			$in['in'] = &$in['value'];
		} elseif (
			(
				!$has_in || !is_array($in['in'])
			) &&
			(
				!$has_value || !is_array($in['value'])
			)
		) {
			$in = [
				'in'    => $in,
				'value' => $in
			];
		}
		return $in;
	}
	/**
	 * Rendering of select tag with autosubstitution of selected attribute when value of option is equal to $data['selected'], $data['selected'] may be
	 * array as well as string
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function select ($in = '', $data = []) {
		return static::select_common($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of optgroup tag with autosubstitution of selected attribute when value of option is equal to $data['selected'], $data['selected'] may be
	 * array as well as string
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function optgroup ($in = '', $data = []) {
		return static::select_common($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of datalist tag with autosubstitution of selected attribute when value of option is equal to $data['selected'], $data['selected'] may be
	 * array as well as string
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function datalist ($in = '', $data = []) {
		return static::select_common($in, $data, __FUNCTION__);
	}
	/**
	 * Template 2
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 * @param string       $function
	 *
	 * @return bool|string
	 */
	protected static function textarea_common ($in = '', $data = [], $function) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic($function, func_get_args());
		}
		if ($in === false) {
			return false;
		}
		if (is_array($in)) {
			if (isset($in['in'])) {
				$in['in'] = static::indentation_protection(is_array($in['in']) ? implode("\n", $in['in']) : $in['in']);
			} else {
				$in = static::indentation_protection(implode("\n", $in));
			}
		} else {
			$in = static::indentation_protection(is_array($in) ? implode("\n", $in) : $in);
		}
		$data['level'] = false;
		return static::wrap($in, $data, $function);
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
		return $text;
	}
	/**
	 * Rendering of textarea tag with supporting multiple input data in the form of array of strings
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function textarea ($in = '', $data = []) {
		return static::textarea_common($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of pre tag with supporting multiple input data in the form of array of strings
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function pre ($in = '', $data = []) {
		return static::textarea_common($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of code tag with supporting multiple input data in the form of array of strings
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function code ($in = '', $data = []) {
		return static::textarea_common($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of button tag, if button type is not specified - it will be button type
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function button ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (is_array($in)) {
			if (!isset($in['type'])) {
				$in['type'] = 'button';
			}
		} elseif (is_array($data)) {
			if (!isset($data['type'])) {
				$data['type'] = 'button';
			}
		}
		return static::wrap($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of style tag, if style type is not specified - it will be text/css type, that is used almost always
	 *
	 * @static
	 *
	 * @param array|string $in
	 * @param array        $data
	 *
	 * @return bool|string
	 */
	static function style ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (is_array($in)) {
			if (!isset($in['type'])) {
				$in['type'] = 'text/css';
			}
		} elseif (is_array($data)) {
			if (!isset($data['type'])) {
				$data['type'] = 'text/css';
			}
		}
		return static::wrap($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of br tag, very simple, only one parameter exists - number of br tags to be rendered, default is 1
	 *
	 * @static
	 *
	 * @param int $repeat
	 *
	 * @return bool|string
	 */
	static function br ($repeat = 1) {
		if ($repeat === false) {
			return false;
		}
		return str_repeat("<br>\n", $repeat);
	}
	/**
	 * Merging of arrays, but joining all 'class' and 'style' items, supports only 2 arrays as input
	 *
	 * @static
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	protected static function smart_array_merge ($array1, $array2) {
		if (isset($array1['class'], $array2['class'])) {
			$array1['class'] .= " $array2[class]";
			unset($array2['class']);
		}
		if (isset($array1['style'], $array2['style'])) {
			$array1['style'] = trim($array1['style'], ';').";$array2[style]";
			unset($array2['style']);
		}
		return array_merge($array1, $array2);
	}
	/**
	 * Processing of complicated rendering structures
	 *
	 * @static
	 *
	 * @param string            $input
	 * @param array|bool|string $data
	 *
	 * @return string
	 */
	static function __callStatic ($input, $data) {
		if ($data === false || $data === [false]) {
			return false;
		}
		$data = (array)$data;
		if (isset($data[1]) && $data[1] === false && !isset($data[2])) {
			unset($data[1]);
		}
		$input = static::parse_nesting_selector(trim($input));
		/**
		 * Analysis of called tag. If nested tags presented
		 */
		if (is_array($input)) {
			return static::handle_nested_selectors($input, $data);
		}
		if (substr($input, -1) == '|') {
			$input = substr($input, 0, -1);
			$data  = [$data];
		}
		/**
		 * Fix for `<textarea>`, `<select>`, `<optgroup>` and `<datalist>` tags because they accept indexed arrays as content by themselves
		 * `\b(..)` is used to capture words only (string might be quite complex, this will help to avoid some false-positive results, other should be avoided intentionally)
		 */
		$element_that_supports_indexed_array_content = preg_match('/\b(textarea|select|optgroup|datalist)/', $input);
		/**
		 * If associative array given then for every element of array separate copy of current tag will be created
		 */
		if (static::is_array_indexed($data)) {
			/**
			 * If there are more than elements - we clearly have to render it like set of separate elements
			 */
			if (count($data) > 2) {
				return static::render_array_of_elements($input, $data);
			}
			/**
			 * If there are 2 elements, where first element is not an array, but second is not an array too or is indexed array - also render it like set of separate elements
			 */
			if (
				isset($data[1]) &&
				!static::is_array_assoc($data[0]) &&
				(
					!is_array($data[1]) ||
					static::is_array_indexed($data[1])
				)
			) {
				return static::render_array_of_elements($input, $data);
			}
			if (
				!$element_that_supports_indexed_array_content &&
				static::is_array_indexed($data[0])
			) {
				$output  = '';
				$data[1] = isset($data[1]) ? $data[1] : [];
				foreach ((array)$data[0] as $d) {
					if (
						!is_array($d) ||
						!isset($d[1]) ||
						!static::is_array_assoc($d[1])
					) {
						$output .= static::__callStatic(
							$input,
							[
								$d,
								$data[1]
							]
						);
					} else {
						$output .= static::__callStatic(
							$input,
							[
								$d[0],
								static::smart_array_merge($data[1], $d[1])
							]
						);
					}
				}
				return $output;
			}
			/**
			 * If we have one element (indexed array) or second element is not index array - also render it like set of separate elements
			 */
			if (
				!$element_that_supports_indexed_array_content &&
				static::is_array_indexed($data[0]) &&
				(
					!isset($data[1]) ||
					!static::is_array_indexed($data[1])
				)
			) {
				return static::render_array_of_elements($input, $data);
			}
		}
		if (isset($data[0])) {
			if (
				!is_array($data[0]) ||
				(
					$element_that_supports_indexed_array_content &&
					!isset($data[0]['in'])
				)
			) {
				$data[0] = ['in' => $data[0]];
			}
			$data = isset($data[1]) ? static::smart_array_merge($data[0], $data[1]) : $data[0];
		}
		list($tag, $attributes) = static::parse_tag_string($input);
		$attributes = static::smart_array_merge($attributes, $data);
		list($in, $attributes) = static::prepare_content($tag, $attributes);
		if (isset($attributes['insert'])) {
			return static::process_inserts($tag, $in, $attributes);
		}
		return static::render_tag($tag, $in, $attributes);
	}
	/**
	 * Analyze CSS selector for nester tags
	 *
	 * @param array|string $in
	 * @param int          $offset
	 *
	 * @return string Returns array of strings if nesting selector detected, unchanged input string otherwise
	 */
	protected static function parse_nesting_selector ($in, $offset = 0) {
		$space_position = strpos($in, ' ', $offset);
		if ($space_position === false) {
			return $in;
		}
		$next_space = strpos($in, ' ', $space_position + 1);
		$attr_close = strpos($in, ']', $space_position);
		if (
			$next_space === false ||
			$attr_close === false ||
			$next_space > $attr_close
		) {
			return [
				substr($in, 0, $space_position),
				substr($in, $space_position + 1)
			];
		}
		return static::parse_nesting_selector($in, $space_position + 1);
	}
	/**
	 * @param string[] $input
	 * @param array    $data
	 *
	 * @return string
	 */
	protected static function handle_nested_selectors ($input, $data) {
		/**
		 * If tag name ends with pipe "|" symbol - for every element of array separate copy of current tag will be created
		 */
		if (strpos($input[0], '|') !== false) {
			$input[0] = substr($input[0], 0, -1);
			$output   = [];
			/**
			 * When parameters are not taken in braces - make this operation, if it is necessary
			 */
			if (
				count($data) > 2 ||
				(
					isset($data[1]) &&
					static::is_array_indexed($data[1])
				)
			) {
				$data = [$data];
			}
			foreach ($data[0] as $d) {
				if (isset($d[0]) && static::is_array_indexed($d[0])) {
					if (
						isset($d[1]) &&
						(
							!is_array($d[1]) ||
							static::is_array_indexed($d[1])
						)
					) {
						$output[] = static::render_array_of_elements($input[1], $d);
					} else {
						$output[] = [
							static::__callStatic($input[1], $d[0]),
							isset($d[1]) ? $d[1] : false
						];
					}
				} else {
					$output[] = static::__callStatic($input[1], $d);
				}
			}
			unset($d);
		} elseif (!isset($data[1]) || static::is_array_assoc($data[1])) {
			$output  = static::__callStatic(
				$input[1],
				[
					isset($data[0]) ? $data[0] : '',
					isset($data[1]) ? $data[1] : false
				]
			);
			$data[1] = [];
		} else {
			$output  = static::__callStatic(
				$input[1],
				$data
			);
			$data[1] = [];
		}
		return static::__callStatic(
			$input[0],
			[
				$output,
				isset($data[1]) ? $data[1] : false
			]
		);
	}
	/**
	 * @param string $input
	 * @param array  $data
	 *
	 * @return string
	 */
	protected static function render_array_of_elements ($input, $data) {
		$output = '';
		foreach ($data as $d) {
			$output .= static::__callStatic($input, $d);
		}
		return $output;
	}
	/**
	 * Tag string might be complex and include id, class (classes) and attributes
	 *
	 * This method takes such string as input and returns pure tag name and array of attributes
	 *
	 * @param string $input
	 *
	 * @return array [$tag, $attributes]
	 */
	protected static function parse_tag_string ($input) {
		$attributes = [];
		/**
		 * Attributes processing
		 */
		$pos = mb_strpos($input, '[');
		if ($pos !== false) {
			$regular_attributes = explode('][', mb_substr($input, $pos + 1, -1));
			$input              = mb_substr($input, 0, $pos);
			foreach ($regular_attributes as &$attr) {
				/**
				 * For attribute without value we just put `true`, as this will be treated as boolean attribute
				 */
				$attr                 = explode('=', $attr, 2);
				$attributes[$attr[0]] = isset($attr[1]) ? $attr[1] : true;
			}
			unset($regular_attributes, $attr);
		}
		/**
		 * Classes processing
		 */
		$pos = mb_strpos($input, '.');
		if ($pos !== false) {
			$attributes['class'] = trim(str_replace('.', ' ', mb_substr($input, $pos)));
			$input               = mb_substr($input, 0, $pos);
		}
		unset($pos);
		/**
		 * Id and tag determination
		 */
		$input = explode('#', $input);
		$tag   = $input[0];
		/**
		 * Convenient support of custom tags for Web Components
		 *
		 * Allows to write BananaHTML::custom_tag() that will be translated to <custom-tag></custom-tag>
		 */
		$tag = str_replace('_', '-', $tag);
		if (isset($input[1])) {
			$attributes['id'] = $input[1];
		}
		return [$tag, $attributes];
	}
	/**
	 * @param string $tag
	 * @param array  $attributes
	 *
	 * @return array
	 */
	protected static function prepare_content ($tag, $attributes) {
		$in = '';
		if (
			$tag == 'select' ||
			$tag == 'optgroup' ||
			$tag == 'datalist'
		) {
			$in = [
				'in' => $attributes['in']
			];
			if (isset($attributes['value'])) {
				$in['value'] = $attributes['value'];
			}
			unset($attributes['in'], $attributes['value']);
		} elseif (isset($attributes['in'])) {
			$in = $attributes['in'];
			unset($attributes['in']);
		}
		return [$in, $attributes];
	}
	/**
	 * @param string       $tag
	 * @param array|string $in
	 * @param array        $attributes
	 *
	 * @return string
	 */
	protected static function process_inserts ($tag, $in, $attributes) {
		$insert = $attributes['insert'];
		unset($attributes['insert']);
		$html = '';
		foreach (static::inserts_processing([$in, $attributes], $insert) as $d) {
			$html .= static::render_tag($tag, $d[0], $d[1]);
		}
		return $html;
	}
	/**
	 * @param array|array[]    $data
	 * @param array[]|string[] $insert
	 *
	 * @return string[]
	 */
	protected static function inserts_processing ($data, $insert) {
		if (static::is_array_indexed($insert) && is_array($insert[0])) {
			$new_data = [];
			foreach ($insert as $i) {
				$new_data[] = static::inserts_replacing_recursive($data, $i);
			}
			return $new_data;
		}
		return static::inserts_replacing_recursive($data, $insert);
	}
	/**
	 * @param string|string[] $data
	 * @param string[]        $insert
	 *
	 * @return string|string[]
	 */
	protected static function inserts_replacing_recursive ($data, $insert) {
		if (is_array($data)) {
			foreach ($data as &$d) {
				$d = static::inserts_replacing_recursive($d, $insert);
			}
			return $data;
		}
		foreach ($insert as $i => $d) {
			$data = str_replace("\$i[$i]", $d, $data);
		}
		return $data;
	}
	/**
	 * @param string       $tag
	 * @param array|string $in
	 * @param array        $attributes
	 *
	 * @return false|string
	 */
	protected static function render_tag ($tag, $in, $attributes) {
		if (method_exists(get_called_class(), $tag)) {
			return static::$tag($in, $attributes);
		} elseif (in_array($tag, static::$unpaired_tags)) {
			$attributes['in'] = $in;
			return static::u_wrap($attributes, $tag);
		}
		return static::wrap($in, $attributes, $tag ?: 'div');
	}
	/**
	 * Checks associativity of array
	 *
	 * @param array $array Array to be checked
	 *
	 * @return bool
	 */
	protected static function is_array_assoc ($array) {
		if (empty($array) || !is_array($array)) {
			return false;
		}
		$count = count($array);
		for ($i = 0; $i < $count; ++$i) {
			if (!isset($array[$i])) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Checks whether array is indexed or not
	 *
	 * @param array $array Array to be checked
	 *
	 * @return bool
	 */
	protected static function is_array_indexed ($array) {
		if (empty($array) || !is_array($array)) {
			return false;
		}
		return !static::is_array_assoc($array);
	}
	/**
	 * Works like <b>array_flip()</b> function, but is used when every item of array is not a string, but may be also array
	 *
	 * @param array $array At least one item must be array, some other items may be strings (or numbers)
	 *
	 * @return array|bool
	 */
	protected static function array_flip_3d ($array) {
		if (!is_array($array)) {
			return false;
		}
		$result = [];
		$size   = 0;
		foreach ($array as $values) {
			$size = max($size, count((array)$values));
		}
		unset($values);
		foreach ($array as $key => $values) {
			for ($i = 0; $i < $size; ++$i) {
				if (is_array($values)) {
					if (isset($values[$i])) {
						$result[$i][$key] = $values[$i];
					}
				} else {
					$result[$i][$key] = $values;
				}
			}
		}
		return $result;
	}
}
