###*
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
###
###
 # Load configuration from special template elements
###
[].forEach.call(
	document.head.querySelectorAll('.cs-config')
	(config) ->
		target		= config.getAttribute('target').split('.')
		data		= JSON.parse(config.innerHTML)
		destination	= window
		target.forEach (target_part, i) ->
			if target_part != 'window'
				if !destination[target_part]
					destination[target_part]	= {}
				if i < target.length - 1
					destination	= destination[target_part]
				else
					if data instanceof Object && !(data instanceof Array)
						destination	= destination[target_part]
						for index, value of data
							destination[index]	= value
					else
						destination[target_part] = data
			return
		return
)
L	= cs.Language
for own key, translation of L
	L[key]		= (do (translation) ->
		result	= ->
			vsprintf(translation, Array::slice.call(arguments))
		result.toString	= ->
			translation
		result
	)
L.get		= (key) ->
	L[key].toString()
L.format	= (key) ->
	vsprintf(L[key].toString(), Array::slice.call(arguments, 1))
