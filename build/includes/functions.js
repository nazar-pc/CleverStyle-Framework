/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function change_mode (value, item) {
	[].forEach.call(
		document.querySelectorAll('.build-mode'),
		function (item) {
			item.parentNode.className	= '';
		}
	);
	item.parentNode.className	= 'active';
	var	modules	= document.querySelector('#modules'),
		plugins	= document.querySelector('#plugins'),
		themes	= document.querySelector('#themes');
	switch (value) {
		case 'core':
			modules.removeAttribute('disabled');
			plugins.removeAttribute('disabled');
			themes.removeAttribute('disabled');
		break;
		case 'module':
			modules.removeAttribute('disabled');
			modules.selectedIndex	= 0;
			plugins.setAttribute('disabled', '');
			themes.setAttribute('disabled', '');
		break;
		case 'plugin':
			plugins.removeAttribute('disabled');
			plugins.selectedIndex	= 0;
			modules.setAttribute('disabled', '');
			themes.setAttribute('disabled', '');
		break;
		case 'theme':
			themes.removeAttribute('disabled');
			themes.selectedIndex	= 0;
			modules.setAttribute('disabled', '');
			plugins.setAttribute('disabled', '');
		break;
	}
}
window.onload	= function () {
	document.getElementsByClassName('build-mode')[0].parentNode.className	= 'active';
	[].forEach.call(
		document.querySelectorAll('select'),
		function (item) {
			item.removeAttribute('disabled');
		}
	);
};
