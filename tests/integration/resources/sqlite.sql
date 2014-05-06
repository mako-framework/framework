DROP TABLE IF EXISTS "articles";
CREATE TABLE "articles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "created_at" text NOT NULL,
  "title" text NOT NULL,
  "body" text NOT NULL
);

INSERT INTO "articles" ("id", "user_id", "created_at", "title", "body") VALUES (1, 1, '2014-04-30 15:02:10', 'article 1', 'article body 1');
INSERT INTO "articles" ("id", "user_id", "created_at", "title", "body") VALUES (2, 1, '2014-04-30 15:02:10', 'article 2', 'article body 2');
INSERT INTO "articles" ("id", "user_id", "created_at", "title", "body") VALUES (3, 2, '2014-04-30 15:32:10', 'article 3', 'article body 3');

DROP TABLE IF EXISTS "groups";
CREATE TABLE "groups" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL
);

INSERT INTO "groups" ("id", "name") VALUES (1, 'admin');
INSERT INTO "groups" ("id", "name") VALUES (2, 'user');

DROP TABLE IF EXISTS "groups_users";
CREATE TABLE "groups_users" (
  "group_id" integer NOT NULL,
  "user_id" integer NOT NULL
);

INSERT INTO "groups_users" ("group_id", "user_id") VALUES (1, 1);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (1, 2);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (1, 3);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (2, 1);

DROP TABLE IF EXISTS "profiles";
CREATE TABLE "profiles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "interests" text NOT NULL
);

INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (1, 1, 'music');
INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (2, 2, 'movies');
INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (3, 3, 'hockey');

DROP TABLE IF EXISTS "users";
CREATE TABLE "users" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "created_at" text NOT NULL,
  "username" text NOT NULL,
  "email" text NOT NULL
);

INSERT INTO "users" ("id", "created_at", "username", "email") VALUES (1, '2014-04-30 14:40:01', 'foo', 'foo@example.org');
INSERT INTO "users" ("id", "created_at", "username", "email") VALUES (2, '2014-04-30 14:02:43', 'bar', 'bar@example.org');
INSERT INTO "users" ("id", "created_at", "username", "email") VALUES (3, '2014-04-30 14:12:43', 'baz', 'baz@example.org');

DROP TABLE IF EXISTS "optimistic_locks";
CREATE TABLE "optimistic_locks" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "lock_version" integer NOT NULL DEFAULT 0,
  "value" text NOT NULL
);

INSERT INTO "optimistic_locks" ("id", "lock_version", "value") VALUES (1, 0, "foo");

DROP TABLE IF EXISTS "uuid_keys";
CREATE TABLE "uuid_keys" (
  "id" text NOT NULL PRIMARY KEY,
  "value" text NOT NULL
);

DROP TABLE IF EXISTS "custom_keys";
CREATE TABLE "custom_keys" (
  "id" text NOT NULL PRIMARY KEY,
  "value" text NOT NULL
);

DROP TABLE IF EXISTS "no_keys";
CREATE TABLE "no_keys" (
  "value" text NOT NULL
);