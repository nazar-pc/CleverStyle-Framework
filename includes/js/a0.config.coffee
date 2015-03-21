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
		data		= JSON.parse(
			config.innerHTML.substring(4, config.innerHTML.length - 3).replace('-  ', '-', 'g')
		)
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
