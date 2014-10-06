###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
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
