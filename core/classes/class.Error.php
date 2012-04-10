<?php
class Error {//TODO need hard work for constructing of structure and errors processing
	public		$error	= true;
	protected	$init = false,	//For single initialization
				$num	= 0;
	function init () {
		if ($this->init) {
			return;
		}
		$this->init = true;
		global $Error;
		$Error = $this;
		//set_error_handler(array($Error, 'process'));
	}
	function process ($errno, $errstr='', $errfile=true, $errline='') {
		if (!$this->error) {
			return;
		}
		global $L, $Page, $Config;
		if (is_array($errno)) {
			$args = $errno;
			unset($errno);
			$errno		= isset($args[0]) ? $args[0] : '';
			$errstr		= isset($args[1]) ? $args[1] : $errstr;
			$errfile	= isset($args[2]) ? $args[2] : $errfile;
			$errline	= isset($args[3]) ? $args[3] : $errline;
			unset($args);
		}
		if ($errfile && $errline) {
			switch ($errno) {
				case E_USER_ERROR:
				case E_ERROR:
					++$this->num;
					$Page->Title = array($L->fatal.' #'.$errno.': '.$errstr.' '.$L->page_generation_aborted.'...');
					$Page->content(
						'<p><span style="text-transform: uppercase; font-weight: bold;">'.$L->fatal.' #'.$errno.':</span> '.$errstr.' '.$L->on_line.' '.$errline.' '.$L->of_file.' '
						.$errfile.', PHP '.PHP_VERSION.' ('.PHP_OS.")<br>\n"
						.$L->page_generation_aborted."...<br>\n"
						.$L->report_to_admin."<br>\n"
						.(is_object($Config) ?
							($Config->core['admin_mail'] ? $L->admin_mail.': <a href="mailto:'.$Config->core['admin_mail']."\">".$Config->core['admin_mail']."</a><br>\n" : '')
							.($Config->core['admin_phone'] ? $L->admin_phone.': '.$Config->core['admin_phone']."<br>\n" : '')
						: '').'<br>'
					);
					global $stop;
					$stop = 2;
					__finish();
				break;
				
				case E_USER_WARNING:
				case E_WARNING:
					$Page->content(
						'<span style="text-transform: uppercase; font-weight: bold;">'.$L->error.' #'.$errno.':</span> '.$errstr.' '.$L->on_line.' '.$errline.' '.$L->of_file.' '
						.$errfile.', PHP '.PHP_VERSION.' ('.PHP_OS.")<br>"
						.$L->report_to_admin."<br>\n"
						.(is_object($Config) ?
							($Config->core['admin_mail'] ? $L->admin_mail.': <a href="mailto:'.$Config->core['admin_mail']."\">".$Config->core['admin_mail']."</a><br>\n" : '')
							.($Config->core['admin_phone'] ? $L->admin_phone.': '.$Config->core['admin_phone']."<br>\n" : '')
						: '').'<br>'
					);
				break;
				
				case E_USER_NOTICE:
				case E_NOTICE:
					$Page->content(
						'<span style="text-transform: uppercase; font-weight: bold;">'.$L->warning.' #'.$errno.':</span> '.$errstr.' '.$L->on_line.' '.$errline.' '.$L->of_file.' '
						.$errfile.', PHP '.PHP_VERSION.' ('.PHP_OS.")<br><br>\n"
					);
				break;
				
				default:
					$Page->content(
						'<span style="text-transform: uppercase; font-weight: bold;">'.$L->error.':</span> '.$errstr.' '.$L->on_line.' '.$errline.' '.$L->of_file.' '
						.$errfile.', PHP '.PHP_VERSION.' ('.PHP_OS.")<br><br>\n"
					);
				break;
			}
		} else {
			if ($errstr == 'stop') {
				$Page->Title = array($Page->Title[0], $L->fatal.' '.$L->page_generation_aborted.'...');
				$Page->Content = '<h2 align="center"><span style="text-transform: uppercase; font-weight: bold;">'.$L->error.':</span> '.$errno."<br></h2>\n";
				global $stop;
				$stop = 2;
				__finish();
			} else {
				$Page->content('<span style="text-transform: uppercase; font-weight: bold;">'.$L->error.':</span> '.$errno."<br>\n");
			}
		}
	}
	protected function log ($text) {
	}
	protected function mail ($text) {
	}
	function num () {
        return $this->num;
    }
	function page ($page = false) {
		if ($page === false) {
			if (defined('ERROR_PAGE')) {
				$page = ERROR_PAGE;
			} else {
				return;
			}
		}
		global $Page;
		$Page->error($page);
	}
	/*function __call ($func, $args) {//TODO Is it necessary?
		$this->process($args);
	}*/
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}