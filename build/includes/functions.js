/**
 * @package		CleverStyle CMS
 * @subpackage	Builder
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function change_mode (value, item) {
	[].forEach.call(
		document.getElementsByClassName('build-mode'),
		function (item) {
			item.parentNode.className	= '';
		}
	);
	item.parentNode.className	= 'active';
	var	modules	= document.getElementById('modules'),
		plugins	= document.getElementById('plugins');
	switch (value) {
		case 'core':
			modules.setAttribute('multiple', '');
			modules.removeAttribute('disabled');
			plugins.setAttribute('multiple', '');
			plugins.removeAttribute('disabled');
		break;
		case 'module':
			modules.removeAttribute('multiple');
			modules.removeAttribute('disabled');
			modules.selectedIndex	= 0;
			plugins.setAttribute('disabled', '');
		break;
		case 'plugin':
			plugins.removeAttribute('multiple');
			plugins.removeAttribute('disabled');
			plugins.selectedIndex	= 0;
			modules.setAttribute('disabled', '');
		break;
	}
}
window.onload	= function () {
	document.getElementsByClassName('build-mode')[0].parentNode.className	= 'active';
};