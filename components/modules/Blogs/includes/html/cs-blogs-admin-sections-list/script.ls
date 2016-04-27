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
				@set('sections', @_prepare_sections(sections))
		)
	_prepare_sections : (sections) ->
		sections_normalized	= {}
		sections_parents	= []
		for section in sections
			sections_normalized[section.id] = section
			sections_parents.push(section.parent)
		for section in sections
			if section.parent > 0
				section.title	= sections_normalized[section.parent].title + ' :: ' + section.title
		sections.sort (a, b) ->
			a.title > b.title
)
