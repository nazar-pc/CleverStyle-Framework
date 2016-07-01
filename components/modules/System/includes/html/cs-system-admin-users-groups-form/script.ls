/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-users-groups-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_users_')
	]
	properties	:
		user			: ''
		user_groups		: Array
		other_groups	: Array
	ready : !->
		@_reload()
	_reload : !->
		cs.api([
			'get api/System/admin/groups'
			"get api/System/admin/users/#{@user}/groups"
		]).then ([groups, user_groups_ids]) !~>
			user_groups			= []
			other_groups		= []
			normalized_groups	= {}
			for group, group of groups
				if user_groups_ids.indexOf(group.id) != -1
					normalized_groups[group.id] = group
				else
					other_groups.push(group)
			for group in user_groups_ids
				user_groups.push(normalized_groups[group])
			@user_groups	= user_groups
			@other_groups	= other_groups
			@_init_sortable()
	_init_sortable : !->
		html5sortable <~! require(['html5sortable-no-jquery'], _)
		if (
			@shadowRoot.querySelectorAll('#user-groups > div:not(:first-child)').length < @user_groups.length ||
			@shadowRoot.querySelectorAll('#other-groups > div:not(:first-child)').length < @other_groups.length
		)
			setTimeout(@_init_sortable.bind(@), 100)
			return
		html5sortable(
			@shadowRoot.querySelectorAll('#user-groups, #other-groups')
			connectWith	: 'user-groups-list'
			items		: 'div:not(:first-child)',
			placeholder	: '<div class="cs-block-primary">'
		)[0]
			.addEventListener('sortupdate', !~>
				for element in @shadowRoot.querySelectorAll('#user-groups > div:not(:first-child)')
					element.classList
						..remove('cs-block-warning', 'cs-text-warning')
						..add('cs-block-success', 'cs-text-success')
				for element in @shadowRoot.querySelectorAll('#other-groups > div:not(:first-child)')
					element.classList
						..remove('cs-block-success', 'cs-text-success')
						..add('cs-block-warning', 'cs-text-warning')
			)
	save : !->
		groups = [].map.call(
			@shadowRoot.querySelectorAll('#user-groups > div:not(:first-child)')
			-> it.group
		)
		cs.api("put api/System/admin/users/#{@user}/groups", {groups}).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
