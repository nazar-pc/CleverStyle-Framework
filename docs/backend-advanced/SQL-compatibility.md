Primary SQL dialect in CleverStyle Framework is MySQL, however, since other databases are supported (SQLite and PostgreSQL in particular), SQL commands are generally limited to those that can work will all supported databases.

Unfortunately, it is not always possible to write 1 SQL query that will work without any changes in all 3 mentioned databases. To resolve this issue, CleverStyle Framework provides some SQL syntax conversion from MySQL dialect to SQLite and PostgreSQL in corresponding database engines.

### SQLite
SQLite has only one minor incompatibility - it doesn't support `INSERT IGNORE`, so all occurrences will be automatically replaced with similar `INSERT OR IGNORE`:
```sql
-- before
INSERT IGNORE INTO `table_name`
    (
        `text`
    ) VALUES (
        ?
    )
-- after
INSERT OR IGNORE INTO `table_name`
    (
        `text`
    ) VALUES (
        ?
    )
```


### PostgreSQL
PosgreSQL has much more incompatibilities than SQLite.

Simplest change is replacing backticks \` with double quotes `"`:
```sql
-- before
SELECT `id` FROM `table_name`
-- after
SELECT "id" FROM "table_name"
```

`INSERT IGNORE INTO` construction is not supported, it will be rewritten to `INSERT INTO ... ON CONFLICT DO NOTHING`:
```sql
-- before
INSERT IGNORE INTO "table_name"
    (
        "text"
    ) VALUES (
        ?
    )
-- after
INSERT INTO "table_name"
    (
        "text"
    ) VALUES (
        ?
    )
ON CONFLICT DO NOTHING
```

`REPLACE INTO` is not supported either, it will be rewritten to `INSERT INTO ... ON CONFLICT ON CONSTRAINT "{table_name}_primary" DO UPDATE SET ...` (where `{table_name}` corresponds to table name) (NOTE: `{table_name}_primary` constraint should be present, obviously):
```sql
-- before
REPLACE INTO "table_name"
    (
        "id",
        "item",
        "value"
    ) VALUES (
        ?,
        ?,
        ?
    )
-- after
INSERT INTO "table_name"
    (
        "id",
        "item",
        "value"
    ) VALUES (
        ?,
        ?,
        ?
    )
ON CONFLICT ON CONSTRAINT "table_name_primary" DO UPDATE SET
    "id"    = EXCLUDED."id",
    "item"  = EXCLUDED."item",
    "value" = EXCLUDED."value"
```

The last incompatibility is, basically, prepared statements syntax, each `?` will be replaced with `$x`, where `x` is incremental number `>= 1`:
```sql
-- before
SELECT "id" FROM "table_name" WHERE `number` > ? AND `age` < ? LIMIT ?
-- after
SELECT "id" FROM "table_name" WHERE `number` > $1 AND `age` < $2 LIMIT $3
```
