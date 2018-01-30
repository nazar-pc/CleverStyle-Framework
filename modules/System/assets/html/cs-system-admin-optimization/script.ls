/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
Polymer(
	is			: 'cs-system-admin-optimization'
	behaviors	: [
		cs.Polymer.behaviors.computed_bindings
		cs.Polymer.behaviors.Language('system_admin_optimization_')
		cs.Polymer.behaviors.admin.System.settings
	]
	properties	:
		path_prefix			: ''
		settings_api_url	: 'api/System/admin/optimization'
	_clean_cache : !->
		@_clean_cache_common('clean_cache')
	_clean_public_cache : !->
		@_clean_cache_common('clean_public_cache')
	_clean_cache_common : (method) !->
		modal = cs.ui.simple_modal("""
			<cs-progress infinite><progress></progress></cs-progress>
		""")
		cs.api("#method " + @settings_api_url, {@path_prefix})
			.then !~>
				modal.innerHTML = """<p class="cs-block-success cs-text-success">#{@L.done}</p>"""
			.catch (o) !~>
				clearTimeout(o.timeout)
				modal.innerHTML = """<p class="cs-block-error cs-text-error">#{@L.error}</p>"""
)
