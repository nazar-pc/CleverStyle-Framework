System classes extension can be done in two ways:
* first allows complete replacement of original class by alternative implementation, such operation can be done only once, and maybe used for creation of custom distributive
* second allows incremental patching of system class (or class from previous case) multiple times, this allows to patch system classes by third-party components and mostly avoid conflicts between different patches

Limitation: this patching applies only for classes that implement `cs\Singleton` trait and are used as objects (so, you can patch even other components's classes). For static classes you can put file with alternative class definition in `custom` directory.

### Complete replacement
In order to replace class with custom implementation you need to declare the same class as original, but put it inside namespace `cs\custom` instead of `cs` where all system classes are.

Practically, since namespaces are different, you can extend system class and modify it, if this suits, it is even better, because smooth upgrade process between versions.

Customized class should be placed directly inside `custom` directory with any name, but it is better to use original class name, use `_` instead of `\` in case of nested namespaces:
* class `cs\Core` from `core/classes/Core.php` to be placed as `custom/Core.php`
* class `cs\Page\Meta` from `core/classes/Page/Meta.php` to be placed as `custom/Page_Meta.php`

Incremental patching if there will be any will be applied to this, custom class instead original, so, everything should work smoothly with third-party components.

WARNING: custom class should keep interface of existing public methods for compatibility reasons. Implementation can be different, but interface should be the same.

### Incremental patching
In order to patch system class, custom class must:
* add suffix to original class name like `Core_Modified`, suffix should correspond to component name, that patches class
* extend class with similar name, but with `_` prefix like `_Core_Modified`
* be placed inside `cs/custom` namespace
* class file should be placed in `custom/classes/{namespace/Class_name.php` file, but namespace `cs\custom` should be dropped here

This all a bit complex, lets show on example of `cs\Page` class patching by `Foo` module:
```<?php
// File: custom/classes/Page_Foo.php
namespace cs\custom;

class Page_Foo extends _Page_Foo {
    // Something here
}
```

Components that want to modify system classes should create corresponding files within `custom/classes` directory on component enabling and remove it on disabling/uninstallation.

Since different components will create classes with different suffixes all patches will be applied without conflicts (don't forget to call method from parent class to keep original functionality if you're not intending to replace it).
