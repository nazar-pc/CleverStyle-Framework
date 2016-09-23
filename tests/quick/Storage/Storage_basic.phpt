--FILE--
<?php
namespace cs\Storage {
	include __DIR__.'/../../unit.php';
	class Fake extends _Abstract {
		/**
		 * @var bool
		 */
		public static $connected_fake = true;
		public function __construct ($base_url, $host, $user = '', $password = '') {
			$this->base_url  = $base_url;
			$this->connected = Fake::$connected_fake;
		}
		public function get_files_list (
			$dir,
			$mask = false,
			$mode = 'f',
			$prefix_path = false,
			$subfolders = false,
			$sort = false,
			$exclusion = false,
			$system_files = false,
			$apply = null,
			$limit = null
		) {
		}
		public function file ($filename, $flags = null) { }
		public function file_get_contents ($filename, $flags = null) { }
		public function file_put_contents ($filename, $data, $flags = null) { }
		public function copy ($source, $dest) { }
		public function unlink ($filename) { }
		public function file_exists ($filename) { }
		public function rename ($oldname, $newname) { }
		public function mkdir ($pathname, $mode = 0777, $recursive = false) { }
		public function rmdir ($dirname) { }
		public function is_file ($filename) { }
		public function is_dir ($filename) { }
		public function url_by_source ($source) { }
		public function source_by_url ($url) { }
	}
}
namespace cs {
	function trigger_error ($error) {
	}

	Config::instance_stub(
		[
			'storage' => [
				1 => [
					'driver'   => 'Fake',
					'url'      => 'http://cscms.travis/storage/public',
					'host'     => 'localhost',
					'user'     => 'user',
					'password' => 'storage 1'
				]
			]
		]
	);
	Core::instance_stub(
		[
			'storage_driver'   => 'Fake',
			'storage_url'      => 'http://cscms.travis/storage/public',
			'storage_host'     => 'localhost',
			'storage_user'     => 'user',
			'storage_password' => 'storage 0'
		]
	);

	var_dump('Get storage instance');
	$Storage  = Storage::instance();
	$instance = $Storage->storage(0);
	var_dump($instance instanceof Storage\Fake);

	var_dump('Get same storage instance');
	var_dump($Storage->storage(0) === $instance);

	var_dump('Connections list (active, successful, failed)');
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_ACTIVE));
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_SUCCESSFUL));
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_FAILED));

	var_dump('Connection fails');
	Storage\Fake::$connected_fake = false;
	Storage::instance_reset();
	$Storage  = Storage::instance();
	$instance = $Storage->storage(1);
	var_dump($instance instanceof False_class);

	var_dump('Get failed instance again');
	Storage\Fake::$connected_fake = false;
	Storage::instance_reset();
	var_dump($Storage->storage(1) === $instance);

	var_dump('Connections list (active, successful, failed)');
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_ACTIVE));
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_SUCCESSFUL));
	var_dump($Storage->get_connections_list(Storage::CONNECTIONS_FAILED));
}
?>
--EXPECTF--
string(20) "Get storage instance"
bool(true)
string(25) "Get same storage instance"
bool(true)
string(45) "Connections list (active, successful, failed)"
array(1) {
  [0]=>
  object(cs\Storage\Fake)#%d (%d) {
    ["connected":protected]=>
    bool(true)
    ["base_url":protected]=>
    string(34) "http://cscms.travis/storage/public"
  }
}
array(1) {
  [0]=>
  string(16) "0/localhost/Fake"
}
array(0) {
}
string(16) "Connection fails"
bool(true)
string(25) "Get failed instance again"
bool(true)
string(45) "Connections list (active, successful, failed)"
array(0) {
}
array(0) {
}
array(1) {
  [1]=>
  string(16) "1/localhost/Fake"
}
