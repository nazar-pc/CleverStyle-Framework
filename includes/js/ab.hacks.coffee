###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ($ = jQuery) ->
	###
	 # Fix for jQuery "ready" event, trigger it after "WebComponentsReady" event triggered by WebComponents.js
	###
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
	# Fix for UIkit dropdown not closed under Shadow DOM when clicked on some selected item
	$ ->
		registerOuterClick__original	= UIkit.components.dropdown.prototype.registerOuterClick
		UIkit.components.dropdown.prototype.registerOuterClick	= ->
			if !WebComponents.flags.shadow && @element[0].matches(':host *')
				$(@element[0]).find('li').one('click', (e) ->
					UIkit.$html.trigger("click.outer.dropdown", e)
				)
			registerOuterClick__original.call(@)
