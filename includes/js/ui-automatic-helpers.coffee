###*
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	window.no_ui_selector		= '.cs-no-ui'
	ui_automatic_helpers_update = (element) ->
		element.filter('.cs-tabs:not(.uk-tab)').cs().tabs()
		element.find('.cs-tabs:not(.uk-tab)').cs().tabs()

		if element.is(no_ui_selector) || element.closest(no_ui_selector).length
			return

		element.filter('textarea:not(.cs-no-resize)')
			.autosize()
		element.find("textarea:not(#{no_ui_selector}, .cs-no-resize)")
			.autosize()
	ui_automatic_helpers_update($('body'))
	do ->
		MutationObserver		= window.MutationObserver || window.WebKitMutationObserver
		eventListenerSupported	= window.addEventListener;
		if MutationObserver
			(
				new MutationObserver (mutations) ->
					mutations.forEach (mutation) ->
						if mutation.addedNodes.length
							ui_automatic_helpers_update($(mutation.addedNodes))
			).observe(
				document.body
				childList	: true
				subtree		: true
			)
		else if eventListenerSupported
			document.body.addEventListener(
				'DOMNodeInserted'
				->
					ui_automatic_helpers_update($('body'))
				false
			)
