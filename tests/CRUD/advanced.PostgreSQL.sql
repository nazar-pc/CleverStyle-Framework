CREATE TABLE "[prefix]crud_test_advanced" (
	"id" smallserial,
	"title" text NOT NULL,
	"description" text NOT NULL
);

CREATE TABLE "[prefix]crud_test_advanced_joined_table1" (
	"id" smallint NOT NULL,
	"value" smallint NOT NULL,
	"lang" text NOT NULL
);

CREATE TABLE "[prefix]crud_test_advanced_joined_table2" (
	"id" smallint NOT NULL,
	"points" smallint NOT NULL
);

ALTER TABLE ONLY "[prefix]crud_test_advanced" ADD CONSTRAINT "[prefix]crud_test_advanced_primary" PRIMARY KEY ("id");

ALTER TABLE ONLY "[prefix]crud_test_advanced_joined_table2" ADD CONSTRAINT "[prefix]crud_test_advanced_joined_table2_primary" PRIMARY KEY ("id", "points");
