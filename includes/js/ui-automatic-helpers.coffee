###*
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	ui_automatic_helpers_update	= (element) ->
		$element	= $(element)

		$element.filter('.cs-tabs:not(.uk-tab)').cs().tabs()
		$element.find('.cs-tabs:not(.uk-tab)').cs().tabs()
	do ->
		body	= document.querySelector('body')
		ui_automatic_helpers_update(body)
		cs.observe_inserts_on(body, ui_automatic_helpers_update)
