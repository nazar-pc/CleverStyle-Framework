<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
rename(DIR.'/components/blocks', DIR.'/blocks');
foreach (get_files_list(DIR.'/components/modules', false, 'd') as $module) {
	rename(DIR."/components/modules/$module", DIR."/modules/$module");
	if (file_exists(DIR."/modules/$module/fs.json")) {
		file_put_contents(
			DIR."/modules/$module/fs.json",
			str_replace(
				'"components/modules/',
				'"modules/',
				file_get_contents(DIR."/modules/$module/fs.json")
			)
		);
	}
}
rmdir_recursive(DIR.'/components/modules');
symlink(DIR.'/blocks', DIR.'/components/blocks');
symlink(DIR.'/modules', DIR.'/components/modules');
Cache::instance()->clean();
