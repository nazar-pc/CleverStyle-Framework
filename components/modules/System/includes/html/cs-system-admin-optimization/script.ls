/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language('system_admin_optimization_')
Polymer(
	'is'		: 'cs-system-admin-optimization'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_optimization_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		path_prefix			: ''
		settings_api_url	: 'api/System/admin/optimization'
	_clean_cache : !->
		@_clean_cache_common('clean_cache')
	_clean_pcache : !->
		@_clean_cache_common('clean_pcache')
	_clean_cache_common : (method) !->
		modal = cs.ui.simple_modal("""
			<progress is="cs-progress" infinite></progress>
		""")
		$.ajax(
			url		: @settings_api_url
			type	: method
			data	:
				path_prefix	: @path_prefix
			success	: !->
				modal.innerHTML = """
					<p class="cs-block-success cs-text-success">#{L.done}</p>
				"""
			error	: !->
				modal.innerHTML = """
					<p class="cs-block-error cs-text-error">#{L.error}</p>
				"""
		)
)
