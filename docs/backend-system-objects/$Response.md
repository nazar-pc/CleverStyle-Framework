`$Response` - is system object, that provides unified target for all needed response data, instance can be obtained in such way:
 ```php
 $Response = \cs\Response::instance();
 ```

### [Methods](#methods) [Properties](#properties)

<a name="methods" />
###[Up](#) Methods

`$Response` object has next public method:
* init()
* init_with_typical_default_settings()
* header()
* redirect()
* cookie()
* output_default()
* output_to_psr7()

#### init($body = '', $body_stream = null, $headers = [], $code = 200, $protocol = 'HTTP/1.1') : \cs\Response
Initialize response object with specified data

#### init_with_typical_default_settings() : \cs\Response
Initialize with typical default settings (headers `Content-Type` and `Vary`, protocol taken from `cs\Request::$protocol`)

#### header($field : string, $value : string, $replace = true : bool) : \cs\Response
Set raw HTTP header

#### redirect($location : string, $code = 302 : int) : \cs\Response
Make redirect to specified location

#### cookie($name : string, $value : string, $expire = 0 : int, $httponly = false : bool) : \cs\Response
Function for setting cookies, taking into account cookies prefix. Parameters like in system `setcookie()` function, but `$path`, `$domain` and `$secure` are skipped, they are detected automatically

#### output_default()
Provides default output for all the response data using `header()`, `http_response_code()` and `echo` or `php://output`

#### output_to_psr7()
Provides output to PSR-7 response object

<a name="properties" />
###[Up](#) Properties

`$Response` object has next public properties:
* protocol
* code
* headers
* body
* body_stream

#### protocol
Protocol, for instance: `HTTP/1.0`, `HTTP/1.1` (default), HTTP/2.0

#### code
HTTP status code

#### headers
Headers are normalized to lowercase keys with hyphen as separator, for instance: `connection`, `referer`, `content-type`, `accept-language`

#### body
String body (is used instead of `body_stream` in most cases, ignored if `body_stream` is present)

#### body_stream
Body in form of stream (might be used instead of `body` in some cases, if present, `body` is ignored)
