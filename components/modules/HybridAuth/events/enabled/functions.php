<?php
/**
 * @package        HybridAuth
 * @category       modules
 * @author         HybridAuth authors
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright      HybridAuth authors
 * @license        MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
use
	Hybrid_Auth,
	cs\Cache,
	cs\Config,
	cs\DB,
	cs\Session,
	cs\User;

/**
 * Get HybridAuth instance with current configuration. Strongly recommended instead of direct usage
 *
 * @param null|string $provider
 * @param null|string $base_url
 *
 * @return Hybrid_Auth
 */
function get_hybridauth_instance ($provider = null, $base_url = null) {
	require_once __DIR__.'/../../Hybrid/Auth.php';
	$Config     = Config::instance();
	$User       = User::instance();
	$HybridAuth = new Hybrid_Auth(
		[
			'base_url'  => $base_url ?: $Config->base_url()."/HybridAuth/$provider/endpoint/".md5($provider.Session::instance()->get_id()),
			'providers' => $Config->module('HybridAuth')->providers
		]
	);
	if ($User->user() && current_module() != 'HybridAuth') {
		$HybridAuth->restoreSessionData(serialize($User->get_data('HybridAuth_session')));
	}
	return $HybridAuth;
}
