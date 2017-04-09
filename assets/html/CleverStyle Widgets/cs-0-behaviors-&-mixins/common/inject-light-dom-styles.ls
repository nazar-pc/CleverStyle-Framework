/**
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
styles	= {}
Polymer.{}cs.{}behaviors.inject-light-styles = [
	attached : !->
		# Hack: The only way to achieve proper styling inside of cs-form, otherwise new slots system doesn't give us enough flexibility
		if @_styles_added
			return
		@_styles_added	= true
		if !styles[@_styles_dom_module]
			head	= document.querySelector('head')
			head.insertAdjacentHTML(
				'beforeend',
				"""<style is="custom-style" include="#{@_styles_dom_module}"></style>"""
			)
			style_element	= head.lastElementChild
			Polymer.updateStyles()
			cs.ui.ready.then !~>
				styles[@_styles_dom_module]	= style_element.textContent.split(':not([style-scope]):not(.style-scope)').join('')
				head.removeChild(style_element)
				@insertAdjacentHTML('beforeend', "<style>#{styles[@_styles_dom_module]}</style>")
		else
			@insertAdjacentHTML('beforeend', "<style>#{styles[@_styles_dom_module]}</style>")
]
