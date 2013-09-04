###*
 * @package		UIkit Helper
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
(($) ->
	$.fn.cs	= ->
		this_	= this
		###*
		 * Radio buttons with UIkit
		 *
		 * Required DOM structure * > label > input:radio, plugin may be applied to any of these elements
		###
		radio		: ->
			if !this_.length
				return this_
			collection	= []
			this_.each ->
				radio	= $(this)
				if !radio.is(':radio')
					radio	= radio.find(':radio')
				collection.push(radio.parent().parent().get())
			collection	= $($.unique(collection))
			collection.each ->
				$(this)
					.addClass('uk-button-group')
					.attr('data-uk-button-radio', '')
					.children('label')
						.addClass('uk-button')
						.click ->
							$(this).find(':radio').prop('checked', true).change()
						.find(':radio')
							.change ->
								$this	= $(this)
								if !$this.is(':checked')
									return
								$this.parent()
									.parent()
										.children('.uk-active')
											.removeClass('uk-active')
											.end()
										.end()
									.addClass('uk-active')
							.filter(':checked')
							.parent()
								.addClass('uk-active')
			this
		###*
		 * Checkboxes with UIkit
		 *
		 * Required DOM structure * > label > input:checkbox, plugin may be applied to any of these elements
		###
		checkbox	: ->
			if !this_.length
				return this_
			collection	= []
			this_.each ->
				checkbox	= $(this)
				if !checkbox.is(':checkbox')
					checkbox	= checkbox.find(':checkbox')
				collection.push(checkbox.parent().parent().get())
			collection	= $($.unique(collection))
			collection.each ->
				$(this)
					.addClass('uk-button-group')
					.attr('data-uk-button-checkbox', '')
					.children('label')
						.addClass('uk-button')
						.click ->
							$(this).find(':radio:not(:checked)').prop('checked', true).change()
						.find(':checkbox')
							.change ->
								$this	= $(this)
								if !$this.is(':checked')
									return
								$this.parent()
									.parent()
										.children('.uk-active')
											.removeClass('uk-active')
											.end()
										.end()
									.addClass('uk-active')
							.filter(':checked')
							.parent()
								.addClass('uk-active')
			this
		###*
		 * Tabs with UIkit
		 *
		 * Required DOM structure *+*, where first element contains list of tabs, and second element content of each tab, plugin must be applied to the first element
		###
		tabs		: ->
			if !this_.length
				return this_
			UI	= $.UIkit
			this_.each ->
				$this	= $(this)
				content	= $this.next()
				$this
					.addClass('uk-tab')
					.attr('data-uk-tab', '')
					.children()
						.each ->
							li	= $(this)
							if !li.children('a').length then li.wrapInner('<a />')
						.first()
							.addClass('uk-active')
				$this
					.data("tab", new UI.tab($this, {connect:content}))
				content
					.addClass('uk-switcher uk-margin')
					.children(':first')
						.addClass('uk-active')
				content
					.data("switcher", new UI.switcher(content))
		###*
		 * Tooltip with Twitter Bootstrap
		 *
		 * Required DOM structure * > label > input:radio, plugin may be applied to any of these elements
		###
		tooltip		: ->
			if !this_.length
				return this_
			this_.tooltip
				html		: true
				container	: 'body'
				placement	: 'auto top'
				delay		: 200
		###*
		 * Tooltip with UIkit
		 *
		 * Required title or data-title attribute with some content, optionally support data-pos attribute with desired position of tooltip
		###
		###tooltip		: ->
			if !this_.length
				return this_
			UI	= $.UIkit
			this.each ->
				$this	= $(this)
				if !$this.attr('title')
					$this
						.attr('title', $this.data('title'))
						.attr('data-title', '')
				pos		= $this.data('pos')
				$this
					.attr('data-uk-tooltip', if pos then "{pos:'#{pos}'}" else '')###
		###*
		 * Dialog with Twitter Bootstrap
		 *
		 * Required DOM structure * > *, plugin must be applied to the root element
		 * If child element is not present - content will be automatically wrapped with <div>
		###
		modal		: (mode) ->
			if !this_.length
				return this_
			UI		= $.UIkit
			mode	= mode || 'init'
			this_.each ->
				$this	= $(this)
				if !$this.data('modal')
					$this
						.addClass('uk-modal')
						.data('modal', new UI.modal.Modal($this))
					content	= $this.children()
					if !content.length
						content	= $this
							.wrapInner('<div />')
							.children()
					content
						.addClass('uk-modal-dialog uk-modal-dialog-slide')
					if $this.data('modal-frameless')
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
				modal	= $this.data('modal')
				switch mode
					when 'show' then modal.show()
					when 'hide' then modal.hide()
	return
)(jQuery)