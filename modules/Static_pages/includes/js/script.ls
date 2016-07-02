/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
html_to_node	= (html) ->
	div				= document.createElement('div')
	div.innerHTML	= html
	div.firstChild
title	= document.querySelector('.cs-static-pages-page-title')
if title
	window.onbeforeunload	= -> true
content	= document.querySelector('.cs-static-pages-page-content')
form	= document.querySelector('.cs-static-pages-page-form')
if !form
	return
while !form.matches('form')
	form	= form.parentElement
form.addEventListener('submit', !->
	window.onbeforeunload	= null
	title_input				= html_to_node('<input name="title" hidden>')
	title_input.value		= title.textContent
	@appendChild(title_input)
	if !content.matches('textarea')
		content_textarea		= html_to_node('<textarea name="content" hidden></textarea>')
		content_textarea.value	= content.innerHTML
		@append(content_textarea)
)
