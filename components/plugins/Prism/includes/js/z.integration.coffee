###*
 * @package		Prism
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
document.removeEventListener('DOMContentLoaded', Prism.highlightAll)
Prism.highlightAll = (async, callback) ->
	elements = document.querySelectorAll('html /deep/ code[class*="language-"], html /deep/ [class*="language-"] code, html /deep/ code[class*="lang-"], html /deep/ [class*="lang-"] code')
	for element in elements
		if element.matches('html /deep/ [contenteditable=true] *') || element.matches('html /deep/ .INLINE_EDITOR *')
			continue
		(
			if element.parentNode.tagName == 'PRE'
				element.parentNode
			else
				element
		).classList.add('line-numbers')
		Prism.highlightElement(element, async == true, callback);
$ Prism.highlightAll
