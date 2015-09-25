/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-admin-storages-form'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		add				: Boolean
		storage-index	: Number
		addition		: false
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
		$.when(
			$.getJSON('api/System/admin/storages')
			$.ajax(
				url		: 'api/System/admin/storages'
				type	: 'engines'
			)
		).then ([@storages], [@engines]) !~>
			if !@add
				@storages.forEach (storage) !~>
					if @storage-index ~= storage.index
						@set('storage', storage)
	_save	: !->
		$.ajax(
			url		:
				'api/System/admin/storages' +
				(
					if !@add
						'/' + @storage-index
					else
						''
				)
			type	: if @add then 'post' else 'patch'
			data	:
				url			: @storage.url
				host		: @storage.host
				connection	: @storage.connection
				user		: @storage.user
				password	: @storage.password
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
