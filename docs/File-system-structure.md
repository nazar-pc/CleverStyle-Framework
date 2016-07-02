### Directories tree

* **build** [dev] - build tools
* **components** - different types of components
  * **blocks**
  * **modules**
* **config** - low level system configuration (DB connection, caching engine, etc.)
* **core**
  * **classes** - basic and supplementary classes
    * **thirdparty** - third party classes
  * **engines** - engines, subdirectories names corresponds to the names of classes, that uses them
    * **Cache**
    * **DB**
    * **Storage**
    * **Text**
  * **languages** - multilingual user interface translation of core and System module
    * **aliases** - additional aliases for languages
  * **traits** - basic traits used by system core
* **custom** - contains files that are used for system classes customization; can be used for custom system builds; can be created manually for by components
* **includes** - css/web components/js/images/fonts
  * **css**
  * **html**
  * **img**
  * **js**
* **install** [dev] - installation instruments
  * **DB** - SQL schemas for every supported DB type
* **storage**
  * **cache** - FileSystem cache
  * **logs** - logs
  * **pcache** - public cache (css/js)
  * **public** - public directory for external access, directory for Local storage
  * **temp** - directory for temporary files
* **templates** (deprecated)
  * **blocks** - blocks templates
* **themes** - themes

[dev] - means required only for developing, are not used in production
