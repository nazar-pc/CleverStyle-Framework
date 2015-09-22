/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L				= cs.Language
Polymer(
	'is'		: 'cs-system-admin-components-storages-list'
	behaviors	: [
		cs.Polymer.behaviors.Language
	]
	ready : !->
		@reload()
	reload : !->
		storages <~! $.getJSON('api/System/admin/storages', _)
		@set('storages', storages)
	_test_connection : (e) !->
		$modal	= $(cs.ui.simple_modal("""<div>
			<h3 class="cs-text-center">#{L.test_connection}</h3>
			<progress is="cs-progress" infinite></progress>
		</div>"""))
		$.ajax(
			url		: 'api/System/admin/storages'
			data	: e.model.storage
			type	: 'test'
			success	: (result) !->
				$modal
					.find('progress')
					.replaceWith("""<p class="cs-text-center cs-block-success cs-text-success" style=text-transform:capitalize;">#{L.success}</p>""")
			error	: !->
				$modal
					.find('progress')
					.replaceWith("""<p class="cs-text-center cs-block-error cs-text-error" style=text-transform:capitalize;">#{L.failed}</p>""")
		)

)
