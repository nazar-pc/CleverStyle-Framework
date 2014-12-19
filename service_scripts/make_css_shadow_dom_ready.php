<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Service scripts
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
if ($argc < 3) {
	exit("Converts any CSS file into CSS that works inside Shadow DOM (native support of polyfill needed after conversion).\nUsage: php make_css_shadow_dom_ready.php source.css destination.css\n");
}
$content = file_get_contents($argv[1]);
if (preg_match('#/deep/#', $content)) {
	exit("Already ready, no actions needed!\n");
}
function process_content ($content) {
	return preg_replace_callback('/([^\{\}]+)(\{[^\}]+\})/', function ($matches) {
		$selector = explode(',', $matches[1]);
		$content  = $matches[2];
		foreach ($selector as &$s) {
			$s = trim($s);
			if ($s == 'html') {
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
file_put_contents($argv[2], $content);
exit("Ready!\n");
