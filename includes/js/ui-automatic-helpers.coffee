###*
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	window.no_ui_selector		= '.cs-no-ui'
	ui_automatic_helpers_update = (element) ->
		element.find('.SIMPLEST_INLINE_EDITOR')
			.prop('contenteditable', true)

		element.filter('[data-title]:not(data-uk-tooltip)').cs().tooltip()
		element.find('[data-title]:not(data-uk-tooltip)').cs().tooltip()

		element.filter('.cs-tabs:not(.uk-tab)').cs().tabs()
		element.find('.cs-tabs:not(.uk-tab)').cs().tabs()

		if element.is(no_ui_selector) || element.closest(no_ui_selector).length
			return

		element.filter('form:not(.uk-form)').addClass('uk-form')
		element.find("form:not(#{no_ui_selector}, .uk-form)").addClass('uk-form')

		element.filter(":not(.uk-button) > input:radio:not(#{no_ui_selector})").cs().radio()
		element.find(":not(.uk-button) > input:radio:not(#{no_ui_selector})").cs().radio()

		element.filter(":not(.uk-button) > input:checkbox:not(#{no_ui_selector})").cs().checkbox()
		element.find(":not(.uk-button) > input:checkbox:not(#{no_ui_selector})").cs().checkbox()

		element.filter(':button:not(.uk-button), .cs-button, .cs-button-compact')
			.addClass('uk-button')
			.disableSelection()
		element.find(":button:not(#{no_ui_selector}, .uk-button), .cs-button, .cs-button-compact")
			.addClass('uk-button')
			.disableSelection()

		element.filter('textarea:not(.cs-no-resize, .EDITOR, .SIMPLE_EDITOR, .cs-autosized)')
			.addClass('cs-autosized')
			.autosize
				append	: "\n"
		element.find("textarea:not(#{no_ui_selector}, .cs-no-resize, .EDITOR, .SIMPLE_EDITOR, .cs-autosized)")
			.addClass('cs-autosized')
			.autosize
				append	: "\n"
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
