Permissions in CleverStyle CMS are used on every page even if you do not specify this explicitly:)

There are two main ways to use permissions:
* for restricting access to specific pages or sections of website
* more precise atomic permissions for specific actions

### Permissions for pages and sections
In generic form page URL looks like `admin|api/Module_name/path/sub_path/more` ([Routing](/docs/Routing.md)).
Every permission is a combination of *group* and *label* properties, this fits very well on page URL.
In this case we split generic URL into two parts:
* `admin|api/Module_name` corresponds to *group*
* `path/sub_path/more` corresponds to *label*, if all pages inside *group* should be affected, magic `index` is used

Also there is one more detail here: if permission exists (meaning you've created *group*/*label* pair, default behavior will differ for admin pages and other:
* for administration pages access it denied by default, unless explicitly allowed for user or its group
* for all other pages access if allowed by default, unless explicitly denied

For instance, if we want to deny any user to administer `Blogs` module - we just need to add such permission:
* *group* - `admin/Blogs`
* *label* - `index`

It is always possible to restrict access to specific pages, moreover, module doesn't need to do anything, this feature is supported by system core out of the box.

### Permissions for specific actions
Any module, however, may define its own permissions at installation or any other time, and use them explicitly to check access to specific actions using system object [$Permission](/docs/$Permission.md).
In this case it is completely up to module author how to choose *group* and *label*, however, the same default access rules as for pages still applies (meaning if *group* starts with `admin/` or `api/Module/admin` access will be denied by default).

### Inheritance
Permissions may be specified for groups and for specific users.
If user consists in several groups - they have some order, which defines which permissions will user get.
Rules for defining final user permissions are:
* at first user have no specific permissions set
* groups permissions are applied from group with lower priority to group with higher priority (higher priority have groups that are placed higher in user's groups list in administration)
* every group with higher priority redefines permissions already set by previous group
* lastly personal user permissions are applied, which may redefine any other permissions set by groups
* any rules above doesn't apply to root administrator (created at installation and have id `2`), it will have access anywhere and everywhere
