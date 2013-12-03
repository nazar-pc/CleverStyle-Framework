<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\Text;
use			cs\Config,
			Exception,
			SoapClient;
/**
 * Provides translation functionality based on Bing translator.
 */
class BingTranslate extends _Abstract {
	protected static	$accessToken	= '';
	/**
	 * @static
	 * Text translation from one language to another
	 *
	 * @param string $text Text for translation
	 * @param string $from Language translate from
	 * @param string $to   Language translate to
	 *
	 * @return bool|string Translated string of <b>false</b> if failed
	 */
	static function translate ($text, $from, $to) {
		if (!curl()) {
			return $text;
		}
		if (empty(self::$accessToken)) {
			$settings =  Config::instance()->core['auto_translation_engine'];
			if (!(
				curl() &&
				isset($settings['client_id'], $settings['client_secret']) &&
				$settings['client_id'] &&
				$settings['client_secret']
			)) {
				return false;
			}
			self::$accessToken  = self::getTokens(
				'client_credentials',
				'http://api.microsofttranslator.com',
				$settings['client_id'],
				$settings['client_secret'],
				'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/'
			);
		}
		//Create a streams context.
		$objContext	= stream_context_create([
			'http'   => [
				'header' => 'Authorization: Bearer '.self::$accessToken
			]
		]);
		//Call Soap Client and get translation
		$Soap		= new SoapClient(
			'http://api.microsofttranslator.com/V2/Soap.svc',
			[
				'soap_version'		=> 'SOAP_1_2',
				'encoding'			=> 'UTF-8',
				'exceptions'		=> true,
				'trace'				=> true,
				'cache_wsdl'		=> 'WSDL_CACHE_NONE',
				'stream_context'	=> $objContext,
				'user_agent'		=> 'PHP-SOAP/'.PHP_VERSION."\r\nAuthorization: Bearer ".self::$accessToken
			]
		);
		if (!$Soap) {
			return $text;
		}
		return $Soap->Translate([
			'text'        => $text,
			'from'        => $from,
			'to'          => $to,
			'contentType' => 'text/html',
			'category'    => 'general'
		])->TranslateResult;
	}
	protected static function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
		try {
			//Initialize the Curl Session.
			$ch = curl_init();
			//Create the request Array.
			$paramArr = array (
				'grant_type'    => $grantType,
				'scope'         => $scopeUrl,
				'client_id'     => $clientID,
				'client_secret' => $clientSecret
			);
			//Create an Http Query.//
			$paramArr = http_build_query($paramArr);
			//Set the Curl URL.
			curl_setopt($ch, CURLOPT_URL, $authUrl);
			//Set HTTP POST Request.
			curl_setopt($ch, CURLOPT_POST, TRUE);
			//Set data to POST in HTTP "POST" Operation.
			curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
			//CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
			//CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			//Execute the  cURL session.
			$strResponse = curl_exec($ch);
			//Get the Error Code returned by Curl.
			$curlErrno = curl_errno($ch);
			if($curlErrno){
				$curlError = curl_error($ch);
				throw new Exception($curlError);
			}
			//Close the Curl Session.
			curl_close($ch);
			//Decode the returned JSON string.
			$objResponse = json_decode($strResponse);
			if (property_exists($objResponse, 'error') && $objResponse->error){
				throw new Exception($objResponse->error_description);
			}
			return $objResponse->access_token;
		} catch (Exception $e) {
			trigger_error("Exception-".$e->getMessage());
		}
		return null;
	}
}