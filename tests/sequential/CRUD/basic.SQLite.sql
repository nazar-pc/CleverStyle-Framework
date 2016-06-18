CREATE TABLE "[prefix]crud_test_basic" (
	"id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	"title" varchar(1024) NOT NULL,
	"description" TEXT NOT NULL,
	"data" TEXT NOT NULL
);

CREATE TABLE "[prefix]crud_test_basic_joined_table" (
	"id" smallint(5) NOT NULL,
	"value" tinyint(1) NOT NULL,
	PRIMARY KEY ("id", "value")
);
