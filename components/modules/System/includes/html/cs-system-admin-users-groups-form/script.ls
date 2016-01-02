/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
BOT_GROUP_ID	= 3
L				= cs.Language
Polymer(
	'is'		: 'cs-system-admin-users-groups-form'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		user			: ''
		user_groups		: Array
		other_groups	: Array
	ready : !->
		@_reload()
	_reload : !->
		Promise.all([
			$.getJSON('api/System/admin/groups')
			$.getJSON("api/System/admin/users/#{@user}/groups")
		]).then ([groups, user_groups_ids]) !~>
			user_groups		= []
			other_groups	= []
			console.log user_groups_ids
			for group, group of groups
				if group.id ~= BOT_GROUP_ID
					continue
				if user_groups_ids.indexOf(group.id) != -1
					user_groups.push(group)
				else
					other_groups.push(group)
			@user_groups	= user_groups
			@other_groups	= other_groups
			@_init_sortable()
	_init_sortable : !->
		$shadowRoot	= $(@shadowRoot)
		if (
			$shadowRoot.find('#user-groups > div:not(:first)').length < @user_groups.length ||
			$shadowRoot.find('#other-groups > div:not(:first)').length < @other_groups.length
		)
			setTimeout(@_init_sortable.bind(@), 100)
			return
		$group	= $shadowRoot.find('#user-groups, #other-groups')
		$group
			.sortable(
				connectWith	: 'user-groups-list'
				items		: 'div:not(:first)',
				placeholder	: '<div class="cs-block-primary">'
			)
			.on('sortupdate', !~>
				$(@$['user-groups']).children('div:not(:first)').removeClass('cs-block-warning cs-text-warning').addClass('cs-block-success cs-text-success')
				$(@$['other-groups']).children('div:not(:first)').removeClass('cs-block-success cs-text-success').addClass('cs-block-warning cs-text-warning')
			)
	save : !->
		console.log($(@$['user-groups']).children('div:not(:first)').map(-> @group).get())
		$.ajax(
			url		: "api/System/admin/users/#{@user}/groups"
			data	:
				groups	: $(@$['user-groups']).children('div:not(:first)').map(-> @group).get()
			type	: 'put'
			success	: ->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
