/**
 * @package    CleverStyle CMS
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
addEventListener('load', !->
	labels		= document.querySelectorAll('label')
	modules		= document.querySelector("[name='modules[]']")
	plugins		= document.querySelector("[name='plugins[]']")
	themes		= document.querySelector("[name='themes[]']")
	document.querySelector('nav')?.addEventListener('click', (e) !->
		if !e.target.matches('input')
			return
		for label in labels
			if label.querySelector("[value=#{e.target.value}]")
				label.classList.add('active')
			else
				label.classList.remove('active')
		[modules.disabled, plugins.disabled, themes.disabled] = switch (e.target.value)
			| 'core'	=> [false, false, false]
			| 'module'	=> [false, true, true]
			| 'plugin'	=> [true, false, true]
			| 'theme'	=> [true, true, false]
	)
	if !document.querySelector('nav > label.active')
		document.querySelector('nav > label')?.click()
)
