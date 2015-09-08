/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
L = cs.Language
Polymer(
	'is'		: 'cs-system-admin-components-blocks-list'
	behaviors	: [cs.Polymer.behaviors.Language]
	properties	:
		blocks			: Object
		blocks_count	: Number
		positions		:
			notify	: true
			type	: String
	ready : !->
		blocks <~! $.getJSON('api/System/admin/blocks', _)
		@blocks_count	= blocks.length
		blocks_grouped	=
			top			: []
			left		: []
			floating	: []
			right		: []
			bottom		: []
		for block, index in blocks
			block.index	= index
			blocks_grouped[block.position].push(block)
		@set('blocks', blocks_grouped)
		@_init_sortable()
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
			.on('sortupdate', !~>
				get_indexes	= ->
					$group.filter("[group=#it]").children('div:not(:first)').map(-> @index).get()
				@positions = JSON.stringify(
					top			: get_indexes('top')
					left		: get_indexes('left')
					floating	: get_indexes('floating')
					right		: get_indexes('right')
					bottom		: get_indexes('bottom')
				)
			)
	_status_class : (active) ->
		if active ~= 1 then 'cs-block-success cs-text-success' else 'cs-block-warning cs-text-warning'
	_block_permissions : (e) !->
		title	= cs.Language.permissions_for_block(e.model.item.title)
		cs.ui.simple_modal("""
			<h2>#title</h2>
			<cs-system-admin-permissions-for-item label="#{e.model.item.index}" group="Block"/>
		""")
)
