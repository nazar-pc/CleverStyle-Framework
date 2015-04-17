<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	PHPMailer;

/**
 * @method static Mail instance($check = false)
 */
class Mail extends PHPMailer {
	use Singleton;
	/**
	 * Setting base mail sending parameters according to system configuration
	 */
	function construct () {
		$Config = Config::instance();
		if ($Config->core['smtp']) {
			$this->IsSMTP();
			$this->Host       = $Config->core['smtp_host'];
			$this->Port       = $Config->core['smtp_port'] ?: $Config->core['smtp_secure'] ? 465 : 25;
			$this->SMTPSecure = $Config->core['smtp_secure'];
			if ($Config->core['smtp_auth']) {
				$this->SMTPAuth = true;
				$this->Username = $Config->core['smtp_user'];
				$this->Password = $Config->core['smtp_password'];
			}
		}
		$this->From     = $Config->core['mail_from'];
		$this->FromName = get_core_ml_text('mail_from_name');
		$this->CharSet  = 'utf-8';
		$this->IsHTML();
	}
	/**
	 * Sending of email
	 *
	 * @param array|string|string[]      $email       if emails without names - string (may be several emails separated by comma) or
	 *                                                1-dimensional array(<i>email</i>)<br>
	 *                                                2-dimensional of emails or array(<i>email</i>, <i>name</i>) must be given
	 * @param string                     $subject     Mail subject
	 * @param string                     $body        html body
	 * @param string|null                $body_text   plain text body
	 * @param array|null|string          $attachments 1- or 2-dimensional array of array(<i>path</i>, <i>name</i>) or simply string with path to the file in
	 *                                                file system
	 * @param array|null|string|string[] $reply_to    Similar to <b>$email</b>, but multiple emails are not supported
	 * @param bool|string                $signature   <b>true</b> - add system signature<br>
	 *                                                <b>false</b> - without signature<br>
	 *                                                <b>string</b> - custom signature
	 *
	 * @return bool
	 */
	function send_to ($email, $subject, $body, $body_text = null, $attachments = null, $reply_to = null, $signature = true) {
		if (!$email || !$subject || !$body) {
			return false;
		}
		foreach ($this->normalize_email($email) as $e) {
			call_user_func_array([$this, 'addAddress'], $e);
		}
		foreach ($this->normalize_email($reply_to) as $r) {
			call_user_func_array([$this, 'AddReplyTo'], $r);
		}
		foreach ($this->normalize_attachment($attachments) as $a) {
			call_user_func_array([$this, 'AddAttachment'], $a);
		}
		$this->Subject = $subject;
		$signature     = $this->make_signature($signature);
		$this->Body    = $this->normalize_body($body, $signature);
		if ($body_text) {
			$this->AltBody = $body_text.strip_tags($signature);
		}
		$result = $this->Send();
		$this->ClearAddresses();
		$this->ClearAttachments();
		$this->ClearReplyTos();
		return $result;
	}
	/**
	 * @param array|string|string[] $email
	 *
	 * @return string[][]
	 */
	protected function normalize_email ($email) {
		if (!$email) {
			return [];
		}
		if (!is_array($email)) {
			$email = _trim(explode(',', $email));
		} elseif (!is_array($email[0])) {
			$email = [$email];
		}
		return _array($email);
	}
	protected function make_signature ($signature) {
		if ($signature === true) {
			$signature = get_core_ml_text('mail_signature');
			return $signature ? "<br>$this->LE<br>$this->LE-- $this->LE<br>$signature" : '';
		}
		return $signature ? "<br>$this->LE<br>$this->LE-- $this->LE<br>".xap($signature, true) : '';
	}
	/**
	 * @param string $body
	 * @param string $signature
	 *
	 * @return string
	 */
	protected function normalize_body ($body, $signature) {
		if (strpos($body, '<!doctype') === 0 && strpos($body, '<body') !== false) {
			$body = "<!doctype html>\n$body";
		}
		if (strpos($body, '<html') === false) {
			if (substr($body, 0, 5) != '<body') {
				$body = h::body($body.$signature);
			} else {
				$body = str_replace('</body>', "$signature</body>", $body);
			}
			$body = h::html(
				h::{'head meta'}(
					[
						'content'    => 'text/html; charset=utf-8',
						'http-equiv' => 'Content-Type'
					]
				).
				$body
			);
		} else {
			$body = str_replace('</body>', "$signature</body>", $body);
		}
		return $body;
	}
	/**
	 * @param array|null|string $attachments
	 *
	 * @return array
	 */
	protected function normalize_attachment ($attachments) {
		if (!$attachments) {
			return [];
		}
		if (!is_array($attachments) || !is_array($attachments[0])) {
			$attachments = [$attachments];
		}
		return _array($attachments);
	}
}
