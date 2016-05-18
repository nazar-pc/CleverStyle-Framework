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
		$shadowRoot	= $(@shadowRoot)
		if $shadowRoot.find('[group] > div:not(:first)').length < @blocks_count
			setTimeout(@_init_sortable.bind(@), 100)
			return
		$group	= $shadowRoot.find('[group]')
		$group
			.sortable(
				connectWith	: 'blocks-list'
				items		: 'div:not(:first)',
				placeholder	: '<div class="cs-block-primary">'
			)
			.on('sortupdate', !->
				get_indexes	= ->
					$group.filter("[group=#it]").children('div:not(:first)').map(-> @index).get()
				order	=
					top			: get_indexes('top')
					left		: get_indexes('left')
					floating	: get_indexes('floating')
					right		: get_indexes('right')
					bottom		: get_indexes('bottom')
				$.ajax(
					url		: 'api/System/admin/blocks'
					type	: 'update_order'
					data	:
						order	: order
					success	: !->
						cs.ui.notify(L.changes_saved, 'success', 5)
				)
			)
	_status_class : (active) ->
		if active ~= 1 then 'cs-block-success cs-text-success' else 'cs-block-warning cs-text-warning'
	_reload : !->
		blocks <~! $.getJSON('api/System/admin/blocks', _)
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
		$(cs.ui.simple_modal("""
			<h3>#{L.block_addition}</h3>
			<cs-system-admin-blocks-form/>
		""")).on('close', !~>
			@_reload()
		)
	_edit_block : (e) !->
		title	= L.editing_block(e.model.item.title)
		$(cs.ui.simple_modal("""
			<h3>#title</h3>
			<cs-system-admin-blocks-form index="#{e.model.item.index}"/>
		""")).on('close', !~>
			@_reload()
		)
	_delete_block : (e) !->
		title	= L.sure_to_delete_block(e.model.item.title)
		cs.ui.confirm(
			"<h3>#title</h3>"
			!~>
				$.ajax(
					url		: 'api/System/admin/blocks/' + e.model.item.index
					type	: 'delete'
					success	: !~>
						cs.ui.notify(L.changes_saved, 'success', 5)
						@_reload()
				)
		)
)
