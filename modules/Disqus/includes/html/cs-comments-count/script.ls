/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
Polymer(
	'is'		: 'cs-comments-count'
	properties	:
		module	: String
		item	: Number
		count	: Number
	ready : !->
		item	= @module + '/' + @item
		cs.api('get_settings api/Disqus').then ({shortname}) !~>
			script	= document.createElement('script')
				..async		= true
				..src		= "//#shortname.disqus.com/count-data.js?1=#item"
				..onload	= !~>
					@count	= DISQUSWIDGETS[item] || 0
				..setAttribute('data-timestamp', +new Date())
			document.head.appendChild(script)
)
