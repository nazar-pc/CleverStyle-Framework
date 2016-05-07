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
		$.ajax(
			url		: 'api/Disqus'
			type	: 'get_settings'
			success	: ({shortname}) !~>
				<~! $.getScript("//#shortname.disqus.com/count-data.js?1=#item")
				@count	= DISQUSWIDGETS[item]
		)
)
