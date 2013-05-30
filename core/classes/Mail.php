<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
class Mail extends \PHPMailer {
	/**
	 * Setting base mail sending parameters according to system configuration
	 */
	function __construct () {
		global $Config;
		if (is_object($Config)) {
			if ($Config->core['smtp']) {
				$this->Mailer		= 'smtp';
				$this->Host			= $Config->core['smtp_host'];
				$this->Port			= $Config->core['smtp_port'] ?: $Config->core['smtp_secure'] ? 465 : 25;
				$this->SMTPSecure	= $Config->core['smtp_secure'];
				if ($Config->core['smtp_auth']) {
					$this->SMTPAuth	= true;
					$this->Username	= $Config->core['smtp_user'];
					$this->Password	= $Config->core['smtp_password'];
				}
			}
		}
		$this->From		= $Config->core['mail_from'];
		$this->FromName	= get_core_ml_text('mail_from_name');
		$this->CharSet	= 'utf-8';
		$this->IsHTML();
	}
	/**
	 * Sending of email
	 *
	 * @param array|string|string[]			$email			if emails without names - string (may be several emails separated by comma) or
	 * 														1-dimensional array(<i>email</i>)<br>
	 * 														else - 2-dimensional array(<i>email</i>, <i>name</i>) must be given
	 * @param string						$subject		Mail subject
	 * @param string						$body			html body
	 * @param string|null					$body_text		plain text body
	 * @param array|null|string				$attachments	1- or 2-dimensional array of array(<i>path</i>, <i>name</i>) or simply string
	 * 														with path to the file in file system
	 * @param array|null|string|string[]	$reply_to		Similar to <b>$email</b>
	 * @param bool|string					$signature		<b>true</b> - add system signature<br>
	 * 														<b>false</b> - without signature<br>
	 * 														<b>string</b> - custom signature
	 * @return bool
	 */
	function send_to ($email, $subject, $body, $body_text = null, $attachments = null, $reply_to = null, $signature = true) {
		if (empty($email) || empty($subject) || empty($body)) {
			return false;
		}
		if (is_array($email)) {
			if (count($email) == 2) {
				$this->AddAddress($email[0], $email[1]);
			} else {
				foreach ($email as $m) {
					if (is_array($m)) {
						$this->AddAddress($m[0], $m[1]);
					} else {
						$this->AddAddress($m);
					}
				}
			}
		} else {
			$email	= _trim(explode(',', $email));
			foreach ($email as $e) {
				$this->AddAddress($e);
			}
			unset($e, $email);
		}
		$this->Subject = $subject;
		if ($signature === true) {
			if ($signature = get_core_ml_text('mail_signature')) {
				$signature = $this->LE.'-- '.$this->LE.$signature;
			}
		} elseif ($signature) {
			$signature = $this->LE.'-- '.$this->LE.xap($signature, true);
		} else {
			$signature = '';
		}
		if (substr($body, 0, 5) != '<html') {
			if (substr($body, 0, 5) != '<body') {
				$body = h::body($body.$signature);
			} else {
				$body = str_replace('</body>', $signature.'</body>', $body);
			}
			$body = h::html(
				h::{'head meta'}([
					'content'		=> 'text/html; charset=utf-8',
					'http-equiv'	=> 'Content-Type'
				]).
				$body
			);
		} else {
			$body = str_replace('</body>', $signature.'</body>', $body);
		}
		$this->Body = $body;
		if ($body_text) {
			$this->AltBody = $body_text.strip_tags($signature);
		}
		if (is_array($attachments)) {
			if (count($attachments) == 2) {
				$this->AddStringAttachment($attachments[0], $attachments[1]);
			} else {
				foreach ($attachments as $a) {
					if (is_array($a)) {
						$this->AddStringAttachment($a[0], $a[1]);
					} else {
						$this->AddStringAttachment($a, pathinfo($a, PATHINFO_FILENAME));
					}
				}
			}
		} elseif (is_string($attachments)) {
			$this->AddStringAttachment($attachments, pathinfo($attachments, PATHINFO_FILENAME));
		}
		if (is_array($reply_to)) {
			if (count($reply_to) == 2) {
				$this->AddReplyTo($reply_to[0], $reply_to[1]);
			} else {
				foreach ($reply_to as $r) {
					if (is_array($r)) {
						$this->AddReplyTo($r[0], $r[1]);
					} else {
						$this->AddReplyTo($r);
					}
				}
			}
		} elseif (is_string($reply_to)) {
			$this->AddReplyTo($reply_to);
		}
		$result = $this->Send();
		$this->ClearAddresses();
		$this->ClearAttachments();
		$this->ClearReplyTos();
		return $result;
	}
}
/**
 * For IDE
 */
if (false) {
	global $Mail;
	$Mail = new Mail;
}