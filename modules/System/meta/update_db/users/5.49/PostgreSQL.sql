ALTER TABLE "[prefix]sign_ins" DROP CONSTRAINT "[prefix]sign_ins_primary";
DROP INDEX "[prefix]sign_ins_id";
ALTER TABLE ONLY "[prefix]sign_ins" ADD CONSTRAINT "[prefix]sign_ins_primary" PRIMARY KEY ("id");
CREATE INDEX "[prefix]sign_ins_expire" ON "[prefix]sign_ins" USING btree ("expire", "login_hash", "ip");
