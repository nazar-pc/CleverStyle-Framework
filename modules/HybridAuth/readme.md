HybridAuth library during next update will need patching:
* `\cs\ExitException` instead of `exit`/`die`
* `\cs\Response::instance()->header()` or `\cs\Response::instance()->redirect()` instead of `header()`
* Since HybridAuth catches all exceptions, we need to separate `\cs\ExitException` from being captured by addition in `Hybrid_Endpoint::processAuthStart()` before line
```php
		catch (Exception $e) {
```
line
```php
		catch (\cs\ExitException $e) {}
```
