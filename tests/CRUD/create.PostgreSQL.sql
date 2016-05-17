CREATE TABLE "[prefix]crud_test" (
	"id" smallserial,
	"title" text NOT NULL,
	"description" text NOT NULL,
	"data" text NOT NULL
);

CREATE TABLE "[prefix]crud_test_joined_table1" (
	"id" smallint NOT NULL,
	"value" smallint NOT NULL
);

ALTER TABLE ONLY "[prefix]crud_test" ADD CONSTRAINT "[prefix]crud_test_primary" PRIMARY KEY ("id");

ALTER TABLE ONLY "[prefix]crud_test_joined_table1" ADD CONSTRAINT "[prefix]crud_test_joined_table1_primary" PRIMARY KEY ("id", "value");
