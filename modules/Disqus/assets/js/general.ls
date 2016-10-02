/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
window.DISQUSWIDGETS	=
	displayCount : (data) !->
		if data.counts[0]
			@[data.counts[0].id] = data.counts[0].comments
