### Welcome to the CleverStyle CMS wiki!
Here you can find all useful information:
* How to use
* How to customize
* How to write additional components

### [Video tutorials for developers](https://www.youtube.com/watch?v=GVXHeCVbO_c&list=PLVUA3QJ02XIiKEzpD4dxoCENgzzJyNEnH)

### [Installation](/docs/Installation.md)
* [Download installation packages](/docs/Download-installation-packages.md)
* [Creation of own installation packages](/docs/Installer-builder.md)
* [Nginx config sample (with PHP-FPM or HHVM)](/docs/Nginx-config-sample.md)

### [File system structure](/docs/File-system-structure.md) [Backend] [dev]

### [Development environment and contribution](/docs/Development-environment-and-contribution.md)

### Core [Backend] [dev]
* [Classes](/docs/Classes.md)
* [Engines](/docs/Engines.md)
* [Languages](/docs/Languages.md)
* [Templates](/docs/Templates.md)
* [Themes](/docs/Themes.md)

### Components [Backend] [dev]
* [Blocks](/docs/Blocks.md)
* [Modules](/docs/Modules.md)
* [Plugins](/docs/Plugins.md)

### Quick start [Backend] [Frontend] [dev]
* [Simplest block](/docs/Simplest-block.md)
* [Simplest module](/docs/Simplest-module.md)
* [Simplest plugin](/docs/Simplest-plugin.md)
* [Code style](/docs/Code-style.md)
* [Plugin architecture](/docs/Plugin-architecture.md)
* [Module architecture](/docs/Module-architecture.md)
* [Events](/docs/Events.md)
 * [Naming](/docs/Events.md#naming)
 * [Subscribing](/docs/Events.md#subscribing)
 * [Dispatching](/docs/Events.md#dispatching)
 * [System events](/docs/Events.md#system-events)
* [Classes autoloading](/docs/Classes-autoloading.md)
* [Page title and content](/docs/Page-title-and-content.md)
* [Page layout](/docs/Page-layout.md)
* [XHR (AJAX)](/docs/XHR.md)
* [CleverStyle Widgets](/docs/CleverStyle-Widgets.md)
* [UI helper methods](/docs/UI-helper-methods.md)
* [Shared styles](/docs/Shared-styles.md)
* [WYSIWYG wrapper elements](/docs/WYSIWYG-wrapper-elements.md)

### CleverStyle CMS-specific features [Backend] [dev]
* [exit/die](/docs/exit_die.md)

### Advanced [Backend] [dev]
* [Testing](/docs/Testing.md)
* [System classes extension](/docs/System-classes-extension.md)
* [Components dependencies and conflicts](/docs/Components-dependencies-and-conflicts.md)
* [Files uploading](/docs/Files-uploading.md#backend)
* [Routing](/docs/Routing.md)
* [Database](/docs/Database.md)
* [Permissions](/docs/Permissions.md)
* [Composer](/docs/Composer.md)

### Advanced [Frontend] [dev]
* [Polymer behaviors](/docs/Polymer-behaviors.md)
* [Polymer elements extension](/docs/Polymer-elements-extension.md)
* [Files uploading](/docs/Files-uploading.md#frontend)
* [RequireJS](/docs/RequireJS.md)
* [Bower & NPM](/docs/Bower-and-NPM.md)

### System objects [Backend] [dev]
Global system objects provides almost all functionality of CleverStyle CMS. Shortly they were described in [classes section](/docs/Classes.md). Here you can see, how to use them in practice.
* [$App](/docs/$App.md)
 * [Methods](/docs/$App.md#methods)
 * [Properties](/docs/$App.md#properties)
 * [Events](/docs/$App.md#events)
* [$Cache](/docs/$Cache.md)
 * [Methods](/docs/$Cache.md#methods)
 * [Engines](/docs/$Cache.md#engines)
 * [Examples](/docs/$Cache.md#examples)
 * [\cs\Cache\\_Abstract class](/docs/$Cache.md#abstract-class)
 * [\cs\Cache\\_Abstract_with_namespace class](/docs/$Cache.md#abstract-with-namespace-class)
 * [\cs\Cache\Prefix class](/docs/$Cache.md#prefix-class)
* [$Config](/docs/$Config.md)
 * [Methods](/docs/$Config.md#methods)
 * [Properties](/docs/$Config.md#properties)
 * [Events](/docs/$Config.md#events)
 * [Constants](/docs/$Config.md#constants)
 * [\cs\Config\Module_Properties class](/docs/$Config.md#module-properties-class)
* [$Core](/docs/$Core.md)
 * [Methods](/docs/$Core.md#methods)
 * [Constants](/docs/$Core.md#constants)
* [$db](/docs/$db.md) (from Database)
 * [Methods](/docs/$db.md#methods)
 * [Properties](/docs/$db.md#properties)
 * [\cs\DB\\_Abstract class](/docs/$db.md#abstract-class)
 * [\cs\DB\Accessor trait](/docs/$db.md#accessor-trait)
* [$Event](/docs/$Event.md)
 * [Methods](/docs/$Event.md#methods)
* [$Group](/docs/$Group.md)
 * [Methods](/docs/$Group.md#methods)
 * [Events](/docs/$Group.md#events)
* [$Key](/docs/$Key.md)
 * [Methods](/docs/$Key.md#methods)
* [$L](/docs/$L.md) (from Language)
 * [Methods](/docs/$L.md#methods)
 * [Properties](/docs/$L.md#properties)
 * [Events](/docs/$L.md#events)
 * [\cs\Language\Prefix class](/docs/$L.md#prefix-class)
* [$Mail](/docs/$Mail.md)
 * [Methods](/docs/$Mail.md#methods)
* [$Menu](/docs/$Menu.md)
 * [Methods](/docs/$Menu.md#methods)
 * [Events](/docs/$Menu.md#events)
* [$Page](/docs/$Page.md)
 * [Methods](/docs/$Page.md#methods)
 * [Properties](/docs/$Page.md#properties)
 * [Events](/docs/$Page.md#events)
 * [\cs\Page\Includes_processing class](/docs/$Page.md#includes-processing-class)
 * [\cs\Page\Meta class](/docs/$Page.md#meta-class)
* [$Permission](/docs/$Permission.md)
 * [Methods](/docs/$Permission.md#methods)
 * [\cs\Permission\All trait](/docs/$Permission.md#all-trait)
* [$Request](/docs/$Request.md)
 * [Methods](/docs/$Request.md#methods)
 * [Properties](/docs/$Request.md#properties)
 * [Events](/docs/$Request.md#events)
* [$Response](/docs/$Response.md)
 * [Methods](/docs/$Response.md#methods)
 * [Properties](/docs/$Response.md#properties)
* [$Session](/docs/$Session.md)
 * [Methods](/docs/$Session.md#methods)
 * [Events](/docs/$Session.md#events)
* [$Storage](/docs/$Storage.md)
 * [Methods](/docs/$Storage.md#methods)
 * [\cs\Storage\\_Abstract class](/docs/$Storage.md#abstract-class)
* [$Text](/docs/$Text.md)
 * [Methods](/docs/$Text.md#methods)
* [$Trigger](/docs/$Trigger.md)
 * [Methods](/docs/$Trigger.md#methods)
* [$User](/docs/$User.md)
 * [Methods](/docs/$User.md#methods)
 * [Properties](/docs/$User.md#properties)
 * [Constants](/docs/$User.md#constants)
 * [Events](/docs/$User.md#events)
 * [\cs\User\Properties class](/docs/$User.md#properties-class)

### System objects [Frontend] [dev]
Some global system objects in Frontend
* [Event](/docs/Event.md)
 * [Methods](/docs/Event.md#methods)
* [L](/docs/L.md) (from Language)
 * [Methods](/docs/L.md#methods)
 * [Properties](/docs/L.md#properties)

### System classes [Backend] [dev]
Some classes are used without objects creation, and contain static methods. Anyway, they also are also  widely used.
* [h](/docs/h.md)
 * [Pseudo-tags](/docs/h.md#pseudo-tags)

### System traits [Backend] [dev]
* [Singleton](/docs/Singleton.md)
 * [Methods](/docs/Singleton.md#methods)
 * [Example](/docs/Singleton.md#example)
* [CRUD](/docs/CRUD.md)
 * [Methods](/docs/CRUD.md#methods)
 * [Properties](/docs/CRUD.md#properties)
 * [Example](/docs/CRUD.md#example)
* [CRUD_helpers](/docs/CRUD_helpers.md)
 * [Methods](/docs/CRUD_helpers.md#methods)
 * [Example](/docs/CRUD_helpers.md#example)

[dev] - means for developers, not for regular users

More information, coming soon, stay tuned...
