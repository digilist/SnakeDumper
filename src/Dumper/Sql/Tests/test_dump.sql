-- ------------------------
-- SnakeDumper SQL Dump
-- ------------------------


CREATE TABLE "Customer" ("id" INTEGER DEFAULT NULL, "name" VARCHAR(10) DEFAULT NULL COLLATE BINARY, PRIMARY KEY("id"));
CREATE TABLE "Billing" ("id" INTEGER DEFAULT NULL, "customer_id" INTEGER DEFAULT NULL, "product" VARCHAR(100) DEFAULT NULL COLLATE BINARY, "amount" DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY("id"));
CREATE INDEX "billing_product" ON "Billing" ("product");
CREATE TABLE "RandomTable" ("id" INTEGER DEFAULT NULL, "name" VARCHAR(10) DEFAULT NULL COLLATE BINARY, PRIMARY KEY("id"));
INSERT INTO "Customer" ("id", "name") VALUES (1, 'Foobar'), (2, 'Foobar'), (3, 'Foobar');
INSERT INTO "Customer" ("id", "name") VALUES (4, 'Foobar');
INSERT INTO "Billing" ("id", "customer_id", "product", "amount") VALUES (1, 1, 'IT', '42.0'), (2, 1, NULL, '1337.0'), (3, 2, 'Some stuff', '1337.0');
