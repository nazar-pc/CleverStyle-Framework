<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs;
use
	HTMLPurifier_Filter;

/**
 * Custom filter to allow iframes within sandbox
 */
class HTMLPurifier_Filter_iframe_sandbox extends HTMLPurifier_Filter {
	/**
	 * @type string
	 */
	public $name = 'iframe_sandbox';
	/**
	 * @param string $html
	 *
	 * @return string
	 */
	public function preFilter ($html, $config, $context) {
		return preg_replace(
			'#<iframe([^>]+)>.*</iframe>#Uis',
			'<span class="iframe-sandbox">\1</span>',
			$html
		);
	}
	/**
	 * @param string $html
	 *
	 * @return string
	 */
	public function postFilter ($html, $config, $context) {
		return preg_replace_callback(
			'#<span class="iframe-sandbox">([^>]+)</span>#',
			function ($matches) {
				if (preg_match_all('/([a-z]+)=["\']?([^\s"\']+)/', $matches[1], $attributes)) {
					$attributes = array_combine($attributes[1], $attributes[2]);
				}
				$attributes_string = 'frameborder="0" allowfullscreen sandbox="allow-same-origin allow-forms allow-popups allow-scripts"';
				if (@$attributes['height']) {
					$attributes_string .= ' height="'.(int)$attributes['height'].'"';
				}
				if (@$attributes['width']) {
					$attributes_string .= ' width="'.(int)$attributes['width'].'"';
				}
				if (@$attributes['src'] && preg_match('#^(http[s]?:)?//#', $attributes['src'])) {
					$attributes_string .= ' src="'.$attributes['src'].'"';
				} else {
					return '';
				}
				return "<iframe $attributes_string></iframe>";
			},
			$html
		);
	}
}
