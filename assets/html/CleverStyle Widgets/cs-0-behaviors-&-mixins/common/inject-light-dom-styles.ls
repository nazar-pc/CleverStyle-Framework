/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
styles								= {}
csw.behaviors.inject-light-styles	= [
	attached : !->
		# Hack: The only way to achieve proper styling inside of cs-form, otherwise new slots system doesn't give us enough flexibility
		if @_styles_added
			return
		@_styles_added	= true
		if !styles[@_styles_dom_module]
			head	= document.querySelector('head')
			head.insertAdjacentHTML(
				'beforeend',
				"""<custom-style><style include="#{@_styles_dom_module}"></style></custom-style>"""
			)
			custom_style_element	= head.lastElementChild
			cs.ui.ready.then !~>
				Polymer.updateStyles()
				styles[@_styles_dom_module]	= custom_style_element.firstElementChild.textContent.split(':not([style-scope]):not(.style-scope)').join('')
				head.removeChild(custom_style_element)
				@insertAdjacentHTML('beforeend', "<style>#{styles[@_styles_dom_module]}</style>")
		else
			@insertAdjacentHTML('beforeend', "<style>#{styles[@_styles_dom_module]}</style>")
]
