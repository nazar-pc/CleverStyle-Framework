###*
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	window.no_ui_selector		= '.cs-no-ui'
	ui_automatic_helpers_update	= (element) ->
		$element	= $(element)

		$element.filter('.cs-tabs:not(.uk-tab)').cs().tabs()
		$element.find('.cs-tabs:not(.uk-tab)').cs().tabs()

		if $element.is(no_ui_selector) || $element.closest(no_ui_selector).length
			return

		$element.filter('textarea:not(.cs-no-resize, .autosizejs)')
			.autosize()
		$element.find("textarea:not(#{no_ui_selector}, .cs-no-resize, .autosizejs)")
			.autosize()
	do ->
		body	= document.querySelector('body')
		ui_automatic_helpers_update(body)
		cs.observe_inserts_on(body, ui_automatic_helpers_update)
