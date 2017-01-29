/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-static-pages-page'
	behaviors	: [
		cs.Polymer.behaviors.Language('static_pages_')
	]
	properties	:
		admin			: Boolean
		editing			: false
		inline_editor	: document.createElement('cs-editor-inline') !== HTMLElement
		page			: Object
		original_title	: String
	_edit : !->
		cs.api('get api/Static_pages/admin/pages/' + @id).then (@page) !~>
			@editing	= true
	_save : !->
		@set('page.title', @$.title.textContent)
		cs.api('put api/Static_pages/admin/pages/' + @id, @page).then(location~reload)
)
