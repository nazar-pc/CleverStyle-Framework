###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ($=jQuery) ->
	helpers	=
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
