/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language('system_admin_databases_')
Polymer(
	'is'		: 'cs-system-admin-databases-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_databases_')
	]
	properties	:
		add				: Boolean
		database-index	: Number
		mirror-index	: Number
		databases		: Array
		database		:
			type	: Object
			value	:
				mirror		: -1
				host		: ''
				type		: 'MySQLi'
				prefix		: ''
				name		: ''
				user		: ''
				password	: ''
				charset		: ''
		engines			: Array
	ready : !->
		Promise.all([
			$.getJSON('api/System/admin/databases')
			$.ajax(
				url		: 'api/System/admin/databases'
				type	: 'engines'
			)
		]).then ([@databases, @engines]) !~>
			if @add
				if !isNaN(@database-index)
					@set('database.mirror', @database-index)
			else
				@databases.forEach (database) !~>
					if @database-index ~= database.index
						if isNaN(@mirror-index)
							@set('database', database)
						else
							database.mirrors.forEach (mirror) !~>
								if @mirror-index ~= mirror.index
									@set('database', mirror)
	_save	: !->
		$.ajax(
			url		:
				'api/System/admin/databases' +
				(
					if !isNaN(@database-index)
						'/' + @database-index +
						(
							if !isNaN(@mirror-index)
								'/' + @mirror-index
							else
								''
						)
					else
						''
				)
			type	: if @add then 'post' else 'patch'
			data	:
				mirror		: @database.mirror
				host		: @database.host
				type		: @database.type
				prefix		: @database.prefix
				name		: @database.name
				user		: @database.user
				password	: @database.password
				charset		: @database.charset
			success	: !->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
	_db_name : (index, host, name) ->
		if index
			"#host/#name"
		else
			L.core_db
	_test_connection : (e) !->
		$modal	= $(cs.ui.simple_modal("""<div>
			<h3 class="cs-text-center">#{L.test_connection}</h3>
			<progress is="cs-progress" infinite></progress>
		</div>"""))
		$.ajax(
			url		: 'api/System/admin/databases'
			data	: @database
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
