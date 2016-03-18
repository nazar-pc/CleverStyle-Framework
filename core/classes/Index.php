<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * @deprecated Use `cs\App` instead
 * @todo       Remove in 4.x
 */
class Index {
	use
		Singleton;

	/**
	 * Getter for `controller_path` property (no other properties supported currently)
	 *
	 * @param string $property
	 *
	 * @return false|string[]
	 */
	function __get ($property) {
		switch ($property) {
			case 'controller_path';
				return App::instance()->controller_path;
		}
		return false;
	}
	/**
	 * Executes plugins processing, blocks and module page generation
	 *
	 * @deprecated use `cs\App::execute()` instead
	 *
	 * @throws ExitException
	 */
	function __finish () {
		App::instance()->execute();
	}
}
