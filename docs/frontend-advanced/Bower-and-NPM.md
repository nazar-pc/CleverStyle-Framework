[Bower](http://bower.io/) and [NPM](https://www.npmjs.com/) are popular package managers for frontend and backend (though, NPM might be used for frontend packages as well).

CleverStyle Framework provides integration for these package managers.

Each package installed using either Bower or NPM will be check for AMD module presence. If one is found - it will be available using just package name on frontend using RequireJS.

For instance, lets assume you've installed `d3` package using Bower and`lodash` package using NPM. In this case any time you need `d3` and/or `lodash` you can call it using RequireJS:

```javascript
require(['d3', 'lodash'], function (d3, _) {
    // Do stuff
});
```

Also `Composer assets` module provides additional support for Bower and NPM packages through packages dependencies.
