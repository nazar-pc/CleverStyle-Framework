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
		engines			: Array
	ready : !->
		cs.api([
			'get		api/System/admin/databases'
			'engines	api/System/admin/databases'
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
		method	= if @add then 'post' else 'patch'
		suffix	=
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
		cs.api("#method api/System/admin/databases#suffix", @database).then !->
			cs.ui.notify(L.changes_saved, 'success', 5)
	_db_name : (index, host, name) ->
		if index
			"#host/#name"
		else
			L.core_db
	_test_connection : (e) !->
		modal	= cs.ui.simple_modal("""<div>
			<h3 class="cs-text-center">#{L.test_connection}</h3>
			<progress is="cs-progress" infinite></progress>
		</div>""")
		cs.api('test api/System/admin/databases', @database)
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
