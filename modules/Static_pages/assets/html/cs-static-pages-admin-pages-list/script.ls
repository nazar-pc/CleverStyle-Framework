/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-static-pages-admin-pages-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		category	: Number
		pages		: Array
	observers	: [
		'_reload_pages(category)'
	]
	_reload_pages : !->
		cs.api("get api/Static_pages/admin/categories/#{@category}/pages").then (pages) !~>
			@set('pages', pages)
	_add : !->
		cs.ui.simple_modal("""
			<h3>#{@L.adding_of_page}</h3>
			<cs-static-pages-admin-pages-add-edit-form category="#{@category}"/>
		""").addEventListener('close', @~_reload_pages)
	_edit : (e) !->
		title	= @L.editing_of_page(e.model.item.title)
		cs.ui.simple_modal("""
			<h2>#{title}</h2>
			<cs-static-pages-admin-pages-add-edit-form id="#{e.model.item.id}"/>
		""").addEventListener('close', @~_reload_pages)
	_delete : (e) !->
		cs.ui.confirm(@L.sure_to_delete_page(e.model.item.title))
			.then -> cs.api('delete api/Static_pages/admin/pages/' + e.model.item.id)
			.then !~>
				cs.ui.notify(@L.changes_saved, 'success', 5)
				@_reload_pages()
)
