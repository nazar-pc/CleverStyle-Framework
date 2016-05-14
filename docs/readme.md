### Welcome to the CleverStyle CMS wiki!
Here you can find all useful information:
* How to use
* How to customize
* How to extend

### [Video tutorials for developers](https://www.youtube.com/watch?v=GVXHeCVbO_c&list=PLVUA3QJ02XIiKEzpD4dxoCENgzzJyNEnH)

### [Installation](/docs/installation/Installation.md)
* [Download installation packages](/docs/installation/Download-installation-packages.md)
* [Creation of own installation packages](/docs/installation/Installer-builder.md)
* [Nginx config sample (with PHP-FPM or HHVM)](/docs/installation/Nginx-config-sample.md)

### [File system structure](/docs/File-system-structure.md) [Backend] [dev]

### [Development environment and contribution](/docs/Development-environment-and-contribution.md) [dev]

### Core [Backend] [dev]
* [Classes](/docs/backend-core/Classes.md)
* [Engines](/docs/backend-core/Engines.md)
* [Languages](/docs/backend-core/Languages.md)
* [Templates](/docs/backend-core/Templates.md)
* [Themes](/docs/backend-core/Themes.md)

### Components [Backend] [dev]
* [Blocks](/docs/backend-components/Blocks.md)
* [Modules](/docs/backend-components/Modules.md)
* [Plugins](/docs/backend-components/Plugins.md)

