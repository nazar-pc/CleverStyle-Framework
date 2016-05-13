Classes aliases is a convenient feature of modules and plugins.

Every module and plugin can provide some functionality of even few of them. Later, this functionality might be used to refer for classes instead of original component name.

Let's say, we have `Blogs` module and `cs\modules\Blogs\Posts` class.
If `meta.json` of this module contains `"provide" : "superblog",`, then we can also refer mentioned class as `cs\modules\superblog\Posts`.

This feature might be useful if 2 or more components can provide the same functionality and you need to refer somehow classes, provided by component.
Having common functionality in `provide` key of `meta.json` for those components not only allows other components to depend on single functionality and avoid installing 2 components that provides the same functionality, but also to be able to call methods on classes, regardless of what exactly components is currently installed.
