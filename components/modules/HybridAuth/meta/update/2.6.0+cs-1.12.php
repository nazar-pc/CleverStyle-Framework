<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
$Config = Config::instance();
unset($Config->components['modules']['HybridAuth']['enable_contacts_detection']);
$Config->save();
