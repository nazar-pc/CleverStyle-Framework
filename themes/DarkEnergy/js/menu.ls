/**
 * @package    CleverStyle Framework
 * @subpackage DarkEnergy theme
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
cs.ui.ready.then !->
	document.querySelector('.cs-mobile-menu').addEventListener('click', !->
		if @hasAttribute('show')
			@removeAttribute('show')
		else
			@setAttribute('show', '')
	)
