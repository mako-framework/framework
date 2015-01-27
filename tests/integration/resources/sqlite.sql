------------------------------------------------------------
-- USERS
------------------------------------------------------------

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

------------------------------------------------------------
-- PROFILES
------------------------------------------------------------

DROP TABLE IF EXISTS "profiles";
CREATE TABLE "profiles" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "interests" text NOT NULL
);

INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (1, 1, 'music');
INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (2, 2, 'movies');
INSERT INTO "profiles" ("id", "user_id", "interests") VALUES (3, 3, 'hockey');

------------------------------------------------------------
-- IMAGES
------------------------------------------------------------

DROP TABLE IF EXISTS "images";
CREATE TABLE "images" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "image" text NOT NULL,
  "imageable_type" text NOT NULL,
  "imageable_id" text NOT NULL
);

INSERT INTO "images" ("id", "image", "imageable_type", "imageable_id") VALUES (1, 'foo.png', "\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile", 1);
INSERT INTO "images" ("id", "image", "imageable_type", "imageable_id") VALUES (2, 'bar.png', "\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile", 2);
INSERT INTO "images" ("id", "image", "imageable_type", "imageable_id") VALUES (3, 'baz.png', "\mako\tests\integration\database\midgard\relations\HasOnePolymorphicProfile", 3);

------------------------------------------------------------
-- GROUPS
------------------------------------------------------------

DROP TABLE IF EXISTS "groups";
CREATE TABLE "groups" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL
);

INSERT INTO "groups" ("id", "name") VALUES (1, 'admin');
INSERT INTO "groups" ("id", "name") VALUES (2, 'user');

------------------------------------------------------------
-- GROUPS_USERS
------------------------------------------------------------

DROP TABLE IF EXISTS "groups_users";
CREATE TABLE "groups_users" (
  "group_id" integer NOT NULL,
  "user_id" integer NOT NULL
);

INSERT INTO "groups_users" ("group_id", "user_id") VALUES (1, 1);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (2, 1);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (2, 2);
INSERT INTO "groups_users" ("group_id", "user_id") VALUES (2, 3);

------------------------------------------------------------
-- ARTICLES
------------------------------------------------------------

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

------------------------------------------------------------
-- ARTICLE_COMMENTS
------------------------------------------------------------

DROP TABLE IF EXISTS "article_comments";
CREATE TABLE "article_comments" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "article_id" integer NOT NULL,
  "user_id" integer NOT NULL,
  "created_at" text NOT NULL,
  "comment" text NOT NULL
);

INSERT INTO "article_comments" ("id", "article_id", "user_id", "created_at", "comment") VALUES (1, 1, 1, '2014-04-30 15:02:10', "article 1 comment 1");
INSERT INTO "article_comments" ("id", "article_id", "user_id", "created_at", "comment") VALUES (2, 1, 2, '2014-04-30 15:02:10', "article 1 comment 2");
INSERT INTO "article_comments" ("id", "article_id", "user_id", "created_at", "comment") VALUES (3, 2, 1, '2014-04-30 15:02:10', "article 2 comment 1");
INSERT INTO "article_comments" ("id", "article_id", "user_id", "created_at", "comment") VALUES (4, 3, 3, '2014-04-30 15:02:10', "article 3 comment 1");

------------------------------------------------------------
-- ARTICLE_COMMENTS
------------------------------------------------------------

DROP TABLE IF EXISTS "polymorphic_comments";
CREATE TABLE "polymorphic_comments" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "user_id" integer NOT NULL,
  "created_at" text NOT NULL,
  "comment" text NOT NULL,
  "commentable_type" text NOT NULL,
  "commentable_id" integer NOT NULL
);

