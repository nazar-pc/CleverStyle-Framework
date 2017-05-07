/**
 * @package   Prism
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Prism.plugins.autoloader.languages_path	= '/modules/Prism/assets/js/components/'
Prism.highlightAll = (async, callback, root) !->
	elements = (root || document).querySelectorAll('code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code')
	for element in elements
		if element.matches('[contenteditable=true] *')
			continue
		(
			if element.parentNode.tagName == 'PRE'
				element.parentNode
			else
				element
		).classList.add('line-numbers')
		Prism.highlightElement(element, async == true, callback)
cs.ui.ready.then(Prism.highlightAll)
cs.Event.on('System/content_enhancement', ({element}) !->
	Prism.highlightAll(true, ->, element)
	if !document.querySelector('custom-style > style[include=cs-prism-styles]')
		element.insertAdjacentHTML(
			'beforeend',
			"""<custom-style><style include="cs-prism-styles"></style></custom-style>"""
		)
)
