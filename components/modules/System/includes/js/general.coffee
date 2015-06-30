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
		$('.cs-permissions-invert').click ->
			$(@)
				.parentsUntil('div')
				.find(':radio:not(:checked)[value!=-1]')
				.prop('checked', true)
				.change()
		$('.cs-permissions-allow-all').click ->
			$(@)
				.parentsUntil('div')
				.find(':radio[value=1]')
				.prop('checked', true)
				.change()
		$('.cs-permissions-deny-all').click ->
			$(@)
				.parentsUntil('div')
				.find(':radio[value=0]')
				.prop('checked', true)
				.change()
		$('#cs-users-search-columns li').click ->
			$this = $(@)
			if $this.hasClass('uk-button-primary')
				$this.removeClass('uk-button-primary')
			else
				$this.addClass('uk-button-primary')
			$('#cs-users-search-selected-columns').val(
				$this.parent().children('.uk-button-primary')
					.map ->
						$.trim(@.innerHTML)
					.get()
					.join(';')
			)
		$('.cs-blocks-permissions').click ->
			$block	= $(@).closest('[data-index]')
			index	= $block.data('index')
			title	= cs.Language.permissions_for_block(
				$block.data('block-title')
			)
			$.cs.simple_modal("""
				<h2>#{title}</h2>
				<cs-system-admin-permissions-for-item label="#{index}" group="Block"/>
			""")
		$('#cs-top-blocks-items, #cs-left-blocks-items, #cs-floating-blocks-items, #cs-right-blocks-items, #cs-bottom-blocks-items')
			.sortable
				connectWith	: '.cs-blocks-items'
				items		: 'li:not(:first)'
			.on(
				'sortupdate'
				->
					$('#cs-blocks-position').val(
						JSON.stringify(
							top			: $('#cs-top-blocks-items li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
							left		: $('#cs-left-blocks-items li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
							floating	: $('#cs-floating-blocks-items li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
							right		: $('#cs-right-blocks-items li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
							bottom		: $('#cs-bottom-blocks-items li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
						)
					)
			)
		$('#cs-users-groups-list, #cs-users-groups-list-selected')
			.sortable
				connectWith	: '#cs-users-groups-list, #cs-users-groups-list-selected'
				items		: 'li:not(:first)'
			.on(
				'sortupdate'
				->
					$('#cs-users-groups-list')
						.find('.uk-alert-success')
						.removeClass('uk-alert-success')
						.addClass('uk-alert-warning')
					selected	= $('#cs-users-groups-list-selected')
					selected
						.find('.uk-alert-warning')
						.removeClass('uk-alert-warning')
						.addClass('uk-alert-success')
					$('#cs-user-groups').val(
						JSON.stringify(
							selected
								.children('li:not(:first)')
								.map ->
									$(@).data('id')
								.get()
						)
					)
			)
		return
