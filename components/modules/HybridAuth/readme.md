HybridAuth library during next update will need patching:
* `\ExitException` instead of `exit`/`die`
* `_header()` instead of `header()`, will be possible to avoid if namespaces will be used
* Since HybridAuth catches all exceptions, we need to separate `\ExitException` from being captured by addition in `Hybrid_Endpoint::processAuthStart()` before line
```php
		catch ( Exception $e ) {
```
line
```php
		catch ( \ExitException $e) {}
```
