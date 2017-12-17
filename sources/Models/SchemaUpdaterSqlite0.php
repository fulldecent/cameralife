<?php
namespace CameraLife\Models;

/**
 * Updates the database schema
 * @author William Entriken <cameralife@phor.net>
 * @copyright 2017 William Entriken
 * @access public
 */
class SchemaUpdaterSqlite0 extends SchemaUpdater
{
    public $scriptInfo = <<<HERE
Currently your databases is empty. This updater will set up your database for
the first time.
HERE;

    private $link;

    /**
     * Side effect: sets up $this->link
     *
     * @access public
     * @return mixed true for succes, string for failure
     */
    public function canUpgrade()
    {
        $result = Database::run('SELECT * FROM sqlite_master WHERE type="table" AND name LIKE "'.Database::$prefix.'%"');
        $hasTables = false;
        while ($table = $result->fetchAssoc()) {
            $hasTables = true;
        }

        if ($hasTables) {
            return "This database actually has tables in it already. Are you using MySQL and forgot to update schemaVersion in the config file?";
        }

        return true;
    }

    public function doUpgrade()
    {
        //Database::beginTransaction();
        $sql = '
        CREATE TABLE "albums" (
          "id" INTEGER PRIMARY KEY NOT NULL,
          "topic" varchar(20) NOT NULL DEFAULT \'\',
          "name" varchar(25) NOT NULL DEFAULT \'\',
          "term" varchar(20) NOT NULL DEFAULT \'\',
          "poster_id" int(11) NOT NULL DEFAULT \'0\',
          "hits" bigint(20) NOT NULL DEFAULT \'0\'
        )
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "comments" (
          "id" INTEGER PRIMARY KEY NOT NULL,
          "photo_id" int(11) NOT NULL,
          "username" varchar(30) NOT NULL,
          "user_ip" varchar(16) NOT NULL,
          "comment" varchar(255) NOT NULL,
          "date" datetime NOT NULL
        );
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "exif" (
          "photoid" int(11) NOT NULL,
          "tag" varchar(50) NOT NULL,
          "value" varchar(255) NOT NULL,
          PRIMARY KEY ("photoid","tag")
        );
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "logs" (
          "id" INTEGER PRIMARY KEY NOT NULL ,
          "record_type" text  NOT NULL DEFAULT \'album\',
          "record_id" int(11) NOT NULL DEFAULT \'0\',
          "value_field" varchar(40) NOT NULL DEFAULT \'\',
          "value_new" text NOT NULL,
          "user_name" varchar(30) NOT NULL DEFAULT \'\',
          "user_ip" varchar(16) NOT NULL DEFAULT \'\',
          "user_date" date NOT NULL DEFAULT \'0000-00-00\'
        );
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "photos" (
          "id" INTEGER PRIMARY KEY NOT NULL ,
          "filename" varchar(255) NOT NULL,
          "path" varchar(255) NOT NULL DEFAULT \'\',
          "description" varchar(255) NOT NULL DEFAULT \'\',
          "username" varchar(30) DEFAULT NULL,
          "status" int(11) NOT NULL DEFAULT \'0\',
          "flag" text  DEFAULT NULL,
          "width" int(11) DEFAULT NULL,
          "height" int(11) DEFAULT NULL,
          "tn_width" int(11) DEFAULT NULL,
          "tn_height" int(11) DEFAULT NULL,
          "hits" bigint(20) NOT NULL DEFAULT \'0\',
          "created" date DEFAULT NULL,
          "fsize" int(11) NOT NULL DEFAULT \'0\',
          "modified" int(1) DEFAULT NULL,
          "mtime" int(11) DEFAULT NULL,
          "keywords" varchar(255) NOT NULL DEFAULT \'\'
        );
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "preferences" (
          "prefmodule" varchar(64) NOT NULL DEFAULT \'core\',
          "prefkey" varchar(64) NOT NULL DEFAULT \'\',
          "prefvalue" varchar(255) NOT NULL DEFAULT \'\',
          "prefdefault" varchar(255) NOT NULL DEFAULT \'\',
          PRIMARY KEY ("prefmodule","prefkey")
        );
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'CameraLife\',\'sitedate\', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'CameraLife\',\'thumbsize\', 240, 240);
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'CameraLife\',\'scaledsize\', 600, 600);
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'CameraLife\',\'optionsizes\', \'\', \'\');
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'CameraLife\',\'sitename\', \'My Photo Site\', \'My Photo Site\');
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'LocalFileStore\',\'cache_dir\', \'config/caches\', \'config/caches\');
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "preferences" VALUES (\'LocalFileStore\',\'photo_dir\', \'config/photos\', \'config/photos\');
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "ratings" (
          "id" INTEGER PRIMARY KEY NOT NULL,
          "username" varchar(30) DEFAULT NULL,
          "user_ip" varchar(16) NOT NULL,
          "rating" int(11) NOT NULL,
          "date" datetime NOT NULL
        );
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE TABLE "users" (
          "id" INTEGER PRIMARY KEY NOT NULL,
          "username" varchar(30) NOT NULL DEFAULT \'\',
          "password" varchar(255) NOT NULL DEFAULT \'\',
          "auth" int(11) NOT NULL DEFAULT \'0\',
          "cookie" varchar(64) NOT NULL DEFAULT \'\',
          "last_online" date NOT NULL DEFAULT \'0000-00-00\',
          "last_ip" varchar(20) DEFAULT NULL,
          "email" varchar(80) DEFAULT NULL,
          UNIQUE ("username")
        );
        ';
        $result = Database::run($sql);

        $sql = '
        INSERT INTO "users" (\'username\', \'password\',\'auth\') VALUES (\'admin\',:password,5);
        ';
        $result = Database::run($sql, ['password'=>Database::$adminAccessCodeHash]);

        $sql = '
        CREATE INDEX "ratings_id_3" ON "ratings" ("id","username","user_ip");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "ratings_id" ON "ratings" ("id");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "ratings_id_2" ON "ratings" ("id","username","user_ip");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "ratings_id_4" ON "ratings" ("id");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "photos_fingerprint" ON "photos" ("filename","fsize");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "photos_description" ON "photos" ("description");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "exif_photoid" ON "exif" ("photoid");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "comments_id" ON "comments" ("photo_id");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "users_username" ON "users" ("username");
        ';
        $result = Database::run($sql);

        $sql = '
        CREATE INDEX "users_id" ON "users" ("id");
        ';
        $result = Database::run($sql);

        $sql = '
        PRAGMA user_version = 1
        ';
        $result = Database::run($sql);

        //Database::commit();
        return true;
    }
}
