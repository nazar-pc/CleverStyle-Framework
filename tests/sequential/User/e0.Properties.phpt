--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Request::instance()->init_from_globals();
$User = User::instance();
var_dump('Prepare user');
$user_id = $User->registration('pr1@test.com', false, true)['id'];
/**
 * @var User\Properties $Properties
 */
$Properties = $User->get($user_id);

var_dump(get_class($Properties));

var_dump('Get username');
var_dump($Properties->get('username'));
var_dump($Properties->username);
var_dump($Properties->username());

var_dump('Set username');
var_dump($Properties->set('username', 'Username 1'));
var_dump($Properties->username);
$Properties->username = 'Username 2';
var_dump($Properties->username);

var_dump('Avatar');
var_dump($Properties->get('avatar'));
var_dump($Properties->avatar);
var_dump($Properties->avatar());
var_dump($Properties->avatar(256));

Config::instance()->core['gravatar_support'] = 1;
var_dump('Avatar (gravatar)');
var_dump($Properties->avatar(256));

var_dump('Set data');
var_dump($Properties->set_data('d1', 'd1'));

var_dump('Get data');
var_dump($Properties->get_data('d1'));

var_dump('Del data');
var_dump($Properties->del_data('d1'));
var_dump($Properties->get_data('d1'));
?>
--EXPECT--
string(12) "Prepare user"
string(18) "cs\User\Properties"
string(12) "Get username"
string(0) ""
string(0) ""
string(3) "pr1"
string(12) "Set username"
bool(true)
string(10) "Username 1"
string(10) "Username 2"
string(6) "Avatar"
string(0) ""
string(0) ""
string(42) "http://cscms.travis/includes/img/guest.svg"
string(42) "http://cscms.travis/includes/img/guest.svg"
string(17) "Avatar (gravatar)"
string(132) "https://www.gravatar.com/avatar/8bcc08ae9406da85668f569be36b62f9?d=mm&s=256&d=http%3A%2F%2Fcscms.travis%2Fincludes%2Fimg%2Fguest.svg"
string(8) "Set data"
bool(true)
string(8) "Get data"
string(2) "d1"
string(8) "Del data"
bool(true)
bool(false)
