[Composer](https://getcomposer.org/) is Dependency Manager for PHP.

This is de-facto standard packages manager and CleverStyle CMS have great support for it.

### composer.json
Usual thing is to create `composer.json` file in the root of the project and declare project dependencies there.

System is self-contained, so it comes with all necessary dependencies bundled and thus doesn't have `composer.json` in the root directory.

However, if you'll create it and install some packages - `vendor` directory will be detected by system and all your dependencies will be automatically available anywhere you need them.

### Composer module
`composer.json` is great, but CleverStyle CMS has own packaging system which is designed specifically for this purpose; sometimes such packages may rely on external dependencies, but having them bundled is not a very good idea because other components might need them as well and it will be very difficult to control conflicts.

For this purpose there is `Composer` module. It allows to declare in `meta.json` not only dependencies on CleverStyle CMS-specific, but also on regular Composer packages.

As the result, when you install such package - all Composer dependencies will be resolved and installed automatically (if there are no conflicts). Moreover, you do not need any access to terminal or even have Composer installed on server - `Composer` module will do everything for you.

For specific details refer to module's readme.
