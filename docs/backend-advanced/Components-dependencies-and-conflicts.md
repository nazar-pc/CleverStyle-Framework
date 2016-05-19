Components dependencies are specified in `meta.json` file of [module](/docs/quick-start/Module-architecture.md#metajson) or [plugin](/docs/quick-start/Plugin-architecture.md#metajson), for instance:
```json
{
    "package"      : "Blogs",
    "category"     : "modules",
    "version"      : "0.105.2+build-118",
    "description"  : "Adds blogging functionality. Comments module is required for comments functionality, Plupload or similar module is required for files uploading functionality.",
    "author"       : "Nazar Mokrynskyi",
    "website"      : "cleverstyle.org/Framework",
    "license"      : "MIT License",
    "db_support"   : [
        "MySQLi"
    ],
    "provide"      : "blogs",
    "require"      : "System=>0.589",
    "optional"     : [
        "Comments",
        "Plupload",
        "TinyMCE",
        "file_upload",
        "editor",
        "simple_editor",
        "inline_editor"
    ],
    "multilingual" : [
        "interface",
        "content"
    ],
    "languages"    : [
        "English",
        "Russian",
        "Ukrainian"
    ]
}
```


There are two main types of connections between components: dependencies and conflicts.

## Conflicts
Conflicts are rather simple: each component can conflict dy component name directly using `conflict` parameter in `meta.json` or with any component that provides the same features with `provide` parameter in `meta.json`.
Reduced example:
```json
{
    "package"  : "Main_module",
    "provide"  : "super_feature",
    "conflict" : "Other_module<=3.0"
}
```
In this example `Main_module` will conflict with any component that provides `super_feature` or with `Other_module` that have version less or equal to `3.0`.

## Dependencies
Dependencies are more complex:
* dependencies that are mandatory for component operation may be specified in `require` parameter in `meta.json`, it is possible to specify both exact components names and provided functionality in `provide` parameter of `meta.json`
* optional dependencies may be specified with `optional` parameter of `meta.json`, syntax the same as for `require`
* there can be reverse dependencies using `provide` parameter in `meta.json` (see below)

### Reverse dependencies
Direct dependencies are specified as is and work just fine. Reverse dependencies are specified in `provide` parameter in `meta.json` in order to show that this component provides extension for other component.
Reduced example:
```json
{
    "package" : "Main_module",
    "provide" : "main_module",
    "require" : "main_dependency"
}
```
```json
{
    "package" : "Main_module_patch",
    "provide" : "Main_module/patch"
}
```
In this example `Main_module_patch` component extends `Main_module`, technically behavior is the same as in case if `Main_module` have optional dependency on `Main_module_patch` with only difference that you don't have to specify any dependencies in `Main_module`.

This is especially handy for [Polymer elements extension](/docs/frontend-advanced/Polymer-elements-extension.md), when there can be any custom components that extend other (see below section **JS/CSS/HTML inclusion**). Because you have such flexible dependency you can extend components multiple times without having any conflicts between components.

Also, you can extend not only by component name, but also by functionality, just like with direct dependencies.

### Dependency on specific version
It is possible to declare dependency on specific version of component. In example above you can see that component depends on `System` with version that is greater or equal than `0.589`.
You can use operators `>`, `<`, `=`, `>=` and `<=` here, but only one at a time.

### Multiple dependencies
If you need to require or conflict with multiple packages - just specify them as array:
```json
{
    "require" : [
        "System>=1.50",
        "composer"
    ]
}
```
or even limit version of packages from lower and upper sides:
```json
{
    "require" : [
        "System>=1.50",
        "System<2.0"
    ]
}
```

### Updating
While there is an update feature, sometimes component might be unable to update from very old version to current directly (though, you might be able to achieve this with incremental updates).
To specify supported versions for updating `update_from_version` parameter in `meta.json` is used.
Reduced example:
```json
{
    "package"             : "Main_module",
    "version"             : "3.0.0+build-300",
    "update_from_version" : "2.9.5"
}
```
In this example `Main_module` can be updated to version `3.0.0` from version `2.9.5` or newer.

### JS/CSS/HTML inclusion
Components dependencies are only effective on installation/updating/deletion stages, but also for files inclusion.

For example from **Reverse dependencies** section above at first JS/CSS/HTML files from `main_dependency` and `Main_module_patch` will be included, and only then ones of `Main_module` itself.
This gives both flexibility and control over includes order in source code of generated HTML for current page.
