/**
 * @package    CleverStyle Framework
 * @subpackage DarkEnergy theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
cs.ui.ready.then !->
	document.querySelector('.cs-mobile-menu').addEventListener('click', !->
		if @hasAttribute('show')
			@removeAttribute('show')
		else
			@setAttribute('show', '')
	)
