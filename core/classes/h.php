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
class h {
	protected static	$unit_atributes = [	//Unit attributes, that have no value, or have the same value as name in xhtml style
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
	 * @param null		$label
	 *
	 * @return bool
	 */
	protected static function data_prepare (&$data, &$in, &$tag, &$add, &$label = null) {
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
		if (in_array('no-label', $data, true)) {
			$label				= false;
			unset($data[array_search('no-label', $data)]);
		} else {
			$label				= true;
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
			$data['src']		= self::url($data['src']);
		}
		if (isset($data['href'])) {
			$data['href']		= str_replace(' ', '%20', $data['href']);
			if ($tag != 'a') {
				$data['href']		= self::url($data['href']);
			}
		}
		if (isset($data['action'])) {
			$data['action']		= str_replace(' ', '%20', $data['action']);
		}
		if (isset($data['formaction'])) {
			$data['formaction']	= str_replace(' ', '%20', $data['formaction']);
		}
		if (isset($data['add'])) {
			$add				= ' '.$data['add'];
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
			$data['value']		= filter($data['value']);
		}
		ksort($data);
		foreach ($data as $key => $value) {
			if (is_int($key)) {
				unset($data[$key]);
				if (in_array($value, self::$unit_atributes)) {
					$add	.= ' '.$value.(XHTML_TAGS_STYLE ? '='.$q.$value.$q : '');
				}
			} elseif ($value !== false) {
				$add			.= ' '.$key.'='.$q.$value.$q;
			}
		}
		return true;
	}
	/**
	 * Adds, if necessary, slash or domain at the beginning of the url, provides correct absolute/relative url
	 *
	 * @static
	 * @param string	$url
	 * @param bool		$absolute	Returns absolute url or relative
	 *
	 * @return string
	 */
	static function url ($url, $absolute = false) {
		if (substr($url, 0, 1) == '#') {
			global $Config;
			if (is_object($Config)) {
				return $Config->base_url().'/'.$Config->server['raw_relative_address'].$url;
			}
		} elseif (
			substr($url, 0, 5) != 'data:' &&
			substr($url, 0, 1) != '/' &&
			substr($url, 0, 7) != 'http://' &&
			substr($url, 0, 8) != 'https://'
		) {
			global $Config;
			if ($absolute && is_object($Config)) {
				return $Config->base_url().'/'.$url;
			}
			return '/'.$url;
		}
		return $url;
	}
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
		$data	= self::array_merge(is_array($in) ? $in : ['in' => $in], is_array($data) ? $data : [], ['tag' => $tag]);
		$in		= $add = '';
		$tag	= 'div';
		$level	= 1;
		if (isset($data['data-title']) && $data['data-title']) {
			$data['data-title'] = filter($data['data-title']);
			if (isset($data['class'])) {
				$data['class'] .= ' cs-info';
			} else {
				$data['class'] = 'cs-info';
			}
		}
		if (isset($data['data-dialog'])) {
			$data['data-dialog'] = filter($data['data-dialog']);
			if (isset($data['class'])) {
				$data['class'] .= ' cs-dialog';
			} else {
				$data['class'] = 'cs-dialog';
			}
		}
		if (isset($data['level'])) {
			$level = $data['level'];
			unset($data['level']);
		}
		if (!self::data_prepare($data, $in, $tag, $add)) {
			return false;
		}
		return	'<'.$tag.$add.'>'.
				($level ? "\n" : '').
				self::level(
					$in || $in === 0 || $in === '0' ? $in.($level ? "\n" : '') : ($in === false ? '' : ($level ? "&nbsp;\n" : '')),
				$level).
				'</'.$tag.'>'.
				($level ? "\n" : '');
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
		$in = $add = '';
		$tag = 'input';
		if (!self::data_prepare($data, $in, $tag, $add, $label)) {
			return false;
		}
		if (isset($data['data-title']) && $data['data-title']) {
			$data_title = $data['data-title'];
			unset($data['data-title']);
		}
		if (isset($data['type']) && $data['type'] == 'checkbox' && $label) {
			$return = '<'.$tag.$add.(XHTML_TAGS_STYLE ? ' /' : '').'>'.self::label(
				$in,
				[
					'for'	=> $data['id']
				]
			)."\n";
			return self::span(
				$return,
				[
					'data-title' => isset($data_title) ? $data_title : false
				]
			);
		} else {
			$return = '<'.$tag.$add.(XHTML_TAGS_STYLE ? ' /' : '').'>'.$in."\n";
			return isset($data_title) ? self::label(
				$return,
				[
					'data-title' => $data_title
				]
			) : $return;
		}
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
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return self::__callStatic(__FUNCTION__, [$in, $data]);
		}
		global $User;
		if (isset($in['method'])) {
			$data['method']	= $in['method'];
		}
		if (!isset($data['method'])) {
			$data['method']	= 'post';
		}
		if (strtolower($data['method']) == 'post' && is_object($User)) {
			$in_ = self::{'input[type=hidden][name=session]'}([
				'value'	=> $User->get_session()
			]);
			if (!is_array($in)) {
				$in			.= $in_;
			} else {
				$in['in']	.= $in_;
			}
			unset($in_);
		}
		return self::wrap($in, $data, __FUNCTION__);
	}
	/**
	 * Rendering of input tag with automatic adding labels for type=radio if necessary and autocorrection if min and/or max attributes are specified
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
		if ($in === false) {
			return '';
		}
		if (!empty($data)) {
			$in = array_merge(['in' => $in], $data);
		}
		if (isset($in['type']) && $in['type'] == 'radio') {
			if (is_array_indexed($in) && is_array($in[0])) {
				return self::__callStatic(__FUNCTION__, [$in, $data]);
			}
			if (is_array($in)) {
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
						if (!isset($in['add'][$i])) {
							$in['add'][$i] = ' checked';
						} else {
							$in['add'][$i] .= ' checked';
						}
						break;
					}
				}
				unset($in['checked'], $i, $v);
				$items = array_flip_3d($in);
				unset($in, $v, $i);
				$temp = '';
				foreach ($items as $item) {
					if (!isset($item['id'])) {
						$item['id'] = uniqid('input_');
					}
					if (isset($item['in'])) {
						$item['in'] = self::label($item['in'], ['for' => $item['id']]);
					}
					$item['tag'] = __FUNCTION__;
					if (isset($item['value'])) {
						$item['value'] = filter($item['value']);
					}
					$temp .= self::u_wrap($item);
				}
				return $temp;
			} else {
				if (!isset($in['id'])) {
					$in['id'] = uniqid('input_');
				}
				$in['in'] = self::label($in['in'], ['for' => $in['id']]);
				$in['tag'] = __FUNCTION__;
				if (isset($in['value'])) {
					$in['value'] = filter($in['value']);
				}
				return self::u_wrap($in);
			}
		} else {
			if (is_array_indexed($in)) {
				return self::__callStatic(__FUNCTION__, [$in, $data]);
			}
			if (
				(
					isset($in['name'])	&& is_array($in['name'])
				) ||
				(
					isset($in['id'])	&& is_array($in['id'])
				)
			) {
				$items	= array_flip_3d($in);
				$return	= '';
				foreach ($items as $item) {
					$return .= self::input($item);
				}
				return $return;
			} else {
				if (!isset($in['type'])) {
					$in['type'] = 'text';
				} elseif ($in['type'] == 'checkbox' && !isset($in['id'])) {
					$in['id'] = uniqid('input_');
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
				return self::u_wrap($in);
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
		if (!is_array($in)) {
			return self::wrap($in, $data, $function);
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
		$options = array_flip_3d($in);
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
			$option			= self::option($option);
		}
		unset($option);
		return self::wrap(implode('', $options), $data, $function);
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
		return self::template_1($in, $data, __FUNCTION__);
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
		return self::template_1($in, $data, __FUNCTION__);
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
		global $Page;
		$uniqid = uniqid('html_replace_');
		if (is_array($in)) {
			if (isset($in['in'])) {
				$Page->replace($uniqid, is_array($in['in']) ? implode("\n", $in['in']) : $in['in']);
				$in['in'] = $uniqid;
			} else {
				$Page->replace($uniqid, implode("\n", $in));
				$in = $uniqid;
			}
		} else {
			$Page->replace($uniqid, is_array($in) ? implode("\n", $in) : $in);
			$in = $uniqid;
		}
		$data['level'] = false;
		return self::wrap($in, $data, $function);
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
		return self::template_2($in, $data, __FUNCTION__);
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
		return self::template_2($in, $data, __FUNCTION__);
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
		return self::template_2($in, $data, __FUNCTION__);
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
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return self::__callStatic(__FUNCTION__, [$in, $data]);
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
		return self::wrap($in, $data, __FUNCTION__);
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
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return self::__callStatic(__FUNCTION__, [$in, $data]);
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
		return self::wrap($in, $data, __FUNCTION__);
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
		return str_repeat(self::u_wrap($in), $repeat);
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
		if ($in === false) {
			return '';
		} elseif (is_array($in)) {
			return self::__callStatic(__FUNCTION__, [$in, $data]);
		}
		global $Config, $L;
		if (is_object($Config) && $Config->core['show_tooltips']) {
			return self::span($L->$in, array_merge(['data-title' => $L->{$in.'_info'}], $data));
		} else {
			return self::span($L->$in, $data);
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
		if ($class === false) {
			return '';
		}
		if (!isset($data['style'])) {
			$data['style'] = 'display: inline-block; margin-bottom: -2px;';
		} else {
			$data['style'] .= ' display: inline-block; margin-bottom: -2px;';
		}
		if (!isset($data['class'])) {
			$data['class'] = 'ui-icon ui-icon-'.$class;
		} else {
			$data['class'] .= ' ui-icon ui-icon-'.$class;
		}
		return self::span($data);
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
			$array1['class'] .= ' '.$array2['class'];
			unset($array2['class']);
		}
		if (isset($array1['class'], $array3['class'])) {
			$array1['class'] .= ' '.$array3['class'];
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
		return array_merge($array1, $array2, $array3);
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
		if ($data === false) {
			return false;
		}
		if (is_scalar($data)) {
			$data		= [$data];
		} elseif (isset($data[1]) && $data[1] === false && !isset($data[2])) {
			unset($data[1]);
		}
		$input	= trim($input);
		/**
		 * Analysis of called tag. If space found - nested tags presented
		 */
		if (strpos($input, ' ') !== false) {
			$input		= explode(' ', $input, 2);
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
						is_array_indexed($data[1])
					)
				) {
					$data	= [$data];
				}
				foreach ($data[0] as $d) {
					if (isset($d[0]) && is_array_indexed($d[0]) && !in_array($d[0][0], self::$unit_atributes)) {
						if (
							isset($d[1]) &&
							(
								!is_array($d[1]) ||
								(
									is_array_indexed($d[1]) && !in_array($d[1][0], self::$unit_atributes)
								)
							)
						) {
							$output_	= [];
							foreach ($d as $d_) {
								$output_[]	= self::__callStatic($input[1], $d_);
							}
							$output[]	= $output_;
							unset($output_);
						} else {
							$output[]	= [
								self::__callStatic($input[1], $d[0]),
								isset($d[1]) ? $d[1] : ''
							];
						}
					} else {
						$output[]	= self::__callStatic($input[1], $d);
					}
				}
				unset($d);
			} elseif (!isset($data[1]) || is_array_assoc($data[1])) {
				$output		= self::__callStatic(
					$input[1],
					[
						isset($data[0]) ? $data[0] : '',
						isset($data[1]) ? $data[1] : false
					]
				);
				$data[1]	= [];
			} else {
				$output		= self::__callStatic(
					$input[1],
					$data
				);
				$data[1]	= [];
			}
			return self::__callStatic(
				$input[0],
				[
					$output,
					isset($data[1]) ? $data[1] : false
				]
			);
		}
		/**
		 * Fix for textarea tag, which can accept array as content
		 */
		if (strpos($input, 'textarea') === 0 && isset($data[0]) && is_array_indexed($data[0]) && !is_array($data[0][0])) {
			$data[0]	= implode("\n", $data[0]);
		}
		/**
		 * If associative array given then for every element of array separate copy of current tag will be created
		 */
		if (is_array_indexed($data)) {
			if (count($data) > 2) {
				$output	= '';
				foreach ($data as $d) {
					$output			.= self::__callStatic(
						$input,
						$d
					);
				}
				return $output;
			} elseif (
				/**
				 * Fix for "select" and "datalist" tags bescause they accept arrays as values
				 */
				strpos($input, 'select') !== 0 &&
				strpos($input, 'datalist') !== 0 &&
				strpos($input, 'input') !== 0 &&
				(
					(
						is_array_indexed($data[0]) &&
						(
							!isset($data[1]) ||
							!is_array($data[1]) ||
							(
								is_array_indexed($data[1]) && !in_array($data[1][0], self::$unit_atributes)
							)
						)
					)
				)
			) {
				$output	= '';
				foreach ($data as $d) {
					$output				.= self::__callStatic(
						$input,
						$d
					);
				}
				return $output;
			} elseif (
				is_array_indexed($data[0]) &&
				(
					/**
					 * Fix for "select" and "datalist" tags bescause they accept arrays as values
					 */
					(
						strpos($input, 'select') !== 0 && strpos($input, 'datalist') !== 0
					) ||
					(
						is_array_indexed($data[0][0]) && !in_array($data[0][0][0], self::$unit_atributes)
					)
				)
			) {
				$output = '';
				foreach ($data[0] as $d) {
					if (!is_array($d) || !isset($d[1]) || !is_array($d[1])) {
						$output			.= self::__callStatic(
							$input,
							[
								$d,
								$data[1]
							]
						);
					} elseif (is_array_indexed($d[1]) && !in_array($d[1], self::$unit_atributes)) {
						$output			.= self::__callStatic(
							$input,
							[
								$d[0],
								$data[1]
							]
						).
						self::__callStatic(
							$input,
							[
								$d[1],
								$data[1]
							]
						);
					} else {
						$output			.= self::__callStatic(
							$input,
							[
								$d[0],
								self::array_merge($data[1], $d[1])
							]
						);
					}
				}
				return $output;
			} elseif (
				!is_array($data[0]) &&
				!in_array($data[0], self::$unit_atributes) &&
				isset($data[1]) &&
				(
					!is_array($data[1]) ||
					(
						is_array_indexed($data[1])  && !in_array($data[1][0], self::$unit_atributes)
					)
				)
			) {
				$output	= '';
				foreach ($data as $d) {
					$output			.= self::__callStatic(
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
		 * Second part of expression - fix for "select" and "datalist" tags bescause they accept array as values
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
			$data		= self::array_merge($data[0], $data[1]);
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
				$attr				= explode('=', $attr);
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
		if (isset($input[1])) {
			$attrs['id'] = $input[1];
		}
		$attrs = self::array_merge($attrs, $data);
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
		if (method_exists('h', $tag)) {
			$in				= self::$tag($in, $attrs);
		} elseif (in_array($tag, self::$unpaired_tags)) {
			$attrs['tag']	= $tag;
			$attrs['in']	= $in;
			$in				= self::u_wrap($attrs);
		} else {
			$in				= self::wrap($in, $attrs, $tag);
		}
		return $in;
	}
}