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
		destination	= window
		target.forEach (target_part) ->
			if target_part != 'window'
				if !destination[target_part]
					destination[target_part]	= {}
				destination	= destination[target_part]
			return
		data	= JSON.parse(
			config.innerHTML.substring(4, config.innerHTML.length - 3).replace('-  ', '-', 'g')
		)
		for index, value of data
			destination[index]	= value
		return
)
