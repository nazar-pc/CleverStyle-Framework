[RequireJS](https://github.com/jrburke/requirejs) is bundled in CleverStyle CMS out of the box.

All other third-party libraries bundled with system like jQuery or jsSHA are also available as AMD modules through RequireJS.

Besides [Bower & NPM](/docs/Bower-and-NPM.md) integration, RequireJS can be used with CleverStyle CMS components.

For instance, you have module or plugin called `Experiment` and AMD module in `includes/js/some-module.js`, then this module can be conveniently consumed as:

```javascript
require(['Experiment/some-module'], function (some_module) {
	// Do stuff
});
```

So in this case you don't need to specify full path to module like `components/modules/Experiment/includes/js/some-module` or `components/plugins/Experiment/includes/js/some-module`, but instead can use nice short syntax.

RequireJS mappings and Bower/NPM directories can be tweaked using [special event](/docs/%24Page.md#systempagerequirejs).
