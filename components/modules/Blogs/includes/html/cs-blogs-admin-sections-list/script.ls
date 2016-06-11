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
		cs.api('get api/Blogs/admin/sections').then (sections) !~>
			@set('sections', sections)
	_add : !->
		cs.ui.simple_modal("""
			<h3>#{@L.addition_of_posts_section}</h3>
			<cs-blogs-admin-sections-add-edit-form/>
		""").addEventListener('close', @~_reload_sections)
	_edit : (e) !->
		title	= @L.editing_of_posts_section(e.model.item.title)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-blogs-admin-sections-add-edit-form id="#{e.model.item.id}"/>
		""").addEventListener('close', @~_reload_sections)
	_delete : (e) !->
		cs.ui.confirm(@L.sure_to_delete_posts_section(e.model.item.title))
			.then -> cs.api('delete api/Blogs/admin/sections/' + e.model.item.id)
			.then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				@_reload_sections()
)
