<?php
/**
 * @package   CleverStyle CMS
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

clearstatcache(true);
if (function_exists('opcache_reset')) {
	opcache_reset();
}
