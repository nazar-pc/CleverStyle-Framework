CREATE TABLE "[prefix]crud_test" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	"title" varchar(1024) NOT NULL,
	"description" TEXT NOT NULL,
	"data" TEXT NOT NULL
);

CREATE TABLE "[prefix]crud_test_joined_table1" (
	"id" smallint(5) NOT NULL,
	"value" tinyint(1) NOT NULL,
	PRIMARY KEY ("id", "value")
);
