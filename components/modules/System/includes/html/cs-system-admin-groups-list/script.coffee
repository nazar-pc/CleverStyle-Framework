###*
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L				= cs.Language
ADMIN_GROUP_ID	= 1
USER_GROUP_ID	= 2
BOT_GROUP_ID	= 3
Polymer(
	'is'				: 'cs-system-admin-groups-list'
	behaviors			: [cs.Polymer.behaviors.Language]
	properties			:
		tooltip_animation	:'{animation:true,delay:200}'
		groups				: []
	ready				: ->
		@reload()
		@workarounds(@shadowRoot)
		cs.observe_inserts_on(@shadowRoot, @workarounds)
	workarounds			: (target) ->
		$(target).cs().tooltips_inside()
	reload				: ->
		$.getJSON('api/System/admin/groups', (groups) =>
			groups.forEach (group) ->
				group.allow_to_delete	= `group.id != ADMIN_GROUP_ID && group.id != USER_GROUP_ID && group.id != BOT_GROUP_ID`
			@groups	= groups
		)
	add_group			: ->
		$.cs.simple_modal("""
			<h3>#{L.adding_a_group}</h3>
			<cs-system-admin-groups-form/>
		""").on(
			'hide.uk.modal'
			=>
				@reload()
		)
	edit_group			: (e) ->
		group	= e.model.group
		$.cs.simple_modal("""
			<h3>#{L.editing_of_group(group.title)}</h3>
			<cs-system-admin-groups-form group_id="#{group.id}" group_title="#{cs.prepare_attr_value(group.title)}" description="#{cs.prepare_attr_value(group.description)}"/>
		""").on(
			'hide.uk.modal'
			=>
				@reload()
		)
	delete_group		: (e) ->
		group	= e.model.group
		UIkit.modal.confirm(
			"""
				<h3>#{L.sure_delete_group(group.title)}</h3>
			"""
			=>
				$.ajax(
					url		: 'api/System/admin/groups/' + group.id
					type	: 'delete'
					success	: =>
						UIkit.notify(L.changes_saved.toString(), 'success')
						@groups.splice(e.model.index, 1)
				)
		)
	edit_permissions	: (e) ->
		group	= e.model.group
		title	= L.permissions_for_group(group.title)
		$.cs.simple_modal("""
			<h2>#{title}</h2>
			<cs-system-admin-permissions-for group="#{group.id}" for="group"/>
		""")
)