INSERT INTO "polymorphic_comments" ("id", "user_id", "created_at", "comment", "commentable_type", "commentable_id") VALUES (1, 1, '2014-04-30 15:02:10', "article 1 comment 1", "\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle", 1);
INSERT INTO "polymorphic_comments" ("id", "user_id", "created_at", "comment", "commentable_type", "commentable_id") VALUES (2, 2, '2014-04-30 15:02:10', "article 1 comment 2", "\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle", 1);
INSERT INTO "polymorphic_comments" ("id", "user_id", "created_at", "comment", "commentable_type", "commentable_id") VALUES (3, 1, '2014-04-30 15:02:10', "article 2 comment 1", "\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle", 2);
INSERT INTO "polymorphic_comments" ("id", "user_id", "created_at", "comment", "commentable_type", "commentable_id") VALUES (4, 3, '2014-04-30 15:02:10', "article 2 comment 1", "\mako\tests\integration\database\midgard\relations\HasManyPolymorphicArticle", 3);

------------------------------------------------------------
-- OPTIMISTIC_LOCKS
------------------------------------------------------------

DROP TABLE IF EXISTS "optimistic_locks";
CREATE TABLE "optimistic_locks" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "lock_version" integer NOT NULL DEFAULT 0,
  "value" text NOT NULL
);

INSERT INTO "optimistic_locks" ("id", "lock_version", "value") VALUES (1, 0, "foo");

------------------------------------------------------------
-- UUID_KEYS
------------------------------------------------------------

DROP TABLE IF EXISTS "uuid_keys";
CREATE TABLE "uuid_keys" (
  "id" text NOT NULL PRIMARY KEY,
  "value" text NOT NULL
);

------------------------------------------------------------
-- CUSTOM_KEYS
------------------------------------------------------------

DROP TABLE IF EXISTS "custom_keys";
CREATE TABLE "custom_keys" (
  "id" text NOT NULL PRIMARY KEY,
  "value" text NOT NULL
);

------------------------------------------------------------
-- NO_KEYS
------------------------------------------------------------

DROP TABLE IF EXISTS "no_keys";
CREATE TABLE "no_keys" (
  "value" text NOT NULL
);

------------------------------------------------------------
-- TIMESTAMPED_FOOS
------------------------------------------------------------

DROP TABLE IF EXISTS "timestamped_foos";
CREATE TABLE "timestamped_foos" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "created_at" text NOT NULL,
  "updated_at" text NOT NULL,
  "value" text NOT NULL
);

INSERT INTO "timestamped_foos" ("id", "created_at", "updated_at", "value") VALUES (1, "2014-05-14 23:00:00", "2014-05-14 23:00:00", "foo");
INSERT INTO "timestamped_foos" ("id", "created_at", "updated_at", "value") VALUES (2, "2014-05-14 23:00:01", "2014-05-14 23:00:01", "bar");
INSERT INTO "timestamped_foos" ("id", "created_at", "updated_at", "value") VALUES (3, "2014-05-14 23:00:02", "2014-05-14 23:00:02", "baz");

------------------------------------------------------------
-- TIMESTAMPED_BARS
------------------------------------------------------------

DROP TABLE IF EXISTS "timestamped_bars";
CREATE TABLE "timestamped_bars" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "timestamped_foo_id" integer NOT NULL,
  "created_at" text NOT NULL,
  "updated_at" text NOT NULL
);

INSERT INTO "timestamped_bars" ("id", "timestamped_foo_id", "created_at", "updated_at") VALUES (1, 1, "2014-05-14 23:00:03", "2014-05-14 23:00:03");
INSERT INTO "timestamped_bars" ("id", "timestamped_foo_id", "created_at", "updated_at") VALUES (2, 2, "2014-05-14 23:00:04", "2014-05-14 23:00:04");

------------------------------------------------------------
-- COUNTERS
------------------------------------------------------------

DROP TABLE IF EXISTS "counters";
CREATE TABLE "counters" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "counter" integer NOT NULL
);

INSERT INTO "counters" ("id", "counter") VALUES (1, 0);
INSERT INTO "counters" ("id", "counter") VALUES (2, 0);
INSERT INTO "counters" ("id", "counter") VALUES (3, 0);