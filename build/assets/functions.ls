/**
 * @package    CleverStyle Framework
 * @subpackage Builder
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
addEventListener('load', !->
	modules		= document.querySelector("[name='modules[]']")
	themes		= document.querySelector("[name='themes[]']")
	document.querySelector('nav')?.addEventListener('click', (e) !->
		if !e.target.matches('input')
			return
		[modules.disabled, themes.disabled] = switch (e.target.value)
			| 'core'	=> [false, false]
			| 'module'	=> [false, true]
			| 'theme'	=> [true, false]
	)
)
