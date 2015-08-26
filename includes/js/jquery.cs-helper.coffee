###*
 * @package		UIkit Helper
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ($=jQuery, UI = UIkit) ->
	helpers	=
		###*
		 * Dialog with UIkit
		 *
		 * Required DOM structure * > *, plugin must be applied to the root element
		 * If child element is not present - content will be automatically wrapped with <div>
		###
		modal					: (mode) ->
			if !@.length
				return @
			mode	= mode || 'init'
			@map ->
				$this	= $(@)
				if $this.hasClass('uk-modal-dialog')
					$this	= $this.wrap('<div/>').parent()
				if !$this.data('modal')
					content	= $this.children()
					if !content.length
						content	= $this
							.wrapInner('<div/>')
							.children()
					content
						.addClass('uk-modal-dialog')
					if $this.is('[data-modal-frameless]')
						content
							.addClass('uk-modal-dialog-frameless')
					if $this.attr('title')
						$('<h3/>')
							.html($this.attr('title'))
							.prependTo(content)
					if content.attr('title')
						$('<h3/>')
							.html(content.attr('title'))
							.prependTo(content)
					$this
						.addClass('uk-modal')
						.data('modal', UI.modal($this))
				modal	= $this.data('modal')
				switch mode
					when 'show' then modal.show()
					when 'hide' then modal.hide()
				$this.get()
		###*
		 * Enabling dynamic pagination inside ShadowDOM, should be called on element.shadowRoot
		###
		pagination_inside	: ->
			@find('[data-uk-pagination]').add(@filter('[data-uk-pagination]')).each ->
				UI.pagination(@, UI.Utils.options($(@).attr('data-uk-pagination')))
			@
		###*
		 * Connecting form elements in ShadowDOM to form element higher in DOM tree, should be called on element.shadowRoot
		###
		connect_to_parent_form	: ->
			@each ->
				# Hack: If ShadowDOM was emulated - we are fine already, this is necessary only for native ShadowDOM
				if WebComponents.flags.shadow
					return
				element	= @
				loop
					if element.tagName == 'FORM'
						$form	= $(element)
						$form.one(
							'submit'
							(e) =>
								e.preventDefault()
								e.stopImmediatePropagation()
								$(@).find('[name]').each ->
									$this	= $(@)
									if @type == 'file'
										$this.clone(true, true).insertAfter($this.hide())
										$this.appendTo($form)
									else
										if (@type == 'checkbox' || @type == 'radio') && !$this.is(':checked')
											return
										$form.append(
											$('<input type="hidden"/>')
												.attr('name', @name)
												.val($this.val())
										)
								$form.submit()
						)
						break
					element	= element.host || element.parentNode
					if !element
						break
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
