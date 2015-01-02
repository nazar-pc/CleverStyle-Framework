###*
 * @package		UIkit Helper
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ($=jQuery, UI = UIkit) ->
	helpers	=
		###*
		 * Tabs with UIkit
		 *
		 * Required DOM structure *+*, where first element contains list of tabs, and second element content of each tab, plugin must be applied to the first element
		###
		tabs			: ->
			if !@.length
				return @
			@.each ->
				$this	= $(@)
				content	= $this.next()
				$this
					.addClass('uk-tab')
					.attr('data-uk-tab', '')
					.children()
						.each ->
							li	= $(@)
							if !li.children('a').length
								li.wrapInner('<a />')
						.first()
							.addClass('uk-active')
				$this
					.data('tab', UI.tab(
						$this
						connect		: content
						animation	: 'fade'
					))
				content
					.addClass('uk-switcher uk-margin')
					.children(':first')
						.addClass('uk-active')
		###*
		 * Dialog with UIkit
		 *
		 * Required DOM structure * > *, plugin must be applied to the root element
		 * If child element is not present - content will be automatically wrapped with <div>
		###
		modal			: (mode) ->
			if !@.length
				return @
			mode	= mode || 'init'
			@.each ->
				$this	= $(@)
				if !$this.data('modal')
					content	= $this.children()
					if !content.length
						content	= $this
							.wrapInner('<div />')
							.children()
					content
						.addClass('uk-modal-dialog uk-modal-dialog-slide')
					if $this.is('[data-modal-frameless]')
						content
							.addClass('uk-modal-dialog-frameless')
					if $this.attr('title')
						$('<h3 />')
							.html($this.attr('title'))
							.prependTo(content)
					if content.attr('title')
						$('<h3 />')
							.html(content.attr('title'))
							.prependTo(content)
					$this
						.addClass('uk-modal')
						.data('modal', UI.modal($this))
				modal	= $this.data('modal')
				switch mode
					when 'show' then modal.show()
					when 'hide' then modal.hide()

	###*
	 * cs helper registration or running (if no parameters specified)
	 *
	 * @param {string}		name
	 * @param {function}	helper
	###
	$.fn.cs	= (name, helper) ->
		if name && helper
			helpers[name]	= helper
			return @
		public_helpers		= {}
		for name, func of helpers
			public_helpers[name] = func.bind(@)
		public_helpers
	$.cs	=
		###*
		 * Simple wrapper around $(...).cs().modal() with inner form
		 *
		 * All content will be inserted into modal form, optionally it is possible to add close button and set width
		 *
		 * @return jQuery Root modal element, it is possible to use .cs().modal() on it and listen for events
		###
		simple_modal	: (content, close = false, width) ->
			style	= if width then ' style="width:' + width + 'px;"' else ''
			close	= if close then """<a class="uk-modal-close uk-close"></a>""" else ''
			$("""
				<div>
					<div class="uk-form"#{style}>
						#{close}
						#{content}
					</div>
				</div>
			""")
				.appendTo('body')
				.cs().modal('show')
				.on 'hide.uk.modal', ->
					$(@).remove()
