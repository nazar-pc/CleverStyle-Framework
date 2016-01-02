/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-admin-blocks-form'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		block		: Object
		index		: Number
		templates	: Array
		types		: Array
	observers	: [
		'_type_change(block.type)'
	]
	ready : !->
		Promise.all([
			$.ajax(
				url		: 'api/System/admin/blocks'
				type	: 'types'
			)
			$.ajax(
				url		: 'api/System/admin/blocks'
				type	: 'templates'
			)
		]).then ([@types, @templates]) ~>
		if @index
			block <~! $.getJSON('api/System/admin/blocks/' + @index, _)
			block.type		= block.type || @types[0]
			block.template	= block.template || @templates[0]
			if block.active == void
				block.active	= 1
			else
				block.active	= parseInt(block.active)
			@block	= block
		else
			@block	=
				active	: 1
				content	: ''
				type	: 'html'
				expire	:
					state	: 0
	_type_change : (type) !->
		$(@shadowRoot).find('.html, .raw_html').prop('hidden', true).filter('.' + type).prop('hidden', false)
	_save : !->
		index	= @index
		$.ajax(
			url		: 'api/System/admin/blocks' + (if index then "/#index" else '')
			type	: if index then 'put' else 'post'
			data	: @block
			success	: ->
				cs.ui.notify(L.changes_saved, 'success', 5)
		)
)
