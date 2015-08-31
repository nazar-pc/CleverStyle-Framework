###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
$ ->
	requestAnimationFrame ->
		if !cs.in_admin
			return
		$('.cs-reload-button').click ->
			location.reload()
		$('#change_active_languages').change ->
			$(@)
				.find("option[value='" + $('#change_language').val() + "']")
				.prop('selected', true)
		$('.cs-blocks-permissions').click ->
			$block	= $(@).closest('[data-index]')
			index	= $block.data('index')
			title	= cs.Language.permissions_for_block(
				$block.data('block-title')
			)
			cs.ui.simple_modal("""
				<h2>#{title}</h2>
				<cs-system-admin-permissions-for-item label="#{index}" group="Block"/>
			""")
		$('#cs-top-blocks-items, #cs-left-blocks-items, #cs-floating-blocks-items, #cs-right-blocks-items, #cs-bottom-blocks-items')
			.sortable
				connectWith	: '.cs-blocks-items'
				items		: 'li:not(:first)',
				placeholder	: '<li class="cs-block-primary">'
			.on(
				'sortupdate'
				->
					$('#cs-blocks-position').val(
						JSON.stringify(
							top			: $('#cs-top-blocks-items li:not(:first)').map(-> @dataset.id).get()
							left		: $('#cs-left-blocks-items li:not(:first)').map(-> @dataset.id).get()
							floating	: $('#cs-floating-blocks-items li:not(:first)').map(-> @dataset.id).get()
							right		: $('#cs-right-blocks-items li:not(:first)').map(-> @dataset.id).get()
							bottom		: $('#cs-bottom-blocks-items li:not(:first)').map(-> @dataset.id).get()
						)
					)
			)
		$('#cs-users-groups-list, #cs-users-groups-list-selected')
			.sortable
				connectWith	: '#cs-users-groups-list, #cs-users-groups-list-selected'
				items		: 'li:not(:first)'
				placeholder	: '<li class="cs-block-primary">'
			.on(
				'sortupdate'
				->
					$('#cs-users-groups-list')
						.find('.cs-block-success')
						.removeClass('cs-block-success cs-text-success')
						.addClass('cs-block-warning cs-text-warning')
					selected	= $('#cs-users-groups-list-selected')
					selected
						.find('.cs-block-warning')
						.removeClass('cs-block-warning cs-text-warning')
						.addClass('cs-block-success cs-text-success')
					$('#cs-user-groups').val(
						JSON.stringify(
							selected
								.children('li:not(:first)')
								.map(-> @dataset.id)
								.get()
						)
					)
			)
		return
