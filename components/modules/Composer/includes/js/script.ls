/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
modal		= null
open_modal	= (action, package_name, category, force = false) ->
	if package_name == 'Composer' && !force
		return
	(new Promise (resolve, reject) !->
		modules	<~! $.getJSON('api/System/admin/modules')
		for module in modules
			if module.name == 'Composer' && module.active != 1
				resolve()
				return
		force	:= if force then 'force' else ''
		modal	:= cs.ui.simple_modal("""<cs-composer action="#action" package="#package_name" category="#category" #force/>""")
		$(modal).on('close', !->
			cs.Event.fire('admin/Composer/canceled')
		)
		cs.Event.once('admin/Composer/updated', !->
			modal.close()
			resolve()
		)
	)
cs.Event
	.on('admin/System/components/modules/install/before', (data) ->
		open_modal('install', data.name, 'modules')
	)
	.on('admin/System/components/modules/uninstall/before', (data) ->
		open_modal('uninstall', data.name, 'modules')
	)
	.on('admin/System/components/modules/update/after', (data) ->
		open_modal('update', data.name, 'modules')
	)
	.on('admin/System/components/plugins/enable/before', (data) ->
		open_modal('install', data.name, 'plugins')
	)
	.on('admin/System/components/plugins/disable/before', (data) ->
		open_modal('uninstall', data.name, 'plugins')
	)
	.on('admin/System/components/plugins/update/after', (data) ->
		open_modal('update', data.name, 'plugins')
	)
$('.cs-composer-admin-force-update').click !->
	open_modal('install', 'Composer', 'modules', true)
