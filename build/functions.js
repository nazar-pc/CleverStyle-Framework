function change_mode (value) {
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