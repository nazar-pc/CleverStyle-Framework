###*
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	if !cs.composer
		return
	cs.composer.button	=
		$('input[type=hidden]')
			.filter('[name=module], [name=plugin]')
			.parent()
			.find('button[type=submit]')
			.on('click.cs-composer', ->
				cs.composer.modal = $.cs.simple_modal('<cs-composer></cs-composer>', false, '90vw')
				false
			)
