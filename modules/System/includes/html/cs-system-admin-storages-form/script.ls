/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language('system_admin_storages_')
Polymer(
	'is'		: 'cs-system-admin-storages-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_storages_')
	]
	properties	:
		add				: Boolean
		storage-index	: Number
		storages		: Array
		storage		:
			type	: Object
			value	:
				url			: ''
				host		: ''
				connection	: 'Local'
				user		: ''
				password	: ''
		engines			: Array
	ready : !->
		cs.api([
			'get		api/System/admin/storages'
			'engines	api/System/admin/storages'
		]).then ([@storages, @engines]) !~>
			if !@add
				@storages.forEach (storage) !~>
					if @storage-index ~= storage.index
						@set('storage', storage)
	_save	: !->
		method	= if @add then 'post' else 'patch'
		suffix	=
			if !@add
				'/' + @storage-index
			else
				''
		cs.api("#method api/System/admin/storages#suffix", @storage).then !->
			cs.ui.notify(L.changes_saved, 'success', 5)
	_test_connection : (e) !->
		$modal	= cs.ui.simple_modal("""<div>
			<h3 class="cs-text-center">#{L.test_connection}</h3>
			<progress is="cs-progress" infinite></progress>
		</div>""")
		cs.api('test api/System/admin/storages', @storage)
			.then !->
				modal.querySelector('progress').outerHTML	= """
					<p class="cs-text-center cs-block-success cs-text-success" style=text-transform:capitalize;">#{L.success}</p>
				"""
			.catch (o) !->
				clearTimeout(o.timeout)
				modal.querySelector('progress').outerHTML	= """
					<p class="cs-text-center cs-block-error cs-text-error" style=text-transform:capitalize;">#{L.failed}</p>
				"""
)
