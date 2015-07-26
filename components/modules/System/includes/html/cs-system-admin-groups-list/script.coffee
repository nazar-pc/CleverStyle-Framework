###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L	= cs.Language
Polymer(
	tooltip_animation	:'{animation:true,delay:200}'
	L					: L
	groups				: []
	created				: ->
		@groups	= JSON.parse(@querySelector('script').innerHTML)
	domReady			: ->
		$(@shadowRoot).cs().tooltips_inside()
	edit_permissions	: (event, detail, sender) ->
		$sender	= $(sender)
		index	= $sender.closest('[data-group-index]').data('group-index')
		group	= @groups[index]
		title	= L.permissions_for_group(group.title)
		$.cs.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-permissions-for group="#{group.id}" for="group"/>
		""")
)
