/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-blogs-admin-sections-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('blogs_')
	]
	properties	:
		sections	: Array
	ready : !->
		@_reload_sections()
	_reload_sections : !->
		$.ajax(
			url		: 'api/Blogs/admin/sections'
			type	: 'get'
			success	: (sections) !~>
				@set('sections', sections)
		)
	_add : !->
		$(cs.ui.simple_modal("""
			<h3>#{@L.addition_of_posts_section}</h3>
			<cs-blogs-admin-sections-add-edit-form/>
		""")).on('close', @~_reload_sections)
	_edit : (e) !->
		title	= @L.editing_of_posts_section(e.model.item.title)
		$(cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-blogs-admin-sections-add-edit-form id="#{e.model.item.id}"/>
		""")).on('close', @~_reload_sections)
	_delete : (e) !->
		cs.ui.confirm(
			@L.sure_to_delete_posts_section(e.model.item.title)
			!~>
				$.ajax(
					url		: 'api/Blogs/admin/sections/' + e.model.item.id
					type	: 'delete'
					success	: !~>
						cs.ui.notify(@L.changes_saved, 'success', 5)
						@_reload_sections()
				)
		)
)