### Quick start [Backend] [Frontend] [dev]
* [Simplest block](/docs/quick-start/Simplest-block.md)
* [Simplest module](/docs/quick-start/Simplest-module.md)
* [Simplest plugin](/docs/quick-start/Simplest-plugin.md)
* [Code style](/docs/quick-start/Code-style.md)
* [Plugin architecture](/docs/quick-start/Plugin-architecture.md)
* [Module architecture](/docs/quick-start/Module-architecture.md)
* [Events](/docs/quick-start/Events.md)
 * [Naming](/docs/quick-start/Events.md#naming)
 * [Subscribing](/docs/quick-start/Events.md#subscribing)
 * [Unsubscribing](/docs/quick-start/Events.md#unsubscribing)
 * [One-time subscribing](/docs/quick-start/Events.md#one-time-subscribing)
 * [Dispatching](/docs/quick-start/Events.md#dispatching)
 * [System events](/docs/quick-start/Events.md#system-events)
* [Classes autoloading](/docs/quick-start/Classes-autoloading.md)
* [Page title and content](/docs/quick-start/Page-title-and-content.md)
* [Page layout](/docs/quick-start/Page-layout.md)
* [XHR (AJAX)](/docs/quick-start/XHR.md)
* [CleverStyle Widgets](/docs/quick-start/CleverStyle-Widgets.md)
* [UI helpers](/docs/quick-start/UI-helpers.md)
* [Shared styles](/docs/quick-start/Shared-styles.md)
* [WYSIWYG wrapper elements](/docs/quick-start/WYSIWYG-wrapper-elements.md)

### CleverStyle CMS-specific features [Backend] [dev]
* [exit/die](/docs/framework-specific-features/exit-die.md)
* [Request/Response](/docs/framework-specific-features/request-response.md)

### Advanced [Backend] [dev]
* [Testing](/docs/backend-advanced/Testing.md)
* [System classes extension](/docs/backend-advanced/System-classes-extension.md)
* [Components dependencies and conflicts](/docs/backend-advanced/Components-dependencies-and-conflicts.md)
* [Files uploading](/docs/backend-advanced/Files-uploading.md)
* [Routing](/docs/backend-advanced/Routing.md)
* [Database](/docs/backend-advanced/Database.md)
* [Permissions](/docs/backend-advanced/Permissions.md)
* [Composer](/docs/backend-advanced/Composer.md)
* [Classes aliases](/docs/backend-advanced/Classes-aliases.md)

### Advanced [Frontend] [dev]
* [Polymer behaviors](/docs/frontend-advanced/Polymer-behaviors.md)
* [Polymer elements extension](/docs/frontend-advanced/Polymer-elements-extension.md)
* [Files uploading](/docs/frontend-advanced/Files-uploading.md)
* [RequireJS](/docs/frontend-advanced/RequireJS.md)
* [Bower & NPM](/docs/frontend-advanced/Bower-and-NPM.md)

### System objects [Backend] [dev]
Global system objects provides almost all functionality of CleverStyle CMS. Shortly they were described in [classes section](/docs/Classes.md). Here you can see, how to use them in practice.
* [$App](/docs/backend-system-objects/$App.md)
 * [Methods](/docs/backend-system-objects/$App.md#methods)
 * [Properties](/docs/backend-system-objects/$App.md#properties)
 * [Events](/docs/backend-system-objects/$App.md#events)
* [$Cache](/docs/backend-system-objects/$Cache.md)
 * [Methods](/docs/backend-system-objects/$Cache.md#methods)
 * [Engines](/docs/backend-system-objects/$Cache.md#engines)
 * [Examples](/docs/backend-system-objects/$Cache.md#examples)
 * [\cs\Cache\\_Abstract class](/docs/backend-system-objects/$Cache.md#abstract-class)
 * [\cs\Cache\\_Abstract_with_namespace class](/docs/backend-system-objects/$Cache.md#abstract-with-namespace-class)
 * [\cs\Cache\Prefix class](/docs/backend-system-objects/$Cache.md#prefix-class)
* [$Config](/docs/backend-system-objects/$Config.md)
 * [Methods](/docs/backend-system-objects/$Config.md#methods)
 * [Properties](/docs/backend-system-objects/$Config.md#properties)
 * [Events](/docs/backend-system-objects/$Config.md#events)
 * [Constants](/docs/backend-system-objects/$Config.md#constants)
 * [\cs\Config\Module_Properties class](/docs/backend-system-objects/$Config.md#module-properties-class)
* [$Core](/docs/backend-system-objects/$Core.md)
 * [Methods](/docs/backend-system-objects/$Core.md#methods)
 * [Properties](/docs/backend-system-objects/$Core.md#properties)
* [$db](/docs/backend-system-objects/$db.md) (from Database)
 * [Methods](/docs/backend-system-objects/$db.md#methods)
 * [Properties](/docs/backend-system-objects/$db.md#properties)
 * [\cs\DB\\_Abstract class](/docs/backend-system-objects/$db.md#abstract-class)
 * [\cs\DB\Accessor trait](/docs/backend-system-objects/$db.md#accessor-trait)
* [$Event](/docs/backend-system-objects/$Event.md)
 * [Methods](/docs/backend-system-objects/$Event.md#methods)
* [$Group](/docs/backend-system-objects/$Group.md)
 * [Methods](/docs/backend-system-objects/$Group.md#methods)
 * [Events](/docs/backend-system-objects/$Group.md#events)
* [$Key](/docs/backend-system-objects/$Key.md)
 * [Methods](/docs/backend-system-objects/$Key.md#methods)
* [$L](/docs/backend-system-objects/$L.md) (from Language)
 * [Methods](/docs/backend-system-objects/$L.md#methods)
 * [Properties](/docs/backend-system-objects/$L.md#properties)
 * [Events](/docs/backend-system-objects/$L.md#events)
 * [\cs\Language\Prefix class](/docs/backend-system-objects/$L.md#prefix-class)
* [$Mail](/docs/backend-system-objects/$Mail.md)
 * [Methods](/docs/backend-system-objects/$Mail.md#methods)
* [$Menu](/docs/backend-system-objects/$Menu.md)
 * [Methods](/docs/backend-system-objects/$Menu.md#methods)
 * [Events](/docs/backend-system-objects/$Menu.md#events)
* [$Page](/docs/backend-system-objects/$Page.md)
 * [Methods](/docs/backend-system-objects/$Page.md#methods)
 * [Properties](/docs/backend-system-objects/$Page.md#properties)
 * [Events](/docs/backend-system-objects/$Page.md#events)
 * [\cs\Page\Includes_processing class](/docs/backend-system-objects/$Page.md#includes-processing-class)
 * [\cs\Page\Meta class](/docs/backend-system-objects/$Page.md#meta-class)
* [$Permission](/docs/backend-system-objects/$Permission.md)
 * [Methods](/docs/backend-system-objects/$Permission.md#methods)
 * [\cs\Permission\All trait](/docs/backend-system-objects/$Permission.md#all-trait)
* [$Request](/docs/backend-system-objects/$Request.md)
 * [Methods](/docs/backend-system-objects/$Request.md#methods)
 * [Properties](/docs/backend-system-objects/$Request.md#properties)
 * [Events](/docs/backend-system-objects/$Request.md#events)
* [$Response](/docs/backend-system-objects/$Response.md)
 * [Methods](/docs/backend-system-objects/$Response.md#methods)
 * [Properties](/docs/backend-system-objects/$Response.md#properties)
* [$Session](/docs/backend-system-objects/$Session.md)
 * [Methods](/docs/backend-system-objects/$Session.md#methods)
 * [Events](/docs/backend-system-objects/$Session.md#events)
* [$Storage](/docs/backend-system-objects/$Storage.md)
 * [Methods](/docs/backend-system-objects/$Storage.md#methods)
 * [\cs\Storage\\_Abstract class](/docs/backend-system-objects/$Storage.md#abstract-class)
* [$Text](/docs/backend-system-objects/$Text.md)
 * [Methods](/docs/backend-system-objects/$Text.md#methods)
* [$Trigger](/docs/backend-system-objects/$Trigger.md)
 * [Methods](/docs/backend-system-objects/$Trigger.md#methods)
* [$User](/docs/backend-system-objects/$User.md)
 * [Methods](/docs/backend-system-objects/$User.md#methods)
 * [Properties](/docs/backend-system-objects/$User.md#properties)
 * [Constants](/docs/backend-system-objects/$User.md#constants)
 * [Events](/docs/backend-system-objects/$User.md#events)
 * [\cs\User\Properties class](/docs/backend-system-objects/$User.md#properties-class)

### System objects [Frontend] [dev]
Some global system objects in Frontend
* [Event](/docs/frontend-system-objects/Event.md)
 * [Methods](/docs/frontend-system-objects/Event.md#methods)
* [L](/docs/frontend-system-objects/L.md) (from Language)
 * [Methods](/docs/frontend-system-objects/L.md#methods)
 * [Properties](/docs/frontend-system-objects/L.md#properties)

### System classes [Backend] [dev]
Some classes are used without objects creation, and contain static methods. Anyway, they also are also  widely used.
* [h](/docs/backend-system-classes/h.md)
 * [Pseudo-tags](/docs/backend-system-classes/h.md#pseudo-tags)

### System traits [Backend] [dev]
* [Singleton](/docs/backend-system-traits/Singleton.md)
 * [Methods](/docs/backend-system-traits/Singleton.md#methods)
 * [Example](/docs/backend-system-traits/Singleton.md#example)
* [CRUD](/docs/backend-system-traits/CRUD.md)
 * [Methods](/docs/backend-system-traits/CRUD.md#methods)
 * [Properties](/docs/backend-system-traits/CRUD.md#properties)
 * [Example](/docs/backend-system-traits/CRUD.md#example)
* [CRUD_helpers](/docs/backend-system-traits/CRUD_helpers.md)
 * [Methods](/docs/backend-system-traits/CRUD_helpers.md#methods)
 * [Example](/docs/backend-system-traits/CRUD_helpers.md#example)

[dev] - means for developers, not for regular users

More information, coming soon, stay tuned...
