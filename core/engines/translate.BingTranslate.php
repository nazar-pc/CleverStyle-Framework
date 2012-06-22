<?php
/**
 * Provides translation functionality based on Bing translator.
 * Require configuration variable $BING_TRANSLATOR = array('client_id' => *, 'client_secret' => *)
 */
class BingTranslate extends TranslateAbstract {
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
		if (empty(self::$accessToken)) {
			global $BING_TRANSLATOR;
			if (!(
				is_array($BING_TRANSLATOR) &&
				isset($BING_TRANSLATOR['client_id'], $BING_TRANSLATOR['client_secret']) &&
				$BING_TRANSLATOR['client_id'] &&
				$BING_TRANSLATOR['client_secret']
			)) {
				return false;
			}
			self::$accessToken  = self::getTokens(
				'client_credentials',
				'http://api.microsofttranslator.com',
				$BING_TRANSLATOR['client_id'],
				$BING_TRANSLATOR['client_secret'],
				'https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/'
			);
		}
		//Create a streams context.
		$objContext = stream_context_create([
			'http'   => [
				'header' => 'Authorization: Bearer '.self::$accessToken
			]
		]);
		//Call Soap Client and get translation
		return @(new SoapClient(
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
		))->Translate([
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

			if ($objResponse->error){
				throw new Exception($objResponse->error_description);
			}
			return $objResponse->access_token;
		} catch (Exception $e) {
			trigger_error("Exception-".$e->getMessage());
		}
	}
}