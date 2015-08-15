###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
get_active_class	= (is_active) -> if is_active then 'uk-active' else ''
L					= cs.Language
Polymer(
	'is'				: 'cs-system-admin-components-blocks-form'
	behaviors			: [cs.Polymer.behaviors.Language]
	properties			:
		tooltip_animation	:'{animation:true,delay:200}'
	ready				: ->
		json = JSON.parse(@querySelector('script').textContent)
		json.block_data.type		= json.block_data.type || json.types[0]
		json.block_data.template	= json.block_data.template || json.templates[0]
		if json.block_data.active == undefined
			json.block_data.active	= 1
		else
			json.block_data.active	= parseInt(json.block_data.active)
		@json						= json
		$(@shadowRoot).find('textarea').val(@json.block_data.content)
		# Since TinyMCE doesn't work inside ShadowDOM yet, we need to move it into regular DOM, and then insert it in right place with <content> element
		# Double wrapping is also because of TinyMCE doesn't handle it nicely otherwise
		editor	= @shadowRoot.querySelector('.EDITOR')
		$(editor).after('<content select=".editor-container"/>')
		$('<div class="editor-container"><div></div></div>')
			.appendTo(@)
			.children()
			.append(editor)
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds			: (target) ->
		$(target)
			.cs().tooltips_inside()
			.cs().radio_buttons_inside()
			.cs().connect_to_parent_form()
	type_change			: ->
		type = @shadowRoot.querySelector("[name='block[type]']").value
		$(@shadowRoot).find('.html, .raw_html').prop('hidden', true).filter('.' + type).prop('hidden', false)
)
