/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
cs.ui.ready.then !->
	promise		= Promise.resolve()
	load_script	= ->
		$.ajax(
			url			: @
			dataType	: 'script'
			cache		: true
		)
	load_import	= ->
		new Promise(Polymer.Base.importHref.bind(@, @))
	preload		= (as, href) !->
		preload	= document.createElement("link")
			..rel	= "preload"
			..as	= as
			..href	= href
		document.head.appendChild(preload)
	for script in cs.optimized_includes?[0] || []
		preload('script', "/#script")
		promise	= promise.then(load_script.bind("/#script"))
	for import_ in cs.optimized_includes?[1] || []
		preload('document', "/#import_")
		promise	= promise.then(load_import.bind("/#import_"))
