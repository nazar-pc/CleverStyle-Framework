/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-system-admin-blocks-form'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_blocks_')
	]
	properties	:
		block		: Object
		index		: Number
		templates	: Array
		types		: Array
	observers	: [
		'_type_change(block.type)'
	]
	ready : !->
		cs.api([
			'types		api/System/admin/blocks'
			'templates	api/System/admin/blocks'
		])
			.then ([@types, @templates]) ~>
				if @index
					cs.api('get api/System/admin/blocks/' + @index).then (block) ~>
						block.type		= block.type || @types[0]
						block.template	= block.template || @templates[0]
						if block.active == undefined
							block.active	= 1
						else
							block.active	= parseInt(block.active)
						block
				else
					active	: 1
					content	: ''
					type	: 'html'
					expire	:
						state	: 0
			.then (@block) !~>
	_type_change : (type) !->
		@shadowRoot
			..querySelector('.html').hidden		= type != 'html'
			..querySelector('.raw_html').hidden	= type != 'raw_html'
	_save : !->
		index	= @index
		method	= if index then 'put' else 'post'
		suffix	= if index then "/#index" else ''
		cs.api("#method api/System/admin/blocks#suffix", @block).then !~>
			cs.ui.notify(@L.changes_saved, 'success', 5)
)
