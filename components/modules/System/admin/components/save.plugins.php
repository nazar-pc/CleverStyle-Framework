<?php
/**
 * Provides next triggers:<br>
 *  admin/System/components/plugins/enable<br>
 *  ['name'	=> <i>plugin_name</i>]<br>
 *  admin/System/components/plugins/disable<br>
 *  ['name'	=> <i>plugin_name</i>]
 */
global $Config, $Index, $Core;
$rc			= $Config->routing['current'];
$plugins	= get_files_list(PLUGINS, false, 'd');
if (isset($_POST['mode'], $_POST['plugin'])) {
	switch ($_POST['mode']) {
		case 'enable':
			if (!in_array($_POST['plugin'], $Config->components['plugins']) && in_array($_POST['plugin'], $plugins)) {
				$Config->components['plugins'][] = $_POST['plugin'];
				$Index->save('components');
				$Core->run_trigger(
					'admin/System/components/plugins/enable',
					[
						'name' => $_POST['plugin']
					]
				);
			}
		break;
		case 'disable':
			if (in_array($_POST['plugin'], $Config->components['plugins'])) {
				foreach ($Config->components['plugins'] as $i => $plugin) {
					if ($plugin == $_POST['plugin'] || !in_array($_POST['plugin'], $plugins)) {
						unset($Config->components['plugins'][$i], $i, $plugin);
						break;
					}
				}
				unset($i, $plugin);
				$Index->save('components');
				$Core->run_trigger(
					'admin/System/components/plugins/disable',
					[
						'name' => $_POST['plugin']
					]
				);
			}
		break;
	}
}