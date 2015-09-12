###*
 * @package    CleverStyle CMS
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
$ ->
	requestAnimationFrame ->
		if !cs.in_admin
			return
		$('#change_active_languages').change ->
			$(@)
				.find("option[value='" + $('#change_language').val() + "']")
				.prop('selected', true)
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
