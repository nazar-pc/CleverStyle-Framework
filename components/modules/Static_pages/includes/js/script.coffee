###*
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
$ ->
	title	= $('.cs-static-pages-page-title')
	if title.length
		window.onbeforeunload	= ->
			true
	content	= $('.cs-static-pages-page-content')
	$('.cs-static-pages-page-form')
		.parents('form')
		.submit ->
			window.onbeforeunload	= null
			form					= $(@)
			form.append(
				$('<input name="title" hidden/>').val(title.text())
			)
			if !content.is('textarea')
				form.append(
					$('<textarea name="content" hidden/>').val(content.html())
				)
