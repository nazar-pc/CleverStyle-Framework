/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L	= cs.Language
Polymer(
	'is'		: 'cs-system-admin-components-blocks-form'
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
		$.when(
			$.ajax(
				url		: 'api/System/admin/blocks'
				type	: 'types'
			)
			$.ajax(
				url		: 'api/System/admin/blocks'
				type	: 'templates'
			)
		).done ([@types], [@templates]) ~>
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
		# Since TinyMCE doesn't work inside ShadowDOM yet, we need to move it into regular DOM, and then insert it in right place with <content> element
		# Double wrapping is also because of TinyMCE doesn't handle it nicely otherwise
		editor	= @shadowRoot.querySelector('.EDITOR')
		$(editor).after('<content select=".editor-container"/>')
		$('<div class="editor-container"><div></div></div>')
			.appendTo(@)
			.children()
			.append(editor)
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
