/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
modal		= null
open_modal	= (action, package_name, category, force = false) ->
	if package_name == 'Composer' && !force
		return
	cs.api('get api/System/admin/modules')
		.then (modules) ->
			for module in modules
				if module.name == 'Composer' && module.active != 1
					return
			force	:= if force then 'force' else ''
			modal	:= cs.ui.simple_modal("""<cs-composer action="#action" package="#package_name" #force/>""")
			modal.addEventListener('close', !->
				cs.Event.fire('admin/Composer/canceled')
			)
			new Promise (resolve) !->
				cs.Event.once('admin/Composer/updated', !->
					if !force
						modal.close()
					resolve()
				)
cs.Event
	.on('admin/System/modules/install/before', (data) ->
		open_modal('install', data.name, 'modules')
	)
	.on('admin/System/modules/uninstall/before', (data) ->
		open_modal('uninstall', data.name, 'modules')
	)
	.on('admin/System/modules/update/after', (data) ->
		open_modal('update', data.name, 'modules')
	)
document.querySelector('.cs-composer-admin-force-update')?.addEventListener('click', !->
	open_modal('install', 'Composer', 'modules', true)
)
