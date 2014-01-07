###*
 * @package		Discus
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
do ->
	if !window.disqus_shortname
		return;
	qs			= (s) ->
		document.querySelector(s)
	container	= qs('head') || qs('body')
	# Comments count
	d = window.DISQUSWIDGETS;
	load_counts = (i) ->
		item	= window.disqus_count_items[i]
		if !window.disqus_count_items.length || !item
			window.DISQUSWIDGETS = d
			return
		s			= document.createElement('script')
		s.async		= true
		s.src		= '//' + disqus_shortname + '.disqus.com/count-data.js?q=1&0=1,' + item
		s.onload	= ->
			load_counts(i + 1)
		window.DISQUSWIDGETS	=
			displayCount : (d) ->
				element	= qs('.cs-disqus-comments-count[data-identifier=\'' + item + '\']')
				if element
					element.outerHTML = d.counts[0].comments
		container.appendChild(s)
	load_counts(0)
	# Comments block
	if !window.disqus_identifier
		return
	disqus_title	= qs('meta[property=\'og:title\']')
	disqus_title	= if disqus_title then disqus_title.content else qs('title').text
	disqus_url		= qs('link[rel=canonical]')
	disqus_url		= if disqus_url then disqus_url.href else window.location.href
	dsq				= document.createElement('script')
	dsq.async		= true
	dsq.src			= '//' + disqus_shortname + '.disqus.com/embed.js'
	container.appendChild(dsq)
	return
