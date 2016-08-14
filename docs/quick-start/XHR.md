XHR requests are primary done through `cs.api()` method.

There are few ways to use this function.

#### cs.api(method_path : string, data : object) : Promise
`method_path` is whitespace-separated HTTP method and path. `data` is optional object, that can be plain key-value object, `HTMLFormElement` node or `FormData` instance.

Examples:
```javascript
cs.api('get api/System/languages')
cs.api('post api/Articles', document.querySelector('#form'))
````

Promise will be resolved with decoded JSON response or rejected with object `{timeout: timeout, xhr: xhr}`.
`timeout` can be used to stop default error handler (user notification) with `clearTimeout(result.timeout)`, `xhr` can be used to get necessary data from response to handle error properly.

Example of error suppression (user will not see error notification):
```javascript
cs.api('get api/System/languages')
    .then(function (result) {
        // Do stuff
    })
    .catch(function (result) {
        clearTimeout(result.timeout);
    });
```
#### cs.api(methods_paths : string[]) : Promise
Similar to previous, but will resolve with array of results or will be rejected completely. Data cannot be passed in this format.

Example:
```javascript
cs.api([
    'get_settings api/Blogs',
    'get          api/System/profile'
]).then(function ([settings, user_profile]) {
	// Do stuff
});
```
