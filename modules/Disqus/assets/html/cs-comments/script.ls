/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	is			: 'cs-comments'
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
		@__proto__._loaded	= true
		cs.api('get_settings api/Disqus').then ({shortname}) !~>
			script	= document.createElement('script')
				..async	= true
				..src	= "//#shortname.disqus.com/embed.js"
				..setAttribute('data-timestamp', +new Date())
			document.head.appendChild(script)
)
