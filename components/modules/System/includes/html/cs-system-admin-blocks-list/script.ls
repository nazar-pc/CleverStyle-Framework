/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language('system_admin_blocks_')
Polymer(
	'is'		: 'cs-system-admin-blocks-list'
	behaviors	: [
		cs.Polymer.behaviors.Language('system_admin_blocks_')
	]
	properties	:
		blocks			: Object
		blocks_count	: Number
	ready : !->
		@_reload()
	_init_sortable : !->
		html5sortable <~! require(['html5sortable'], _)
		if @blocks_count == undefined || @shadowRoot.querySelectorAll('[group] > div:not(:first-child)').length < @blocks_count
			setTimeout(@~_init_sortable, 100)
			return
		group	= @shadowRoot.querySelectorAll('[group]')
		html5sortable(
			@shadowRoot.querySelectorAll('[group]')
			connectWith	: 'blocks-list'
			items		: 'div:not(:first-child)',
			placeholder	: '<div class="cs-block-primary"/>'
		)[0]
			.addEventListener('sortupdate', !~>
				get_indexes	= (group) ~>
					[].map.call(
						@shadowRoot.querySelectorAll("[group=#group] > div:not(:first-child)")
						-> it.index
					)
				order	=
					top			: get_indexes('top')
					left		: get_indexes('left')
					floating	: get_indexes('floating')
					right		: get_indexes('right')
					bottom		: get_indexes('bottom')
				cs.api('update_order api/System/admin/blocks', {order}).then !->
					cs.ui.notify(L.changes_saved, 'success', 5)
			)
	_status_class : (active) ->
		if active ~= 1 then 'cs-block-success cs-text-success' else 'cs-block-warning cs-text-warning'
	_reload : !->
		cs.api('get api/System/admin/blocks').then (blocks) !~>
			@blocks_count	= blocks.length
			blocks_grouped	=
				top			: []
				left		: []
				floating	: []
				right		: []
				bottom		: []
			for block, index in blocks
				blocks_grouped[block.position].push(block)
			@set('blocks', blocks_grouped)
			@_init_sortable()
	_block_permissions : (e) !->
		title	= L.permissions_for_block(e.model.item.title)
		cs.ui.simple_modal("""
			<h3>#title</h3>
			<cs-system-admin-permissions-for-item label="#{e.model.item.index}" group="Block"/>
		""")
	_add_block : !->
		cs.ui.simple_modal("""
			<h3>#{L.block_addition}</h3>
			<cs-system-admin-blocks-form/>
		""").addEventListener('close', @~_reload)
	_edit_block : (e) !->
		title	= L.editing_block(e.model.item.title)
		cs.ui.simple_modal("""
			<h3>#title</h3>
			<cs-system-admin-blocks-form index="#{e.model.item.index}"/>
		""").addEventListener('close', @~_reload)
	_delete_block : (e) !->
		cs.ui.confirm(L.sure_to_delete_block(e.model.item.title))
			.then -> cs.api('delete api/System/admin/blocks/' + e.model.item.index)
			.then !~>
				cs.ui.notify(L.changes_saved, 'success', 5)
				@_reload()
)
