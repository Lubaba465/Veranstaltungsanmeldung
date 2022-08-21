<?php

class InitMigrations extends Migration
{
    /**
     * Insert all necessary tables into the database.
     *
     * @return void
     */
    function up()
    {
        $db = DBManager::get();

        $db->execute("CREATE TABLE  IF NOT EXISTS`VAPlannung` (
   `id` varchar(100) CHARACTER SET latin1 COLLATE latin1_bin NULL,
	`user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
    `sem_types_id` int(30) NOT NULL,
    `SWS` double NOT NULL,
  `Semester_ID` int(10) NOT NULL,
  `Titel` varchar(100) NOT NULL,
  `Lehrsprache` enum('deutsch','english','nach absprech mit den studierenden') DEFAULT 'english' NULL,
  `SGenerale` int(10) DEFAULT NULL,
    `status` enum('in Bearbeitung', 'fertig', 'noch nicht bearbeitet') DEFAULT 'noch nicht bearbeitet' NULL,
    `astatus` enum('in Bearbeitung', 'fertig', 'noch nicht bearbeitet') DEFAULT 'noch nicht bearbeitet' NULL,
		`institut_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NULL,
  `Nachhaltigkeit` tinyint(1) DEFAULT NULL,
  `Energierelevant` tinyint(1) DEFAULT NULL,
  `Dauer` enum('1 Semester','2 Semester') DEFAULT '1 Semester' NULL,
  `Turnus` enum('blockveranstaltung','wöchentlich','zweiwöchentlich','nach vereinbarung') DEFAULT 'wöchentlich' NULL,
  `Teilnehmer` int(10) DEFAULT NULL,
  `Anzahl` int(10) DEFAULT NULL,
    `start_date` varchar(500) DEFAULT 0 NULL,
  `VNummer` varchar(500) DEFAULT NULL,
  `AngZugang` varchar(500) DEFAULT NULL,
  `Ausstattung` varchar(500) DEFAULT NULL,
  `Wunschraum` varchar(500) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
    `chdate` bigint(20) DEFAULT 0 NULL,
	`mkdate` bigint(20) DEFAULT 0 NULL,
PRIMARY KEY (`id`)
);");


        $db->execute("CREATE TABLE  IF NOT EXISTS`vermod` (
    `id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modul_ID`varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
PRIMARY KEY (id,modul_id)
            );");

        $db->execute("CREATE TABLE  IF NOT EXISTS`veranstalter` (
    `id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `LVS` double NOT NULL,
  `Lehrauftrag` tinyint(1) DEFAULT NULL,
               PRIMARY KEY (`id`,`user_id`)
            );");
        $db->execute("CREATE TABLE  IF NOT EXISTS`tutor` (
    `id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `LVS` double NOT NULL,
               PRIMARY KEY (`id`,`user_id`)
            );");

        $db->execute("CREATE TABLE  IF NOT EXISTS`anspreachpartner` (
    `id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
              `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
               PRIMARY KEY (`id`,`user_id`)
            );");

    }

}
