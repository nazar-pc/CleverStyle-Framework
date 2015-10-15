/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language
Polymer(
	'is'		: 'cs-system-admin-languages'
	# TODO: everything below can be likely decoupled into behavior and reused elsewhere, new property with basic path would be only needed instead
	behaviors	: [
		cs.Polymer.behaviors.Language
	]
	properties	:
		settings	: Object
	ready : !->
		@reload()
	reload : !->
		$.ajax(
			url		: 'api/System/admin/languages'
			type	: 'get_settings'
			success	: (settings) !~>
				@set('settings', settings)
		)
	_apply : !->
		$.ajax(
			url		: 'api/System/admin/languages'
			type	: 'apply_settings'
			data	: @settings
			success	: !~>
				@reload()
				cs.ui.notify(L.changes_applied + L.check_applied, 'warning', 5)
		)
	_save : !->
		$.ajax(
			url		: 'api/System/admin/languages'
			type	: 'save_settings'
			data	: @settings
			success	: !~>
				@reload()
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
	_cancel : !->
		$.ajax(
			url		: 'api/System/admin/languages'
			type	: 'cancel_settings'
			success	: !~>
				@reload()
				cs.ui.notify(L.changes_canceled, 'success', 5)
		)
)
