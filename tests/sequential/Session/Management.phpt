--FILE--
<?php
namespace cs\Session {
	function time ($time = 0) {
		static $stored_time;
		if (!isset($stored_time)) {
			$stored_time = \time();
		}
		if ($time) {
			$stored_time = $time;
		}
		return $stored_time;
	}
}
namespace cs {
  include __DIR__.'/../../bootstrap.php';
  $Config  = Config::instance();
  $Request = Request::instance();
  $Request->init_from_globals();
  $Session = Session::instance();
  $User    = User::instance();

  var_dump('Create session');
  $user_id = $User->registration('sm1@test.com', false, false)['id'];
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());
  $session_id = $Session->add(User::ROOT_ID);
  var_dump($session_id);

  var_dump('Load session from header');
  Session::instance_reset();
  $Session = Session::instance();
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());
  var_dump($Session->get($session_id));

  var_dump('New session without destroying previous');
  var_dump($new_session = $Session->add($user_id, false));
  var_dump($Session->get($session_id));

  var_dump('New session with destroying previous');
  var_dump($Session->add($user_id));
  var_dump($Session->admin(), $Session->user(), $Session->guest());
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->get($session_id));
  var_dump($Session->get($new_session));

  var_dump('Loading existing session manually');
  var_dump($Session->load($session_id));
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());

  var_dump('Check session owner');
  $Config->core['remember_user_ip'] = 1;
  var_dump($Session->is_session_owner($session_id, $Request->header('user-agent'), $Request->remote_addr, $Request->ip));
  var_dump($Session->is_session_owner($session_id, 'Foo', $Request->remote_addr, $Request->ip));
  var_dump($Session->is_session_owner($session_id, $Request->header('user-agent'), '99.99.99.99', $Request->ip));
  var_dump($Session->is_session_owner($session_id, $Request->header('user-agent'), $Request->remote_addr, '99.99.99.99'));
  $Config->core['remember_user_ip'] = 0;
  var_dump($Session->is_session_owner($session_id, 'Foo', $Request->remote_addr, $Request->ip));
  var_dump($Session->is_session_owner($session_id, $Request->header('user-agent'), '99.99.99.99', $Request->ip));
  var_dump($Session->is_session_owner($session_id, $Request->header('user-agent'), $Request->remote_addr, '99.99.99.99'));

  var_dump('Load not specified session (present in cookies)');
  Session::instance_reset();
  $Session = Session::instance();
  var_dump($Session->load());
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());

  var_dump('Load not specified session (not present in cookies)');
  unset($Request->cookie['session']);
  Session::instance_reset();
  $Session = Session::instance();
  var_dump($Session->load());
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());

  var_dump('Loading session with non-existing id');
  var_dump($Session->load('a0000000000000000000000000000000'));
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());

  var_dump('Session prolongation');
  var_dump($Session->add(User::ROOT_ID));
  $session_data = $Session->get(null);
  Session\time(Session\time() + $Config->core['session_expire'] - 3);
  var_dump($Session->load());
  $new_session_data = $Session->get(null);
  var_dump($session_data, $new_session_data, $new_session_data['expire'] > $session_data['expire']);

  var_dump('Add session to non-existing users');
  var_dump($Session->add(0));
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());
  var_dump($Session->add(999999));
  var_dump($Session->get_id());
  var_dump($Session->get_user());
  var_dump($Session->admin(), $Session->user(), $Session->guest());

  var_dump('Add session to inactive user');
  $User->set('status', User::STATUS_INACTIVE, $user_id);
  var_dump($Session->add($user_id));

  var_dump('Add session to not activated user');
  $User->set('status', User::STATUS_NOT_ACTIVATED, $user_id);
  var_dump($Session->add($user_id));

  var_dump('Delete session with incorrect id');
  var_dump($Session->del('foo'));

  var_dump('Delete all sessions for user');
  $session_id = $Session->add(User::ROOT_ID);
  $Session->del_all(User::ROOT_ID);
  var_dump($Session->get($session_id));

  var_dump('Delete all sessions for guest');
  var_dump($Session->del_all(User::GUEST_ID));
}
?>
--EXPECTF--
string(14) "Create session"
bool(false)
int(1)
bool(false)
bool(false)
bool(true)
string(32) "%s"
string(24) "Load session from header"
string(32) "%s"
int(2)
bool(true)
bool(true)
bool(false)
array(7) {
  ["id"]=>
  string(32) "%s"
  ["user"]=>
  int(2)
  ["created"]=>
  int(%d)
  ["expire"]=>
  int(%d)
  ["user_agent"]=>
  string(0) ""
  ["remote_addr"]=>
  string(32) "0000000000000000000000007f000001"
  ["ip"]=>
  string(32) "0000000000000000000000007f000001"
}
string(39) "New session without destroying previous"
string(32) "%s"
array(7) {
  ["id"]=>
  string(32) "%s"
  ["user"]=>
  int(2)
  ["created"]=>
  int(%d)
  ["expire"]=>
  int(%d)
  ["user_agent"]=>
  string(0) ""
  ["remote_addr"]=>
  string(32) "0000000000000000000000007f000001"
  ["ip"]=>
  string(32) "0000000000000000000000007f000001"
}
string(36) "New session with destroying previous"
string(32) "%s"
bool(false)
bool(true)
bool(false)
string(32) "%s"
int(3)
array(7) {
  ["id"]=>
  string(32) "%s"
  ["user"]=>
  int(2)
  ["created"]=>
  int(%d)
  ["expire"]=>
  int(%d)
  ["user_agent"]=>
  string(0) ""
  ["remote_addr"]=>
  string(32) "0000000000000000000000007f000001"
  ["ip"]=>
  string(32) "0000000000000000000000007f000001"
}
bool(false)
string(33) "Loading existing session manually"
int(2)
string(32) "%s"
int(2)
bool(true)
bool(true)
bool(false)
string(19) "Check session owner"
bool(true)
bool(false)
bool(false)
bool(false)
bool(false)
bool(true)
bool(true)
string(47) "Load not specified session (present in cookies)"
int(3)
string(32) "%s"
int(3)
bool(false)
bool(true)
bool(false)
string(51) "Load not specified session (not present in cookies)"
int(1)
string(32) "%s"
int(1)
bool(false)
bool(false)
bool(true)
string(36) "Loading session with non-existing id"
int(1)
string(32) "%s"
int(1)
bool(false)
bool(false)
bool(true)
string(20) "Session prolongation"
string(32) "%s"
int(2)
array(7) {
  ["id"]=>
  string(32) "%s"
  ["user"]=>
  int(2)
  ["created"]=>
  int(%d)
  ["expire"]=>
  int(%d)
  ["user_agent"]=>
  string(0) ""
  ["remote_addr"]=>
  string(32) "0000000000000000000000007f000001"
  ["ip"]=>
  string(32) "0000000000000000000000007f000001"
}
array(7) {
  ["id"]=>
  string(32) "%s"
  ["user"]=>
  int(2)
  ["created"]=>
  int(%d)
  ["expire"]=>
  int(%d)
  ["user_agent"]=>
  string(0) ""
  ["remote_addr"]=>
  string(32) "0000000000000000000000007f000001"
  ["ip"]=>
  string(32) "0000000000000000000000007f000001"
}
bool(true)
string(33) "Add session to non-existing users"
string(32) "%s"
string(32) "%s"
int(1)
bool(false)
bool(false)
bool(true)
string(32) "%s"
string(32) "%s"
int(1)
bool(false)
bool(false)
bool(true)
string(28) "Add session to inactive user"
string(32) "%s"
string(33) "Add session to not activated user"
string(32) "%s"
string(32) "Delete session with incorrect id"
bool(false)
string(28) "Delete all sessions for user"
bool(false)
string(29) "Delete all sessions for guest"
bool(false)
