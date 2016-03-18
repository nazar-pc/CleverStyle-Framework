/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
window.WebComponents	= window.WebComponents || {}
window.Polymer			=
	dom				: 'shadow'
	lazyRegister	: true
###
 # Load configuration from special script elements
###
Array::forEach.call(
	document.head.querySelectorAll('.cs-config')
	(config) !->
		target		= config.getAttribute('target').split('.')
		data		= JSON.parse(config.innerHTML)
		destination	= window
		target.forEach (target_part, i) !->
			if target_part != 'window'
				if !destination[target_part]
					destination[target_part]	= {}
				if i < target.length - 1
					destination	:= destination[target_part]
				else
					if data instanceof Object && !(data instanceof Array)
						destination	:= destination[target_part]
						for index, value of data
							destination[index]	= value
					else
						destination[target_part] = data
)
# Correct page URL to include language prefix if necessary (it is necessary if document.baseURI contains language prefix and document.URL doesn't)
if document.URL.indexOf(document.baseURI.substr(0, document.baseURI.length - 1)) != 0
	url_lang = document.baseURI.split('/')[3]
	new_url = location.href.split('/')
	new_url.splice(
		3
		if !new_url[3] then 1 else 0
		url_lang
	)
	new_url = new_url.join('/')
	history.replaceState({}, document.title, new_url)
