/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-about-server'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_about_server_')
	]
	properties	:
		server_config	: Object
	ready : !->
		cs.api('get api/System/admin/about_server').then (@server_config) !~>
)
