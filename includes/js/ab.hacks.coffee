###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
###
 # Fix for "TypeError: Argument 1 of Window.getComputedStyle does not implement interface Element." with Polymer Platform
###
do ->
	getComputedStyle_original	= window.getComputedStyle
	window.getComputedStyle		= (el, pseudo) ->
		if !(el instanceof HTMLElement)
			return true
		getComputedStyle_original.call(window, el, pseudo)
###
 # Fix for jQuery "ready" event, trigger it after "WebComponentsReady" event triggered by Polymer Platform
###
do ($ = jQuery) ->
	ready_original	= $.fn.ready
	functions		= []
	ready			= false
	$.fn.ready = (fn) ->
		functions.push(fn)
	document.addEventListener('WebComponentsReady', ->
		if !ready
			ready		= true;
			$.fn.ready	= ready_original
			functions.forEach (fn) ->
				$(fn)
			functions	= []
	)
###
 # Fix for jQuery.css() method with Polymer Platform
###
do ($ = jQuery) ->
	css_original	= $.css
	$.css			= (elem, name, extra, styles) ->
		css_original.call(@, wrap(elem), name, extra, styles)
