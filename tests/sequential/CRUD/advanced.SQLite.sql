CREATE TABLE "[prefix]crud_test_advanced" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	"title" varchar(1024) NOT NULL,
	"description" TEXT NOT NULL
);

CREATE TABLE "[prefix]crud_test_advanced_joined_table1" (
	"id" smallint(5) NOT NULL,
	"value" tinyint(1) NOT NULL,
	"lang" varchar(255) NOT NULL
);

CREATE TABLE "[prefix]crud_test_advanced_joined_table2" (
	"id" smallint(5) NOT NULL,
	"points" tinyint(1) NOT NULL,
	PRIMARY KEY ("id", "points")
);
