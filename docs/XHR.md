XHR requests are primary done through `$.ajax()` method.

For convenience purposes some defaults of `$.ajax()` were set using `$.ajaxSetup()`:

#### contents.script
`contents.script` is set to `false` by default to avoid interpreting response as JS code.

#### error
Error handler is specified by default and will result in warning notification with error message if error happens.

Also if you want to capture only some specific error code and show default notification for others, you can provide custom `error_{code}` property to `$.ajax()` call:
```javascript
$.ajax({
    ...
    error_404 : function () {
        // custom handler for "404 Not Found" only
    }
});
```
