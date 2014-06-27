###*
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	ui_automatic_helpers_update = (element) ->
		cs.async_call [
			->
				element.filter('form:not(.cs-no-ui, .uk-form)').addClass('uk-form')
				element.find('form:not(.cs-no-ui, .uk-form)').addClass('uk-form')
			->
				element.filter(':not(.uk-button) > input:radio:not(.cs-no-ui)').cs().radio()
				element.find(':not(.uk-button) > input:radio:not(.cs-no-ui)').cs().radio()
			->
				element.filter(':not(.uk-button) > input:checkbox:not(.cs-no-ui)').cs().checkbox()
				element.find(':not(.uk-button) > input:checkbox:not(.cs-no-ui)').cs().checkbox()
			->
				element.filter('.cs-table').addClass('uk-table uk-table-condensed uk-table-hover')
				element.find('.cs-table').addClass('uk-table uk-table-condensed uk-table-hover')
			->
				element.filter(':button:not(.cs-no-ui), .cs-button, .cs-button-compact, .uk-button')
					.addClass('uk-button')
					.disableSelection()
				element.find(':button:not(.cs-no-ui), .cs-button, .cs-button-compact, .uk-button')
					.addClass('uk-button')
					.disableSelection()
			->
				element.filter('textarea:not(.cs-no-ui, .cs-no-resize, .EDITOR, .SIMPLE_EDITOR, .cs-autosized)')
					.addClass('cs-autosized')
					.autosize
						append	: "\n"
				element.find('textarea:not(.cs-no-ui, .cs-no-resize, .EDITOR, .SIMPLE_EDITOR, .cs-autosized)')
					.addClass('cs-autosized')
					.autosize
						append	: "\n"
			->
				element.find('.SIMPLEST_INLINE_EDITOR')
					.prop('contenteditable', true)
			->
				element.find('[data-title]:not(data-uk-tooltip)').cs().tooltip()
			->
				element.find('.cs-tabs:not(.uk-tab)').cs().tabs()
		]
	ui_automatic_helpers_update($('body'))
	do ->
		MutationObserver		= window.MutationObserver || window.WebKitMutationObserver
		eventListenerSupported	= window.addEventListener;
		if !MutationObserver
			(
				new MutationObserver (mutations) ->
					mutations.forEach ->
						if @.addedNodes.length
							ui_automatic_helpers_update($(@))
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
