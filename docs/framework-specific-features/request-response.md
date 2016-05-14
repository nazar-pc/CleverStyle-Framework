Unlike plain simple PHP code, CLeverStyle CMS provides Request/Response abstractions that developers MUST use to ensure everything will work regardless of environment configuration and system setup.

Essential pieces here are [$Request](/docs/backend-system-objects/$Request.md) and [$Response](/docs/backend-system-objects/$Response.md) system objects, they provide interfaces that work with Web/CLI interfaces, support custom request methods (including files uploads) and work with built-in HTTP server when using `Http server` module.

Some common uses of PHP superglobals and functions alongside with their alternatives in CleverStyle CMS are listed below.

#### $_SERVER
Instead of using `$_SERVER` directly, use properties of `$Request` object. It contains all essential information about request you might need from `$_SERVER`, for instance, you can get request headers using `$Request->headers` property or `$Request->header()` method.

#### $_GET
Instead of using `$_GET` directly, use `$Request->query` property or even `$Request->query()` method to get parameters from query string during Web or CLI request.

#### $_POST
Instead of using `$_POST` directly, use `$Request->data` property or even `$Request->data()` method to get request data, in contrast with `$_POST` it supports JSON request and works for any custom request methods.

#### $_COOKIE
Instead of using `$_COOKIE` directly, use `$Request->cookie` property or even `$Request->cookie()` method to get request cookie, in contrast with `$_COOKIE` it will remove system-specific cookie prefix automatically and will also contain cookies set during current response generation.

#### $_FILES
Instead of using `$_FILES` directly, use `$Request->files` property or even `$Request->files()` method to get request data, in contrast with `$_POST` it works for any custom request methods and provides normalized structure when uploading multiple files like `files[]` in contrast with plain `$_FILES`.

#### setcookie()
Instead of using `setcookie()` directly, use `$Response->cookie()` method to set cookie for response, it will also cause setting this cookie in `$Request->cookie` property.

#### header()
Instead of using `header()` directly, use `$Response->header()` method to set response headers or if you need to make redirect, there is a convenient `$Response->redirect()` method.

#### http_response_code()
Instead of using `http_response_code()` directly, set `$Response->code` instead.
