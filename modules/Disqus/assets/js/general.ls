/**
 * @package  Disqus
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
window.DISQUSWIDGETS	=
	displayCount : (data) !->
		if data.counts[0]
			@[data.counts[0].id] = data.counts[0].comments
