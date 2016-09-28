/**
 * @package   Static pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-static-pages-admin-pages-list'
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
)
