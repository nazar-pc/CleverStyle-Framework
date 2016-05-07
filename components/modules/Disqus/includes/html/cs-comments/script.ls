/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-comments'
	properties	:
		module		: String
		item		: Number
	ready : !->
		@_disqus_configure()
		@_disqus_load()
	_disqus_configure : !->
		instance	= @
		div			= document.createElement('div')
			..id		= 'disqus_thread'
			..hidden	= true
		document.body.appendChild(div)
		ready_callback	= !~>
			if div.parentNode != @shadowRoot
				@shadowRoot.appendChild(div)
				div.hidden	= false
		config = !->
			@page.identifier	= instance.module + '/' + instance.item
			@callbacks.onReady.push(ready_callback)
			# Hack: Disable this experiment, otherwise Disqus will fail by putting `document` into `window.getComputedStyle()` call under Shadow DOM polyfill
			@experiment.enable_scroll_container = !window.WebComponents?.flags
		if window.DISQUS
			DISQUS.reset(
				reload	: true
				config	: config
			)
		else
			window.disqus_config	= config
	_disqus_load : !->
		# Comments block
		if @_loaded
			return
		# TODO: __proto__ might be used here, but shitty IE10 doesn't support it
		Object.getPrototypeOf(@)_loaded	= true
		$.ajax(
			url		: 'api/Disqus'
			type	: 'get_settings'
			success	: ({shortname}) !~>
				script	= document.createElement('script')
					..async	= true
					..src	= "//#shortname.disqus.com/embed.js"
					..setAttribute('data-timestamp', +new Date())
				document.head.appendChild(script)
		)
)
