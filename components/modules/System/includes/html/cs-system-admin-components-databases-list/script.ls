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
	'is'		: 'cs-system-admin-components-databases-list'
	behaviors	: [
		cs.Polymer.behaviors.Language
	]
	ready : !->
		@reload()
	reload : !->
		databases <~! $.getJSON('api/System/admin/databases', _)
		@set('databases', databases)
	_test_connection : (e) !->
		$modal	= $(cs.ui.simple_modal("""<div>
			<h3 class="cs-text-center">#{L.test_connection}</h3>
			<progress is="cs-progress" infinite></progress>
		</div>"""))
		$.ajax(
			url		: 'api/System/admin/databases'
			data	: e.model.database
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
	_add : (e) !->
		database	= e.model && e.model.database
		$(cs.ui.simple_modal("""
			<h3>#{L.addition_of_db}</h3>
			<cs-system-admin-components-databases-form add database-index="#{database && database.index}"/>
		""")).on('close', !~>
			@reload()
		)
	_edit : (e) !->
		# Hack: ugly, but the only way to do it while https://github.com/Polymer/polymer/issues/1865 not resolved
		database_model	= @$.databases_list.modelForElement(e.target)
		database		= e.model.database || database_model.database
		mirror			= e.model.mirror
		name			= @_database_name(database, mirror)
		$(cs.ui.simple_modal("""
			<h3>#{L.editing_the_database(name)}</h3>
			<cs-system-admin-components-databases-form database-index="#{database.index}" mirror-index="#{mirror && mirror.index}"/>
		""")).on('close', !~>
			@reload()
		)
	_database_name : (database, mirror) ->
		if mirror
			master_db_name = do !~>
				for db in @databases
					if db.index ~= database.index
						return "#{db.name} #{db.host}/#{db.type}"
			L.mirror + ' ' + (if database.index then L.db + ' ' + master_db_name else L.core_db) + ", #{mirror.name} #{mirror.host}/#{mirror.type}"
		else
			"#{L.db} #{database.name} #{database.host}/#{database.type}"
	_delete : (e) !->
		# Hack: ugly, but the only way to do it while https://github.com/Polymer/polymer/issues/1865 not resolved
		database_model	= @$.databases_list.modelForElement(e.target)
		database		= e.model.database || database_model.database
		mirror			= e.model.mirror
		name			= @_database_name(database, mirror)
		cs.ui.confirm(
			"#{L.sure_to_delete} #name?"
			!~>
				$.ajax(
					url		:
						'api/System/admin/databases/' + database.index +
						(
							if mirror
								'/' + mirror.index
							else
								''
						)
					type	: 'delete'
					success	: !~>
						cs.ui.notify(L.changes_saved, 'success', 5)
						@reload()
				)
		)
)
