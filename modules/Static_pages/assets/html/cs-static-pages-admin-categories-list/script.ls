/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-static-pages-admin-categories-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		categories	: Array
	ready : !->
		@_reload_categories()
	_reload_categories : !->
		cs.api('get api/Static_pages/admin/categories').then (categories) !~>
			@set('categories', categories)
	_add : !->
		cs.ui.simple_modal("""
			<h3>#{@L.addition_of_page_category}</h3>
			<cs-static-pages-admin-categories-add-edit-form/>
		""").addEventListener('close', @~_reload_categories)
	_edit : (e) !->
		title	= @L.editing_of_page_category(e.model.item.title)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-static-pages-admin-categories-add-edit-form id="#{e.model.item.id}"/>
		""").addEventListener('close', @~_reload_categories)
	_delete : (e) !->
		cs.ui.confirm(@L.sure_to_delete_page_category(e.model.item.title))
			.then -> cs.api('delete api/Static_pages/admin/categories/' + e.model.item.id)
			.then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				@_reload_categories()
	_add_page : !->
		cs.ui.simple_modal("""
			<h3>#{@L.adding_of_page}</h3>
			<cs-static-pages-admin-pages-add-edit-form/>
		""").addEventListener('close', @~_reload_categories)
)
