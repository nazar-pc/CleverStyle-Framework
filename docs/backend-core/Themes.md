Theme is part of system, that defines appearance of website.

Theme should have at least `index.html` or `index.php` file.

All themes are located in `themes` directory.

Also theme might have `meta.json` just like modules (but much simpler), it is required for theme in order to create its distributive, here is an example:
```json
{
	"package"     : "DarkEnergy",
	"category"    : "themes",
	"version"     : "1.6.2+build-21",
	"description" : "Dark responsive theme for CleverStyle Framework",
	"author"      : "Nazar Mokrynskyi",
	"website"     : "cleverstyle.org/Framework",
	"license"     : "MIT License"
}
```
If this file exists it should have at least next properties:
* package - package name, should be the same as theme directory name
* category - always *themes* for themes
* version - package version in order to distinguish different versions of the same theme
* description - short theme description in few words
* author - theme author
* license - theme license

Other possible properties are:
* website
