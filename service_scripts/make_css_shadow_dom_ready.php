<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Service scripts
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if ($argc < 2) {
	echo 'Converts any CSS file into CSS that works inside Shadow DOM (native support of polyfill needed after conversion).\nUsage: php make_css_shadow_dom_ready.php source.css[ destination.css]';
	return;
}
$content = file_get_contents($argv[1]);
if (preg_match('#/deep/#', $content)) {
	echo 'Already ready, no actions needed!';
	return;
}
function process_content ($content) {
	return preg_replace_callback('/([^\{\}]+)(\{[^\}]+\})/', function ($matches) {
		$selector = explode(',', $matches[1]);
		$content  = $matches[2];
		foreach ($selector as &$s) {
			$s = trim($s);
			if ($s == 'html' || preg_match('/^[0-9]+(\s+)?%$/', $s)) {
				continue;
			}
			if (strpos($s, '@') === 0) {
				$content = process_content($content);
				continue;
			}
			if (strpos($s, 'html') === 0) {
				$s = explode(' ', $s, 2);
				$s = "$s[0] /deep/ $s[1]";
			} else {
				$s = "html /deep/ $s";
			}
		}
		$selector = implode(',', $selector);
		return "$selector$content";
	}, $content);
}

$content = process_content($content);
file_put_contents(@$argv[2] ?: $argv[1], $content);
echo 'Ready!';
