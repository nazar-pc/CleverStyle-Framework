### Welcome to the CleverStyle CMS wiki!
Here you can find all useful information:
* How to use
* How to customize
* How to write additional components

### [Video tutorials for developers](https://www.youtube.com/watch?v=GVXHeCVbO_c&list=PLVUA3QJ02XIiKEzpD4dxoCENgzzJyNEnH)

### [Installation](/docs/Installation)
* [Download installation packages](/docs/Download-installation-packages)
* [Creation of own installation packages](/docs/Installer-builder)
* [Nginx config sample (with PHP-FPM or HHVM)](/docs/Nginx-config-sample)

### [File system structure](/docs/File-system-structure) [Backend] [dev]

### [Development environment and contribution](/docs/Development-environment-and-contribution)

### Core [Backend] [dev]
* [Classes](/docs/Classes)
* [Engines](/docs/Engines)
* [Languages](/docs/Languages)
* [Templates](/docs/Templates)
* [Themes](/docs/Themes)

### Components [Backend] [dev]
* [Blocks](/docs/Blocks)
* [Modules](/docs/Modules)
* [Plugins](/docs/Plugins)

### Quick start [Backend] [Frontend] [dev]
* [Simplest block](/docs/Simplest-block)
* [Simplest module](/docs/Simplest-module)
* [Simplest plugin](/docs/Simplest-plugin)
* [Code style](/docs/Code-style)
* [Plugin architecture](/docs/Plugin-architecture)
* [Module architecture](/docs/Module-architecture)
* [Events](/docs/Events)
 * [Naming](/docs/Events#naming)
 * [Subscribing](/docs/Events#subscribing)
 * [Dispatching](/docs/Events#dispatching)
 * [System events](/docs/Events#system-events)
* [Classes autoloading](/docs/Classes-autoloading)
* [Page title and content](/docs/Page-title-and-content)
* [Page layout](/docs/Page-layout)
* [XHR (AJAX)](/docs/XHR)
* [CleverStyle Widgets](/docs/CleverStyle-Widgets)
* [UI helper methods](/docs/UI-helper-methods)
* [Shared styles](/docs/Shared-styles)
* [WYSIWYG wrapper elements](/docs/WYSIWYG-wrapper-elements)

### CleverStyle CMS-specific features [Backend] [dev]
* [exit/die](/docs/exit_die)

### Advanced [Backend] [dev]
* [Testing](/docs/Testing)
* [System classes extension](/docs/System-classes-extension)
* [Components dependencies and conflicts](/docs/Components-dependencies-and-conflicts)
* [Files uploading](/docs/Files-uploading#backend)
* [Routing](/docs/Routing)
* [Database](/docs/Database)
* [Permissions](/docs/Permissions)
* [Composer](/docs/Composer)

### Advanced [Frontend] [dev]
* [Polymer behaviors](/docs/Polymer-behaviors)
* [Polymer elements extension](/docs/Polymer-elements-extension)
* [Files uploading](/docs/Files-uploading#frontend)
* [RequireJS](/docs/RequireJS)
* [Bower & NPM](/docs/Bower-and-NPM)

### System objects [Backend] [dev]
Global system objects provides almost all functionality of CleverStyle CMS. Shortly they were described in [classes section](/docs/Classes). Here you can see, how to use them in practice.
* [$App](/docs/$App)
 * [Methods](/docs/$App#methods)
 * [Properties](/docs/$App#properties)
 * [Events](/docs/$App#events)
* [$Cache](/docs/$Cache)
 * [Methods](/docs/$Cache#methods)
 * [Engines](/docs/$Cache#engines)
 * [Examples](/docs/$Cache#examples)
 * [\cs\Cache\\_Abstract class](/docs/$Cache#abstract-class)
 * [\cs\Cache\\_Abstract_with_namespace class](/docs/$Cache#abstract-with-namespace-class)
 * [\cs\Cache\Prefix class](/docs/$Cache#prefix-class)
* [$Config](/docs/$Config)
 * [Methods](/docs/$Config#methods)
 * [Properties](/docs/$Config#properties)
 * [Events](/docs/$Config#events)
 * [Constants](/docs/$Config#constants)
 * [\cs\Config\Module_Properties class](/docs/$Config#module-properties-class)
* [$Core](/docs/$Core)
 * [Methods](/docs/$Core#methods)
 * [Constants](/docs/$Core#constants)
* [$db](/docs/$db) (from Database)
 * [Methods](/docs/$db#methods)
 * [Properties](/docs/$db#properties)
 * [\cs\DB\\_Abstract class](/docs/$db#abstract-class)
 * [\cs\DB\Accessor trait](/docs/$db#accessor-trait)
* [$Event](/docs/$Event)
 * [Methods](/docs/$Event#methods)
* [$Group](/docs/$Group)
 * [Methods](/docs/$Group#methods)
 * [Events](/docs/$Group#events)
* [$Key](/docs/$Key)
 * [Methods](/docs/$Key#methods)
* [$L](/docs/$L) (from Language)
 * [Methods](/docs/$L#methods)
 * [Properties](/docs/$L#properties)
 * [Events](/docs/$L#events)
 * [\cs\Language\Prefix class](/docs/$L#prefix-class)
* [$Mail](/docs/$Mail)
 * [Methods](/docs/$Mail#methods)
* [$Menu](/docs/$Menu)
 * [Methods](/docs/$Menu#methods)
 * [Events](/docs/$Menu#events)
* [$Page](/docs/$Page)
 * [Methods](/docs/$Page#methods)
 * [Properties](/docs/$Page#properties)
 * [Events](/docs/$Page#events)
 * [\cs\Page\Includes_processing class](/docs/$Page#includes-processing-class)
 * [\cs\Page\Meta class](/docs/$Page#meta-class)
* [$Permission](/docs/$Permission)
 * [Methods](/docs/$Permission#methods)
 * [\cs\Permission\All trait](/docs/$Permission#all-trait)
* [$Request](/docs/$Request)
 * [Methods](/docs/$Request#methods)
 * [Properties](/docs/$Request#properties)
 * [Events](/docs/$Request#events)
* [$Response](/docs/$Response)
 * [Methods](/docs/$Response#methods)
 * [Properties](/docs/$Response#properties)
* [$Session](/docs/$Session)
 * [Methods](/docs/$Session#methods)
 * [Events](/docs/$Session#events)
* [$Storage](/docs/$Storage)
 * [Methods](/docs/$Storage#methods)
 * [\cs\Storage\\_Abstract class](/docs/$Storage#abstract-class)
* [$Text](/docs/$Text)
 * [Methods](/docs/$Text#methods)
* [$Trigger](/docs/$Trigger)
 * [Methods](/docs/$Trigger#methods)
* [$User](/docs/$User)
 * [Methods](/docs/$User#methods)
 * [Properties](/docs/$User#properties)
 * [Constants](/docs/$User#constants)
 * [Events](/docs/$User#events)
 * [\cs\User\Properties class](/docs/$User#properties-class)

### System objects [Frontend] [dev]
Some global system objects in Frontend
* [Event](/docs/Event)
 * [Methods](/docs/Event#methods)
* [L](/docs/L) (from Language)
 * [Methods](/docs/L#methods)
 * [Properties](/docs/L#properties)

### System classes [Backend] [dev]
Some classes are used without objects creation, and contain static methods. Anyway, they also are also  widely used.
* [h](/docs/h)
 * [Pseudo-tags](/docs/h#pseudo-tags)

### System traits [Backend] [dev]
* [Singleton](/docs/Singleton)
 * [Methods](/docs/Singleton#methods)
 * [Example](/docs/Singleton#example)
* [CRUD](/docs/CRUD)
 * [Methods](/docs/CRUD#methods)
 * [Properties](/docs/CRUD#properties)
 * [Example](/docs/CRUD#example)
* [CRUD_helpers](/docs/CRUD_helpers)
 * [Methods](/docs/CRUD_helpers#methods)
 * [Example](/docs/CRUD_helpers#example)

[dev] - means for developers, not for regular users

More information, coming soon, stay tuned...
