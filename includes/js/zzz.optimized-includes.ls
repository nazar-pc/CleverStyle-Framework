/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
if !cs.optimized_includes
	return
original_ready	= cs.ui.ready
cs.ui.ready		= (new Promise (resolve) !->
	content_loaded	= !->
		# Wait for last import to load, which is usually faster than document load event
		imports	= document.querySelectorAll('link[rel=import]:not([async]')
		imports[imports.length - 1].addEventListener('load', resolve)
	switch document.readyState
	| 'complete'	=> resolve()
	| 'interactive'	=> content_loaded()
	| otherwise		=> addEventListener('DOMContentLoaded', content_loaded)
)
	.then ->
		load_script	= ->
			new Promise (resolve, reject) !~>
				script	= document.createElement("script")
					..async		= true
					..src		= @
					..onload	= resolve
					..onerror	= reject
				document.head.appendChild(script)
		load_import	= ->
			new Promise(Polymer.Base.importHref.bind(@, @))
		preload		= (as, href) !->
			preload	= document.createElement("link")
				..rel	= "preload"
				..as	= as
				..href	= href
			document.head.appendChild(preload)
		promise		= Promise.resolve()
		for script in cs.optimized_includes[0]
			preload('script', script)
			promise	:= promise.then(load_script.bind(script))
		for import_ in cs.optimized_includes[1]
			preload('document', import_)
			promise	:= promise.then(load_import.bind(import_))
		promise
	.then -> original_ready
