CREATE TABLE "[prefix]crud_test_basic" (
	"id" smallserial,
	"max" int NOT NULL,
	"set" text NOT NULL,
	"number" int NOT NULL,
	"title" text NOT NULL,
	"description" text NOT NULL,
	"data" text NOT NULL
);

CREATE TABLE "[prefix]crud_test_basic_joined_table" (
	"id" smallint NOT NULL,
	"value" smallint NOT NULL
);

ALTER TABLE ONLY "[prefix]crud_test_basic" ADD CONSTRAINT "[prefix]crud_test_basic_primary" PRIMARY KEY ("id");

ALTER TABLE ONLY "[prefix]crud_test_basic_joined_table" ADD CONSTRAINT "[prefix]crud_test_basic_joined_table_primary" PRIMARY KEY ("id", "value");
