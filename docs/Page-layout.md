Page layout in CleverStyle CMS generally depends on theme which is used, but there are few common elements that may be used in most themes.

Schematically it can be represented by table.

|                   | `$Page->Top`             |                      |
|-------------------|--------------------------|----------------------|
| `$Page->Left`     | `$Page->Content`         | `$Page->Right`       |
|                   | `$Page->Bottom`          |                      |

Each part have corresponding property in `\cs\Page` class. Top/Left/Right/Bottom elements are used for blocks placement (though, they might be not present in some specific theme). Top also is used for messages (`$Page->success()`, `$Page->notice()` and `$Page->warning()` methods).

There are 2 special properties `$Page->pre_Body` and `$Page->post_Body` which are not intended to be used for main content, but sometimes are very useful.
