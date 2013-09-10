###*
 * @package		UIkit Helper
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ($=jQuery) ->
	helpers	=
		###*
		 * Radio buttons with UIkit
		 *
		 * Required DOM structure * > label > input:radio, plugin may be applied to any of these elements
		###
		radio			: ->
			if !this.length
				return this
			collection	= []
			this.each ->
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
		checkbox		: ->
			if !this.length
				return this
			collection	= []
			this.each ->
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
		tabs			: ->
			if !this.length
				return this
			UI	= $.UIkit
			this.each ->
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
		 * Tooltip with UIkit
		 *
		 * Required title or data-title attribute with some content, optionally support data-pos attribute with desired position of tooltip
		###
		tooltip		: ->
			if !this.length
				return this
			UI	= $.UIkit
			this.each ->
				$this	= $(this)
				if !$this.attr('title')
					$this
						.attr('title', $this.data('title'))
						.attr('data-title', '')
				pos		= $this.data('pos')
				$this
					.data(
						'tooltip'
						new UI.tooltip(
							$this
							UI.Utils.options(
								pos			: if pos then pos else 'top'
								animation	: true
								delay		: 200
							)
						)
					)
		###*
		 * Dialog with UIkit
		 *
		 * Required DOM structure * > *, plugin must be applied to the root element
		 * If child element is not present - content will be automatically wrapped with <div>
		###
		modal			: (mode) ->
			if !this.length
				return this
			UI		= $.UIkit
			mode	= mode || 'init'
			this.each ->
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
	###*
	 * cs helper registration or running (if no parameters specified)
	 *
	 * @param {string}		name
	 * @param {function}	helper
	###
	$.fn.cs	= (name, helper) ->
		if name && helper
			helpers[name]	= helper
			return this
		public_helpers		= {}
		this_				= this
		public_helpers[key]	= (do (method) ->
			-> method.apply this_, arguments
		) for own key, method of helpers
		public_helpers
	return