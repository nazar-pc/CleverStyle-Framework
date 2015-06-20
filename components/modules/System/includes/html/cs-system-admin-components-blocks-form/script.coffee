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
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	created				: ->
		json = JSON.parse(@querySelector('script').innerHTML)
		json.block_data.type		= json.block_data.type || json.types[0]
		json.block_data.template	= json.block_data.template || json.templates[0]
		if json.block_data.active == undefined
			json.block_data.active	= 1
		@active_yes_class			= get_active_class(json.block_data.active)
		@active_no_class			= get_active_class(!json.block_data.active)
		@expire_never_class			= get_active_class(!json.block_data.expire.state)
		@expire_as_specified_class	= get_active_class(json.block_data.expire.state)
		@json						= json
	ready				: ->
		$(@shadowRoot).find('textarea').val(@json.block_data.content)
	domReady			: ->
		$(@shadowRoot)
			.cs().tooltips_inside()
			.cs().radio_buttons_inside()
			.cs().connect_to_parent_form()
		# Since TinyMCE doesn't work inside ShadowDOM yet, we need to move it into regular DOM, and then insert it in right place with <content> element
		# Double wrapping is also because of TinyMCE doesn't handle it nicely otherwise
		$(@shadowRoot.querySelector('.EDITOR'))
			.after('<content select=".editor-container"/>')
			.appendTo(@)
			.wrap('<div class="editor-container"/>')
			.wrap('<div/>')
	type_change			: ->
		type = @shadowRoot.querySelector("[name='block[type]']").value
		$(@shadowRoot).find('.html, .raw_html').prop('hidden', true).filter('.' + type).prop('hidden', false)
);
