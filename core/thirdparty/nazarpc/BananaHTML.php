<?php
/**
 * @package		BananaHTML
 * @version		2.1.6
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	nazarpc;
/**
 * If constant "XHTML_TAGS_STYLE" is true - tags will be generated according to rules of xhtml
 */
defined('XHTML_TAGS_STYLE') || define('XHTML_TAGS_STYLE', false);
/**
 * BananaHTML - single class that makes HTML generating easier
 *
 * This is class for HTML code rendering in accordance with the standards of HTML5, and with useful syntax extensions for simpler usage
 */
class BananaHTML {
	protected static	$known_unit_atributes = [	//Unit attributes, that have no value, or have the same value as name in xhtml style
			'async',
			'defer',
			'formnovalidate',
			'autofocus',
			'checked',
			'selected',
			'readonly',
			'required',
			'disabled',
			'multiple',
			'pubdate',
			'noshade',
			'autoplay',
			'controls',
			'loop',
			'itemscope',
			'no-label'
		],
		$unpaired_tags = [
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
	 * @param string	$in
	 * @param int		$level
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
	 * @param array		$data
	 * @param string	$in
	 * @param string	$tag
	 * @param string	$add
	 *
	 * @return bool
	 */
	protected static function data_prepare (&$data, &$in, &$tag, &$add) {
		$q = '"';
		if (isset($data['in'])) {
			if ($data['in'] === false) {
				return false;
			}
			$in = trim($data['in']);
			unset($data['in']);
		}
		if (array_search(false, $data, true) !== false) {
			foreach ($data as $i => $item) {
				if ($item === false) {
					unset($data[$i]);
				}
			}
			unset($i, $item);
		}
		if (isset($data['tag'])) {
			if ($data['tag']) {
				$tag				= $data['tag'];
			}
			if ($tag == 'img' && !isset($data['alt'])) {
				$data['alt']	= '';
			}
			unset($data['tag']);
		}
		if (isset($data['src'])) {
			$data['src']		= str_replace(' ', '%20', $data['src']);
			$data['src']		= static::prepare_url($data['src']);
		}
		if (isset($data['href'])) {
			$data['href']		= str_replace(
				[' ', '"'],
				['%20', '&quot;'],
				$data['href']
			);
			if ($tag != 'a') {
				$data['href']	= static::prepare_url($data['href']);
			} elseif (substr($data['href'], 0, 1) == '#') {
				$data['href']	= static::url_with_hash($data['href']);
			}
		}
		if (isset($data['action'])) {
			$data['action']		= str_replace(' ', '%20', $data['action']);
		}
		if (isset($data['formaction'])) {
			$data['formaction']	= str_replace(' ', '%20', $data['formaction']);
		}
		if (isset($data['add'])) {
			$add				= ' '.trim($data['add']);
			unset($data['add']);
		}
		/**
		 * If quotes symbol specified - use it
		 */
		if (isset($data['quote'])) {
			$q					= $data['quote'];
			unset($data['quote']);
		}
		if (isset($data['class']) && empty($data['class'])) {
			unset($data['class']);
		}
		if (isset($data['style']) && empty($data['style'])) {
			unset($data['style']);
		}
		if (isset($data['value'])) {
			$data['value']		= static::prepare_attr_value($data['value']);
		}
		ksort($data);
		foreach ($data as $key => $value) {
			if (is_int($key)) {
				unset($data[$key]);
				$add	.= " $value".(XHTML_TAGS_STYLE ? "=$q$value$q" : '');
			} elseif ($value !== false) {
				$add			.= " $key=$q$value$q";
			}
		}
		return true;
	}
	/**
	 * Adds, if necessary, slash or domain at the beginning of the url, provides correct absolute/relative url
	 *
	 * @static
	 *
	 * @param string	$url
	 * @param bool		$absolute	Returns absolute url or relative
	 *
	 * @return string
	 */
	static function prepare_url ($url, $absolute = false) {
		if (substr($url, 0, 1) == '#') {
			$url	= static::url_with_hash($url);
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
	 * @param string	$url
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
	 * @param string	$url
	 *
	 * @return string
	 */
	protected static function absolute_url ($url) {
		return "/$url";
	}
	/**
	 * Empty stub, may be redefined if needed for custom attributes processing
	 *
	 * @static
	 *
	 * @param array	$attributes
	 */
	protected static function pre_processing (&$attributes) {}
	/**
	 * Wrapper for paired tags rendering
	 *
	 * @static
	 *
	 * @param string		$in
	 * @param array			$data
	 * @param string		$tag
	 *
	 * @return bool|string
	 */
	protected static function wrap ($in = '', $data = [], $tag = 'div') {
		$data		= static::array_merge(is_array($in) ? $in : ['in' => $in], is_array($data) ? $data : [], ['tag' => $tag]);
		$in			= $add = '';
		$tag		= 'div';
		$level		= 1;
		if (isset($data['level'])) {
			$level	= $data['level'];
			unset($data['level']);
		}
		static::pre_processing($data);
		if (!static::data_prepare($data, $in, $tag, $add)) {
			return false;
		}
		if (!(
			$in ||
			$in === 0 ||
			$in === '0'
		)) {
			$in		= $in === false || !$level ? '' : '&nbsp;';
		}
		if (
			$in &&
			(
				strpos($in, "\n") !== false ||
				strpos($in, "<") !== false
			) &&
			$level
		) {
			$in		= $level ? "\n".static::level("$in\n", $level) : "\n$in\n";
;		}
		return "<$tag$add>$in</$tag>".($level ? "\n" : '');
	}
	/**
	 * Wrapper for unpaired tags rendering
	 *
	 * @static
	 *
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	protected static function u_wrap ($data = []) {
		$in		= $add		= '';
		$tag	= 'input';
		static::pre_processing($data);
		if (!static::data_prepare($data, $in, $tag, $add)) {
			return false;
		}
		$add	.= XHTML_TAGS_STYLE ? ' /' : '';
		return "<$tag$add>".($in ? " $in" : '')."\n";
	}
	/**
	 * Rendering of form tag, default method is post, if form method is post - special session key in hidden input is added for security.
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
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
			$data['method']	= $in['method'];
		}
		if (!isset($data['method'])) {
			$data['method']	= 'post';
		}
		if (strtolower($data['method']) == 'post') {
			if (!is_array($in)) {
				$in			.= static::form_csrf();
			} else {
				$in['in']	.= static::form_csrf();
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
	protected static function input_merge ($in, $data) {
		if (!empty($data)) {
			$in = array_merge(
				static::is_array_assoc($in) ? $in : ['in' => $in],
				$data
			);
		}
		return $in;
	}
	/**
	 * Rendering of input tag with automatic adding labels for type=radio if necessary and automatic correction if min and/or max attributes are specified
	 * and value is out of this scope
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
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
		$in	= static::input_merge($in, $data);
		if (static::is_array_indexed($in) && is_array($in[0])) {
			return static::__callStatic(__FUNCTION__, [$in, $data]);
		}
		if (isset($in['type']) && $in['type'] == 'radio') {
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
			foreach ($in['value'] as $i => $v) {
				if ($v == $in['checked']) {
					@$in['add'][$i] .= ' checked';
					break;
				}
			}
			unset($in['checked'], $i, $v);
			$items = static::array_flip_3d($in);
			unset($in, $v, $i);
			$temp = '';
			foreach ($items as $item) {
				$item['tag'] = __FUNCTION__;
				if (isset($item['value'])) {
					$item['value'] = static::prepare_attr_value($item['value']);
				}
				$temp .= static::u_wrap($item);
			}
			return $temp;
		} else {
			if (
				(
					isset($in['name'])	&& is_array($in['name'])
				) ||
				(
					isset($in['id'])	&& is_array($in['id'])
				)
			) {
				$items	= static::array_flip_3d($in);
				$return	= '';
				foreach ($items as $item) {
					$return .= static::input($item);
				}
				return $return;
			} else {
				if (!isset($in['type'])) {
					$in['type'] = 'text';
				}
				if ($in['type'] == 'checkbox' && isset($in['value'], $in['checked']) && $in['value'] == $in['checked']) {
					$in[]	= 'checked';
				}
				unset($in['checked']);
				if (isset($in['min'], $in['value']) && $in['min'] !== false && $in['min'] > $in['value']) {
					$in['value'] = $in['min'];
				}
				if (isset($in['max'], $in['value']) && $in['max'] !== false && $in['max'] < $in['value']) {
					$in['value'] = $in['max'];
				}
				$in['tag'] = __FUNCTION__;
				return static::u_wrap($in);
			}
		}
	}
	/**
	 * Template 1
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 * @param string			$function
	 *
	 * @return bool|string
	 */
	protected static function template_1 ($in = '', $data = [], $function) {
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
		if (
			!isset($in['value']) && isset($in['in']) && is_array($in['in'])
		) {
			$in['value']	= &$in['in'];
		} elseif (
			!isset($in['in']) && isset($in['value']) && is_array($in['value'])
		) {
			$in['in']		= &$in['value'];
		} elseif (
			(
				!isset($in['in']) || !is_array($in['in'])
			) &&
			(
				!isset($in['value']) || !is_array($in['value'])
			) &&
			is_array($in)
		) {
			$temp			= $in;
			$in				= [];
			$in['value']	= &$temp;
			$in['in']		= &$temp;
			unset($temp);
		}
		if (!isset($in['value']) && !isset($in['in'])) {
			return false;
		}
		/**
		 * Moves arrays of attributes into option tags
		 */
		foreach ($data as $attr => &$value) {
			if (is_array($value)) {
				$in[$attr] = $value;
				unset($data[$attr]);
			}
		}
		if (is_array($in['value'])) {
			if (isset($in['disabled'])) {
				$data['disabled']	= array_merge((array)$in['disabled'], isset($data['disabled']) ? $data['disabled'] : []);
				unset($in['disabled']);
			}
			if (isset($in['selected'])) {
				$data['selected']	= array_merge((array)$in['selected'], isset($data['selected']) ? $data['selected'] : []);
				unset($in['selected']);
			}
			if (!isset($data['selected'])) {
				$data['selected']	= $in['value'][0];
			}
			$data['selected']	= (array)$data['selected'];
			if (isset($data['disabled'])) {
				$data['disabled']	= (array)$data['disabled'];
				$data['selected']	= array_diff($data['selected'], $data['disabled']);
			} else {
				$data['disabled']	= [];
			}
			foreach ($in['value'] as $i => $v) {
				if (in_array($v, $data['selected'])) {
					if (!isset($in['add'][$i])) {
						$in['add'][$i]	= ' selected';
					} else {
						$in['add'][$i]	.= ' selected';
					}
				}
				if (in_array($v, $data['disabled'])) {
					if (!isset($in['add'][$i])) {
						$in['add'][$i]	= ' disabled';
					} else {
						$in['add'][$i]	.= ' disabled';
					}
				}
			}
			unset($data['disabled'], $data['selected'], $i, $v);
		}
		$options = static::array_flip_3d($in);
		unset($in);
		foreach ($options as &$option) {
			if (isset($option[1])) {
				$option	= array_merge(
					[
						'in'	=> $option[0]
					],
					$option[1]
				);
			}
			$option['in']	= str_replace('<', '&lt;', $option['in']);
			$option			= static::option($option);
		}
		unset($option);
		return static::wrap(implode('', $options), $data, $function);
	}
	/**
	 * Rendering of select tag with autosubstitution of selected attribute when value of option is equal to $data['selected'], $data['selected'] may be
	 * array as well as string
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	static function select ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		return static::template_1($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of datalist tag with autosubstitution of selected attribute when value of option is equal to $data['selected'], $data['selected'] may be
	 * array as well as string
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	static function datalist ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		return static::template_1($in, $data, __FUNCTION__);
	}
	/**
	 * Template 2
	 * @static
	 * @param array|string $in
	 * @param array        $data
	 * @param string       $function
	 * @return bool|string
	 */
	protected static function template_2 ($in = '', $data = [], $function) {
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
	 * @param string	$text
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
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	static function textarea ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		return static::template_2($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of pre tag with supporting multiple input data in the form of array of strings
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	static function pre ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		return static::template_2($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of code tag with supporting multiple input data in the form of array of strings
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
	 *
	 * @return bool|string
	 */
	static function code ($in = '', $data = []) {
		if (isset($in['insert']) || isset($data['insert'])) {
			return static::__callStatic(__FUNCTION__, func_get_args());
		}
		return static::template_2($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of button tag, if button type is not specified - it will be button type
	 *
	 * @static
	 *
	 * @param array|string	$in
	 * @param array			$data
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
	 * @param array|string	$in
	 * @param array			$data
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
		$in['tag'] = __FUNCTION__;
		return str_repeat(static::u_wrap($in), $repeat);
	}
	/**
	 * Merging of arrays, but joining all 'class' and 'style' items, supports 2-3 arrays for input
	 *
	 * @static
	 *
	 * @param array		$array1
	 * @param array		$array2
	 * @param array		$array3
	 *
	 * @return array
	 */
	protected static function array_merge ($array1, $array2, $array3 = []) {
		if (isset($array1['class'], $array2['class'])) {
			$array1['class'] .= " $array2[class]";
			unset($array2['class']);
		}
		if (isset($array1['class'], $array3['class'])) {
			$array1['class'] .= " $array3[class]";
			unset($array3['class']);
		}
		if (isset($array1['style'], $array2['style'])) {
			$array1['style'] .= $array2['style'];
			unset($array2['style']);
		}
		if (isset($array1['style'], $array3['style'])) {
			$array1['style'] .= $array3['style'];
			unset($array3['class']);
		}
		return $array3 + $array2 + $array1;
	}
	/**
	 * Analyze CSS selector for nester tags
	 *
	 * @param string	$in
	 * @param int		$offset
	 *
	 * @return bool			Returns <i>true</i> and changes <i>&$in</i> to array if nested tags detected
	 */
	protected static function analyze_selector (&$in, $offset = 0) {
		$space_position	= strpos($in, ' ', $offset);
		if ($space_position === false) {
			return false;
		}
		$next_space		= strpos($in, ' ', $space_position + 1);
		$attr_close		= strpos($in, ']', $space_position);
		if (
			$next_space === false ||
			$attr_close === false ||
			$next_space > $attr_close
		) {
			$in	= [
				substr($in, 0, $space_position),
				substr($in, $space_position + 1)
			];
			return true;
		}
		return static::analyze_selector($in, $space_position + 1);
	}
	/**
	 * @param array[]|string|string[]	$data
	 * @param string[]					$insert
	 */
	protected static function inserts_replacing_recursive (&$data, &$insert) {
		if (is_array($data)) {
			foreach ($data as &$d) {
				static::inserts_replacing_recursive($d, $insert);
			}
		} else {
			foreach ($insert as $i => $d) {
				$data	= str_replace("\$i[$i]", $d, $data);
			}
		}
	}
	/**
	 * @param array|array[]		$data
	 * @param array[]|string[]	$insert
	 */
	protected static function inserts_processing (&$data, &$insert) {
		if (!$insert) {
			$data	= '';
			return;
		}
		if (static::is_array_indexed($insert) && is_array($insert[0])) {
			$new_data	= [];
			foreach ($insert as $i) {
				$new_data[] = $data;
				static::inserts_replacing_recursive($new_data[count($new_data) - 1], $i);
			}
			$data		= $new_data;
		} else {
			 static::inserts_replacing_recursive($data, $insert);
		}
	}
	/**
	 * Processing of complicated rendering structures
	 *
	 * @static
	 *
	 * @param string			$input
	 * @param array|bool|string	$data
	 *
	 * @return string
	 */
	static function __callStatic ($input, $data) {
		if ($data === false || $data === [false]) {
			return false;
		}
		if (is_scalar($data)) {
			$data		= [$data];
		} elseif (isset($data[1]) && $data[1] === false && !isset($data[2])) {
			unset($data[1]);
		}
		$input	= trim($input);
		/**
		 * Analysis of called tag. If nested tags presented
		 */
		if (static::analyze_selector($input)) {
			/**
			 * If tag name ends with pipe "|" symbol - for every element of array separate copy of current tag will be created
			 */
			if (strpos($input[0], '|') !== false) {
				$input[0]	= substr($input[0], 0, -1);
				$output		= [];
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
					$data	= [$data];
				}
				foreach ($data[0] as $d) {
					if (isset($d[0]) && static::is_array_indexed($d[0]) && !in_array($d[0][0], static::$known_unit_atributes)) {
						if (
							isset($d[1]) &&
							(
								!is_array($d[1]) ||
								(
									static::is_array_indexed($d[1]) && !in_array($d[1][0], static::$known_unit_atributes)
								)
							)
						) {
							$output_	= [];
							foreach ($d as $d_) {
								$output_[]	= static::__callStatic($input[1], $d_);
							}
							$output[]	= $output_;
							unset($output_);
						} else {
							$output[]	= [
								static::__callStatic($input[1], $d[0]),
								isset($d[1]) ? $d[1] : false
							];
						}
					} else {
						$output[]	= static::__callStatic($input[1], $d);
					}
				}
				unset($d);
			} elseif (!isset($data[1]) || static::is_array_assoc($data[1])) {
				$output		= static::__callStatic(
					$input[1],
					[
						isset($data[0]) ? $data[0] : '',
						isset($data[1]) ? $data[1] : false
					]
				);
				$data[1]	= [];
			} else {
				$output		= static::__callStatic(
					$input[1],
					$data
				);
				$data[1]	= [];
			}
			return static::__callStatic(
				$input[0],
				[
					$output,
					isset($data[1]) ? $data[1] : false
				]
			);
		}
		if (substr($input, -1) == '|') {
			$input	= substr($input, 0, -1);
			$data	= [$data];
		}
		/**
		 * Fix for textarea tag, which can accept array as content
		 */
		if (strpos($input, 'textarea') === 0 && isset($data[0]) && static::is_array_indexed($data[0]) && !is_array($data[0][0])) {
			$data[0]	= implode("\n", $data[0]);
		}
		/**
		 * If associative array given then for every element of array separate copy of current tag will be created
		 */
		if (static::is_array_indexed($data)) {
			if (count($data) > 2) {
				$output	= '';
				foreach ($data as $d) {
					$output			.= static::__callStatic(
						$input,
						$d
					);
				}
				return $output;
			} elseif (
				/**
				 * Fix for "select" and "datalist" tags because they accept arrays as values
				 */
				strpos($input, 'select') !== 0 &&
				strpos($input, 'datalist') !== 0 &&
				strpos($input, 'input') !== 0 &&
				static::is_array_indexed($data[0]) &&
				(
					!isset($data[1]) ||
					!is_array($data[1]) ||
					(
						static::is_array_indexed($data[1]) && !in_array($data[1][0], static::$known_unit_atributes)
					)
				)
			) {
				$output	= '';
				foreach ($data as $d) {
					$output				.= static::__callStatic(
						$input,
						$d
					);
				}
				return $output;
			} elseif (
				static::is_array_indexed($data[0]) &&
				(
					/**
					 * Fix for "select" and "datalist" tags because they accept arrays as values
					 */
					(
						strpos($input, 'select') !== 0 && strpos($input, 'datalist') !== 0
					) ||
					(
						static::is_array_indexed($data[0][0]) && !in_array($data[0][0][0], static::$known_unit_atributes)
					)
				)
			) {
				$output = '';
				foreach ((array)$data[0] as $d) {
					$data[1]	= isset($data[1]) ? $data[1] : [];
					if (!is_array($d) || !isset($d[1]) || !is_array($d[1])) {
						$output			.= static::__callStatic(
							$input,
							[
								$d,
								$data[1]
							]
						);
					} elseif (static::is_array_indexed($d[1]) && !in_array($d[1], static::$known_unit_atributes)) {
						$output			.= static::__callStatic(
							$input,
							[
								$d[0],
								$data[1]
							]
						).
						static::__callStatic(
							$input,
							[
								$d[1],
								$data[1]
							]
						);
					} else {
						$output			.= static::__callStatic(
							$input,
							[
								$d[0],
								static::array_merge($data[1], $d[1])
							]
						);
					}
				}
				return $output;
			} elseif (
				!is_array($data[0]) &&
				!in_array($data[0], static::$known_unit_atributes) &&
				isset($data[1]) &&
				(
					!is_array($data[1]) ||
					(
						static::is_array_indexed($data[1])  && !in_array($data[1][0], static::$known_unit_atributes)
					)
				)
			) {
				$output	= '';
				foreach ($data as $d) {
					$output			.= static::__callStatic(
						$input,
						$d
					);
				}
				return $output;
			}
		} else {
			$data[0]	= $data;
		}
		if (!isset($data[0])) {
			$data[0]	=  '';
		}
		/**
		 * Second part of expression - fix for "select" and "datalist" tags because they accept array as values
		 */
		if (
			!is_array($data[0]) ||
			(
				(
					strpos($input, 'select') === 0 || strpos($input, 'datalist') === 0
				) &&
				!isset($data[0]['in'])
			)
		) {
			$data[0]	= ['in'	=> $data[0]];
		}
		if (isset($data[1])) {
			$data		= static::array_merge($data[0], $data[1]);
		} else {
			$data		= $data[0];
		}
		$attrs	= [];
		/**
		 * Attributes processing
		 */
		if (($pos = mb_strpos($input, '[')) !== false) {
			$attrs_ = explode('][', mb_substr($input, $pos+1, -1));
			$input = mb_substr($input, 0, $pos);
			foreach ($attrs_ as &$attr) {
				$attr				= explode('=', $attr, 2);
				if (isset($attr[1])) {
					$attrs[$attr[0]]	= $attr[1];
				} else {
					$attrs[]			= $attr[0];
				}
			}
			unset($attrs_, $attr);
		}
		/**
		 * Classes processing
		 */
		if (($pos = mb_strpos($input, '.')) !== false) {
			if (!isset($attrs['class'])) {
				$attrs['class']	= '';
			}
			$attrs['class']	= trim($attrs['class'].' '.str_replace('.', ' ', mb_substr($input, $pos)));
			$input			= mb_substr($input, 0, $pos);
		}
		unset($pos);
		/**
		 * Id and tag determination
		 */
		$input	= explode('#', $input);
		$tag	= $input[0];
		/**
		 * Convenient support of custom tags for Web Components
		 *
		 * Allows to write BananaHTML::custom_tag() that will be translated to <custom-tag></custom-tag>
		 */
		$tag	= strtr($tag, '_', '-');
		if (isset($input[1])) {
			$attrs['id'] = $input[1];
		}
		$attrs = static::array_merge($attrs, $data);
		unset($data);
		if ($tag == 'select' || $tag == 'datalist') {
			if (isset($attrs['value'])) {
				$in = [
					'in'	=> $attrs['in'],
					'value'	=> $attrs['value']
				];
				unset($attrs['in'], $attrs['value']);
			} else {
				$in = [
					'in'	=> $attrs['in']
				];
				unset($attrs['in']);
			}
		} elseif (isset($attrs['in'])) {
			$in = $attrs['in'];
			unset($attrs['in']);
		} else {
			$in = '';
		}
		if (isset($attrs['insert'])) {
			$insert	= $attrs['insert'];
			unset($attrs['insert']);
			$data	= [$in, $attrs];
			static::inserts_processing($data, $insert);
			$html	= '';
			foreach ($data as $d) {
				if (method_exists(get_called_class(), $tag)) {
					$html			.= static::$tag($d[0], $d[1]);
				} elseif (in_array($tag, static::$unpaired_tags)) {
					$d[1]['tag']	= $tag;
					$d[1]['in']		= $d[0];
					$html			.= static::u_wrap($d[1]);
				} else {
					$html			.= static::wrap($d[0], $d[1], $tag);
				}
			}
			return $html;
		}
		if (method_exists(get_called_class(), $tag)) {
			$in				= static::$tag($in, $attrs);
		} elseif (in_array($tag, static::$unpaired_tags)) {
			$attrs['tag']	= $tag;
			$attrs['in']	= $in;
			$in				= static::u_wrap($attrs);
		} else {
			$in				= static::wrap($in, $attrs, $tag);
		}
		return $in;
	}
	/**
	 * Checks associativity of array
	 *
	 * @param array	$array	Array to be checked
	 *
	 * @return bool
	 */
	protected static function is_array_assoc ($array) {
		if (!is_array($array) || empty($array)) {
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
	 * @param array	$array	Array to be checked
	 *
	 * @return bool
	 */
	protected static function is_array_indexed ($array) {
		if (!is_array($array) || empty($array)) {
			return false;
		}
		return !static::is_array_assoc($array);
	}
	/**
	 * Prepare text to be used as value for html attribute value
	 *
	 * @param string|string[]	$text
	 *
	 * @return string|string[]
	 */
	static function prepare_attr_value ($text) {
		if (is_array($text)) {
			foreach ($text as &$val) {
				$val = static::prepare_attr_value($val);
			}
			return $text;
		}
		return strtr(
			$text,
			[
				'&'		=> '&amp;',
				'"'		=> '&quot;',
				'\''	=> '&apos;',
				'<'		=> '&lt;',
				'>'		=> '&gt;'
			]
		);
	}
	/**
	 * Works like <b>array_flip()</b> function, but is used when every item of array is not a string, but may be also array
	 *
	 * @param array			$array	At least one item must be array, some other items may be strings (or numbers)
	 *
	 * @return array|bool
	 */
	protected static function array_flip_3d ($array) {
		if (!is_array($array)) {
			return false;
		}
		$result	= [];
		$size	= 0;
		foreach ($array as $values) {
			$size	= max($size, count((array)$values));
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
