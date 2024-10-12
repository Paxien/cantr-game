-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: localhost    Database: cantr_temp
-- ------------------------------------------------------
-- Server version	5.7.24-0ubuntu0.18.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `_converthistory`
--

DROP TABLE IF EXISTS `_converthistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_converthistory` (
  `note` int(11) NOT NULL,
  `player` int(11) NOT NULL,
  `encoding` varchar(10) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_converthistory`
--

LOCK TABLES `_converthistory` WRITE;
/*!40000 ALTER TABLE `_converthistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `_converthistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_converttemp`
--

DROP TABLE IF EXISTS `_converttemp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_converttemp` (
  `content` text CHARACTER SET utf8,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_converttemp`
--

LOCK TABLES `_converttemp` WRITE;
/*!40000 ALTER TABLE `_converttemp` DISABLE KEYS */;
/*!40000 ALTER TABLE `_converttemp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `_obj_notes_b`
--

DROP TABLE IF EXISTS `_obj_notes_b`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_obj_notes_b` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `contents` text CHARACTER SET latin1,
  `setting` tinyint(1) DEFAULT NULL,
  `encoding` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT 'unknown',
  `utf8title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `utf8contents` text CHARACTER SET utf8,
  `transfer` varbinary(40000) DEFAULT NULL,
  `transfertitle` varbinary(255) DEFAULT NULL,
  `converted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `encoding` (`encoding`),
  KEY `convstatus` (`encoding`,`converted`),
  KEY `TitleFTS` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_obj_notes_b`
--

LOCK TABLES `_obj_notes_b` WRITE;
/*!40000 ALTER TABLE `_obj_notes_b` DISABLE KEYS */;
/*!40000 ALTER TABLE `_obj_notes_b` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accepted_emails`
--

DROP TABLE IF EXISTS `accepted_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accepted_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(256) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='stores emails are accepted by our mailing list filter';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accepted_emails`
--

LOCK TABLES `accepted_emails` WRITE;
/*!40000 ALTER TABLE `accepted_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `accepted_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `access`
--

DROP TABLE IF EXISTS `access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access` (
  `player` mediumint(9) NOT NULL DEFAULT '0',
  `page` tinyint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`player`,`page`) COMMENT 'page'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access`
--

LOCK TABLES `access` WRITE;
/*!40000 ALTER TABLE `access` DISABLE KEYS */;
INSERT INTO `access` VALUES (118841,1),(118841,2),(118841,3),(118841,4),(118841,5),(118841,6),(118841,7),(118841,8),(118841,9),(118841,10),(118841,11),(118841,12),(118841,13),(118841,14),(118841,15),(118841,16),(118841,17),(118841,18),(118841,19),(118841,20),(118841,21),(118841,22),(118841,23),(118841,24),(118841,25),(118841,26),(118841,27),(118841,28),(118841,29),(118841,30),(118841,31),(118841,32),(118841,33),(118841,34),(118841,35),(118841,36),(118841,37),(118841,38),(118841,39),(118841,40),(118841,41),(118841,42),(118841,43),(118841,44),(118841,45),(118841,46);
/*!40000 ALTER TABLE `access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `access_types`
--

DROP TABLE IF EXISTS `access_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_types` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `description` varchar(150) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_types`
--

LOCK TABLES `access_types` WRITE;
/*!40000 ALTER TABLE `access_types` DISABLE KEYS */;
INSERT INTO `access_types` VALUES (1,'Allowed to lock the game'),(2,'Allowed to see and alter passwords of staff members'),(3,'Allowed to access database of players'),(4,'Allowed to alter assignments and privileges'),(5,'Allowed to manage raw material locations'),(6,'Allowed to alter passwords'),(7,'Allowed to alter animal placement'),(8,'Allowed to alter and add machine types'),(9,'Allowed to send and receive private messages'),(10,'Allowed to alter raw material types and associated tools'),(11,'Allowed to alter object types and access of vehicles on connection type'),(12,'Allowed to alter locking status of player account'),(13,'Allowed to post a message to all players'),(14,'Allowed to remove player accounts'),(15,'Allowed to alter locations table'),(16,'Allowed to view the list of locations'),(17,'Allowed to accept players'),(18,'Allowed to alter clothing types'),(19,'Allowed to view (non-public) statistics'),(20,'Allowed to access vehicle applet'),(21,'Allowed to access administrative maps'),(22,'Allowed to search notes and have access to limited version of character accounts'),(23,'Allowed to mail all players'),(24,'Allowed to manage advertisement overview'),(25,'Allowed to alter animal types'),(26,'Allowed to manage translations'),(27,'Allowed to access extended version of character accounts'),(29,'Allowed to alter clothing categories'),(30,'Allowed to search events'),(31,'Allowed to create polls and votes'),(32,'Allowed to check SQL error logs'),(33,'Allowed to use Cantr Explorer'),(34,'Allowed to view the research on players'),(35,'Allowed to manage the recruitment page and questionnaires'),(36,'Allowed to see database structure'),(37,'Allowed to alter credits'),(38,'Allowed to add files to test environment'),(39,'Allowed to add files to production environment'),(40,'Allowed to access database directly'),(41,'Allowed to read staff documentation'),(42,'Allowed to alter players email address'),(43,'Allowed to run util.tester in test environment'),(44,'Allowed to run util.tester in live environment'),(45,'Allowed to manage voting links (test)'),(46,'Allowed to manage voting links (live)'),(47,'Allowed to managing events groups'),(49,'Allowed to decode game urls'),(50,'Allowed to use Manual event creation tool'),(51,'Allowed to see travels timeline'),(52,'Allowed to see indirect object transfers report');
/*!40000 ALTER TABLE `access_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advert_report`
--

DROP TABLE IF EXISTS `advert_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advert_report` (
  `id` mediumint(8) unsigned DEFAULT NULL,
  `register` smallint(5) unsigned DEFAULT NULL,
  `name` tinytext CHARACTER SET latin1,
  `email` tinytext CHARACTER SET latin1,
  `reference` text CHARACTER SET latin1,
  `referrer` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `language` tinyint(4) DEFAULT '1',
  `country` tinytext CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advert_report`
--

LOCK TABLES `advert_report` WRITE;
/*!40000 ALTER TABLE `advert_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `advert_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advertisement`
--

DROP TABLE IF EXISTS `advertisement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisement` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `website` varchar(250) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `description` text CHARACTER SET latin1 NOT NULL,
  `contact` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `author` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `notes` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertisement`
--

LOCK TABLES `advertisement` WRITE;
/*!40000 ALTER TABLE `advertisement` DISABLE KEYS */;
/*!40000 ALTER TABLE `advertisement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animal_domesticated`
--

DROP TABLE IF EXISTS `animal_domesticated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_domesticated` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `from_animal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key - id from `animals`',
  `from_object` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key - id from `objects`',
  `from_location` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'foreign key - id from locations',
  `fullness` int(10) unsigned NOT NULL COMMENT 'state opposite to hunger',
  `specifics` text CHARACTER SET utf8 NOT NULL,
  `loyal_to` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'character id animal is loyal to or 0 if not',
  `loyalty` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '[0-10000] level of loyalty to char from col "loyal_to"',
  PRIMARY KEY (`id`),
  KEY `from_animal` (`from_animal`),
  KEY `from_object` (`from_object`),
  KEY `from_location` (`from_location`),
  KEY `loyal_to` (`loyal_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal_domesticated`
--

LOCK TABLES `animal_domesticated` WRITE;
/*!40000 ALTER TABLE `animal_domesticated` DISABLE KEYS */;
/*!40000 ALTER TABLE `animal_domesticated` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animal_domesticated_types`
--

DROP TABLE IF EXISTS `animal_domesticated_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_domesticated_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `of_animal_type` int(10) unsigned NOT NULL,
  `of_object_type` int(10) unsigned NOT NULL,
  `type_details` text CHARACTER SET latin1 NOT NULL COMMENT 'text data for d. animal type',
  `food_type` tinyint(3) unsigned NOT NULL COMMENT 'bitmask; 1: hay, 2: vegetables, 4: meat',
  `food_amount` int(11) unsigned NOT NULL,
  `tame_rules` text CHARACTER SET latin1 NOT NULL COMMENT 'info what things are needed to domesticate into that animal from wild type',
  `weight` int(11) unsigned NOT NULL,
  `can_be_loyal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - can be loyal to one person; 0 - not',
  PRIMARY KEY (`id`),
  UNIQUE KEY `of_animal_types` (`of_animal_type`),
  UNIQUE KEY `of_object_type` (`of_object_type`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal_domesticated_types`
--

LOCK TABLES `animal_domesticated_types` WRITE;
/*!40000 ALTER TABLE `animal_domesticated_types` DISABLE KEYS */;
INSERT INTO `animal_domesticated_types` VALUES (1,212,1281,'shearing_raws:wool>170>17>1000;shearing_tools:blade shears;butchering_raws:mutton>2400>120>2400,large bones>400>100>400,sinew>100>25>100',3,60,'raws:hay>120;days:2;success_chance:40',60000,1),(2,213,1282,'milking_raws:milk>20>4>320;milking_tools:bucket;shearing_raws:wool>200>20>1000;shearing_tools:blade shears;butchering_raws:mutton>2400>120>2400,small bones>200>50>200,large bones>250>100>250',3,70,'raws:hay>140;days:2;success_chance:70',60000,1),(3,214,1283,'milking_raws:milk>80>8>240;milking_tools:bucket;butchering_raws:beef>6000>150>2000,hide>1250>65>650,large bones>400>100>400,small bones>640>160>320,sinew>150>15>75',3,150,'raws:hay>300;days:3;success_chance:40',300000,1),(4,215,1285,'milking_raws:milk>50>5>200;milking_tools:bucket;shearing_raws:wool>150>15>900;shearing_tools:blade shears;butchering_raws:mutton>3600>120>2400,fur>500>50>500,hide>750>75>500,small bones>350>50>350,sinew>50>5>50',1,87,'raws:hay>175;days:2;success_chance:70',120000,1),(5,216,1286,'milking_raws:milk>20>4>120;milking_tools:bucket;shearing_raws:wool>250>25>800;shearing_tools:blade shears;butchering_raws:mutton>2400>120>2400,small bones>200>100>200,large bones>250>50>250',1,75,'raws:hay>150;days:2;success_chance:70',60000,1),(6,217,1287,'milking_raws:milk>100>18>300;milking_tools:bucket;butchering_raws:beef>10000>143>2500,hide>2500>100>1000,large bones>1500>50>500,sinew>300>15>100',1,130,'raws:hay>260;days:4;success_chance:50',250000,1),(7,218,1289,'milking_raws:milk>100>10>240;milking_tools:bucket;butchering_raws:beef>8000>100>2000,hide>2500>75>1000,large bones>750>30>250,sinew>160>5>160',1,80,'raws:hay>240;tools:lasso;days:5;success_chance:70',150000,1),(8,219,1290,'milking_raws:milk>90>18>270;milking_tools:bucket;butchering_raws:beef>9000>130>3000,hide>2600>100>1300,large bones>1000>40>500,sinew>250>10>150',1,130,'raws:hay>260;days:3;success_chance:25',250000,1),(9,220,1291,'milking_raws:milk>30>5>120;milking_tools:bucket;butchering_raws:mutton>3000>60>1000,hide>1250>40>500,large bones>500>20>250,small bones>900>30>450,sinew>150>5>75',1,53,'raws:hay>100;days:2;success_chance:60',50000,1),(10,221,1292,'milking_raws:milk>100>25>300;milking_tools:bucket;butchering_raws:beef>6000>150>2000,hide>2400>70>800,large bones>1000>25>400,sinew>250>10>150',1,150,'raws:hay>300;days:3;success_chance:60',300000,1),(11,222,1293,'milking_raws:milk>60>11>240;milking_tools:bucket;butchering_raws:beef>2400>96>2400,hide>1600>80>1600,large bones>800>50>800,sinew>150>15>150',1,70,'raws:hay>150;days:2;success_chance:70',100000,1),(12,223,1294,'milking_raws:milk>28>4>180;milking_tools:bucket;shearing_raws:wool>250>25>1000;shearing_tools:blade shears;butchering_raws:mutton>2400>120>2400,small bones>240>15>240,large bones>250>25>250',1,75,'raws:hay>150;days:2;success_chance:70',60000,1),(13,224,1295,'milking_raws:milk>100>23>400;milking_tools:bucket;butchering_raws:beef>11000>183>2400,hide>3000>60>1000,large bones>1500>30>500,sinew>200>5>80',1,180,'raws:hay>400;days:4;success_chance:60',360000,1),(14,225,1297,'milking_raws:milk>42>7>320;milking_tools:bucket;shearing_raws:wool>72>8>1000;shearing_tools:blade shears;butchering_raws:mutton>2800>112>1800,fur>500>25>500,hide>750>40>500,small bones>350>20>250,sinew>50>5>40',3,82,'raws:hay>160;days:2;success_chance:70',70000,1),(15,226,1298,'milking_raws:milk>20>4>160;milking_tools:bucket;shearing_raws:wool>130>13>1000;shearing_tools:blade shears;butchering_raws:mutton>3700>123>2000,fur>500>20>500,hide>750>25>500,large bones>400>20>400,sinew>80>8>80',1,88,'raws:hay>180;days:2;success_chance:50',92000,1),(16,227,1299,'shearing_raws:wool>250>25>1000;shearing_tools:blade shears;butchering_raws:mutton>4400>150>2200,large bones>1000>50>500,sinew>150>10>150',3,100,'raws:hay>200;days:2;success_chance:70',110000,1),(17,228,1300,'milking_raws:milk>12>1>200;milking_tools:bucket;shearing_raws:wool>42>7>1000;shearing_tools:blade shears;butchering_raws:mutton>1700>57>1200,small bones>250>15>250,large bones>250>15>250',1,33,'raws:hay>70;days:1;success_chance:50',43000,1),(18,229,1301,'milking_raws:milk>36>6>320;milking_tools:bucket;shearing_raws:wool>100>10>1000;shearing_tools:blade shears;butchering_raws:mutton>3700>90>1900,hide>750>20>400,small bones>350>10>300,sinew>50>5>50',1,90,'raws:hay>200;days:2;success_chance:50',93000,1),(19,230,1302,'milking_raws:milk>90>15>280;milking_tools:bucket;butchering_raws:beef>9600>192>2400,hide>2000>50>1000,large bones>1500>50>1000,sinew>350>10>250',1,125,'raws:hay>250;days:2;success_chance:70',240000,1),(20,231,1303,'milking_raws:milk>12>4>160;milking_tools:bucket;shearing_raws:wool>250>25>1000;shearing_tools:blade shears;butchering_raws:mutton>2400>120>2400,small bones>240>24>240,large bones>300>30>300',1,75,'raws:hay>150;days:2;success_chance:70',60000,1),(21,232,1304,'milking_raws:milk>25>5>200;milking_tools:bucket;shearing_raws:wool>150>15>1000;shearing_tools:blade shears;butchering_raws:mutton>4800>160>2400,small bones>200>20>200,large bones>200>20>200',1,88,'raws:hay>190;days:2;success_chance:70',120000,1),(22,233,1306,'milking_raws:milk>150>30>450;milking_tools:bucket;butchering_raws:beef>9000>130>2400,hide>1600>25>700,large bones>800>20>400,sinew>250>10>250',1,195,'raws:hay>400;days:4;success_chance:60',400000,1),(23,234,1308,'milking_raws:milk>120>30>240;milking_tools:bucket;butchering_raws:beef>12000>150>2000,hide>4000>50>1000,large bones>900>20>400,sinew>400>10>200',1,180,'raws:hay>360;days:4;success_chance:30',340000,1),(24,235,1309,'milking_raws:milk>75>15>300;milking_tools:bucket;butchering_raws:mutton>5000>100>2000,hide>2000>40>1000,large bones>1000>20>1000,sinew>350>10>350',1,100,'raws:hay>200;days:1;success_chance:70',200000,1),(25,236,1284,'butchering_raws:pork>9600>320>4800,hide>3600>120>1800,large bones>2800>140>1600,sinew>300>10>200',6,110,'raws:vegetable feed>220;days:2;success_chance:40',120000,1),(26,237,1288,'butchering_raws:pork>8000>267>4000,hide>3600>120>1800,large bones>2800>140>1600,sinew>300>10>200',6,80,'raws:vegetable feed>160;days:2;success_chance:40',80000,1),(27,238,1296,'butchering_raws:pork>9600>320>4800,hide>3600>120>1800,large bones>2800>140>1600,sinew>300>10>200',6,110,'raws:vegetable feed>220;days:2;success_chance:40',120000,1),(28,239,1305,'butchering_raws:pork>9200>310>4800,hide>3300>110>1800,large bones>2800>140>1600,sinew>300>10>200',6,100,'raws:vegetable feed>220;days:2;success_chance:20',100000,1),(29,240,1307,'butchering_raws:pork>9200>310>4800,hide>3300>110>1800,large bones>2800>140>1600,sinew>300>10>200',6,100,'raws:vegetable feed>220;days:2;success_chance:40',100000,1),(30,242,1389,'collecting_raws:eggs>45>3>600;butchering_raws:poultry>600>60>900,feathers>360>36>540,small bones>360>36>540,eggs>20>2>40',1,20,'raws:hay>40;days:1;success_chance:70',4000,1),(31,243,1390,'collecting_raws:eggs>60>4>600;butchering_raws:poultry>500>50>500,feathers>300>30>500,small bones>300>30>500,eggs>20>2>40',1,25,'raws:hay>50;days:1;success_chance:70',2000,1),(32,244,1391,'collecting_raws:eggs>60>4>750;butchering_raws:poultry>1000>100>1000,feathers>500>30>500,small bones>300>30>300,eggs>20>2>20',3,36,'raws:hay>72;days:1.5;success_chance:60',8000,1),(33,245,1392,'collecting_raws:eggs>90>6>540;butchering_raws:poultry>2200>147>1100,feathers>750>50>500,small bones>800>60>600,eggs>40>3>40',3,50,'raws:hay>100;days:2;success_chance:60',37000,1),(34,246,1393,'collecting_raws:eggs>90>6>400;butchering_raws:poultry>3300>165>1700,feathers>1000>50>500,small bones>1200>60>600,eggs>40>2>30',3,60,'raws:hay>120;days:2;success_chance:60',100000,1),(35,247,1394,'collecting_raws:eggs>40>2>400;butchering_raws:poultry>1000>33>1000,feathers>500>50>500,small bones>600>50>600,eggs>20>2>20',4,35,'raws:meat feed>70;days:1;success_chance:60',6500,1),(36,248,1395,'collecting_raws:eggs>45>3>450;butchering_raws:poultry>500>50>500,feathers>300>30>600,small bones>300>30>700,eggs>20>2>40',1,22,'raws:hay>44;days:1;success_chance:55',450,1),(37,249,1396,'collecting_raws:eggs>45>3>450;butchering_raws:poultry>500>50>500,feathers>300>30>600,small bones>300>30>700,eggs>20>2>40',1,22,'raws:hay>44;days:1;success_chance:55',450,1),(38,250,1397,'collecting_raws:eggs>45>3>600;butchering_raws:poultry>600>40>900,feathers>450>30>700,small bones>450>30>700,eggs>20>2>40',3,22,'raws:hay>44;days:1;success_chance:55',1000,1),(39,251,1398,'collecting_raws:eggs>45>3>600;butchering_raws:poultry>600>40>900,feathers>450>30>700,small bones>450>30>700,eggs>20>2>40',3,22,'raws:hay>44;days:1;success_chance:55',1000,1),(40,252,1399,'collecting_raws:eggs>45>3>600;butchering_raws:poultry>600>40>900,feathers>450>30>700,small bones>450>30>700,eggs>20>2>40',3,22,'raws:hay>44;days:1;success_chance:55',1000,1),(41,253,1400,'collecting_raws:eggs>60>3>750;butchering_raws:poultry>800>53>1200,feathers>600>60>900,small bones>450>30>900,eggs>20>2>40',3,25,'raws:hay>50;days:1;success_chance:55',1500,1),(42,254,1401,'collecting_raws:eggs>45>3>460;butchering_raws:poultry>1000>67>1000,feathers>1000>100>1000,small bones>450>30>700,eggs>20>2>40',3,30,'raws:hay>60;days:1;success_chance:70',5000,1),(43,255,1402,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>50>3>200,feathers>60>60>120,small bones>40>3>80,eggs>20>2>40',2,25,'raws:vegetable feed>50;days:1;success_chance:65',200,1),(44,256,1403,'collecting_raws:eggs>20>2>120;butchering_raws:poultry>100>7>200,feathers>150>15>300,small bones>100>10>200,eggs>10>1>20',2,25,'raws:vegetable feed>50;days:1;success_chance:65',1500,1),(45,257,1404,'collecting_raws:eggs>20>2>120;butchering_raws:poultry>75>5>150,feathers>50>5>100,small bones>40>4>80,eggs>10>1>20',3,30,'raws:hay>60;days:1;success_chance:60',1000,1),(46,259,1405,'collecting_raws:eggs>20>2>100;butchering_raws:poultry>200>13>300,feathers>200>13>300,small bones>100>10>200,eggs>10>1>20',2,25,'raws:vegetable feed>50;days:1;success_chance:60',300,1),(47,260,1406,'collecting_raws:eggs>10>1>120;butchering_raws:poultry>250>17>250,feathers>150>15>300,small bones>120>12>240,eggs>10>1>20',7,25,'raws:hay>50;days:1;success_chance:55',1000,1),(48,261,1407,'collecting_raws:eggs>60>4>720;butchering_raws:poultry>800>53>800,feathers>300>30>600,small bones>150>15>300,eggs>10>1>20',3,30,'raws:hay>60;days:1;success_chance:70',14000,1),(49,262,1408,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>400>27>400,feathers>400>27>400,small bones>250>25>250,eggs>10>1>20',4,40,'raws:meat feed>80;days:3;success_chance:45',4500,1),(50,263,1409,'collecting_raws:eggs>12>1>72;butchering_raws:poultry>400>27>400,feathers>360>36>360,small bones>160>16>160,eggs>10>1>20',4,35,'raws:meat feed>70;days:2;success_chance:40',1600,1),(51,266,1410,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>100>7>200,feathers>50>5>150,small bones>20>2>40,eggs>10>1>20',4,35,'raws:meat feed>70;days:2;success_chance:40',200,1),(52,267,1411,'collecting_raws:eggs>12>1>60;butchering_raws:poultry>400>27>400,feathers>400>27>400,small bones>150>25>150,eggs>10>1>20',4,45,'raws:meat feed>90;days:3;success_chance:40',3200,1),(53,268,1412,'collecting_raws:eggs>12>1>60;butchering_raws:poultry>350>23>350,feathers>350>23>350,small bones>200>20>200,eggs>10>1>20',4,40,'raws:meat feed>80;days:3;success_chance:40',2500,1),(54,269,1413,'collecting_raws:eggs>8>1>64;butchering_raws:poultry>500>33>500,feathers>300>20>300,small bones>250>25>250,eggs>10>1>20',4,50,'raws:meat feed>100;days:3;success_chance:35',5500,1),(55,270,1414,'collecting_raws:eggs>12>1>60;butchering_raws:poultry>300>20>300,feathers>200>20>200,small bones>150>15>150,eggs>10>1>20',4,40,'raws:meat feed>80;days:3;success_chance:40',1000,1),(56,271,1415,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>300>20>400,feathers>200>15>200,small bones>150>15>150,eggs>10>1>20',4,40,'raws:meat feed>80;days:2;success_chance:40',1500,1),(57,272,1416,'collecting_raws:eggs>10>1>84;butchering_raws:poultry>300>20>300,feathers>200>13>200,small bones>100>8>100,eggs>10>1>20',4,30,'raws:meat feed>80;days:2;success_chance:40',1600,1),(58,273,1417,'collecting_raws:eggs>12>1>72;butchering_raws:poultry>250>16>250,feathers>200>17>200,small bones>150>15>150,eggs>10>1>20',4,80,'raws:meat feed>80;days:3;success_chance:35',1000,1),(59,274,1418,'collecting_raws:eggs>8>1>120;butchering_raws:poultry>100>10>200,feathers>100>10>400,small bones>50>5>100,eggs>10>1>20',3,20,'raws:hay>40;days:0.75;success_chance:70',260,1),(60,275,1419,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>250>17>250,feathers>200>15>200,small bones>100>8>100,eggs>10>1>20',4,40,'raws:meat feed>80;days:2;success_chance:50',1000,1),(61,276,1420,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>300>20>300,feathers>200>17>200,small bones>100>8>100,eggs>10>1>20',7,30,'raws:hay>60;days:1.5;success_chance:60',1200,1),(62,277,1421,'collecting_raws:eggs>10>1>120;butchering_raws:poultry>100>10>200,feathers>40>4>80,small bones>20>2>40,eggs>10>1>20',4,20,'raws:meat feed>40;days:2;success_chance:40',180,1),(63,278,1422,'collecting_raws:eggs>10>1>80;butchering_raws:poultry>250>13>500,feathers>100>5>200,small bones>50>5>100,eggs>10>1>20',4,40,'raws:meat feed>80;days:2;success_chance:50',1000,1),(64,279,1471,'collecting_raws:eggs>30>1>100;butchering_raws:poultry>200>13>400,feathers>50>5>250,small bones>40>4>200,eggs>10>1>40',4,20,'raws:meat feed>40;days:1;success_chance:60',350,1),(65,280,1472,'collecting_raws:eggs>14>1>140;butchering_raws:poultry>60>6>120,feathers>30>3>60,small bones>20>2>40,eggs>10>1>20',3,20,'raws:hay>40;days:1;success_chance:60',120,1),(66,281,1473,'collecting_raws:eggs>15>1>120;butchering_raws:poultry>40>4>160,feathers>20>2>80,small bones>10>1>40,eggs>5>1>40',3,18,'raws:hay>36;days:1;success_chance:70',60,1),(67,282,1474,'collecting_raws:eggs>15>1>120;butchering_raws:poultry>200>14>400,feathers>40>3>80,small bones>20>2>40,eggs>10>1>40',7,20,'raws:hay>40;days:1;success_chance:70',1000,1),(68,283,1475,'collecting_raws:eggs>15>1>75;butchering_raws:poultry>400>40>600,feathers>200>20>400,small bones>120>12>240,eggs>15>2>30',4,30,'raws:meat feed>60;days:1;success_chance:60',3000,1),(69,284,1476,'collecting_raws:eggs>12>1>60;butchering_raws:poultry>200>14>400,feathers>80>10>200,small bones>80>10>200,eggs>10>1>20',2,30,'raws:vegetable feed>60;days:1;success_chance:60',400,1),(70,285,1477,'collecting_raws:eggs>10>1>100;butchering_raws:poultry>25>3>100,feathers>10>2>40,small bones>5>1>20,eggs>5>1>20',2,15,'raws:vegetable feed>30;days:0.75;success_chance:80',50,1),(71,286,1478,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>800>54>800,feathers>240>24>240,small bones>100>10>100,eggs>20>2>40',4,30,'raws:meat feed>60;days:1.5;success_chance:50',6000,1),(72,287,1479,'collecting_raws:eggs>12>1>90;butchering_raws:poultry>50>5>100,feathers>20>2>50,small bones>10>1>20,eggs>5>1>10',2,20,'raws:vegetable feed>40;days:1;success_chance:60',100,1),(73,288,1480,'collecting_raws:eggs>15>1>35;butchering_raws:poultry>600>30>600,feathers>200>20>200,small bones>100>10>150,eggs>10>2>20',4,35,'raws:meat feed>70;days:1;success_chance:35',8000,1),(74,289,1481,'collecting_raws:eggs>10>1>40;butchering_raws:poultry>100>10>200,feathers>100>10>200,small bones>100>10>200,eggs>10>1>20',4,37,'raws:meat feed>74;days:1;success_chance:45',800,1),(75,290,1482,'collecting_raws:eggs>10>1>60;butchering_raws:poultry>50>5>200,feathers>25>3>50,small bones>25>3>60,eggs>10>2>20',7,20,'raws:hay>40;days:1;success_chance:70',100,1),(76,291,1483,'collecting_raws:eggs>60>4>600;butchering_raws:poultry>500>50>500,feathers>300>30>500,small bones>300>30>500,eggs>20>2>40',7,27,'raws:hay>54;days:2;success_chance:60',2400,1),(77,292,1484,'collecting_raws:eggs>6>1>24;butchering_raws:poultry>500>34>350,feathers>200>20>200,small bones>200>20>200,eggs>10>1>20',4,35,'raws:meat feed>70;days:3;success_chance:35',6000,1),(78,293,1485,'collecting_raws:eggs>10>1>30;butchering_raws:poultry>750>50>750,feathers>250>25>250,small bones>200>20>400,eggs>20>2>40',4,25,'raws:meat feed>50;days:2;success_chance:50',5000,1),(79,294,1596,'milking_raws:milk>50>8>200;milking_tools:bucket;butchering_raws:beef>3200>107>2400,hide>1200>60>900,large bones>800>40>800,small bones>640>80>640,sinew>150>15>120',3,60,'raws:hay>120;tools:lasso;days:4;success_chance:70',100000,1),(80,295,1597,'milking_raws:milk>50>10>300;milking_tools:bucket;butchering_raws:beef>4800>120>2400,hide>1600>60>1000,large bones>1200>40>800,small bones>900>60>600,sinew>300>15>200',3,75,'raws:hay>150;tools:lasso;days:5;success_chance:70',140000,1),(81,296,1598,'milking_raws:milk>50>8>240;milking_tools:bucket;butchering_raws:beef>3600>120>2400,hide>800>60>800,large bones>600>40>600,small bones>600>40>600,sinew>150>15>150',3,70,'raws:hay>140;tools:lasso;days:5;success_chance:70',130000,1),(82,297,1599,'milking_raws:milk>50>6>300;milking_tools:bucket;butchering_raws:beef>2700>108>2700,hide>1000>60>1500,large bones>800>50>1200,small bones>800>50>120,sinew>150>15>200',3,50,'raws:hay>100;tools:lasso;days:4;success_chance:70',80000,1),(83,298,1600,'milking_raws:milk>75>15>225;milking_tools:bucket;butchering_raws:beef>5000>125>3000,hide>1500>75>1500,large bones>800>40>800,small bones>800>40>800,sinew>250>15>250',3,75,'raws:hay>150;tools:lasso;days:5;success_chance:70',140000,1);
/*!40000 ALTER TABLE `animal_domesticated_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animal_interaction`
--

DROP TABLE IF EXISTS `animal_interaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_interaction` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `perpetrator_type` smallint(5) unsigned NOT NULL,
  `perpetrator_id` int(8) unsigned NOT NULL,
  `victim_type` smallint(5) unsigned NOT NULL,
  `victim_id` int(8) unsigned NOT NULL,
  `interaction_type` smallint(5) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal_interaction`
--

LOCK TABLES `animal_interaction` WRITE;
/*!40000 ALTER TABLE `animal_interaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `animal_interaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animal_types`
--

DROP TABLE IF EXISTS `animal_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_types` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` tinytext CHARACTER SET latin1,
  `travel_chance` float unsigned DEFAULT NULL,
  `reproduction_chance` float unsigned DEFAULT NULL,
  `area_types` tinytext CHARACTER SET latin1,
  `attack_chance` smallint(5) unsigned DEFAULT NULL,
  `attack_force` smallint(5) unsigned DEFAULT NULL,
  `strength` smallint(5) unsigned DEFAULT NULL,
  `armour` smallint(5) unsigned DEFAULT NULL,
  `resources` tinytext CHARACTER SET latin1,
  `max_in_location` mediumint(9) NOT NULL DEFAULT '50',
  `domesticable_into` smallint(5) unsigned DEFAULT NULL COMMENT 'id in animal_types of domesticated version of that animal',
  PRIMARY KEY (`id`),
  KEY `domesticable_into` (`domesticable_into`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal_types`
--

LOCK TABLES `animal_types` WRITE;
/*!40000 ALTER TABLE `animal_types` DISABLE KEYS */;
INSERT INTO `animal_types` VALUES (1,'wolf',1,4,'grassland,mountains,hills,forest,jungle,beach,plains,tundra',15,32,80,0,'meat>250,fur>150,large bones>75,small bones>50,sinew>18,fresh dung>100',11,NULL),(2,'rabbit',8,15,'grassland,hills,swamp,forest,beach,plains,tundra',0,0,15,0,'meat>100,fur>80,small bones>40',20,NULL),(3,'horse',1,4,'grassland,hills,desert,beach,plains',2,10,125,0,'meat>625,fresh dung>500,hide>400,large bones>300,sinew>35',20,295),(4,'cow',1,4,'grassland,hills,plains',1,5,120,2,'meat>600,fresh dung>480,hide>800,large bones>300,sinew>50,milk>150',30,221),(5,'scorpion',5,10,'hills,desert,forest,jungle',16,20,30,0,'',20,NULL),(6,'crocodile',4,7,'swamp',12,42,130,15,'meat>650,crocodile hide>400,large bones>200,eggs>30',14,NULL),(7,'deer',4,7,'grassland,hills,desert,swamp,forest,beach,plains',3,8,90,0,'meat>450,fresh dung>300,hide>250,large bones>100,small bones>180,sinew>30',30,NULL),(8,'hawk',1,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,270),(9,'mountain goat',1,3,'mountains,hills',6,12,70,0,'meat>400,wool>100,hide>150,small bones>70,sinew>9,milk>50,fresh dung>200',30,229),(10,'pelican',8,15,'grassland,hills,swamp,forest,beach',2,8,40,0,'meat>100,feathers>120,small bones>60,eggs>30',40,286),(11,'swamp snake',8,15,'swamp',16,10,35,0,'meat>100,small bones>30,snakeskin>120,eggs>10',15,NULL),(12,'desert snake',8,15,'mountains,hills,desert',10,12,55,2,'meat>150,snakeskin>120,small bones>30,eggs>10',15,NULL),(13,'toucan',4,7,'grassland,swamp,forest,jungle,beach',0,5,50,0,'meat>120,feathers>100,small bones>65,eggs>30',30,259),(14,'whale',10,3,'beach',2,50,255,30,'meat>10000,large bones>4000',20,NULL),(15,'wild boar',2,3,'grassland,hills,swamp,forest,jungle',15,30,140,5,'meat>700,fresh dung>250,hide>300,large bones>280,sinew>20',20,240),(16,'bear',1,4,'grassland,mountains,hills,swamp,forest,beach,tundra',10,30,200,5,'meat>1000,fresh dung>500,fur>450,large bones>250,sinew>60',10,NULL),(21,'racoon',1,4,'grassland,hills,swamp,forest,beach,plains',3,5,35,0,'meat>120,fur>80,small bones>50',15,NULL),(24,'cheetah',1,4,'grassland,desert,plains',2,28,90,0,'meat>450,fresh dung>250,fur>120,large bones>50,small bones>150,sinew>15',10,NULL),(25,'turkey',4,7,'grassland,hills,forest,jungle,beach,plains',10,4,35,0,'meat>200,feathers>60,small bones>50,eggs>40',20,244),(26,'gorilla',1,4,'grassland,forest,jungle,beach',12,22,155,5,'meat>775,fresh dung>300,fur>200,large bones>300',7,NULL),(27,'sheep',5,10,'grassland,hills,plains',1,4,80,0,'meat>400,wool>300,small bones>40,large bones>50,milk>40,fresh dung>220',30,231),(28,'pigeon',8,15,'grassland,hills,forest,beach,plains',0,5,15,0,'meat>50,feathers>20,small bones>30,eggs>10',30,274),(29,'rhino',1,4,'grassland,forest,jungle,plains',8,30,220,18,'meat>1100,fresh dung>880,hide>400,large bones>350,tusk>300',7,0),(30,'desert tortoise',4,7,'mountains,desert',0,3,75,25,'meat>150,tortoiseshell>250,eggs>40',30,NULL),(31,'lion',4,7,'grassland,hills,desert,forest,jungle,beach,plains',8,38,180,0,'meat>900,fresh dung>400,hide>420,large bones>250,sinew>40',8,NULL),(32,'scarab',4,7.5,'grassland,mountains,hills,desert,forest,jungle,beach,plains',11,11,70,17,'shell>200,meat>75',17,NULL),(33,'elephant',1,4,'grassland,hills,desert,forest,jungle,beach,plains',8,36,320,16,'meat>1600,fresh dung>1280,hide>1200,large bones>950,ivory>250',8,NULL),(34,'ostrich',4,7.5,'grassland,hills,desert,forest,beach,plains',2,10,90,0,'meat>450,feathers>100,small bones>170,eggs>40',20,246),(35,'kangaroo',4,7,'grassland,hills,desert,forest,beach,plains',1,9,85,0,'meat>425,fresh dung>330,hide>120,small bones>120,sinew>50',20,NULL),(36,'zebra',4,7,'grassland,hills,desert,forest,jungle,beach,plains',2,8,110,0,'meat>550,fresh dung>400,hide>250,large bones>120,small bones>100,sinew>20',20,298),(37,'hippopotamus',1,4,'grassland,swamp,forest,jungle,beach',4,30,200,8,'meat>1000,fresh dung>800,hide>800,large bones>450',20,0),(38,'leopard',4,7.5,'grassland,hills,desert,forest,jungle,beach,plains',8,24,70,0,'meat>230,fur>150,small bones>90,sinew>10,fresh dung>120',15,NULL),(39,'coyote',4,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains',5,18,65,0,'meat>150,fur>60,small bones>100,fresh dung>100',12,NULL),(40,'koala',1,4,'forest,jungle',0,6,55,0,'meat>100,fur>100,small bones>50',20,NULL),(41,'moose',1,4,'grassland,hills,forest,beach,plains,tundra',3,12,130,0,'meat>650,fresh dung>450,hide>350,large bones>120,small bones>200,sinew>60',18,NULL),(42,'buffalo',1,4,'grassland,hills,forest,plains',2,15,220,8,'meat>1100,fresh dung>880,hide>1000,large bones>300,sinew>70,milk>80',30,217),(43,'dog',4,7.5,'grassland,hills,forest,beach,plains',1,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(44,'lemur',1,4,'forest,jungle',1,3,40,0,'meat>120,fur>80,small bones>50',30,NULL),(45,'lemming',8,15,'grassland,hills,forest,beach',0,2,22,0,'meat>60,fur>20,small bones>20',40,NULL),(46,'mammoth',1,4,'grassland,hills,plains,tundra',8,36,320,16,'meat>1600,fresh dung>1280,hide>1500,large bones>1150,ivory>350',9,0),(48,'albatross',3,7,'grassland,mountains,hills,beach',1,9,110,0,'meat>325,feathers>100,small bones>150,eggs>30',30,293),(49,'bluejay',4,7,'grassland,mountains,hills,forest,plains',1,4,15,0,'meat>25,feathers>30,small bones>20,eggs>16',30,290),(50,'buzzard',4,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains',2,10,80,0,'meat>150,feathers>150,small bones>100,eggs>34',15,289),(51,'condor',1,4,'mountains',2,8,100,0,'meat>200,feathers>75,small bones>125,eggs>40',15,288),(52,'eagle',1,4,'grassland,mountains,hills,forest,jungle,beach,plains,tundra',6,20,90,0,'meat>150,feathers>150,small bones>100,eggs>34',15,262),(53,'falcon',1,4,'grassland,mountains,hills,desert,forest,beach,plains',1,12,40,0,'meat>50,feathers>20,small bones>30,eggs>10',15,266),(54,'flamingo',4,7,'swamp',0,1,15,0,'meat>80,feathers>75,small bones>40,eggs>20',30,NULL),(55,'blue-heron',1,4,'swamp,beach',0,0,30,0,'meat>100,feathers>60,small bones>60,eggs>20',20,291),(56,'owl',1,4,'mountains,forest,tundra',2,15,70,0,'meat>120,feathers>100,small bones>80,eggs>30',15,272),(57,'peacock',4,7,'grassland,hills,forest,plains',0,0,35,0,'meat>100,feathers>300,small bones>60,eggs>20',30,254),(58,'penguin',4,7,'hills,beach,tundra',0,0,35,0,'meat>100,feathers>50,small bones>60,eggs>20',30,247),(59,'pheasant',4,7,'forest',0,0,25,0,'meat>50,feathers>30,small bones>30,eggs>10',30,250),(60,'raven',4,7,'grassland,mountains,hills,forest,beach,plains',1,1,30,0,'meat>50,feathers>20,small bones>30,eggs>10',20,276),(61,'roadrunner',1,4,'desert',0,0,60,0,'meat>50,feathers>20,small bones>30,eggs>10',20,284),(62,'robin',4,7,'grassland,mountains,hills,forest,plains',0,0,15,0,'meat>25,feathers>10,small bones>15,eggs>10',20,285),(63,'seagull',4,7,'grassland,mountains,hills,beach,plains',5,1,30,0,'meat>50,feathers>20,small bones>30,eggs>10',30,282),(64,'white-stork',4,7,'grassland,hills,desert,swamp,forest,jungle,beach,plains',1,6,35,0,'meat>100,feathers>50,small bones>60,eggs>20',20,283),(65,'woodpecker',4,7,'forest,jungle',0,0,40,0,'meat>50,feathers>20,small bones>30,eggs>10',20,NULL),(66,'redwinged-blackbird',4,7,'grassland,hills,swamp',0,0,18,0,'meat>25,feathers>10,small bones>15,eggs>10',20,281),(70,'donkey',1,4,'grassland,hills,desert,forest,beach,plains',4,10,62,0,'meat>310,fresh dung>248,hide>200,large bones>150,sinew>17',20,294),(71,'weasel',4,7.5,'grassland,hills,forest,plains',2,5,40,0,'meat>150,fur>70,small bones>70',20,NULL),(75,'iguana',5,10,'grassland,mountains,hills,forest,jungle,beach,plains',1,3,40,3,'meat>120,hide>100,small bones>30,eggs>10',30,NULL),(76,'cobra',8,15,'grassland,swamp,plains',10,17,65,2,'meat>160,snakeskin>130,small bones>35,eggs>10',14,NULL),(77,'crab',8,15,'beach',5,5,90,12,'meat>20,shell>100',30,NULL),(78,'saltwater crocodile',4,7,'beach',14,48,150,15,'meat>750,crocodile hide>500,large bones>250,eggs>30',5,NULL),(79,'hamster',15,15,'grassland,plains',3,3,15,0,'meat>70,fur>60,small bones>30',10,NULL),(80,'mountain coati',1,4,'mountains',0,0,35,0,'meat>120,fur>100,small bones>50',25,NULL),(81,'forest coati',1,4,'forest,jungle',0,0,35,0,'meat>110,fur>80,small bones>50',25,NULL),(82,'macaw',1,4,'forest,jungle',2,3,20,0,'meat>53,feathers>33,small bones>33,eggs>12',30,256),(83,'cockatoo',1,2,'forest,jungle',1,2,20,0,'meat>47,feathers>24,small bones>24,eggs>10',20,257),(84,'tree python',8,15,'forest,jungle',5,10,120,2,'meat>113,snakeskin>100,small bones>25,eggs>10',18,NULL),(85,'water monitor',8,15,'grassland',5,20,120,7,'meat>600,hide>320,large bones>200,sinew>30,fresh dung>300,eggs>40',14,NULL),(86,'do-do',3,4,'grassland,hills,forest,jungle,plains',0,0,45,0,'meat>300,feathers>75,small bones>100,eggs>20',30,261),(87,'wombat',1,4,'grassland,hills,forest,plains',5,10,45,0,'meat>150,fur>100,small bones>70',20,NULL),(88,'mountain lion',4,7.5,'mountains,hills,swamp,forest',15,20,150,0,'meat>750,hide>320,large bones>200,sinew>30,fresh dung>300',9,NULL),(89,'llama',1,4,'mountains,hills',3,7,110,0,'meat>550,wool>400,large bones>200,sinew>30,fresh dung>440',30,227),(90,'toad',8,15,'forest,jungle',0,0,10,0,'meat>15',30,NULL),(91,'gazelle',4,7,'grassland,hills,plains',1,6,85,0,'meat>425,fresh dung>300,hide>225,large bones>100,small bones>180,sinew>30',20,NULL),(92,'cape buffalo',1,4,'grassland,hills,forest,plains',15,10,143,8,'meat>715,fresh dung>572,hide>650,large bones>195,sinew>46,milk>52',13,219),(93,'pygmy rhino',1,4,'forest',4,22,110,18,'meat>550,fresh dung>440,hide>200,large bones>175,sinew>40,tusk>150',16,NULL),(94,'giraffe',1,4,'grassland,desert,forest,plains',1,15,150,0,'meat>750,fresh dung>600,hide>600,large bones>475,sinew>50',18,NULL),(95,'spotted lion',4,7,'grassland,hills',8,30,120,0,'meat>600,fresh dung>400,hide>420,large bones>250,sinew>40',8,NULL),(96,'sidewinder',8,15,'desert',10,14,12,2,'meat>160,snakeskin>130,small bones>33,eggs>10',15,NULL),(97,'mouflon',5,10,'grassland,mountains,hills,plains',2,6,85,0,'meat>425,wool>250,small bones>40,large bones>50,milk>40,fresh dung>220',30,228),(98,'elk',1,4,'grassland,forest',3,10,110,0,'meat>550,fresh dung>375,hide>300,small bones>190,sinew>45',20,NULL),(99,'snow leopard',4,7.5,'mountains',7,18,65,0,'meat>200,fur>130,small bones>70,sinew>10,fresh dung>100',16,NULL),(100,'camel',1,4,'desert',3,8,113,4,'meat>565,fresh dung>452,hide>512,large bones>154,sinew>36,milk>41',20,218),(101,'dire wolf',3,4,'grassland,mountains,hills,swamp,forest,plains,tundra',21,41,104,0,'meat>325,fresh dung>130,fur>195,large bones>98,small bones>65,sinew>23',11,NULL),(102,'squirrel',8,15,'grassland,hills,forest',0,0,15,0,'meat>80,fur>75,small bones>40',30,NULL),(103,'red-tailed hawk',1,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,275),(104,'white-tailed hawk',1,4,'grassland,hills,desert,beach,plains',1,13,55,0,'meat>95,feathers>95,small bones>75,eggs>18',15,278),(105,'silver pheasant',4,7,'forest',0,0,23,0,'meat>50,feathers>30,small bones>30,eggs>12',30,253),(106,'copper pheasant',4,7,'forest',0,0,25,0,'meat>50,feathers>30,small bones>30,eggs>10',30,251),(107,'golden pheasant',4,7,'forest',0,0,27,0,'meat>50,feathers>30,small bones>30,eggs>10',30,252),(108,'spotted hyena',4,7,'grassland,hills,desert',12,28,71,0,'meat>222,hide>133,large bones>67,small bones>44,sinew>16,fresh dung>89',30,NULL),(109,'black bear',1,4,'swamp,forest',5,30,120,5,'meat>600,fur>450,large bones>250,sinew>60,fresh dung>500',12,NULL),(110,'speckled tortoise',3,7,'grassland,hills,forest',0,0,16,25,'meat>150,eggs>40,tortoiseshell>250',30,NULL),(111,'quail',8,15,'grassland,hills,forest,plains',0,0,21,0,'meat>50,feathers>30,small bones>30,eggs>20',30,280),(112,'black sheep',5,10,'grassland,hills,plains',2,5,90,0,'meat>450,wool>312,small bones>40,large bones>50,milk>40,fresh dung>220',30,216),(113,'dwarf cow',3,4,'grassland,plains',1,0,80,1,'meat>400,fresh dung>320,hide>400,large bones>198,sinew>33,milk>99',30,222),(114,'mule deer',4,7,'grassland,mountains,hills,desert,swamp,forest,plains',3,6,70,0,'meat>350,fresh dung>264,hide>220,large bones>88,small bones>154,sinew>26',30,NULL),(115,'jack rabbit',3,5,'grassland,hills,forest',0,3,15,0,'meat>100,fur>80,small bones>40',20,NULL),(116,'striped hyena',4,7.5,'grassland,hills,desert,forest',12,15,60,0,'meat>222,hide>133,large bones>67,small bones>44,sinew>16,fresh dung>89',7,NULL),(117,'water buffalo',1,1,'grassland,hills,forest,jungle',8,33,120,2,'meat>600,fresh dung>480,hide>800,large bones>300,sinew>50,milk>150',30,233),(118,'antelope',3,5,'grassland,hills,forest',2,8,90,0,'meat>450,hide>250,large bones>100,small bones>180,sinew>30,fresh dung>300,milk>30',30,214),(120,'tiger',1,4,'grassland,hills,forest,jungle',18,30,200,5,'meat>1000,fresh dung>500,fur>450,large bones>250,sinew>60',10,NULL),(121,'ocelot',1,1.25,'grassland,hills,forest',3,5,35,0,'meat>120,fur>80,small bones>50',20,NULL),(122,'warthog',4,7,'grassland,hills,forest',15,30,140,5,'meat>700,fresh dung>250,hide>300,large bones>280,sinew>20',13,239),(123,'jackrabbit',8,15,'grassland,hills,forest',0,0,15,0,'meat>100,fur>80,small bones>40',20,NULL),(124,'alpaca',1,4,'grassland,mountains,hills',5,0,60,0,'meat>307,wool>650,large bones>82,sinew>13,fresh dung>205 ',30,212),(125,'rattlesnake',8,15,'mountains,hills',8,12,40,2,'meat>150,snakeskin>120,small bones>30,eggs>10',15,NULL),(126,'dingo',4,7.5,'grassland,hills,forest',12,32,80,0,'meat>250,fur>150,large bones>75,small bones>50,sinew>18,fresh dung>100',12,NULL),(127,'kookaburra',4,7,'grassland,hills,forest',0,0,45,0,'meat>100,feathers>100,small bones>80,eggs>20',15,279),(128,'bearded pig',3,5,'grassland,hills,forest',8,30,140,5,'meat>700,fresh dung>250,hide>300,large bones>280,sinew>20',10,236),(129,'emu',4,7.5,'grassland,hills,forest',4,2,90,0,'meat>450,feathers>100,small bones>170,eggs>40',20,245),(130,'bushpig',4,7,'hills,forest',8,30,140,5,'meat>700,fresh dung>250,hide>300,large bones>280,sinew>20',12,237),(131,'spectacled bear',1,4,'hills,forest',8,30,200,5,'meat>1000,fresh dung>500,fur>450,large bones>250,sinew>60',7,NULL),(132,'bearded vulture',1,4,'grassland,mountains,hills,forest,jungle,beach',1,15,60,0,'meat>100,small bones>80,feathers>100,eggs>20',15,292),(133,'rainbow lorikeet',1,4,'jungle,beach',0,0,15,0,'meat>25,feathers>30,small bones>20,eggs>16',40,255),(134,'golden eagle',1,4,'grassland,mountains,hills,forest,beach',6,20,90,0,'meat>150,feathers>150,small bones>100,eggs>34',15,267),(135,'yak',1,4,'grassland,mountains,hills,plains',1,15,220,8,'meat>1100,fresh dung>880,hide>1000,large bones>300,sinew>70,milk>80',30,235),(136,'musk ox',1,4,'grassland,hills,forest,plains,tundra',1,15,220,8,'meat>1100,fresh dung>880,hide>1000,large bones>300,sinew>70,milk>80',24,230),(137,'rhinoceros beetle',8,15,'grassland,mountains,hills,desert,forest,jungle,beach,plains',0,11,70,17,'shell>200,meat>75',17,NULL),(138,'stag beetle',8,15,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',10,11,70,17,'shell>200,meat>75',17,NULL),(139,'mastodon',1,4,'grassland,hills,forest,beach,plains,tundra',1,38,320,16,'meat>1600,fresh dung>1280,hide>1500,large bones>1150,ivory>350',9,NULL),(140,'peregrine falcon',4,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,273),(141,'gyrfalcon',4,7,'mountains,hills,beach,tundra',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,268),(142,'osprey',4,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,271),(143,'grizzly bear',1,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',10,30,200,5,'meat>1000,fresh dung>500,fur>450,large bones>250,sinew>60',10,NULL),(144,'cave bear',1,4,'mountains,hills,forest',18,30,200,5,'meat>1000,fresh dung>500,fur>450,large bones>250,sinew>60',10,NULL),(145,'arctic hare',8,15,'mountains,hills,forest,tundra',0,0,15,0,'meat>100,fur>80,small bones>40',10,NULL),(146,'clouded leopard',4,7.5,'grassland,hills,forest',6,24,70,0,'meat>230,fur>150,small bones>90,sinew>10,fresh dung>120',10,NULL),(147,'snow sheep',5,10,'mountains,hills',1,4,80,0,'meat>400,wool>300,small bones>40,large bones>50,milk>40,fresh dung>220',30,232),(148,'prairie dog',8,15,'grassland',0,3,15,0,'meat>100,fur>80,small bones>40',30,NULL),(149,'flat-tailed sheep',5,10,'grassland,hills',1,4,80,0,'meat>400,wool>300,small bones>40,large bones>50,milk>40,fresh dung>220',30,223),(150,'red fox',4,7.5,'grassland,hills,desert,swamp,forest,beach,plains,tundra',1,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(151,'wisent',1,4,'grassland,hills,forest,plains',1,15,220,8,'meat>1100,fresh dung>880,hide>1000,large bones>300,sinew>70,milk>80',13,234),(152,'grey partridge',4,7,'grassland,hills',0,0,15,0,'meat>50,feathers>20,small bones>30,eggs>10',20,248),(153,'wildebeest',1,4,'grassland,hills,forest',4,10,125,0,'meat>625,fresh dung>500,hide>400,large bones>300,sinew>35',30,NULL),(154,'rat',8,15,'hills,forest',100,1,15,0,'meat>100,fur>80,small bones>40',30,NULL),(155,'white rhino',1,4,'grassland,forest,jungle,beach,plains',9,30,220,18,'meat>1100,fresh dung>880,hide>400,large bones>350,tusk>300',7,0),(156,'sabre-toothed cat',1,4,'grassland,hills,desert,forest,jungle,beach,plains',8,30,180,0,'meat>900,fresh dung>400,hide>420,large bones>250,sinew>40',5,0),(157,'desert hare',8,15,'grassland,desert',0,0,15,0,'meat>100,fur>80,small bones>40',20,NULL),(158,'spurred tortoise',4,7.5,'desert',0,0,75,25,'meat>150,tortoiseshell>250,eggs>40',20,NULL),(159,'titan beetle',8,15,'grassland,hills,forest,jungle,plains',10,11,70,17,'shell>200,meat>75',17,NULL),(160,'raccoon dog',4,7.5,'grassland,mountains,hills,forest,jungle,beach,plains',1,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(161,'brown hare',8,15,'grassland,hills,forest',0,0,15,0,'meat>100,fur>80,small bones>40',20,NULL),(162,'partridge',4,7,'grassland,hills',0,0,15,0,'meat>50,feathers>20,small bones>30,eggs>10',20,249),(163,'angora goat',4,7,'grassland,mountains,hills',6,4,80,0,'meat>400,wool>600,small bones>40,large bones>50,milk>40,fresh dung>220',30,213),(164,'giant deer',1,4,'grassland,hills,forest',3,16,120,0,'meat>600,fresh dung>480,hide>500,large bones>400,small bones>180,sinew>60',20,NULL),(165,'black rhino',1,4,'grassland,forest,jungle,plains',12,30,220,18,'meat>1100,fresh dung>880,hide>400,large bones>350,tusk>300',15,0),(166,'marmot',4,7.5,'grassland,mountains,hills,forest',0,0,15,0,'meat>100,fur>80,small bones>40',20,NULL),(167,'badger',8,15,'grassland,hills,forest,plains',6,16,45,0,'meat>120,fur>80,small bones>50',30,NULL),(171,'crow',4,7,'grassland,mountains,hills,desert,swamp,forest,beach,plains',1,1,45,0,'meat>50,feathers>30,small bones>30,eggs>10',20,260),(172,'mountain tortoise',4,7,'mountains,desert',0,0,75,25,'meat>150,tortoiseshell>250,eggs>40',30,NULL),(173,'bighorn sheep',5,10,'mountains,hills,desert',6,12,70,0,'meat>400,fur>100,hide>150,small bones>70,sinew>9,milk>50,fresh dung>200',30,215),(174,'axis deer',4,7,'grassland,forest',3,8,90,0,'meat>450,fresh dung>300,hide>250,large bones>100,small bones>180,sinew>30',30,NULL),(175,'red deer',4,7,'grassland,hills,desert,swamp,forest,beach,plains',3,8,90,0,'meat>450,fresh dung>300,hide>250,large bones>100,small bones>180,sinew>30',30,NULL),(176,'alligator',4,7,'swamp',10,42,130,15,'meat>650,crocodile hide>400,large bones>200,eggs>30',14,NULL),(177,'goat',4,7,'grassland,hills,desert,forest,plains',6,12,70,0,'meat>400,fur>100,hide>150,small bones>70,sinew>9,milk>50,fresh dung>200',30,225),(178,'giant forest hog',4,7,'forest',13,30,140,5,'meat>700,fresh dung>250,hide>300,large bones>280,sinew>20',13,238),(179,'gaur',1,4,'grassland,hills,plains',1,15,120,2,'meat>600,fresh dung>480,hide>800,large bones>300,sinew>50,milk>150',15,224),(180,'fallow deer',4,7,'grassland,hills,desert,swamp,forest,beach,plains',3,8,90,0,'meat>450,fresh dung>300,hide>250,large bones>100,small bones>180,sinew>30',30,NULL),(181,'black goose',4,7,'grassland,swamp',7,2,25,0,'meat>50,feathers>30,small bones>30,eggs>10',30,242),(182,'grey fox',4,7.5,'grassland,mountains,hills,desert,swamp,forest,beach,plains',2,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(183,'caiman',4,7,'swamp',10,22,80,15,'meat>400,crocodile hide>200,large bones>100,eggs>15',17,NULL),(184,'chicken',8,15,'grassland,hills,swamp,forest',1,3,25,0,'meat>50,feathers>30,small bones>30,eggs>10',30,243),(185,'pika',8,15,'mountains,hills',0,0,15,0,'meat>100,fur>80,small bones>40',30,NULL),(186,'ibex',1,4,'mountains,hills',6,12,70,0,'meat>400,fur>100,hide>150,large bones>70,sinew>9,milk>50,fresh dung>200',20,226),(187,'walrus',1,4,'beach',4,10,143,16,'meat>715,fresh dung>572,hide>650,large bones>195,sinew>46,ivory>52',30,NULL),(188,'kiang',1,4,'grassland,mountains,hills',4,10,62,0,'meat>310,fresh dung>248,hide>200,large bones>150,sinew>17',20,296),(189,'orangutan',1,4,'forest,jungle',5,22,155,5,'meat>775,fresh dung>300,large bones>300',10,NULL),(190,'spider monkey',1,4,'forest,jungle',3,8,40,0,'meat>120,fur>80,small bones>50',25,NULL),(191,'green monkey',1,4,'forest,jungle,beach',7,3,40,0,'meat>120,fur>80,small bones>50',30,NULL),(192,'harpy eagle',1,4,'grassland,hills,forest,jungle,beach',6,20,90,0,'meat>150,feathers>150,small bones>100,eggs>34',15,269),(193,'jackal',4,7.5,'grassland,forest,beach',2,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(194,'great spotted woodpecker',4,7,'forest',0,0,40,0,'meat>50,feathers>20,small bones>30,eggs>10',30,287),(195,'eagle owl',1,4,'mountains,hills,forest',2,15,70,0,'meat>120,feathers>100,small bones>80,eggs>30',15,263),(196,'chamois',1,4,'mountains,hills,forest',1,8,90,0,'meat>450,hide>250,large bones>100,small bones>180,sinew>30,fresh dung>300,milk>30',30,220),(197,'blue hare',8,15,'mountains,hills,forest',0,0,15,0,'meat>100,fur>80,small bones>40',30,NULL),(198,'wildcat',1,4,'grassland,mountains,hills,forest,beach',2,5,35,0,'meat>120,fur>80,small bones>50',30,NULL),(199,'aspic viper',8,15,'grassland,mountains,hills,desert,swamp,forest,beach,plains',12,12,40,2,'meat>150,snakeskin>120,small bones>30,eggs>10',15,NULL),(200,'golden jackal',4,7.5,'mountains,hills,forest,beach',2,20,75,0,'meat>200,fur>100,large bones>70,small bones>45 ',20,NULL),(201,'common noctule bat',1,4,'mountains,hills,forest,beach',3,2,15,0,'meat>75,fresh dung>60,small bones>15',30,NULL),(202,'sparrowhawk',4,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',1,15,60,0,'meat>100,feathers>100,small bones>80,eggs>20',15,277),(203,'wood pigeon',4,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'meat>50,feathers>20,small bones>30,eggs>10',30,NULL),(204,'collared pratincole',4,7,'grassland,hills',0,0,15,0,'meat>50,feathers>20,small bones>30,eggs>30',30,NULL),(205,'green toad',8,15,'grassland,mountains,hills,forest',0,0,10,0,'meat>15',30,NULL),(206,'marginated tortoise',4,7.5,'grassland,mountains,hills,forest',0,0,75,25,'meat>150,tortoiseshell>250,eggs>40',20,NULL),(207,'stoat',4,7,'grassland,mountains,hills,forest',2,5,40,0,'meat>150,fur>70,small bones>70',30,NULL),(208,'field adder',8,15,'grassland,forest',13,12,40,0,'meat>150,snakeskin>120,small bones>30,eggs>10',14,NULL),(209,'white donkey',1,4,'mountains,hills',4,10,62,0,'meat>310,fresh dung>248,hide>200,large bones>150,sinew>17',20,297),(210,'greater noctule bat',1,4,'mountains,hills,forest',3,2,15,0,'meat>75,fresh dung>60,small bones>15',30,NULL),(212,'domesticated_alpaca',0,3.5,'grassland,mountains,hills,forest,beach,plains',0,0,60,0,'mutton>307,wool>120,large bones>82,sinew>13',4,NULL),(213,'domesticated_angora_goat',0,7.5,'grassland,mountains,hills,forest,beach,plains',0,0,80,0,'mutton>500,wool>120,small bones>40,large bones>50',4,NULL),(214,'domesticated_antelope',0,4,'grassland,hills,forest,plains',0,0,90,0,'beef>750,hide>250,large bones>100,small bones>180,sinew>30',2,NULL),(215,'domesticated_bighorn_sheep',0,5,'grassland,mountains,hills,desert,forest,beach,plains,tundra',0,0,70,0,'mutton>400,fur>100,hide>150,small bones>70,sinew>9',4,NULL),(216,'domesticated_black_sheep',0,5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,90,0,'mutton>525,wool>112,small bones>40,large bones>50',4,NULL),(217,'domesticated_buffalo',0,3,'grassland,hills,forest,beach,plains',0,0,220,8,'beef>2500,hide>1000,large bones>300,sinew>70',2,NULL),(218,'domesticated_camel',0,3,'grassland,hills,desert,beach,plains',0,0,113,4,'beef>1280,hide>512,large bones>154,sinew>36',2,NULL),(219,'domesticated_cape_buffalo',0,3.5,'grassland,hills,forest,beach,plains',0,0,143,8,'beef>1625,hide>650,large bones>195,sinew>46',2,NULL),(220,'domesticated_chamois',0,3.5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,90,0,'mutton>750,hide>250,large bones>100,small bones>180,sinew>30',4,NULL),(221,'domesticated_cow',0,3.5,'grassland,hills,forest,beach,plains',0,0,120,2,'beef>1200,hide>800,large bones>300,sinew>50',2,NULL),(222,'domesticated_dwarf_cow',0,3.5,'grassland,mountains,hills,forest,beach,plains',0,0,80,1,'beef>700,hide>400,large bones>198,sinew>33',2,NULL),(223,'domesticated_flat-tailed_sheep',0,5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,80,0,'mutton>500,wool>300,small bones>40,large bones>50',4,NULL),(224,'domesticated_gaur',0,3,'grassland,hills,forest,beach,plains',0,0,120,2,'beef>2000,hide>800,large bones>300,sinew>50',2,NULL),(225,'domesticated_goat',0,5,'grassland,mountains,hills,desert,forest,beach,plains',0,0,70,0,'mutton>400,fur>100,hide>150,small bones>70,sinew>9',4,NULL),(226,'domesticated_ibex',0,3.5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,70,0,'mutton>400,fur>100,hide>150,large bones>70,sinew>9',4,NULL),(227,'domesticated_llama',0,3.5,'grassland,mountains,hills,forest,beach,plains',0,0,110,0,'mutton>750,wool>400,large bones>200,sinew>30',4,NULL),(228,'domesticated_mouflon',0,7.5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,85,0,'mutton>500,wool>250,small bones>40,large bones>50',6,NULL),(229,'domesticated_mountain_goat',0,3.4,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,70,0,'mutton>400,wool>50,hide>150,small bones>70,sinew>9',4,NULL),(230,'domesticated_musk_ox',0,3,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,220,8,'beef>2500,hide>1000,large bones>300,sinew>70',2,NULL),(231,'domesticated_sheep',0,5,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,80,0,'mutton>500,wool>100,small bones>40,large bones>50',4,NULL),(232,'domesticated_snow_sheep',0,4,'grassland,mountains,hills,forest,beach,plains,tundra',0,0,80,0,'mutton>500,wool>100,small bones>40,large bones>50',4,NULL),(233,'domesticated_water_buffalo',0,1.25,'grassland,hills,forest,jungle,beach,plains',0,0,120,2,'beef>2000,hide>800,large bones>300,sinew>50',2,NULL),(234,'domesticated_wisent',0,3,'grassland,hills,forest,beach,plains',0,0,220,8,'beef>2500,hide>1000,large bones>300,sinew>70',2,NULL),(235,'domesticated_yak',0,3.5,'grassland,mountains,hills,beach,plains,tundra',0,0,220,8,'mutton>2500,hide>1000,large bones>300,sinew>70',2,NULL),(236,'domesticated_bearded_pig',0,5,'grassland,mountains,hills,desert,swamp,forest,beach,plains,tundra',0,0,140,5,'pork>800,hide>300,large bones>280,sinew>20',3,NULL),(237,'domesticated_bushpig',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,140,5,'pork>800,hide>300,large bones>280,sinew>20',3,NULL),(238,'domesticated_giant_forest_hog',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,140,5,'pork>800,hide>300,large bones>280,sinew>20',3,NULL),(239,'domesticated_warthog',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,140,5,'pork>800,hide>300,large bones>280,sinew>20',3,NULL),(240,'domesticated_wild_boar',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,140,5,'pork>800,hide>300,large bones>280,sinew>20',3,NULL),(242,'domesticated_black_goose',0,12.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,25,0,'poultry>50,feathers>30,small bones>30,eggs>5',6,NULL),(243,'domesticated_chicken',0,15,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,25,0,'poultry>50,feathers>30,small bones>30,eggs>5',6,NULL),(244,'domesticated_turkey',0,12.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,35,0,'poultry>200,feathers>60,small bones>50,eggs>20',5,NULL),(245,'domesticated_emu',0,5,'grassland,mountains,hills,swamp,forest,jungle,beach,plains',0,0,90,0,'poultry>500,feathers>100,small bones>170,eggs>40',3,NULL),(246,'domesticated_ostrich',0,5,'grassland,hills,desert,forest,jungle,beach,plains',0,0,90,0,'poultry>600,feathers>100,small bones>170,eggs>40',2,NULL),(247,'domesticated_penguin',0,7.5,'grassland,mountains,hills,swamp,forest,beach,plains,tundra',0,0,35,0,'poultry>250,feathers>50,small bones>60,eggs>10',4,NULL),(248,'domesticated_grey_partridge',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>200,feathers>100,small bones>120,eggs>5',4,NULL),(249,'domesticated_partridge',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>200,feathers>100,small bones>120,eggs>5',4,NULL),(250,'domesticated_pheasant',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,25,0,'poultry>50,feathers>30,small bones>30,eggs>5',4,NULL),(251,'domesticated_copper_pheasant',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,25,0,'poultry>50,feathers>30,small bones>30,eggs>5',4,NULL),(252,'domesticated_golden_pheasant',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,27,0,'poultry>50,feathers>30,small bones>30,eggs>4',4,NULL),(253,'domesticated_silver_pheasant',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,23,0,'poultry>50,feathers>30,small bones>30,eggs>6',4,NULL),(254,'domesticated_peacock',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,35,0,'poultry>100,feathers>300,small bones>60,eggs>10',3,NULL),(255,'domesticated_rainbow_lorikeet',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>25,feathers>30,small bones>20,eggs>8',3,NULL),(256,'domesticated_macaw',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,20,0,'poultry>53,feathers>33,small bones>33,eggs>6',3,NULL),(257,'domesticated_cockatoo',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,20,0,'poultry>47,feathers>24,small bones>24,eggs>4',2,NULL),(259,'domesticated_toucan',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,50,0,'poultry>120,feathers>100,small bones>65,eggs>15',3,NULL),(260,'domesticated_crow',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,45,0,'poultry>50,feathers>30,small bones>30,eggs>5',4,NULL),(261,'domesticated_do-do',0,7,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,45,0,'poultry>300,feathers>75,small bones>100,eggs>10',4,NULL),(262,'domesticated_eagle',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,90,0,'poultry>150,feathers>150,small bones>100,eggs>17',2,NULL),(263,'domesticated_eagle_owl',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,70,0,'poultry>120,feathers>100,small bones>80,eggs>15',3,NULL),(266,'domesticated_falcon',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,40,0,'poultry>50,feathers>20,small bones>30,eggs>5',2,NULL),(267,'domesticated_golden_eagle',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,90,0,'poultry>150,feathers>150,small bones>100,eggs>17',2,NULL),(268,'domesticated_gyrfalcon',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',2,NULL),(269,'domesticated_harpy_eagle',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,90,0,'poultry>150,feathers>150,small bones>100,eggs>17',2,NULL),(270,'domesticated_hawk',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',2,NULL),(271,'domesticated_osprey',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',2,NULL),(272,'domesticated_owl',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,70,0,'poultry>120,feathers>100,small bones>80,eggs>15',3,NULL),(273,'domesticated_peregrine_falcon',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',2,NULL),(274,'domesticated_pigeon',0,15,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>50,feathers>20,small bones>30,eggs>5',4,NULL),(275,'domesticated_red-tailed_hawk',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',2,NULL),(276,'domesticated_raven',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,30,0,'poultry>50,feathers>20,small bones>30,eggs>5',3,NULL),(277,'domesticated_sparrowhawk',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>100,small bones>80,eggs>10',3,NULL),(278,'domesticated_white-tailed_hawk',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,55,0,'poultry>95,feathers>95,small bones>75,eggs>9',2,NULL),(279,'domesticated_kookaburra',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,45,0,'poultry>100,feathers>50,small bones>40,eggs>10',2,NULL),(280,'domesticated_quail',0,15,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,21,0,'poultry>40,feathers>20,small bones>20,eggs>5',4,NULL),(281,'domesticated_redwinged-blackbird',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,18,0,'poultry>25,feathers>10,small bones>15,eggs>5',4,NULL),(282,'domesticated_seagull',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,30,0,'poultry>50,feathers>20,small bones>30,eggs>5',4,NULL),(283,'domesticated_white-stork',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,35,0,'poultry>200,feathers>50,small bones>60,eggs>10',3,NULL),(284,'domesticated_roadrunner',0,3.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>100,feathers>20,small bones>30,eggs>5',3,NULL),(285,'domesticated_robin',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>25,feathers>10,small bones>15,eggs>2',5,NULL),(286,'domesticated_pelican',0,15,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,40,0,'poultry>100,feathers>120,small bones>60,eggs>30',5,NULL),(287,'domesticated_great_spotted_woodpecker',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,40,0,'poultry>40,feathers>20,small bones>10,eggs>5',4,NULL),(288,'domesticated_condor',0,3.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,100,0,'poultry>300,feathers>50,small bones>59,eggs>10',2,NULL),(289,'domesticated_buzzard',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,80,0,'poultry>100,feathers>50,small bones>50,eggs>29',2,NULL),(290,'domesticated_bluejay',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,15,0,'poultry>25,feathers>10,small bones>10,eggs>5',3,NULL),(291,'domesticated_blue-heron',0,3,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,30,0,'poultry>60,feathers>20,small bones>60,eggs>10',3,NULL),(292,'domesticated_bearded_vulture',0,3.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,60,0,'poultry>200,feathers>100,small bones>80,eggs>10',2,NULL),(293,'domesticated_albatross',0,7.5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,110,0,'poultry>325,feathers>100,small bones>150,eggs>30',3,NULL),(294,'domesticated_donkey',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,80,0,'beef>500,hide>200,large bones>150,sinew>17,fresh dung>300',2,NULL),(295,'domesticated_horse',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,125,0,'beef>1000,hide>400,large bones>300,sinew>35',2,NULL),(296,'domesticated_kiang',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,90,0,'beef>500,hide>200,large bones>150,sinew>17',2,NULL),(297,'domesticated_white_donkey',0,4,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,80,0,'beef>500,hide>200,large bones>150,sinew>17',2,NULL),(298,'domesticated_zebra',0,5,'grassland,mountains,hills,desert,swamp,forest,jungle,beach,plains,tundra',0,0,110,0,'beef>900,hide>250,large bones>120,small bones>100,sinew>20',2,NULL);
/*!40000 ALTER TABLE `animal_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `animals`
--

DROP TABLE IF EXISTS `animals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL DEFAULT '0',
  `number` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `damage` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `location` (`location`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animals`
--

LOCK TABLES `animals` WRITE;
/*!40000 ALTER TABLE `animals` DISABLE KEYS */;
INSERT INTO `animals` VALUES (2,636,2,20,0);
/*!40000 ALTER TABLE `animals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applicationforms`
--

DROP TABLE IF EXISTS `applicationforms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `applicationforms` (
  `name` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `title` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `content` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`name`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicationforms`
--

LOCK TABLES `applicationforms` WRITE;
/*!40000 ALTER TABLE `applicationforms` DISABLE KEYS */;
INSERT INTO `applicationforms` VALUES ('',3,'',''),('communications',1,'','Please choose an individual role within the Public Relations Department to apply for.'),('communications',3,'Public Relations Department','This department is responsible for establishing policies regarding the moderation of Cantr\'s satellite resources (forum, wiki, irc, webzine), maintaining these resources in order to best promote the free exchange of ideas between the players, promoting Cantr in order to gain more players, and, if needed, soliciting funds from the players to pay for Cantr\'s costs. Specific responsibilities of the Public Relations Department include moderating the public forums and our IRC channel, publishing our newsletter (ezine), supervising/assisting in maintaining and updating our Cantr Wiki, and acting as intermediaries between the Administration and the various language groups. Please see below each specific division of the Public Relations Department for a more detailed description, and individual applications for each. (If you are interested in being involved in more than one division, please only fill out the application for the one you are most interested in, and indicate on the last question which other areas you would wish to help with - any pertinent questions not already asked will be forwarded to you)'),('intro',2,'','For those who would like to assist in running this game, Cantr II is almost always looking for new staff members. As you can see on the departments page (link at the bottom of each page), the staff is organised in different groups, that each have different tasks. Below you can find a description of each department, or of specific tasks within each department. \r\n\r\nIf you are interested in any of those positions, you can use the button below the description, which will automatically send you an application form. Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce). \r\n\r\nThis is where the application procedure will start. This procedure can sometimes take some time, so be patient. When filling in the form, please avoid extremely brief answers, but also pages long essays. Note that you need to be able to read and write English to get involved in the staff.\r\nThe Game Administration Board and Finance Officer are recruited from existing staff and upon the initiative of the GAB. In other words, you can only be invited to those positions and applications will be ignored.'),('intro',3,'',''),('languages',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) Please give a little information about yourself: name, age, occupation, hobbies, etc.\r\n\r\n2) What elements of Cantr appeal to you the most? What have been your favourite episodes to date, involving your own characters?\r\n\r\n3) What languages are you fluent in, both written and spoken?\r\n\r\n4) What experience do you have with translation?\r\n\r\n5) Please translate the following texts into as many languages as you can:\r\n“You see a woman in her twenties. She is wearing leather shoes, a cotton shirt and a fur hat.”\r\n\r\n\"Please note that it is not allowed to create a character in one language group, and then play it as if it belonged to another language group. If you spawn an English character it has to be able to communicate (somewhat) in English, and should be played as an English character.\"\r\n\r\n6) Do you have computer programming skills or knowledge of HTML language?'),('languages',3,'Public Relations Department (Translator)','Since the game is available in a number of different languages and most staff members are only fluent in English,\r\nwe need people that can translate between English and the other languages. A translator has two main tasks:\r\ntranslating correspondence or other texts upon request from other departments and translating the website itself. For\r\nthe letter, forms on the web are used. No specific knowledge is required, except for being fluent in both English and\r\nthe language you want to translate for.'),('liaison officer',1,'Liaison Officer','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you as a Liaison Officer?\r\n\r\n2) Why did you apply for this position?\r\n\r\n3) Name some positive things about yourself; qualities, skills...things you are proud of.\r\n\r\n4) What language other than English are you fluent in?\r\n\r\n5) Are you active in the forums in your language group?\r\n\r\n6) Part of being an LO is to pass along information from your language group to the rest of the staff in an impartial manner.  Are there any personal issues (with other players in your language group, etc.) that may impact your ability to do so?\r\n\r\n7) Part of being an LO is to recruit and manage other staff in your language group.  Do you perceive any problems being able to fulfill these vital roles?\r\n\r\n8) As a senior PR department member, you will have full authority to participate in any activity that the department covers.  Is there any aspect of the department that you feel either unwilling or unable to participate in?  Are there areas in which you have a great amount of interest?\r\n\r\n9) Are there any players from your language group that you would care to list as a reference?  Is there anyone who you would recommend that we not ask? (e.g. someone that has a grudge or dislikes you).'),('liaison officer',3,'Public Relations Department (Liaison Officer)','The Liaison Officer is a senior member of the Public Relations Department who is bilingual in English and another language used in Cantr.  They are responsible for recruiting and managing non-English speaking members of their language groups for all positions within the Public Relations Department, as well as communicating the needs and desires of their language groups to the Administration in an impartial manner.'),('marketing',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) Please give name, age, and a little information about yourself, such as job/interests/hobbies, etc.\r\n\r\n2) Why would you make a good member of the Public Relations Department, particularly with marketing?\r\n\r\n3) What resources would you use to bring new players into Cantr?\r\n\r\n4) Do you think Cantr appeals to a certain age group, or would you envision ‘targeting’ a more general age range of potential players?\r\n\r\n5) Do you believe that merchandising would be an effective source of advertising? If so, which products would you like to see implemented?\r\n\r\n6) What do you think could be useful improvements in the area of the Public Relations Department?\r\n\r\n7) What experience do you have in terms of writing articles, interviews, or newsletters? Do you have any experience with developing/maintaining websites?\r\n\r\n8) Why did you apply for this position?\r\n\r\n9) Name some positive things about yourself; qualities, skills...things you are proud of. Elaborate how they show themselves.\r\n\r\n10) Name some negative things about yourself.\r\n\r\n11) Are you interested in applying for any other aspect of the work the Public Relations Department undertakes (IRC moderatorship, forum moderatorship, wiki sysop, etc.)?'),('marketing',3,'Public Relations Department (Marketing Specialist)','Cantr always tries to get more and more players, especially because the more active players, the more interesting\r\nthe game. Hence, good marketing is crucial. Next to developing new ideas for marketing the game, a key activity\r\nis to surf the web for other sites that might be related to Cantr and to \'spread the word\' and advertise Cantr on\r\nforums, sites, etc. Standard texts are available, hence the main requirement for a member of this department is simply\r\nenthousiasm and some activity in exploring the world outside Cantr where Cantr can be marketed.'),('moderator',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you with the Public Relations Department?\r\n\r\n2) Are you primarily applying for IRC moderatorship, forum moderatorship, something else, or several of those?\r\n\r\n3) What experience do you have with IRC and IRC moderating (operating)?\r\n\r\n4) How often/much are you generally on our IRC channel, and how often do you read or write on our forum? What times are you on IRC regularly (specify timezone)?\r\n\r\n5) What do you think of our current forum and IRC and the contribution to those of our players?\r\n\r\n6) What do you think could be useful improvements in the area of the Public Relations Department?\r\n\r\n7) What experience do you have in terms of writing articles, interviews, or newsletters? Do you have any experience with developing/maintaining websites?\r\n\r\n8) Why did you apply for this position?\r\n\r\n9) Name some positive things about yourself; qualities, skills...things you are proud of. Elaborate how they show themselves.\r\n\r\n10) Name some negative things about yourself.\r\n\r\n11) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n12) Have you had a conflict with another player in the past? About what, and how did it work out?\r\n\r\n13) Can you describe the four-day-rule and how you would moderate this without taking over the job of the PD?\r\n\r\n14) How might one distinguish between an obnoxious player and an obnoxious character?\r\n\r\n15) How would you deal with a player who consistently uses abusive language, to the clear irritation of other players?\r\n\r\n16) Are you fluent in any languages besides English?'),('moderator',3,'Public Relations Department  (Forum and/or IRC Moderator)','One role within the Public Relations Department is that of forum and IRC moderator. This job involves maintaining \r\nthe friendly and welcoming atmosphere of the forums and IRC channel by resolving any out-of-control conflicts and \r\nflaming, as well as removing spam. No experience with the game is required for this position, however prior activity\r\non the Cantr forum and IRC channel is preferred. Applicants should have clear, friendly online communications skills,\r\nshould be good mediators, and should know when and when not to use moderation to resolve conflicts. This position is \r\nnot generally very time-consuming if you are already active on the forum and IRC channel. Editorial Board and Wiki \r\nSysop department members are also moderators, but have additional roles.'),('personnel',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to BeepBeep or Pilot, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you as a Personnel Officer?\r\n\r\n2) Why did you apply for this position?\r\n\r\n3) Name some positive things about yourself; qualities, skills...things you are proud of.\r\n\r\n4) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n5) What do you think are the most important tasks of the Personnel Officer? How would you go about performing those tasks?\r\n\r\n6) How do you see the position of the Personnel Officer relative to the Game Administration Board?\r\n\r\n7) What methods would you apply to keep staff morale high?\r\n\r\n8) How would you approach a staff member who is clearly not doing his or her job properly?\r\n\r\n9) How do you think the fact that all staff members are volunteers affects how they should be approached / dealt with by the Personnel Officer?\r\n\r\n10) Are you fluent in any natural languages besides English?'),('personnel',3,'Personnel Officer','The Personnel Officer is responsible for handling all applications - in close cooperation with the GAB and the Chairs\r\nof the different departments, and in keeping track of the quality of the existing staff. A good Personnel Officer has\r\ngood communication skills, has a sense of how an organisation works, and how to keep staff members enthousiast and\r\nsatisfied with their position. This person must be able to keep things confidential and to mediate when conflicts between\r\nstaff members arise.'),('players',1,'','Fill this in, copy/paste it into a private message on the forum and send it to Pilot or BeepBeep, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you with the Players Department?\r\n\r\n2) Why did you apply for this position?\r\n\r\n3) Name some positive things about yourself; qualities, skills...things you are proud of.\r\n\r\n4) Name some negative things about yourself.\r\n\r\n5) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n6) Have you had a conflict with another player in the past? About what, and how did it work out?\r\n\r\n7) How might one distinguish between an obnoxious player and an obnoxious character?\r\n\r\n8 ) Are there any circumstances in which multiple characters of the same player working together are not violating the Capital Rule? Explain.\r\n\r\n9) Are you fluent in any languages besides English?'),('players',3,'Players Department','The Players Department handles all requests and questions by players that are not clearly related to any other\r\ndepartment and is responsible to handling applications by players and breaches of the Capital Rule or other bad\r\nplayer behaviour. The department is thus a mixture of a customer service and a detective / court system. Members\r\nof this department need to have clear and friendly email communication skills and need to have a perfect grasp of the\r\nCapital Rule. A good sense of what is acceptable and what is not in terms of player behaviour is crucial. Again,\r\nscreening is somewhat thorough.'),('programming',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you with the Programming Department?\r\n\r\n2) Why did you apply for this position?\r\n\r\n3) Name some positive things about yourself; qualities, skills...things you are proud of.\r\n\r\n4) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n5) What programming languages do you know and how well do you know them?\r\n\r\n6) What experiences do you have with relational and other databases?\r\n\r\n7) In terms of time, how much do you expect to be able to dedicate to this position, on average?\r\n\r\n8) Do you consider yourself a good team worker? Why?\r\n\r\n9) Are you fluent in any natural languages besides English?'),('programming',3,'Programming Department','The Programming Department is responsible for all bug repairs and writing all additions to the game (for as far as\r\nthey cannot be handled by the Resources Department). Cantr is written in PHP, using the support of a mySQL database,\r\nhence some knowledge of these two is a strong advantage. Both are easy to learn, however, when you already have a solid\r\nprogramming background or when you have enough time and enthousiasm to dedicate to learning them. The Programming\r\nDepartment will consider applications from people with no prior programming experience, given that the person has\r\nenough will, time, and enthousiasm to dedicate to learning to program. Because of the access privileges of a full\r\nmember of the department, every new member will first be named \'aspirant member\' with limited access, so that we can\r\nestimate the reliability of the new member. The department is interested both in people concentrating on bug repairs\r\nand in people concentrating on new development, or both. All new development has to be approved by the Chair, as the\r\nconcept of the game is very closely guarded.'),('resources',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\nRESOURCE DEPARTMENT APPLICATION QUESTIONNAIRE\r\n\r\n1. Share some basic biographical information about yourself.  (Age, location, job, school, etc) \r\n\r\n\r\n\r\n2. Share a few positive qualities about yourself. (works well in groups, creative, self-motivated, etc) \r\n\r\n\r\n\r\n3. Share a few negative qualities about yourself. (opininated and stubborn, long stretches of inactivity, etc) \r\n\r\n\r\n\r\n4. How much of the Cantr world do you get to see directly on a regular basis?  (how many characters do you play actively, how many islands do they live on, what language groups do you play in, etc.) \r\n\r\n\r\n\r\n5. Describe a few of the professions or character types you have played during your Cantr career.  What are some of your favorites? \r\n\r\n\r\n\r\n6. Resource Department is responsible for defining everything your character can gather, build, eat, and/or use.  Name a few things in the game that you feel could use some improvement. \r\n\r\n\r\n\r\n7. Name a few things that you think have been implemented very well and give some thoughts on why you think they improve the game. \r\n\r\n\r\n\r\n8. What kind of resources or items would you be interested in working on as a member of the Resources Department?  What sources of information would you use to help bring a balance of realism and playability to your projects? \r\n\r\n\r\n\r\n9. What do you feel are the major factors involved in determining the popularity, usefulness, or overall quality of a resource or item?  What basic strategies would you consider to decide how best to improve the game balance of an item or process? \r\n\r\n\r\n\r\n10. Do you feel that the mechanics of gathering, processing, and building unfairly encourage or inhibit the development of certain types of social elements (economy, security, combat, crime, etc)?  If so, what thoughts do you have on how to fix them?\r\n\r\n\r\n'),('resources',3,'Resources Department','This department is responsible for defining new types of objects, new types of raw materials, new types of vehicles,\r\nand new types of machines, and can make alterations in the allocation of resources in the game world. All changes are\r\nmade using forms on the website, which will be extensively explained before you will use them. Some are more technical\r\nthan others (e.g. the object types definition table), but there is definitely no need for any programming experience.\r\nWhat we really need here is people that are interested in surfing the web and reading up on real life production\r\nprocesses, so that the Cantr game can be made a bit more realistic. The department tries to keep an interesting balance\r\nin the game in terms of resources and building requirements and to keep a certain level of complexity in the industrial\r\nprocess in the game. So, if you are interested in thinking about how things should be produced, what steps are involved\r\nin an industrial process, etc., and you like to discuss those on the forum and devise new object and resource types,\r\nthis might be the department for you.'),('tailor',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\nRESOURCE DEPARTMENT APPLICATION QUESTIONNAIRE\r\n\r\n1. Share some basic biographical information about yourself.  (Age, location, job, school, etc) \r\n\r\n\r\n\r\n2. Share a few positive qualities about yourself. (works well in groups, creative, self-motivated, etc) \r\n\r\n\r\n\r\n3. Share a few negative qualities about yourself. (opininated and stubborn, long stretches of inactivity, etc) \r\n\r\n\r\n\r\n4. How much of the Cantr world do you get to see directly on a regular basis?  (how many characters do you play actively, how many islands do they live on, what language groups do you play in, etc.) \r\n\r\n\r\n\r\n5. Describe a few of the professions or character types you have played during your Cantr career.  What are some of your favorites? \r\n\r\n\r\n\r\n6. Resource Department is responsible for defining everything your character can gather, build, eat, and/or use.  Name a few things in the game that you feel could use some improvement. \r\n\r\n\r\n\r\n7. Name a few things that you think have been implemented very well and give some thoghts on why you think they improve the game. \r\n\r\n\r\n\r\n8. What kind of resources or items would you be interested in working on as a member of the Resources Department?  What sources of information would you use to help bring a balance of realism and playability to your projects? \r\n\r\n\r\n\r\n9. What do you feel are the major factors involved in determining the popularity, usefulness, or overall quality of a resource or item?  What basic strategies would you consider to decide how best to improve the game balance of an item or process? \r\n\r\n\r\n\r\n10. Do you feel that the mechanics of gathering, processing, and building unfairly encourage or inhibit the development of certain types of social elements (economy, security, combat, crime, etc)?  If so, what thoughts do you have on how to fix them?\r\n\r\n\r\n'),('tailor',3,'Resources Department - Tailor','The tailor is a member of the RD, but has a very specific role. One of the definitions just described is the definition\r\nof the different types of clothes. Writing their descriptions and defining what requirements are there to manufacture\r\nthem. The tailor will interact directly with players who can make requests for new clothes and discuss with them what\r\nwould be the right description. Here are combination is needed of some interest in how manufacturing processes are\r\nbuild up and some talent in writing fiction English (like, not as boring as my writing :) ...).'),('webzine',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) Please tell us a bit about yourself: What is your name? What is your age and occupation, and what timezone are you in? What are your general interests (eg. hobbies)?\r\n\r\n2) Why did you apply for this position?\r\n\r\n3) What experience do you have in terms of writing articles, interviews, or newsletters? Do you have any experience with developing/maintaining websites?\r\n\r\n4) Name some positive things about yourself; qualities, skills...things you are proud of. Elaborate how they show themselves.\r\n\r\n5) Name some negative things about yourself.\r\n\r\n6) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n7) Please edit the following sentence, paying close attention to all punctuation, spelling, and correct word usage in proper English:\r\n\r\na old man ran toward the water chassing after The boat laughin loudly lol\r\n\r\n8) Are you fluent in any languages besides English?\r\n\r\n9) Do you have computer programming skills, or knowledge of HTML language?\r\n\r\n10) Are you interested in applying for any other aspect of the work the Public Relations Department undertakes (marketing, IRC moderatorship, forum moderatorship, wiki sysop, etc.)?'),('webzine',3,'Public Relations Department (Editorial Board)','The Public Relations Department is also responsible for publishing our quarterly webzine. The Editorial Board\r\nfinds submissions, writes articles, conducts the webzine\'s interviews, and edits submitted content. Applicants for \r\nthe Editorial Board should have good writing, editing, and interviewing skills. This position sometimes requires \r\nquite a bit of time (when editing a submission, writing an article, releasing a new issue, or conducting an interview, \r\nfor example), but isn\'t demanding on a daily basis.'),('wiki',1,'','Fill this in, copy/paste it into a private message on the forum and send it to the Chair of the department to which you are applying, to Cdls, or send it to the personnel department via the webform on the contact page (direct mail will bounce).\r\n\r\n1) What is your age, and what real life experience do you have that might help you with the Public Relations Department (particularly with wiki editing)?\r\n\r\n2) Are you primarily applying for wiki management, something else, or more than one area of the Public Relations Department?\r\n\r\n3) What experience do you have with IRC and IRC moderating (operating)?\r\n\r\n4) How often/much are you generally on our IRC channel, and how often do you read or write on our forum? What times are you on IRC regularly (specify timezone)?\r\n\r\n5) What do you think of our current forum and IRC and the contribution to those of our players?\r\n\r\n6) What do you think could be useful improvements in the area of the Public Relations Department?\r\n\r\n7) What experience do you have in terms of writing articles, interviews, or newsletters? Do you have any experience with developing/maintaining websites?\r\n\r\n8) Why did you apply for this position?\r\n\r\n9) Name some positive things about yourself; qualities, skills...things you are proud of. Elaborate how they show themselves.\r\n\r\n10) Name some negative things about yourself.\r\n\r\n11) What elements of Cantr appeal to you the most? What have been your favorite episodes to date, involving your own characters?\r\n\r\n12) Have you had a conflict with another player in the past? About what, and how did it work out?\r\n\r\n13) Can you describe the four-day-rule and how would you moderate this without taking over the job of the PD?\r\n\r\n14) How might one distinguish between an obnoxious player and an obnoxious character?\r\n\r\n15) How would you deal with a player who consistently uses abusive language, to the clear irritation of other players?\r\n\r\n16) Are you fluent in any languages besides English?'),('wiki',3,'Public Relations Department (Wiki Management)','Other roles within the Public Relations Department are that of the wiki moderator and wiki administrator. The wiki \r\nadministrator is in charge of deciding how the information on the wiki should be organized, and is responsible for \r\ncommunicating with staff to obtain that information. The wiki moderator watches the wiki for errors and vandalism. \r\nFor these positions, applicants are preferred (though not required) to have experience with the Cantr wiki and \r\nMediaWiki editing knowledge.');
/*!40000 ALTER TABLE `applicationforms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` tinytext CHARACTER SET latin1,
  `maxbuildings` tinyint(3) unsigned DEFAULT NULL,
  `road_factor` decimal(3,1) DEFAULT NULL,
  `created` tinyint(1) unsigned DEFAULT NULL,
  `parent_type` text CHARACTER SET latin1,
  `requirements` text CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES (1,'grassland',50,1.0,0,'',''),(2,'mountains',4,2.0,0,'',''),(3,'hills',20,1.2,0,'',''),(4,'desert',4,0.8,0,'',''),(5,'swamp',0,4.0,0,'',''),(6,'forest',10,1.5,0,'',''),(12,'jungle',1,4.0,0,NULL,NULL),(13,'beach',4,0.8,0,NULL,NULL),(14,'plains',30,0.6,0,NULL,NULL),(15,'tundra',5,3.0,0,NULL,NULL);
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assignments`
--

DROP TABLE IF EXISTS `assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assignments` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `player` mediumint(8) unsigned DEFAULT NULL,
  `council` tinyint(3) unsigned DEFAULT NULL,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `special` tinytext CHARACTER SET latin1,
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  KEY `council` (`council`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assignments`
--

LOCK TABLES `assignments` WRITE;
/*!40000 ALTER TABLE `assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_ips` (
  `ip` tinytext CHARACTER SET latin1,
  `block_type` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_ips`
--

LOCK TABLES `blocked_ips` WRITE;
/*!40000 ALTER TABLE `blocked_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmark_whispering`
--

DROP TABLE IF EXISTS `bookmark_whispering`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmark_whispering` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner` int(11) NOT NULL,
  `target` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `character` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmark_whispering`
--

LOCK TABLES `bookmark_whispering` WRITE;
/*!40000 ALTER TABLE `bookmark_whispering` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookmark_whispering` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borders`
--

DROP TABLE IF EXISTS `borders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `borders` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `subtype` smallint(6) NOT NULL DEFAULT '0',
  `minx` int(11) NOT NULL DEFAULT '0',
  `miny` int(11) NOT NULL DEFAULT '0',
  `maxx` int(11) NOT NULL DEFAULT '0',
  `maxy` int(11) NOT NULL DEFAULT '0',
  `points` text CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borders`
--

LOCK TABLES `borders` WRITE;
/*!40000 ALTER TABLE `borders` DISABLE KEYS */;
/*!40000 ALTER TABLE `borders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `name` tinytext CHARACTER SET latin1,
  `full_description` tinytext CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES ('unmanufacturable',NULL),('tools','Tools'),('raw material',NULL),('transport','Transportation'),('temporarily unmanufacturable',NULL),('buildings','Buildings'),('machinery','Machinery'),('engines','Engines'),('vehicles','Vehicles'),('weapons','Weapons'),('jewelry',''),('recycle bin',''),('protection','Protection'),('furniture','Furniture'),('storage','Storage'),('awaiting approval',NULL),('awaiting programming',NULL),('awaiting game progress',NULL),('semi-finished','Semi-finished products'),('semi-finished (unmanufacturable)',NULL),('electronics','Electronics'),('musical-instruments','Musical Instruments'),('miscellaneous','Miscellaneous'),('clothes','Clothes'),('roleplay','Roleplay'),('domesticated animals','Domesticated animals');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ceAccess`
--

DROP TABLE IF EXISTS `ceAccess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ceAccess` (
  `player` mediumint(6) NOT NULL DEFAULT '0',
  `access` varchar(32) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ceAccess`
--

LOCK TABLES `ceAccess` WRITE;
/*!40000 ALTER TABLE `ceAccess` DISABLE KEYS */;
/*!40000 ALTER TABLE `ceAccess` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ceAccessTypes`
--

DROP TABLE IF EXISTS `ceAccessTypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ceAccessTypes` (
  `id` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT 'unnamed',
  `description` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ceAccessTypes`
--

LOCK TABLES `ceAccessTypes` WRITE;
/*!40000 ALTER TABLE `ceAccessTypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `ceAccessTypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ceLog`
--

DROP TABLE IF EXISTS `ceLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ceLog` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `player` mediumint(6) DEFAULT NULL,
  `params` text CHARACTER SET latin1,
  `description` text CHARACTER SET latin1,
  `accesstime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ceLog`
--

LOCK TABLES `ceLog` WRITE;
/*!40000 ALTER TABLE `ceLog` DISABLE KEYS */;
/*!40000 ALTER TABLE `ceLog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `changes`
--

DROP TABLE IF EXISTS `changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changes` (
  `id` mediumint(9) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` text CHARACTER SET latin1,
  `table_id` mediumint(9) unsigned DEFAULT '0',
  `hash` mediumtext CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changes`
--

LOCK TABLES `changes` WRITE;
/*!40000 ALTER TABLE `changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `char_limitations`
--

DROP TABLE IF EXISTS `char_limitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `char_limitations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `char_id` int(11) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT 'const values stored in class.Limitations.php',
  `end_time` int(11) unsigned DEFAULT NULL COMMENT 'cantr time format=sec+min*60+hour*60*36+day*60*36*8',
  `target` int(11) DEFAULT NULL COMMENT 'used if necessary',
  `start_rl_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chartype` (`char_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='used to limit characters actions - class.Limitations.php';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `char_limitations`
--

LOCK TABLES `char_limitations` WRITE;
/*!40000 ALTER TABLE `char_limitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `char_limitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `char_near_death`
--

DROP TABLE IF EXISTS `char_near_death`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `char_near_death` (
  `char_id` int(11) NOT NULL,
  `state` smallint(11) NOT NULL,
  `day` int(11) NOT NULL,
  `hour` smallint(11) NOT NULL,
  PRIMARY KEY (`char_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `char_near_death`
--

LOCK TABLES `char_near_death` WRITE;
/*!40000 ALTER TABLE `char_near_death` DISABLE KEYS */;
/*!40000 ALTER TABLE `char_near_death` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `char_on_loc_count`
--

DROP TABLE IF EXISTS `char_on_loc_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `char_on_loc_count` (
  `location` mediumint(9) DEFAULT NULL,
  `root` mediumint(9) DEFAULT NULL,
  `language` smallint(6) DEFAULT NULL,
  `number` smallint(6) DEFAULT NULL,
  `sq_number` smallint(6) DEFAULT NULL,
  KEY `location` (`location`),
  KEY `number` (`number`),
  KEY `sq_number` (`sq_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `char_on_loc_count`
--

LOCK TABLES `char_on_loc_count` WRITE;
/*!40000 ALTER TABLE `char_on_loc_count` DISABLE KEYS */;
/*!40000 ALTER TABLE `char_on_loc_count` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charnaming`
--

DROP TABLE IF EXISTS `charnaming`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `charnaming` (
  `observer` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `observed` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8,
  PRIMARY KEY (`observer`,`observed`,`type`),
  KEY `observed` (`observed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charnaming`
--

LOCK TABLES `charnaming` WRITE;
/*!40000 ALTER TABLE `charnaming` DISABLE KEYS */;
/*!40000 ALTER TABLE `charnaming` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chars`
--

DROP TABLE IF EXISTS `chars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chars` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `player` mediumint(8) unsigned DEFAULT NULL,
  `language` tinyint(3) unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `sex` tinyint(1) DEFAULT NULL,
  `location` mediumint(5) unsigned DEFAULT NULL,
  `register` smallint(5) unsigned DEFAULT NULL,
  `spawning_location` mediumint(9) NOT NULL DEFAULT '0',
  `spawning_age` tinyint(4) NOT NULL DEFAULT '20',
  `lastdate` smallint(5) unsigned DEFAULT NULL,
  `lasttime` smallint(5) unsigned DEFAULT NULL,
  `death_cause` tinyint(3) unsigned DEFAULT '0',
  `death_weapon` smallint(5) unsigned DEFAULT '0',
  `death_date` smallint(5) unsigned DEFAULT '0',
  `project` int(9) DEFAULT '0',
  `activity` mediumint(8) unsigned DEFAULT '0',
  `description` text CHARACTER SET latin1,
  `status` tinyint(3) unsigned DEFAULT NULL,
  `newbie` tinyint(3) unsigned DEFAULT '0',
  `custom_desc` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `player` (`player`),
  KEY `project` (`project`),
  KEY `register` (`register`),
  KEY `lastdate` (`lastdate`),
  KEY `loc_alive` (`location`,`status`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chars`
--

LOCK TABLES `chars` WRITE;
/*!40000 ALTER TABLE `chars` DISABLE KEYS */;
/*!40000 ALTER TABLE `chars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clothes`
--

DROP TABLE IF EXISTS `clothes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clothes` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(70) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` int(10) NOT NULL DEFAULT '0',
  `description` text CHARACTER SET latin1 NOT NULL,
  `shortdescription` varchar(80) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `requirements` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `skill` tinyint(3) unsigned DEFAULT '0',
  `validated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clothes`
--

LOCK TABLES `clothes` WRITE;
/*!40000 ALTER TABLE `clothes` DISABLE KEYS */;
/*!40000 ALTER TABLE `clothes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clothes_categories`
--

DROP TABLE IF EXISTS `clothes_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clothes_categories` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(70) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `hides` varchar(70) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `sortn` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clothes_categories`
--

LOCK TABLES `clothes_categories` WRITE;
/*!40000 ALTER TABLE `clothes_categories` DISABLE KEYS */;
INSERT INTO `clothes_categories` VALUES (37,'Hats','',1),(38,'Jackets','49',25),(39,'Shirts','49',45),(40,'Trousers','43',70),(41,'Gloves','48',55),(42,'Shoes','',95),(43,'Underpants','',75),(44,'Robes','39,43,49',40),(45,'Masks','',5),(46,'Earrings','',10),(47,'Bracelets','',57),(48,'Rings','',60),(49,'Undershirts','',47),(50,'Necklaces','',17),(51,'Scarves','50',15),(52,'Skirts','43',65),(53,'Belts','',62),(54,'Cloaks','',20),(55,'Vests','49',35),(56,'Aprons','49',30),(57,'Socks','',90),(58,'Dresses','43,40,49,52',42),(60,'Bags','',61),(62,'Hair_Accessories','',5);
/*!40000 ALTER TABLE `clothes_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connection_parts`
--

DROP TABLE IF EXISTS `connection_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `connection_parts` (
  `connection` int(10) unsigned NOT NULL,
  `part_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned DEFAULT NULL,
  `deterioration` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`part_id`),
  KEY `connection` (`connection`)
) ENGINE=InnoDB AUTO_INCREMENT=66995 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connection_parts`
--

LOCK TABLES `connection_parts` WRITE;
/*!40000 ALTER TABLE `connection_parts` DISABLE KEYS */;
INSERT INTO `connection_parts` VALUES (646,33398,11,0),(647,33399,11,0),(648,33400,11,0),(649,33401,11,0),(650,33402,11,0);
/*!40000 ALTER TABLE `connection_parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `connections` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `start` mediumint(5) unsigned DEFAULT NULL,
  `end` mediumint(5) unsigned DEFAULT NULL,
  `direction` smallint(6) DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT '1',
  `length` smallint(5) unsigned DEFAULT NULL,
  `improving` tinyint(1) DEFAULT '0',
  `start_area` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `end_area` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `deterioration` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `start` (`start`),
  KEY `end` (`end`),
  KEY `start_area` (`start_area`),
  KEY `end_area` (`end_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connections`
--

LOCK TABLES `connections` WRITE;
/*!40000 ALTER TABLE `connections` DISABLE KEYS */;
INSERT INTO `connections` VALUES (646,636,640,19,11,34,0,3,1,0),(647,640,638,124,11,36,0,1,2,0),(648,640,637,44,11,25,0,1,3,0),(649,638,637,345,11,41,0,2,3,0),(650,638,639,63,11,35,0,2,2,0);
/*!40000 ALTER TABLE `connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `connecttypes`
--

DROP TABLE IF EXISTS `connecttypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `connecttypes` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` tinytext CHARACTER SET latin1,
  `vehicles` tinytext CHARACTER SET latin1,
  `speed_factor` smallint(5) unsigned DEFAULT NULL,
  `description` tinytext CHARACTER SET latin1,
  `speedlimit` smallint(5) unsigned NOT NULL DEFAULT '65535',
  `improved_from` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `improve_requirements` text CHARACTER SET latin1 NOT NULL,
  `deter_rate_turn` smallint(5) unsigned NOT NULL DEFAULT '0',
  `repair_rate` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `connecttypes`
--

LOCK TABLES `connecttypes` WRITE;
/*!40000 ALTER TABLE `connecttypes` DISABLE KEYS */;
INSERT INTO `connecttypes` VALUES (1,'path','walking,54,55,56,57,87,91,132,133,137,148,324,551,709,710,714,715,716,1393,1596,1597,1598,1599,1600,1289',80,'default connection between locations, over land',65535,0,'',0,0),(2,'sand_road','walking,18,54,55,56,57,58,59,86,87,91,131,132,133,135,137,148,139,140,276,316,324,551,706,707,708,709,710,711,712,713,714,715,716,717,718,1393,1596,1597,1598,1599,1600,1289',100,'first improvement of a path',65535,1,'raws:sand>1000;days:0.5',0,0),(3,'inland_waterway','38,70,85,129,155,275,289,328,838,839',100,'connection between two landing docks, over a lake',65535,0,'',0,0),(5,'sea_waterway','64,127,128,129,155,275,289,328,329,838,839',100,'connection between two larger harbours, over a sea',65535,0,'',0,0),(6,'impassable','',100,'road which cannot be passed in any way',65535,0,'',0,0),(7,'paved_road','walking,18,54,55,56,57,58,59,86,87,91,131,132,133,135,137,148,139,140,276,316,324,551,706,707,708,709,710,711,712,713,714,715,716,717,718,764,1393,1596,1597,1598,1599,1600,1289',135,'second improvement of a path',65535,2,'raws:sand>700,stone>500,oil>10;days:0.7',0,0),(8,'small_plane_airline','',100,'connection between two small airfields',65535,0,'',0,0),(9,'airline','',100,'connection between two big airfields',65535,0,'',0,0),(10,'highway','walking,18,54,55,56,57,58,59,86,87,91,131,132,133,135,137,148,139,140,276,316,324,551,706,707,708,709,710,711,712,713,714,715,716,717,718,764,1393,1596,1597,1598,1599,1600,1289',200,'third improvement of a path',65535,7,'raws:sand>1000,stone>800,oil>100;days:1',0,0),(11,'expressway','walking,18,54,55,56,57,58,59,86,87,91,131,132,133,135,137,148,139,140,276,316,324,551,706,707,708,709,710,711,712,713,714,715,716,717,718,764,1393,1596,1597,1598,1599,1600,1289',280,'fourth improvement of a path',65535,10,'raws:sand>800,stone>1200,oil>120;days:1.2',0,0),(12,'railroad','',100,'',65535,0,'',0,0);
/*!40000 ALTER TABLE `connecttypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactips`
--

DROP TABLE IF EXISTS `contactips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactips` (
  `ip` text CHARACTER SET latin1,
  `time` bigint(20) NOT NULL,
  `banned` tinyint(4) NOT NULL DEFAULT '0',
  KEY `time` (`time`),
  KEY `banned` (`banned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactips`
--

LOCK TABLES `contactips` WRITE;
/*!40000 ALTER TABLE `contactips` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cook`
--

DROP TABLE IF EXISTS `cook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cook` (
  `player` int(11) NOT NULL DEFAULT '0',
  `cookie` char(8) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `player` (`player`),
  KEY `cookie` (`cookie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cook`
--

LOCK TABLES `cook` WRITE;
/*!40000 ALTER TABLE `cook` DISABLE KEYS */;
/*!40000 ALTER TABLE `cook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cookmult`
--

DROP TABLE IF EXISTS `cookmult`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cookmult` (
  `cookie` char(8) CHARACTER SET latin1 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookmult`
--

LOCK TABLES `cookmult` WRITE;
/*!40000 ALTER TABLE `cookmult` DISABLE KEYS */;
/*!40000 ALTER TABLE `cookmult` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corners`
--

DROP TABLE IF EXISTS `corners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `corners` (
  `id` mediumint(8) unsigned DEFAULT NULL,
  `x` smallint(5) unsigned DEFAULT NULL,
  `y` smallint(5) unsigned DEFAULT NULL,
  `changedir` tinyint(4) DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT NULL,
  `typeid` mediumint(9) DEFAULT NULL,
  `next` mediumint(8) unsigned DEFAULT NULL,
  KEY `x` (`x`),
  KEY `y` (`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corners`
--

LOCK TABLES `corners` WRITE;
/*!40000 ALTER TABLE `corners` DISABLE KEYS */;
INSERT INTO `corners` VALUES (1,252,1098,0,1,NULL,2),(2,222,1139,0,1,NULL,3),(3,218,1198,0,1,NULL,4),(4,186,1244,1,1,NULL,5),(5,213,1270,1,1,NULL,6),(6,202,1297,1,1,NULL,7),(7,207,1340,1,1,NULL,8),(8,199,1375,0,1,NULL,9),(9,182,1389,1,1,NULL,10),(10,183,1406,0,1,NULL,11),(11,201,1425,0,1,NULL,12),(12,264,1426,0,1,NULL,13),(13,277,1413,0,1,NULL,14),(14,285,1393,0,1,NULL,15),(15,321,1397,0,1,NULL,16),(16,349,1385,0,1,NULL,17),(17,354,1388,1,1,NULL,18),(18,340,1420,0,1,NULL,19),(19,234,1442,0,1,NULL,20),(20,177,1433,0,1,NULL,21),(21,162,1435,0,1,NULL,22),(22,114,1475,0,1,NULL,23),(23,111,1501,1,1,NULL,24),(24,117,1507,0,1,NULL,25),(25,125,1556,0,1,NULL,26),(26,238,1641,0,1,NULL,27),(27,301,1635,0,1,NULL,28),(28,357,1601,1,1,NULL,29),(29,354,1567,1,1,NULL,30),(30,364,1564,0,1,NULL,31),(31,380,1583,0,1,NULL,32),(32,381,1601,0,1,NULL,33),(33,390,1611,0,1,NULL,34),(34,410,1612,0,1,NULL,35),(35,428,1602,0,1,NULL,36),(36,452,1570,0,1,NULL,37),(37,483,1553,0,1,NULL,38),(38,500,1556,0,1,NULL,39),(39,518,1576,0,1,NULL,40),(40,532,1583,0,1,NULL,41),(41,572,1569,0,1,NULL,42),(42,582,1552,0,1,NULL,43),(43,703,1438,0,1,NULL,44),(44,709,1416,0,1,NULL,45),(45,732,1402,0,1,NULL,46),(46,746,1386,0,1,NULL,47),(47,748,1311,0,1,NULL,48),(48,757,1286,1,1,NULL,49),(49,748,1272,0,1,NULL,50),(50,728,1273,0,1,NULL,51),(51,694,1285,0,1,NULL,52),(52,648,1314,0,1,NULL,53),(53,602,1301,0,1,NULL,54),(54,573,1297,0,1,NULL,55),(55,493,1145,1,1,NULL,56),(56,514,1103,1,1,NULL,57),(57,465,1076,0,1,NULL,58),(58,418,1075,0,1,NULL,59),(59,386,1064,0,1,NULL,60),(60,308,1065,0,1,NULL,1),(61,320,1078,0,2,NULL,62),(62,260,1110,1,2,NULL,63),(63,268,1132,1,2,NULL,64),(64,237,1173,1,2,NULL,65),(65,240,1187,0,2,NULL,66),(66,270,1188,0,2,NULL,67),(67,292,1194,0,2,NULL,68),(68,321,1180,0,2,NULL,69),(69,349,1171,0,2,NULL,70),(70,363,1158,0,2,NULL,71),(71,377,1102,1,2,NULL,61),(72,352,1217,1,2,NULL,73),(73,345,1224,0,2,NULL,74),(74,336,1224,0,2,NULL,75),(75,320,1240,0,2,NULL,76),(76,295,1255,1,2,NULL,77),(77,298,1260,0,2,NULL,78),(78,351,1260,0,2,NULL,79),(79,361,1248,1,2,NULL,80),(80,350,1241,1,2,NULL,72),(81,349,1285,0,2,NULL,82),(82,288,1282,0,2,NULL,83),(83,263,1275,0,2,NULL,84),(84,260,1280,1,2,NULL,85),(85,275,1289,0,2,NULL,86),(86,284,1299,0,2,NULL,87),(87,285,1317,0,2,NULL,88),(88,297,1323,0,2,NULL,89),(89,317,1313,0,2,NULL,90),(90,345,1315,0,2,NULL,91),(91,356,1349,1,2,NULL,92),(92,351,1315,0,2,NULL,81),(93,453,1184,0,2,NULL,94),(94,434,1198,1,2,NULL,95),(95,441,1248,1,2,NULL,96),(96,403,1314,0,2,NULL,97),(97,383,1394,1,2,NULL,98),(98,398,1416,0,2,NULL,99),(99,456,1427,0,2,NULL,100),(100,490,1473,0,2,NULL,101),(101,500,1472,0,2,NULL,102),(102,521,1404,1,2,NULL,103),(103,464,1293,1,2,NULL,104),(104,465,1248,0,2,NULL,105),(105,473,1217,1,2,NULL,106),(106,464,1185,0,2,NULL,93),(107,207,1501,0,2,NULL,108),(108,190,1522,1,2,NULL,109),(109,195,1550,0,2,NULL,110),(110,245,1570,0,2,NULL,111),(111,287,1556,0,2,NULL,112),(112,288,1533,1,2,NULL,107),(113,357,1519,1,2,NULL,114),(114,361,1529,0,2,NULL,115),(115,410,1514,0,2,NULL,116),(116,434,1520,0,2,NULL,117),(117,453,1519,1,2,NULL,118),(118,437,1488,0,2,NULL,119),(119,431,1462,0,2,NULL,120),(120,420,1458,0,2,NULL,121),(121,406,1485,0,2,NULL,113),(122,855,970,0,1,NULL,123),(123,827,970,0,1,NULL,124),(124,817,978,0,1,NULL,125),(125,816,994,1,1,NULL,126),(126,825,1013,0,1,NULL,127),(127,828,1025,1,1,NULL,128),(128,813,1070,1,1,NULL,129),(129,820,1109,0,1,NULL,130),(130,838,1144,0,1,NULL,131),(131,860,1255,0,1,NULL,132),(132,875,1276,0,1,NULL,133),(133,901,1293,0,1,NULL,134),(134,965,1312,0,1,NULL,135),(135,986,1312,0,1,NULL,136),(136,1026,1285,0,1,NULL,137),(137,1081,1204,0,1,NULL,138),(138,1100,1186,0,1,NULL,139),(139,1122,1180,0,1,NULL,140),(140,1135,1237,0,1,NULL,141),(141,1181,1282,0,1,NULL,142),(142,1226,1300,0,1,NULL,143),(143,1306,1295,0,1,NULL,144),(144,1352,1271,0,1,NULL,145),(145,1384,1236,0,1,NULL,146),(146,1387,1211,1,1,NULL,147),(147,1369,1190,0,1,NULL,148),(148,1327,1175,0,1,NULL,149),(149,1320,1168,1,1,NULL,150),(150,1321,1149,0,1,NULL,151),(151,1339,1138,0,1,NULL,152),(152,1400,1120,0,1,NULL,153),(153,1422,1098,0,1,NULL,154),(154,1423,1083,1,1,NULL,155),(155,1382,1060,0,1,NULL,156),(156,1381,1050,1,1,NULL,157),(157,1398,1032,0,1,NULL,158),(158,1399,1020,1,1,NULL,159),(159,1378,1004,1,1,NULL,160),(160,1379,990,0,1,NULL,161),(161,1444,950,0,1,NULL,162),(162,1455,922,1,1,NULL,163),(163,1438,904,0,1,NULL,164),(164,1416,904,0,1,NULL,165),(165,1335,937,0,1,NULL,166),(166,1277,940,0,1,NULL,167),(167,1244,929,1,1,NULL,168),(168,1246,912,0,1,NULL,169),(169,1270,893,0,1,NULL,170),(170,1334,870,0,1,NULL,171),(171,1345,860,1,1,NULL,172),(172,1322,855,0,1,NULL,173),(173,1272,869,0,1,NULL,174),(174,1208,862,0,1,NULL,175),(175,1175,880,0,1,NULL,176),(176,1161,909,0,1,NULL,177),(177,1133,924,0,1,NULL,178),(178,1080,894,0,1,NULL,179),(179,1040,886,0,1,NULL,2026),(180,914,900,0,1,NULL,181),(181,865,933,1,1,NULL,182),(182,866,965,0,1,NULL,183),(183,878,983,0,1,NULL,184),(184,901,1001,0,1,NULL,185),(185,961,995,0,1,NULL,186),(186,1031,960,0,1,NULL,187),(187,1099,938,0,1,NULL,188),(188,1127,936,0,1,NULL,189),(189,1128,965,1,1,NULL,190),(190,1111,988,0,1,NULL,191),(191,1055,1006,0,1,NULL,192),(192,995,1040,0,1,NULL,193),(193,958,1091,0,1,NULL,194),(194,948,1118,0,1,NULL,195),(195,924,1126,0,1,NULL,196),(196,901,1114,0,1,NULL,2027),(197,868,1057,1,1,NULL,198),(198,870,989,1,1,NULL,122),(199,1262,1023,0,2,NULL,200),(200,1251,1068,0,2,NULL,201),(201,1240,1084,0,2,NULL,202),(202,1213,1097,1,2,NULL,203),(203,1214,1118,0,2,NULL,204),(204,1228,1116,0,2,NULL,205),(205,1245,1125,0,2,NULL,206),(206,1250,1149,0,2,NULL,207),(207,1260,1151,0,2,NULL,208),(208,1272,1147,0,2,NULL,209),(209,1310,1148,0,2,NULL,210),(210,1346,1112,1,2,NULL,211),(211,1337,1056,1,2,NULL,212),(212,1355,1043,1,2,NULL,213),(213,1352,1038,0,2,NULL,199),(214,893,1196,0,2,NULL,215),(215,885,1205,1,2,NULL,216),(216,885,1227,0,2,NULL,217),(217,874,1243,1,2,NULL,218),(218,891,1270,0,2,NULL,219),(219,910,1270,0,2,NULL,220),(220,913,1259,1,2,NULL,221),(221,899,1232,1,2,NULL,222),(222,901,1200,1,2,NULL,214),(223,600,980,0,1,NULL,224),(224,640,989,0,1,NULL,225),(225,650,1010,1,1,NULL,226),(226,647,1018,0,1,NULL,227),(227,620,1014,0,1,NULL,228),(228,617,1017,1,1,NULL,229),(229,619,1032,0,1,NULL,230),(230,624,1039,1,1,NULL,231),(231,620,1051,0,1,NULL,232),(232,598,1045,1,1,NULL,233),(233,600,1018,0,1,NULL,234),(234,610,994,0,1,NULL,235),(235,615,993,0,1,NULL,236),(236,616,987,1,1,NULL,237),(237,610,984,0,1,NULL,238),(238,594,986,0,1,NULL,239),(239,591,979,1,1,NULL,240),(240,598,975,0,1,NULL,223),(241,2718,1615,0,2,NULL,242),(242,2710,1636,1,2,NULL,243),(243,2732,1669,0,2,NULL,244),(244,2735,1700,0,2,NULL,245),(245,2750,1722,1,2,NULL,246),(246,2749,1752,1,2,NULL,247),(247,2762,1761,1,2,NULL,248),(248,2758,1751,1,2,NULL,249),(249,2759,1720,1,2,NULL,250),(250,2743,1701,0,2,NULL,251),(251,2739,1654,0,2,NULL,252),(252,2719,1638,0,2,NULL,241),(253,2345,1590,0,2,NULL,254),(254,2340,1610,0,2,NULL,255),(255,2316,1659,1,2,NULL,256),(256,2335,1682,0,2,NULL,257),(257,2350,1680,0,2,NULL,258),(258,2362,1655,0,2,NULL,259),(259,2380,1649,0,2,NULL,260),(260,2384,1639,1,2,NULL,261),(261,2369,1594,0,2,NULL,253),(262,2294,1421,0,1,NULL,263),(263,2255,1455,0,1,NULL,264),(264,2212,1527,0,1,NULL,265),(265,2192,1569,1,1,NULL,266),(266,2193,1584,0,1,NULL,267),(267,2197,1616,1,1,NULL,268),(268,2188,1638,1,1,NULL,269),(269,2195,1683,1,1,NULL,270),(270,2194,1704,1,1,NULL,271),(271,2200,1715,0,1,NULL,272),(272,2222,1720,0,1,NULL,273),(273,2228,1725,0,1,NULL,274),(274,2230,1755,0,1,NULL,275),(275,2253,1774,0,1,NULL,276),(276,2291,1771,0,1,NULL,277),(277,2330,1751,0,1,NULL,278),(278,2384,1769,0,1,NULL,279),(279,2386,1774,1,1,NULL,280),(280,2370,1787,0,1,NULL,281),(281,2369,1793,0,1,NULL,282),(282,2345,1805,0,1,NULL,283),(283,2327,1822,1,1,NULL,284),(284,2329,1838,1,1,NULL,285),(285,2325,1846,1,1,NULL,286),(286,2333,1854,0,1,NULL,287),(287,2355,1847,0,1,NULL,288),(288,2384,1851,0,1,NULL,289),(289,2452,1807,0,1,NULL,290),(290,2503,1805,0,1,NULL,291),(291,2530,1813,0,1,NULL,292),(292,2560,1803,0,1,NULL,293),(293,2582,1776,0,1,NULL,294),(294,2591,1715,1,1,NULL,295),(295,2588,1660,0,1,NULL,296),(296,2559,1624,0,1,NULL,297),(297,2549,1604,0,1,NULL,298),(298,2525,1588,0,1,NULL,299),(299,2518,1572,0,1,NULL,300),(300,2485,1559,0,1,NULL,301),(301,2475,1574,1,1,NULL,302),(302,2485,1599,0,1,NULL,303),(303,2518,1615,1,1,NULL,304),(304,2514,1640,1,1,NULL,305),(305,2523,1664,1,1,NULL,306),(306,2508,1691,0,1,NULL,307),(307,2495,1674,1,1,NULL,308),(308,2495,1665,0,1,NULL,309),(309,2490,1660,0,1,NULL,310),(310,2483,1635,0,1,NULL,311),(311,2427,1608,0,1,NULL,312),(312,2414,1595,0,1,NULL,313),(313,2413,1560,0,1,NULL,314),(314,2400,1548,1,1,NULL,315),(315,2404,1534,0,1,NULL,316),(316,2421,1510,0,1,NULL,317),(317,2423,1496,1,1,NULL,318),(318,2377,1431,0,1,NULL,319),(319,2360,1430,0,1,NULL,320),(320,2323,1452,0,1,NULL,321),(321,2313,1446,0,1,NULL,322),(322,2311,1433,0,1,NULL,262),(323,2776,1812,0,1,NULL,324),(324,2814,1806,0,1,NULL,325),(325,2826,1790,1,1,NULL,326),(326,2808,1721,0,1,NULL,327),(327,2785,1693,0,1,NULL,328),(328,2744,1617,1,1,NULL,329),(329,2763,1570,0,1,NULL,330),(330,2824,1512,0,1,NULL,331),(331,2832,1485,1,1,NULL,332),(332,2822,1459,0,1,NULL,333),(333,2790,1440,0,1,NULL,334),(334,2756,1437,0,1,NULL,335),(335,2740,1445,0,1,NULL,336),(336,2712,1446,0,1,NULL,337),(337,2699,1438,0,1,NULL,338),(338,2684,1434,0,1,NULL,339),(339,2665,1444,0,1,NULL,340),(340,2648,1448,0,1,NULL,341),(341,2605,1430,0,1,NULL,342),(342,2575,1429,0,1,NULL,343),(343,2542,1439,0,1,NULL,344),(344,2525,1468,1,1,NULL,345),(345,2528,1480,1,1,NULL,346),(346,2509,1492,0,1,NULL,347),(347,2480,1494,0,1,NULL,348),(348,2446,1520,0,1,NULL,349),(349,2445,1552,1,1,NULL,350),(350,2455,1558,0,1,NULL,351),(351,2474,1542,0,1,NULL,352),(352,2488,1553,0,1,NULL,353),(353,2506,1555,0,1,NULL,354),(354,2517,1566,0,1,NULL,355),(355,2527,1565,0,1,NULL,356),(356,2538,1571,0,1,NULL,357),(357,2563,1574,0,1,NULL,358),(358,2598,1558,0,1,NULL,359),(359,2619,1563,0,1,NULL,360),(360,2628,1587,0,1,NULL,361),(361,2658,1625,0,1,NULL,362),(362,2660,1640,1,1,NULL,363),(363,2646,1674,1,1,NULL,364),(364,2689,1712,1,1,NULL,365),(365,2686,1745,1,1,NULL,366),(366,2711,1761,0,1,NULL,367),(367,2737,1789,0,1,NULL,368),(368,2750,1794,0,1,NULL,369),(369,2751,1805,0,1,NULL,323),(1144,4496,1697,0,1,NULL,1145),(1143,4513,1664,1,1,NULL,1144),(1142,4510,1650,0,1,NULL,1143),(1141,4495,1638,0,1,NULL,1142),(1140,4469,1632,0,1,NULL,1141),(1139,4442,1627,0,1,NULL,1140),(1138,4427,1620,0,1,NULL,1139),(1137,4407,1616,0,1,NULL,1138),(1298,5019,3054,1,1,NULL,1272),(1297,5020,3063,0,1,NULL,1298),(1296,5076,3100,0,1,NULL,2024),(1295,5156,3130,0,1,NULL,1296),(1294,5203,3141,0,1,NULL,1295),(1293,5310,3179,0,1,NULL,1294),(1292,5361,3178,0,1,NULL,1293),(1291,5392,3172,0,1,NULL,1292),(1290,5414,3167,0,1,NULL,1291),(1289,5434,3168,0,1,NULL,1290),(1288,5466,3177,0,1,NULL,1289),(1287,5488,3173,0,1,NULL,1288),(1286,5493,3159,0,1,NULL,2023),(1285,5470,3152,0,1,NULL,1286),(1284,5450,3142,0,1,NULL,1285),(1283,5402,3128,0,1,NULL,1284),(1282,5383,3125,0,1,NULL,1283),(1281,5353,3123,0,1,NULL,1282),(1280,5291,3114,0,1,NULL,1281),(1279,5180,3082,0,1,NULL,1280),(1278,5166,3071,0,1,NULL,1279),(1277,5143,3055,0,1,NULL,1278),(1276,5112,3042,0,1,NULL,1277),(1275,5085,3034,0,1,NULL,1276),(1274,5054,3034,0,1,NULL,1275),(1273,5045,3040,0,1,NULL,1274),(1272,5029,3045,0,1,NULL,1273),(479,246,4390,0,2,NULL,471),(478,234,4414,1,2,NULL,479),(477,237,4423,0,2,NULL,478),(476,263,4418,0,2,NULL,477),(475,270,4409,0,2,NULL,476),(474,281,4405,0,2,NULL,475),(473,288,4389,1,2,NULL,474),(472,277,4381,0,2,NULL,473),(471,261,4380,0,2,NULL,472),(470,164,4473,0,1,NULL,443),(469,178,4499,0,1,NULL,470),(468,179,4524,1,1,NULL,469),(467,143,4583,1,1,NULL,468),(466,146,4633,0,1,NULL,467),(465,156,4640,0,1,NULL,466),(464,188,4629,0,1,NULL,465),(463,210,4601,0,1,NULL,464),(462,231,4582,0,1,NULL,463),(461,274,4487,0,1,NULL,462),(460,296,4463,0,1,NULL,461),(459,334,4442,0,1,NULL,460),(458,364,4404,1,1,NULL,459),(457,363,4387,0,1,NULL,458),(456,305,4312,0,1,NULL,457),(455,290,4283,0,1,NULL,456),(454,277,4275,0,1,NULL,455),(453,266,4262,0,1,NULL,454),(452,254,4259,0,1,NULL,453),(451,239,4265,0,1,NULL,452),(450,231,4273,0,1,NULL,451),(449,214,4324,0,1,NULL,450),(448,212,4343,0,1,NULL,449),(447,202,4365,0,1,NULL,448),(446,188,4383,0,1,NULL,447),(445,162,4405,0,1,NULL,446),(444,156,4425,1,1,NULL,445),(443,157,4447,0,1,NULL,444),(442,320,4042,0,1,NULL,422),(441,281,4089,0,1,NULL,442),(440,262,4126,1,1,NULL,441),(439,263,4135,0,1,NULL,440),(438,271,4145,0,1,NULL,439),(437,284,4149,0,1,NULL,438),(436,309,4158,0,1,NULL,437),(435,319,4184,0,1,NULL,436),(434,320,4214,0,1,NULL,435),(433,328,4232,0,1,NULL,434),(432,338,4238,0,1,NULL,433),(431,353,4229,0,1,NULL,432),(430,354,4210,0,1,NULL,431),(429,358,4179,0,1,NULL,430),(428,371,4158,0,1,NULL,429),(427,419,4138,0,1,NULL,428),(426,438,4121,1,1,NULL,427),(425,435,4107,0,1,NULL,426),(424,388,4068,0,1,NULL,425),(423,347,4044,0,1,NULL,424),(422,325,4041,0,1,NULL,423),(421,382,3872,0,1,NULL,405),(420,372,3892,1,1,NULL,421),(419,373,3906,0,1,NULL,420),(418,389,3927,0,1,NULL,2025),(417,390,3939,1,1,NULL,418),(416,370,3956,1,1,NULL,417),(415,371,3972,0,1,NULL,416),(414,386,3987,0,1,NULL,415),(413,408,3989,0,1,NULL,414),(412,424,3998,0,1,NULL,413),(411,445,3991,0,1,NULL,412),(410,461,3964,1,1,NULL,411),(409,458,3947,0,1,NULL,410),(408,445,3929,1,1,NULL,409),(407,446,3914,1,1,NULL,408),(406,406,3879,0,1,NULL,407),(405,389,3871,0,1,NULL,406),(1136,4428,1401,0,1,NULL,1106),(1135,4427,1414,0,1,NULL,1136),(1134,4421,1436,0,1,NULL,1135),(1133,4412,1447,0,1,NULL,1134),(1132,4411,1458,0,1,NULL,1133),(1131,4403,1477,0,1,NULL,1132),(1130,4402,1496,1,1,NULL,1131),(1129,4406,1503,0,1,NULL,1130),(1128,4431,1528,1,1,NULL,1129),(1127,4429,1535,1,1,NULL,1128),(1126,4433,1539,1,1,NULL,1127),(1125,4432,1560,1,1,NULL,1126),(1124,4434,1577,0,1,NULL,1125),(1123,4446,1584,0,1,NULL,1124),(1122,4456,1588,0,1,NULL,1123),(1121,4463,1588,0,1,NULL,1122),(1120,4479,1576,0,1,NULL,1121),(1119,4495,1550,1,1,NULL,1120),(1118,4494,1515,0,1,NULL,1119),(1117,4485,1501,0,1,NULL,1118),(1116,4462,1484,0,1,NULL,1117),(1115,4447,1468,0,1,NULL,1116),(1114,4446,1462,0,1,NULL,1115),(1113,4441,1452,0,1,NULL,1114),(1112,4440,1427,1,1,NULL,1113),(1111,4458,1410,0,1,NULL,1112),(1110,4461,1405,0,1,NULL,1111),(1109,4462,1397,1,1,NULL,1110),(1108,4451,1388,0,1,NULL,1109),(1107,4438,1389,0,1,NULL,1108),(1106,4430,1391,0,1,NULL,1107),(1105,4409,1277,1,1,NULL,1062),(1104,4406,1286,0,1,NULL,1105),(1103,4401,1293,0,1,NULL,1104),(1102,4390,1299,0,1,NULL,1103),(1101,4373,1303,0,1,NULL,1102),(1100,4343,1297,0,1,NULL,1101),(1099,4331,1296,0,1,NULL,1100),(1098,4323,1303,0,1,NULL,1099),(1097,4321,1313,0,1,NULL,1098),(1096,4317,1318,1,1,NULL,1097),(1095,4318,1334,0,1,NULL,1096),(1094,4324,1343,0,1,NULL,1095),(1093,4326,1359,0,1,NULL,1094),(1092,4327,1370,1,1,NULL,1093),(1091,4321,1376,0,1,NULL,1092),(1090,4310,1387,0,1,NULL,1091),(1089,4296,1408,0,1,NULL,1090),(1088,4290,1414,0,1,NULL,1089),(1087,4288,1427,1,1,NULL,1088),(1086,4296,1433,0,1,NULL,1087),(1085,4307,1435,0,1,NULL,1086),(1084,4316,1437,0,1,NULL,1085),(1083,4323,1434,0,1,NULL,1084),(1082,4338,1404,0,1,NULL,1083),(1081,4344,1399,0,1,NULL,1082),(1080,4348,1383,1,1,NULL,1081),(1079,4347,1360,0,1,NULL,1080),(1078,4337,1337,1,1,NULL,1079),(1077,4338,1326,0,1,NULL,1078),(1076,4346,1320,0,1,NULL,1077),(1075,4357,1322,0,1,NULL,1076),(1074,4363,1327,0,1,NULL,1075),(1073,4373,1332,0,1,NULL,1074),(1072,4382,1335,0,1,NULL,1073),(1071,4404,1335,0,1,NULL,1072),(1070,4425,1313,0,1,NULL,1071),(1069,4428,1302,0,1,NULL,1070),(1068,4442,1287,0,1,NULL,1069),(1067,4448,1271,1,1,NULL,1068),(1066,4446,1262,0,1,NULL,1067),(1065,4439,1254,0,1,NULL,1066),(1064,4420,1254,0,1,NULL,1065),(1063,4414,1262,0,1,NULL,1064),(1062,4408,1264,1,1,NULL,1063),(1061,4502,1140,0,1,NULL,1000),(1060,4512,1160,0,1,NULL,1061),(1059,4513,1167,1,1,NULL,1060),(1058,4498,1169,0,1,NULL,1059),(1057,4495,1167,0,1,NULL,1058),(1056,4484,1167,0,1,NULL,1057),(1055,4479,1178,0,1,NULL,1056),(1054,4478,1189,1,1,NULL,1055),(1053,4486,1210,1,1,NULL,1054),(1052,4474,1220,0,1,NULL,1053),(1051,4461,1219,0,1,NULL,1052),(1050,4451,1224,1,1,NULL,1051),(1049,4452,1236,0,1,NULL,1050),(1048,4462,1246,0,1,NULL,1049),(1047,4472,1247,0,1,NULL,1048),(1046,4485,1237,0,1,NULL,1047),(1045,4492,1235,0,1,NULL,1046),(1044,4502,1226,0,1,NULL,1045),(1043,4511,1220,0,1,NULL,1044),(1042,4512,1211,1,1,NULL,1043),(1041,4505,1199,1,1,NULL,1042),(1040,4506,1190,0,1,NULL,1041),(1039,4510,1190,0,1,NULL,1040),(1038,4516,1192,0,1,NULL,1039),(1037,4524,1203,0,1,NULL,1038),(1036,4527,1216,0,1,NULL,1037),(1035,4532,1225,0,1,NULL,1036),(1034,4538,1234,0,1,NULL,1035),(1033,4547,1233,0,1,NULL,1034),(1032,4549,1244,1,1,NULL,1033),(1031,4546,1251,0,1,NULL,1032),(1030,4537,1254,0,1,NULL,1031),(1029,4521,1254,0,1,NULL,1030),(1028,4513,1250,1,1,NULL,1029),(1027,4514,1245,0,1,NULL,1028),(1026,4519,1234,1,1,NULL,1027),(1025,4516,1230,0,1,NULL,1026),(1024,4510,1233,0,1,NULL,1025),(1022,4502,1239,1,1,NULL,1024),(1021,4503,1249,1,1,NULL,1022),(1020,4498,1253,0,1,NULL,1021),(1019,4481,1257,0,1,NULL,1020),(1018,4472,1262,0,1,NULL,1019),(1017,4463,1274,1,1,NULL,1018),(1016,4464,1287,0,1,NULL,1017),(1015,4469,1298,0,1,NULL,1016),(1014,4476,1301,0,1,NULL,1015),(1013,4487,1300,0,1,NULL,1014),(1012,4502,1280,0,1,NULL,1013),(1011,4513,1273,0,1,NULL,1012),(1010,4537,1272,0,1,NULL,1011),(1009,4553,1273,0,1,NULL,1010),(1008,4567,1260,1,1,NULL,1009),(1007,4566,1227,0,1,NULL,1008),(1006,4560,1214,0,1,NULL,1007),(1005,4540,1188,1,1,NULL,1006),(1004,4541,1178,1,1,NULL,1005),(1003,4539,1171,0,1,NULL,1004),(1002,4525,1142,0,1,NULL,1003),(1001,4515,1130,0,1,NULL,1002),(1000,4501,1131,1,1,NULL,1001),(999,4434,1119,1,1,NULL,978),(998,4429,1131,1,1,NULL,999),(997,4430,1148,1,1,NULL,998),(996,4423,1164,1,1,NULL,997),(995,4424,1173,0,1,NULL,996),(994,4428,1179,0,1,NULL,995),(993,4431,1193,1,1,NULL,994),(992,4430,1198,1,1,NULL,993),(991,4439,1207,0,1,NULL,992),(990,4449,1206,0,1,NULL,991),(989,4458,1197,0,1,NULL,990),(988,4459,1185,1,1,NULL,989),(987,4455,1172,0,1,NULL,988),(986,4451,1162,0,1,NULL,987),(985,4450,1151,1,1,NULL,986),(984,4463,1141,0,1,NULL,985),(983,4465,1128,1,1,NULL,984),(982,4464,1113,0,1,NULL,983),(981,4456,1111,0,1,NULL,982),(980,4448,1107,0,1,NULL,981),(979,4439,1107,0,1,NULL,980),(978,4433,1112,1,1,NULL,979),(977,4370,1100,1,1,NULL,959),(976,4362,1113,0,1,NULL,977),(975,4361,1124,1,1,NULL,976),(974,4363,1126,0,1,NULL,975),(973,4364,1137,0,1,NULL,974),(972,4381,1142,0,1,NULL,973),(971,4387,1142,0,1,NULL,972),(970,4392,1137,0,1,NULL,971),(969,4397,1138,0,1,NULL,970),(968,4400,1139,0,1,NULL,969),(967,4404,1140,0,1,NULL,968),(966,4408,1136,0,1,NULL,967),(965,4411,1129,1,1,NULL,966),(964,4410,1118,0,1,NULL,965),(963,4404,1110,0,1,NULL,964),(962,4395,1106,0,1,NULL,963),(961,4388,1099,0,1,NULL,962),(960,4377,1094,0,1,NULL,961),(959,4369,1096,1,1,NULL,960),(958,4289,1095,0,1,NULL,946),(957,4282,1104,1,1,NULL,958),(956,4283,1116,0,1,NULL,957),(955,4292,1128,0,1,NULL,956),(954,4303,1130,0,1,NULL,955),(953,4309,1125,0,1,NULL,954),(952,4326,1119,0,1,NULL,953),(951,4336,1118,0,1,NULL,952),(950,4337,1109,1,1,NULL,951),(949,4329,1099,0,1,NULL,950),(948,4311,1098,0,1,NULL,949),(947,4300,1092,0,1,NULL,948),(946,4292,1093,0,1,NULL,947),(945,4150,1342,0,1,NULL,938),(944,4158,1343,0,1,NULL,945),(943,4165,1337,0,1,NULL,944),(942,4166,1322,1,1,NULL,943),(941,4159,1316,0,1,NULL,942),(940,4152,1316,0,1,NULL,941),(939,4146,1321,0,1,NULL,940),(938,4145,1337,1,1,NULL,939),(937,4118,1343,0,1,NULL,932),(936,4132,1344,1,1,NULL,937),(935,4131,1335,0,1,NULL,936),(934,4123,1322,0,1,NULL,935),(933,4115,1323,0,1,NULL,934),(932,4114,1336,1,1,NULL,933),(931,3842,1264,0,1,NULL,881),(930,3850,1280,0,1,NULL,931),(929,3862,1288,0,1,NULL,930),(928,3883,1293,0,1,NULL,929),(927,3900,1295,0,1,NULL,928),(926,3922,1287,0,1,NULL,927),(925,3959,1278,0,1,NULL,926),(924,3960,1268,1,1,NULL,925),(923,3946,1248,0,1,NULL,924),(922,3940,1241,0,1,NULL,923),(921,3916,1229,0,1,NULL,922),(920,3913,1220,1,1,NULL,921),(919,3920,1210,0,1,NULL,920),(918,3939,1199,0,1,NULL,919),(917,3968,1176,0,1,NULL,918),(916,3969,1167,1,1,NULL,917),(915,3961,1159,0,1,NULL,916),(914,3938,1159,0,1,NULL,915),(913,3927,1172,0,1,NULL,914),(912,3909,1186,0,1,NULL,913),(911,3894,1190,0,1,NULL,912),(910,3872,1190,0,1,NULL,911),(909,3860,1194,0,1,NULL,910),(908,3827,1196,0,1,NULL,909),(907,3805,1203,0,1,NULL,908),(906,3798,1206,0,1,NULL,907),(905,3795,1210,0,1,NULL,906),(904,3781,1215,0,1,NULL,905),(903,3761,1215,0,1,NULL,904),(902,3725,1210,0,1,NULL,903),(901,3691,1199,0,1,NULL,902),(900,3682,1200,0,1,NULL,901),(899,3672,1204,0,1,NULL,900),(898,3664,1229,1,1,NULL,899),(897,3665,1243,0,1,NULL,898),(896,3672,1262,0,1,NULL,897),(895,3673,1277,1,1,NULL,896),(894,3668,1282,1,1,NULL,895),(893,3669,1293,0,1,NULL,894),(892,3682,1309,0,1,NULL,893),(891,3689,1310,0,1,NULL,892),(890,3695,1306,0,1,NULL,891),(889,3702,1297,1,1,NULL,890),(888,3701,1278,1,1,NULL,889),(887,3703,1265,0,1,NULL,888),(886,3711,1255,0,1,NULL,887),(885,3761,1260,0,1,NULL,886),(884,3783,1259,0,1,NULL,885),(883,3805,1251,0,1,NULL,884),(882,3824,1251,0,1,NULL,883),(881,3838,1257,0,1,NULL,882),(880,3221,1233,0,1,NULL,524),(879,3224,1240,0,1,NULL,880),(878,3250,1273,0,1,NULL,879),(877,3271,1295,0,1,NULL,878),(876,3281,1312,0,1,NULL,877),(875,3298,1339,1,1,NULL,876),(874,3292,1353,0,1,NULL,875),(873,3281,1361,0,1,NULL,874),(872,3275,1369,0,1,NULL,873),(871,3254,1381,0,1,NULL,872),(870,3237,1385,0,1,NULL,871),(869,3226,1386,0,1,NULL,870),(868,3216,1380,0,1,NULL,869),(867,3209,1366,0,1,NULL,868),(866,3198,1355,0,1,NULL,867),(865,3191,1340,1,1,NULL,866),(864,3209,1322,0,1,NULL,865),(863,3217,1301,0,1,NULL,864),(862,3218,1278,1,1,NULL,863),(861,3193,1249,0,1,NULL,862),(860,3186,1227,0,1,NULL,861),(859,3175,1213,0,1,NULL,860),(858,3160,1209,0,1,NULL,859),(857,3149,1209,0,1,NULL,858),(856,3134,1218,0,1,NULL,857),(855,3124,1244,0,1,NULL,856),(854,3123,1256,0,1,NULL,855),(853,3120,1264,0,1,NULL,854),(852,3116,1291,0,1,NULL,853),(851,3111,1312,0,1,NULL,852),(850,3100,1338,0,1,NULL,851),(849,3096,1342,0,1,NULL,850),(848,3095,1379,1,1,NULL,849),(847,3102,1395,0,1,NULL,848),(846,3115,1414,0,1,NULL,847),(845,3122,1419,0,1,NULL,846),(844,3190,1440,0,1,NULL,845),(843,3227,1445,0,1,NULL,844),(842,3259,1448,0,1,NULL,843),(841,3294,1443,0,1,NULL,842),(840,3326,1418,0,1,NULL,841),(839,3370,1419,0,1,NULL,840),(838,3376,1414,0,1,NULL,839),(837,3388,1415,0,1,NULL,838),(836,3417,1403,0,1,NULL,837),(835,3428,1388,0,1,NULL,836),(834,3429,1359,0,1,NULL,835),(833,3432,1350,0,1,NULL,834),(832,3454,1339,0,1,NULL,833),(831,3461,1332,0,1,NULL,832),(830,3463,1321,1,1,NULL,831),(829,3451,1300,0,1,NULL,830),(828,3448,1285,1,1,NULL,829),(827,3480,1280,0,1,NULL,828),(826,3575,1279,0,1,NULL,827),(825,3578,1296,0,1,NULL,826),(824,3590,1327,0,1,NULL,825),(823,3591,1342,0,1,NULL,824),(822,3597,1360,0,1,NULL,823),(821,3608,1391,0,1,NULL,822),(820,3609,1414,1,1,NULL,821),(819,3604,1422,0,1,NULL,820),(818,3548,1500,0,1,NULL,819),(817,3540,1507,0,1,NULL,818),(816,3521,1511,0,1,NULL,817),(815,3460,1510,0,1,NULL,816),(814,3446,1512,0,1,NULL,815),(813,3419,1526,0,1,NULL,814),(812,3400,1529,0,1,NULL,813),(811,3326,1527,0,1,NULL,812),(810,3296,1516,0,1,NULL,811),(809,3251,1513,0,1,NULL,810),(808,3223,1515,0,1,NULL,809),(807,3213,1518,0,1,NULL,808),(806,3184,1511,0,1,NULL,807),(805,3179,1497,0,1,NULL,806),(804,3162,1495,0,1,NULL,805),(803,3139,1501,0,1,NULL,804),(802,3133,1511,0,1,NULL,803),(801,3120,1519,0,1,NULL,802),(800,3104,1518,0,1,NULL,801),(799,3054,1534,0,1,NULL,800),(798,3036,1530,0,1,NULL,799),(797,3031,1549,1,1,NULL,798),(796,3032,1560,0,1,NULL,797),(795,3048,1580,0,1,NULL,796),(794,3059,1606,0,1,NULL,795),(793,3062,1670,0,1,NULL,794),(792,3066,1682,0,1,NULL,793),(791,3078,1694,0,1,NULL,792),(790,3128,1708,1,1,NULL,791),(789,3127,1730,0,1,NULL,790),(788,3107,1733,0,1,NULL,789),(787,3104,1749,1,1,NULL,788),(786,3108,1770,1,1,NULL,787),(785,3105,1789,0,1,NULL,786),(784,3081,1837,0,1,NULL,785),(783,3073,1841,0,1,NULL,784),(782,3064,1840,0,1,NULL,783),(781,3041,1846,0,1,NULL,782),(780,3024,1872,1,1,NULL,781),(779,3028,1884,1,1,NULL,780),(778,3027,1900,1,1,NULL,779),(777,3040,1951,0,1,NULL,778),(776,3061,1983,0,1,NULL,777),(775,3069,1986,0,1,NULL,776),(774,3078,1996,0,1,NULL,775),(773,3097,2005,0,1,NULL,774),(772,3142,2009,0,1,NULL,773),(771,3167,2003,0,1,NULL,772),(770,3212,2004,0,1,NULL,771),(769,3239,2025,0,1,NULL,770),(768,3245,2057,1,1,NULL,769),(767,3230,2084,0,1,NULL,768),(766,3212,2121,0,1,NULL,767),(765,3211,2134,0,1,NULL,766),(764,3205,2140,0,1,NULL,765),(763,3196,2163,0,1,NULL,764),(762,3189,2172,0,1,NULL,763),(761,3143,2195,0,1,NULL,762),(760,3132,2215,1,1,NULL,761),(759,3133,2225,1,1,NULL,760),(758,3127,2247,1,1,NULL,759),(757,3141,2276,0,1,NULL,758),(756,3191,2296,0,1,NULL,757),(755,3226,2297,0,1,NULL,756),(754,3265,2293,0,1,NULL,755),(753,3282,2289,0,1,NULL,754),(752,3305,2275,0,1,NULL,753),(751,3330,2253,0,1,NULL,752),(750,3368,2211,0,1,NULL,751),(749,3386,2204,0,1,NULL,750),(748,3393,2208,0,1,NULL,749),(747,3395,2216,0,1,NULL,748),(746,3396,2233,1,1,NULL,747),(745,3383,2261,0,1,NULL,746),(744,3378,2268,0,1,NULL,745),(743,3354,2285,0,1,NULL,744),(742,3342,2289,0,1,NULL,743),(741,3333,2290,0,1,NULL,742),(740,3324,2294,0,1,NULL,741),(739,3321,2307,1,1,NULL,740),(738,3329,2314,0,1,NULL,739),(737,3354,2316,0,1,NULL,738),(736,3360,2313,0,1,NULL,737),(735,3366,2306,0,1,NULL,736),(734,3378,2294,0,1,NULL,735),(733,3402,2284,0,1,NULL,734),(732,3436,2286,0,1,NULL,733),(731,3483,2286,0,1,NULL,732),(730,3496,2281,0,1,NULL,731),(729,3503,2268,0,1,NULL,730),(728,3504,2256,1,1,NULL,729),(727,3464,2219,0,1,NULL,728),(726,3463,2211,1,1,NULL,727),(725,3489,2191,0,1,NULL,726),(724,3500,2185,0,1,NULL,725),(723,3631,2188,0,1,NULL,724),(722,3646,2189,0,1,NULL,723),(721,3680,2214,0,1,NULL,722),(720,3694,2216,0,1,NULL,721),(719,3723,2248,0,1,NULL,720),(718,3735,2260,0,1,NULL,719),(717,3762,2280,0,1,NULL,718),(716,3792,2298,0,1,NULL,717),(715,3813,2298,0,1,NULL,716),(714,3841,2284,0,1,NULL,715),(713,3854,2284,0,1,NULL,714),(712,3861,2278,0,1,NULL,713),(711,3876,2272,0,1,NULL,712),(710,3883,2267,0,1,NULL,711),(709,3890,2267,0,1,NULL,710),(708,3915,2258,0,1,NULL,709),(707,3961,2227,0,1,NULL,708),(706,4013,2177,0,1,NULL,707),(705,4037,2158,0,1,NULL,706),(704,4054,2159,0,1,NULL,705),(703,4087,2156,0,1,NULL,704),(702,4108,2146,0,1,NULL,703),(701,4129,2123,0,1,NULL,702),(700,4143,2095,0,1,NULL,701),(699,4144,2075,1,1,NULL,700),(698,4138,2057,1,1,NULL,699),(697,4141,2013,0,1,NULL,698),(696,4146,2002,0,1,NULL,697),(695,4165,1974,0,1,NULL,696),(694,4185,1965,0,1,NULL,695),(693,4199,1959,0,1,NULL,694),(692,4220,1936,0,1,NULL,693),(691,4241,1917,0,1,NULL,692),(690,4264,1883,0,1,NULL,691),(689,4300,1791,0,1,NULL,690),(688,4302,1782,0,1,NULL,689),(687,4304,1763,1,1,NULL,688),(686,4300,1757,0,1,NULL,687),(685,4253,1740,0,1,NULL,686),(684,4226,1724,0,1,NULL,685),(683,4200,1715,0,1,NULL,684),(682,4187,1703,0,1,NULL,683),(681,4158,1632,0,1,NULL,682),(680,4146,1613,0,1,NULL,681),(679,4124,1597,0,1,NULL,680),(678,4056,1571,0,1,NULL,679),(677,4036,1570,0,1,NULL,678),(676,4034,1565,1,1,NULL,677),(675,4044,1540,0,1,NULL,676),(674,4059,1514,0,1,NULL,675),(673,4065,1493,0,1,NULL,674),(672,4074,1478,0,1,NULL,673),(671,4106,1449,0,1,NULL,672),(670,4186,1426,0,1,NULL,671),(669,4196,1425,0,1,NULL,670),(668,4224,1415,0,1,NULL,669),(667,4249,1393,0,1,NULL,668),(666,4271,1360,0,1,NULL,667),(665,4286,1327,0,1,NULL,666),(664,4287,1318,1,1,NULL,665),(663,4278,1304,0,1,NULL,664),(662,4264,1297,0,1,NULL,663),(661,4243,1297,0,1,NULL,662),(660,4223,1309,0,1,NULL,661),(659,4197,1333,0,1,NULL,660),(658,4170,1350,0,1,NULL,659),(657,4147,1361,0,1,NULL,658),(656,4120,1359,0,1,NULL,657),(655,4106,1342,0,1,NULL,656),(654,4092,1306,0,1,NULL,655),(653,4091,1277,0,1,NULL,654),(652,4089,1259,0,1,NULL,653),(651,4079,1245,0,1,NULL,652),(650,4073,1245,0,1,NULL,651),(649,4051,1253,0,1,NULL,650),(648,4022,1273,0,1,NULL,649),(647,3994,1300,0,1,NULL,648),(646,3971,1317,0,1,NULL,647),(645,3915,1338,0,1,NULL,646),(644,3907,1340,0,1,NULL,645),(643,3892,1345,0,1,NULL,644),(642,3880,1345,0,1,NULL,643),(641,3879,1347,0,1,NULL,642),(640,3846,1346,0,1,NULL,641),(639,3832,1334,0,1,NULL,640),(638,3830,1326,0,1,NULL,639),(637,3827,1310,0,1,NULL,638),(636,3813,1294,0,1,NULL,637),(635,3800,1294,0,1,NULL,636),(634,3783,1300,0,1,NULL,635),(633,3762,1304,0,1,NULL,634),(632,3752,1309,0,1,NULL,633),(631,3734,1335,0,1,NULL,632),(630,3728,1347,0,1,NULL,631),(629,3716,1356,0,1,NULL,630),(628,3694,1359,0,1,NULL,629),(627,3683,1362,0,1,NULL,628),(626,3666,1354,0,1,NULL,627),(625,3659,1329,0,1,NULL,626),(624,3644,1303,0,1,NULL,625),(623,3635,1297,0,1,NULL,624),(622,3623,1295,0,1,NULL,623),(621,3614,1293,0,1,NULL,622),(620,3606,1280,1,1,NULL,621),(619,3617,1263,1,1,NULL,620),(618,3616,1233,0,1,NULL,619),(617,3608,1208,0,1,NULL,618),(616,3584,1179,0,1,NULL,617),(615,3583,1166,1,1,NULL,616),(614,3599,1158,0,1,NULL,615),(613,3628,1159,0,1,NULL,614),(612,3691,1169,0,1,NULL,613),(611,3787,1169,0,1,NULL,612),(610,3855,1152,0,1,NULL,611),(609,3901,1151,0,1,NULL,610),(608,3913,1147,0,1,NULL,609),(607,3951,1121,0,1,NULL,608),(606,3978,1122,0,1,NULL,607),(605,3988,1130,0,1,NULL,606),(604,4001,1135,0,1,NULL,605),(603,4005,1142,1,1,NULL,604),(602,3992,1164,0,1,NULL,603),(601,3991,1178,0,1,NULL,602),(600,3980,1192,1,1,NULL,601),(599,3981,1200,0,1,NULL,600),(598,3988,1207,0,1,NULL,599),(597,4012,1215,0,1,NULL,598),(596,4063,1199,0,1,NULL,597),(595,4078,1198,0,1,NULL,596),(594,4090,1206,0,1,NULL,595),(593,4112,1239,0,1,NULL,594),(592,4145,1240,0,1,NULL,593),(591,4167,1249,0,1,NULL,592),(590,4177,1271,0,1,NULL,591),(589,4178,1271,0,1,NULL,590),(588,4189,1268,0,1,NULL,589),(587,4211,1263,0,1,NULL,588),(586,4240,1265,0,1,NULL,587),(585,4253,1273,0,1,NULL,586),(584,4273,1278,0,1,NULL,585),(583,4290,1275,0,1,NULL,584),(582,4316,1264,0,1,NULL,583),(581,4326,1264,0,1,NULL,582),(580,4342,1279,0,1,NULL,581),(579,4346,1281,0,1,NULL,580),(578,4364,1276,0,1,NULL,579),(577,4384,1263,0,1,NULL,578),(576,4399,1249,0,1,NULL,577),(575,4409,1221,1,1,NULL,576),(574,4408,1190,0,1,NULL,575),(573,4389,1172,0,1,NULL,574),(572,4371,1165,0,1,NULL,573),(571,4345,1165,0,1,NULL,572),(570,4301,1160,0,1,NULL,571),(569,4270,1148,0,1,NULL,570),(568,4260,1137,0,1,NULL,569),(567,4258,1125,0,1,NULL,568),(566,4252,1110,1,1,NULL,567),(565,4254,1100,1,1,NULL,566),(564,4243,1091,0,1,NULL,565),(563,4233,1088,0,1,NULL,564),(562,4185,1088,0,1,NULL,563),(561,4163,1092,0,1,NULL,562),(560,4148,1098,0,1,NULL,561),(559,4115,1100,0,1,NULL,560),(558,4077,1096,0,1,NULL,559),(557,4051,1083,0,1,NULL,558),(556,4038,1070,0,1,NULL,557),(555,4009,1050,0,1,NULL,556),(554,3979,1049,0,1,NULL,555),(553,3958,1057,0,1,NULL,554),(552,3941,1064,0,1,NULL,553),(551,3926,1065,0,1,NULL,552),(550,3850,1089,0,1,NULL,551),(549,3837,1095,0,1,NULL,550),(548,3792,1099,0,1,NULL,549),(547,3730,1091,0,1,NULL,548),(546,3663,1090,0,1,NULL,547),(545,3623,1100,0,1,NULL,546),(544,3608,1100,0,1,NULL,545),(543,3590,1106,0,1,NULL,544),(542,3578,1106,0,1,NULL,543),(541,3547,1116,0,1,NULL,542),(540,3524,1116,0,1,NULL,541),(539,3487,1119,0,1,NULL,540),(538,3452,1113,0,1,NULL,539),(537,3435,1110,0,1,NULL,538),(536,3403,1100,0,1,NULL,537),(535,3358,1100,0,1,NULL,536),(534,3351,1104,0,1,NULL,535),(533,3336,1108,0,1,NULL,534),(532,3325,1118,0,1,NULL,533),(531,3307,1127,0,1,NULL,532),(530,3288,1129,0,1,NULL,531),(529,3270,1122,0,1,NULL,530),(528,3258,1122,0,1,NULL,529),(527,3241,1117,0,1,NULL,528),(526,3229,1119,0,1,NULL,527),(525,3201,1147,0,1,NULL,526),(524,3200,1154,1,1,NULL,525),(523,3287,1097,0,1,NULL,514),(522,3288,1102,0,1,NULL,523),(521,3298,1106,0,1,NULL,522),(520,3315,1107,0,1,NULL,521),(519,3324,1095,0,1,NULL,520),(518,3325,1076,1,1,NULL,519),(517,3314,1069,0,1,NULL,518),(516,3303,1069,0,1,NULL,517),(515,3294,1073,0,1,NULL,516),(514,3286,1081,1,1,NULL,515),(513,3098,1183,0,1,NULL,480),(512,3091,1189,0,1,NULL,513),(511,3082,1200,0,1,NULL,512),(510,3079,1209,0,1,NULL,511),(509,3078,1228,1,1,NULL,510),(508,3083,1234,0,1,NULL,509),(507,3089,1234,0,1,NULL,508),(506,3105,1227,0,1,NULL,507),(505,3116,1210,0,1,NULL,506),(504,3149,1186,0,1,NULL,505),(503,3168,1175,0,1,NULL,504),(502,3176,1159,0,1,NULL,503),(501,3178,1134,0,1,NULL,502),(500,3193,1114,0,1,NULL,501),(499,3205,1109,0,1,NULL,500),(498,3223,1096,0,1,NULL,499),(497,3254,1092,0,1,NULL,498),(496,3268,1080,0,1,NULL,497),(495,3269,1071,1,1,NULL,496),(494,3265,1065,0,1,NULL,495),(493,3250,1059,0,1,NULL,494),(492,3222,1057,0,1,NULL,493),(491,3194,1063,0,1,NULL,492),(490,3166,1076,0,1,NULL,491),(489,3155,1087,0,1,NULL,490),(488,3143,1095,0,1,NULL,489),(487,3132,1103,0,1,NULL,488),(486,3125,1113,0,1,NULL,487),(485,3121,1121,0,1,NULL,486),(484,3105,1143,0,1,NULL,485),(483,3102,1150,0,1,NULL,484),(482,3101,1159,1,1,NULL,483),(481,3104,1171,1,1,NULL,482),(480,3103,1176,0,1,NULL,481),(1145,4495,1703,0,1,NULL,1146),(1146,4487,1714,0,1,NULL,1147),(1147,4477,1721,0,1,NULL,1148),(1148,4451,1753,0,1,NULL,1149),(1149,4448,1782,0,1,NULL,1150),(1150,4441,1792,1,1,NULL,1151),(1151,4442,1807,0,1,NULL,1152),(1152,4453,1839,0,1,NULL,1153),(1153,4457,1861,1,1,NULL,1154),(1154,4456,1890,0,1,NULL,1155),(1155,4449,1913,0,1,NULL,1156),(1156,4435,1943,0,1,NULL,1157),(1157,4428,1948,0,1,NULL,1158),(1158,4421,1948,0,1,NULL,1159),(1159,4414,1942,0,1,NULL,1160),(1160,4378,1889,0,1,NULL,1161),(1161,4364,1862,0,1,NULL,1162),(1162,4358,1832,0,1,NULL,1163),(1163,4355,1799,1,1,NULL,1164),(1164,4369,1766,0,1,NULL,1165),(1165,4377,1757,0,1,NULL,1166),(1166,4382,1736,0,1,NULL,1167),(1167,4384,1712,0,1,NULL,1168),(1168,4387,1658,0,1,NULL,1169),(1169,4393,1640,0,1,NULL,1170),(1170,4404,1624,0,1,NULL,1137),(1171,4378,2112,0,1,NULL,1172),(1172,4390,2107,0,1,NULL,1173),(1173,4399,2110,1,1,NULL,1174),(1174,4392,2119,1,1,NULL,1175),(1175,4393,2143,0,1,NULL,1176),(1176,4399,2154,0,1,NULL,1177),(1177,4407,2162,0,1,NULL,1178),(1178,4412,2173,0,1,NULL,1179),(1179,4441,2225,1,1,NULL,1180),(1180,4440,2263,0,1,NULL,1181),(1181,4434,2280,0,1,NULL,1182),(1182,4417,2291,0,1,NULL,1183),(1183,4402,2291,0,1,NULL,1184),(1184,4392,2277,1,1,NULL,1185),(1185,4393,2263,0,1,NULL,1186),(1186,4405,2243,1,1,NULL,1187),(1187,4398,2200,0,1,NULL,1188),(1188,4388,2185,0,1,NULL,1189),(1189,4373,2177,0,1,NULL,1190),(1190,4364,2171,0,1,NULL,1191),(1191,4361,2164,0,1,NULL,1192),(1192,4359,2143,1,1,NULL,1193),(1193,4362,2129,0,1,NULL,1194),(1194,4366,2124,0,1,NULL,1195),(1195,4376,2117,0,1,NULL,1171),(1196,4271,2213,0,1,NULL,1197),(1197,4295,2212,0,1,NULL,1198),(1198,4300,2214,0,1,NULL,1199),(1199,4309,2221,0,1,NULL,1200),(1200,4313,2230,0,1,NULL,1201),(1201,4321,2235,0,1,NULL,1202),(1202,4322,2288,1,1,NULL,1203),(1203,4314,2296,0,1,NULL,1204),(1204,4307,2299,0,1,NULL,1205),(1205,4293,2300,0,1,NULL,1206),(1206,4281,2295,0,1,NULL,1207),(1207,4270,2294,0,1,NULL,1208),(1208,4261,2292,0,1,NULL,1209),(1209,4252,2281,0,1,NULL,1210),(1210,4249,2275,0,1,NULL,1211),(1211,4248,2254,1,1,NULL,1212),(1212,4256,2242,0,1,NULL,1213),(1213,4259,2223,0,1,NULL,1196),(1214,4471,2213,1,1,NULL,1215),(1215,4481,2201,0,1,NULL,1216),(1216,4489,2197,0,1,NULL,1217),(1217,4496,2199,0,1,NULL,1218),(1218,4510,2211,1,1,NULL,1219),(1219,4504,2327,0,1,NULL,1220),(1220,4485,2347,0,1,NULL,1221),(1221,4475,2350,0,1,NULL,1222),(1222,4429,2354,0,1,NULL,1223),(1223,4417,2360,0,1,NULL,1224),(1224,4403,2360,0,1,NULL,1225),(1225,4380,2347,0,1,NULL,1226),(1226,4370,2338,0,1,NULL,1227),(1227,4369,2316,1,1,NULL,1228),(1228,4373,2316,0,1,NULL,1229),(1229,4381,2321,0,1,NULL,1230),(1230,4382,2326,0,1,NULL,1231),(1231,4394,2336,0,1,NULL,1232),(1232,4401,2336,0,1,NULL,1233),(1233,4407,2331,0,1,NULL,1234),(1234,4425,2331,0,1,NULL,1235),(1235,4436,2325,0,1,NULL,1236),(1236,4447,2324,0,1,NULL,1237),(1237,4456,2318,0,1,NULL,1238),(1238,4470,2315,0,1,NULL,1239),(1239,4479,2303,0,1,NULL,1240),(1240,4483,2295,0,1,NULL,1241),(1241,4484,2264,1,1,NULL,1242),(1242,4472,2239,0,1,NULL,1214),(1243,3541,2260,1,1,NULL,1244),(1244,3555,2249,0,1,NULL,1245),(1245,3590,2249,0,1,NULL,1246),(1246,3599,2246,0,1,NULL,1247),(1247,3609,2239,0,1,NULL,1248),(1248,3637,2240,0,1,NULL,1249),(1249,3647,2245,0,1,NULL,1250),(1250,3656,2251,0,1,NULL,1251),(1251,3669,2257,0,1,NULL,1252),(1252,3687,2262,0,1,NULL,1253),(1253,3688,2267,0,1,NULL,1254),(1254,3696,2269,0,1,NULL,1255),(1255,3700,2268,0,1,NULL,1256),(1256,3723,2274,0,1,NULL,1257),(1257,3736,2288,0,1,NULL,1258),(1258,3748,2307,0,1,NULL,1259),(1259,3749,2330,1,1,NULL,1260),(1260,3722,2327,0,1,NULL,1261),(1261,3710,2313,0,1,NULL,1262),(1262,3695,2304,0,1,NULL,1263),(1263,3673,2301,0,1,NULL,1264),(1264,3655,2296,0,1,NULL,1265),(1265,3632,2293,0,1,NULL,1266),(1266,3629,2290,0,1,NULL,1267),(1267,3579,2290,0,1,NULL,1268),(1268,3574,2288,0,1,NULL,1269),(1269,3560,2288,0,1,NULL,1270),(1270,3551,2284,0,1,NULL,1271),(1271,3542,2275,0,1,NULL,1243),(1299,708,1851,0,1,NULL,1300),(1300,731,1847,0,1,NULL,1301),(1301,766,1843,0,1,NULL,1302),(1302,786,1854,0,1,NULL,1303),(1303,801,1859,0,1,NULL,1304),(1304,823,1865,0,1,NULL,1305),(1305,828,1865,0,1,NULL,1306),(1306,844,1871,0,1,NULL,1307),(1307,865,1873,0,1,NULL,1308),(1308,882,1859,0,1,NULL,1309),(1309,911,1851,0,1,NULL,1310),(1310,923,1851,0,1,NULL,1311),(1311,948,1854,0,1,NULL,1312),(1312,973,1855,0,1,NULL,1313),(1313,981,1850,0,1,NULL,1314),(1314,1019,1847,0,1,NULL,1315),(1315,1031,1838,0,1,NULL,1316),(1316,1038,1829,0,1,NULL,1317),(1317,1043,1827,1,1,NULL,1318),(1318,1006,1817,0,1,NULL,1319),(1319,1000,1811,1,1,NULL,1320),(1320,1005,1801,0,1,NULL,1321),(1321,1024,1792,0,1,NULL,1322),(1322,1043,1793,0,1,NULL,1323),(1323,1083,1795,0,1,NULL,1324),(1324,1098,1792,0,1,NULL,1325),(1325,1149,1794,0,1,NULL,1326),(1326,1158,1799,1,1,NULL,1327),(1327,1156,1809,0,1,NULL,1328),(1328,1131,1814,0,1,NULL,1329),(1329,1123,1822,1,1,NULL,1330),(1330,1158,1855,1,1,NULL,1331),(1331,1147,1874,0,1,NULL,1332),(1332,1131,1881,0,1,NULL,1333),(1333,1116,1892,0,1,NULL,1334),(1334,1100,1917,0,1,NULL,1335),(1335,1098,1933,1,1,NULL,1336),(1336,1117,1961,0,1,NULL,1337),(1337,1118,2013,0,1,NULL,1338),(1338,1120,2027,1,1,NULL,1339),(1339,1111,2036,0,1,NULL,1340),(1340,1093,2050,0,1,NULL,1341),(1341,1083,2082,1,1,NULL,1342),(1342,1093,2123,1,1,NULL,1343),(1343,1087,2134,0,1,NULL,1344),(1344,1070,2144,0,1,NULL,1345),(1345,1055,2142,0,1,NULL,1346),(1346,1042,2137,0,1,NULL,1347),(1347,1036,2127,0,1,NULL,1348),(1348,995,2125,0,1,NULL,1349),(1349,980,2132,0,1,NULL,1350),(1350,968,2131,0,1,NULL,1351),(1351,952,2112,0,1,NULL,1352),(1352,949,2094,1,1,NULL,1353),(1353,967,2063,0,1,NULL,1354),(1354,977,2053,0,1,NULL,1355),(1355,983,2034,0,1,NULL,1356),(1356,998,2014,0,1,NULL,1357),(1357,1016,2010,0,1,NULL,1358),(1358,1032,1999,0,1,NULL,1359),(1359,1043,1975,1,1,NULL,1360),(1360,1033,1962,0,1,NULL,1361),(1361,1005,1975,0,1,NULL,1362),(1362,1002,1982,0,1,NULL,1363),(1363,991,1987,0,1,NULL,1364),(1364,976,2002,0,1,NULL,1365),(1365,969,2011,0,1,NULL,1366),(1366,967,2022,1,1,NULL,1367),(1367,968,2032,1,1,NULL,1368),(1368,958,2043,0,1,NULL,1369),(1369,953,2044,0,1,NULL,1370),(1370,945,2052,1,1,NULL,1371),(1371,946,2081,1,1,NULL,1372),(1372,926,2094,0,1,NULL,1373),(1373,921,2104,1,1,NULL,1374),(1374,923,2110,0,1,NULL,1375),(1375,924,2119,1,1,NULL,1376),(1376,907,2127,0,1,NULL,1377),(1377,899,2126,0,1,NULL,1378),(1378,889,2133,0,1,NULL,1379),(1379,857,2182,0,1,NULL,1380),(1380,851,2185,0,1,NULL,1381),(1381,850,2247,0,1,NULL,1382),(1382,836,2268,0,1,NULL,1383),(1383,818,2273,0,1,NULL,1384),(1384,808,2272,0,1,NULL,1385),(1385,784,2259,0,1,NULL,1386),(1386,767,2236,0,1,NULL,1387),(1387,764,2206,0,1,NULL,1388),(1388,750,2174,0,1,NULL,1389),(1389,694,2132,0,1,NULL,1390),(1390,674,2076,1,1,NULL,1391),(1391,676,2035,0,1,NULL,1392),(1392,681,1997,1,1,NULL,1393),(1393,669,1976,0,1,NULL,1394),(1394,657,1913,0,1,NULL,1395),(1395,644,1899,0,1,NULL,1396),(1396,619,1893,0,1,NULL,1397),(1397,595,1875,0,1,NULL,1398),(1398,588,1865,1,1,NULL,1399),(1399,589,1848,0,1,NULL,1400),(1400,610,1824,0,1,NULL,1401),(1401,624,1822,0,1,NULL,1402),(1402,637,1810,0,1,NULL,1403),(1403,646,1805,0,1,NULL,1404),(1404,670,1809,0,1,NULL,1405),(1405,686,1813,0,1,NULL,1406),(1406,703,1834,0,1,NULL,1407),(1407,704,1842,0,1,NULL,1299),(1408,810,1960,0,2,NULL,1409),(1409,817,1957,0,2,NULL,1410),(1410,837,1960,0,2,NULL,1411),(1411,838,1967,0,2,NULL,1412),(1412,844,1979,0,2,NULL,1413),(1413,849,1977,0,2,NULL,1414),(1414,854,1965,0,2,NULL,1415),(1415,868,1961,0,2,NULL,1416),(1416,879,1968,0,2,NULL,1417),(1417,880,1980,1,2,NULL,1418),(1418,874,1987,0,2,NULL,1419),(1419,864,1991,0,2,NULL,1420),(1420,849,1991,0,2,NULL,1421),(1421,843,1998,1,2,NULL,1422),(1422,844,2005,0,2,NULL,1423),(1423,846,2023,1,2,NULL,1424),(1424,842,2030,0,2,NULL,1425),(1425,829,2037,0,2,NULL,1426),(1426,820,2054,0,2,NULL,1427),(1427,809,2078,0,2,NULL,1428),(1428,802,2081,0,2,NULL,1429),(1429,800,2077,0,2,NULL,1430),(1430,791,2075,0,2,NULL,1431),(1431,781,2029,1,2,NULL,1432),(1432,812,2018,0,2,NULL,1433),(1433,822,2007,1,2,NULL,1434),(1434,821,2000,0,2,NULL,1435),(1435,812,1992,0,2,NULL,1436),(1436,811,1978,0,2,NULL,1437),(1437,807,1966,1,2,NULL,1408),(1438,2075,2130,0,1,NULL,1439),(1439,2084,2131,0,1,NULL,1440),(1440,2096,2125,0,1,NULL,1441),(1441,2103,2125,0,1,NULL,1442),(1442,2109,2128,0,1,NULL,370),(370,2127,2129,0,1,NULL,371),(371,2128,2146,1,1,NULL,372),(372,2120,2158,0,1,NULL,373),(373,2114,2159,0,1,NULL,374),(374,2111,2166,0,1,NULL,375),(375,2103,2174,0,1,NULL,376),(376,2097,2186,0,1,NULL,377),(377,2081,2198,0,1,NULL,378),(378,2076,2218,1,1,NULL,379),(379,2077,2238,1,1,NULL,380),(380,2066,2263,0,1,NULL,381),(381,2057,2283,1,1,NULL,382),(382,2060,2315,0,1,NULL,383),(383,2061,2340,1,1,NULL,384),(384,2047,2383,0,1,NULL,385),(385,2036,2410,0,1,NULL,386),(386,2027,2413,0,1,NULL,387),(387,2007,2403,0,1,NULL,388),(388,1991,2391,0,1,NULL,389),(389,1986,2382,0,1,NULL,390),(390,1957,2353,0,1,NULL,391),(391,1953,2346,1,1,NULL,392),(392,1955,2339,0,1,NULL,393),(393,1959,2335,0,1,NULL,394),(394,2004,2322,0,1,NULL,395),(395,2007,2314,1,1,NULL,396),(396,2004,2297,0,1,NULL,397),(397,1999,2286,0,1,NULL,398),(398,1998,2243,1,1,NULL,399),(399,2006,2219,1,1,NULL,400),(400,2004,2193,1,1,NULL,401),(401,2013,2178,0,1,NULL,402),(402,2034,2162,0,1,NULL,403),(403,2048,2153,0,1,NULL,404),(404,2066,2134,0,1,NULL,1438),(1443,1795,2452,0,1,NULL,1444),(1444,1838,2421,0,1,NULL,1445),(1445,1858,2418,0,1,NULL,1446),(1446,1869,2421,0,1,NULL,1447),(1447,1878,2420,0,1,NULL,1448),(1448,1894,2410,0,1,NULL,1449),(1449,1911,2410,0,1,NULL,1450),(1450,1932,2416,0,1,NULL,1451),(1451,1952,2433,1,1,NULL,1452),(1452,1951,2454,0,1,NULL,1453),(1453,1935,2471,0,1,NULL,1454),(1454,1931,2479,0,1,NULL,1455),(1455,1923,2487,0,1,NULL,1456),(1456,1919,2497,0,1,NULL,1457),(1457,1918,2508,1,1,NULL,1458),(1458,1940,2529,0,1,NULL,1459),(1459,1949,2531,0,1,NULL,1460),(1460,1973,2524,0,1,NULL,1461),(1461,1992,2516,0,1,NULL,1462),(1462,2009,2517,0,1,NULL,1463),(1463,2023,2539,0,1,NULL,1464),(1464,2028,2544,0,1,NULL,1465),(1465,2036,2547,0,1,NULL,1466),(1466,2048,2554,0,1,NULL,1467),(1467,2059,2566,0,1,NULL,1468),(1468,2073,2581,0,1,NULL,1469),(1469,2083,2599,0,1,NULL,1470),(1470,2096,2630,0,1,NULL,1471),(1471,2100,2640,0,1,NULL,1472),(1472,2112,2656,0,1,NULL,1473),(1473,2127,2692,0,1,NULL,1474),(1474,2132,2707,1,1,NULL,1475),(1475,2130,2734,0,1,NULL,1476),(1476,2124,2747,0,1,NULL,1477),(1477,2113,2753,0,1,NULL,1478),(1478,2099,2756,0,1,NULL,1479),(1479,2092,2755,0,1,NULL,1480),(1480,2089,2751,0,1,NULL,1481),(1481,2087,2742,1,1,NULL,1482),(1482,2091,2712,1,1,NULL,1483),(1483,2076,2675,0,1,NULL,1484),(1484,2067,2662,0,1,NULL,1485),(1485,2049,2645,0,1,NULL,1486),(1486,2042,2635,0,1,NULL,1487),(1487,2038,2621,0,1,NULL,1488),(1488,2033,2604,0,1,NULL,1489),(1489,2020,2587,0,1,NULL,1490),(1490,2002,2584,0,1,NULL,1491),(1491,1988,2586,0,1,NULL,1492),(1492,1977,2590,0,1,NULL,1493),(1493,1973,2603,0,1,NULL,1494),(1494,1972,2627,1,1,NULL,1495),(1495,1973,2636,0,1,NULL,1496),(1496,1982,2655,0,1,NULL,1497),(1497,1993,2670,0,1,NULL,1498),(1498,1994,2687,1,1,NULL,1499),(1499,1989,2698,0,1,NULL,1500),(1500,1981,2706,0,1,NULL,1501),(1501,1959,2708,0,1,NULL,1502),(1502,1948,2704,0,1,NULL,1503),(1503,1933,2696,0,1,NULL,1504),(1504,1915,2685,0,1,NULL,1505),(1505,1902,2683,0,1,NULL,1506),(1506,1894,2684,0,1,NULL,1507),(1507,1869,2695,0,1,NULL,1508),(1508,1841,2702,0,1,NULL,1509),(1509,1825,2699,0,1,NULL,1510),(1510,1824,2703,1,1,NULL,1511),(1511,1839,2711,0,1,NULL,1512),(1512,1843,2721,0,1,NULL,1513),(1513,1845,2731,0,1,NULL,1514),(1514,1847,2738,0,1,NULL,1515),(1515,1898,2753,0,1,NULL,1516),(1516,1921,2762,0,1,NULL,1517),(1517,1935,2771,0,1,NULL,1518),(1518,1950,2777,0,1,NULL,1519),(1519,1959,2785,0,1,NULL,1520),(1520,1963,2816,1,1,NULL,1521),(1521,1961,2851,0,1,NULL,1522),(1522,1942,2881,0,1,NULL,1523),(1523,1936,2894,0,1,NULL,1524),(1524,1917,2907,0,1,NULL,1525),(1525,1904,2907,0,1,NULL,1526),(1526,1890,2900,0,1,NULL,1527),(1527,1874,2888,0,1,NULL,1528),(1528,1866,2878,0,1,NULL,1529),(1529,1853,2870,1,1,NULL,1530),(1530,1854,2853,0,1,NULL,1531),(1531,1867,2823,0,1,NULL,1532),(1532,1868,2809,1,1,NULL,1533),(1533,1862,2802,0,1,NULL,1534),(1534,1844,2793,0,1,NULL,1535),(1535,1828,2791,0,1,NULL,1536),(1536,1823,2793,0,1,NULL,1537),(1537,1771,2779,0,1,NULL,1538),(1538,1753,2778,0,1,NULL,1539),(1539,1746,2782,0,1,NULL,1540),(1540,1734,2780,0,1,NULL,1541),(1541,1726,2777,0,1,NULL,1542),(1542,1713,2765,0,1,NULL,1543),(1543,1697,2738,0,1,NULL,1544),(1544,1685,2702,0,1,NULL,1545),(1545,1684,2693,1,1,NULL,1546),(1546,1703,2673,0,1,NULL,1547),(1547,1715,2666,0,1,NULL,1548),(1548,1742,2638,0,1,NULL,1549),(1549,1803,2592,0,1,NULL,1550),(1550,1820,2593,0,1,NULL,1551),(1551,1829,2596,0,1,NULL,1552),(1552,1847,2596,0,1,NULL,1553),(1553,1861,2589,0,1,NULL,1554),(1554,1862,2558,1,1,NULL,1555),(1555,1859,2541,0,1,NULL,1556),(1556,1843,2511,0,1,NULL,1557),(1557,1815,2507,0,1,NULL,1558),(1558,1804,2499,0,1,NULL,1559),(1559,1795,2489,0,1,NULL,1560),(1560,1787,2471,1,1,NULL,1561),(1561,1789,2462,0,1,NULL,1443),(1562,1419,2589,0,1,NULL,1563),(1563,1428,2591,0,1,NULL,1564),(1564,1435,2595,0,1,NULL,1565),(1565,1446,2619,0,1,NULL,1566),(1566,1467,2644,0,1,NULL,1567),(1567,1492,2655,0,1,NULL,1568),(1568,1501,2687,0,1,NULL,1569),(1569,1517,2719,1,1,NULL,1570),(1570,1510,2770,0,1,NULL,1571),(1571,1504,2774,0,1,NULL,1572),(1572,1488,2773,0,1,NULL,1573),(1573,1477,2757,0,1,NULL,1574),(1574,1467,2757,0,1,NULL,1575),(1575,1450,2755,0,1,NULL,1576),(1576,1446,2771,0,1,NULL,1577),(1577,1437,2773,0,1,NULL,1578),(1578,1431,2769,0,1,NULL,1579),(1579,1413,2729,1,1,NULL,1580),(1580,1415,2685,0,1,NULL,1581),(1581,1423,2659,1,1,NULL,1582),(1582,1422,2648,0,1,NULL,1583),(1583,1413,2632,0,1,NULL,1584),(1584,1409,2617,0,1,NULL,1585),(1585,1408,2597,1,1,NULL,1562),(1586,1490,2359,0,1,NULL,1587),(1587,1498,2358,0,1,NULL,1588),(1588,1503,2351,0,1,NULL,1589),(1589,1517,2342,0,1,NULL,1590),(1590,1541,2358,0,1,NULL,1591),(1591,1542,2366,1,1,NULL,1592),(1592,1540,2376,1,1,NULL,1593),(1593,1541,2392,0,1,NULL,1594),(1594,1544,2418,1,1,NULL,1595),(1595,1540,2442,0,1,NULL,1596),(1596,1529,2453,0,1,NULL,1597),(1597,1500,2473,0,1,NULL,1598),(1598,1498,2496,0,1,NULL,1599),(1599,1486,2507,0,1,NULL,1600),(1600,1452,2527,0,1,NULL,1601),(1601,1445,2540,1,1,NULL,1602),(1602,1446,2558,1,1,NULL,1603),(1603,1438,2564,0,1,NULL,1604),(1604,1422,2558,0,1,NULL,1605),(1605,1418,2549,0,1,NULL,1606),(1606,1417,2516,1,1,NULL,1607),(1607,1420,2507,0,1,NULL,1608),(1608,1431,2492,0,1,NULL,1609),(1609,1448,2487,0,1,NULL,1610),(1610,1465,2479,0,1,NULL,1611),(1611,1470,2468,0,1,NULL,1612),(1612,1471,2458,1,1,NULL,1613),(1613,1467,2448,0,1,NULL,1614),(1614,1466,2436,0,1,NULL,1615),(1615,1450,2431,0,1,NULL,1616),(1616,1440,2413,1,1,NULL,1617),(1617,1443,2395,0,1,NULL,1618),(1618,1454,2385,0,1,NULL,1619),(1619,1462,2371,0,1,NULL,1620),(1620,1471,2358,0,1,NULL,1621),(1621,1480,2353,0,1,NULL,1622),(1622,1485,2354,0,1,NULL,1586),(1623,1590,2098,0,1,NULL,1624),(1624,1611,2092,0,1,NULL,1625),(1625,1630,2091,0,1,NULL,1626),(1626,1639,2099,0,1,NULL,1627),(1627,1646,2100,0,1,NULL,1628),(1628,1660,2098,0,1,NULL,1629),(1629,1670,2109,1,1,NULL,1630),(1630,1667,2132,0,1,NULL,1631),(1631,1666,2156,0,1,NULL,1632),(1632,1654,2169,0,1,NULL,1633),(1633,1645,2168,0,1,NULL,1634),(1634,1636,2173,0,1,NULL,1635),(1635,1617,2195,0,1,NULL,1636),(1636,1612,2202,1,1,NULL,1637),(1637,1613,2242,1,1,NULL,1638),(1638,1606,2260,0,1,NULL,1639),(1639,1602,2282,0,1,NULL,1640),(1640,1595,2285,0,1,NULL,1641),(1641,1581,2281,0,1,NULL,1642),(1642,1577,2277,0,1,NULL,1643),(1643,1571,2279,0,1,NULL,1644),(1644,1565,2287,0,1,NULL,1645),(1645,1546,2279,1,1,NULL,1646),(1646,1550,2267,1,1,NULL,1647),(1647,1538,2261,0,1,NULL,1648),(1648,1537,2244,1,1,NULL,1649),(1649,1549,2218,1,1,NULL,1650),(1650,1538,2211,0,1,NULL,1651),(1651,1534,2202,1,1,NULL,1652),(1652,1537,2182,0,1,NULL,1653),(1653,1557,2127,0,1,NULL,1654),(1654,1570,2128,0,1,NULL,1655),(1655,1578,2124,0,1,NULL,1623),(1656,1757,2173,1,1,NULL,1657),(1657,1794,2163,0,1,NULL,1658),(1658,1805,2172,0,1,NULL,1659),(1659,1821,2172,0,1,NULL,1660),(1660,1829,2176,0,1,NULL,1661),(1661,1834,2181,0,1,NULL,1662),(1662,1844,2184,0,1,NULL,1663),(1663,1853,2180,0,1,NULL,1664),(1664,1864,2196,0,1,NULL,1665),(1665,1874,2207,0,1,NULL,1666),(1666,1875,2223,1,1,NULL,1667),(1667,1865,2251,0,1,NULL,1668),(1668,1856,2254,0,1,NULL,1669),(1669,1834,2248,0,1,NULL,1670),(1670,1819,2239,0,1,NULL,1671),(1671,1804,2237,0,1,NULL,1672),(1672,1788,2232,0,1,NULL,1673),(1673,1775,2232,0,1,NULL,1674),(1674,1770,2236,0,1,NULL,1675),(1675,1750,2268,0,1,NULL,1676),(1676,1745,2286,1,1,NULL,1677),(1677,1746,2302,0,1,NULL,1678),(1678,1751,2314,1,1,NULL,1679),(1679,1750,2329,0,1,NULL,1680),(1680,1747,2350,0,1,NULL,1681),(1681,1741,2355,0,1,NULL,1682),(1682,1727,2396,0,1,NULL,1683),(1683,1715,2404,0,1,NULL,1684),(1684,1704,2413,0,1,NULL,1685),(1685,1698,2435,1,1,NULL,1686),(1686,1702,2448,0,1,NULL,1687),(1687,1711,2463,1,1,NULL,1688),(1688,1710,2475,0,1,NULL,1689),(1689,1694,2508,0,1,NULL,1690),(1690,1666,2532,0,1,NULL,1691),(1691,1649,2541,0,1,NULL,1692),(1692,1625,2569,0,1,NULL,1693),(1693,1624,2579,1,1,NULL,1694),(1694,1632,2602,0,1,NULL,1695),(1695,1633,2636,0,1,NULL,1696),(1696,1648,2687,0,1,NULL,1697),(1697,1655,2694,0,1,NULL,1698),(1698,1661,2708,1,1,NULL,1699),(1699,1656,2715,0,1,NULL,1700),(1700,1611,2721,0,1,NULL,1701),(1701,1606,2737,0,1,NULL,1702),(1702,1597,2749,1,1,NULL,1703),(1703,1603,2765,0,1,NULL,1704),(1704,1604,2788,1,1,NULL,1705),(1705,1597,2792,0,1,NULL,1706),(1706,1575,2784,0,1,NULL,1707),(1707,1566,2772,0,1,NULL,1708),(1708,1564,2761,1,1,NULL,1709),(1709,1568,2744,0,1,NULL,1710),(1710,1569,2711,0,1,NULL,1711),(1711,1574,2689,1,1,NULL,1712),(1712,1573,2669,0,1,NULL,1713),(1713,1571,2657,0,1,NULL,1714),(1714,1560,2642,0,1,NULL,1715),(1715,1544,2630,0,1,NULL,1716),(1716,1513,2604,0,1,NULL,1717),(1717,1511,2598,0,1,NULL,1718),(1718,1510,2588,1,1,NULL,1719),(1719,1520,2576,0,1,NULL,1720),(1720,1532,2548,0,1,NULL,1721),(1721,1545,2523,0,1,NULL,1722),(1722,1561,2515,0,1,NULL,1723),(1723,1608,2513,0,1,NULL,1724),(1724,1628,2501,0,1,NULL,1725),(1725,1632,2472,0,1,NULL,1726),(1726,1637,2460,1,1,NULL,1727),(1727,1636,2452,0,1,NULL,1728),(1728,1625,2423,0,1,NULL,1729),(1729,1622,2402,1,1,NULL,1730),(1730,1631,2386,0,1,NULL,1731),(1731,1639,2381,0,1,NULL,1732),(1732,1648,2372,0,1,NULL,1733),(1733,1652,2361,0,1,NULL,1734),(1734,1661,2355,0,1,NULL,1735),(1735,1672,2351,0,1,NULL,1736),(1736,1682,2353,0,1,NULL,1737),(1737,1687,2355,0,1,NULL,1738),(1738,1696,2363,0,1,NULL,1739),(1739,1704,2366,0,1,NULL,1740),(1740,1715,2360,0,1,NULL,1741),(1741,1723,2356,0,1,NULL,1742),(1742,1725,2340,1,1,NULL,1743),(1743,1718,2322,0,1,NULL,1744),(1744,1707,2281,1,1,NULL,1745),(1745,1709,2260,0,1,NULL,1746),(1746,1720,2250,0,1,NULL,1747),(1747,1733,2208,0,1,NULL,1748),(1748,1742,2202,0,1,NULL,1749),(1749,1751,2190,0,1,NULL,1750),(1750,1757,2173,0,1,NULL,1656),(1751,1672,2392,0,2,NULL,1752),(1752,1681,2394,1,2,NULL,1753),(1753,1679,2410,0,2,NULL,1754),(1754,1660,2424,0,2,NULL,1755),(1755,1654,2417,1,2,NULL,1756),(1756,1662,2409,0,2,NULL,1757),(1757,1664,2401,0,2,NULL,1751),(1758,1570,2553,0,2,NULL,1759),(1759,1579,2549,0,2,NULL,1760),(1760,1585,2549,0,2,NULL,1761),(1761,1598,2561,1,2,NULL,1762),(1762,1592,2593,1,2,NULL,1763),(1763,1595,2634,0,2,NULL,1764),(1764,1598,2646,0,2,NULL,1765),(1765,1610,2669,0,2,NULL,1766),(1766,1612,2677,1,2,NULL,1767),(1767,1599,2679,0,2,NULL,1768),(1768,1595,2653,0,2,NULL,1769),(1769,1587,2631,0,2,NULL,1770),(1770,1577,2614,0,2,NULL,1771),(1771,1570,2602,1,2,NULL,1772),(1772,1571,2587,1,2,NULL,1773),(1773,1568,2573,0,2,NULL,1774),(1774,1567,2560,1,2,NULL,1758),(1775,1291,2012,0,1,NULL,1776),(1776,1294,2016,0,1,NULL,1777),(1777,1301,2043,0,1,NULL,1778),(1778,1319,2073,0,1,NULL,1779),(1779,1324,2088,1,1,NULL,1780),(1780,1323,2107,0,1,NULL,1781),(1781,1319,2124,0,1,NULL,1782),(1782,1311,2136,0,1,NULL,1783),(1783,1297,2151,0,1,NULL,1784),(1784,1286,2168,0,1,NULL,1785),(1785,1278,2193,0,1,NULL,1786),(1786,1263,2216,0,1,NULL,1787),(1787,1226,2254,0,1,NULL,1788),(1788,1223,2292,1,1,NULL,1789),(1789,1244,2320,0,1,NULL,1790),(1790,1252,2323,0,1,NULL,1791),(1791,1260,2322,0,1,NULL,1792),(1792,1277,2312,0,1,NULL,1793),(1793,1294,2308,0,1,NULL,1794),(1794,1315,2295,0,1,NULL,1795),(1795,1319,2286,0,1,NULL,1796),(1796,1341,2242,0,1,NULL,1797),(1797,1355,2225,0,1,NULL,1798),(1798,1367,2206,0,1,NULL,1799),(1799,1383,2191,0,1,NULL,1800),(1800,1395,2171,0,1,NULL,1801),(1801,1418,2112,0,1,NULL,1802),(1802,1432,2104,0,1,NULL,1803),(1803,1456,2100,0,1,NULL,1804),(1804,1468,2099,0,1,NULL,1805),(1805,1475,2102,0,1,NULL,1806),(1806,1486,2116,1,1,NULL,1807),(1807,1485,2168,0,1,NULL,1808),(1808,1479,2205,1,1,NULL,1809),(1809,1481,2247,0,1,NULL,1810),(1810,1494,2268,1,1,NULL,1811),(1811,1493,2277,0,1,NULL,1812),(1812,1478,2295,0,1,NULL,1813),(1813,1460,2298,0,1,NULL,1814),(1814,1441,2299,0,1,NULL,1815),(1815,1433,2300,0,1,NULL,1816),(1816,1417,2310,0,1,NULL,1817),(1817,1405,2321,0,1,NULL,1818),(1818,1398,2340,1,1,NULL,1819),(1819,1400,2345,0,1,NULL,1820),(1820,1405,2346,0,1,NULL,1821),(1821,1406,2360,0,1,NULL,1822),(1822,1414,2373,1,1,NULL,1823),(1823,1410,2384,0,1,NULL,1824),(1824,1393,2394,0,1,NULL,1825),(1825,1370,2394,0,1,NULL,1826),(1826,1364,2397,0,1,NULL,1827),(1827,1344,2396,0,1,NULL,1828),(1828,1335,2394,0,1,NULL,1829),(1829,1321,2379,0,1,NULL,1830),(1830,1304,2372,0,1,NULL,1831),(1831,1285,2373,0,1,NULL,1832),(1832,1275,2408,0,1,NULL,1833),(1833,1265,2432,0,1,NULL,1834),(1834,1248,2439,0,1,NULL,1835),(1835,1242,2436,0,1,NULL,1836),(1836,1230,2435,0,1,NULL,1837),(1837,1223,2432,0,1,NULL,1838),(1838,1199,2399,0,1,NULL,1839),(1839,1189,2381,0,1,NULL,1840),(1840,1184,2377,0,1,NULL,1841),(1841,1174,2377,0,1,NULL,1842),(1842,1149,2375,0,1,NULL,1843),(1843,1128,2382,0,1,NULL,1844),(1844,1108,2390,0,1,NULL,1845),(1845,1094,2398,0,1,NULL,1846),(1846,1086,2411,0,1,NULL,1847),(1847,1085,2425,1,1,NULL,1848),(1848,1093,2440,0,1,NULL,1849),(1849,1097,2461,0,1,NULL,1850),(1850,1108,2485,0,1,NULL,1851),(1851,1116,2497,0,1,NULL,1852),(1852,1138,2519,0,1,NULL,1853),(1853,1145,2539,1,1,NULL,1854),(1854,1142,2564,1,1,NULL,1855),(1855,1145,2592,0,1,NULL,1856),(1856,1150,2599,0,1,NULL,1857),(1857,1195,2624,0,1,NULL,1858),(1858,1208,2669,1,1,NULL,1859),(1859,1207,2684,1,1,NULL,1860),(1860,1215,2705,0,1,NULL,1861),(1861,1228,2716,0,1,NULL,1862),(1862,1234,2716,0,1,NULL,1863),(1863,1241,2709,0,1,NULL,1864),(1864,1255,2686,0,1,NULL,1865),(1865,1259,2672,1,1,NULL,1866),(1866,1244,2649,0,1,NULL,1867),(1867,1241,2628,1,1,NULL,1868),(1868,1245,2614,0,1,NULL,1869),(1869,1255,2594,0,1,NULL,1870),(1870,1261,2577,1,1,NULL,1871),(1871,1256,2574,1,1,NULL,1872),(1872,1257,2569,1,1,NULL,1873),(1873,1256,2561,0,1,NULL,1874),(1874,1254,2552,1,1,NULL,1875),(1875,1257,2544,0,1,NULL,1876),(1876,1262,2535,1,1,NULL,1877),(1877,1256,2533,0,1,NULL,1878),(1878,1224,2534,0,1,NULL,1879),(1879,1196,2530,0,1,NULL,1880),(1880,1191,2525,0,1,NULL,1881),(1881,1181,2511,1,1,NULL,1882),(1882,1182,2498,0,1,NULL,1883),(1883,1186,2489,0,1,NULL,1884),(1884,1200,2484,0,1,NULL,1885),(1885,1209,2484,0,1,NULL,1886),(1886,1224,2488,0,1,NULL,1887),(1887,1246,2486,0,1,NULL,1888),(1888,1254,2468,0,1,NULL,1889),(1889,1261,2461,0,1,NULL,1890),(1890,1272,2456,0,1,NULL,1891),(1891,1275,2452,0,1,NULL,1892),(1892,1281,2450,0,1,NULL,1893),(1893,1310,2450,0,1,NULL,1894),(1894,1321,2448,0,1,NULL,1895),(1895,1343,2428,0,1,NULL,1896),(1896,1385,2431,0,1,NULL,1897),(1897,1396,2450,1,1,NULL,1898),(1898,1395,2474,0,1,NULL,1899),(1899,1387,2489,0,1,NULL,1900),(1900,1377,2499,0,1,NULL,1901),(1901,1370,2503,0,1,NULL,1902),(1902,1360,2510,0,1,NULL,1903),(1903,1359,2517,0,1,NULL,1904),(1904,1358,2532,0,1,NULL,1905),(1905,1351,2546,0,1,NULL,1906),(1906,1342,2551,0,1,NULL,1907),(1907,1334,2563,0,1,NULL,1908),(1908,1329,2584,0,1,NULL,1909),(1909,1325,2587,0,1,NULL,1910),(1910,1320,2584,0,1,NULL,1911),(1911,1314,2599,1,1,NULL,1912),(1912,1315,2636,0,1,NULL,1913),(1913,1320,2651,0,1,NULL,1914),(1914,1348,2688,0,1,NULL,1915),(1915,1357,2712,1,1,NULL,1916),(1916,1356,2733,0,1,NULL,1917),(1917,1351,2754,0,1,NULL,1918),(1918,1340,2769,0,1,NULL,1919),(1919,1322,2784,0,1,NULL,1920),(1920,1319,2790,1,1,NULL,1921),(1921,1324,2796,0,1,NULL,1922),(1922,1360,2808,0,1,NULL,1923),(1923,1375,2813,0,1,NULL,1924),(1924,1396,2817,0,1,NULL,1925),(1925,1408,2823,0,1,NULL,1926),(1926,1427,2827,0,1,NULL,1927),(1927,1440,2827,0,1,NULL,1928),(1928,1447,2823,0,1,NULL,1929),(1929,1465,2822,0,1,NULL,1930),(1930,1512,2835,0,1,NULL,1931),(1931,1515,2843,0,1,NULL,1932),(1932,1516,2870,1,1,NULL,1933),(1933,1511,2879,0,1,NULL,1934),(1934,1492,2888,0,1,NULL,1935),(1935,1469,2901,0,1,NULL,1936),(1936,1452,2902,0,1,NULL,1937),(1937,1439,2903,0,1,NULL,1938),(1938,1409,2894,0,1,NULL,1939),(1939,1397,2887,0,1,NULL,1940),(1940,1389,2888,0,1,NULL,1941),(1941,1366,2876,0,1,NULL,1942),(1942,1338,2860,0,1,NULL,1943),(1943,1312,2854,0,1,NULL,1944),(1944,1291,2850,0,1,NULL,1945),(1945,1253,2850,0,1,NULL,1946),(1946,1230,2858,0,1,NULL,1947),(1947,1203,2864,0,1,NULL,1948),(1948,1183,2878,0,1,NULL,1949),(1949,1168,2875,0,1,NULL,1950),(1950,1154,2870,0,1,NULL,1951),(1951,1141,2867,0,1,NULL,1952),(1952,1125,2857,0,1,NULL,1953),(1953,1116,2849,0,1,NULL,1954),(1954,1107,2834,1,1,NULL,1955),(1955,1108,2808,0,1,NULL,1956),(1956,1112,2778,0,1,NULL,1957),(1957,1121,2747,1,1,NULL,1958),(1958,1118,2728,0,1,NULL,1959),(1959,1110,2709,0,1,NULL,1960),(1960,1093,2685,0,1,NULL,1961),(1961,1070,2663,1,1,NULL,1962),(1962,1071,2652,0,1,NULL,1963),(1963,1080,2638,1,1,NULL,1964),(1964,1077,2609,0,1,NULL,1965),(1965,1069,2595,0,1,NULL,1966),(1966,1034,2560,1,1,NULL,1967),(1967,1035,2525,1,1,NULL,1968),(1968,1034,2484,0,1,NULL,1969),(1969,1029,2470,0,1,NULL,1970),(1970,1005,2466,0,1,NULL,1971),(1971,988,2457,0,1,NULL,1972),(1972,971,2458,0,1,NULL,1973),(1973,960,2464,0,1,NULL,1974),(1974,932,2463,0,1,NULL,1975),(1975,913,2464,0,1,NULL,1976),(1976,900,2468,0,1,NULL,1977),(1977,893,2489,1,1,NULL,1978),(1978,894,2509,1,1,NULL,1979),(1979,882,2522,0,1,NULL,1980),(1980,865,2540,0,1,NULL,1981),(1981,842,2540,0,1,NULL,1982),(1982,819,2534,0,1,NULL,1983),(1983,802,2535,0,1,NULL,1984),(1984,795,2530,0,1,NULL,1985),(1985,780,2503,1,1,NULL,1986),(1986,781,2488,0,1,NULL,1987),(1987,784,2479,0,1,NULL,1988),(1988,798,2465,0,1,NULL,1989),(1989,834,2436,0,1,NULL,1990),(1990,847,2416,0,1,NULL,1991),(1991,848,2403,1,1,NULL,1992),(1992,836,2381,1,1,NULL,1993),(1993,838,2360,0,1,NULL,1994),(1994,848,2346,0,1,NULL,1995),(1995,861,2339,0,1,NULL,1996),(1996,899,2340,0,1,NULL,1997),(1997,910,2347,0,1,NULL,1998),(1998,917,2359,0,1,NULL,1999),(1999,921,2368,0,1,NULL,2000),(2000,936,2370,0,1,NULL,2001),(2001,940,2367,0,1,NULL,2002),(2002,966,2368,0,1,NULL,2003),(2003,983,2373,0,1,NULL,2004),(2004,996,2373,0,1,NULL,2005),(2005,1018,2367,0,1,NULL,2006),(2006,1019,2363,0,1,NULL,2007),(2007,1025,2361,0,1,NULL,2008),(2008,1060,2326,0,1,NULL,2009),(2009,1088,2301,0,1,NULL,2010),(2010,1115,2287,0,1,NULL,2011),(2011,1125,2278,0,1,NULL,2012),(2012,1143,2253,0,1,NULL,2013),(2013,1156,2240,0,1,NULL,2014),(2014,1157,2235,0,1,NULL,2015),(2015,1175,2214,0,1,NULL,2016),(2016,1185,2208,0,1,NULL,2017),(2017,1189,2198,0,1,NULL,2018),(2018,1192,2164,0,1,NULL,2019),(2019,1213,2097,0,1,NULL,2020),(2020,1223,2088,0,1,NULL,2021),(2021,1238,2082,1,1,NULL,2022),(2022,1237,2053,1,1,NULL,1023),(1023,1256,2026,0,1,NULL,1775),(2023,5496,3162,1,1,NULL,1287),(2024,5040,3082,0,1,NULL,1297),(2025,379,3923,0,1,NULL,419),(2026,1015,884,0,1,NULL,180),(2027,881,1089,0,1,NULL,197),(2049,1602,3745,0,1,NULL,2028),(2048,1571,3793,0,1,NULL,2049),(2047,1540,3803,0,1,NULL,2048),(2046,1514,3828,0,1,NULL,2047),(2045,1508,3861,1,1,NULL,2046),(2044,1513,3886,0,1,NULL,2045),(2043,1537,3915,1,1,NULL,2044),(2042,1523,3931,1,1,NULL,2043),(2041,1526,3944,0,1,NULL,2042),(2040,1580,3958,0,1,NULL,2041),(2039,1612,3959,0,1,NULL,2040),(2038,1623,3948,0,1,NULL,2039),(2037,1685,3943,0,1,NULL,2038),(2036,1713,3924,0,1,NULL,2037),(2035,1719,3905,0,1,NULL,2036),(2034,1736,3875,0,1,NULL,2035),(2033,1778,3816,0,1,NULL,2034),(2032,1800,3801,1,1,NULL,2033),(2031,1798,3784,0,1,NULL,2032),(2030,1765,3752,0,1,NULL,2031),(2029,1753,3746,0,1,NULL,2030),(2028,1637,3733,0,1,NULL,2029),(2050,1434,3587,0,1,NULL,2051),(2051,1470,3590,0,1,NULL,2052),(2052,1549,3588,0,1,NULL,2053),(2053,1721,3587,0,1,NULL,2054),(2054,1787,3575,0,1,NULL,2055),(2055,1797,3567,0,1,NULL,2056),(2056,1818,3563,0,1,NULL,2057),(2057,1833,3552,0,1,NULL,2058),(2058,1853,3545,0,1,NULL,2059),(2059,1865,3546,0,1,NULL,2060),(2060,1872,3540,0,1,NULL,2061),(2061,2028,3539,0,1,NULL,2062),(2062,2074,3519,0,1,NULL,2063),(2063,2087,3508,0,1,NULL,2064),(2064,2096,3498,0,1,NULL,2065),(2065,2101,3502,0,1,NULL,2066),(2066,2107,3523,0,1,NULL,2067),(2067,2132,3564,0,1,NULL,2068),(2068,2135,3690,1,1,NULL,2069),(2069,2087,3821,0,1,NULL,2070),(2070,2011,4082,0,1,NULL,2071),(2071,2010,4242,1,1,NULL,2072),(2072,2041,4306,0,1,NULL,2073),(2073,2078,4342,0,1,NULL,2074),(2074,2167,4378,0,1,NULL,2075),(2075,2301,4395,0,1,NULL,2076),(2076,2351,4394,0,1,NULL,2077),(2077,2372,4383,0,1,NULL,2078),(2078,2400,4340,0,1,NULL,2079),(2079,2401,4190,1,1,NULL,2080),(2080,2395,4176,0,1,NULL,2081),(2081,2393,4147,0,1,NULL,2082),(2082,2389,4133,0,1,NULL,2083),(2083,2388,4071,1,1,NULL,2084),(2084,2413,4020,0,1,NULL,2085),(2085,2414,4008,0,1,NULL,2086),(2086,2425,3986,0,1,NULL,2087),(2087,2426,3960,0,1,NULL,2088),(2088,2430,3949,0,1,NULL,2089),(2089,2431,3911,0,1,NULL,2090),(2090,2436,3903,0,1,NULL,2091),(2091,2437,3879,0,1,NULL,2092),(2092,2443,3870,0,1,NULL,2093),(2093,2444,3848,0,1,NULL,2094),(2094,2455,3837,1,1,NULL,2095),(2095,2454,3788,1,1,NULL,2096),(2096,2463,3771,0,1,NULL,2097),(2097,2468,3740,0,1,NULL,2098),(2098,2501,3643,0,1,NULL,2099),(2099,2508,3609,1,1,NULL,2100),(2100,2507,3548,0,1,NULL,2101),(2101,2439,3466,0,1,NULL,2102),(2102,2382,3439,0,1,NULL,2103),(2103,2334,3438,0,1,NULL,2104),(2104,2234,3414,0,1,NULL,2105),(2105,2151,3408,0,1,NULL,2106),(2106,2145,3402,0,1,NULL,2107),(2107,2049,3403,0,1,NULL,2108),(2108,2027,3410,0,1,NULL,2109),(2109,2019,3425,0,1,NULL,2110),(2110,1946,3451,0,1,NULL,2111),(2111,1741,3450,0,1,NULL,2112),(2112,1721,3438,0,1,NULL,2113),(2113,1672,3439,0,1,NULL,2114),(2114,1661,3432,0,1,NULL,2115),(2115,1626,3431,0,1,NULL,2116),(2116,1565,3414,0,1,NULL,2117),(2117,1492,3415,0,1,NULL,2118),(2118,1467,3426,0,1,NULL,2119),(2119,1450,3427,0,1,NULL,2120),(2120,1430,3438,0,1,NULL,2121),(2121,1407,3439,0,1,NULL,2122),(2122,1366,3451,0,1,NULL,2123),(2123,1307,3478,0,1,NULL,2124),(2124,1300,3493,0,1,NULL,2125),(2125,1150,3565,0,1,NULL,2126),(2126,1122,3586,0,1,NULL,2127),(2127,1095,3619,0,1,NULL,2128),(2128,1058,3717,1,1,NULL,2129),(2129,1060,3736,1,1,NULL,2130),(2130,1046,3758,0,1,NULL,2131),(2131,1023,3932,0,1,NULL,2132),(2132,1021,4023,1,1,NULL,2133),(2133,1028,4041,1,1,NULL,2134),(2134,1027,4068,1,1,NULL,2135),(2135,1057,4154,0,1,NULL,2136),(2136,1133,4267,0,1,NULL,2137),(2137,1196,4443,1,1,NULL,2138),(2138,1195,4461,1,1,NULL,2139),(2139,1207,4488,0,1,NULL,2140),(2140,1208,4601,1,1,NULL,2141),(2141,1201,4613,1,1,NULL,2142),(2142,1202,4622,1,1,NULL,2143),(2143,1172,4661,0,1,NULL,2144),(2144,1170,4865,1,1,NULL,2145),(2145,1213,4989,0,1,NULL,2146),(2146,1263,5039,0,1,NULL,2147),(2147,1342,5069,0,1,NULL,2148),(2148,1438,5088,0,1,NULL,2149),(2149,1464,5089,0,1,NULL,2150),(2150,1651,5123,0,1,NULL,2151),(2151,1765,5164,0,1,NULL,2152),(2152,1780,5176,0,1,NULL,2153),(2153,1921,5234,0,1,NULL,2154),(2154,1936,5263,0,1,NULL,2155),(2155,1979,5314,0,1,NULL,2156),(2156,2000,5320,0,1,NULL,2157),(2157,2033,5352,0,1,NULL,2158),(2158,2112,5392,0,1,NULL,2159),(2159,2146,5397,0,1,NULL,2160),(2160,2211,5398,0,1,NULL,2161),(2161,2226,5391,0,1,NULL,2162),(2162,2253,5393,0,1,NULL,2163),(2163,2273,5385,0,1,NULL,2164),(2164,2413,5386,0,1,NULL,2165),(2165,2439,5397,0,1,NULL,2166),(2166,2453,5398,0,1,NULL,2167),(2167,2486,5416,0,1,NULL,2168),(2168,2500,5417,0,1,NULL,2169),(2169,2530,5429,0,1,NULL,2170),(2170,2539,5438,0,1,NULL,2171),(2171,2567,5475,0,1,NULL,2172),(2172,2585,5483,0,1,NULL,2173),(2173,2668,5541,0,1,NULL,2174),(2174,2818,5590,0,1,NULL,2175),(2175,2892,5591,0,1,NULL,2176),(2176,2975,5620,0,1,NULL,2177),(2177,3126,5657,0,1,NULL,2178),(2178,3265,5658,0,1,NULL,2179),(2179,3280,5669,0,1,NULL,2180),(2180,3298,5668,0,1,NULL,2181),(2181,3366,5690,0,1,NULL,2182),(2182,3447,5709,0,1,NULL,2183),(2183,3459,5717,0,1,NULL,2184),(2184,3615,5718,0,1,NULL,2185),(2185,3629,5710,0,1,NULL,2186),(2186,3652,5711,0,1,NULL,2187),(2187,3663,5703,0,1,NULL,2188),(2188,3715,5704,0,1,NULL,2189),(2189,3740,5698,0,1,NULL,2190),(2190,3814,5697,0,1,NULL,2191),(2191,3910,5668,0,1,NULL,2192),(2192,3927,5667,0,1,NULL,2193),(2193,3942,5655,0,1,NULL,2194),(2194,3961,5583,0,1,NULL,2195),(2195,3983,5536,0,1,NULL,2196),(2196,3990,5516,0,1,NULL,2197),(2197,3992,5453,1,1,NULL,2198),(2198,3939,5364,1,1,NULL,2199),(2199,3948,5361,1,1,NULL,2200),(2200,3918,5333,0,1,NULL,2201),(2201,3912,5312,1,1,NULL,2202),(2202,3913,5295,1,1,NULL,2203),(2203,3881,5232,1,1,NULL,2204),(2204,3887,5222,0,1,NULL,2205),(2205,3888,5145,0,1,NULL,2206),(2206,3895,5133,1,1,NULL,2207),(2207,3894,5112,1,1,NULL,2208),(2208,3921,5040,0,1,NULL,2209),(2209,3940,5028,0,1,NULL,2210),(2210,3947,5013,0,1,NULL,2211),(2211,4038,4924,0,1,NULL,2212),(2212,4055,4877,0,1,NULL,2213),(2213,4056,4813,1,1,NULL,2214),(2214,4050,4797,0,1,NULL,2215),(2215,4049,4773,0,1,NULL,2216),(2216,4034,4729,0,1,NULL,2217),(2217,4010,4690,0,1,NULL,2218),(2218,3983,4663,0,1,NULL,2219),(2219,3932,4632,0,1,NULL,2220),(2220,3831,4598,0,1,NULL,2221),(2221,3777,4596,0,1,NULL,2222),(2222,3617,4551,0,1,NULL,2223),(2223,3550,4494,0,1,NULL,2224),(2224,3512,4414,0,1,NULL,2225),(2225,3511,4394,0,1,NULL,2226),(2226,3493,4341,0,1,NULL,2227),(2227,3492,4292,1,1,NULL,2228),(2228,3501,4264,0,1,NULL,2229),(2229,3532,4227,0,1,NULL,2230),(2230,3583,4198,0,1,NULL,2231),(2231,3616,4197,0,1,NULL,2232),(2232,3659,4187,0,1,NULL,2233),(2233,3914,4188,0,1,NULL,2234),(2234,3960,4230,0,1,NULL,2235),(2235,4047,4454,0,1,NULL,2236),(2236,4069,4505,0,1,NULL,2237),(2237,4099,4540,0,1,NULL,2238),(2238,4137,4541,0,1,NULL,2239),(2239,4169,4515,0,1,NULL,2240),(2240,4193,4498,0,1,NULL,2241),(2241,4212,4425,1,1,NULL,2242),(2242,4211,4369,1,1,NULL,2243),(2243,4217,4347,0,1,NULL,2244),(2244,4218,4270,1,1,NULL,2245),(2245,4206,4197,0,1,NULL,2246),(2246,4193,4154,0,1,NULL,2247),(2247,4175,4118,0,1,NULL,2248),(2248,4169,4086,0,1,NULL,2249),(2249,4131,3963,0,1,NULL,2250),(2250,4125,3926,0,1,NULL,2251),(2251,4098,3848,1,1,NULL,2252),(2252,4099,3744,0,1,NULL,2253),(2253,4145,3625,0,1,NULL,2254),(2254,4146,3603,0,1,NULL,2255),(2255,4158,3568,1,1,NULL,2256),(2256,4157,3533,1,1,NULL,2257),(2257,4165,3525,1,1,NULL,2258),(2258,4164,3422,1,1,NULL,2259),(2259,4195,3372,0,1,NULL,2260),(2260,4196,3350,0,1,NULL,2261),(2261,4212,3338,0,1,NULL,2262),(2262,4229,3306,0,1,NULL,2263),(2263,4230,3252,1,1,NULL,2264),(2264,4219,3237,0,1,NULL,2265),(2265,4192,3222,0,1,NULL,2266),(2266,4146,3221,0,1,NULL,2267),(2267,4104,3208,0,1,NULL,2268),(2268,4033,3258,0,1,NULL,2269),(2269,4013,3268,0,1,NULL,2270),(2270,4010,3278,0,1,NULL,2271),(2271,3988,3294,0,1,NULL,2272),(2272,3974,3314,0,1,NULL,2273),(2273,3938,3333,0,1,NULL,2274),(2274,3910,3338,0,1,NULL,2275),(2275,3797,3401,0,1,NULL,2276),(2276,3749,3456,0,1,NULL,2277),(2277,3701,3508,0,1,NULL,2278),(2278,3636,3547,0,1,NULL,2279),(2279,3556,3642,0,1,NULL,2280),(2280,3494,3690,0,1,NULL,2281),(2281,3440,3771,0,1,NULL,2282),(2282,3425,3800,0,1,NULL,2283),(2283,3382,3840,0,1,NULL,2284),(2284,3357,3874,0,1,NULL,2285),(2285,3327,3934,0,1,NULL,2286),(2286,3309,3976,0,1,NULL,2287),(2287,3279,4068,0,1,NULL,2288),(2288,3225,4136,0,1,NULL,2289),(2289,3221,4150,0,1,NULL,2290),(2290,3167,4189,0,1,NULL,2291),(2291,2991,4194,0,1,NULL,2292),(2292,2922,4153,0,1,NULL,2293),(2293,2861,4095,0,1,NULL,2294),(2294,2795,4055,0,1,NULL,2295),(2295,2770,4019,0,1,NULL,2296),(2296,2759,3982,1,1,NULL,2297),(2297,2760,3933,0,1,NULL,2298),(2298,2772,3906,0,1,NULL,2299),(2299,2773,3851,1,1,NULL,2300),(2300,2753,3804,0,1,NULL,2301),(2301,2742,3741,0,1,NULL,2302),(2302,2710,3693,0,1,NULL,2303),(2303,2659,3680,0,1,NULL,2304),(2304,2568,3677,0,1,NULL,2305),(2305,2540,3692,0,1,NULL,2306),(2306,2518,3708,0,1,NULL,2307),(2307,2499,3783,0,1,NULL,2308),(2308,2498,3943,1,1,NULL,2309),(2309,2535,4007,0,1,NULL,2310),(2310,2547,4012,0,1,NULL,2311),(2311,2574,4053,0,1,NULL,2312),(2312,2605,4068,0,1,NULL,2313),(2313,2677,4138,0,1,NULL,2314),(2314,2755,4218,0,1,NULL,2315),(2315,2773,4242,0,1,NULL,2316),(2316,2780,4342,1,1,NULL,2317),(2317,2719,4487,0,1,NULL,2318),(2318,2642,4574,0,1,NULL,2319),(2319,2589,4605,0,1,NULL,2320),(2320,2562,4627,0,1,NULL,2321),(2321,2483,4644,0,1,NULL,2322),(2322,2407,4645,0,1,NULL,2323),(2323,2310,4613,0,1,NULL,2324),(2324,2295,4615,0,1,NULL,2325),(2325,2095,4536,0,1,NULL,2326),(2326,2039,4507,0,1,NULL,2327),(2327,1923,4463,0,1,NULL,2328),(2328,1862,4434,0,1,NULL,2329),(2329,1824,4417,0,1,NULL,2330),(2330,1796,4397,0,1,NULL,2331),(2331,1742,4363,0,1,NULL,2332),(2332,1700,4343,0,1,NULL,2333),(2333,1658,4332,0,1,NULL,2334),(2334,1597,4331,0,1,NULL,2335),(2335,1560,4323,0,1,NULL,2336),(2336,1527,4305,0,1,NULL,2337),(2337,1496,4301,0,1,NULL,2338),(2338,1427,4267,0,1,NULL,2339),(2339,1350,4147,0,1,NULL,2340),(2340,1319,4079,1,1,NULL,2341),(2341,1320,4058,1,1,NULL,2342),(2342,1302,4006,1,1,NULL,2343),(2343,1303,3949,1,1,NULL,2344),(2344,1290,3885,0,1,NULL,2345),(2345,1289,3851,0,1,NULL,2346),(2346,1285,3840,1,1,NULL,2347),(2347,1286,3812,0,1,NULL,2348),(2348,1319,3730,0,1,NULL,2349),(2349,1364,3681,0,1,NULL,2350),(2350,1395,3628,0,1,NULL,2351),(2351,1416,3601,0,1,NULL,2050),(2352,2036,5211,0,2,NULL,2353),(2353,2044,5214,0,2,NULL,2354),(2354,2058,5250,1,2,NULL,2355),(2355,2049,5274,0,2,NULL,2356),(2356,2037,5276,0,2,NULL,2357),(2357,2020,5256,1,2,NULL,2358),(2358,2024,5238,0,2,NULL,2352),(2359,2161,4972,0,2,NULL,2360),(2360,2202,4989,0,2,NULL,2361),(2361,2209,5012,1,2,NULL,2362),(2362,2200,5057,1,2,NULL,2363),(2363,2201,5092,1,2,NULL,2364),(2364,2195,5134,0,2,NULL,2365),(2365,2179,5155,0,2,NULL,2366),(2366,2175,5167,0,2,NULL,2367),(2367,2146,5148,0,2,NULL,2368),(2368,2137,5125,1,2,NULL,2369),(2369,2140,5098,0,2,NULL,2370),(2370,2141,5035,1,2,NULL,2371),(2371,2132,5026,0,2,NULL,2372),(2372,2126,5031,0,2,NULL,2373),(2373,2106,5060,0,2,NULL,2374),(2374,2083,5075,0,2,NULL,2375),(2375,2071,5112,0,2,NULL,2376),(2376,2017,5092,1,2,NULL,2377),(2377,2037,5059,1,2,NULL,2378),(2378,2027,5040,1,2,NULL,2379),(2379,2043,5037,0,2,NULL,2380),(2380,2057,5012,0,2,NULL,2381),(2381,2113,4979,0,2,NULL,2382),(2382,2133,4979,0,2,NULL,2359),(2383,3428,5183,0,2,NULL,2384),(2384,3496,5183,0,2,NULL,2385),(2385,3573,5212,0,2,NULL,2386),(2386,3647,5213,0,2,NULL,2387),(2387,3672,5227,0,2,NULL,2388),(2388,3697,5257,0,2,NULL,2389),(2389,3704,5291,0,2,NULL,2390),(2390,3755,5357,0,2,NULL,2391),(2391,3757,5407,1,2,NULL,2392),(2392,3746,5413,0,2,NULL,2393),(2393,3738,5436,0,2,NULL,2394),(2394,3640,5430,0,2,NULL,2395),(2395,3623,5418,0,2,NULL,2396),(2396,3526,5418,0,2,NULL,2397),(2397,3429,5381,0,2,NULL,2398),(2398,3377,5347,0,2,NULL,2399),(2399,3349,5321,0,2,NULL,2400),(2400,3277,5280,0,2,NULL,2401),(2401,3236,5266,0,2,NULL,2402),(2402,3211,5227,0,2,NULL,2403),(2403,3210,5177,1,2,NULL,2404),(2404,3227,5180,0,2,NULL,2405),(2405,3304,5201,0,2,NULL,2406),(2406,3368,5202,0,2,NULL,2407),(2407,3397,5189,0,2,NULL,2383),(2408,3857,5274,0,2,NULL,2409),(2409,3866,5279,0,2,NULL,2410),(2410,3880,5320,1,2,NULL,2411),(2411,3876,5333,0,2,NULL,2412),(2412,3854,5311,0,2,NULL,2413),(2413,3852,5277,1,2,NULL,2408),(2414,3967,4749,0,2,NULL,2415),(2415,3976,4744,0,2,NULL,2416),(2416,3977,4752,1,2,NULL,2417),(2417,3959,4757,0,2,NULL,2418),(2418,3954,4750,1,2,NULL,2414),(2419,3936,4689,0,2,NULL,2420),(2420,3942,4696,1,2,NULL,2421),(2421,3932,4709,0,2,NULL,2422),(2422,3931,4721,0,2,NULL,2423),(2423,3927,4720,0,2,NULL,2424),(2424,3926,4704,1,2,NULL,2419),(2425,3776,4621,0,2,NULL,2426),(2426,3806,4644,0,2,NULL,2427),(2427,3809,4676,0,2,NULL,2428),(2428,3818,4686,1,2,NULL,2429),(2429,3814,4692,0,2,NULL,2430),(2430,3803,4690,0,2,NULL,2431),(2431,3763,4690,0,2,NULL,2432),(2432,3737,4662,0,2,NULL,2433),(2433,3731,4640,1,2,NULL,2434),(2434,3743,4615,0,2,NULL,2435),(2435,3752,4614,0,2,NULL,2425),(2436,3778,4640,0,2,NULL,2437),(2437,3788,4651,0,2,NULL,2438),(2438,3791,4676,1,2,NULL,2439),(2439,3775,4676,0,2,NULL,2440),(2440,3768,4644,1,2,NULL,2436),(2441,2197,4049,0,2,NULL,2442),(2442,2221,4051,0,2,NULL,2443),(2443,2233,4058,1,2,NULL,2444),(2444,2232,4137,0,2,NULL,2445),(2445,2226,4142,1,2,NULL,2446),(2446,2231,4147,0,2,NULL,2447),(2447,2232,4163,0,2,NULL,2448),(2448,2246,4172,1,2,NULL,2449),(2449,2244,4189,1,2,NULL,2450),(2450,2248,4216,1,2,NULL,2451),(2451,2205,4264,0,2,NULL,2452),(2452,2185,4264,0,2,NULL,2453),(2453,2167,4224,0,2,NULL,2454),(2454,2166,4206,0,2,NULL,2455),(2455,2154,4174,1,2,NULL,2456),(2456,2155,4102,0,2,NULL,2457),(2457,2164,4093,0,2,NULL,2458),(2458,2169,4075,0,2,NULL,2459),(2459,2180,4071,0,2,NULL,2441),(2460,2936,4710,0,2,NULL,2461),(2461,2950,4723,0,2,NULL,2462),(2462,2972,4737,1,2,NULL,2463),(2463,2964,4772,0,2,NULL,2464),(2464,2956,4807,0,2,NULL,2465),(2465,2938,4810,0,2,NULL,2466),(2466,2902,4809,0,2,NULL,2467),(2467,2893,4799,0,2,NULL,2468),(2468,2889,4768,1,2,NULL,2469),(2469,2895,4735,0,2,NULL,2470),(2470,2915,4732,0,2,NULL,2460),(2471,2218,4802,0,2,NULL,2472),(2472,2291,4785,0,2,NULL,2473),(2473,2367,4755,0,2,NULL,2474),(2474,2407,4732,0,2,NULL,2475),(2475,2434,4726,0,2,NULL,2476),(2476,2481,4727,0,2,NULL,2477),(2477,2495,4720,0,2,NULL,2478),(2478,2510,4721,0,2,NULL,2479),(2479,2570,4690,0,2,NULL,2480),(2480,2592,4683,0,2,NULL,2481),(2481,2615,4659,0,2,NULL,2482),(2482,2644,4641,0,2,NULL,2483),(2483,2699,4644,0,2,NULL,2484),(2484,2714,4649,1,2,NULL,2485),(2485,2712,4667,0,2,NULL,2486),(2486,2695,4700,0,2,NULL,2487),(2487,2650,4734,0,2,NULL,2488),(2488,2619,4740,0,2,NULL,2489),(2489,2602,4752,0,2,NULL,2490),(2490,2529,4765,0,2,NULL,2491),(2491,2508,4776,0,2,NULL,2492),(2492,2458,4783,0,2,NULL,2493),(2493,2413,4802,0,2,NULL,2494),(2494,2358,4814,1,2,NULL,2495),(2495,2359,4838,0,2,NULL,2496),(2496,2373,4880,0,2,NULL,2497),(2497,2518,4976,0,2,NULL,2498),(2498,2609,4999,0,2,NULL,2499),(2499,2639,5018,0,2,NULL,2500),(2500,2642,5053,1,2,NULL,2501),(2501,2627,5071,0,2,NULL,2502),(2502,2576,5076,0,2,NULL,2503),(2503,2530,5028,0,2,NULL,2504),(2504,2464,5011,0,2,NULL,2505),(2505,2454,5017,0,2,NULL,2506),(2506,2221,4937,0,2,NULL,2507),(2507,2039,4883,0,2,NULL,2508),(2508,1830,4828,0,2,NULL,2509),(2509,1771,4799,0,2,NULL,2510),(2510,1711,4789,0,2,NULL,2511),(2511,1568,4780,0,2,NULL,2512),(2512,1496,4751,0,2,NULL,2513),(2513,1477,4729,0,2,NULL,2514),(2514,1461,4717,1,2,NULL,2515),(2515,1462,4697,0,2,NULL,2516),(2516,1510,4679,0,2,NULL,2517),(2517,1535,4678,0,2,NULL,2518),(2518,1589,4660,0,2,NULL,2519),(2519,1653,4662,0,2,NULL,2520),(2520,1671,4666,0,2,NULL,2521),(2521,1717,4666,0,2,NULL,2522),(2522,1769,4683,0,2,NULL,2523),(2523,1852,4731,0,2,NULL,2524),(2524,2035,4786,0,2,NULL,2525),(2525,2068,4787,0,2,NULL,2526),(2526,2127,4804,0,2,NULL,2471),(2606,3804,3646,0,2,NULL,2527),(2527,3844,3670,0,2,NULL,2528),(2528,3851,3690,0,2,NULL,2529),(2529,3853,3733,1,2,NULL,2530),(2530,3846,3742,0,2,NULL,2531),(2531,3843,3796,0,2,NULL,2532),(2532,3830,3840,0,2,NULL,2533),(2533,3823,3881,0,2,NULL,2534),(2534,3813,3890,0,2,NULL,2535),(2535,3797,3918,0,2,NULL,2536),(2536,3784,3918,0,2,NULL,2537),(2537,3768,3931,0,2,NULL,2538),(2538,3756,3970,0,2,NULL,2539),(2539,3740,3990,0,2,NULL,2540),(2540,3726,4021,1,2,NULL,2541),(2541,3741,4028,0,2,NULL,2542),(2542,3876,3983,0,2,NULL,2543),(2543,3935,3918,0,2,NULL,2544),(2544,3951,3884,1,2,NULL,2545),(2545,3946,3844,0,2,NULL,2546),(2546,3936,3802,0,2,NULL,2547),(2547,3935,3763,0,2,NULL,2548),(2548,3932,3740,1,2,NULL,2549),(2549,3936,3707,0,2,NULL,2550),(2550,3954,3677,1,2,NULL,2551),(2551,3949,3671,1,2,NULL,2552),(2552,3954,3663,0,2,NULL,2553),(2553,3959,3618,1,2,NULL,2554),(2554,3956,3591,1,2,NULL,2555),(2555,3971,3578,0,2,NULL,2556),(2556,3976,3567,0,2,NULL,2557),(2557,3996,3553,0,2,NULL,2558),(2558,4005,3560,0,2,NULL,2559),(2559,4008,3596,0,2,NULL,2560),(2560,4022,3638,0,2,NULL,2561),(2561,4028,3682,0,2,NULL,2562),(2562,4042,3724,0,2,NULL,2563),(2563,4063,3774,1,2,NULL,2564),(2564,4062,3800,0,2,NULL,2565),(2565,4045,3822,0,2,NULL,2566),(2566,4010,3878,1,2,NULL,2567),(2567,4014,3899,0,2,NULL,2568),(2568,4016,3936,1,2,NULL,2569),(2569,4004,3965,0,2,NULL,2570),(2570,3990,3975,0,2,NULL,2571),(2571,3963,3982,0,2,NULL,2572),(2572,3938,3996,0,2,NULL,2573),(2573,3937,4006,1,2,NULL,2574),(2574,3958,4026,0,2,NULL,2575),(2575,4000,4045,0,2,NULL,2576),(2576,4014,4066,0,2,NULL,2577),(2577,4016,4094,1,2,NULL,2578),(2578,4005,4101,0,2,NULL,2579),(2579,3989,4098,0,2,NULL,2580),(2580,3963,4067,0,2,NULL,2581),(2581,3944,4049,0,2,NULL,2582),(2582,3920,4017,0,2,NULL,2583),(2583,3904,4008,0,2,NULL,2584),(2584,3881,4013,0,2,NULL,2585),(2585,3810,4040,0,2,NULL,2586),(2586,3786,4043,0,2,NULL,2587),(2587,3769,4050,0,2,NULL,2588),(2588,3735,4078,0,2,NULL,2589),(2589,3696,4086,0,2,NULL,2590),(2590,3663,4079,0,2,NULL,2591),(2591,3653,4069,1,2,NULL,2592),(2592,3654,4061,0,2,NULL,2593),(2593,3698,4034,0,2,NULL,2594),(2594,3716,4003,0,2,NULL,2595),(2595,3720,3978,0,2,NULL,2596),(2596,3724,3965,0,2,NULL,2597),(2597,3725,3947,1,2,NULL,2598),(2598,3679,3948,0,2,NULL,2599),(2599,3674,3943,0,2,NULL,2600),(2600,3645,3942,1,2,NULL,2601),(2601,3646,3931,1,2,NULL,2602),(2602,3594,3923,0,2,NULL,2603),(2603,3569,3899,1,2,NULL,2604),(2604,3579,3875,0,2,NULL,2605),(2605,3752,3682,0,2,NULL,2606),(2607,4004,3700,0,2,NULL,2608),(2608,4018,3714,1,2,NULL,2609),(2609,4017,3750,1,2,NULL,2610),(2610,4035,3782,1,2,NULL,2611),(2611,4034,3803,0,2,NULL,2612),(2612,4019,3808,0,2,NULL,2613),(2613,3989,3835,0,2,NULL,2614),(2614,3975,3823,1,2,NULL,2615),(2615,3976,3798,1,2,NULL,2616),(2616,3969,3754,1,2,NULL,2617),(2617,3974,3720,1,2,NULL,2618),(2618,3970,3707,1,2,NULL,2619),(2619,3986,3668,0,2,NULL,2620),(2620,4000,3683,0,2,NULL,2607),(2621,3287,4686,0,2,NULL,2622),(2622,3336,4722,0,2,NULL,2623),(2623,3337,4759,1,2,NULL,2624),(2624,3303,4801,0,2,NULL,2625),(2625,3271,4791,0,2,NULL,2626),(2626,3256,4774,0,2,NULL,2627),(2627,3247,4775,0,2,NULL,2628),(2628,3237,4763,0,2,NULL,2629),(2629,3234,4707,1,2,NULL,2630),(2630,3246,4699,0,2,NULL,2621),(2631,3072,4464,0,2,NULL,2632),(2632,3117,4470,0,2,NULL,2633),(2633,3131,4492,0,2,NULL,2634),(2634,3152,4517,0,2,NULL,2635),(2635,3166,4588,1,2,NULL,2636),(2636,3151,4624,0,2,NULL,2637),(2637,3113,4651,0,2,NULL,2638),(2638,3081,4681,0,2,NULL,2639),(2639,3065,4698,0,2,NULL,2640),(2640,3043,4687,0,2,NULL,2641),(2641,3032,4673,0,2,NULL,2642),(2642,3008,4672,0,2,NULL,2643),(2643,2958,4673,0,2,NULL,2644),(2644,2939,4656,0,2,NULL,2645),(2645,2932,4640,0,2,NULL,2646),(2646,2920,4632,0,2,NULL,2647),(2647,2895,4630,1,2,NULL,2648),(2648,2899,4607,1,2,NULL,2649),(2649,2893,4560,1,2,NULL,2650),(2650,2903,4541,0,2,NULL,2651),(2651,2925,4520,0,2,NULL,2652),(2652,2966,4509,0,2,NULL,2653),(2653,3016,4483,0,2,NULL,2654),(2654,3031,4473,0,2,NULL,2631),(2655,3040,4581,0,2,NULL,2656),(2656,3064,4575,0,2,NULL,2657),(2657,3076,4545,0,2,NULL,2658),(2658,3089,4541,0,2,NULL,2659),(2659,3090,4573,0,2,NULL,2660),(2660,3099,4601,1,2,NULL,2661),(2661,3098,4621,0,2,NULL,2662),(2662,3063,4652,0,2,NULL,2663),(2663,3039,4652,0,2,NULL,2664),(2664,2986,4630,1,2,NULL,2665),(2665,2988,4610,0,2,NULL,2666),(2666,3007,4593,0,2,NULL,2655),(2667,1574,3985,0,1,NULL,2668),(2668,1600,3997,0,1,NULL,2669),(2669,1609,4012,0,1,NULL,2670),(2670,1614,4021,0,1,NULL,2671),(2671,1622,4025,0,1,NULL,2672),(2672,1629,4033,1,1,NULL,2673),(2673,1628,4048,0,1,NULL,2674),(2674,1609,4088,1,1,NULL,2675),(2675,1612,4110,0,1,NULL,2676),(2676,1631,4126,0,1,NULL,2677),(2677,1641,4140,0,1,NULL,2678),(2678,1643,4156,1,1,NULL,2679),(2679,1641,4164,0,1,NULL,2680),(2680,1634,4184,0,1,NULL,2681),(2681,1626,4192,0,1,NULL,2682),(2682,1612,4192,0,1,NULL,2683),(2683,1607,4180,1,1,NULL,2684),(2684,1607,4174,0,1,NULL,2685),(2685,1601,4165,0,1,NULL,2686),(2686,1580,4155,0,1,NULL,2687),(2687,1568,4154,0,1,NULL,2688),(2688,1554,4159,0,1,NULL,2689),(2689,1542,4174,0,1,NULL,2690),(2690,1514,4171,0,1,NULL,2691),(2691,1479,4134,1,1,NULL,2692),(2692,1482,4126,0,1,NULL,2693),(2693,1494,4107,0,1,NULL,2694),(2694,1504,4077,1,1,NULL,2695),(2695,1504,4061,1,1,NULL,2696),(2696,1512,4041,0,1,NULL,2697),(2697,1524,4030,0,1,NULL,2698),(2698,1539,4019,0,1,NULL,2699),(2699,1554,4012,1,1,NULL,2700),(2700,1554,4000,1,1,NULL,2701),(2701,1560,3988,0,1,NULL,2667),(2702,1773,4107,0,1,NULL,2703),(2703,1798,4080,1,1,NULL,2704),(2704,1792,4063,1,1,NULL,2705),(2705,1792,4041,1,1,NULL,2706),(2706,1798,4020,0,1,NULL,2707),(2707,1806,4002,0,1,NULL,2708),(2708,1818,3988,0,1,NULL,2709),(2709,1836,3984,0,1,NULL,2710),(2710,1850,3987,0,1,NULL,2711),(2711,1860,4002,0,1,NULL,2712),(2712,1864,4018,1,1,NULL,2713),(2713,1860,4030,0,1,NULL,2714),(2714,1848,4045,1,1,NULL,2715),(2715,1853,4071,1,1,NULL,2716),(2716,1850,4095,0,1,NULL,2717),(2717,1835,4109,1,1,NULL,2718),(2718,1835,4114,1,1,NULL,2719),(2719,1840,4131,0,1,NULL,2720),(2720,1847,4147,1,1,NULL,2721),(2721,1842,4165,0,1,NULL,2722),(2722,1824,4174,0,1,NULL,2723),(2723,1805,4178,0,1,NULL,2724),(2724,1783,4192,0,1,NULL,2725),(2725,1767,4183,0,1,NULL,2726),(2726,1743,4180,0,1,NULL,2727),(2727,1712,4171,0,1,NULL,2728),(2728,1689,4157,0,1,NULL,2729),(2729,1678,4141,0,1,NULL,2730),(2730,1665,4124,0,1,NULL,2731),(2731,1658,4112,0,1,NULL,2732),(2732,1645,4096,1,1,NULL,2733),(2733,1645,4081,1,1,NULL,2734),(2734,1656,4057,0,1,NULL,2735),(2735,1676,4048,0,1,NULL,2736),(2736,1699,4039,0,1,NULL,2737),(2737,1724,4037,0,1,NULL,2738),(2738,1726,4031,1,1,NULL,2739),(2739,1718,4019,1,1,NULL,2740),(2740,1719,4011,0,1,NULL,2741),(2741,1725,4004,0,1,NULL,2742),(2742,1726,3991,1,1,NULL,2743),(2743,1723,3985,1,1,NULL,2744),(2744,1725,3965,0,1,NULL,2745),(2745,1731,3956,0,1,NULL,2746),(2746,1744,3956,0,1,NULL,2747),(2747,1756,3960,0,1,NULL,2748),(2748,1768,3974,0,1,NULL,2749),(2749,1774,3977,0,1,NULL,2750),(2750,1785,3993,1,1,NULL,2751),(2751,1783,4007,0,1,NULL,2752),(2752,1778,4017,0,1,NULL,2753),(2753,1777,4034,0,1,NULL,2754),(2754,1776,4054,0,1,NULL,2755),(2755,1746,4099,1,1,NULL,2756),(2756,1746,4112,1,1,NULL,2757),(2757,1766,4113,0,1,NULL,2702);
/*!40000 ALTER TABLE `corners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cornersnw`
--

DROP TABLE IF EXISTS `cornersnw`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cornersnw` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `minx` int(11) NOT NULL DEFAULT '0',
  `miny` int(11) NOT NULL DEFAULT '0',
  `maxx` int(11) NOT NULL DEFAULT '0',
  `maxy` int(11) NOT NULL DEFAULT '0',
  `points` text CHARACTER SET latin1 NOT NULL,
  KEY `typeid` (`typeid`),
  KEY `miny` (`miny`),
  KEY `maxx` (`maxx`),
  KEY `maxy` (`maxy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cornersnw`
--

LOCK TABLES `cornersnw` WRITE;
/*!40000 ALTER TABLE `cornersnw` DISABLE KEYS */;
/*!40000 ALTER TABLE `cornersnw` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `councils`
--

DROP TABLE IF EXISTS `councils`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `councils` (
  `id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` tinytext CHARACTER SET latin1,
  `email` tinytext CHARACTER SET latin1,
  `description` text CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `councils`
--

LOCK TABLES `councils` WRITE;
/*!40000 ALTER TABLE `councils` DISABLE KEYS */;
INSERT INTO `councils` VALUES (1,'Game Administration Board','gab','dept_gab_description'),(5,'Resources Department','resources','dept_resources_description'),(6,'Players Department','players','dept_players_description'),(8,'Programming Department','programming','dept_programming_description'),(9,'Languages Department','languages','This department is responsible for all translations between English and other languages, which includes translating the website, correspondence, help pages, etc. The department also cooperates with the Advertisement Department to help with advertisement on non-English sites by finding the sites and translating the texts from the Advertisement Department.'),(10,'Administrative Support Department','personnel','dept_support_description'),(11,'Public Relations Department','PR','dept_marketing_description');
/*!40000 ALTER TABLE `councils` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `count_sessions`
--

DROP TABLE IF EXISTS `count_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `count_sessions` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `number` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `count_sessions`
--

LOCK TABLES `count_sessions` WRITE;
/*!40000 ALTER TABLE `count_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `count_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `counters`
--

DROP TABLE IF EXISTS `counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counters` (
  `page` text CHARACTER SET latin1 NOT NULL,
  `player` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `turn` smallint(5) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counters`
--

LOCK TABLES `counters` WRITE;
/*!40000 ALTER TABLE `counters` DISABLE KEYS */;
/*!40000 ALTER TABLE `counters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credits_alterations`
--

DROP TABLE IF EXISTS `credits_alterations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credits_alterations` (
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_id` mediumint(8) unsigned NOT NULL,
  `player_id` mediumint(8) unsigned NOT NULL,
  `from` mediumint(8) unsigned NOT NULL,
  `to` mediumint(8) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credits_alterations`
--

LOCK TABLES `credits_alterations` WRITE;
/*!40000 ALTER TABLE `credits_alterations` DISABLE KEYS */;
/*!40000 ALTER TABLE `credits_alterations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credits_log`
--

DROP TABLE IF EXISTS `credits_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credits_log` (
  `day` mediumint(8) unsigned DEFAULT NULL,
  `player` mediumint(8) unsigned DEFAULT NULL,
  `credits` mediumint(8) unsigned DEFAULT NULL,
  `minutes` mediumint(8) unsigned DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credits_log`
--

LOCK TABLES `credits_log` WRITE;
/*!40000 ALTER TABLE `credits_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `credits_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `css_skins`
--

DROP TABLE IF EXISTS `css_skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `css_skins` (
  `player` int(10) unsigned NOT NULL COMMENT 'id from table `players`',
  `is_custom` tinyint(3) unsigned NOT NULL,
  `base_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `custom_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `css_skins`
--

LOCK TABLES `css_skins` WRITE;
/*!40000 ALTER TABLE `css_skins` DISABLE KEYS */;
/*!40000 ALTER TABLE `css_skins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dbinfo`
--

DROP TABLE IF EXISTS `dbinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `field` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  `info` text CHARACTER SET latin1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_version`
--

CREATE TABLE IF NOT EXISTS `db_version` (
  `number` int(10) unsigned NOT NULL COMMENT 'version',
  `date` datetime NOT NULL COMMENT 'when did last migration happen',
  PRIMARY KEY (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `db_version`
--

INSERT INTO `db_version` (`number`, `date`) VALUES
(1, '2019-01-04 23:19:51');

--
-- Dumping data for table `dbinfo`
--

LOCK TABLES `dbinfo` WRITE;
/*!40000 ALTER TABLE `dbinfo` DISABLE KEYS */;
INSERT INTO `dbinfo` VALUES (1,'access_types','','Records the various access privilege types'),(2,'assignments','special','Description of special positions other than regular members, e.g. liaison positions'),(3,'categories','','Lists the various categories for the building menu, including some that are not visible to players'),(4,'categories','name','Unique identifier of category'),(5,'dbinfo','','Contains these yellow descriptions'),(6,'animal_interaction','victim_type','1 = character, 2 = animal'),(8,'animal_foodchain','','Records which animal types can potentially attack which other animal types'),(9,'animal_foodchain','prey','Key to animal_types; 0 = vegetation (hunter is herbivore or omnivore)'),(10,'animal_foodchain','hunter','Key to animal_types'),(11,'dbinfo','field','Field name of table; empty means table as a whole'),(12,'chars','newbie','1 = newbie island; 0 = normal'),(13,'chars','status','0 = pending; 1 = active; 2 = deceased; 3 = being buried; 4 = buried'),(14,'animal_interaction','perpetrator_type','1 = character, 2 = animal'),(15,'changes','','Keeps track of changes in some RD tables to post to Wiki'),(16,'chars','project','Key to projects.id; 0 = not working; -1 = working on activity'),(17,'chars','register','Turn number of spawning date'),(18,'animal_interaction','interaction_type','1 = attack, 2 = poke, 3 = pet, 4 = feed'),(19,'locations','local_number','Contains the number shown on the buildings list'),(20,'locations','type','1 - outside, 2 - building, 3 - land vehicle or docked boat, 4 - (unused), 5 - sailing vessel'),(21,'locations','area','Links to areas if type=1, otherwise to objecttypes'),(22,'kills','kills','0 - no kill, 1 - kill, can also be more than 1 with animals');
/*!40000 ALTER TABLE `dbinfo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `descriptions`
--

DROP TABLE IF EXISTS `descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `descriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL COMMENT 'type stored in class.Descriptions.php',
  `for_id` int(11) NOT NULL COMMENT 'id of linked data, different for different types',
  `content` text CHARACTER SET utf8 NOT NULL,
  `sub_id` int(11) DEFAULT NULL COMMENT 'if needed',
  `author` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `desc_for` (`type`,`for_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `descriptions`
--

LOCK TABLES `descriptions` WRITE;
/*!40000 ALTER TABLE `descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `diseases`
--

DROP TABLE IF EXISTS `diseases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diseases` (
  `person` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `disease` smallint(5) unsigned NOT NULL DEFAULT '0',
  `infector` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `date` smallint(5) unsigned NOT NULL DEFAULT '0',
  `specifics` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  KEY `person` (`person`),
  KEY `disease` (`disease`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `diseases`
--

LOCK TABLES `diseases` WRITE;
/*!40000 ALTER TABLE `diseases` DISABLE KEYS */;
INSERT INTO `diseases` VALUES (1,1,0,1,'');
/*!40000 ALTER TABLE `diseases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dow_rates`
--

DROP TABLE IF EXISTS `dow_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dow_rates` (
  `rawtype` int(11) NOT NULL,
  `rate` int(11) NOT NULL,
  `rawname` varchar(64) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`rawtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dow_rates`
--

LOCK TABLES `dow_rates` WRITE;
/*!40000 ALTER TABLE `dow_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `dow_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draggers`
--

DROP TABLE IF EXISTS `draggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draggers` (
  `dragging_id` int(11) NOT NULL DEFAULT '0',
  `dragger` mediumint(9) NOT NULL DEFAULT '0',
  KEY `dragging_id` (`dragging_id`),
  KEY `dragger` (`dragger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draggers`
--

LOCK TABLES `draggers` WRITE;
/*!40000 ALTER TABLE `draggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `draggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dragging`
--

DROP TABLE IF EXISTS `dragging`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dragging` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `victimtype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `victim` int(8) unsigned NOT NULL DEFAULT '0',
  `goal` mediumint(8) DEFAULT '0',
  `weight` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `victim` (`victim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dragging`
--

LOCK TABLES `dragging` WRITE;
/*!40000 ALTER TABLE `dragging` DISABLE KEYS */;
/*!40000 ALTER TABLE `dragging` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) DEFAULT NULL,
  `day` smallint(6) NOT NULL DEFAULT '0',
  `hour` tinyint(4) NOT NULL DEFAULT '0',
  `minute` tinyint(3) unsigned DEFAULT '0',
  `parameters` text CHARACTER SET latin1,
  PRIMARY KEY (`id`),
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_groups`
--

DROP TABLE IF EXISTS `events_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_groups` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT 'Event Group',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='groups for event\r\ntypes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_groups`
--

LOCK TABLES `events_groups` WRITE;
/*!40000 ALTER TABLE `events_groups` DISABLE KEYS */;
INSERT INTO `events_groups` VALUES (1,'Attacks (including animals)'),(2,'Attacks (including animals) on me'),(3,'People/vehicles entering'),(4,'People/vehicles leaving'),(5,'Spawning'),(6,'Project setup/completion'),(7,'Dragging on me'),(8,'Dragging'),(9,'Radio messages'),(10,'Eating'),(11,'Picking locks'),(12,'Passing notes (to me)'),(13,'Passing notes'),(14,'Passing objects (to me)'),(15,'Passing objects'),(16,'Passing resouces (to me)'),(17,'Passing resouces'),(18,'Sailing and docking'),(19,'Putting things into containers'),(20,'Taking things out of containers'),(21,'Talking'),(22,'Whispers'),(23,'Whispers to me'),(24,'Others'),(25,'Dragging characters'),(26,'Your whispers'),(27,'Domestication'),(28,'Pointing'),(29,'Healing near death'),(30,'Hunting'),(31,'Object take/drop'),(32,'Weather'),(33,'Destruction'),(34,'Land travel');
/*!40000 ALTER TABLE `events_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_obs`
--

DROP TABLE IF EXISTS `events_obs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_obs` (
  `observer` mediumint(9) NOT NULL DEFAULT '0',
  `event` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`observer`,`event`),
  KEY `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_obs`
--

LOCK TABLES `events_obs` WRITE;
/*!40000 ALTER TABLE `events_obs` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_obs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_types`
--

DROP TABLE IF EXISTS `events_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_types` (
  `type` smallint(6) NOT NULL DEFAULT '0',
  `description` varchar(250) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `group` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_types`
--

LOCK TABLES `events_types` WRITE;
/*!40000 ALTER TABLE `events_types` DISABLE KEYS */;
INSERT INTO `events_types` VALUES (0,'',34),(1,'Talk to specific person: observed by others',22),(2,'Talk to specific person: observed by listener',23),(3,'Talk to specific person: observed by speaker',26),(4,'Talk to all: observed by people',21),(5,'Talk to all: observed by speaker',21),(6,'Point to person: observed by others',28),(7,'Point to person: observed by victim',28),(8,'Point to person: observed by actor',28),(9,'Adjust sailing - obsolete',24),(10,'Eating (Strengthening Foods)',10),(11,'Attract attention: Somebody seen',24),(12,'Attract attention: Somebody heard',24),(13,'Attract attention: Actor',24),(14,'Knock on door: Somebody seen',24),(15,'Knock on door: Somebody heard',24),(16,'Knock on door: Actor',24),(17,'Pick lock: Somebody seen',11),(18,'Pick lock: Somebody heard',11),(19,'Pick lock: Actor',11),(20,'Turn around: Actor',24),(21,'Turn around: Watcher',24),(22,'Travelling alone: Actor',34),(23,'Travelling alone: Watcher',34),(24,'Travelling in vehicle: Actor',34),(25,'Travelling in vehicle: Watcher',34),(26,'Undocking',18),(27,'Retrieve: Watcher',20),(28,'Retrieve: Actor',20),(29,'Kill animal: Watcher',30),(30,'Kill animal: Actor',30),(31,'Poke animal: Watcher',30),(32,'Poke animal: Actor',30),(33,'Hurt animal: Watcher',30),(34,'Hurt animal: Actor',30),(35,'Animal kills: Watcher',1),(36,'Animal kills: Victim',2),(37,'Animal attacks: Watcher',1),(38,'Animal attacks: Victim',2),(39,'Animal attacks: Victim with protection',2),(40,'Use object: Watcher',6),(41,'Use object: Actor',6),(42,'Store object: Watcher',19),(43,'Store object: Actor',19),(44,'Project finished',6),(45,'Project finished: result on ground',6),(46,'Project finished: result in inventory',6),(47,'A man dies',1),(48,'A woman dies',1),(49,'Killing: Actor',1),(50,'Killing: Victim',2),(51,'Killing: Watcher',1),(52,'Slapping: Actor',1),(53,'Slapping: Victim',2),(54,'Slapping: Watcher',1),(55,'Miss: Actor',1),(56,'Miss: Victim',2),(57,'Hurting: Victim without protection',2),(58,'Hurting: Victim with protection',2),(59,'Hurting: Victim protection failed',2),(60,'Hurting: Actor',1),(61,'Hurting: Watcher',1),(62,'Eating: everything',10),(63,'Eating: something',10),(64,'Eating: hungry',10),(65,'Arriving: Actor',34),(66,'Arriving alone: Watcher',34),(67,'Arriving in vehicle: Watcher',34),(68,'Successful lockpicking',11),(69,'Unsuccessful lockpicking',11),(70,'Project: not the right tools',6),(72,'Drop object: Watcher',31),(73,'Drop raws: Actor',31),(74,'Drop raws: Watcher',31),(78,'Give raws: Actor',17),(79,'Give raws: Watcher',17),(80,'Give raws: Victim',16),(83,'Take raws: Actor',31),(84,'Take raws: Watcher',31),(85,'Use on project: Actor',6),(86,'Use on project: Watcher',6),(87,'Loot: Actor',24),(88,'Loot: Watcher',24),(89,'Finish project: on ground',6),(90,'Finish project: in inventory',6),(91,'Finish project: general',6),(92,'Sneeze: Watcher',24),(93,'Sneeze: Actor',24),(94,'Ship docks (seen from town)',18),(96,'Ship docks (seen from ship)',18),(97,'Ship stops at coast',18),(98,'See a dockable place',18),(99,'Stop docking (target moving)',18),(100,'Dragging failed (too many inside)',8),(101,'Dragging person failed (weight)',25),(103,'Dragging person failed (locked)',25),(105,'Drag person from central - actor',25),(112,'Drag person from place - actor',25),(114,'Drag person from place - watcher outside',25),(115,'Drag person from place - watcher inside',25),(116,'Leave central - actor',24),(117,'Leave central - watcher outside',4),(118,'Leave central - watcher inside',3),(119,'Leave place - actor',24),(120,'Leave place - watcher outside',4),(121,'Leave place - watcher inside',3),(125,'Item decays in public',24),(126,'Item decays in private',24),(127,'Point to road - actor',28),(128,'Point to road - watcher',28),(129,'Point to building - actor',28),(130,'Point to building - watcher',28),(132,'Dragging object failed (door locked)',8),(133,'Dragging object from central (Actor)',8),(134,'Dragging object from central (Watcher at start)',8),(135,'Dragging object from central (Watcher at goal)',8),(136,'Dragging object from/to non-central (Actor)',8),(137,'Dragging object from/to non-central (Watcher at start)',8),(138,'Dragging object from/to non-central (Watcher at goal)',8),(141,'Drop an object: Actor',31),(142,'Drop an object: Watcher',31),(143,'Give an object: Actor',15),(144,'Give an object: Watcher',15),(145,'Give an object: Receiver',14),(146,'Take an object: Actor',31),(147,'Take an object: Watcher',31),(148,'Drag person from central - victim',7),(149,'Drag person from central - watcher outside',25),(150,'Drag person from central - watcher inside',25),(151,'Drag person from place - victim',7),(152,'Drag person from place - watcher outside',25),(153,'Drag person from place - watcher inside',25),(154,'Radio receiving side',9),(155,'Radio sending side',9),(156,'Radio sending side - actor',9),(157,'Picking up a copy of a note: Actor',24),(158,'Picking up a copy of a note: Watcher',24),(159,'Change radio receiver frequency : Actor',9),(160,'Change radio receiver frequency : Observer',9),(161,'Actor plays musical instrument',24),(162,'Observer sees musical instrument played',24),(163,'Ringing the doorbell: Watcher outside',NULL),(164,'Ringing the doorbell: Watcher inside',24),(165,'Ringing the doorbell: Actor',24),(166,'Kill animal (new): Watcher',30),(167,'Kill animal (new): Actor',30),(168,'Hit animal (new): Watcher',30),(169,'Hit animal (new): Actor',30),(170,'Kill character (new): Actor',1),(171,'Kill character (new): Victim',2),(172,'Kill character (new): Watcher',1),(173,'Hit character and miss (new): Actor',1),(174,'Hit character and miss (new): Victim',2),(175,'Hit character and damage (new): Victim',2),(176,'Hit character and block (new): Victim',2),(177,'Hit character and shield ineffective (new): Victim',2),(178,'Hit character (new): Actor',1),(179,'Hit character (new): Watcher',1),(180,'Cancel Docking - Observer',18),(181,'Cancel Docking Actor',18),(182,'Start Docking - Observer on Target',18),(183,'Start Docking - Actor',18),(184,'Start Docking - Observer on Ship',18),(185,'Continuation of Docking - Target Observer',18),(186,'Notifcation of ship stopping (non coast)',18),(187,'Eating (Energy foods)',10),(188,'Character dies (Generic) : Watcher',1),(189,'Project Finished - Ending up on Ground',6),(190,'Project finished - Ending up in Inventory',6),(191,'Activities description for New Char',24),(192,'Objects Description for New Char',24),(193,'People description for new Char',24),(194,'Buildings description for new Char',24),(195,'Inventory description for new Char',24),(196,'Location Description for new Char',24),(197,'OOC interface Instructions for New Player',24),(198,'Observer noticing new spawn',5),(199,'Working on project in different location',6),(200,'Project fails - not on water',6),(201,'Project fails - not sailing (moving)',6),(202,'Project fails - not floating',6),(203,'Project fails - not docked',6),(204,'Project fails - not parked',6),(205,'Project fails - not inside building',6),(206,'Project fails - not outside',6),(207,'Project fails - not travelling',6),(208,'Project fails - not on land',6),(209,'Project fails - not on sea',6),(210,'Project fails - not on lake',6),(211,'Being dragged from project',7),(212,'Witness someone being dragged from project',25),(213,'Begin sign changing project - Actor',6),(214,'Begin sign changeing project - Outside Observer',6),(215,'Begin changing project - Inside Observer',6),(216,'Project fails - target sign no longer in same location',6),(217,'Talk to specific person: overheard by others',21),(218,'Toss a coin (obverse): Observer',24),(219,'Toss a coin (reverse): Observer',24),(220,'Toss a coin (obverse): Actor',24),(221,'Toss a coin (reverse): Actor',24),(222,'Roll a die: Observer',24),(223,'Roll a die: Actor',24),(224,'Disassembly - observer',33),(225,'Disassembly - actor',33),(226,'Give note: Actor',13),(227,'Give note: Observer',13),(228,'Give a note: Receiver',12),(229,'Drop a note: Actor',31),(230,'Drop a note: Observer',31),(231,'Take a note: Actor',31),(232,'Take a note: Observer',31),(233,'Changing ship course - observer',18),(234,'Changing ship course - actor',18),(235,'Vehicle out of fuel',34),(236,'Vehicle low on fuel',34),(237,'Stacked objects drop: actor',31),(238,'Stacked objects drop: observer',31),(239,'Stacked objects give: actor',15),(240,'Stacked objects give: receiver',14),(241,'Stacked objects give: observer',15),(242,'Stacked objects take: actor',31),(243,'Stacked objects take: observer',31),(244,'Person faints: Observer',24),(245,'Reserved for neardeath state (not impl yet)',NULL),(246,'Purging: Actor',24),(247,'Purging: Observer',24),(250,'Feeling faint',24),(251,'Point at pack of animals: Actor',28),(252,'Point at pack of animals: Observer',28),(253,'Impossible to drag from a ship, because ship undocked: Actor',25),(254,'Impossible to drag onto a ship, because ship undocked: Actor',25),(255,'Impossible to disassemble lock, because it\'s locked: Actor',6),(256,'Impossible to drag object from a ship, because ship undocked: Actor',8),(257,'Impossible to drag object onto a ship, because ship undocked: Actor',8),(258,'Edit noticeboard: Actor',24),(259,'Edit noticeboard: Observer',24),(260,'Point at object: Actor',28),(261,'Point at object: Observer',28),(262,'Pick up objects from container: Observer',20),(263,'Pick up objects from container: Actor',20),(264,'Point at object in inventory: Actor',28),(265,'Point at object in inventory: Actor',28),(266,'Project cancellation: Actor',6),(267,'Project cancellation: Observer',6),(268,'Starting project cancelation: Observer',6),(269,'Cancel project: Actor',6),(270,'Project cancellation: Observer',6),(271,'New animal in location: Observer',NULL),(272,'Manual created event (by GAB)',NULL),(273,'Custom event - used in custom event manager',NULL),(274,'Open window: Actor',24),(275,'Open window: Observer',24),(276,'Close window: Actor',24),(277,'Close window: Observer',24),(278,'Change of building description: Actor',6),(279,'Change of building description: Observer',6),(280,'Start changing of building description: Actor',6),(281,'Start changing of building description: Observer',6),(282,'Start taming: Actor',27),(283,'Start taming: Observer',27),(284,'Animal death of starvation: Observer',27),(285,'Animal turn back into wild because of starvation: Observer',27),(286,'Animal becomes loyal: Observer',27),(287,'Only initiator can work on project: Actor',6),(288,'Join animal with a pack: Actor',27),(289,'Join animal with a pack: Observer',27),(290,'Separate animal from a pack: Actor',27),(291,'Separate animal from a pack: Observer',27),(292,'Heal wounded animal: Actor',27),(293,'Heal wounded animal: Observer',27),(294,'Project failed, animal not present: Actor',27),(295,'Animal cant find feed: Observer',27),(296,'Start butchering animal: Observer',27),(297,'Cant work, target of a project not here: Actor',6),(298,'You start project: Actor',6),(299,'Start project: Observer',6),(300,'Point at project: Actor',28),(301,'Point at project: Observer',28),(302,'Death from injuries: Actor',2),(303,'Death from injuries: Observer',1),(304,'Start healing victim in nds: Actor',29),(305,'Start healing victim in nds: Victim',29),(306,'Start healing victim in nds: Observer',29),(307,'Finish off victim in nds: Actor',1),(308,'Finish off victim in nds: Victim',2),(309,'Finish off victim in nds: Observer',1),(310,'Cured from nds: Victim',29),(311,'Cured from nds: Observer',29),(312,'Leave nds: Actor',29),(313,'Leave nes: Observer',29),(314,'Fall into nds: Actor',2),(315,'Fall into nds: Observer',1),(316,'See smoke: Observer',24),(317,'Use for project from ground: Actor',6),(318,'Use for project from ground: Observer',6),(319,'Error cant drag - fixed object',25),(320,'Error cant drag - object unreachable',25),(321,'Error cant drag - person unreachable',25),(322,'Error cant drag - goal too far away',25),(323,'Start building destruction: Actor',33),(324,'Start building destruction: Observer',33),(325,'Start building destruction - inside near',33),(326,'Start building destruction - inside farther',33),(327,'Building destroyed - inside near',33),(328,'Building destroyed - inside farther',33),(329,'New season',32),(330,'weather - rain',32),(331,'weather - raining hard',32),(332,'weather - hailstorm',32),(333,'weather - storm',32),(334,'weather - light snow',32),(335,'weather - snow',32),(336,'weather - blizzard',32),(337,'weather - fog',32),(338,'weather - tropical rain',32),(339,'error cant work - target of project not empty',6),(340,'falling off horse: Victim',27),(341,'horse death - starvation',27),(342,'horse turns into wild - starvation',27),(343,'pulling object/person: Actor',25),(344,'pulling object/person: Observer',25),(345,'weather - torrential downpour',32),(346,'start saddling: Actor',27),(347,'start saddling: Observer',27),(348,'unsaddle: Actor',27),(349,'unsaddle: Observer',27),(350,'error cant unsaddle - steed not empty',27),(351,'start vehicle disassembling: Actor',33),(352,'start vehicle disassembling: Observer',33),(353,'train passing location - inside',24),(354,'train passing location - outside',3),(355,'parroting',21),(356,'eat from portable storage: Actor',10),(357,'passing out: Actor',NULL),(358,'passing out: Observer',NULL),(359,'test character leaving intro: Observer',NULL),(360,'steed changes owner: Observer',27),(361,'area custom event: Observer near',NULL),(362,'area custom event: Actor',NULL),(363,'area custom event: Observer far away',NULL),(1016,'Character starts to destroy a lock: Watcher',11),(1017,'Character starts to destroy a lock: Listener',11),(1018,'Character starts to destroy a lock: Actor',11);
/*!40000 ALTER TABLE `events_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events_view`
--

DROP TABLE IF EXISTS `events_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events_view` (
  `observer` mediumint(9) unsigned NOT NULL,
  `viewed` tinyint(1) unsigned NOT NULL,
  `event` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`observer`,`viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events_view`
--

LOCK TABLES `events_view` WRITE;
/*!40000 ALTER TABLE `events_view` DISABLE KEYS */;
/*!40000 ALTER TABLE `events_view` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `finance`
--

DROP TABLE IF EXISTS `finance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `finance` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `month` varchar(7) CHARACTER SET latin1 NOT NULL,
  `transactionfees` decimal(5,2) NOT NULL DEFAULT '0.00',
  `serverrental` decimal(5,2) NOT NULL DEFAULT '0.00',
  `domainname` decimal(5,2) NOT NULL DEFAULT '0.00',
  `marketing` decimal(5,2) NOT NULL DEFAULT '0.00',
  `advertisements` decimal(5,2) NOT NULL DEFAULT '0.00',
  `periodicdonations` decimal(5,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `finance`
--

LOCK TABLES `finance` WRITE;
/*!40000 ALTER TABLE `finance` DISABLE KEYS */;
/*!40000 ALTER TABLE `finance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gamelock`
--

DROP TABLE IF EXISTS `gamelock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gamelock` (
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`locked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gamelock`
--

LOCK TABLES `gamelock` WRITE;
/*!40000 ALTER TABLE `gamelock` DISABLE KEYS */;
/*!40000 ALTER TABLE `gamelock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genes`
--

DROP TABLE IF EXISTS `genes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genes` (
  `person` mediumint(9) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `value` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`person`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genes`
--

LOCK TABLES `genes` WRITE;
/*!40000 ALTER TABLE `genes` DISABLE KEYS */;
/*!40000 ALTER TABLE `genes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `global_config`
--

DROP TABLE IF EXISTS `global_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_config` (
  `key` varchar(32) CHARACTER SET utf8 NOT NULL,
  `value` int(11) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_config`
--

LOCK TABLES `global_config` WRITE;
/*!40000 ALTER TABLE `global_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `global_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hunting`
--

DROP TABLE IF EXISTS `hunting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hunting` (
  `pack_id` int(11) NOT NULL DEFAULT '0',
  `perpetrator` mediumint(8) NOT NULL DEFAULT '0',
  `turn` smallint(5) NOT NULL DEFAULT '0',
  `turnpart` tinyint(3) NOT NULL DEFAULT '0',
  `location` mediumint(8) unsigned DEFAULT '0',
  `animal_type` smallint(5) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hunting`
--

LOCK TABLES `hunting` WRITE;
/*!40000 ALTER TABLE `hunting` DISABLE KEYS */;
/*!40000 ALTER TABLE `hunting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ids`
--

DROP TABLE IF EXISTS `ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ids` (
  `tabel` tinytext CHARACTER SET latin1 NOT NULL,
  `id` bigint(20) unsigned DEFAULT NULL,
  `locked` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tabel`(15))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ids`
--

LOCK TABLES `ids` WRITE;
/*!40000 ALTER TABLE `ids` DISABLE KEYS */;
INSERT INTO `ids` VALUES ('advertisement',64,0),('animal_types',298,0),('bodies',7832,0),('chars',352049,0),('clothes',407,0),('clothes_categories',28,0),('dragging',4129674,0),('events',9,0),('locations',43145,0),('machines',1206,0),('messages',683,0),('objects',13144758,0),('objecttypes',888,0),('obj_notes',708584,0),('pconnections',336,0),('players',150448,0),('pqueue',385157,0),('programming',21,0),('projects',3722674,0),('queue',142745229,0),('rawtools',238,0),('rawtypes',562,0),('regions',5,0),('sailing',221261,0),('suggestions',210,0),('suggestions_new',438,0),('travels',1065119,0);
/*!40000 ALTER TABLE `ids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingame_stats`
--

DROP TABLE IF EXISTS `ingame_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingame_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) CHARACTER SET utf8 NOT NULL,
  `subtype` varchar(32) CHARACTER SET utf8 NOT NULL,
  `number` int(10) unsigned NOT NULL,
  `day` mediumint(8) unsigned NOT NULL,
  `char_id` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type_subtype` (`type`,`subtype`),
  KEY `type_day` (`type`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingame_stats`
--

LOCK TABLES `ingame_stats` WRITE;
/*!40000 ALTER TABLE `ingame_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `ingame_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ips`
--

DROP TABLE IF EXISTS `ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ips` (
  `player` mediumint(9) NOT NULL DEFAULT '0',
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `client_ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `times` smallint(6) NOT NULL DEFAULT '0',
  `lasttime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endtime` datetime DEFAULT NULL,
  KEY `player` (`player`),
  KEY `lasttime` (`lasttime`),
  KEY `ip` (`ip`),
  KEY `client_ip` (`client_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ips`
--

LOCK TABLES `ips` WRITE;
/*!40000 ALTER TABLE `ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `islands`
--

DROP TABLE IF EXISTS `islands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `islands` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `mass` bigint(21) NOT NULL DEFAULT '0',
  `minid` mediumint(5) unsigned DEFAULT NULL,
  `maxid` mediumint(5) unsigned DEFAULT NULL,
  `name` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `islands`
--

LOCK TABLES `islands` WRITE;
/*!40000 ALTER TABLE `islands` DISABLE KEYS */;
/*!40000 ALTER TABLE `islands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kills`
--

DROP TABLE IF EXISTS `kills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kills` (
  `aggressor` int(11) unsigned NOT NULL,
  `victim` int(11) unsigned NOT NULL,
  `animal` mediumint(5) NOT NULL,
  `kills` smallint(4) unsigned NOT NULL DEFAULT '0',
  `damage` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aggressor`,`victim`,`animal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kills`
--

LOCK TABLES `kills` WRITE;
/*!40000 ALTER TABLE `kills` DISABLE KEYS */;
/*!40000 ALTER TABLE `kills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `spawning_allowed` tinyint(1) unsigned DEFAULT NULL,
  `use_density_spawning` tinyint(1) unsigned DEFAULT NULL,
  `newbie_island` mediumint(8) unsigned DEFAULT '0',
  `original_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `abbreviation` char(3) CHARACTER SET latin1 DEFAULT NULL,
  `paypal_lc` char(2) CHARACTER SET latin1 NOT NULL DEFAULT 'US',
  `encoding` varchar(12) CHARACTER SET latin1 NOT NULL DEFAULT 'unknown',
  `encoding_mysql` varchar(12) CHARACTER SET latin1 NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'english',1,1,0,'English','en','US','cp1252','latin1'),(2,'dutch',1,1,0,'Nederlands','nl','NL','cp1252','latin1'),(3,'french',1,1,0,'Francais','fr','FR','cp1252','latin1'),(4,'german',1,1,0,'Deutsch','de','DE','cp1252','latin1'),(5,'spanish',1,1,0,'Español','es','ES','cp1252','latin1'),(6,'russian',1,1,0,'Russkij','ru','RU','cp1251','cp1251'),(7,'swedish',1,1,0,'Svenska','se','SE','cp1252','latin1'),(8,'esperanto',1,0,0,'Esperanto','eo','US','utf-8','utf8'),(9,'polish',1,1,0,'Polski','pl','PL','latin2','latin2'),(10,'latin',0,0,0,'Latina','la','US','cp1252','latin1'),(11,'arabic',0,0,0,'Araby','ar','US','ISO-8859-6','latin6'),(12,'turkish',1,1,0,'Turkce','tu','TR','ISO-8859-9','latin5'),(13,'portuguese',1,1,0,'Português','pt','PT','cp1252','latin1'),(14,'lithuanian',1,1,0,'Lietuviskai','lt','LT','ISO-8859-13','latin7'),(15,'chinese',0,1,0,'Zhongwen','zh','CN','utf-8','utf8'),(16,'finnish',1,1,0,'Suomi','fi','US','cp1252','latin1'),(17,'lojban',1,1,0,'Lojban','jbo','US','cp1252','latin1'),(18,'italian',1,0,0,'Italiano','it','US','cp1252','latin1'),(19,'bulgarian',1,1,0,'български','bg','US','cp1251','cp1251');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `limitations`
--

DROP TABLE IF EXISTS `limitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `limitations` (
  `player` mediumint(8) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='people disallowed from editing descriptions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `limitations`
--

LOCK TABLES `limitations` WRITE;
/*!40000 ALTER TABLE `limitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `limitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loc_root`
--

DROP TABLE IF EXISTS `loc_root`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loc_root` (
  `id` int(11) NOT NULL,
  `root` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loc_root`
--

LOCK TABLES `loc_root` WRITE;
/*!40000 ALTER TABLE `loc_root` DISABLE KEYS */;
/*!40000 ALTER TABLE `loc_root` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location_object_junction`
--

DROP TABLE IF EXISTS `location_object_junction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_object_junction` (
  `location` int(10) unsigned NOT NULL,
  `object` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location`),
  KEY `object` (`object`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location_object_junction`
--

LOCK TABLES `location_object_junction` WRITE;
/*!40000 ALTER TABLE `location_object_junction` DISABLE KEYS */;
/*!40000 ALTER TABLE `location_object_junction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `location_visits`
--

DROP TABLE IF EXISTS `location_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_visits` (
  `location` mediumint(8) unsigned NOT NULL,
  `amortized` mediumint(8) unsigned NOT NULL,
  `last` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='stores last date of visit for locations (type:1) and ships (type:5)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location_visits`
--

LOCK TABLES `location_visits` WRITE;
/*!40000 ALTER TABLE `location_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `location_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `local_number` smallint(6) DEFAULT '0',
  `type` tinyint(3) unsigned DEFAULT NULL,
  `region` mediumint(5) DEFAULT NULL,
  `area` smallint(5) unsigned DEFAULT NULL,
  `borders_lake` tinyint(1) DEFAULT NULL,
  `borders_sea` tinyint(1) DEFAULT NULL,
  `map` tinyint(1) DEFAULT NULL,
  `x` smallint(5) unsigned DEFAULT NULL,
  `y` smallint(5) unsigned DEFAULT NULL,
  `island` smallint(6) unsigned NOT NULL DEFAULT '0',
  `deterioration` mediumint(9) DEFAULT '0',
  `expired_date` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT 'when has location expired, format: 10*day+hour',
  `digging_slots` tinyint(3) unsigned DEFAULT '0',
  `size` mediumint(8) unsigned DEFAULT NULL,
  `pollution` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `island` (`island`),
  KEY `region` (`region`),
  KEY `pos` (`type`,`x`)
) ENGINE=InnoDB AUTO_INCREMENT=72594 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (636,'',0,1,7,1730,0,1,0,593,979,5,0,0,12,NULL,NULL),(637,'',0,1,7,1730,0,1,0,645,1009,5,0,0,8,NULL,NULL),(638,'',0,1,7,1729,0,1,0,605,1020,5,0,0,8,NULL,NULL),(639,'',0,1,7,1729,0,1,0,619,1048,5,0,0,7,NULL,NULL),(640,'',0,1,7,1728,0,1,0,625,990,5,0,0,11,NULL,NULL),(975,'Castle Nraam',1,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(979,'Freddy\'s',2,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(991,'<font color=\"#5f5f5f\"><strong>Community Building</strong></font>',1,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(1028,'Fort James',1,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(1097,'Federation Explorer',0,3,636,155,0,0,0,593,979,0,0,0,0,NULL,NULL),(1110,'Shai Apartments',2,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(1128,'Kitchen',1,2,637,11,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(1302,'Sring-sri Kitchen',1,2,640,5,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(1319,'<font color=\"#656565\"><strong>Dhung Prison</strong></font>',2,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(1335,'<font color=\"#5f5f5f\"><strong>Raw Storage</strong></font>',1,2,991,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(1376,'Bhak City Storage',2,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(1416,'<font color=\"#5f5f5f\"><strong>Processed Storage</strong></font>',2,2,991,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(1950,'<font color=\"#6b6b6b\"><strong>Dhung Anchorage</strong></font>',3,2,639,22,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(1999,'Public Storage',3,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(2388,'Bhak Coaster Harbour',4,2,637,22,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(2415,'Pantry',1,2,1302,11,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(2455,'Office',1,2,975,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(2895,'s.s. suga shack-THE PRINCESS BHAK',0,3,2388,155,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(2904,'Wanderlust',0,3,36058,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(2926,'Federation Trade Center',2,2,640,11,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(3161,'three',0,3,37258,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(3339,'Twilight Wonder',0,3,36274,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(3408,'Tulsen Forest Transport',0,3,638,155,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(4161,'<font color=\"#5f5f5f\"><strong>Foods Storage</strong></font>',3,2,991,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(7180,'The Starlight Lounge',3,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(7321,'Armageddon',0,3,639,129,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(7408,'Wodołaz',0,3,42097,155,0,0,0,625,990,0,0,0,0,NULL,NULL),(7525,'Sanctuary',4,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(7564,'Smoke Jaguar Water Trasport - 1',0,3,36139,129,0,0,0,605,1020,0,0,0,0,NULL,NULL),(8239,'Reid Residence',5,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(8508,'Port of Nraam',6,2,638,22,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(9135,'Cindy',0,3,640,155,0,0,0,625,990,0,0,0,0,NULL,NULL),(9200,'Firecam - The Ashkevron II',0,3,8508,155,0,0,0,605,1020,0,0,0,0,NULL,NULL),(9413,'Fishing Vessel',0,3,639,155,0,0,0,619,1048,0,0,0,0,NULL,NULL),(14820,'Caffrey`s Adventure',0,3,39027,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(16055,'Normandy',0,3,40073,155,0,0,0,588,979,0,0,0,0,NULL,NULL),(16450,'Alquavene',0,3,640,289,0,0,0,625,990,0,0,0,0,NULL,NULL),(16489,'Little La La',0,3,638,155,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(16767,'The Double-D Drake',0,3,42097,155,0,0,0,625,990,0,0,0,0,NULL,NULL),(17171,'Teregotha Trade Loop Runner',0,3,8508,329,0,0,0,605,1020,0,0,0,0,NULL,NULL),(17207,'Dristigheten',0,3,36139,328,0,0,0,605,1020,0,0,0,0,NULL,NULL),(17421,'Fisherman`s Friend',0,3,33932,328,0,0,0,625,990,0,0,0,0,NULL,NULL),(17596,'Little Thunder',0,3,37841,289,0,0,0,593,979,0,0,0,0,NULL,NULL),(17835,'Renaissance',0,3,637,289,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(18061,'KORT Longboat - 2 KB',0,3,40959,155,0,0,0,593,979,0,0,0,0,NULL,NULL),(18143,'Revival',0,3,2388,328,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(18472,'Lillskeppet',0,3,9135,289,0,0,0,625,990,0,0,0,0,NULL,NULL),(18597,'Victoria',0,3,23585,155,0,0,0,619,1048,0,0,0,0,NULL,NULL),(18889,'The Chirp',0,3,1097,289,0,0,0,593,979,0,0,0,0,NULL,NULL),(18947,'Private Quarters',1,2,1319,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(19172,'El Albatros',0,3,638,155,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(19538,'Kastor`s Ship',0,3,639,328,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(19613,'Storage',1,2,1028,270,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(19863,'Szalupa Wardzingow',0,3,22934,289,0,0,0,645,1009,0,0,0,0,NULL,NULL),(19911,'Marley`s Bike',0,3,638,56,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(19923,'Robonia II',0,3,639,155,0,0,0,619,1048,0,0,0,0,NULL,NULL),(20298,'Shai Archives',3,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(20445,'<font color=\"#717171\"><strong>Dhung Ocean View Apartments</strong></font>',4,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(20603,'Welcome Home',1,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(20735,'Vault of Shai',1,2,20298,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(20747,'==Shames` House And Workshop==',2,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(20810,'Miron Hills - built by Alessandra Tropica c1700',3,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(21062,'Dhung Dirt Hog',0,3,639,324,0,0,0,619,1048,0,0,0,0,NULL,NULL),(21179,'<font color=\"#777777\"><strong>Stone Structure</strong></font>',5,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(21220,'Scarab Claw of Dhung',0,3,639,324,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(21376,'Zlota Arka ',0,3,22934,289,0,0,0,645,1009,0,0,0,0,NULL,NULL),(21438,'Faye`s Place',4,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(21738,'Pełnia Księżyca',0,3,25655,64,0,0,0,625,990,0,0,0,0,NULL,NULL),(21789,'Egnard`s Apartment',5,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(21934,'Nraam Runner',0,3,636,56,0,0,0,593,979,0,0,0,0,NULL,NULL),(22258,'Federation Docks',4,2,636,22,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(22589,'Kayla`s Bakery and Clothing Shop',5,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(22653,'Mesa',1,2,21738,549,0,0,0,625,990,0,0,0,0,NULL,NULL),(22701,'Storage',1,2,20810,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(22729,'The Anne',0,3,2388,289,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(22934,'Gimmick',0,3,33920,328,0,0,0,645,1009,0,0,0,0,NULL,NULL),(22977,'Vågsaga',0,3,34524,328,0,0,0,593,979,0,0,0,0,NULL,NULL),(23585,'Sea Master I',0,3,1950,64,0,0,0,619,1048,0,0,0,0,NULL,NULL),(23860,'Shai Bike',0,3,636,54,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(23930,'Mausoleum of Shai',2,2,20298,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(23974,'Paradiso Perduto',6,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(24030,'Ezra`s Shack',6,2,636,270,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(24063,'The Game',0,3,2388,328,0,0,0,645,1009,0,0,0,0,NULL,NULL),(24090,'Apartment 1',1,2,1110,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(24238,'T.S.S. Fluffy Alpaca',0,3,28120,328,0,0,0,625,990,0,0,0,0,NULL,NULL),(24262,'Stelarius',0,3,42737,328,0,0,0,645,1009,0,0,0,0,NULL,NULL),(24273,'City Storage',2,2,1110,270,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(24478,'Shai Lighthouse',7,2,636,109,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(24819,'Cargo Hold',1,2,23585,549,0,0,0,619,1048,0,0,0,0,NULL,NULL),(24857,'The White Horse',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,NULL),(24908,'Crew Quarters',1,2,24857,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(24915,'Captain`s Cabin',2,2,24857,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(24937,'Captain`s Cabin',2,2,23585,322,0,0,0,619,1048,0,0,0,0,NULL,NULL),(24984,'Cargo Hold',3,2,24857,549,0,0,0,605,1020,0,0,0,0,NULL,NULL),(25133,'Teregotha Meeting Hall',7,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(25241,'<font color=\"#7d7d7d\"><strong>Teregotha Regional Hospital</strong></font>',6,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(25258,'Citizens Quarters',8,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(25356,'<font color=\"#7d7d7d\"><strong>Office and Medical Stores</strong></font>',1,2,25241,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(25515,'Jaffars place/workplace',1,2,25258,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(25520,'Pearl',0,3,33932,289,0,0,0,625,990,0,0,0,0,NULL,NULL),(25580,'Oro Darter- 01',0,3,8508,129,0,0,0,605,1020,0,0,0,0,NULL,NULL),(25597,'Scarab Pantry',1,2,979,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(25655,'Sring-Sri Harbour',3,2,640,22,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(25666,'Red Eyes Black Dragon',0,3,31208,155,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(25708,'The Padlock',0,3,25655,328,0,0,0,625,990,0,0,0,0,NULL,NULL),(25799,'Cargo Hold',1,2,17171,550,0,0,0,605,1020,0,0,0,0,NULL,NULL),(25855,'Jumes Boat',0,3,28477,328,0,0,0,605,1020,0,0,0,0,NULL,NULL),(26031,'Gypsy\'s Kiss',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(26063,'Pantry',1,2,26031,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(26092,'Nanna',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,NULL),(26173,'Ładownia',1,2,26092,549,0,0,0,605,1020,0,0,0,0,NULL,NULL),(26176,'Personnel Quarters ',2,2,17171,544,0,0,0,605,1020,0,0,0,0,NULL,NULL),(26228,'<font color=\"#838383\"><strong>Stronghold</strong></font>',7,2,639,10,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(26355,'<font color=\"#898989\"><strong>Bank of Dhung</strong></font>',8,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(26401,'Bluejay Nest',7,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(26417,'Stronghold',1,2,26228,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(26434,'The Tiber Septim',0,3,639,328,0,0,0,619,1048,0,0,0,0,NULL,NULL),(26486,'Crumb Stash',2,2,26031,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(26591,'Rocks&#44; Minerals&#44; and Metals',2,2,975,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(26603,'Baker\'s Bounty',1,2,1128,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(26654,'Nikki Nest',8,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(26775,'<font color=\"#898989\"><strong>Office</strong></font>',1,2,26355,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(27001,'Nraam Sunchaser',0,3,8508,289,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(27418,'Skanshytten',1,2,17207,544,0,0,0,605,1020,0,0,0,0,NULL,NULL),(27488,'Knickknacks ',3,2,26031,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(27532,'Sring-Sri Castle',4,2,640,5,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(27692,'T.S.S. Black Crow',0,3,636,328,0,0,0,593,979,0,0,0,0,NULL,NULL),(27741,'Secret, hidden room containing booby traps. Keep out.',2,2,1028,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(27810,'<font color=\"#8f8f8f\"><strong>The Eastons: Zol and Roses</strong></font>',9,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(28032,'Blomsterhyddan',8,2,636,297,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(28053,'Sring-sri Lighthouse 483',5,2,640,109,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(28108,'United Traders Ship#1',0,3,2388,328,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(28120,'T.S.S. Spirit of the Forest',0,3,25655,64,0,0,0,625,990,0,0,0,0,NULL,NULL),(28243,'Dhung Flower',0,3,639,55,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(28305,'Shai Inn',9,2,636,5,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(28477,'The Farseeing Fox',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,NULL),(28621,'The SL Longbeak',0,3,640,155,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(28754,'C.M.S. Victorious',0,3,22258,328,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(28772,'The Bhak Town Hall',5,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(28869,'Keldorn Kub',0,3,28477,289,0,0,0,605,1020,0,0,0,0,NULL,NULL),(28907,'Guest Cabin',4,2,26031,543,0,0,0,645,1009,0,0,0,0,NULL,NULL),(28911,'Tool Shed',5,2,26031,543,0,0,0,645,1009,0,0,0,0,NULL,NULL),(29029,'RFe Navy Raft',0,3,41513,275,0,0,0,625,990,0,0,0,0,NULL,NULL),(29069,'Mausoleum In Memoriam',6,2,640,5,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(29107,'pantry',1,2,22589,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(29226,'In Memory of Vynn',0,3,30250,328,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(29258,'Dhung Kiang',0,3,639,55,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(29348,'Nraam Rickshaw',0,3,636,55,0,0,0,593,979,0,0,0,0,NULL,NULL),(29425,'Empty',6,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(29493,'Apartment 2 -- Snow\'s Dollhouse',3,2,1110,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(29642,'Teregoth Island University',9,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(29819,'Bhakcycle',0,3,637,316,0,0,0,645,1009,0,0,0,0,NULL,NULL),(29859,'Geralt\'s Room',2,2,25258,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(30250,'Crown of Dhung',0,3,1950,329,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(30258,'Fia Leopârd Den',7,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(30294,'Lounge',1,2,28305,293,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(30302,'Little piece of Dhung',0,3,25666,289,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(30330,'Claera',0,3,639,129,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(30342,'City of Dhung Sparrow.',0,3,1950,129,0,0,0,619,1048,0,0,0,0,NULL,NULL),(30413,'Monstrous`s Medical Clinic and Labratory',7,2,640,11,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(30447,'Lorenzo\'s General Store',10,2,638,10,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(30520,'Back to Bhak on the waters.',0,3,36058,129,0,0,0,645,1009,0,0,0,0,NULL,NULL),(30564,'Cat`s Cradle',1,2,30258,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(30594,'White Cloud',0,3,1950,328,0,0,0,619,1048,0,0,0,0,NULL,NULL),(30696,'Nraam Rickshaw 2',0,3,638,55,0,0,0,605,1020,0,0,0,0,NULL,NULL),(30742,'Food Pantry',3,2,975,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(30834,'Victor Franklin Attorney at Law',8,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(30929,'Storage',1,2,27532,293,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(30990,'Hold',1,2,28477,549,0,0,0,605,1020,0,0,0,0,NULL,NULL),(31016,'Zaidhos Tahdroe Grotto',9,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31086,'Manufactured Goods',4,2,975,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(31208,'Out at Sea',0,3,22258,128,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(31238,'~Nraam Whipper~',0,3,638,316,0,0,0,605,1020,0,0,0,0,NULL,NULL),(31279,'Treasury of Bhak',10,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31305,'Below Deck',1,2,31208,548,NULL,NULL,NULL,590,990,0,0,0,0,NULL,NULL),(31321,'Apartment 3',4,2,1110,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(31338,'Wir 320',0,3,40588,328,0,0,0,605,1020,0,0,0,0,NULL,NULL),(31395,'Sring-Sri Raft',0,3,25520,275,0,0,0,625,990,0,0,0,0,NULL,NULL),(31412,'Lunarejo - Pay for view [Esquife]',0,3,34489,289,0,0,0,645,1009,0,0,0,0,NULL,NULL),(31460,'Lorin`s Houseboat',0,3,40991,129,0,0,0,645,1009,0,0,0,0,NULL,NULL),(31513,'Secundus Sanctum',11,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31596,'Starboard',2,2,31208,543,NULL,NULL,NULL,590,990,0,0,0,0,NULL,NULL),(31638,'Nraam\'s Potion Shop',11,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(31687,'Sring-Sri Raft #2',0,3,41513,275,0,0,0,625,990,0,0,0,0,NULL,NULL),(31757,'Closet',1,2,2926,294,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(31773,'Teregotha Thinkery',12,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31788,'Kestrel\'s Nest: Clothing and Accessories',13,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31825,'Port',3,2,31208,543,NULL,NULL,NULL,590,990,0,0,0,0,NULL,NULL),(31925,'~Lightbeams of Bhak~',14,2,637,109,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(31945,'Free Use Materials',2,2,2926,294,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(32017,'Min Steel Trading',0,3,2388,155,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32025,'Bhak Castle',15,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32052,'Herbal Crow Hut',16,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32085,'Gatehouse',1,2,32025,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32142,'Outer Ward',1,2,32085,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32179,'Olive room',1,2,27810,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(32195,'Inner Gatehouse',1,2,32142,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32223,'Akki shack',17,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32227,'Spike`s Import Export and other things CO.',10,2,636,270,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(32329,'Radio Room',2,2,28477,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(32336,'Inner Ward',1,2,32195,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32390,'~Bhak Brock~',0,3,639,316,0,0,0,619,1048,0,0,0,0,NULL,NULL),(32437,'Apartment 4',5,2,1110,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(32447,'The Sring-Sri Inn',8,2,640,630,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(32485,'a sloop',0,3,25655,328,0,0,0,625,990,0,0,0,0,NULL,NULL),(32517,'Fia\'s \"Vine & Dairy\"',18,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32518,'~Bhak Burner~',0,3,636,316,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(32519,'Workshops',1,2,32336,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32540,'Spike`s Ship',0,3,638,155,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(32587,'Sring-Sri Residentials',9,2,640,786,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(32593,'Frigus Justitia',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(32595,'Kitchen',2,2,32336,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32648,'~Femme Fatál~',0,3,640,316,0,0,0,625,990,0,0,0,0,NULL,NULL),(32661,'~Bhak Apotheosis~',0,3,637,316,0,0,0,645,1009,0,0,0,0,NULL,NULL),(32739,'~Bhak Scrambler~',0,3,637,316,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32777,'Smithy',3,2,32336,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32789,'Firefly Clothier ',19,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32879,'Barracks',4,2,32336,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32880,'<font color=\"#959595\"><strong>Dhung`s Library and Center of Research</strong></font>',10,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(32913,'Sring Sri Envoy',0,3,640,316,0,0,0,625,990,0,0,0,0,NULL,NULL),(32995,'Apartments',5,2,32336,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(32999,'Cargo Hold',1,2,32593,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33070,'The Bridge',2,2,32593,322,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33088,'Aft Cargo Hold',3,2,28477,549,0,0,0,605,1020,0,0,0,0,NULL,NULL),(33197,'Kajuta Centralna',2,2,21738,322,0,0,0,625,990,0,0,0,0,NULL,NULL),(33217,'Kajuta dziobowa',3,2,21738,543,0,0,0,625,990,0,0,0,0,NULL,NULL),(33235,'General Storage (fur, bones, reeds, etc)',5,2,975,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(33271,'Back room',1,2,32447,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(33291,'Sring-Sri Interceptor',0,3,23585,289,0,0,0,619,1048,0,0,0,0,NULL,NULL),(33341,'Black Bolt',0,3,637,316,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33357,'Kitchen',1,2,32789,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(33359,'Quillan Gornt Trading',0,3,2388,328,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33464,'Room 1',2,2,26228,270,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(33494,'~ Classic Bhak ~',0,3,637,140,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33725,'<font color=\"#5f5f5f\"><strong>Tools and Armory</strong></font>',4,2,991,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(33762,'Joshua`s',0,3,637,155,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(33769,'Inn Private Room #1',2,2,32447,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(33783,'Inn Private Room #2',3,2,32447,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(33836,'Raft 01 - Lunarejo - Pay for View',0,3,31412,275,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33920,'The Good Brother',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(33932,'The Sring-Sri Star',0,3,25655,329,0,0,0,625,990,0,0,0,0,NULL,NULL),(34029,'Mostek',1,2,33920,543,0,0,0,645,1009,0,0,0,0,NULL,NULL),(34047,'Archives Room',1,2,31773,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(34059,'Small cabin',1,2,33932,543,0,0,0,625,990,0,0,0,0,NULL,NULL),(34069,'Kambuz',2,2,33920,543,0,0,0,645,1009,0,0,0,0,NULL,NULL),(34164,'Kubryk',3,2,33920,322,0,0,0,645,1009,0,0,0,0,NULL,NULL),(34245,'Ελπιδιος (Elpidios)',0,3,639,328,0,0,0,619,1048,0,0,0,0,NULL,NULL),(34262,'Shai Security',0,3,636,316,0,0,0,593,979,0,0,0,0,NULL,NULL),(34278,'Tool Room',1,2,33725,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(34425,'Armory Room',2,2,33725,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(34483,'Cellar',1,2,32595,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(34489,'Lunarejo Bill - Pay for view',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(34524,'Hemvändaren',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,NULL),(34528,'Shai Security 2',0,3,636,316,0,0,0,593,979,0,0,0,0,NULL,NULL),(34545,'Shmoag Wallace F`Shnernk. Yes. Really.',0,3,637,551,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(34548,'Kadmorr Memorial Industries',20,2,637,11,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(34609,'The Guiding Star',0,3,36139,328,0,0,0,605,1020,0,0,0,0,NULL,NULL),(34668,'Smith`s Room',1,2,34548,294,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(34955,'Maskinrum',1,2,34524,322,0,0,0,593,979,0,0,0,0,NULL,NULL),(35068,'Scupper',0,3,39027,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(35118,'Lastrum',2,2,34524,549,0,0,0,593,979,0,0,0,0,NULL,NULL),(35135,'Walsh Mausoleum',11,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(35162,'Cellar',0,3,39027,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(35170,'Sea Beetle',0,3,638,275,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(35290,'T-100 Reed Boat',0,3,22258,839,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(35304,'Czarna Godzina',0,3,21376,275,0,0,0,645,1009,0,0,0,0,NULL,NULL),(35309,'Myers Residence - built by Alex Myers of Bhak c3050',9,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35326,'Bedroom',1,2,32517,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(35356,'Escape from Bhak',0,3,637,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(35363,'Cargo Hold',1,2,28120,549,0,0,0,625,990,0,0,0,0,NULL,NULL),(35415,'Teregotha`s United Transit Tandem 1 (no locks allowed)',0,3,639,56,0,0,0,619,1048,0,0,0,0,NULL,NULL),(35474,'Reed Crest',0,3,2388,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(35484,'Teregotha`s United Transit Tandem 3 (no locks allowed)',0,3,640,56,0,0,0,625,990,0,0,0,0,NULL,NULL),(35500,'Teregotha`s United Transit Tandem 2 (no locks allowed)',0,3,639,56,0,0,0,619,1048,0,0,0,0,NULL,NULL),(35515,'Teregotha`s United Transit Tandem 5 (no locks allowed)',0,3,636,56,0,0,0,593,979,0,0,0,0,NULL,NULL),(35546,'Teregotha`s United Transit Tandem 4 (no locks allowed)',0,3,637,56,0,0,0,645,1009,0,0,0,0,NULL,NULL),(35565,'Teregotha`s United Transit Tandem 6 (no locks allowed)',0,3,639,56,0,0,0,619,1048,0,0,0,0,NULL,NULL),(35582,'Teregotha`s United Transit Tandem 8 (no locks allowed)',0,3,636,56,0,0,0,593,979,0,0,0,0,NULL,NULL),(35591,'Private Quarters',2,2,28120,543,0,0,0,625,990,0,0,0,0,NULL,NULL),(35600,'Tailoring Materials',6,2,975,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(35610,'Bedroom',1,2,35309,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35617,'Workshop',3,2,28120,322,0,0,0,625,990,0,0,0,0,NULL,NULL),(35640,'Teregotha`s United Transit Tandem 7 (no locks allowed)',0,3,636,56,0,0,0,593,979,0,0,0,0,NULL,NULL),(35664,'Kitchen',2,2,35309,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35696,'Closet',1,2,23974,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35797,'the Chieftain',0,3,639,316,0,0,0,619,1048,0,0,0,0,NULL,NULL),(35827,'Storage',3,2,35309,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35838,'<font color=\"#9b9b9b\"><strong>Gentry Metal Works</strong></font>',11,2,639,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(35893,'Reed Boat',0,3,67038,839,0,0,0,645,1009,0,0,0,0,NULL,NULL),(35993,'Trout`s Voyager',0,3,24063,839,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36043,'Storage Room #1',1,2,35838,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(36058,'Silver Scarab',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36066,'Carborundum`s Place - GO AWAY.',10,2,20445,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(36139,'Cesaret',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,NULL),(36158,'Tool and Weapon Till',2,2,1128,294,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(36189,'Kabin',1,2,36139,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(36266,'Serendipity',0,3,637,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36274,'The Astrella',0,3,2388,329,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36296,'Shai Security 3',0,3,636,316,0,0,0,593,979,0,0,0,0,NULL,NULL),(36306,'Shai Security 4',0,3,636,316,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(36318,'Jessa Fox`s Longboat',0,3,40925,155,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36326,'Spirit of Adventure',0,3,8508,328,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(36363,'Runner Inc. Resource Runner 01',0,3,639,316,0,0,0,619,1048,0,0,0,0,NULL,NULL),(36374,'Nraam Security Center',12,2,638,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(36393,'Ship Comm',4,2,24857,322,0,0,0,605,1020,0,0,0,0,NULL,NULL),(36419,'Jessa Fox Cottage',21,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(36426,'Machine Room',1,2,36058,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(36598,'Apartment 5',6,2,1110,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(36671,'Adventure Time',0,3,639,155,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(36745,'Beachfront Villa',22,2,637,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(36777,'Apartment 6',7,2,1110,294,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(36971,'Küçük Depo',2,2,36139,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(36988,'The Brig',5,2,24857,543,0,0,0,605,1020,0,0,0,0,NULL,NULL),(37120,'♣Scupper-II♣',0,3,639,838,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(37258,'Into The Void',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(37367,'Store',2,2,36058,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(37384,'Tyrus Residence',23,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(37396,'One More Room',2,2,35838,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(37473,'Bhak Trader',0,3,637,316,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(37543,'Captain`s Office',1,2,37258,322,0,0,0,645,1009,0,0,0,0,NULL,NULL),(37598,'escape from Teragotha 1',0,3,637,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(37646,'Crew Quarters',3,2,36058,543,0,0,0,645,1009,0,0,0,0,NULL,NULL),(37678,'~Closed. No Entry~',1,2,7180,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(37711,'Darkmyre Keep',12,2,636,11,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(37746,'Bhak Trading Center',24,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(37841,'Falling Star',0,3,22258,329,0,0,0,593,979,0,0,0,0,NULL,NULL),(37874,'Cargo Hold',1,2,37841,550,0,0,0,593,979,0,0,0,0,NULL,NULL),(37886,'Food Room',2,2,37841,322,0,0,0,593,979,0,0,0,0,NULL,NULL),(37902,'<font color=\"#5f5f5f\"><strong>Shiraz Clothing</strong></font>',5,2,991,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(37986,'Lillen',0,3,34524,289,0,0,0,593,979,0,0,0,0,NULL,NULL),(38108,'*leopard headed reed boat*',0,3,38508,839,0,0,0,645,1009,0,0,0,0,NULL,NULL),(38114,'*a cage suspended by a rope from the ceiling*',0,3,37678,849,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(38121,'Captain`s Cabin',3,2,37841,544,0,0,0,593,979,0,0,0,0,NULL,NULL),(38158,'Main cargo',1,2,30250,550,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(38198,'Faiths big Cabin',2,2,30250,544,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(38243,'Rose Room',2,2,7180,785,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(38283,'The Pantry',1,2,33769,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(38291,'Ładownia',4,2,33920,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(38373,'Daisy Room',3,2,7180,785,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(38508,'*Figurehead: leopard like a clear, sunless sky fully embracing a light ocelot with a little fire in her eyes*',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(38541,'Captains Quarters',3,2,30250,322,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(38664,'Kebabble -- Restaurant and Theater',13,2,638,10,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(38714,'Skin & Bone',0,3,636,838,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(38748,'Ina`s Trade Wagon',0,3,639,57,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(38803,'Radio Room',4,2,30250,543,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(38865,'Depo - Cesaret',3,2,36139,549,0,0,0,605,1020,0,0,0,0,NULL,NULL),(38943,'Shaw is Nice',0,3,640,55,0,0,0,625,990,0,0,0,0,NULL,NULL),(39027,'Miron Animal Transporter',0,3,2388,128,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39053,'Rybnik',1,2,21179,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(39113,'Crows` Claw',2,2,21179,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(39154,'<font color=\"#a1a1a1\"><strong>Animal Dropouts and More - Free Stuff for All</strong></font>',12,2,639,881,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(39165,'Secure Storage 1',1,2,8239,270,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(39190,'Secure Storage 2',1,2,39165,270,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(39207,'vault',1,2,2455,785,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(39209,'Dhung Free Sail 001',0,3,639,838,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(39274,'Fer`s Room',2,2,32789,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39281,'Zazi The Beast',1,2,39027,543,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39338,'Seeds and Stuff',1,2,32052,270,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39436,'Sewing Shack',3,2,1128,270,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39444,'Dedrick`s Rappsody',0,3,25655,64,0,0,0,625,990,0,0,0,0,NULL,NULL),(39490,'Nikolai`s Fate',0,3,40959,155,0,0,0,593,979,0,0,0,0,NULL,NULL),(39563,'Cargo',2,2,39027,548,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(39606,'Bamilia',0,3,1950,129,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(39792,'Marell del Mar',0,3,30520,838,0,0,0,645,1009,0,0,0,0,NULL,NULL),(40060,'~Shooting Star~',1,2,7525,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(40073,'The Mighty Angel',0,5,0,64,0,0,0,588,979,0,0,0,0,NULL,NULL),(40151,'Office of Research',1,2,32880,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(40153,'Back Room',1,2,37384,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(40553,'Sring-Sri City Hall',10,2,640,630,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(40588,'Celeste',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,NULL),(40653,'Trade Loop Depot',1,2,8508,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(40779,'Blue Ribbons',1,2,40588,322,0,0,0,605,1020,0,0,0,0,NULL,NULL),(40925,'The Foxy Lady',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(40959,'Gypsy Queen',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,NULL),(40991,'Ships - N - Giggles II',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(41009,'Town Storage',13,2,636,5,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41049,'Rubí Azul',0,3,39444,155,0,0,0,625,990,0,0,0,0,NULL,NULL),(41057,'Forbidden Fruit',0,3,22258,329,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41072,'Animal Products',1,2,41009,293,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41081,'Raspberry Room',1,2,41057,544,NULL,NULL,NULL,589,976,0,0,0,0,NULL,NULL),(41096,'Cargo Hold',1,2,38508,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(41111,'Food Products',2,2,41009,293,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41136,'Metals and Resources',3,2,41009,293,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41145,'Mausoleum',1,2,29642,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(41162,'Tools and Weapons',4,2,41009,293,NULL,NULL,NULL,593,979,0,0,0,0,NULL,NULL),(41330,'Strawberry Room',2,2,41057,322,NULL,NULL,NULL,589,976,0,0,0,0,NULL,NULL),(41361,'Storage',1,2,31638,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(41497,'Below the deck (Cargo Hold)',1,2,40991,549,0,0,0,645,1009,0,0,0,0,NULL,NULL),(41508,'Radio Room',3,2,17171,322,0,0,0,605,1020,0,0,0,0,NULL,NULL),(41513,'Destiny',0,3,42097,129,0,0,0,625,990,0,0,0,0,NULL,NULL),(41557,'Cell Block A',2,2,1319,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,NULL),(42087,'Zellum',0,3,2388,155,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,NULL),(42097,'Pływający Jaskier',0,3,25655,64,0,0,0,625,990,0,0,0,0,NULL,NULL),(42150,'Ładownia',1,2,42097,549,0,0,0,625,990,0,0,0,0,NULL,NULL),(42160,'Cellae Servorum',2,2,42097,543,0,0,0,625,990,0,0,0,0,NULL,NULL),(42165,'Robert\'s Residence',3,2,25258,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42185,'The Bedroom',3,2,42097,543,0,0,0,625,990,0,0,0,0,NULL,NULL),(42186,'Kajuta 3',4,2,42097,322,0,0,0,625,990,0,0,0,0,NULL,NULL),(42202,'Cathy Dubois\' Apt.',4,2,25258,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42264,'A small ship',0,3,637,289,0,0,0,645,1009,0,0,0,0,NULL,NULL),(42364,'Holding Room A',1,2,36374,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42379,'Jaskiernik',0,3,42097,328,0,0,0,625,990,0,0,0,0,NULL,NULL),(42392,'Holding Room B',2,2,36374,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42465,'Holding Room C',3,2,36374,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42527,'Starlight of Nraam',14,2,638,109,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(42737,'Ciemność Nocy',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,NULL),(42792,'Sanktuarium Chaosu',1,2,42737,322,0,0,0,645,1009,0,0,0,0,NULL,NULL),(42836,'Nraam Rapid Transport',0,3,638,59,0,0,0,605,1020,0,0,0,0,NULL,NULL),(42987,'Cargo Hold',1,2,39444,549,0,0,0,625,990,0,0,0,0,NULL,NULL),(43086,'Roses',0,3,638,717,0,0,0,605,1020,0,0,0,0,NULL,NULL),(65548,'Bedroom',1,2,29859,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,NULL),(65590,'Grand Kingfisher',0,3,637,1525,0,0,0,645,1009,0,0,0,0,NULL,NULL),(65622,'Guest Cabin',2,2,39444,543,0,0,0,625,990,0,0,0,0,NULL,0),(65895,'Main Cabin',3,2,39444,322,0,0,0,625,990,0,0,0,0,NULL,0),(65960,'Guest Cabin 2',4,2,39444,543,0,0,0,625,990,0,0,0,0,NULL,0),(66093,'<font color=\"#a6a6a6\"><strong>Sanctuary</strong></font>',13,2,639,10,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(66440,'<font color=\"#a6a6a6\"><strong>Catacombs</strong></font>',1,2,66093,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(66591,'Star`s Room',1,2,39338,270,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(66659,'Casino Royale',1,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(66971,'Rebelled Machine',0,3,638,716,0,0,0,605,1020,0,0,0,0,NULL,0),(67017,'The raft',0,3,17596,275,0,0,0,593,979,0,0,0,0,NULL,0),(67038,'The Albatross',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,0),(67056,'tyrus kayak',0,3,637,838,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(67181,'radio hut',1,2,40959,543,0,0,0,593,979,0,0,0,0,NULL,0),(67263,'<font color=\"#6b6b6b\"><strong>Trade Loop Depository</strong></font>',1,2,1950,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(67308,'Sylvan Stone',2,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(67325,'<font color=\"#E76300\"><strong> Φ~The Wisdom Seeker~Φ </strong></font>',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,0),(67470,'Cesaret - Kamara',4,2,36139,322,0,0,0,605,1020,0,0,0,0,NULL,0),(67497,'§°Red Raven Designs°§',25,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(67507,'Cold Storage \"Basement, in Corner of Room\"',1,2,36745,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(67527,'machinery',2,2,40959,322,0,0,0,593,979,0,0,0,0,NULL,0),(67547,'Klaus Haus',26,2,637,611,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(67658,'Captain`s Suite',6,2,26031,322,0,0,0,645,1009,0,0,0,0,NULL,0),(67710,'Decklan`s Ride (Sleek Black Pickup)',0,3,638,715,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(67841,'The Fluffy Mouflon',0,3,640,128,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(67843,'Storage',1,2,36274,550,0,0,0,645,1009,0,0,0,0,NULL,0),(67863,'Captain`s Quarters',2,2,36274,544,0,0,0,645,1009,0,0,0,0,NULL,0),(67867,'Second Cabin',3,2,36274,322,0,0,0,645,1009,0,0,0,0,NULL,0),(67879,'Small Cabin #1',4,2,36274,543,0,0,0,645,1009,0,0,0,0,NULL,0),(67896,'Small Cabin #2',5,2,36274,543,0,0,0,645,1009,0,0,0,0,NULL,0),(67897,'House of Theon',27,2,637,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(67910,'Nraam Security',0,3,638,316,0,0,0,605,1020,0,0,0,0,NULL,0),(67948,'Cabin',1,2,67038,322,0,0,0,645,1009,0,0,0,0,NULL,0),(67965,'Teregotha Executive',0,3,638,316,0,0,0,605,1020,0,0,0,0,NULL,0),(68032,'Quarters',1,2,67841,543,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68056,'Bedroom',2,2,36745,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(68156,'Day Breaker',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,0),(68228,'Cargo Hold',3,2,40959,549,0,0,0,593,979,0,0,0,0,NULL,0),(68250,'<CANTR REPLACE NAME=animal_domesticated_kiang_s><CANTR REPLACE NAME=name_steed_of> <CANTR CHARNAME ID=348237>',0,3,637,1598,0,0,0,645,1009,0,0,0,0,NULL,NULL),(68252,'Justice At Sea',0,3,25655,328,0,0,0,625,990,0,0,0,0,NULL,0),(68457,'<CANTR REPLACE NAME=animal_domesticated_kiang_s><CANTR REPLACE NAME=name_steed_of> <CANTR CHARNAME ID=357332>',0,3,640,1598,NULL,NULL,NULL,625,990,0,0,0,0,NULL,NULL),(68488,'~Lon Wilton~',1,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68590,'Extra Storage',4,2,40959,543,0,0,0,593,979,0,0,0,0,NULL,0),(68728,'Guest Storage',4,2,32447,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68735,'<CANTR REPLACE NAME=animal_domesticated_kiang_s><CANTR REPLACE NAME=name_steed_of> <CANTR CHARNAME ID=376584>',0,3,640,1598,0,0,0,625,990,0,0,0,0,NULL,NULL),(68797,'Gently Used Knickknacks',3,2,36745,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(68818,'Bhak Jail  cell',1,2,28772,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(68866,'Daisy`s Office',1,2,40553,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68871,'◆◇ Silver Rain ◇◆',0,3,636,316,0,0,0,593,979,0,0,0,0,NULL,0),(68874,'Library',2,2,40553,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68942,'Will of Flight',11,2,640,630,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68965,'~Benjamin~',2,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68968,'~William~',3,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(68986,'<font color=\"#acacac\"><strong>Finger of Dhung</strong></font>',14,2,639,109,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(69089,'Mighty Chitin Crusher',0,3,640,708,0,0,0,625,990,0,0,0,0,NULL,0),(69273,'Conference Room',3,2,40553,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69287,'The Lazy Kiang Lounge',4,2,40553,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69480,'Galley',2,2,67841,543,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69514,'<font color=\"#a6a6a6\"><strong>Tomb of Faith Crows</strong></font>',1,2,66440,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(69543,'<font color=\"#a6a6a6\"><strong>Tomb of Alice Clear</strong></font>',2,2,66440,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(69552,'<font color=\"#a6a6a6\"><strong>Tomb of Sarah</strong></font>',3,2,66440,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(69566,'<font color=\"#a6a6a6\"><strong>Tomb of Cholera Steelbourne</strong></font>',4,2,66440,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(69582,'Kalea`s Room',3,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69589,'Room 4',4,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69602,'Lilo`s ~Hideaway~',5,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69609,'Arielle\'s ',5,2,25258,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(69693,'~Dedrick Rapp~',4,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69755,'<CANTR REPLACE NAME=animal_domesticated_kiang_s><CANTR REPLACE NAME=name_steed_of> <CANTR CHARNAME ID=376584>',0,3,640,1598,0,0,0,625,990,0,0,0,0,NULL,NULL),(69772,'~Summer~',5,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(69804,'Cargo Hold',2,2,37258,549,0,0,0,645,1009,0,0,0,0,NULL,0),(69900,'Temple of the Ocean',28,2,637,11,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(70043,'Freddy`s Nautical Surprise',0,3,8508,64,0,0,0,605,1020,0,0,0,0,NULL,0),(70044,'Chapel of Rest',6,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(70185,'',1,2,70043,549,0,0,0,605,1020,0,0,0,0,NULL,0),(70201,'Solar',1,2,69900,294,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(70202,'a walled garden with moon gate',1,2,30413,294,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(70254,'Study',2,2,69900,294,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(70270,'Sanctum',1,2,70254,294,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(70299,'~Casement Stays~',7,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(70340,'~Lilo the Famous~',8,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(70431,'Teregotha University Terarrium',2,2,29642,5,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(70449,'<font color=\"#a6a6a6\"><strong>Tomb of Margaret</strong></font>',5,2,66440,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70533,'<font color=\"#acacac\"><strong>Basement</strong></font>',1,2,68986,5,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70568,'Supplies',1,2,30447,785,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(70583,'<font color=\"#898989\"><strong>Safety Deposit Vault</strong></font>',2,2,26355,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70639,'~Nimmi~',9,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(70665,'<font color=\"#898989\"><strong>Storage</strong></font>',1,2,26775,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70791,'<font color=\"#898989\"><strong>Foreign Exchange</strong></font>',3,2,26355,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70899,'Chapel',2,2,66093,293,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70903,'<font color=\"#898989\"><strong>MINT</strong></font>',1,2,70665,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70920,'<font color=\"#898989\"><strong>Currency Backing Vault A</strong></font>',1,2,70903,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(70953,'<font color=\"#898989\"><strong>Currency Backing Vault B</strong></font>',2,2,70903,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(71100,'Various and Sundries',4,2,36745,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(71102,'Resident Fisher',0,3,639,64,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(71127,'Rumble',0,3,637,708,0,0,0,645,1009,0,0,0,0,NULL,0),(71149,'~October Skye~',10,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(71168,'Lair of Awesome',6,2,32587,784,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(71298,'Captains Quarters',1,2,71102,322,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(71302,'Free Boat',0,3,637,839,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(71315,'Crews Quarters',2,2,71102,543,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(71334,'Workshop',3,2,71102,543,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(71373,'Winterwind',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,0),(71385,'a raker',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,0),(71409,'Tool Shed',3,2,2926,270,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(71563,'Windwood',0,3,22258,64,0,0,0,593,979,0,0,0,0,NULL,0),(71575,'~Cosmo~',11,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(71615,'Sexy Secundus',0,3,40073,839,0,0,0,588,979,0,0,0,0,NULL,0),(71664,'Cellar',5,2,36745,293,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(71691,'T.F.V. Dusty Kiang',0,3,22258,155,0,0,0,593,979,0,0,0,0,NULL,0),(71710,'Ali`s Workshop',2,2,8239,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(71756,'Storage',1,2,36419,291,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(71757,'Storage Hold',1,2,71373,549,0,0,0,593,979,0,0,0,0,NULL,0),(71768,'Snow`s Den',2,2,71373,322,0,0,0,593,979,0,0,0,0,NULL,0),(71774,'Ɐ swirly black wrought iron cage with  blood red fur floor',0,3,71710,849,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(71901,'Storage',1,2,42165,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(71930,'Bedroom',2,2,42165,293,NULL,NULL,NULL,605,1020,0,0,0,0,NULL,0),(71937,'Stationary',1,2,70270,5,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(71990,'',1,2,71937,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(72022,'',1,2,71990,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(72045,'<font color=\"#7d7d7d\"><strong>Examination Room A</strong></font>',2,2,25241,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(72050,'',0,2,72022,785,NULL,NULL,NULL,645,1009,0,0,0,0,NULL,0),(72054,'Jewel of the Waves',0,3,2388,64,0,0,0,645,1009,0,0,0,0,NULL,0),(72061,'§°Master Chambers°§',1,2,40073,322,0,0,0,588,979,0,0,0,0,NULL,0),(72086,'§°Mini Hayhem°§',2,2,40073,543,0,0,0,588,979,0,0,0,0,NULL,0),(72096,'<font color=\"#7d7d7d\"><strong>Examination Room B</strong></font>',3,2,25241,785,NULL,NULL,NULL,619,1048,0,0,0,0,NULL,0),(72120,'',0,3,2388,328,0,0,0,645,1009,0,0,0,0,NULL,0),(72275,'~Amber~',12,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(72371,'Soul Reflection',0,3,1950,64,0,0,0,619,1048,0,0,0,0,NULL,0),(72381,'~Lavender~',13,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(72442,'I want to be happy.',0,3,638,275,0,0,0,605,1020,0,0,0,0,NULL,0),(72494,'Below Deck',1,2,67325,549,NULL,NULL,NULL,593,979,0,0,0,0,NULL,0),(72519,'T.S.V. Lost Raven',0,3,636,275,NULL,NULL,NULL,593,979,0,0,0,0,NULL,0),(72587,'T.S.V. Woolly Alpaca',0,3,640,289,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(72588,'~Walter Garyscott~',14,2,29069,785,NULL,NULL,NULL,625,990,0,0,0,0,NULL,0),(72593,'§°Workshop°§',0,2,40073,549,0,0,0,588,979,0,0,0,0,NULL,0);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `machines`
--

DROP TABLE IF EXISTS `machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `machines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(5) unsigned DEFAULT NULL,
  `requirements` text CHARACTER SET latin1,
  `result` tinytext CHARACTER SET latin1,
  `multiply` tinyint(1) DEFAULT NULL,
  `name` tinytext CHARACTER SET latin1,
  `max_participants` smallint(6) NOT NULL DEFAULT '0',
  `skill` tinyint(3) unsigned DEFAULT '0',
  `automatic` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1212 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `machines`
--

LOCK TABLES `machines` WRITE;
/*!40000 ALTER TABLE `machines` DISABLE KEYS */;
INSERT INTO `machines` VALUES (1,24,'raws:wheat flour>500,wood>30;days:1','46:500',1,'<CANTR REPLACE NAME=project_baking RAW=bread>',2,25,1),(2,24,'raws:rye flour>400,wood>30;days:1','47:400',1,'<CANTR REPLACE NAME=project_baking RAW=rye_bread>',2,25,1),(3,8,'days:1;ignorerawtools','25:3200',1,'<CANTR REPLACE NAME=project_harvesting RAW=potatoes>',4,1,0),(4,42,'days:1;ignorerawtools','124:1500',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=hematite>',4,20,0),(5,49,'raws:iron>420,coal>420,limestone>1400;days:1;tools:bellows','14:700',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=steel FUEL=coal>',6,24,0),(6,50,'raws:iron>980,coal>700,limestone>3500;days:1','14:1750',1,'Manufacture steel',0,0,0),(7,47,'days:1;ignorerawtools','18:400',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=nickel>',4,20,0),(8,50,'raws:iron>100,nickel>50,coal>70,chromium>30;days:1','49:170',1,'Manufacture stainless steel',0,0,0),(9,119,'raws:sand>600,wood>60,soda>200;days:1','252:300',1,'<CANTR REPLACE NAME=project_manu_glass RAW=glass_bars>',2,23,0),(10,52,'days:1;ignorerawtools','5:4000',1,'<CANTR REPLACE NAME=project_machine_digging RAW=sand>',4,18,0),(11,51,'days:1;ignorerawtools','22:240',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=magnesium>',4,20,0),(12,92,'days:1','17:350',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=copper>',4,20,0),(13,81,'days:1;ignorerawtools','16:500',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=coal>',4,20,0),(14,75,'days:1;ignorerawtools','35:1600',1,'<CANTR REPLACE NAME=project_harvesting RAW=carrots>',4,1,0),(15,120,'raws:wheat flour>600,coal>30;days:1','46:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=bread FUEL=coal>',2,25,1),(16,120,'raws:rye flour>500,coal>30;days:1','47:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rye_bread FUEL=coal>',2,25,1),(17,24,'raws:corn>750,wood>60;days:1','69:750',1,'<CANTR REPLACE NAME=project_popping RAW=popcorn IN=corn>',2,25,0),(18,24,'raws:pastry dough>400,sugar>150,wood>30;days:1','70:550',1,'<CANTR REPLACE NAME=project_baking RAW=cookies>',2,25,2),(19,120,'raws:pastry dough>480,sugar>180,coal>30;days:1','70:660',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cookies FUEL=coal>',2,25,2),(20,120,'raws:corn>800,coal>40;days:1','69:800',1,'<CANTR REPLACE NAME=project_popping_fueled RAW=popcorn IN=corn FUEL=coal>',2,25,0),(21,178,'raws:wheat>1800;days:1','72:1800',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=wheat>',2,25,0),(22,178,'raws:rye>1200;days:1','73:1200',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=rye>',2,25,0),(23,177,'raws:rye>400;days:1','73:400',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=rye>',2,25,0),(24,177,'raws:wheat>900;days:1','72:900',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=wheat>',2,25,0),(25,49,'raws:alumina>200,coal>100;days:1','74:100',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=aluminium FUEL=coal>',2,24,0),(26,186,'raws:bauxite>900,soda>150,coal>200;days:1;ignorerawtools','12:300',1,'<CANTR REPLACE NAME=project_refining RAW=alumina>',2,23,0),(27,24,'raws:rainbow trout>400,wood>40;days:1','78:400',1,'<CANTR REPLACE NAME=project_baking RAW=rainbow_trout>',2,25,0),(28,120,'raws:cod>500,coal>30;days:1','79:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cod FUEL=coal>',2,25,0),(29,120,'raws:rainbow trout>500,coal>30;days:1','78:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rainbow_trout FUEL=coal>',2,25,0),(30,24,'raws:cod>400,wood>40;days:1','79:400',1,'<CANTR REPLACE NAME=project_baking RAW=cod>',2,25,0),(31,187,'days:1;ignorerawtools','59:260',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=bauxite>',4,20,0),(32,167,'days:1;ignorerawtools','1:1250',1,'<CANTR REPLACE NAME=project_derrick_pumping RAW=oil>',4,20,0),(33,177,'raws:sorghum>750;days:1','86:750',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=sorghum>',2,25,0),(34,24,'raws:sorghum flour>600,wood>30;days:1','87:600',1,'<CANTR REPLACE NAME=project_baking RAW=sorghum_bread>',2,25,1),(35,120,'raws:sorghum flour>725,coal>30;days:1','87:725',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=sorghum_bread FUEL=coal>',2,25,1),(36,112,'raws:cotton yarn>600;days:1','91:590',1,'<CANTR REPLACE NAME=project_weaving RAW=cotton_cloth>',2,26,0),(37,72,'raws:cotton fibers>325;days:1','89:300',1,'<CANTR REPLACE NAME=project_spinning RAW=cotton_yarn>',2,26,0),(38,72,'raws:silk cocoons>250;days:1','93:240',1,'<CANTR REPLACE NAME=project_spinning RAW=silk_yarn>',2,26,0),(39,166,'raws:cotton yarn>500;days:1','137:500',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=cotton_yarn>',2,26,0),(40,72,'raws:wool>700;days:1','95:690',1,'<CANTR REPLACE NAME=project_spinning RAW=wool_yarn>',2,26,0),(41,112,'raws:silk yarn>650;days:1','97:645',1,'<CANTR REPLACE NAME=project_weaving RAW=silk_cloth>',2,26,0),(42,136,'raws:cotton>1200;days:1','98:1100',1,'<CANTR REPLACE NAME=project_ginning RAW=cotton>',4,26,0),(43,219,'raws:hide>1500,salt>500;days:1;tools:hide scraper','103:1050',1,'<CANTR REPLACE NAME=project_curing RAW=hide>',3,26,0),(44,178,'raws:sorghum>1500;days:1','86:1500',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=sorghum>',2,25,0),(45,120,'raws:meat>450,coal>25;days:1','107:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=coal>',2,25,0),(46,24,'raws:meat>450,wood>25;days:1','107:400',1,'<CANTR REPLACE NAME=project_cooking RAW=meat>',2,25,0),(47,147,'raws:meat>250,wood>150;days:1','109:225',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=wood>',2,25,0),(48,208,'raws:meat>600,wood>250;days:1','109:545',1,'<CANTR REPLACE NAME=project_grilling RAW=meat>',2,25,0),(49,147,'raws:wheat flour>200,wood>150;days:0.25;tools:cooking stone','110:200',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pancake FUEL=wood>',2,25,0),(50,117,'raws:wheat flour>1600;days:1;tools:wooden bowl,doughroller','111:1500',1,'<CANTR REPLACE NAME=project_making_dough RAW=pastry_dough>',2,25,0),(51,120,'raws:pastry dough>300,tomatos>500,coal>60;days:1;tools:knife','112:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pizza FUEL=coal>',2,25,0),(52,24,'raws:pastry dough>200,tomatos>300,wood>80;days:1;tools:knife','112:500',1,'<CANTR REPLACE NAME=project_baking RAW=pizza>',2,25,0),(53,95,'raws:rice>1000,seaweed>800,meat>300;days:1;tools:wooden bowl,knife,makisu','118:2100',1,'<CANTR REPLACE NAME=project_making_gen RAW=sushi_(meat)>',4,25,0),(54,95,'raws:rice>1000,seaweed>800,rainbow trout>300;days:1;tools:wooden bowl,knife,makisu','117:2100',1,'<CANTR REPLACE NAME=project_making_gen RAW=sushi_(fish)>',4,25,0),(55,95,'raws:rice>1000,seaweed>800,cod>300;days:1;tools:wooden bowl,knife,makisu','117:2100',1,'<CANTR REPLACE NAME=project_making_gen RAW=sushi_(fish)>',4,25,0),(56,216,'days:1;ignorerawtools','119:1800',1,'<CANTR REPLACE NAME=project_machine_digging RAW=clay>',4,18,0),(57,104,'raws:wood>300;days:1','120:100',1,'<CANTR REPLACE NAME=project_manu_charcoal RAW=charcoal>',2,0,0),(58,214,'raws:wood>900;days:1','120:425',1,'<CANTR REPLACE NAME=project_manu_charcoal RAW=charcoal>',3,0,0),(59,237,'raws:string>800;days:1','90:800',1,'<CANTR REPLACE NAME=project_manufacturing_rope RAW=thin_rope>',5,26,0),(60,49,'raws:alumina>200,charcoal>190;days:1','74:100',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=aluminium FUEL=charcoal>',2,24,0),(61,120,'raws:wheat flour>600,charcoal>55;days:1','46:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=bread FUEL=charcoal>',2,25,1),(62,120,'raws:cod>500,charcoal>55;days:1','79:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cod FUEL=charcoal>',2,25,0),(63,120,'raws:pastry dough>480,sugar>180,charcoal>55;days:1','70:660',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cookies FUEL=charcoal>',2,25,2),(64,120,'raws:pastry dough>300,tomatos>500,charcoal>55;days:1;tools:knife','112:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pizza FUEL=charcoal>',2,25,0),(65,120,'raws:rye flour>500,charcoal>55;days:1','47:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rye_bread FUEL=charcoal>',2,25,1),(66,120,'raws:sorghum flour>725,charcoal>55;days:1','87:725',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=sorghum_bread FUEL=charcoal>',2,25,1),(67,120,'raws:rainbow trout>500,charcoal>55;days:1','78:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rainbow_trout FUEL=charcoal>',2,25,0),(68,120,'raws:meat>450,charcoal>45;days:1','107:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=charcoal>',2,25,0),(69,120,'raws:corn>800,charcoal>70;days:1','69:800',1,'<CANTR REPLACE NAME=project_popping_fueled RAW=popcorn IN=corn FUEL=charcoal>',2,25,0),(70,49,'raws:iron ore>700,coal>420,limestone>1400;days:1;ignorerawtools','10:350',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=coal>',6,23,0),(71,112,'raws:hemp yarn>830;days:1','129:820',1,'<CANTR REPLACE NAME=project_weaving RAW=hemp_cloth>',2,26,0),(72,72,'raws:hemp>840;days:1','130:830',1,'<CANTR REPLACE NAME=project_spinning RAW=hemp_yarn>',2,26,0),(73,223,'raws:hematite>1500;days:1;tools:sledgehammer','134:1050',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=hematite TOOL=sledgehammer>',10,16,0),(74,166,'raws:hemp yarn>830;days:1','137:830',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=hemp_yarn>',2,26,0),(75,219,'raws:snakeskin>200,salt>100;days:1;tools:hide scraper','138:200',1,'<CANTR REPLACE NAME=project_treating_skin RAW=snakeskin>',3,26,0),(76,239,'raws:iron ore>840,coal>560,limestone>1750;days:1;ignorerawtools','10:350',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=coal>',3,23,0),(77,239,'raws:iron ore>840,charcoal>840,limestone>1750;days:1;ignorerawtools','10:350',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=charcoal>',3,23,0),(78,238,'raws:medium rope>400;days:1','136:226',1,'<CANTR REPLACE NAME=project_manufacturing_rope RAW=thick_rope>',7,26,0),(79,237,'raws:thin rope>800;days:1','135:400',1,'<CANTR REPLACE NAME=project_manufacturing_rope RAW=medium_rope>',5,26,0),(80,49,'raws:iron ore>700,charcoal>700,limestone>1400;days:1;ignorerawtools','10:350',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=charcoal>',6,23,0),(81,223,'raws:hematite>750;days:1;tools:hammer','134:525',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=hematite TOOL=hammer>',10,16,0),(82,223,'raws:magnetite>1500;days:1;tools:hammer','134:1080',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=magnetite TOOL=hammer>',10,16,0),(83,223,'raws:taconite>1000;days:1;tools:hammer','134:650',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=taconite TOOL=hammer>',10,16,0),(84,223,'raws:magnetite>3000;days:1;tools:sledgehammer','134:2160',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=magnetite TOOL=sledgehammer>',10,16,0),(85,223,'raws:taconite>2000;days:1;tools:sledgehammer','134:1300',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=taconite TOOL=sledgehammer>',10,16,0),(86,219,'raws:crocodile hide>100,salt>50;days:1;tools:hide scraper','143:100',1,'<CANTR REPLACE NAME=project_treating_skin RAW=crocodile_hide>',3,26,0),(87,0,'raws:meat>300,wood>175;days:1;tools:pot','107:280',0,'Cooking meat (pot) (wood)',1,25,0),(88,0,'raws:meat>600,wood>325;days:1;tools:pot','107:560',0,'Cooking meat (pot) (wood)',1,25,0),(89,147,'raws:meat>1000,wood>750;days:1;tools:pot','107:935',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=wood>',2,25,0),(90,208,'raws:meat>1000,wood>600;days:1;tools:pot','107:935',1,'<CANTR REPLACE NAME=project_cooking RAW=meat>',2,25,0),(91,0,'raws:meat>600,wood>300;days:1;tools:pot','107:560',1,'Cooking meat (pot)',1,25,0),(92,0,'raws:meat>300,wood>150;days:1;tools:pot','107:280',1,'Cooking meat (pot)',1,25,0),(93,266,'raws:hematite>750;days:1;tools:hammer','134:525',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=hematite TOOL=hammer>',10,16,0),(94,266,'raws:hematite>1500;days:1;tools:sledgehammer','134:1050',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=hematite TOOL=sledgehammer>',10,16,0),(95,104,'raws:clay>1100,water>25,coal>30;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_1_fueled RAW=brick IN1=clay FUEL=coal>',3,4,0),(96,104,'raws:mud>1100,reed>400,coal>30;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=reed FUEL=coal>',3,4,0),(97,104,'raws:mud>1100,reed>400,charcoal>60;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=reed FUEL=charcoal>',3,4,0),(98,243,'raws:water>1000,sand>4000;days:1','55:2000',1,'<CANTR REPLACE NAME=project_mixing_manu RAW=mud>',4,0,0),(99,242,'raws:water>300,sand>600;days:1;tools:bucket','55:500',0,'<CANTR REPLACE NAME=project_mixing_manu RAW=mud>',1,0,0),(100,247,'raws:water>300,sand>600;days:1;tools:bucket','55:500',0,'<CANTR REPLACE NAME=project_mixing_manu RAW=mud>',1,0,0),(101,119,'raws:sand>300,wood>30,soda>100;days:1','151:125',1,'<CANTR REPLACE NAME=project_manu_glass RAW=glass_beads>',2,23,0),(102,282,'raws:meat>500,wood>120;days:1','109:450',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=wood>',2,25,0),(103,292,'objects:note>1;days:0.25','8:0',0,'Burning note',1,0,0),(104,117,'raws:potatoes>1600,eggs>10,milk>200;days:1','296:1800',1,'<CANTR REPLACE NAME=project_mixing_food RAW=potato_salad>',4,25,0),(105,301,'raws:barley>400,blueberries>100;days:1;tools:mixing bowl','298:500',1,'<CANTR REPLACE NAME=project_mixing_food RAW=granola>',2,25,0),(106,117,'raws:barley>800,blueberries>200;days:1','298:1000',1,'<CANTR REPLACE NAME=project_mixing_food RAW=granola>',2,25,0),(107,24,'raws:meat>225,barley>225,wood>25;days:1','299:450',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=barley>',2,25,0),(108,117,'raws:spinage>400,tomatos>200,carrots>200;days:1;tools:knife','297:700',1,'<CANTR REPLACE NAME=project_tossing RAW=salad>',4,25,0),(109,462,'raws:meat>400,wood>30;days:1','159:275',1,'<CANTR REPLACE NAME=project_smoking RAW=meat>',2,25,0),(110,462,'raws:cod>400,wood>50;days:1','161:350',1,'<CANTR REPLACE NAME=project_smoking RAW=cod>',2,25,0),(111,462,'raws:salmon>400,wood>50;days:1','160:350',1,'<CANTR REPLACE NAME=project_smoking RAW=salmon>',2,25,0),(112,462,'raws:pike>400,wood>50;days:1','162:350',1,'<CANTR REPLACE NAME=project_smoking RAW=pike>',2,25,0),(113,462,'raws:rainbow trout>400,wood>50;days:1','163:350',1,'<CANTR REPLACE NAME=project_smoking RAW=rainbow_trout>',2,25,0),(114,304,'raws:arnica>100;days:1','167:100',1,'<CANTR REPLACE NAME=project_drying RAW=arnica>',1,25,1),(115,303,'raws:arnica>100;days:0.25','167:100',1,'Drying arnica',1,25,0),(116,304,'raws:basil>100;days:1','170:100',1,'<CANTR REPLACE NAME=project_drying RAW=basil>',1,25,1),(117,303,'raws:basil>100;days:0.25','170:100',1,'Drying basil',1,25,0),(118,304,'raws:clove>100;days:1','175:100',1,'<CANTR REPLACE NAME=project_drying RAW=clove>',1,25,1),(119,303,'raws:clove>100;days:0.25','175:100',1,'Drying clove',1,25,0),(120,304,'raws:dill>100;days:1','178:100',1,'<CANTR REPLACE NAME=project_drying RAW=dill>',1,25,1),(121,303,'raws:dill>100;days:0.25','178:100',1,'Drying dill',1,25,0),(122,304,'raws:garlic>100;days:1','182:100',1,'<CANTR REPLACE NAME=project_drying RAW=garlic>',1,25,1),(123,303,'raws:garlic>100;days:0.25','182:100',1,'Drying garlic',1,25,0),(124,304,'raws:ginger>100;days:1','185:100',1,'<CANTR REPLACE NAME=project_drying RAW=ginger>',1,25,1),(125,303,'raws:ginger>100;days:0.25','185:100',1,'Drying ginger',1,25,0),(126,304,'raws:hamamelis>100;days:1','188:100',1,'<CANTR REPLACE NAME=project_drying RAW=hamamelis>',1,25,1),(127,303,'raws:hamamelis>100;days:0.25','188:100',1,'Drying hamamelis',1,25,0),(128,304,'raws:lavender>100;days:1','191:100',1,'<CANTR REPLACE NAME=project_drying RAW=lavender>',1,25,1),(129,303,'raws:lavender>100;days:0.25','191:100',1,'Drying lavender',1,25,0),(130,304,'raws:myrrh>100;days:1','196:100',1,'<CANTR REPLACE NAME=project_drying RAW=myrrh>',1,25,1),(131,303,'raws:myrrh>100;days:0.25','196:100',1,'Drying myrrh',1,25,0),(132,304,'raws:peppermint>100;days:1','200:100',1,'<CANTR REPLACE NAME=project_drying RAW=peppermint>',1,25,1),(133,303,'raws:peppermint>100;days:0.25','200:100',1,'Drying peppermint',1,25,0),(134,304,'raws:sage>100;days:1','203:100',1,'<CANTR REPLACE NAME=project_drying RAW=sage>',1,25,1),(135,303,'raws:sage>100;days:0.25','203:100',1,'Drying sage',1,25,0),(136,304,'raws:tarragon>100;days:1','206:100',1,'<CANTR REPLACE NAME=project_drying RAW=tarragon>',1,25,1),(137,303,'raws:tarragon>100;days:0.25','206:100',1,'Drying tarragon',1,25,0),(138,304,'raws:thyme>100;days:1','209:100',1,'<CANTR REPLACE NAME=project_drying RAW=thyme>',1,25,1),(139,303,'raws:thyme>100;days:0.25','209:100',1,'Drying thyme',1,25,0),(140,304,'raws:white willow>100;days:1','212:100',1,'<CANTR REPLACE NAME=project_drying RAW=white_willow>',1,25,1),(141,303,'raws:white willow>100;days:0.25','212:100',1,'Drying white willow',1,25,0),(142,304,'raws:cod>200;days:1','313:100',1,'<CANTR REPLACE NAME=project_drying RAW=cod>',1,0,1),(143,279,'raws:iron ore>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=dried_dung>',2,23,0),(144,279,'raws:iron ore>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=fish_flour>',2,23,0),(145,279,'raws:iron ore>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=dried_dung>',2,23,0),(146,279,'raws:iron ore>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(147,279,'raws:iron ore>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=dried_dung>',2,23,0),(148,307,'raws:gas>200,wood>55;days:1','237:150',1,'<CANTR REPLACE NAME=project_purify_fueled RAW=gas FUEL=wood>',2,23,0),(149,279,'raws:iron ore>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=fish_flour>',2,23,0),(150,279,'raws:phosphorus>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_1 RAW=explosive_powder IN1=phosphorus>',2,23,0),(151,279,'raws:salt>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=dried_dung>',2,23,0),(152,279,'raws:salt>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=fish_flour>',2,23,0),(153,279,'raws:salt>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=dried_dung>',2,23,0),(154,279,'raws:salt>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=fish_flour>',2,23,0),(155,279,'raws:salt>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=dried_dung>',2,23,0),(156,279,'raws:salt>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=fish_flour>',2,23,0),(157,0,'raws:dry yarrow>100;days:0.5','236:25',1,'Crushing yarrow',1,0,0),(158,304,'raws:yarrow>100;days:1','235:100',1,'<CANTR REPLACE NAME=project_drying RAW=yarrow>',1,25,1),(159,303,'raws:yarrow>100;days:0.25','235:100',1,'Drying yarrow',1,25,0),(160,277,'raws:dry arnica>100,dry hamamelis>100,bergamot oil>50;days:1;tools:mortar and pestle','224:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(a) IN1=arnica IN2=hamamelis IN3=bergamot_oil>',2,25,0),(163,277,'raws:dry arnica>100,dry lavender>100,camphor oil>50;days:1;tools:mortar and pestle','225:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(b) IN1=arnica IN2=lavender IN3=camphor_oil>',2,25,0),(164,277,'raws:dry basil>100,dry dill>100,water>50;days:1;tools:mortar and pestle','226:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(c) IN1=basil IN2=dill IN3=water>',2,25,0),(165,277,'raws:dry ginger>100,dry hamamelis>100,water>50;days:1;tools:mortar and pestle','227:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(d) IN1=ginger IN2=hamamelis IN3=water>',2,25,0),(166,277,'raws:dry basil>100,dry hamamelis>100,patchouli oil>50;days:1;tools:mortar and pestle','228:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(e) IN1=basil IN2=hamamelis IN3=patchouli_oil>',2,25,0),(167,277,'raws:dry clove>100,dry sage>100,bergamot oil>50;days:1;tools:mortar and pestle','229:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(f) IN1=clove IN2=sage IN3=bergamot_oil>',2,25,0),(168,277,'raws:dry garlic>100,dry yarrow>100,lemon juice>50;days:1;tools:mortar and pestle','230:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(g) IN1=garlic IN2=yarrow IN3=lemons>',2,25,0),(169,277,'raws:dry peppermint>100,dry sage>100,lemon juice>50;days:1;tools:mortar and pestle','231:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(h) IN1=peppermint IN2=sage IN3=lemons>',2,25,0),(170,277,'raws:dry myrrh>100,dry white willow>100,eucalyptus oil>50;days:1;tools:mortar and pestle','232:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(i) IN1=myrrh IN2=white_willow IN3=eucalyptus_oil>',2,25,0),(171,277,'raws:dry tarragon>100,dry thyme>100,eucalyptus oil>50;days:1;tools:mortar and pestle','233:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(j) IN1=tarragon IN2=thyme IN3=eucalyptus_oil>',2,25,0),(172,147,'raws:barley>900,milk>100,wood>750;days:1;tools:pot','300:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=wood>',2,25,0),(173,0,'raws:barley>525,milk>75,dried dung>433;days:1;tools:pot','300:600',0,'Cooking porridge (pot) (dung)',2,25,0),(174,0,'raws:barley>270,milk>30,wood>140;days:1;tools:pot','300:300',1,'Cooking porridge (pot)',2,25,0),(175,308,'raws:barley>720,milk>80,propane>8;days:1','300:800',1,'<CANTR REPLACE NAME=project_cooking RAW=porridge>',2,25,0),(176,147,'raws:meat>500,barley>500,dried dung>1000;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=dried_dung>',2,25,0),(177,51,'raws:alcohol>150;days:1;ignorerawtools','22:960',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnesium FUEL=alcohol>',1,20,0),(178,216,'raws:propane>50;days:1;ignorerawtools','119:7200',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=clay FUEL=propane>',1,18,0),(179,251,'raws:rice>700,wood>125;days:1;tools:steamer','304:1050',1,'<CANTR REPLACE NAME=project_steaming_fueled RAW=rice FUEL=wood>',2,25,0),(180,120,'raws:potatoes>960,charcoal>45;days:1','305:960',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=potatoes FUEL=charcoal>',2,25,0),(181,120,'raws:rice>840,charcoal>45;days:1;tools:steamer','304:1260',1,'<CANTR REPLACE NAME=project_steaming_fueled RAW=rice FUEL=charcoal>',2,25,0),(182,0,'raws:barley>270,milk>30,wood>175;days:1;tools:pot','300:300',0,'Cooking porridge (pot) (wood)',2,25,0),(183,147,'raws:barley>900,milk>100,dried dung>1000;days:1;tools:pot','300:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=dried_dung>',2,25,0),(184,0,'raws:barley>525,milk>75,wood>260;days:1;tools:pot','300:600',1,'Cooking porridge (pot)',2,25,0),(185,120,'raws:barley>360,milk>40,charcoal>45;days:1','300:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=charcoal>',2,25,0),(186,81,'raws:propane>50;days:1;ignorerawtools','16:2000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=coal FUEL=propane>',1,20,0),(187,208,'raws:meat>500,barley>500,wood>600;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=barley>',2,25,0),(188,147,'raws:meat>500,potatoes>300,carrots>200,dried dung>1000;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=dried_dung>',2,25,0),(189,24,'raws:rice>700,wood>25;days:1;tools:steamer','304:1050',1,'<CANTR REPLACE NAME=project_steaming RAW=rice>',2,25,0),(190,251,'raws:potatoes>800,dried dung>166;days:1','305:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=potatoes FUEL=dried_dung>',2,25,0),(191,251,'raws:rice>700,dried dung>166;days:1;tools:steamer','304:1050',1,'<CANTR REPLACE NAME=project_steaming_fueled RAW=rice FUEL=dried_dung>',2,25,0),(192,223,'objects:copper coin>4;days:1;tools:hammer','17:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=copper OBJECT=copper_coin>',2,16,0),(193,208,'raws:barley>900,milk>100,wood>600;days:1;tools:pot','300:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=porridge>',2,25,0),(194,120,'raws:barley>360,milk>40,coal>25;days:1','300:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=coal>',2,25,0),(195,42,'raws:propane>50;days:1;ignorerawtools','124:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=hematite FUEL=propane>',1,20,0),(196,92,'raws:propane>50;days:1;ignorerawtools','17:1400',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=copper FUEL=propane>',1,20,0),(197,147,'raws:meat>500,potatoes>300,carrots>200,wood>750;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=wood>',2,25,0),(198,52,'raws:biodiesel>100;days:1;ignorerawtools','5:16000',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=sand FUEL=biodiesel>',1,18,0),(199,251,'raws:potatoes>800,wood>125;days:1','305:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=potatoes FUEL=wood>',2,25,0),(200,308,'raws:potatoes>1600,propane>8;days:1','305:1600',1,'<CANTR REPLACE NAME=project_baking RAW=potatoes>',2,25,0),(201,308,'raws:rice>1400,propane>8;days:1;tools:steamer','304:2100',1,'<CANTR REPLACE NAME=project_steaming RAW=rice>',2,25,0),(202,308,'raws:wheat flour>1000,propane>10;days:1','46:1000',1,'<CANTR REPLACE NAME=project_baking RAW=bread>',2,25,1),(203,308,'raws:cod>800,propane>12;days:1','79:800',1,'<CANTR REPLACE NAME=project_baking RAW=cod>',2,25,0),(204,308,'raws:pastry dough>800,sugar>300,propane>20;days:1','70:1100',1,'<CANTR REPLACE NAME=project_baking RAW=cookies>',2,25,2),(205,308,'raws:pastry dough>600,tomatos>1000,propane>20;days:1;tools:knife','112:1400',1,'<CANTR REPLACE NAME=project_baking RAW=pizza>',2,25,0),(206,308,'raws:rye flour>800,propane>20;days:1','47:800',1,'<CANTR REPLACE NAME=project_baking RAW=rye_bread>',2,25,1),(207,308,'raws:sorghum flour>1200,propane>20;days:1','87:1200',1,'<CANTR REPLACE NAME=project_baking RAW=sorghum_bread>',2,25,1),(208,308,'raws:rainbow trout>800,propane>12;days:1','78:800',1,'<CANTR REPLACE NAME=project_baking RAW=rainbow_trout>',2,25,0),(209,0,'raws:barley>525,milk>75,wood>325;days:1;tools:pot','300:600',0,'Cooking porridge (pot) (wood)',2,25,0),(210,0,'raws:barley>270,milk>30,dried dung>233;days:1;tools:small pot','300:300',0,'Cooking porridge (small pot) (dung)',2,25,0),(211,24,'raws:barley>360,milk>40,wood>25;days:1','300:400',1,'<CANTR REPLACE NAME=project_cooking RAW=porridge>',2,25,0),(212,147,'raws:meat>500,barley>500,wood>750;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=wood>',2,25,0),(213,51,'raws:biodiesel>100;days:1;ignorerawtools','22:960',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnesium FUEL=biodiesel>',1,20,0),(214,42,'raws:biodiesel>100;days:1;ignorerawtools','124:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=hematite FUEL=biodiesel>',1,20,0),(215,187,'raws:propane>50;days:1;ignorerawtools','59:1040',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=bauxite FUEL=propane>',1,20,0),(216,24,'raws:potatoes>800,wood>25;days:1','305:800',1,'<CANTR REPLACE NAME=project_baking RAW=potatoes>',2,25,0),(217,120,'raws:potatoes>960,coal>25;days:1','305:960',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=potatoes FUEL=coal>',2,25,0),(218,120,'raws:rice>840,coal>25;days:1;tools:steamer','304:1260',1,'<CANTR REPLACE NAME=project_steaming_fueled RAW=rice FUEL=coal>',2,25,0),(219,308,'raws:meat>900,propane>16;days:1','107:800',1,'<CANTR REPLACE NAME=project_cooking RAW=meat>',2,25,0),(220,308,'raws:corn>1500,propane>40;days:1','69:1500',1,'<CANTR REPLACE NAME=project_popping RAW=popcorn IN=corn>',2,25,0),(221,304,'raws:tea leaves>100;days:1','248:100',1,'<CANTR REPLACE NAME=project_drying RAW=tea_leaves>',1,25,1),(222,0,'raws:roasted coffee>100;days:0.5','246:75',1,'Grinding coffee',1,25,0),(223,0,'raws:dry tea>100;days:0.5','247:50',1,'Crushing tea',1,0,0),(224,239,'raws:iron ore>840,propane>175,limestone>1750;days:1;ignorerawtools','10:525',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=propane>',3,23,0),(225,147,'raws:beeswax>100,resin>40,wood>20;days:1','259:80',1,'<CANTR REPLACE NAME=project_forming RAW=wax_mixture>',1,0,0),(226,24,'raws:dry tea>300,wood>25,water>450;days:1;tools:mortar and pestle','250:750',1,'<CANTR REPLACE NAME=project_brewing RAW=tea>',1,25,0),(227,24,'raws:ground coffee>200,wood>25,water>300;days:1','249:500',1,'<CANTR REPLACE NAME=project_brewing RAW=coffee>',1,25,0),(228,49,'raws:alumina>250,propane>45;days:1','74:156',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=aluminium FUEL=propane>',2,24,0),(229,49,'raws:iron ore>700,propane>140,limestone>1400;days:1;ignorerawtools','10:525',1,'<CANTR REPLACE NAME=project_refining_fueled RAW=iron IN=iron_ore FUEL=propane>',6,23,0),(230,49,'raws:iron>525,propane>175,limestone>1750;days:1;tools:bellows','14:963',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=steel FUEL=propane>',6,24,0),(231,120,'raws:ground coffee>250,coal>25,water>350;days:1','249:600',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=coffee FUEL=coal>',1,25,0),(232,120,'raws:ground coffee>250,charcoal>40,water>350;days:1','249:600',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=coffee FUEL=charcoal>',1,25,0),(233,120,'raws:dry tea>350,coal>25,water>500;days:1;tools:mortar and pestle','250:850',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=tea FUEL=coal>',1,25,0),(234,120,'raws:dry tea>350,charcoal>40,water>500;days:1;tools:mortar and pestle','250:850',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=tea FUEL=charcoal>',1,25,0),(235,308,'raws:ground coffee>300,propane>16,water>400;days:1','249:700',1,'<CANTR REPLACE NAME=project_brewing RAW=coffee>',1,25,0),(236,308,'raws:dry tea>400,propane>16,water>550;days:1;tools:mortar and pestle','250:950',1,'<CANTR REPLACE NAME=project_brewing RAW=tea>',1,25,0),(237,0,'raws:olives>1000;days:1','239:800',1,'Grinding olives',2,25,0),(238,759,'raws:propane>50;days:1;ignorerawtools','142:12000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=taconite FUEL=propane>',1,20,0),(239,47,'raws:propane>50;days:1;ignorerawtools','18:1600',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=nickel FUEL=propane>',1,20,0),(240,214,'raws:glass bars>800;days:1','253:600',1,'<CANTR REPLACE NAME=project_manu_glass RAW=window_glass>',3,23,0),(241,569,'days:1;tools:bucket','6:300',1,'<CANTR REPLACE NAME=project_pump_pumping RAW=water>',2,20,0),(242,330,'days:1;ignorerawtools','38:2800',1,'<CANTR REPLACE NAME=project_harvesting RAW=rice>',4,1,0),(243,301,'raws:wax mixture>80,bitumen>40;days:1;tools:mixing bowl','258:60',1,'<CANTR REPLACE NAME=project_mixing_manu RAW=sealing_wax>',1,0,0),(244,208,'raws:beeswax>100,resin>40,wood>20;days:1','259:80',1,'<CANTR REPLACE NAME=project_forming RAW=wax_mixture>',1,0,0),(245,374,'raws:cotton yarn>50;days:1','137:50',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=cotton_yarn>',2,26,0),(246,374,'raws:hemp yarn>60;days:1','137:60',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=hemp_yarn>',2,26,0),(247,373,'raws:cotton>120;days:1','98:110',1,'<CANTR REPLACE NAME=project_ginning RAW=cotton>',2,26,0),(248,166,'raws:silk yarn>400;days:1','137:400',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=silk_yarn>',2,26,0),(249,374,'raws:silk yarn>40;days:1','137:40',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=silk_yarn>',2,26,0),(252,117,'raws:steamed rice>2600,salt>40;days:1','322:2600',1,'<CANTR REPLACE NAME=project_rolling RAW=rice_balls>',4,25,0),(253,0,'raws:water>300,coffee cherries>500;days:1','264:500',0,'Fermenting coffee cherries',1,25,0),(254,0,'raws:water>300,coffee cherries>500;days:1','264:500',0,'Fermenting coffee cherries',1,25,0),(255,304,'raws:grapes>200;days:1','339:175',1,'<CANTR REPLACE NAME=project_drying RAW=grapes>',0,0,1),(256,117,'raws:coffee beans>1000;days:1;tools:mortar and pestle','246:1000',1,'<CANTR REPLACE NAME=project_grinding_coffee RAW=ground_coffee_beans IN=coffee_beans>',1,25,0),(257,49,'raws:copper>280,tin>40,charcoal>240;days:1','267:200',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=bronze FUEL=charcoal>',6,24,0),(258,239,'raws:copper>350,tin>50,charcoal>300;days:1','267:200',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=bronze FUEL=charcoal>',6,24,0),(259,758,'raws:biodiesel>100;days:1;ignorerawtools','141:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnetite FUEL=biodiesel>',1,20,0),(260,628,'days:1;location_state:sailing_floating;ignorerawtools','154:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=salmon>',1,17,0),(261,307,'raws:gas>200,coal>15;days:1','237:150',1,'<CANTR REPLACE NAME=project_purify_fueled RAW=gas FUEL=coal>',2,23,0),(262,299,'raws:cod>400,salt>50;days:1','269:350',1,'<CANTR REPLACE NAME=project_salting RAW=cod>',2,25,0),(263,299,'raws:meat>400,salt>50;days:1','273:350',1,'<CANTR REPLACE NAME=project_salting RAW=meat>',2,25,0),(264,299,'raws:pike>400,salt>50;days:1','270:350',1,'<CANTR REPLACE NAME=project_salting RAW=pike>',2,25,0),(265,299,'raws:salmon>400,salt>50;days:1','271:350',1,'<CANTR REPLACE NAME=project_salting RAW=salmon>',2,25,0),(266,299,'raws:rainbow trout>400,salt>50;days:1','272:350',1,'<CANTR REPLACE NAME=project_salting RAW=rainbow_trout>',2,25,0),(267,304,'raws:meat>120;days:1','274:30',1,'<CANTR REPLACE NAME=project_drying RAW=meat>',1,25,2),(268,24,'raws:meat>200,potatoes>120,carrots>80,wood>25;days:1;tools:knife','299:400',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=vegetables>',2,25,0),(269,514,'days:1;ignorerawtools','4:3750',1,'<CANTR REPLACE NAME=project_drilling_quarry RAW=stone>',2,20,0),(270,208,'raws:eggs>60,wood>40;days:1;tools:cooking stone','289:48',1,'<CANTR REPLACE NAME=project_scrambling RAW=eggs>',2,25,0),(271,75,'raws:propane>50;days:1;ignorerawtools','35:6400',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=carrots FUEL=propane>',1,1,0),(272,875,'raws:cotton fibers>110;days:1;tools:drop spindle','89:100',1,'<CANTR REPLACE NAME=project_spinning RAW=cotton_yarn>',0,26,0),(273,208,'raws:eggs>90,wood>54;days:1;tools:pot','290:90',1,'<CANTR REPLACE NAME=project_boiling RAW=eggs>',2,25,0),(274,147,'raws:eggs>90,wood>54;days:1;tools:pot','290:90',1,'<CANTR REPLACE NAME=project_boiling_fueled RAW=eggs FUEL=wood>',2,25,0),(275,75,'raws:alcohol>150;days:1;ignorerawtools','35:6400',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=carrots FUEL=alcohol>',1,1,0),(276,0,'raws:eggs>20,wood>14;days:1;tools:small pot','290:20',1,'Boiling eggs (small pot) (wood)',1,25,0),(277,147,'raws:eggs>60,wood>40;days:1;tools:cooking stone','289:48',1,'<CANTR REPLACE NAME=project_scrambling_fueled RAW=eggs FUEL=wood>',2,25,0),(278,242,'raws:milk>400,sugar>300;days:1;tools:ice cream crank,bucket','291:700',1,'<CANTR REPLACE NAME=project_making_icecream RAW=ice_cream>',2,25,0),(279,147,'raws:milk>600,wood>300;days:1;tools:pot','292:550',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=wood>',2,25,0),(280,24,'raws:milk>300,wood>20;days:1','292:250',1,'<CANTR REPLACE NAME=project_curdling RAW=milk>',2,25,0),(281,519,'raws:cheese curds>500;days:1','293:500',1,'<CANTR REPLACE NAME=project_making_cheese RAW=cheese>',2,25,1),(282,208,'raws:milk>600,wood>300;days:1;tools:pot','292:550',1,'<CANTR REPLACE NAME=project_curdling RAW=milk>',2,25,0),(283,247,'raws:milk>400,sugar>300;days:1;tools:ice cream crank,bucket','291:700',1,'<CANTR REPLACE NAME=project_making_icecream RAW=ice_cream>',2,25,0),(284,251,'raws:wheat flour>500,wood>150;days:1','46:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=bread FUEL=wood>',2,25,0),(285,251,'raws:cod>400,wood>200;days:1','79:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cod FUEL=wood>',2,25,0),(286,251,'raws:rye flour>400,wood>150;days:1','47:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rye_bread FUEL=wood>',2,25,0),(287,251,'raws:sorghum flour>600,wood>150;days:1','87:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=sorghum_bread FUEL=wood>',2,25,0),(288,251,'raws:rainbow trout>400,wood>200;days:1','78:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rainbow_trout FUEL=wood>',2,25,0),(289,251,'raws:meat>500,wood>125;days:1','107:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=wood>',2,25,0),(290,251,'raws:milk>300,wood>100;days:1','292:250',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=wood>',2,25,0),(291,251,'raws:corn>750,wood>300;days:1','69:750',1,'<CANTR REPLACE NAME=project_popping RAW=popcorn IN=corn FUEL=wood>',2,25,0),(292,251,'raws:fresh dung>1000;days:1','295:800',1,'<CANTR REPLACE NAME=project_drying RAW=fresh_dung>',2,25,1),(293,307,'raws:oil>200,coal>20;days:1','257:130',1,'<CANTR REPLACE NAME=project_purify_fueled RAW=bitumen FUEL=coal>',2,23,0),(294,307,'raws:oil>200,wood>75;days:1','257:130',1,'<CANTR REPLACE NAME=project_purify_fueled RAW=bitumen FUEL=wood>',2,23,0),(295,147,'raws:fresh dung>1000;days:1','295:800',1,'<CANTR REPLACE NAME=project_drying RAW=fresh_dung>',2,25,1),(296,147,'raws:meat>1000,dried dung>1000;days:1;tools:pot','107:935',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=dried_dung>',2,25,0),(297,0,'raws:meat>600,dried dung>433;days:1;tools:pot','107:560',0,'Cooking meat (pot) (dung)',1,25,0),(298,0,'raws:meat>300,dried dung>233;days:1;tools:small pot','107:280',0,'Cooking meat (small pot) (dung)',1,25,0),(299,147,'raws:milk>600,dried dung>400;days:1;tools:pot','292:550',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=dried_dung>',2,25,0),(300,147,'raws:meat>250,dried dung>200;days:1','109:225',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=dried_dung>',2,25,0),(301,147,'raws:eggs>60,dried dung>53;days:1;tools:cooking stone','289:48',1,'<CANTR REPLACE NAME=project_scrambling_fueled RAW=eggs FUEL=dried_dung>',2,25,0),(302,147,'raws:eggs>90,dried dung>72;days:1;tools:pot','290:90',1,'<CANTR REPLACE NAME=project_boiling_fueled RAW=eggs FUEL=dried_dung>',2,25,0),(303,875,'raws:silk cocoons>85;days:1;tools:drop spindle','93:80',1,'<CANTR REPLACE NAME=project_spinning RAW=silk_yarn>',0,26,0),(304,330,'raws:biodiesel>100;days:1;ignorerawtools','38:11200',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=rice FUEL=biodiesel>',1,1,0),(305,147,'raws:wheat flour>200,dried dung>200;days:0.25;tools:cooking stone','110:200',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pancake FUEL=dried_dung>',2,25,0),(306,282,'raws:meat>500,dried dung>133;days:1','109:450',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=dried_dung>',2,25,0),(307,251,'raws:wheat flour>500,dried dung>200;days:1','46:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=bread FUEL=dried_dung>',2,25,0),(308,251,'raws:cod>400,dried dung>266;days:1','79:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cod FUEL=dried_dung>',2,25,0),(309,251,'raws:rye flour>400,dried dung>200;days:1','47:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rye_bread FUEL=dried_dung>',2,25,0),(310,251,'raws:sorghum flour>600,dried dung>200;days:1','87:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=sorghum_bread FUEL=dried_dung>',2,25,0),(311,251,'raws:rainbow trout>400,dried dung>266;days:1','78:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=rainbow_trout FUEL=dried_dung>',2,25,0),(312,251,'raws:meat>500,dried dung>166;days:1','107:400',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=dried_dung>',2,25,0),(313,251,'raws:milk>300,dried dung>133;days:1','292:250',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=dried_dung>',2,25,0),(314,251,'raws:corn>750,dried dung>400;days:1','69:750',1,'<CANTR REPLACE NAME=project_popping RAW=popcorn IN=corn FUEL=dried_dung>',2,25,0),(315,301,'raws:potatoes>800,eggs>5,milk>100;days:1;tools:mixing bowl','296:900',1,'<CANTR REPLACE NAME=project_mixing_food RAW=potato_salad>',2,25,0),(316,301,'raws:spinage>200,tomatos>100,carrots>100;days:1;tools:knife,mixing bowl','297:350',1,'<CANTR REPLACE NAME=project_tossing RAW=salad>',2,25,0),(317,538,'days:1;ignorerawtools','48:4400',1,'<CANTR REPLACE NAME=project_drilling_quarry RAW=limestone>',2,20,0),(318,117,'raws:lemons>180;days:1','194:90',1,'<CANTR REPLACE NAME=project_squeezing RAW=lemon_juice>',2,25,0),(319,277,'raws:lemons>180;days:1','194:90',1,'<CANTR REPLACE NAME=project_squeezing RAW=lemon_juice>',2,25,0),(320,282,'raws:fresh dung>1000;days:1','295:800',1,'<CANTR REPLACE NAME=project_drying RAW=fresh_dung>',2,25,1),(321,308,'raws:milk>600,propane>4;days:1','292:500',1,'<CANTR REPLACE NAME=project_curdling RAW=milk>',2,25,0),(322,120,'raws:milk>300,coal>20;days:1','292:250',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=coal>',2,25,0),(323,120,'raws:milk>300,charcoal>35;days:1','292:250',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=charcoal>',2,25,0),(324,251,'raws:meat>225,barley>225,wood>125;days:1','299:450',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=wood>',2,25,0),(325,120,'raws:meat>225,barley>225,coal>25;days:1','299:450',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=coal>',2,25,0),(326,120,'raws:meat>225,barley>225,charcoal>45;days:1','299:450',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=charcoal>',2,25,0),(327,120,'raws:meat>200,potatoes>120,carrots>80,coal>25;days:1;tools:knife','299:400',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=coal>',2,25,0),(328,120,'raws:meat>200,potatoes>120,carrots>80,charcoal>45;days:1;tools:knife','299:400',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=charcoal>',2,25,0),(329,308,'raws:meat>500,barley>500,propane>8;days:1','299:900',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=barley>',2,25,0),(330,308,'raws:meat>400,potatoes>240,carrots>160,propane>8;days:1;tools:knife','299:800',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=vegetables>',2,25,0),(331,251,'raws:meat>200,potatoes>120,carrots>80,wood>125;days:1;tools:knife','299:400',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=wood>',2,25,0),(332,251,'raws:meat>225,barley>225,dried dung>167;days:1','299:450',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=dried_dung>',2,25,0),(333,251,'raws:meat>200,potatoes>120,carrots>80,dried dung>167;days:1;tools:knife','299:400',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=dried_dung>',2,25,0),(334,52,'raws:propane>50;days:1;ignorerawtools','5:16000',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=sand FUEL=propane>',1,18,0),(335,208,'raws:meat>500,potatoes>300,carrots>200,wood>600;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2 IN1=meat IN2=vegetables>',2,25,0),(336,81,'raws:biodiesel>100;days:1;ignorerawtools','16:2000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=coal FUEL=biodiesel>',1,20,0),(337,216,'raws:alcohol>150;days:1;ignorerawtools','119:7200',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=clay FUEL=alcohol>',1,18,0),(338,266,'raws:magnetite>1500;days:1;tools:hammer','134:1080',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=magnetite TOOL=hammer>',10,16,0),(339,266,'raws:magnetite>3000;days:1;tools:sledgehammer','134:2160',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=magnetite TOOL=sledgehammer>',10,16,0),(340,266,'raws:taconite>1000;days:1;tools:hammer','134:650',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=taconite TOOL=hammer>',10,16,0),(341,266,'raws:taconite>2000;days:1;tools:sledgehammer','134:1300',1,'<CANTR REPLACE NAME=project_crushing_tool RAW=taconite TOOL=sledgehammer>',10,16,0),(342,112,'raws:wool yarn>400;days:1','301:390',1,'<CANTR REPLACE NAME=project_weaving RAW=wool_cloth>',2,26,0),(343,299,'raws:cucumbers>1000,salt>50;days:1','276:750',1,'<CANTR REPLACE NAME=project_making_sour RAW=pickles IN=cucumbers>',2,25,0),(344,575,'raws:timber>2000;days:1;tools:bucket,shovel,froe','1:100',1,'<CANTR REPLACE NAME=project_tarpit_making RAW=oil>',2,23,0),(345,299,'raws:asparagus>1000,salt>50;days:1','302:750',1,'<CANTR REPLACE NAME=project_salting RAW=asparagus>',2,25,0),(346,299,'raws:carrots>1000,salt>50;days:1','303:750',1,'<CANTR REPLACE NAME=project_salting RAW=carrots>',2,25,0),(347,223,'objects:silver coin>4;days:1;tools:hammer','15:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=silver OBJECT=silver_coin>',2,16,0),(348,304,'raws:potatoes>1600;days:1','317:800',1,'<CANTR REPLACE NAME=project_drying RAW=potatoes>',2,25,0),(349,304,'raws:rainbow trout>200;days:1','314:100',1,'<CANTR REPLACE NAME=project_drying RAW=rainbow_trout>',1,0,1),(350,117,'raws:baked potatoes>2000,salt>40;days:1','320:2000',1,'<CANTR REPLACE NAME=project_making_gen RAW=fries>',4,25,0),(351,223,'objects:steel coin>4;days:1;tools:hammer','14:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=steel OBJECT=steel_coin>',2,16,0),(352,24,'raws:pastry dough>1000,meat>1000,wood>80;days:1','319:2000',1,'<CANTR REPLACE NAME=project_baking RAW=meat_pie>',2,25,0),(353,147,'raws:potatoes>1600,milk>400,wood>600;days:1;tools:pot','306:2000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mashed_potatoes FUEL=wood>',2,25,0),(354,304,'raws:rice>1400;days:1','318:700',1,'<CANTR REPLACE NAME=project_drying RAW=rice>',2,25,0),(355,223,'objects:iron coin>4;days:1;tools:hammer','10:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=iron OBJECT=iron_coin>',2,16,0),(356,104,'raws:mud>1100,grass>800,coal>30;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=grass FUEL=coal>',3,4,0),(357,104,'raws:mud>1100,grass>800,charcoal>60;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=grass FUEL=charcoal>',3,4,0),(358,147,'raws:asparagus>550,wood>300;days:1','315:550',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=wood>',2,25,0),(359,223,'objects:nickel coin>4;days:1;tools:hammer','18:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=nickel OBJECT=nickel_coin>',2,16,0),(360,223,'objects:golden coin>4;days:1;tools:hammer','2:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=gold OBJECT=golden_coin>',2,16,0),(361,266,'objects:golden coin>4;days:1;tools:hammer','2:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=gold OBJECT=golden_coin>',2,16,0),(362,266,'objects:iron coin>4;days:1;tools:hammer','10:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=iron OBJECT=iron_coin>',2,16,0),(363,266,'objects:nickel coin>4;days:1;tools:hammer','18:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=nickel OBJECT=nickel_coin>',2,16,0),(364,266,'objects:silver coin>4;days:1;tools:hammer','15:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=silver OBJECT=silver_coin>',2,16,0),(365,266,'objects:steel coin>4;days:1;tools:hammer','14:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=steel OBJECT=steel_coin>',2,16,0),(366,266,'objects:copper coin>4;days:1;tools:hammer','17:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=copper OBJECT=copper_coin>',2,16,0),(367,147,'raws:potatoes>1600,milk>400,dried dung>800;days:1;tools:pot','306:2000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mashed_potatoes FUEL=dried_dung>',2,25,0),(368,0,'raws:potatoes>960,milk>240,wood>300;days:1;tools:pot','306:1200',0,'Cooking mashed potatoes (pot) (wood)',2,25,0),(369,0,'raws:potatoes>500,milk>120,wood>150;days:1;tools:small pot','306:620',0,'Cooking mashed potatoes (small pot) (wood)',2,25,0),(370,0,'raws:potatoes>960,milk>240,dried dung>400;days:1;tools:pot','306:1200',0,'Cooking mashed potatoes (pot) (dung)',2,25,0),(371,0,'raws:potatoes>500,milk>120,dried dung>200;days:1;tools:small pot','306:620',0,'Cooking mashed potatoes (small pot) (dung)',2,25,0),(372,208,'raws:potatoes>1600,milk>400,wood>480;days:1;tools:pot','306:2000',1,'<CANTR REPLACE NAME=project_cooking RAW=mashed_potatoes>',2,25,0),(373,0,'raws:potatoes>960,milk>240,wood>240;days:1;tools:pot','306:1200',1,'Cooking mashed potatoes (pot)',2,25,0),(374,0,'raws:potatoes>500,milk>120,wood>120;days:1;tools:small pot','306:620',1,'Cooking mashed potatoes (small pot)',2,25,0),(375,147,'raws:asparagus>550,dried dung>400;days:1','315:550',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=dried_dung>',2,25,0),(376,147,'raws:carrots>800,wood>300;days:1','316:800',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=wood>',2,25,0),(377,147,'raws:carrots>800,dried dung>400;days:1','316:800',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=dried_dung>',2,25,0),(378,208,'raws:asparagus>550,wood>200;days:1','315:550',1,'<CANTR REPLACE NAME=project_grilling RAW=asparagus>',2,25,0),(379,208,'raws:carrots>800,wood>200;days:1','316:800',1,'<CANTR REPLACE NAME=project_roasting RAW=carrots>',2,25,0),(380,282,'raws:asparagus>825,wood>300;days:1','315:825',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=wood>',2,25,0),(381,282,'raws:asparagus>825,dried dung>400;days:1','315:825',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=dried_dung>',2,25,0),(382,282,'raws:carrots>1200,wood>300;days:1','316:1200',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=wood>',2,25,0),(383,282,'raws:carrots>1200,dried dung>400;days:1','316:1200',0,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=dried_dung>',2,25,0),(384,24,'raws:pike>400,wood>40;days:1','157:400',1,'<CANTR REPLACE NAME=project_baking RAW=pike>',2,25,0),(385,24,'raws:salmon>400,wood>40;days:1','156:400',1,'<CANTR REPLACE NAME=project_baking RAW=salmon>',2,25,0),(386,251,'raws:pike>400,wood>200;days:1','157:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pike FUEL=wood>',2,25,0),(387,251,'raws:pike>400,dried dung>266;days:1','157:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pike FUEL=dried_dung>',2,25,0),(388,251,'raws:salmon>400,wood>200;days:1','156:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=salmon FUEL=wood>',2,25,0),(389,251,'raws:salmon>400,dried dung>266;days:1','156:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=salmon FUEL=dried_dung>',2,25,0),(390,120,'raws:pike>500,charcoal>55;days:1','157:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pike FUEL=charcoal>',2,25,0),(391,120,'raws:pike>500,coal>30;days:1','157:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pike FUEL=coal>',2,25,0),(392,120,'raws:salmon>500,charcoal>55;days:1','156:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=salmon FUEL=charcoal>',2,25,0),(393,120,'raws:salmon>500,coal>30;days:1','156:500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=salmon FUEL=coal>',2,25,0),(394,308,'raws:pike>800,propane>12;days:1','157:800',1,'<CANTR REPLACE NAME=project_baking RAW=pike>',2,25,0),(395,308,'raws:salmon>800,propane>12;days:1','156:800',1,'<CANTR REPLACE NAME=project_baking RAW=salmon>',2,25,0),(396,120,'raws:pastry dough>1500,meat>1500,charcoal>55;days:1','319:3000',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=meat_pie FUEL=charcoal>',2,25,0),(397,120,'raws:pastry dough>1500,meat>1500,coal>60;days:1','319:3000',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=meat_pie FUEL=coal>',2,25,0),(398,308,'raws:pastry dough>1800,meat>1800,propane>20;days:1','319:3600',1,'<CANTR REPLACE NAME=project_baking RAW=meat_pie>',2,25,0),(399,301,'raws:pineapples>100,coconuts>50,papayas>150;days:1;tools:knife,mixing bowl','333:300',1,'<CANTR REPLACE NAME=project_mixing_food RAW=fruit_salad>',2,25,0),(400,277,'raws:dry garlic>100,dry tea>100,water>50;days:1;tools:mortar and pestle','327:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(k) IN1=garlic IN2=dry_tea IN3=water>',2,25,0),(401,277,'raws:dry thyme>100,dry myrrh>100,aloe vera>50;days:1;tools:mortar and pestle','328:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(l) IN1=thyme IN2=myrrh IN3=aloe_vera>',2,25,0),(402,277,'raws:dry ginger>100,dry dill>100,water>50;days:1;tools:mortar and pestle','329:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(m) IN1=ginger IN2=dill IN3=water>',2,25,0),(403,277,'raws:dry lavender>100,dry garlic>100,aloe vera>50;days:1;tools:mortar and pestle','330:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(n) IN1=lavender IN2=garlic IN3=aloe_vera>',2,25,0),(404,573,'raws:aloe vera>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','164:150',1,'<CANTR REPLACE NAME=project_growing RAW=aloe_vera>',2,21,2),(405,573,'raws:fresh dung>200;days:1;tools:shovel','331:100',1,'<CANTR REPLACE NAME=project_composting_1 IN1=fresh_dung>',2,21,0),(406,573,'raws:fresh dung>50,grass>150;days:1;tools:shovel','331:200',1,'<CANTR REPLACE NAME=project_composting_2 IN1=fresh_dung IN2=grass>',2,21,0),(407,573,'raws:grass>200;days:1;tools:shovel','331:150',1,'<CANTR REPLACE NAME=project_composting_1 IN1=grass>',2,21,0),(408,573,'raws:arnica>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','166:180',1,'<CANTR REPLACE NAME=project_growing RAW=arnica>',2,21,2),(409,573,'raws:basil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','169:240',1,'<CANTR REPLACE NAME=project_growing RAW=basil>',2,21,2),(410,573,'raws:bergamot oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','172:210',1,'<CANTR REPLACE NAME=project_growing RAW=bergamot_oil>',2,21,2),(411,573,'raws:camphor oil>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','173:180',1,'<CANTR REPLACE NAME=project_growing RAW=camphor_oil>',2,21,2),(412,573,'raws:clove>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','174:180',1,'<CANTR REPLACE NAME=project_growing RAW=clove>',2,21,2),(413,573,'raws:dill>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','177:210',1,'<CANTR REPLACE NAME=project_growing RAW=dill>',2,21,2),(414,573,'raws:eucalyptus oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','180:150',1,'<CANTR REPLACE NAME=project_growing RAW=eucalyptus_oil>',2,21,2),(415,573,'raws:garlic>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','181:150',1,'<CANTR REPLACE NAME=project_growing RAW=garlic>',2,21,2),(416,573,'raws:ginger>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','184:210',1,'<CANTR REPLACE NAME=project_growing RAW=ginger>',2,21,2),(417,573,'raws:hamamelis>18,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','187:150',1,'<CANTR REPLACE NAME=project_growing RAW=hamamelis>',2,21,2),(418,573,'raws:lavender>18,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','190:180',1,'<CANTR REPLACE NAME=project_growing RAW=lavender>',2,21,2),(419,573,'raws:lemons>18,fertilizer>60,water>60;days:1;tools:knife,dung-fork','193:180',1,'<CANTR REPLACE NAME=project_growing RAW=lemons>',2,21,2),(420,573,'raws:myrrh>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','195:180',1,'<CANTR REPLACE NAME=project_growing RAW=myrrh>',2,21,2),(421,573,'raws:patchouli oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','198:240',1,'<CANTR REPLACE NAME=project_growing RAW=patchouli_oil>',2,21,2),(422,573,'raws:peppermint>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','199:210',1,'<CANTR REPLACE NAME=project_growing RAW=peppermint>',2,21,2),(423,573,'raws:sage>24,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','202:120',1,'<CANTR REPLACE NAME=project_growing RAW=sage>',2,21,2),(424,573,'raws:tarragon>24,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','205:120',1,'<CANTR REPLACE NAME=project_growing RAW=tarragon>',2,21,2),(425,573,'raws:tea leaves>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','244:180',1,'<CANTR REPLACE NAME=project_growing RAW=tea_leaves>',2,21,2),(426,573,'raws:thyme>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','208:150',1,'<CANTR REPLACE NAME=project_growing RAW=thyme>',2,21,2),(427,573,'raws:white willow>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','211:210',1,'<CANTR REPLACE NAME=project_growing RAW=white_willow>',2,21,2),(428,573,'raws:yarrow>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','234:210',1,'<CANTR REPLACE NAME=project_growing RAW=yarrow>',2,21,2),(429,570,'raws:wheat flour>800,wood>600;days:1;tools:cooking stone','110:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pancake FUEL=wood>',2,25,0),(430,570,'raws:eggs>90,wood>54;days:1;tools:pot','290:90',1,'<CANTR REPLACE NAME=project_boiling_fueled RAW=eggs FUEL=wood>',2,25,0),(431,875,'raws:hemp>280;days:1;tools:drop spindle','130:270',1,'<CANTR REPLACE NAME=project_spinning RAW=hemp_yarn>',0,26,0),(432,75,'raws:biodiesel>100;days:1;ignorerawtools','35:6400',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=carrots FUEL=biodiesel>',1,1,0),(433,570,'raws:potatoes>1600,milk>400,wood>600;days:1;tools:pot','306:2000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mashed_potatoes FUEL=wood>',2,25,0),(434,0,'raws:potatoes>960,milk>240,wood>300;days:1;tools:pot','306:1200',1,'Cooking mashed potatoes (pot) ',2,25,0),(435,0,'raws:potatoes>500,milk>120,wood>150;days:1;tools:small pot','306:620',1,'Cooking mashed potatoes (small pot) ',2,25,0),(436,570,'raws:meat>1000,wood>750;days:1;tools:pot','107:935',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=wood>',2,25,0),(437,0,'raws:meat>600,wood>325;days:1;tools:pot','107:560',1,'Cooking meat (pot)',1,25,0),(438,0,'raws:meat>300,wood>175;days:1;tools:small pot','107:280',1,'Cooking meat (small pot) ',1,25,0),(439,570,'raws:barley>900,milk>100,wood>750;days:1;tools:pot','300:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=wood>',2,25,0),(440,0,'raws:barley>525,milk>75,wood>325;days:1;tools:pot','300:600',1,'Cooking porridge (pot) ',2,25,0),(441,570,'raws:milk>600,wood>300;days:1;tools:pot','292:550',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=wood>',2,25,0),(442,0,'raws:barley>270,milk>30,wood>175;days:1;tools:small pot','300:300',1,'Cooking porridge (small pot) ',2,25,0),(443,570,'raws:asparagus>550,wood>300;days:1','315:550',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=wood>',2,25,0),(444,570,'raws:meat>300,wood>200;days:1','109:270',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=wood>',2,25,0),(445,570,'raws:carrots>800,wood>300;days:1','316:800',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=wood>',2,25,0),(446,570,'raws:eggs>60,wood>40;days:1;tools:cooking stone','289:48',1,'<CANTR REPLACE NAME=project_scrambling_fueled RAW=eggs FUEL=wood>',2,25,0),(447,570,'raws:beeswax>100,resin>40,wood>20;days:1','259:80',1,'<CANTR REPLACE NAME=project_forming RAW=wax_mixture>',1,0,0),(448,758,'raws:propane>50;days:1;ignorerawtools','141:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnetite FUEL=propane>',1,20,0),(449,570,'raws:meat>500,barley>500,wood>750;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=wood>',2,25,0),(450,51,'raws:propane>50;days:1;ignorerawtools','22:960',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnesium FUEL=propane>',1,20,0),(451,92,'raws:alcohol>150;days:1;ignorerawtools','17:1400',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=copper FUEL=alcohol>',1,20,0),(452,570,'raws:meat>500,potatoes>300,carrots>200,wood>750;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=wood>',2,25,0),(453,187,'raws:alcohol>150;days:1;ignorerawtools','59:1040',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=bauxite FUEL=alcohol>',1,20,0),(454,216,'raws:biodiesel>100;days:1;ignorerawtools','119:7200',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=clay FUEL=biodiesel>',1,18,0),(455,574,'raws:fresh dung>200;days:1;tools:shovel','331:100',1,'<CANTR REPLACE NAME=project_composting_1 IN1=fresh_dung>',2,21,0),(456,574,'raws:fresh dung>50,grass>150;days:1;tools:shovel','331:200',1,'<CANTR REPLACE NAME=project_composting_2 IN1=fresh_dung IN2=grass>',2,21,0),(457,574,'raws:grass>200;days:1;tools:shovel','331:150',1,'<CANTR REPLACE NAME=project_composting_1 IN1=grass>',2,21,0),(458,574,'raws:aloe vera>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','164:150',1,'<CANTR REPLACE NAME=project_growing RAW=aloe_vera>',2,21,2),(459,574,'raws:arnica>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','166:180',1,'<CANTR REPLACE NAME=project_growing RAW=arnica>',2,21,2),(460,574,'raws:basil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','169:240',1,'<CANTR REPLACE NAME=project_growing RAW=basil>',2,21,2),(461,574,'raws:bergamot oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','172:210',1,'<CANTR REPLACE NAME=project_growing RAW=bergamot_oil>',2,21,2),(462,574,'raws:camphor oil>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','173:180',1,'<CANTR REPLACE NAME=project_growing RAW=camphor_oil>',2,21,2),(463,574,'raws:clove>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','174:180',1,'<CANTR REPLACE NAME=project_growing RAW=clove>',2,21,2),(464,574,'raws:dill>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','177:210',1,'<CANTR REPLACE NAME=project_growing RAW=dill>',2,21,2),(465,574,'raws:eucalyptus oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','180:150',1,'<CANTR REPLACE NAME=project_growing RAW=eucalyptus_oil>',2,21,2),(466,574,'raws:garlic>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','181:150',1,'<CANTR REPLACE NAME=project_growing RAW=garlic>',2,21,2),(467,574,'raws:ginger>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','184:210',1,'<CANTR REPLACE NAME=project_growing RAW=ginger>',2,21,2),(468,574,'raws:hamamelis>18,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','187:150',1,'<CANTR REPLACE NAME=project_growing RAW=hamamelis>',2,21,2),(469,574,'raws:lavender>18,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','190:180',1,'<CANTR REPLACE NAME=project_growing RAW=lavender>',2,21,2),(470,574,'raws:lemons>18,fertilizer>60,water>60;days:1;tools:knife,dung-fork','193:180',1,'<CANTR REPLACE NAME=project_growing RAW=lemons>',2,21,2),(471,574,'raws:myrrh>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','195:180',1,'<CANTR REPLACE NAME=project_growing RAW=myrrh>',2,21,2),(472,574,'raws:patchouli oil>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','198:240',1,'<CANTR REPLACE NAME=project_growing RAW=patchouli_oil>',2,21,2),(473,574,'raws:peppermint>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','199:210',1,'<CANTR REPLACE NAME=project_growing RAW=peppermint>',2,21,2),(474,574,'raws:sage>24,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','202:120',1,'<CANTR REPLACE NAME=project_growing RAW=sage>',2,21,2),(475,574,'raws:tarragon>24,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','205:120',1,'<CANTR REPLACE NAME=project_growing RAW=tarragon>',2,21,2),(476,574,'raws:tea leaves>36,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','244:180',1,'<CANTR REPLACE NAME=project_growing RAW=tea_leaves>',2,21,2),(477,574,'raws:thyme>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','208:150',1,'<CANTR REPLACE NAME=project_growing RAW=thyme>',2,21,2),(478,574,'raws:white willow>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','211:210',1,'<CANTR REPLACE NAME=project_growing RAW=white_willow>',2,21,2),(479,574,'raws:yarrow>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','234:210',1,'<CANTR REPLACE NAME=project_growing RAW=yarrow>',2,21,2),(480,402,'days:1;tools:bucket','6:400',1,'<CANTR REPLACE NAME=project_well_fetching RAW=water>',2,20,0),(481,570,'raws:wheat flour>800,dried dung>800;days:1;tools:cooking stone','110:800',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pancake FUEL=dried_dung>',2,25,0),(482,570,'raws:fresh dung>1000;days:1','295:800',1,'<CANTR REPLACE NAME=project_drying RAW=fresh_dung>',2,25,1),(483,570,'raws:eggs>90,dried dung>72;days:1;tools:pot','290:90',1,'<CANTR REPLACE NAME=project_boiling_fueled RAW=eggs FUEL=dried_dung>',2,25,0),(484,875,'raws:wool>235;days:1;tools:drop spindle','95:230',1,'<CANTR REPLACE NAME=project_spinning RAW=wool_yarn>',0,26,0),(485,0,'raws:eggs>20,dried dung>19;days:1;tools:small pot','290:20',1,'Boiling eggs (small pot) (dung)',1,25,0),(486,570,'raws:potatoes>1600,milk>400,dried dung>800;days:1;tools:pot','306:2000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mashed_potatoes FUEL=dried_dung>',2,25,0),(487,0,'raws:potatoes>960,milk>240,dried dung>400;days:1;tools:pot','306:1200',1,'Cooking mashed potatoes (pot) (dung)',2,25,0),(488,0,'raws:potatoes>500,milk>120,dried dung>200;days:1;tools:small pot','306:620',1,'Cooking mashed potatoes (small pot) (dung)',2,25,0),(489,570,'raws:meat>1000,dried dung>1000;days:1;tools:pot','107:935',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meat FUEL=dried_dung>',2,25,0),(490,0,'raws:meat>600,dried dung>433;days:1;tools:pot','107:560',1,'Cooking meat (pot) (dung)',1,25,0),(491,0,'raws:meat>300,dried dung>233;days:1;tools:small pot','107:280',1,'Cooking meat (small pot) (dung)',1,25,0),(492,570,'raws:barley>900,milk>100,dried dung>1000;days:1;tools:pot','300:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=porridge FUEL=dried_dung>',2,25,0),(493,0,'raws:barley>525,milk>75,dried dung>433;days:1;tools:pot','300:600',1,'Cooking porridge (pot) (dung)',2,25,0),(494,0,'raws:barley>270,milk>30,dried dung>233;days:1;tools:small pot','300:300',1,'Cooking porridge (small pot) (dung)',2,25,0),(495,570,'raws:milk>600,dried dung>400;days:1;tools:pot','292:550',1,'<CANTR REPLACE NAME=project_curdling_fueled RAW=milk FUEL=dried_dung>',2,25,0),(496,570,'raws:asparagus>550,dried dung>400;days:1','315:550',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=asparagus FUEL=dried_dung>',2,25,0),(497,570,'raws:meat>300,dried dung>266;days:1','109:270',1,'<CANTR REPLACE NAME=project_grilling_fueled RAW=meat FUEL=dried_dung>',2,25,0),(498,570,'raws:carrots>800,dried dung>400;days:1','316:800',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=carrots FUEL=dried_dung>',2,25,0),(499,570,'raws:eggs>60,dried dung>53;days:1;tools:cooking stone','289:48',1,'<CANTR REPLACE NAME=project_scrambling_fueled RAW=eggs FUEL=dried_dung>',2,25,0),(500,570,'raws:meat>500,barley>500,dried dung>1000;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=barley FUEL=dried_dung>',2,25,0),(501,42,'raws:alcohol>150;days:1;ignorerawtools','124:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=hematite FUEL=alcohol>',1,20,0),(502,92,'raws:biodiesel>100;days:1;ignorerawtools','17:1400',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=copper FUEL=biodiesel>',1,20,0),(503,81,'raws:alcohol>150;days:1;ignorerawtools','16:2000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=coal FUEL=alcohol>',1,20,0),(504,570,'raws:meat>500,potatoes>300,carrots>200,dried dung>1000;days:1;tools:pot','299:1000',1,'<CANTR REPLACE NAME=project_stewing_2_fueled IN1=meat IN2=vegetables FUEL=dried_dung>',2,25,0),(505,187,'raws:biodiesel>100;days:1;ignorerawtools','59:1040',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=bauxite FUEL=biodiesel>',1,20,0),(506,52,'raws:alcohol>150;days:1;ignorerawtools','5:16000',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=sand FUEL=alcohol>',1,18,0),(507,266,'objects:bronze coin>4;days:1;tools:hammer','267:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=bronze OBJECT=bronze_coin>',2,16,0),(508,223,'objects:bronze coin>4;days:1;tools:hammer','267:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=bronze OBJECT=bronze_coin>',2,16,0),(509,266,'objects:chromium coin>4;days:1;tools:hammer','50:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=chromium OBJECT=chromium_coin>',2,16,0),(510,223,'objects:chromium coin>4;days:1;tools:hammer','50:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=chromium OBJECT=chromium_coin>',2,16,0),(511,266,'objects:cobalt coin>4;days:1;tools:hammer','23:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=cobalt OBJECT=cobalt_coin>',2,16,0),(512,223,'objects:cobalt coin>4;days:1;tools:hammer','23:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=cobalt OBJECT=cobalt_coin>',2,16,0),(513,24,'raws:coffee cherries>1000,wood>25;days:1','265:1000',1,'<CANTR REPLACE NAME=project_roasting_coffee RAW=coffee_cherries>',2,25,0),(514,266,'objects:platinum coin>4;days:1;tools:hammer','99:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=platinum OBJECT=platinum_coin>',2,16,0),(515,223,'objects:platinum coin>4;days:1;tools:hammer','99:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=platinum OBJECT=platinum_coin>',2,16,0),(516,266,'objects:tin coin>4;days:1;tools:hammer','19:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=tin OBJECT=tin_coin>',2,16,0),(517,223,'objects:tin coin>4;days:1;tools:hammer','19:40',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=tin OBJECT=tin_coin>',2,16,0),(518,120,'raws:coffee cherries>1250,coal>25;days:1','265:1250',1,'<CANTR REPLACE NAME=project_roasting_coffee_fueled RAW=coffee_cherries FUEL=coal>',2,25,0),(519,120,'raws:coffee cherries>1250,charcoal>40;days:1','265:1250',1,'<CANTR REPLACE NAME=project_roasting_coffee_fueled RAW=coffee_cherries FUEL=charcoal>',2,25,0),(520,308,'raws:coffee cherries>1500,propane>16;days:1','265:1500',1,'<CANTR REPLACE NAME=project_roasting_coffee RAW=coffee_cherries>',2,25,0),(521,24,'raws:pastry dough>400,apples>100,wood>25;days:1;tools:knife','332:500',1,'<CANTR REPLACE NAME=project_baking RAW=apple_pie>',2,25,0),(522,120,'raws:pastry dough>450,apples>150,coal>25;days:1;tools:knife','332:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=apple_pie FUEL=coal>',2,25,0),(523,120,'raws:pastry dough>450,apples>150,charcoal>40;days:1;tools:knife','332:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=apple_pie FUEL=charcoal>',2,25,0),(524,308,'raws:pastry dough>500,apples>200,propane>16;days:1;tools:knife','332:700',1,'<CANTR REPLACE NAME=project_baking RAW=apple_pie>',2,25,0),(525,117,'raws:pineapples>200,coconuts>100,papayas>300;days:1;tools:knife,wooden bowl','333:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=fruit_salad>',4,25,0),(526,618,'raws:grape juice>400,wood>80;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=grape_juice FUEL=wood>',1,25,1),(527,618,'raws:wort>400,wood>80;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=wort FUEL=wood>',1,25,1),(528,618,'raws:potato mash>400,wood>80;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=potato_mash FUEL=wood>',1,25,1),(529,618,'raws:apple juice>400,wood>80;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=apple_juice FUEL=wood>',1,25,1),(530,618,'raws:amazake>450,wood>80;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=amazake FUEL=wood>',1,25,1),(531,0,'raws:water>800,wood>580;days:1','334:100',1,'Distilling wood (wood)',1,25,1),(532,625,'raws:meat>1000,alcohol>250,soda>80,coal>50;days:1','335:1200',1,'<CANTR REPLACE NAME=project_mixing_manu_fueled RAW=impure_biodiesel FUEL=coal>',1,25,1),(533,625,'raws:meat>1000,alcohol>250,soda>80,wood>80;days:1','335:1200',1,'<CANTR REPLACE NAME=project_mixing_manu_fueled RAW=impure_biodiesel FUEL=wood>',1,25,1),(534,625,'raws:impure biodiesel>400,water>200;days:1','336:300',1,'<CANTR REPLACE NAME=project_washing RAW=biodiesel>',2,25,0),(535,759,'raws:alcohol>150;days:1;ignorerawtools','142:12000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=taconite FUEL=alcohol>',1,20,0),(536,24,'raws:wheat flour>550,salt>50,wood>30;days:1','338:600',1,'<CANTR REPLACE NAME=project_baking RAW=pretzels>',2,25,0),(537,214,'raws:clay>1100,water>25,coal>30;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_1_fueled RAW=brick IN1=clay FUEL=coal>',3,4,0),(538,214,'raws:clay>1100,water>25,charcoal>60;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_1_fueled RAW=brick IN1=clay FUEL=charcoal>',3,4,0),(539,104,'raws:clay>1100,water>25,charcoal>60;days:1;tools:brick mould','150:550',1,'<CANTR REPLACE NAME=project_hardening_1_fueled RAW=brick IN1=clay FUEL=charcoal>',3,4,0),(540,214,'raws:mud>1100,grass>800,charcoal>60;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=grass FUEL=charcoal>',3,4,0),(541,214,'raws:mud>1100,grass>800,coal>30;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=grass FUEL=coal>',3,4,0),(542,214,'raws:mud>1100,reed>400,charcoal>60;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=reed FUEL=charcoal>',3,4,0),(543,214,'raws:mud>1100,reed>400,coal>30;days:1;tools:brick mould','150:750',1,'<CANTR REPLACE NAME=project_hardening_2_fueled RAW=brick IN1=mud IN2=reed FUEL=coal>',3,4,0),(544,304,'raws:blueberries>200;days:1','339:175',1,'<CANTR REPLACE NAME=project_drying RAW=blueberries>',0,0,1),(545,304,'raws:blackberries>200;days:1','339:175',1,'<CANTR REPLACE NAME=project_drying RAW=blackberries>',0,0,1),(546,304,'raws:raspberries>200;days:1','339:175',1,'<CANTR REPLACE NAME=project_drying RAW=raspberries>',0,0,1),(547,629,'days:1;location_state:sailing_floating;ignorerawtools','154:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=salmon>',1,17,0),(548,568,'days:1;tools:water skin;location_state:outside','6:50',1,'<CANTR REPLACE NAME=project_spring_fetching RAW=water>',1,19,0),(549,618,'raws:wort>400,coal>50;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=wort FUEL=coal>',1,25,1),(550,618,'raws:potato mash>400,coal>50;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=potato_mash FUEL=coal>',1,25,1),(551,618,'raws:amazake>450,coal>50;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=amazake FUEL=coal>',1,25,1),(552,618,'raws:grape juice>400,coal>50;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=grape_juice FUEL=coal>',1,25,1),(553,618,'raws:apple juice>400,coal>50;days:3','334:200',0,'<CANTR REPLACE NAME=project_fermenting_distilling_fueled RAW=apple_juice FUEL=coal>',1,25,1),(554,0,'raws:water>800,wood>500,coal>50;days:1','334:100',1,'Distilling wood (coal)',1,25,1),(555,618,'raws:grape juice>400,coal>50;days:3','403:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=brandy FUEL=coal>',1,25,1),(556,618,'raws:grape juice>400,wood>80;days:3','403:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=brandy FUEL=wood>',1,25,1),(557,618,'raws:potato mash>400,coal>50;days:3','401:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=potato_spirit FUEL=coal>',1,25,1),(558,618,'raws:potato mash>400,wood>80;days:3','401:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=potato_spirit FUEL=wood>',1,25,1),(559,618,'raws:amazake>450,coal>50;days:3','400:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=rice_spirit FUEL=coal>',1,25,1),(560,618,'raws:amazake>450,wood>80;days:3','400:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=rice_spirit FUEL=wood>',1,25,1),(561,0,'raws:rye>500,water>800,coal>50;days:1','334:300',1,'Distilling rye (coal)',1,25,1),(562,0,'raws:rye>500,water>800,wood>80;days:1','334:300',1,'Distilling rye (wood)',1,25,1),(563,0,'raws:wheat>500,water>800,coal>50;days:1','334:250',1,'Distilling wheat (coal)',1,25,1),(564,0,'raws:wheat>500,water>800,wood>80;days:1','334:250',1,'Distilling wheat (wood)',1,25,1),(565,0,'raws:water>800,wood>500,coal>50;days:1','334:100',1,'Distilling wood (coal)',1,25,1),(566,0,'raws:water>800,wood>580;days:1','334:100',1,'Distilling wood (wood)',1,25,1),(567,618,'raws:apple juice>400,coal>50;days:3','402:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=apple_brandy FUEL=coal>',1,25,1),(568,618,'raws:apple juice>400,wood>80;days:3','402:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=apple_brandy FUEL=wood>',1,25,1),(569,618,'raws:wort>400,coal>50;days:3','399:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=grain_spirit FUEL=coal>',1,25,1),(570,618,'raws:wort>400,wood>80;days:3','399:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=grain_spirit FUEL=wood>',1,25,1),(571,0,'raws:potatoes>500,water>800,coal>50;days:1','334:200',1,'Distilling potatoes (coal)',1,25,1),(572,0,'raws:potatoes>500,water>800,wood>80;days:1','334:200',1,'Distilling potatoes (wood)',1,25,1),(573,0,'raws:rye>500,water>800,coal>50;days:1','334:300',1,'Distilling rye (coal)',1,25,1),(574,0,'raws:rye>500,water>800,wood>80;days:1','334:300',1,'Distilling rye (wood)',1,25,1),(575,0,'raws:wheat>500,water>800,coal>50;days:1','334:250',1,'Distilling wheat (coal)',1,25,1),(576,0,'raws:wheat>500,water>800,wood>80;days:1','334:250',1,'Distilling wheat (wood)',1,25,1),(577,0,'raws:water>800,wood>500,coal>50;days:1','334:100',1,'Distilling wood (coal)',1,25,1),(578,0,'raws:water>800,wood>580;days:1','334:100',1,'Distilling wood (wood)',1,25,1),(579,178,'raws:corn>1200;days:1','340:1200',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=corn>',2,25,0),(580,177,'raws:corn>800;days:1','340:800',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=corn>',2,25,0),(581,147,'raws:cornmeal>600,dried dung>500;days:1;tools:cooking stone','341:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tortillas FUEL=dried_dung>',2,25,0),(582,147,'raws:cornmeal>600,wood>200;days:1;tools:cooking stone','341:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tortillas FUEL=wood>',2,25,0),(583,570,'raws:cornmeal>600,dried dung>500;days:1;tools:cooking stone','341:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tortillas FUEL=dried_dung>',2,25,0),(584,570,'raws:cornmeal>600,wood>200;days:1;tools:cooking stone','341:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tortillas FUEL=wood>',2,25,0),(585,24,'raws:cornmeal>550,milk>40,eggs>10,wood>40;days:1','342:600',1,'<CANTR REPLACE NAME=project_baking RAW=cornbread>',2,25,1),(586,251,'raws:cornmeal>550,milk>40,eggs>10,wood>200;days:1','342:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cornbread FUEL=wood>',2,25,0),(587,251,'raws:cornmeal>550,milk>40,eggs>10,dried dung>500;days:1','342:600',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cornbread FUEL=dried_dung>',2,25,0),(588,120,'raws:cornmeal>640,milk>50,eggs>15,coal>40;days:1','342:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cornbread FUEL=coal>',2,25,1),(589,120,'raws:cornmeal>645,milk>50,eggs>15,charcoal>80;days:1','342:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=cornbread FUEL=charcoal>',2,25,1),(590,117,'raws:corn>400,milk>50;days:1','343:450',1,'<CANTR REPLACE NAME=project_making_gen RAW=grits>',2,25,0),(591,308,'raws:cornmeal>730,milk>55,eggs>15,propane>20;days:1','342:800',1,'<CANTR REPLACE NAME=project_baking RAW=cornbread>',2,25,1),(592,120,'raws:wheat flour>625,salt>75,charcoal>55;days:1','338:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pretzels FUEL=charcoal>',2,25,0),(593,120,'raws:wheat flour>625,salt>75,coal>30;days:1','338:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pretzels FUEL=coal>',2,25,0),(594,308,'raws:wheat flour>700,salt>100,propane>10;days:1','338:800',1,'<CANTR REPLACE NAME=project_baking RAW=pretzels>',2,25,0),(595,117,'raws:rice>500,sugar>100,milk>100;days:1;tools:wooden bowl','344:700',1,'<CANTR REPLACE NAME=project_making_gen RAW=rice_pudding>',2,25,0),(596,301,'raws:rice>250,sugar>50,milk>50;days:1;tools:mixing bowl','344:350',1,'<CANTR REPLACE NAME=project_making_gen RAW=rice_pudding>',2,25,0),(597,117,'raws:bread>200,cheese>100;days:1;tools:knife','345:300',1,'<CANTR REPLACE NAME=project_making_gen RAW=cheese_sandwich>',2,25,0),(598,47,'raws:alcohol>150;days:1;ignorerawtools','18:1600',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=nickel FUEL=alcohol>',1,20,0),(599,758,'raws:alcohol>150;days:1;ignorerawtools','141:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnetite FUEL=alcohol>',1,20,0),(600,626,'raws:oil>2000;days:1','346:1500',1,'<CANTR REPLACE NAME=project_refining RAW=petrol>',1,23,0),(601,47,'raws:biodiesel>100;days:1;ignorerawtools','18:1600',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=nickel FUEL=biodiesel>',1,20,0),(602,573,'raws:roses>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','114:150',1,'<CANTR REPLACE NAME=project_growing RAW=roses>',2,21,2),(603,573,'raws:tulips>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','113:150',1,'<CANTR REPLACE NAME=project_growing RAW=tulips>',2,21,2),(604,573,'raws:daisies>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','115:120',1,'<CANTR REPLACE NAME=project_growing RAW=daisies>',2,21,2),(605,573,'raws:buttercups>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','116:120',1,'<CANTR REPLACE NAME=project_growing RAW=buttercups>',2,21,2),(606,574,'raws:daisies>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','115:120',1,'<CANTR REPLACE NAME=project_growing RAW=daisies>',2,21,2),(607,574,'raws:buttercups>12,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','116:120',1,'<CANTR REPLACE NAME=project_growing RAW=buttercups>',2,21,2),(608,574,'raws:roses>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','114:150',1,'<CANTR REPLACE NAME=project_growing RAW=roses>',2,21,2),(609,574,'raws:tulips>30,fertilizer>60,water>60;days:1;tools:scissors,dung-fork','113:150',1,'<CANTR REPLACE NAME=project_growing RAW=tulips>',2,21,2),(610,117,'raws:bread>300,baked cod>200;days:1;tools:knife','347:500',1,'<CANTR REPLACE NAME=project_making_gen_from RAW=fish_sandwich IN=baked_cod>',2,25,0),(611,117,'raws:bread>300,baked salmon>200;days:1;tools:knife','347:500',1,'<CANTR REPLACE NAME=project_making_gen_from RAW=fish_sandwich IN=baked_salmon>',2,25,0),(612,117,'raws:bread>300,baked pike>200;days:1;tools:knife','347:500',1,'<CANTR REPLACE NAME=project_making_gen_from RAW=fish_sandwich IN=baked_pike>',2,25,0),(613,117,'raws:bread>300,baked trout>200;days:1;tools:knife','347:500',1,'<CANTR REPLACE NAME=project_making_gen_from RAW=fish_sandwich IN=baked_trout>',2,25,0),(614,304,'raws:pike>200;days:1','348:100',1,'<CANTR REPLACE NAME=project_drying RAW=pike>',1,25,1),(615,304,'raws:salmon>200;days:1','349:100',1,'<CANTR REPLACE NAME=project_drying RAW=salmon>',1,25,1),(616,242,'days:1;tools:bucket;location_state:outside','6:100',1,'<CANTR REPLACE NAME=project_spring_fetching RAW=water>',1,19,0),(617,247,'days:1;tools:bucket;location_state:outside','6:100',1,'<CANTR REPLACE NAME=project_spring_fetching RAW=water>',1,19,0),(618,214,'raws:clay>355,coal>30;days:1','350:250',1,'<CANTR REPLACE NAME=project_hardening_0_fueled RAW=clay_beads FUEL=coal>',4,4,0),(619,104,'raws:clay>245,charcoal>60;days:1','350:180',1,'<CANTR REPLACE NAME=project_hardening_0_fueled RAW=clay_beads FUEL=charcoal>',4,4,0),(620,104,'raws:clay>245,coal>30;days:1','350:180',1,'<CANTR REPLACE NAME=project_hardening_0_fueled RAW=clay_beads FUEL=coal>',4,4,0),(621,214,'raws:clay>355,charcoal>60;days:1','350:250',1,'<CANTR REPLACE NAME=project_hardening_0_fueled RAW=clay_beads FUEL=charcoal>',4,4,0),(622,266,'raws:clay>500,stone>200,water>50;days:1;tools:hammer,broom','351:350',1,'<CANTR REPLACE NAME=project_mixing_clay RAW=ceramic_clay>',2,4,0),(623,223,'raws:clay>500,stone>200,water>50;days:1;tools:hammer,broom','351:350',1,'<CANTR REPLACE NAME=project_mixing_clay RAW=ceramic_clay>',2,4,0),(624,266,'raws:clay>500,stone>200,marble>150,water>50;days:1;tools:hammer,broom','352:425',1,'<CANTR REPLACE NAME=project_mixing_clay RAW=porcelain_clay>',2,4,0),(625,223,'raws:clay>500,stone>200,marble>150,water>50;days:1;tools:hammer,broom','352:425',1,'<CANTR REPLACE NAME=project_mixing_clay RAW=porcelain_clay>',2,4,0),(626,8,'raws:petrol>100;days:1;ignorerawtools','25:12800',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=potatoes FUEL=petrol>',1,1,0),(627,42,'raws:petrol>100;days:1;ignorerawtools','124:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=hematite FUEL=petrol>',1,20,0),(628,47,'raws:petrol>100;days:1;ignorerawtools','18:1600',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=nickel FUEL=petrol>',1,20,0),(629,51,'raws:petrol>100;days:1;ignorerawtools','22:960',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnesium FUEL=petrol>',1,20,0),(630,52,'raws:petrol>100;days:1;ignorerawtools','5:16000',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=sand FUEL=petrol>',1,18,0),(631,75,'raws:petrol>100;days:1;ignorerawtools','35:6400',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=carrots FUEL=petrol>',1,1,0),(632,81,'raws:petrol>100;days:1;ignorerawtools','16:2000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=coal FUEL=petrol>',1,20,0),(633,92,'raws:petrol>100;days:1;ignorerawtools','17:1400',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=copper FUEL=petrol>',1,20,0),(634,759,'raws:biodiesel>100;days:1;ignorerawtools','142:12000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=taconite FUEL=biodiesel>',1,20,0),(635,187,'raws:petrol>100;days:1;ignorerawtools','59:1040',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=bauxite FUEL=petrol>',1,20,0),(636,724,'raws:roses>10,water>60;days:1','114:50',1,'<CANTR REPLACE NAME=project_growing RAW=roses>',1,21,1),(637,724,'raws:tulips>10,water>60;days:1','113:50',1,'<CANTR REPLACE NAME=project_growing RAW=tulips>',1,21,1),(638,724,'raws:daisies>4,water>60;days:1','115:40',1,'<CANTR REPLACE NAME=project_growing RAW=daisies>',1,21,1),(639,724,'raws:buttercups>4,water>60;days:1','116:40',1,'<CANTR REPLACE NAME=project_growing RAW=buttercups>',1,21,1),(640,732,'raws:buttercups>2,water>30;days:1','116:20',1,'<CANTR REPLACE NAME=project_growing RAW=buttercups>',1,21,2),(641,732,'raws:daisies>2,water>30;days:1','115:20',1,'<CANTR REPLACE NAME=project_growing RAW=daisies>',1,21,2),(642,732,'raws:roses>5,water>30;days:1','114:25',1,'<CANTR REPLACE NAME=project_growing RAW=roses>',1,21,2),(643,732,'raws:tulips>5,water>30;days:1','113:25',1,'<CANTR REPLACE NAME=project_growing RAW=tulips>',1,21,2),(644,219,'raws:hemp cloth>900,oil>150;days:1;tools:paintbrush','353:1000',1,'<CANTR REPLACE NAME=project_preparing_hemp_sailcloth RAW=sailcloth IN=oil>',3,26,0),(645,219,'raws:cotton cloth>700,oil>325;days:1;tools:paintbrush','353:1000',1,'<CANTR REPLACE NAME=project_preparing_cotton_sailcloth RAW=sailcloth IN=oil>',3,26,0),(646,219,'raws:cotton cloth>700,resin>325;days:1;tools:paintbrush','353:1000',1,'<CANTR REPLACE NAME=project_preparing_cotton_sailcloth RAW=sailcloth IN=resin>',3,26,0),(647,219,'raws:hemp cloth>900,resin>150;days:1;tools:paintbrush','353:1000',1,'<CANTR REPLACE NAME=project_preparing_hemp_sailcloth RAW=sailcloth IN=resin>',3,26,0),(648,301,'raws:sorghum flour>150;days:1;tools:mixing bowl','355:150',1,'<CANTR REPLACE NAME=project_mixing_food RAW=couscous>',2,25,0),(649,117,'raws:sorghum flour>300;days:1;tools:wooden bowl','355:300',1,'<CANTR REPLACE NAME=project_mixing_food RAW=couscous>',2,25,0),(650,117,'raws:pastry dough>500;days:1;tools:knife','354:500',1,'<CANTR REPLACE NAME=project_making_gen RAW=croissants>',2,25,0),(651,762,'raws:roses>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=roses>',1,19,0),(652,762,'raws:daisies>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=daisies>',1,19,0),(653,762,'raws:tulips>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=tulips>',1,19,0),(654,762,'raws:buttercups>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=buttercups>',1,19,0),(655,762,'raws:basil>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=basil>',1,19,0),(656,762,'raws:dill>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=dill>',1,19,0),(657,762,'raws:lavender>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=lavender>',1,19,0),(658,762,'raws:hamamelis>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=hamamelis>',1,19,0),(659,762,'raws:peppermint>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=peppermint>',1,19,0),(660,762,'raws:sage>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=sage>',1,19,0),(661,762,'raws:tarragon>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=tarragon>',1,19,0),(662,762,'raws:arnica>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=arnica>',1,19,0),(663,762,'raws:thyme>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=thyme>',1,19,0),(664,762,'raws:patchouli oil>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=patchouli_oil>',1,19,0),(665,762,'raws:yarrow>50;days:1','356:180',1,'<CANTR REPLACE NAME=project_collecting_honey RAW=yarrow>',1,19,0),(666,759,'days:1;ignorerawtools','142:3000',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=taconite>',4,20,0),(667,758,'days:1;ignorerawtools','141:1500',1,'<CANTR REPLACE NAME=project_drilling_drill RAW=magnetite>',4,20,0),(668,758,'raws:petrol>100;days:1;ignorerawtools','141:6000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=magnetite FUEL=petrol>',1,20,0),(669,759,'raws:petrol>100;days:1;ignorerawtools','142:12000',1,'<CANTR REPLACE NAME=project_drilling_drill_fueled RAW=taconite FUEL=petrol>',1,20,0),(670,216,'raws:petrol>100;days:1;ignorerawtools','119:7200',1,'<CANTR REPLACE NAME=project_machine_digging_fueled RAW=clay FUEL=petrol>',1,18,0),(671,330,'raws:petrol>100;days:1;ignorerawtools','38:11200',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=rice FUEL=petrol>',1,1,0),(672,330,'raws:propane>50;days:1;ignorerawtools','38:11200',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=rice FUEL=propane>',1,1,0),(673,330,'raws:alcohol>150;days:1;ignorerawtools','38:11200',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=rice FUEL=alcohol>',1,1,0),(674,724,'raws:arnica>12,water>60;days:1','166:60',1,'<CANTR REPLACE NAME=project_growing RAW=arnica>',1,21,1),(675,724,'raws:basil>10,water>60;days:1','169:80',1,'<CANTR REPLACE NAME=project_growing RAW=basil>',1,21,1),(676,724,'raws:dill>4,water>60;days:1','177:70',1,'<CANTR REPLACE NAME=project_growing RAW=dill>',1,21,1),(677,724,'raws:hamamelis>6,water>60;days:1','187:50',1,'<CANTR REPLACE NAME=project_growing RAW=hamamelis>',1,21,1),(678,732,'raws:lavender>3,water>30;days:1','190:30',1,'<CANTR REPLACE NAME=project_growing RAW=lavender>',1,21,1),(679,724,'raws:lavender>6,water>60;days:1','190:60',1,'<CANTR REPLACE NAME=project_growing RAW=lavender>',1,21,1),(680,724,'raws:patchouli oil>10,water>60;days:1','198:80',1,'<CANTR REPLACE NAME=project_growing RAW=patchouli_oil>',1,21,1),(681,724,'raws:peppermint>4,water>60;days:1','199:70',1,'<CANTR REPLACE NAME=project_growing RAW=peppermint>',1,21,1),(682,724,'raws:sage>8,water>60;days:1','202:40',1,'<CANTR REPLACE NAME=project_growing RAW=sage>',1,21,1),(683,724,'raws:tarragon>8,water>60;days:1','205:40',1,'<CANTR REPLACE NAME=project_growing RAW=tarragon>',1,21,1),(684,724,'raws:thyme>10,water>60;days:1','208:50',1,'<CANTR REPLACE NAME=project_growing RAW=thyme>',1,21,1),(685,724,'raws:yarrow>10,water>60;days:1','234:70',1,'<CANTR REPLACE NAME=project_growing RAW=yarrow>',1,21,1),(686,724,'raws:clove>4,water>60;days:1','174:60',1,'<CANTR REPLACE NAME=project_growing RAW=clove>',1,21,1),(687,724,'raws:bergamot oil>10,water>60;days:1','172:70',1,'<CANTR REPLACE NAME=project_growing RAW=bergamot_oil>',1,21,1),(688,724,'raws:camphor oil>4,water>60;days:1','173:60',1,'<CANTR REPLACE NAME=project_growing RAW=camphor_oil>',1,21,1),(689,724,'raws:garlic>10,water>60;days:1','181:50',1,'<CANTR REPLACE NAME=project_growing RAW=garlic>',1,21,1),(690,724,'raws:myrrh>12,water>60;days:1','195:60',1,'<CANTR REPLACE NAME=project_growing RAW=myrrh>',1,21,1),(691,724,'raws:tea leaves>12,water>60;days:1','244:60',1,'<CANTR REPLACE NAME=project_growing RAW=tea>',1,21,1),(692,732,'raws:arnica>6,water>30;days:1','166:30',1,'<CANTR REPLACE NAME=project_growing RAW=arnica>',1,21,1),(693,732,'raws:basil>5,water>30;days:1','169:40',1,'<CANTR REPLACE NAME=project_growing RAW=basil>',1,21,1),(694,732,'raws:dill>2,water>30;days:1','177:35',1,'<CANTR REPLACE NAME=project_growing RAW=dill>',1,21,1),(695,732,'raws:hamamelis>3,water>30;days:1','187:25',1,'<CANTR REPLACE NAME=project_growing RAW=hamamelis>',1,21,1),(696,732,'raws:patchouli oil>5,water>30;days:1','198:40',1,'<CANTR REPLACE NAME=project_growing RAW=patchouli_oil>',1,21,1),(697,732,'raws:peppermint>2,water>30;days:1','199:35',1,'<CANTR REPLACE NAME=project_growing RAW=peppermint>',1,21,1),(698,732,'raws:sage>4,water>30;days:1','202:20',1,'<CANTR REPLACE NAME=project_growing RAW=sage>',1,21,1),(699,732,'raws:tarragon>4,water>30;days:1','205:20',1,'<CANTR REPLACE NAME=project_growing RAW=tarragon>',1,21,1),(700,732,'raws:thyme>5,water>30;days:1','208:25',1,'<CANTR REPLACE NAME=project_growing RAW=thyme>',1,21,1),(701,732,'raws:yarrow>5,water>30;days:1','234:35',1,'<CANTR REPLACE NAME=project_growing RAW=yarrow>',1,21,1),(702,732,'raws:clove>2,water>30;days:1','174:30',1,'<CANTR REPLACE NAME=project_growing RAW=clove>',1,21,1),(703,732,'raws:bergamot oil>5,water>30;days:1','172:35',1,'<CANTR REPLACE NAME=project_growing RAW=bergamot_oil>',1,21,1),(704,732,'raws:camphor oil>2,water>30;days:1','173:30',1,'<CANTR REPLACE NAME=project_growing RAW=camphor_oil>',1,21,1),(705,732,'raws:garlic>5,water>30;days:1','181:25',1,'<CANTR REPLACE NAME=project_growing RAW=garlic>',1,21,1),(706,732,'raws:myrrh>6,water>30;days:1','195:30',1,'<CANTR REPLACE NAME=project_growing RAW=myrrh>',1,21,1),(707,732,'raws:tea leaves>6,water>30;days:1','244:30',1,'<CANTR REPLACE NAME=project_growing RAW=tea_leaves>',1,21,1),(708,724,'raws:aloe vera>10,water>60;days:1','164:50',1,'<CANTR REPLACE NAME=project_growing RAW=aloe_vera>',1,21,1),(709,732,'raws:aloe vera>5,water>30;days:1','164:25',1,'<CANTR REPLACE NAME=project_growing RAW=aloe_vera>',1,21,1),(710,724,'raws:ginger>12,water>60;days:1','184:70',1,'<CANTR REPLACE NAME=project_growing RAW=ginger>',1,21,1),(711,732,'raws:ginger>6,water>30;days:1','184:35',1,'<CANTR REPLACE NAME=project_growing RAW=ginger>',1,21,1),(712,724,'raws:lemons>6,water>60;days:1','193:60',1,'<CANTR REPLACE NAME=project_growing RAW=lemons>',1,21,1),(713,732,'raws:lemons>3,water>30;days:1','193:30',1,'<CANTR REPLACE NAME=project_growing RAW=lemons>',1,21,1),(714,724,'raws:white willow>10,water>60;days:1','211:70',1,'<CANTR REPLACE NAME=project_growing RAW=white_willow>',1,21,1),(715,732,'raws:white willow>5,water>30;days:1','211:35',1,'<CANTR REPLACE NAME=project_growing RAW=white_willow>',1,21,1),(716,724,'raws:eucalyptus oil>10,water>60;days:1','180:50',1,'<CANTR REPLACE NAME=project_growing RAW=eucalyptus_oil>',1,21,1),(717,732,'raws:eucalyptus oil>5,water>30;days:1','180:25',1,'<CANTR REPLACE NAME=project_growing RAW=eucalyptus_oil>',1,21,1),(718,519,'raws:grapes>300;days:1','358:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=grapes>',2,25,0),(719,519,'raws:apples>400;days:1','359:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=apples>',2,25,0),(720,769,'days:1;location_state:sailing_moving','6:15',1,'<CANTR REPLACE NAME=project_purify RAW=water>',0,0,1),(721,178,'raws:rice>700;days:1','363:700',1,'<CANTR REPLACE NAME=project_polishing RAW=rice>',2,25,0),(722,177,'raws:rice>350;days:1','363:350',1,'<CANTR REPLACE NAME=project_polishing RAW=rice>',2,25,0),(723,238,'raws:thin rope>800;days:1','135:400',1,'<CANTR REPLACE NAME=project_manufacturing_rope RAW=medium_rope>',5,26,0),(724,519,'raws:malt>250;days:1','367:250',1,'<CANTR REPLACE NAME=project_mashing RAW=malt>',2,25,0),(725,242,'raws:barley>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=barley>',1,25,0),(726,242,'raws:honey>50,water>50;days:1;tools:bucket','361:50',1,'<CANTR REPLACE NAME=project_clarifying RAW=honey>',1,25,0),(727,770,'raws:grape juice>400;days:20','357:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=wine>',0,25,1),(728,770,'raws:apple juice>400;days:20','360:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=cider>',0,25,1),(729,770,'raws:clarified honey>200,water>200;days:20','362:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=mead>',0,25,1),(730,770,'raws:amazake>450;days:10','365:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=sake>',0,25,1),(731,770,'raws:wort>400;days:10','368:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=beer>',0,25,1),(732,242,'raws:polished rice>350,water>100;days:1;tools:bucket','364:300',1,'<CANTR REPLACE NAME=project_filtering RAW=amazake>',1,25,0),(733,247,'raws:barley>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=barley>',1,25,0),(734,247,'raws:polished rice>350,water>100;days:1;tools:bucket','364:300',1,'<CANTR REPLACE NAME=project_filtering RAW=amazake>',1,25,0),(735,247,'raws:honey>50,water>50;days:1;tools:bucket','361:50',1,'<CANTR REPLACE NAME=project_clarifying RAW=honey>',1,25,0),(736,304,'raws:mushrooms>200;days:1','369:100',1,'<CANTR REPLACE NAME=project_drying RAW=mushrooms>',1,0,1),(737,780,'raws:arnica>250;days:1','167:250',1,'<CANTR REPLACE NAME=project_drying RAW=arnica>',1,25,1),(738,780,'raws:basil>250;days:1','170:250',1,'<CANTR REPLACE NAME=project_drying RAW=basil>',1,25,1),(739,780,'raws:clove>250;days:1','175:250',1,'<CANTR REPLACE NAME=project_drying RAW=clove>',1,25,1),(740,780,'raws:dill>250;days:1','178:250',1,'<CANTR REPLACE NAME=project_drying RAW=dill>',1,25,1),(741,780,'raws:garlic>250;days:1','182:250',1,'<CANTR REPLACE NAME=project_drying RAW=garlic>',1,25,1),(742,780,'raws:ginger>250;days:1','185:250',1,'<CANTR REPLACE NAME=project_drying RAW=ginger>',1,25,1),(743,780,'raws:hamamelis>250;days:1','188:250',1,'<CANTR REPLACE NAME=project_drying RAW=hamamelis>',1,25,1),(744,780,'raws:lavender>250;days:1','191:250',1,'<CANTR REPLACE NAME=project_drying RAW=lavender>',1,25,1),(745,780,'raws:myrrh>250;days:1','196:250',1,'<CANTR REPLACE NAME=project_drying RAW=myrrh>',1,25,1),(746,780,'raws:peppermint>250;days:1','200:250',1,'<CANTR REPLACE NAME=project_drying RAW=peppermint>',1,25,1),(747,780,'raws:sage>250;days:1','203:250',1,'<CANTR REPLACE NAME=project_drying RAW=sage>',1,25,1),(748,780,'raws:tarragon>250;days:1','206:250',1,'<CANTR REPLACE NAME=project_drying RAW=tarragon>',1,25,1),(749,780,'raws:tea leaves>250;days:1','248:250',1,'<CANTR REPLACE NAME=project_drying RAW=tea_leaves>',1,25,1),(750,780,'raws:thyme>250;days:1','209:250',1,'<CANTR REPLACE NAME=project_drying RAW=thyme>',1,25,1),(751,780,'raws:white willow>250;days:1','212:250',1,'<CANTR REPLACE NAME=project_drying RAW=white_willow>',1,25,1),(752,780,'raws:yarrow>250;days:1','235:250',1,'<CANTR REPLACE NAME=project_drying RAW=yarrow>',1,25,1),(753,782,'raws:salmon>200,potatoes>200;days:1;tools:tajine','370:300',1,'<CANTR REPLACE NAME=project_making_food RAW=fish_cakes>',2,25,0),(754,782,'raws:rice>350,mushrooms>50;days:1;tools:tajine','371:250',1,'<CANTR REPLACE NAME=project_making_food RAW=mushroom_risotto>',2,25,0),(755,782,'raws:tomatos>400,basil>50;days:1;tools:tajine','372:400',1,'<CANTR REPLACE NAME=project_stewing_1 IN1=tomatos>',2,25,0),(756,782,'raws:carrots>300,dill>50;days:1;tools:tajine','373:350',1,'<CANTR REPLACE NAME=project_making_food RAW=carrot_stew>',2,25,0),(757,782,'raws:potatoes>200,spinage>400;days:1;tools:tajine','374:600',1,'<CANTR REPLACE NAME=project_making_food RAW=saag_aloo>',2,25,0),(758,781,'raws:meat>400,coal>25;days:1','375:400',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=kebabs FUEL=coal>',4,25,0),(759,781,'raws:meat>400,charcoal>45;days:1','375:400',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=kebabs FUEL=charcoal>',4,25,0),(760,781,'raws:potatoes>1000,onions>50,coal>25;days:1','376:1000',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=potatoes FUEL=coal>',4,25,0),(761,781,'raws:potatoes>1000,onions>50,charcoal>45;days:1','376:1000',1,'<CANTR REPLACE NAME=project_roasting_fueled RAW=potatoes FUEL=charcoal>',4,25,0),(762,781,'raws:meat>100,large bones>500,water>50,coal>25;days:1','377:300',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=aspic FUEL=coal>',4,25,0),(763,781,'raws:meat>100,large bones>500,water>50,charcoal>45;days:1','377:300',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=aspic FUEL=charcoal>',4,25,0),(764,781,'raws:pastry dough>150,cheese curds>200,coal>25;days:1','378:350',1,'<CANTR REPLACE NAME=project_making_pierogi_fueled RAW=pierogi_with_cheese FUEL=coal>',4,25,0),(765,781,'raws:pastry dough>150,cheese curds>200,charcoal>45;days:1','378:350',1,'<CANTR REPLACE NAME=project_making_pierogi_fueled RAW=pierogi_with_cheese FUEL=charcoal>',4,25,0),(766,783,'raws:large bones>2500,water>750,propane>20;days:1;tools:pot,campstove','379:1000',1,'<CANTR REPLACE NAME=project_making_food RAW=broth>',3,25,0),(767,783,'raws:salmon>200,potatoes>300,propane>15;days:1;tools:pot,campstove','380:500',1,'<CANTR REPLACE NAME=project_making_food RAW=chowder>',3,25,0),(768,783,'raws:rice>300,spinage>100,salmon>100,propane>10;days:1;tools:pot,campstove','381:500',1,'<CANTR REPLACE NAME=project_making_food RAW=gumbo>',3,25,0),(769,783,'raws:corn>30,cornmeal>300,meat>200,propane>15;days:1;tools:pot,campstove','382:500',1,'<CANTR REPLACE NAME=project_making_food RAW=tamales>',3,25,0),(770,783,'raws:rice>900,propane>15;days:1;tools:pot,campstove','383:900',1,'<CANTR REPLACE NAME=project_making_food RAW=congee>',2,25,0),(771,783,'raws:potatoes>200,blackberries>50,propane>10;days:1;tools:pot,campstove','384:250',1,'<CANTR REPLACE NAME=project_making_kisiel RAW=kisiel IN=blackberries>',3,25,0),(772,783,'raws:potatoes>200,strawberries>50,propane>10;days:1;tools:pot,campstove','384:250',1,'<CANTR REPLACE NAME=project_making_kisiel RAW=kisiel IN=strawberries>',3,25,0),(773,783,'raws:potatoes>200,raspberries>50,propane>10;days:1;tools:pot,campstove','384:250',1,'<CANTR REPLACE NAME=project_making_kisiel RAW=kisiel IN=raspberries>',3,25,0),(774,783,'raws:bread>200,meat>200,propane>10;days:1;tools:campstove','385:400',1,'<CANTR REPLACE NAME=project_making_food RAW=burgers>',3,25,0),(775,783,'raws:tortillas>300,meat>200,propane>10;days:1;tools:campstove','386:500',1,'<CANTR REPLACE NAME=project_making_food RAW=tacos>',3,25,0),(776,783,'raws:tortillas>300,cheese>200,propane>10;days:1;tools:campstove','387:500',1,'<CANTR REPLACE NAME=project_making_food RAW=pupusas>',3,25,0),(777,166,'raws:sinew>50;days:1','137:50',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=sinew>',2,26,0),(778,374,'raws:sinew>20;days:1','137:20',1,'<CANTR REPLACE NAME=project_twining RAW=string IN=sinew>',2,26,0),(779,0,'days:1','4:3750',1,'Drilling for stone',2,20,0),(780,8,'raws:alcohol>150;days:1;ignorerawtools','25:12800',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=potatoes FUEL=alcohol>',1,1,0),(781,783,'raws:potatoes>520,eggs>30,propane>10;days:1;tools:campstove,knife','389:550',1,'<CANTR REPLACE NAME=project_making_food RAW=potato_omelette>',3,25,0),(782,781,'raws:rye flour>200,potatoes>120,carrots>80,coal>30;days:1;tools:knife','390:400',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=vegetable_cakes FUEL=coal>',4,25,0),(783,781,'raws:rye flour>200,potatoes>120,carrots>80,charcoal>60;days:1;tools:knife','390:400',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=vegetable_cakes FUEL=charcoal>',4,25,0),(784,783,'raws:potatoes>200,blueberries>50,propane>10;days:1;tools:pot,campstove','384:250',1,'<CANTR REPLACE NAME=project_making_kisiel RAW=kisiel IN=blueberries>',3,25,0),(785,782,'raws:spinage>200,garlic>50;days:1;tools:tajine','391:250',1,'<CANTR REPLACE NAME=project_cooking RAW=spinach_soup_with_garlic>',2,25,0),(786,781,'raws:wheat flour>75,rye flour>75,honey>60,ginger>25,charcoal>40;days:1','392:200',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=gingerbread FUEL=charcoal>',2,25,0),(787,781,'raws:wheat flour>75,rye flour>75,honey>60,ginger>25,coal>20;days:1','392:200',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=gingerbread FUEL=coal>',2,25,0),(788,781,'raws:raspberries>300,sugar>150,charcoal>25;days:1;tools:pot','393:250',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=raspberry_jam FUEL=charcoal>',2,25,0),(789,781,'raws:raspberries>300,sugar>150,coal>15;days:1;tools:pot','393:250',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=raspberry_jam FUEL=coal>',2,25,0),(790,117,'raws:gingerbread>100,raisins>25,raspberry jam>50;days:1;tools:knife','394:125',1,'<CANTR REPLACE NAME=project_assembling_food RAW=piernik IN=gingerbread>',2,25,0),(791,242,'raws:pineapples>400,honey>150,beer>350;days:1;tools:bucket,knife','395:800',1,'<CANTR REPLACE NAME=project_making_tepache RAW=tepache>',3,25,0),(792,781,'raws:wine>400,sugar>150,clove>20,lemons>30,charcoal>40;days:1;tools:pot','396:600',1,'<CANTR REPLACE NAME=project_mulling_fueled RAW=wine FUEL=charcoal>',3,25,0),(793,781,'raws:wine>400,sugar>150,clove>20,lemons>30,coal>25;days:1;tools:pot','396:600',1,'<CANTR REPLACE NAME=project_mulling_fueled RAW=wine FUEL=coal>',3,25,0),(794,247,'raws:pineapples>400,honey>150,beer>350;days:1;tools:bucket,knife','395:800',1,'<CANTR REPLACE NAME=project_making_tepache RAW=tepache>',3,25,0),(795,223,'objects:key>1;days:0.1;tools:hammer','10:10',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=iron OBJECT=key>',2,23,0),(796,769,'days:1;location_state:sailing_moving;location_areatype:sea','13:15',1,'<CANTR REPLACE NAME=project_extracting RAW=salt>',0,0,1),(797,8,'raws:biodiesel>100;days:1;ignorerawtools','25:12800',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=potatoes FUEL=biodiesel>',1,1,0),(798,112,'raws:cotton yarn>500;days:1','397:480',1,'<CANTR REPLACE NAME=project_weaving RAW=denim>',2,26,0),(799,242,'raws:wheat>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=wheat>',1,25,0),(800,242,'raws:rye>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=rye>',1,25,0),(801,242,'raws:sorghum>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=sorghum>',1,25,0),(802,242,'raws:corn>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=corn>',1,25,0),(803,247,'raws:wheat>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=wheat>',1,25,0),(804,247,'raws:rye>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=rye>',1,25,0),(805,247,'raws:sorghum>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=sorghum>',1,25,0),(806,247,'raws:corn>250,water>100;days:1;tools:bucket','366:250',1,'<CANTR REPLACE NAME=project_malting RAW=corn>',1,25,0),(807,242,'raws:baked potatoes>400,water>200;days:1;tools:bucket','398:200',1,'<CANTR REPLACE NAME=project_making_food RAW=potato_mash>',1,25,0),(808,247,'raws:baked potatoes>400,water>200;days:1;tools:bucket','398:200',1,'<CANTR REPLACE NAME=project_making_food RAW=potato_mash>',1,25,0),(809,631,'raws:amazake>450,coal>50;days:3','334:200',0,'Fermenting & distilling amazake (coal)',1,25,1),(810,631,'raws:amazake>450,wood>80;days:3','334:200',0,'Fermenting & distilling amazake (wood)',1,25,1),(811,631,'raws:apple juice>400,coal>50;days:3','334:200',0,'Fermenting & distilling apple juice (coal)',1,25,1),(812,631,'raws:apple juice>400,wood>80;days:3','334:200',0,'Fermenting & distilling apple juice (wood)',1,25,1),(813,631,'raws:grape juice>400,coal>50;days:3','334:200',0,'Fermenting & distilling grape juice (coal)',1,25,1),(814,631,'raws:grape juice>400,wood>80;days:3','334:200',0,'Fermenting & distilling grape juice (wood)',1,25,1),(815,631,'raws:potato mash>400,coal>50;days:3','334:200',0,'Fermenting & distilling potato mash (coal)',1,25,1),(816,631,'raws:potato mash>400,wood>80;days:3','334:200',0,'Fermenting & distilling potato mash (wood)',1,25,1),(817,631,'raws:wort>400,coal>50;days:3','334:200',0,'Fermenting & distilling wort (coal)',1,25,1),(818,631,'raws:wort>400,wood>80;days:3','334:200',0,'Fermenting & distilling wort (wood)',1,25,1),(819,632,'raws:amazake>450,coal>50;days:3','334:200',0,'Fermenting & distilling amazake (coal)',1,25,1),(820,632,'raws:amazake>450,wood>80;days:3','334:200',0,'Fermenting & distilling amazake (wood)',1,25,1),(821,632,'raws:apple juice>400,coal>50;days:3','334:200',0,'Fermenting & distilling apple juice (coal)',1,25,1),(822,632,'raws:apple juice>400,wood>80;days:3','334:200',0,'Fermenting & distilling apple juice (wood)',1,25,1),(823,632,'raws:grape juice>400,coal>50;days:3','334:200',0,'Fermenting & distilling grape juice (coal)',1,25,1),(824,632,'raws:grape juice>400,wood>80;days:3','334:200',0,'Fermenting & distilling grape juice (wood)',1,25,1),(825,632,'raws:potato mash>400,coal>50;days:3','334:200',0,'Fermenting & distilling potato mash (coal)',1,25,1),(826,632,'raws:potato mash>400,wood>80;days:3','334:200',0,'Fermenting & distilling potato mash (wood)',1,25,1),(827,632,'raws:wort>400,coal>50;days:3','334:200',0,'Fermenting & distilling wort (coal)',1,25,1),(828,632,'raws:wort>400,wood>80;days:3','334:200',0,'Fermenting & distilling wort (wood)',1,25,1),(829,832,'raws:cotton yarn>120;days:1','91:115',1,'<CANTR REPLACE NAME=project_weaving RAW=cotton_cloth>',2,26,0),(830,832,'raws:cotton yarn>100;days:1','397:95',1,'<CANTR REPLACE NAME=project_weaving RAW=denim>',2,26,0),(831,832,'raws:hemp yarn>165;days:1','129:160',1,'<CANTR REPLACE NAME=project_weaving RAW=hemp_cloth>',2,26,0),(832,832,'raws:silk yarn>130;days:1','97:125',1,'<CANTR REPLACE NAME=project_weaving RAW=silk_cloth>',2,26,0),(833,832,'raws:wool yarn>80;days:1','301:75',1,'<CANTR REPLACE NAME=project_weaving RAW=wool_cloth>',2,26,0),(834,219,'raws:fur>1500,salt>500;days:1;tools:hide scraper','103:750',1,'<CANTR REPLACE NAME=project_curing RAW=fur>',3,26,0),(835,49,'raws:copper>280,tin>40,coal>160;days:1','267:200',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=bronze FUEL=coal>',6,24,0),(836,239,'raws:copper>350,tin>50,coal>200;days:1','267:200',1,'<CANTR REPLACE NAME=project_smelting_fueled RAW=bronze FUEL=coal>',6,24,0),(837,8,'raws:propane>50;days:1;ignorerawtools','25:12800',1,'<CANTR REPLACE NAME=project_harvesting_fueled RAW=potatoes FUEL=propane>',1,1,0),(838,208,'raws:wood>50;days:1','120:5',1,'<CANTR REPLACE NAME=project_lighting_fire>',1,25,1),(839,628,'days:1;location_state:sailing_floating;location_areatype:sea;ignorerawtools','76:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=cod>',1,17,0),(840,628,'days:1;location_state:sailing_floating;location_areatype:lake;ignorerawtools','155:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=pike>',1,17,0),(841,628,'days:1;location_state:sailing_floating;ignorerawtools','77:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=rainbow_trout>',1,17,0),(842,629,'days:1;location_state:sailing_floating;location_areatype:sea;ignorerawtools','76:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=cod>',1,17,0),(843,629,'days:1;location_state:sailing_floating;location_areatype:lake;ignorerawtools','155:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=pike>',1,17,0),(844,629,'days:1;location_state:sailing_floating;ignorerawtools','77:750',1,'<CANTR REPLACE NAME=project_net_fishing RAW=rainbow_trout>',1,17,0),(845,147,'raws:rice>1600,water>100,dried dung>1200;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming_fueled RAW=rice_paste FUEL=dried_dung>',2,25,0),(846,147,'raws:rice>1600,water>100,wood>600;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming_fueled RAW=rice_paste FUEL=wood>',2,25,0),(847,304,'raws:rice paste>720;days:1','405:700',1,'<CANTR REPLACE NAME=project_drying RAW=rice_paste>',1,4,1),(848,570,'raws:rice>1600,water>100,dried dung>1200;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming_fueled RAW=rice_paste FUEL=dried_dung>',2,25,0),(849,570,'raws:rice>1600,water>100,wood>600;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming_fueled RAW=rice_paste FUEL=wood>',2,25,0),(850,780,'raws:rice paste>1440;days:1','405:1400',1,'<CANTR REPLACE NAME=project_drying RAW=rice_paper>',1,4,1),(851,178,'raws:dried cod>200;days:1','407:200',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=cod>',2,25,0),(852,178,'raws:dried pike>200;days:1','407:200',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=pike>',2,25,0),(853,178,'raws:dried salmon>200;days:1','407:200',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=salmon>',2,25,0),(854,178,'raws:dried trout>200;days:1','407:200',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=rainbow_trout>',2,25,0),(855,177,'raws:dried cod>100;days:1','407:100',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=cod>',2,25,0),(856,177,'raws:dried pike>100;days:1','407:100',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=pike>',2,25,0),(857,177,'raws:dried salmon>100;days:1','407:100',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=salmon>',2,25,0),(858,177,'raws:dried trout>100;days:1','407:100',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=fish_flour IN=rainbow_trout>',2,25,0),(859,242,'raws:corn>600,water>100;days:1;tools:bucket;ignorerawtools','33:150',1,'<CANTR REPLACE NAME=project_refining RAW=sugar>',1,25,0),(860,247,'raws:corn>600,water>100;days:1;tools:bucket','33:150',1,'<CANTR REPLACE NAME=project_refining RAW=sugar>',1,25,0),(861,1230,'raws:iron ore>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=dried_dung>',2,23,0),(862,1230,'raws:iron ore>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=fish_flour>',2,23,0),(863,1230,'raws:iron ore>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(864,1230,'raws:iron ore>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(865,1230,'raws:iron ore>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=dried_dung>',2,23,0),(866,1230,'raws:iron ore>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=fish_flour>',2,23,0),(867,1230,'raws:phosphorus>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_1 RAW=explosive_powder IN1=phosphorus>',2,23,0),(868,1230,'raws:salt>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=dried_dung>',2,23,0),(869,1230,'raws:salt>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=fish_flour>',2,23,0),(870,1230,'raws:salt>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=dried_dung>',2,23,0),(871,1230,'raws:salt>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=fish_flour>',2,23,0),(872,1230,'raws:salt>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=dried_dung>',2,23,0),(873,1230,'raws:salt>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=fish_flour>',2,23,0),(874,1231,'raws:iron ore>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=dried_dung>',2,23,0),(875,1232,'raws:iron ore>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=dried_dung>',2,23,0),(876,1231,'raws:iron ore>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=fish_flour>',2,23,0),(877,1232,'raws:iron ore>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=charcoal IN3=fish_flour>',2,23,0),(878,1231,'raws:iron ore>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(879,1232,'raws:iron ore>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(880,1231,'raws:iron ore>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(881,1232,'raws:iron ore>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=honey IN3=fish_flour>',2,23,0),(882,1231,'raws:iron ore>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=dried_dung>',2,23,0),(883,1232,'raws:iron ore>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=dried_dung>',2,23,0),(884,1231,'raws:iron ore>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=fish_flour>',2,23,0),(885,1232,'raws:iron ore>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=iron_ore IN2=sugar IN3=fish_flour>',2,23,0),(886,1231,'raws:phosphorus>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_1 RAW=explosive_powder IN1=phosphorus>',2,23,0),(887,1232,'raws:phosphorus>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_1 RAW=explosive_powder IN1=phosphorus>',2,23,0),(888,1231,'raws:salt>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=dried_dung>',2,23,0),(889,1232,'raws:salt>30,charcoal>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=dried_dung>',2,23,0),(890,1231,'raws:salt>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=fish_flour>',2,23,0),(891,1232,'raws:salt>30,charcoal>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=charcoal IN3=fish_flour>',2,23,0),(892,1231,'raws:salt>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=dried_dung>',2,23,0),(893,1232,'raws:salt>30,honey>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=dried_dung>',2,23,0),(894,1231,'raws:salt>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=fish_flour>',2,23,0),(895,1232,'raws:salt>30,honey>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=honey IN3=fish_flour>',2,23,0),(896,1231,'raws:salt>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=dried_dung>',2,23,0),(897,1232,'raws:salt>30,sugar>30,dried dung>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=dried_dung>',2,23,0),(898,1231,'raws:salt>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=fish_flour>',2,23,0),(899,1232,'raws:salt>30,sugar>30,fish flour>60;days:0.5;tools:mortar and pestle','406:60',1,'<CANTR REPLACE NAME=project_crushing_3 RAW=explosive_powder IN1=salt IN2=sugar IN3=fish_flour>',2,23,0),(900,208,'raws:rice>1600,water>100,wood>600;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming RAW=rice_paste>',2,25,0),(901,299,'raws:boerenkool>1000,salt>50;days:1','410:750',1,'<CANTR REPLACE NAME=project_making_sour RAW=sauerkraut IN=boerenkool>',2,25,0),(902,762,'raws:honey>400;days:1;ignorerawtools','256:40',1,'<CANTR REPLACE NAME=project_collecting_wax RAW=beeswax>',1,19,0),(903,0,'days:1;location_state:sailing_floating','154:750',1,'Fishing for salmon',1,17,0),(904,0,'days:1;location_state:sailing_floating','154:750',1,'Fishing for salmon',1,17,0),(905,266,'objects:key>1;days:0.1;tools:hammer','10:10',1,'<CANTR REPLACE NAME=project_reclaiming_object RAW=iron OBJECT=key>',2,23,0),(906,1246,'raws:grass>800;days:1','57:800',1,'<CANTR REPLACE NAME=project_drying_feed RAW=hay IN=grass>',2,1,0),(907,1246,'raws:wheat>3400;days:1','57:900',1,'<CANTR REPLACE NAME=project_drying_feed RAW=hay IN=wheat>',2,1,0),(908,1246,'raws:rye>1400;days:1','57:800',1,'<CANTR REPLACE NAME=project_drying_feed RAW=hay IN=rye>',2,1,0),(909,1246,'raws:sorghum>1600;days:1','57:800',1,'<CANTR REPLACE NAME=project_drying_feed RAW=hay IN=sorghum>',2,1,0),(910,1246,'raws:barley>2800;days:1','57:900',1,'<CANTR REPLACE NAME=project_drying_feed RAW=hay IN=barley>',2,1,0),(911,1243,'raws:spinage>900;days:1','409:800',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=spinage>',2,25,0),(912,1243,'raws:asparagus>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=asparagus>',2,25,0),(913,1243,'raws:carrots>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=carrots>',2,25,0),(914,1243,'raws:rice>1500;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=rice>',2,25,0),(915,1243,'raws:potatoes>1800;days:1','409:500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=potatoes>',2,25,0),(916,1244,'raws:asparagus>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=asparagus>',2,25,0),(917,1244,'raws:potatoes>1800;days:1','409:500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=potatoes>',2,25,0),(918,1244,'raws:rice>1500;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=rice>',2,25,0),(919,1244,'raws:spinage>900;days:1','409:800',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=spinage>',2,25,0),(920,1244,'raws:carrots>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=carrots>',2,25,0),(921,1245,'raws:carrots>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=carrots>',2,25,0),(922,1245,'raws:potatoes>1800;days:1','409:500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=potatoes>',2,25,0),(923,1245,'raws:rice>1500;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=rice>',2,25,0),(924,1245,'raws:spinage>900;days:1','409:800',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=spinage>',2,25,0),(925,1245,'raws:asparagus>900;days:1','409:600',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=vegetable_feed IN=asparagus>',2,25,0),(926,301,'raws:meat>2000;days:1;tools:mixing bowl','408:800',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=meat_feed IN=meat>',2,25,0),(927,301,'raws:cod>1500;days:1;tools:mixing bowl','408:1500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=meat_feed IN=cod>',2,25,0),(928,301,'raws:salmon>1500;days:1;tools:mixing bowl','408:1500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=meat_feed IN=salmon>',2,25,0),(929,301,'raws:pike>1500;days:1;tools:mixing bowl','408:1500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=meat_feed IN=pike>',2,25,0),(930,301,'raws:rainbow trout>1500;days:1;tools:mixing bowl','408:1500',1,'<CANTR REPLACE NAME=project_mixing_feed RAW=meat_feed IN=rainbow_trout>',2,25,0),(931,304,'raws:roses>100;days:1','414:100',1,'<CANTR REPLACE NAME=project_drying RAW=roses>',1,25,1),(932,304,'raws:buttercups>100;days:1','415:100',1,'<CANTR REPLACE NAME=project_drying RAW=buttercups>',1,25,1),(933,304,'raws:daisies>100;days:1','416:100',1,'<CANTR REPLACE NAME=project_drying RAW=daisies>',1,25,1),(934,780,'raws:roses>250;days:1','414:250',1,'<CANTR REPLACE NAME=project_drying RAW=roses>',1,25,1),(935,780,'raws:buttercups>250;days:1','415:250',1,'<CANTR REPLACE NAME=project_drying RAW=buttercups>',1,25,1),(936,780,'raws:daisies>250;days:1','416:250',1,'<CANTR REPLACE NAME=project_drying RAW=daisies>',1,25,1),(937,0,'raws:dry arnica>100,dry hamamelis>100,bergamot oil>50;days:1;tools:mortar and pestle','224:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=healing_liquid_(a) IN1=arnica IN2=hamamelis IN3=bergamot_oil>',2,25,0),(938,277,'raws:dry roses>100,dry tea>100,aloe vera>50;days:1;tools:mortar and pestle','417:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(a) IN1=roses IN2=tea_leaves IN3=aloe_vera>',2,25,0),(939,277,'raws:dry lavender>100,dry clove>100,water>50;days:1;tools:mortar and pestle','418:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(b) IN1=lavender IN2=clove IN3=water>',2,25,0),(940,277,'raws:dry buttercups>100,dry daisies>100,camphor oil>50;days:1;tools:mortar and pestle','419:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(c) IN1=buttercups IN2=daisies IN3=camphor_oil>',2,25,0),(941,0,'raws:dry buttercups>100,dry daisies>100,camphor oil>50;days:1;tools:mortar and pestle','419:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(c) IN1=buttercups IN2=daisies IN3=camphor_oil>',2,25,0),(942,277,'raws:copper>100,dry white willow>100,lemon juice>50;days:1;tools:mortar and pestle,file','420:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(d) IN1=copper IN2=white_willow IN3=lemon_juice>',2,25,0),(943,277,'raws:chromium>100,dry peppermint>100,lemon juice>50;days:1;tools:mortar and pestle,file','421:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(e) IN1=chromium IN2=peppermint IN3=lemon_juice>',2,25,0),(944,277,'raws:cobalt>100,dry yarrow>100,lemon juice>50;days:1;tools:mortar and pestle,file','422:250',1,'<CANTR REPLACE NAME=project_crushing_mixing_3 RAW=poison_(f) IN1=cobalt IN2=yarrow IN3=lemon_juice>',2,25,0),(945,147,'raws:wood>50;days:1','120:5',0,'<CANTR REPLACE NAME=project_lighting_bonfire>',1,25,1),(946,116,'raws:wood>15;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=wood>',0,0,1),(947,116,'raws:propane>2;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=propane>',0,0,1),(948,116,'raws:charcoal>10;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=charcoal>',0,0,1),(949,116,'raws:coal>25;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=coal>',0,0,1),(950,116,'raws:petrol>15;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=petrol>',0,0,1),(951,116,'raws:biodiesel>2;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=biodiesel>',0,0,1),(952,116,'raws:dried dung>100;days:1','120:1',1,'<CANTR REPLACE NAME=project_lighting_lighthouse FUEL=dried_dung>',0,0,1),(953,242,'raws:maple sap>50,water>20;days:1;tools:bucket','423:70',1,'<CANTR REPLACE NAME=project_purify RAW=maple_syrup>',1,25,0),(954,247,'raws:maple sap>50,water>20;days:1;tools:bucket','423:70',1,'<CANTR REPLACE NAME=project_purify RAW=maple_syrup>',1,25,0),(955,1322,'raws:beef>3000;days:1;tools:knife,meat grinder','426:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=beef_sausage IN=beef>',2,25,0),(956,1322,'raws:mutton>3000;days:1;tools:knife,meat grinder','424:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=lamb_sausage IN=mutton>',2,25,0),(957,1322,'raws:pork>3000;days:1;tools:knife,meat grinder','425:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=pork_sausage IN=pork>',2,25,0),(958,1322,'raws:beef>3000;days:1;tools:knife,meat grinder','428:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=ground_meat IN=beef>',2,25,0),(959,1322,'raws:mutton>3000;days:1;tools:knife,meat grinder','428:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=ground_meat IN=mutton>',2,25,0),(960,1322,'raws:pork>3000;days:1;tools:knife,meat grinder','428:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=ground_meat IN=pork>',2,25,0),(961,782,'raws:lamb sausage>700,couscous>100,lemon juice>20;days:1;tools:tajine','429:800',1,'<CANTR REPLACE NAME=project_cooking RAW=merguez>',2,25,0),(962,782,'raws:beef>500,pork>400,carrots>300,potatoes>300;tools:knife,tajine;days:1','439:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=cocido>',2,25,0),(963,117,'raws:beef sausage>850,pickles>50,dill>20;days:1','431:900',1,'<CANTR REPLACE NAME=project_assembling_food RAW=liverwurst IN=beef_sausage>',2,25,0),(964,1323,'raws:pork sausage>1000,rice>1000,thyme>30,coal>40;tools:pot,hibachi;days:1','430:2000',1,'<CANTR REPLACE NAME=project_cooking RAW=andouille>',2,25,0),(965,1323,'raws:beef sausage>800,mashed potatoes>200,coal>30;tools:pot,hibachi;days:1','432:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=bangers_and_mash>',2,25,0),(966,1323,'raws:pork sausage>500,olives>100,coal>25;tools:pot,hibachi;days:1','433:600',1,'<CANTR REPLACE NAME=project_cooking RAW=mortadella>',2,25,0),(967,1323,'raws:mutton>800,coal>30;tools:knife,hibachi;days:1','438:800',1,'<CANTR REPLACE NAME=project_cooking RAW=shawarma>',2,25,0),(968,1323,'raws:beef>800,large bones>500,lemons>20,coal>30;tools:pot,hibachi;days:1','444:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=menudo>',2,25,0),(969,1323,'raws:pork>800,large bones>500,corn>200,coal>30;tools:pot,hibachi;days:1','445:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=pozole>',2,25,0),(970,1323,'raws:pork sausage>600,beef sausage>500,sauerkraut>200,mushrooms>100,tomatos>100,coal>30;days:1;tools:pot,hibachi','447:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=bigos>',2,25,0),(971,1334,'raws:beef>1500,propane>40;tools:knife;days:1','448:1500',1,'<CANTR REPLACE NAME=project_cooking_generic RAW=brisket>',2,25,0),(972,1334,'raws:pork>1500,propane>40;tools:knife;days:1','449:1500',1,'<CANTR REPLACE NAME=project_cooking_generic RAW=shortribs>',2,25,0),(973,1334,'raws:mutton>1500,propane>40;tools:knife;days:1','450:1500',1,'<CANTR REPLACE NAME=project_cooking_generic RAW=rack_of_lamb>',2,25,0),(974,1334,'raws:pork>5000,pineapples>100,apples>50,bananas>100,propane>30;days:2;tools:knife','457:5000',0,'<CANTR REPLACE NAME=project_cooking_generic RAW=hog_roast>',2,25,0),(975,279,'raws:tomatos>400,onions>200,clove>30,thyme>20,lemons>20,garlic>40;days:1','451:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=barbeque_sauce>',2,25,0),(976,1230,'raws:tomatos>400,onions>200,clove>30,thyme>20,lemons>20,garlic>40;days:1','451:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=barbeque_sauce>',2,25,0),(977,1231,'raws:tomatos>400,onions>200,clove>30,thyme>20,lemons>20,garlic>40;days:1','451:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=barbeque_sauce>',2,25,0),(978,1232,'raws:tomatos>400,onions>200,clove>30,thyme>20,lemons>20,garlic>40;days:1','451:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=barbeque_sauce>',2,25,0),(979,301,'raws:peppermint>50,sugar>30,lemons>20;tools:knife,mixing bowl;days:1','452:100',1,'<CANTR REPLACE NAME=project_mixing_food RAW=mint_jelly>',2,25,0),(980,301,'raws:eggs>80,lemon juice>20,garlic>20;days:1;tools:whisk,mixing bowl','456:120',1,'<CANTR REPLACE NAME=project_mixing_food RAW=aioli>',2,25,0),(981,301,'raws:apples>100;tools:knife,mixing bowl;days:1','453:100',1,'<CANTR REPLACE NAME=project_preparing_food RAW=applesauce>',2,25,0),(982,781,'raws:beef>600,pastry dough>200,clove>20,coal>35;tools:knife;days:1','442:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=beef_wellington FUEL=coal>',4,25,0),(983,781,'raws:beef>600,pastry dough>200,clove>20,charcoal>35;tools:knife;days:1','442:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=beef_wellington FUEL=charcoal>',4,25,0),(984,781,'raws:ground meat>570,eggs>30,coal>35;tools:pot;days:1','436:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meatloaf FUEL=coal>',4,25,0),(985,781,'raws:ground meat>570,eggs>30,charcoal>35;tools:pot;days:1','436:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=meatloaf FUEL=charcoal>',4,25,0),(986,783,'raws:pork sausage>700,wheat>300,propane>20;tools:pot,campstove;days:1','435:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=black_pudding>',3,25,0),(987,783,'raws:mushrooms>200,carrots>300,wheat flour>100,propane>10;tools:knife,pot,campstove;days:1','491:500',1,'<CANTR REPLACE NAME=project_cooking RAW=mushroom_soup>',2,25,0),(988,299,'raws:pork>1200,salt>100;days:1','443:1000',1,'<CANTR REPLACE NAME=project_salting RAW=ham>',2,25,0),(989,147,'raws:ground meat>800,dried dung>200;days:1;tools:cooking stone','437:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=keema FUEL=dried_dung>',2,25,0),(990,570,'raws:ground meat>800,dried dung>200;days:1;tools:cooking stone','437:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=keema FUEL=dried_dung>',2,25,0),(991,214,'raws:large bones>2000,coal>100;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=large_bones FUEL=coal>',4,0,0),(992,104,'raws:large bones>2000,coal>100;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=large_bones FUEL=coal>',4,0,0),(993,214,'raws:small bones>2000,coal>100;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=small_bones FUEL=coal>',4,0,0),(994,104,'raws:small bones>2000,coal>100;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=small_bones FUEL=coal>',4,0,0),(995,214,'raws:large bones>2000,charcoal>25;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=large_bones FUEL=charcoal>',4,0,0),(996,104,'raws:large bones>2000,charcoal>25;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=large_bones FUEL=charcoal>',4,0,0),(997,214,'raws:small bones>2000,charcoal>25;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=small_bones FUEL=charcoal>',4,0,0),(998,104,'raws:small bones>2000,charcoal>25;days:1','458:200',1,'<CANTR REPLACE NAME=project_kiln_burning_fueled RAW=bone_ash IN=small_bones FUEL=charcoal>',4,0,0),(999,223,'raws:bone ash>400,stone>200,clay>200;days:1;tools:hammer,broom','459:400',1,'<CANTR REPLACE NAME=project_mixing_clay RAW=bone_china>',2,4,0),(1000,782,'raws:poultry>500,wine>100,thyme>20;tools:knife,tajine;days:1','440:600',1,'<CANTR REPLACE NAME=project_making_food RAW=coq-au-vin>',2,25,0),(1001,1323,'raws:poultry>500,pastry dough>200,small bones>300,coal>30;tools:pot,hibachi;days:1','446:1000',1,'<CANTR REPLACE NAME=project_cooking_generic RAW=matzoh_balls_soup>',2,25,0),(1002,0,'raws:beef>1500,propane>40;tools:knife;days:1','448:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=brisket>',2,25,0),(1003,1334,'raws:poultry>1000,propane>30;tools:knife;days:1','461:1000',1,'<CANTR REPLACE NAME=project_cooking_generic RAW=wings>',2,25,0),(1004,0,'raws:apples>400;days:1','359:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=apples>',2,25,0),(1005,519,'raws:olives>2000;days:1','242:400',1,'<CANTR REPLACE NAME=project_press_crushing RAW=olives>',2,25,0),(1006,147,'raws:pasta>950,olive oil>50,dried dung>200;tools:pot;days:1','462:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=plain_pasta FUEL=dried_dung>',2,25,0),(1007,0,'raws:sorghum flour>2450,cornmeal>150,eggs>200,olive oil>85,water>400,propane>15;days:1;tools:campstove,doughroller,knife,pot','463:3000',1,'<CANTR REPLACE NAME=project_cooking RAW=gourmet_pasta>',2,25,0),(1008,0,'raws:wheat flour>1300,barley>1300,eggs>200,olive oil>85,water>400,propane>15;days:1;tools:campstove,doughroller,knife,pot,mortar and pestle','463:3000',1,'<CANTR REPLACE NAME=project_cooking RAW=gourmet_pasta>',2,25,0),(1009,0,'raws:rye flour>2600,eggs>200,olive oil>85,water>400,propane>15;days:1;tools:campstove,doughroller,knife,pot','463:3000',1,'<CANTR REPLACE NAME=project_cooking RAW=gourmet_pasta>',2,25,0),(1010,117,'raws:rice flour>1700;tools:doughroller,knife;days:1','464:1500',1,'<CANTR REPLACE NAME=project_making_gen RAW=rice_noodles>',2,25,0),(1011,24,'raws:pasta>750,stewed tomatoes>250,meat>400,cheese>100,wood>50;tools:casserole dish;days:1','465:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=lasagna>',2,25,0),(1012,120,'raws:pasta>750,stewed tomatoes>250,meat>400,cheese>100,coal>50;tools:casserole dish;days:1','465:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=lasagna FUEL=coal>',2,25,0),(1013,308,'raws:pasta>750,stewed tomatoes>250,meat>400,cheese>100,propane>20;tools:casserole dish;days:1','465:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=lasagna>',2,25,0),(1014,783,'raws:pasta>900,cheese>100,propane>20;tools:pot,campstove;days:1','466:1000',1,'<CANTR REPLACE NAME=project_making_food RAW=macaroni_and_cheese>',2,25,0),(1015,783,'raws:pasta>500,stewed tomatoes>250,propane>15;tools:pot,campstove;days:1','467:750',1,'<CANTR REPLACE NAME=project_making_food RAW=spaghetti_with_tomato_sauce>',2,25,0),(1016,147,'raws:rice noodles>800,poultry>500,mushrooms>200,dried dung>300;tools:pot;days:1','468:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=poultry_noodle_wok FUEL=dried_dung>',2,25,0),(1017,1323,'raws:rice noodles>500,pork>500,spinage>500,coal>30;tools:pot,hibachi;days:1','469:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=pork_noodle_wok>',2,25,0),(1018,783,'raws:rice noodles>1000,broth>400,seaweed>100,propane>20;tools:pot,campstove;days:1','470:1500',1,'<CANTR REPLACE NAME=project_making_food RAW=ramen>',2,25,0),(1019,781,'raws:pasta>750,olives>150,onions>100,thyme>20,coal>40;tools:pot;days:1','472:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pasta_with_vegetables FUEL=coal>',2,25,0),(1020,1323,'raws:pasta>700,ground meat>500,coal>30;tools:pot,hibachi;days:1','473:1200',1,'<CANTR REPLACE NAME=project_cooking RAW=pasta_with_meat>',2,25,0),(1021,117,'raws:plain pasta>750,nuts>200,basil>30,garlic>20;tools:mortar and pestle;days:1','474:1000',1,'<CANTR REPLACE NAME=project_making_gen RAW=pasta_with_pesto>',2,25,0),(1022,782,'raws:pasta>750,honey>300,cheese curds>200;days:1;tools:tajine','475:1250',1,'<CANTR REPLACE NAME=project_cooking RAW=honey_pasta>',2,25,0),(1023,462,'raws:lamb sausage>500,wood>50;days:1','486:500',1,'<CANTR REPLACE NAME=project_smoking RAW=sujuk>',2,25,0),(1024,0,'raws:meat>400,wood>30;days:1','159:275',1,'<CANTR REPLACE NAME=project_smoking RAW=meat>',2,25,0),(1025,462,'raws:pork sausage>500,wood>50;days:1','487:500',1,'<CANTR REPLACE NAME=project_smoking RAW=linguica>',2,25,0),(1026,462,'raws:beef sausage>500,wood>50;days:1','488:500',1,'<CANTR REPLACE NAME=project_smoking RAW=rookworst>',2,25,0),(1027,1323,'raws:potatoes>600,boerenkool>200,rookworst>200,coal>30;tools:knife,pot,hibachi;days:1','489:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=stamppot>',2,25,0),(1028,1323,'raws:sujuk>200,carrots>1000,coal>40,ginger>20,clove>20;tools:knife,pot;days:1','490:1200',1,'<CANTR REPLACE NAME=project_cooking RAW=lamb_curry>',2,25,0),(1029,783,'raws:beef>800,wine>200,tarragon>20,propane>15;tools:knife,pot,campstove;days:1','441:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=chateaubriand>',2,25,0),(1030,0,'raws:wheat flour>200,dried dung>200;days:0.25;tools:cooking stone','110:200',0,'<CANTR REPLACE NAME=project_baking_fueled RAW=pancake FUEL=dried_dung>',2,25,0),(1031,147,'raws:mutton>500,barley>200,large bones>100,dried dung>300;tools:pot;days:1','492:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=haggis FUEL=dried_dung>',2,25,0),(1032,147,'raws:mutton>500,barley>200,large bones>100,wood>150;tools:pot;days:1','492:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=haggis FUEL=wood>',2,25,0),(1033,570,'raws:mutton>500,barley>200,large bones>100,dried dung>300;tools:pot;days:1','492:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=haggis FUEL=dried_dung>',2,25,0),(1034,570,'raws:mutton>500,barley>200,large bones>100,wood>150;tools:pot;days:1','492:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=haggis FUEL=wood>',2,25,0),(1035,117,'raws:cheese>370,eggs>30,strawberries>100;tools:pot;days:1','493:500',1,'<CANTR REPLACE NAME=project_making_gen RAW=strawberry_cheesecake>',2,25,0),(1036,1322,'raws:poultry>3000;days:1;tools:knife,meat grinder','427:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=poultry_sausage IN=poultry>',2,25,0),(1037,1322,'raws:poultry>3000;days:1;tools:knife,meat grinder','428:3000',1,'<CANTR REPLACE NAME=project_grinding_fish RAW=ground_meat IN=poultry>',2,25,0),(1038,1323,'raws:poultry sausage>600,steamed rice>200,ginger>20,coal>25;tools:pot;days:1','434:800',1,'<CANTR REPLACE NAME=project_cooking RAW=yun_cheong>',2,25,0),(1039,0,'raws:rice>1600,water>100,dried dung>1200;days:1;tools:pot','404:1500',1,'<CANTR REPLACE NAME=project_forming_fueled RAW=rice_paste FUEL=dried_dung>',2,25,0),(1040,0,'raws:rice>1000,seaweed>800,rainbow trout>300;days:1;tools:wooden bowl,knife,makisu','117:2100',1,'<CANTR REPLACE NAME=project_making_gen RAW=sushi_(fish)>',4,25,0),(1041,147,'raws:sugar>700,dried dung>300;tools:pot;days:1','495:700',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=hard_candy FUEL=dried_dung>',2,25,0),(1042,147,'raws:sugar>700,wood>100;tools:pot;days:1','495:700',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=hard_candy FUEL=wood>',2,25,0),(1043,147,'raws:maple syrup>200,dried dung>100;tools:pot;days:1','496:200',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=maple_sugar_candy FUEL=dried_dung>',2,25,0),(1044,147,'raws:maple syrup>200,wood>50;tools:pot;days:1','496:200',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=maple_sugar_candy FUEL=wood>',2,25,0),(1045,117,'raws:honey>1200,cornmeal>300;days:1','497:1500',1,'<CANTR REPLACE NAME=project_making_gen RAW=lokum>',2,25,0),(1046,783,'raws:sugar>250,ginger>500,propane>20;tools:pot,knife,campstove;days:1','498:750',1,'<CANTR REPLACE NAME=project_making_food RAW=candied_ginger>',3,25,0),(1047,783,'raws:sugar>450,lemon juice>50,propane>15;tools:pot,campstove;days:1','499:500',1,'<CANTR REPLACE NAME=project_making_food RAW=lemon_drops>',3,25,0),(1048,147,'raws:honey>800,nuts>500,milk>200,dried dung>300;tools:cooking stone;days:1','500:1500',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=nut_brittle FUEL=dried_dung>',2,25,0),(1049,570,'raws:honey>800,nuts>500,milk>200,dried dung>300;tools:cooking stone;days:1','500:1500',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=nut_brittle FUEL=dried_dung>',2,25,0),(1050,147,'raws:honey>800,nuts>500,milk>200,wood>100;tools:cooking stone;days:1','500:1500',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=nut_brittle FUEL=wood>',2,25,0),(1051,570,'raws:honey>800,nuts>500,milk>200,wood>100;tools:cooking stone;days:1','500:1500',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=nut_brittle FUEL=wood>',2,25,0),(1052,570,'raws:sugar>700,dried dung>300;tools:pot;days:1','495:700',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=hard_candy FUEL=dried_dung>',2,25,0),(1053,570,'raws:sugar>700,wood>100;tools:pot;days:1','495:700',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=hard_candy FUEL=wood>',2,25,0),(1054,570,'raws:maple syrup>200,dried dung>100;tools:pot;days:1','496:200',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=maple_sugar_candy FUEL=dried_dung>',2,25,0),(1055,570,'raws:maple syrup>200,wood>50;tools:pot;days:1','496:200',1,'<CANTR REPLACE NAME=project_making_food_fueled RAW=maple_sugar_candy FUEL=wood>',2,25,0),(1056,24,'raws:pumpkin>700,pastry dough>270,eggs>30,clove>20,wood>30;days:1;tools:knife','501:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=pumpkin_pie>',2,25,0),(1057,120,'raws:pumpkin>700,pastry dough>270,eggs>30,clove>20,coal>30;days:1;tools:knife','501:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_pie FUEL=coal>',2,25,0),(1058,0,'raws:pumpkin>700,pastry dough>200,eggs>100,clove>20,charcoal>50;days:1;tools:knife','501:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_pie FUEL=charcoal>',2,25,0),(1059,308,'raws:pumpkin>700,pastry dough>270,eggs>30,clove>20,propane>20;days:1;tools:knife','501:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=pumpkin_pie>',2,25,0),(1060,120,'raws:pumpkin>700,pastry dough>270,eggs>30,clove>20,charcoal>50;days:1;tools:knife','501:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_pie FUEL=charcoal>',2,25,0),(1061,147,'raws:pumpkin>1000,carrots>500,dried dung>500;days:1;tools:pot','502:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_soup FUEL=dried_dung>',2,25,0),(1062,147,'raws:pumpkin>1000,carrots>500,wood>200;days:1;tools:pot','502:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_soup FUEL=wood>',2,25,0),(1063,570,'raws:pumpkin>1000,carrots>500,dried dung>500;days:1;tools:pot','502:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_soup FUEL=dried_dung>',2,25,0),(1064,570,'raws:pumpkin>1000,carrots>500,wood>200;days:1;tools:pot','502:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_soup FUEL=wood>',2,25,0),(1065,782,'raws:pumpkin>500,beef>500,potatoes>500,sage>30;days:1;tools:knife','503:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=pumpkin_stew>',2,25,0),(1066,781,'raws:pumpkin>500,rice>500,coconuts>100,ginger>30,coal>40;days:1;tools:pot,knife','504:1100',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_curry FUEL=coal>',4,25,0),(1067,781,'raws:pumpkin>500,rice>500,coconuts>100,ginger>30,charcoal>40;days:1;tools:pot,knife','504:1100',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pumpkin_curry FUEL=charcoal>',4,25,0),(1068,251,'raws:pumpkin>750,salt>50,dried dung>150;days:1','505:750',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pumpkin_seeds FUEL=dried_dung>',2,25,0),(1069,251,'raws:pumpkin>750,salt>50,wood>50;days:1','505:750',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pumpkin_seeds FUEL=wood>',2,25,0),(1070,24,'raws:spinage>870,pastry dough>600,eggs>30,wood>60;days:1;tools:casserole dish','506:1500',1,'<CANTR REPLACE NAME=project_baking RAW=spinach_pie>',2,25,0),(1071,120,'raws:spinage>870,pastry dough>600,eggs>30,coal>40;days:1;tools:casserole dish','506:1500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=spinach_pie FUEL=coal>',2,25,0),(1072,120,'raws:spinage>870,pastry dough>600,eggs>30,charcoal>40;days:1;tools:casserole dish','506:1500',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=spinach_pie FUEL=charcoal>',2,25,0),(1073,308,'raws:spinage>870,pastry dough>600,eggs>30,propane>20;days:1;tools:casserole dish','506:1500',1,'<CANTR REPLACE NAME=project_baking RAW=spinach_pie>',2,25,0),(1074,783,'raws:milk>500,onions>250,propane>10;tools:knife,pot,campstove;days:1','507:750',1,'<CANTR REPLACE NAME=project_making_food RAW=cream_of_onion_soup>',2,25,0),(1075,783,'raws:coffee>500,sugar>200,milk>100,propane>20;tools:pot,campstove;days:1','508:800',1,'<CANTR REPLACE NAME=project_making_food RAW=coffee_candy>',3,25,0),(1076,770,'raws:wine>400;days:20','509:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=champagne>',0,25,1),(1077,770,'raws:potato spirit>300,dill>40,peppermint>40;days:20','510:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=akvavit>',0,25,1),(1079,618,'raws:molasses>400,coal>50;days:3','514:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=rum FUEL=coal>',0,25,1),(1080,242,'raws:rum>200,water>400,sugar>50,lemon juice>50;days:1;tools:bucket','513:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=grog>',1,25,0),(1082,770,'raws:coffee beans>300,sugar>200,rice spirit>100;days:20','515:250',0,'<CANTR REPLACE NAME=project_fermenting RAW=coffee_liqueur>',1,25,1),(1083,770,'raws:grain spirit>400,sugar>300,lemons>100;days:20','517:600',0,'<CANTR REPLACE NAME=project_fermenting RAW=limoncello>',1,0,1),(1085,783,'raws:sugar>800,water>200,propane>15;tools:pot,campstove;days:1','518:500',1,'<CANTR REPLACE NAME=project_cooking RAW=molasses>',1,25,0),(1086,519,'raws:nuts>500;days:1','519:500',1,'<CANTR REPLACE NAME=project_press_crushing RAW=nuts>',1,25,0),(1087,770,'raws:crushed nuts>250,brandy>50,grain spirit>100;days:20','511:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=amaretto>',0,25,1),(1088,519,'raws:blueberries>300;days:1','476:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=blueberries>',2,25,0),(1089,519,'raws:blackberries>300;days:1','477:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=blackberries>',2,25,0),(1090,519,'raws:raspberries>300;days:1','478:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=raspberries>',2,25,0),(1091,519,'raws:strawberries>300;days:1','479:200',1,'<CANTR REPLACE NAME=project_press_crushing RAW=strawberries>',2,25,0),(1092,770,'raws:blueberry juice>400;days:20','482:200',0,'<CANTR REPLACE NAME=project_fermenting RAW=blueberry_wine>',0,0,1),(1093,770,'raws:blackberry juice>400;days:20','483:200',0,'<CANTR REPLACE NAME=project_fermenting RAW=blackberry_wine>',0,0,1),(1094,770,'raws:raspberry juice>400;days:20','484:200',0,'<CANTR REPLACE NAME=project_fermenting RAW=raspberry_wine>',0,0,1),(1095,770,'raws:strawberry juice>400;days:20','485:200',0,'<CANTR REPLACE NAME=project_fermenting RAW=strawberry_wine>',0,0,1),(1096,242,'raws:coconuts>250,water>250;tools:hammer;days:1','480:300',1,'<CANTR REPLACE NAME=project_making_gen RAW=palm_nectar>',3,25,0),(1097,770,'raws:palm nectar>400;days:20','481:350',0,'<CANTR REPLACE NAME=project_fermenting RAW=palm_wine>',0,0,1),(1098,618,'raws:molasses>400,wood>80;days:3','514:150',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=rum FUEL=wood>',0,25,1),(1099,242,'raws:ginger>300,sugar>100,water>100;days:1;tools:bucket','520:500',1,'<CANTR REPLACE NAME=project_making_food RAW=ginger_syrup>',3,25,0),(1100,770,'raws:ginger syrup>400;days:10','512:300',0,'<CANTR REPLACE NAME=project_fermenting RAW=ginger_beer>',0,25,1),(1101,770,'raws:rum>200,milk>400;days:20','516:600',0,'<CANTR REPLACE NAME=project_fermenting RAW=cream_liqueur>',0,25,1),(1102,247,'raws:rum>200,water>400,sugar>50,lemon juice>50;days:1;tools:bucket','513:600',1,'<CANTR REPLACE NAME=project_mixing_food RAW=grog>',1,25,0),(1103,247,'raws:coconuts>250,water>250;tools:hammer;days:1','480:300',1,'<CANTR REPLACE NAME=project_making_gen RAW=palm_nectar>',3,25,0),(1104,247,'raws:ginger>300,sugar>100,water>100;days:1;tools:bucket','520:500',1,'<CANTR REPLACE NAME=project_making_food RAW=ginger_syrup>',3,25,0),(1105,0,'raws:coffee beans>300,sugar>200,rice spirit>100,coal>50;days:20','515:250',0,'<CANTR REPLACE NAME=project_distilling_fueled RAW=coffee_liqueur FUEL=coal>',1,25,1),(1106,147,'raws:rice>400,meat>400,dried dung>400;days:1;tools:cooking stone','521:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_meat FUEL=dried_dung>',2,25,0),(1107,147,'raws:rice>400,meat>400,wood>200;days:1;tools:cooking stone','521:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_meat FUEL=wood>',2,25,0),(1108,147,'raws:rice>400,carrots>400,dried dung>400;days:1;tools:cooking stone','522:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_vegetables FUEL=dried_dung>',2,25,0),(1109,147,'raws:rice>400,carrots>400,wood>200;days:1;tools:cooking stone','522:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_vegetables FUEL=wood>',2,25,0),(1110,570,'raws:rice>400,meat>400,dried dung>400;days:1;tools:cooking stone','521:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_meat FUEL=dried_dung>',2,25,0),(1111,570,'raws:rice>400,meat>400,wood>200;days:1;tools:cooking stone','521:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_meat FUEL=wood>',2,25,0),(1112,570,'raws:rice>400,carrots>400,wood>200;days:1;tools:cooking stone','522:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_vegetables FUEL=wood>',2,25,0),(1113,570,'raws:rice>400,carrots>400,dried dung>400;days:1;tools:cooking stone','522:800',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=fried_rice_with_vegetables FUEL=dried_dung>',2,25,0),(1114,1323,'raws:steamed rice>900,olive oil>100,coal>40;tools:pot,hibachi;days:1','523:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=arancini>',2,25,0),(1115,301,'raws:cucumbers>500,milk>100,lemon juice>50;tools:knife,mixing bowl;days:1','527:650',1,'<CANTR REPLACE NAME=project_mixing_food RAW=cucumber_raita>',2,25,0),(1116,117,'raws:apples>300,nuts>150,clove>50;tools:knife;days:1','526:500',1,'<CANTR REPLACE NAME=project_making_gen RAW=apple_chutney>',2,25,0),(1117,301,'raws:papayas>250,carrots>200,fish sauce>50;tools:knife,mixing bowl;days:1','524:500',1,'<CANTR REPLACE NAME=project_mixing_food RAW=papaya_salad>',2,25,0),(1118,781,'raws:cheese curds>450,ginger>30,lemons>30,garlic>20,charcoal>30;days:1','528:500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=paneer_tikka FUEL=charcoal>',4,25,0),(1119,781,'raws:cheese curds>450,ginger>30,lemons>30,garlic>20,coal>30;days:1','528:500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=paneer_tikka FUEL=coal>',4,25,0),(1120,782,'raws:poultry>500,ginseng>100,garlic>50;tools:knife,tajine;days:1','529:650',1,'<CANTR REPLACE NAME=project_cooking RAW=ginseng_soup>',2,25,0),(1121,208,'raws:ginseng>400,water>250,wood>250;tools:pot;days:1','530:650',1,'<CANTR REPLACE NAME=project_brewing RAW=ginseng_tea>',2,25,0),(1122,24,'raws:ginseng>400,water>250,wood>50;tools:pot;days:1','530:650',1,'<CANTR REPLACE NAME=project_brewing RAW=ginseng_tea>',1,25,0),(1123,120,'raws:ginseng>400,water>250,charcoal>40;tools:pot;days:1','530:650',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=ginseng_tea FUEL=charcoal>',1,25,0),(1124,120,'raws:ginseng>400,water>250,coal>30;tools:pot;days:1','530:650',1,'<CANTR REPLACE NAME=project_brewing_fueled RAW=ginseng_tea FUEL=coal>',1,25,0),(1125,308,'raws:ginseng>400,water>250,propane>10;tools:pot;days:1','530:650',1,'<CANTR REPLACE NAME=project_brewing RAW=ginseng_tea>',1,25,0),(1126,117,'raws:salmon>500,salt>50,dill>20;days:1;tools:knife','531:500',1,'<CANTR REPLACE NAME=project_making_gen RAW=gravlax>',2,25,0),(1127,95,'raws:cod>500,lemon juice>50;tools:knife,makisu;days:1','532:500',1,'<CANTR REPLACE NAME=project_making_gen RAW=ceviche>',2,25,0),(1128,117,'raws:pike>520,onions>100,eggs>30;tools:knife,wooden bowl;days:1','533:650',1,'<CANTR REPLACE NAME=project_making_gen RAW=gefilte_fish>',2,25,0),(1129,1323,'raws:rainbow trout>500,lemons>30,tarragon>20,coal>30;tools:knife;days:1','534:500',1,'<CANTR REPLACE NAME=project_grilling RAW=rainbow_trout>',2,25,0),(1130,770,'raws:dried cod>300,salt>100;days:8','525:300',0,'<CANTR REPLACE NAME=project_fermenting_fish RAW=fish_sauce IN=dried_cod>',0,25,1),(1131,770,'raws:dried pike>300,salt>100;days:8','525:300',0,'<CANTR REPLACE NAME=project_fermenting_fish RAW=fish_sauce IN=dried_pike>',0,25,1),(1132,770,'raws:dried salmon>300,salt>100;days:8','525:300',0,'<CANTR REPLACE NAME=project_fermenting_fish RAW=fish_sauce IN=dried_salmon>',0,25,1),(1133,770,'raws:dried trout>300,salt>100;days:8','525:300',0,'<CANTR REPLACE NAME=project_fermenting_fish RAW=fish_sauce IN=dried_trout>',0,25,1),(1134,178,'raws:barley>1500;days:1','535:1500',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=barley>',2,25,0),(1135,178,'raws:polished rice>2000;days:1','536:2000',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=polished_rice>',2,25,0),(1136,177,'raws:barley>750;days:1','535:750',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=barley>',2,25,0),(1137,177,'raws:polished rice>1000;days:1','536:1000',1,'<CANTR REPLACE NAME=project_mill_grinding RAW=polished_rice>',2,25,0),(1138,301,'raws:wheat flour>1200;days:1;tools:sifter,mixing bowl','538:1000',1,'<CANTR REPLACE NAME=project_sifting_flour IN=wheat_flour>',2,25,0),(1139,301,'raws:rye flour>1200;days:1;tools:sifter,mixing bowl','538:1000',1,'<CANTR REPLACE NAME=project_sifting_flour IN=rye_flour>',2,25,0),(1140,301,'raws:sorghum flour>1200;days:1;tools:sifter,mixing bowl','538:1000',1,'<CANTR REPLACE NAME=project_sifting_flour IN=sorghum_flour>',2,25,0),(1141,301,'raws:barley flour>1200;tools:sifter,mixing bowl;days:1','538:1000',1,'<CANTR REPLACE NAME=project_sifting_flour IN=barley_flour>',2,25,0),(1142,301,'raws:rice flour>1200;days:1;tools:sifter,mixing bowl','538:1000',1,'<CANTR REPLACE NAME=project_sifting_flour IN=rice_flour>',2,25,0),(1143,117,'raws:cake flour>300,honey>200,nuts>100;days:1;tools:doughroller','539:600',1,'<CANTR REPLACE NAME=project_making_gen RAW=baklava>',2,25,0),(1144,24,'raws:cake flour>200,carrots>200,raisins>50,wood>40;days:1;tools>knife','541:450',1,'<CANTR REPLACE NAME=project_baking RAW=carrot_cake>',2,25,0),(1145,120,'raws:cake flour>200,carrots>200,raisins>50,coal>30;days:1;tools>knife','541:450',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=carrot_cake FUEL=coal>',2,25,0),(1146,120,'raws:cake flour>200,carrots>200,raisins>50,charcoal>30;days:1;tools>knife','541:450',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=carrot_cake FUEL=charcoal>',2,25,0),(1147,308,'raws:cake flour>200,carrots>200,raisins>50,propane>20;days:1;tools>knife','541:450',1,'<CANTR REPLACE NAME=project_baking RAW=carrot_cake>',2,25,0),(1148,24,'raws:cake flour>270,eggs>30,sugar>100,wood>40;tools:whisk;days:1','540:400',1,'<CANTR REPLACE NAME=project_baking RAW=angel_food_cake>',2,25,0),(1149,120,'raws:cake flour>270,eggs>30,sugar>100,coal>30;tools:whisk;days:1','540:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=angel_food_cake FUEL=coal>',2,25,0),(1150,120,'raws:cake flour>270,eggs>30,sugar>100,charcoal>30;tools:whisk;days:1','540:400',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=angel_food_cake FUEL=charcoal>',2,25,0),(1151,308,'raws:cake flour>270,eggs>30,sugar>100,propane>20;tools:whisk;days:1','540:400',1,'<CANTR REPLACE NAME=project_baking RAW=angel_food_cake>',2,25,0),(1152,117,'raws:barley flour>1500,eggs>30;tools:doughroller,knife;days:1','537:1500',1,'<CANTR REPLACE NAME=project_making_pasta IN=barley_flour>',2,25,0),(1153,117,'raws:wheat flour>1500,eggs>30;tools:doughroller,knife;days:1','537:1500',1,'<CANTR REPLACE NAME=project_making_pasta IN=wheat_flour>',2,25,0),(1154,117,'raws:cornmeal>1500,eggs>30;tools:doughroller,knife;days:1','537:1500',1,'<CANTR REPLACE NAME=project_making_pasta IN=cornmeal>',2,25,0),(1155,117,'raws:rye flour>1500,eggs>30;tools:doughroller,knife;days:1','537:1500',1,'<CANTR REPLACE NAME=project_making_pasta IN=rye_flour>',2,25,0),(1156,117,'raws:sorghum flour>1500,eggs>30;tools:doughroller,knife;days:1','537:1500',1,'<CANTR REPLACE NAME=project_making_pasta IN=sorghum_flour>',2,25,0),(1157,1323,'raws:rice noodles>1000,cod>500,coal>50;tools:pot,hibachi;days:1','542:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=rice_noodles_with_cod>',2,25,0),(1158,147,'raws:pasta>950,olive oil>50,wood>100;tools:pot;days:1','462:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=plain_pasta FUEL=wood>',2,25,0),(1159,570,'raws:pasta>950,olive oil>50,dried dung>200;tools:pot;days:1','462:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=plain_pasta FUEL=dried_dung>',2,25,0),(1160,570,'raws:pasta>950,olive oil>50,wood>100;tools:pot;days:1','462:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=plain_pasta FUEL=wood>',2,25,0),(1161,120,'raws:pasta>750,stewed tomatoes>250,meat>400,cheese>100,charcoal>50;tools:casserole dish;days:1','465:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=lasagna FUEL=charcoal>',2,25,0),(1162,781,'raws:pasta>750,olives>150,onions>100,thyme>20,charcoal>40;tools:pot;days:1','472:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=pasta_with_vegetables FUEL=charcoal>',2,25,0),(1163,147,'raws:rice noodles>800,poultry>500,mushrooms>200,wood>150;tools:pot;days:1','468:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=poultry_noodle_wok FUEL=wood>',2,25,0),(1164,570,'raws:rice noodles>800,poultry>500,mushrooms>200,dried dung>300;tools:pot;days:1','468:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=poultry_noodle_wok FUEL=dried_dung>',2,25,0),(1165,570,'raws:rice noodles>800,poultry>500,mushrooms>200,wood>150;tools:pot;days:1','468:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=poultry_noodle_wok FUEL=wood>',2,25,0),(1166,147,'raws:pasta>750,spinage>500,cheese>250,dried dung>300;tools:pot;days:1','543:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tagliatelle_with_spinach FUEL=dried_dung>',2,25,0),(1167,147,'raws:pasta>750,spinage>500,cheese>250,wood>150;tools:pot;days:1','543:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tagliatelle_with_spinach FUEL=wood>',2,25,0),(1168,570,'raws:pasta>750,spinage>500,cheese>250,dried dung>300;tools:pot;days:1','543:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tagliatelle_with_spinach FUEL=dried_dung>',2,25,0),(1169,570,'raws:pasta>750,spinage>500,cheese>250,wood>150;tools:pot;days:1','543:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=tagliatelle_with_spinach FUEL=wood>',2,25,0),(1170,24,'raws:pasta>750,beef>500,mushrooms>100,milk>100,wine>50,wood>40;tools:casserole dish;days:1','471:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=beef_stroganoff>',2,25,0),(1171,120,'raws:pasta>750,beef>500,mushrooms>100,milk>100,wine>50,coal>40;tools:casserole dish;days:1','471:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=beef_stroganoff FUEL=coal>',2,25,0),(1172,120,'raws:pasta>750,beef>500,mushrooms>100,milk>100,wine>50,charcoal>40;tools:casserole dish;days:1','471:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=beef_stroganoff FUEL=charcoal>',2,25,0),(1173,308,'raws:pasta>750,beef>500,mushrooms>100,milk>100,wine>50,propane>20;tools:casserole dish;days:1','471:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=beef_stroganoff>',2,25,0),(1174,304,'raws:boerenkool>900;days:1','544:500',1,'<CANTR REPLACE NAME=project_drying RAW=boerenkool>',2,25,0),(1175,782,'raws:asparagus>800,milk>200,tarragon>20;tools:tajine;days:1','545:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=asparagus_soup>',2,25,0),(1176,24,'raws:bananas>500,cake flour>200,wood>40;days:1','546:700',1,'<CANTR REPLACE NAME=project_baking RAW=banana_muffins>',2,25,0),(1177,120,'raws:bananas>500,cake flour>200,coal>40;days:1','546:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=banana_muffins FUEL=coal>',2,25,0),(1178,120,'raws:bananas>500,cake flour>200,charcoal>40;days:1','546:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=banana_muffins FUEL=charcoal>',2,25,0),(1179,308,'raws:bananas>500,cake flour>200,propane>20;days:1','546:700',1,'<CANTR REPLACE NAME=project_baking RAW=banana_muffins>',2,25,0),(1180,24,'raws:beef sausage>500,boerenkool>500,rice>500,wood>40;tools:pot;days:1','547:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=cabbage_rolls>',2,25,0),(1181,120,'raws:beef sausage>500,boerenkool>500,rice>500,coal>40;tools:pot;days:1','547:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=cabbage_rolls FUEL=coal>',2,25,0),(1182,120,'raws:beef sausage>500,boerenkool>500,rice>500,charcoal>40;tools:pot;days:1','547:1500',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=cabbage_rolls FUEL=charcoal>',2,25,0),(1183,308,'raws:beef sausage>500,boerenkool>500,rice>500,propane>20;tools:pot;days:1','547:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=cabbage_rolls>',2,25,0),(1184,301,'raws:cod>500,olive oil>100,garlic>20;tools:mixing bowl;days:1','548:600',1,'<CANTR REPLACE NAME=project_making_gen RAW=brandade>',2,25,0),(1185,781,'raws:pineapples>300,cake flour>400,eggs>20,charcoal>40;tools:pot;days:1','549:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pineapple_cake FUEL=charcoal>',2,25,0),(1186,781,'raws:pineapples>300,cake flour>400,eggs>20,coal>40;tools:pot;days:1','549:700',1,'<CANTR REPLACE NAME=project_baking_fueled RAW=pineapple_cake FUEL=coal>',2,25,0),(1187,1716,'raws:barley flour>1800,propane>30;days:1','559:1800',1,'<CANTR REPLACE NAME=project_cooking RAW=bannock>',2,25,0),(1188,1716,'raws:poultry>500,small bones>500,potatoes>500,honey>50,propane>40;tools:pot;days:1','558:1100',1,'<CANTR REPLACE NAME=project_cooking RAW=czernina>',2,25,0),(1189,1716,'raws:poultry>700,barley flour>100,eggs>20,propane>30;tools:hammer;days:1','557:800',1,'<CANTR REPLACE NAME=project_cooking RAW=schnitzel>',2,25,0),(1190,1716,'raws:grapes>100,ground meat>500,rice>1000,propane>30;tools:pot;days:1','556:1500',1,'<CANTR REPLACE NAME=project_cooking RAW=sarma>',2,25,0),(1191,1716,'raws:rice flour>300,coconuts>200,bananas>100,propane>30;tools:pot;days:1','555:600',1,'<CANTR REPLACE NAME=project_cooking RAW=bibingka>',2,25,0),(1192,1717,'raws:cake flour>500,honey>300,wine>100,raisins>100,wood>80;tools:pot;days:1','554:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=buccellato>',2,25,0),(1193,1717,'raws:sorghum flour>400,palm nectar>100,roses>20,wood>80;tools:pot;days:1','553:500',1,'<CANTR REPLACE NAME=project_cooking RAW=basbousa>',2,25,0),(1194,1717,'raws:asparagus>500,potatoes>500,poultry>300,wood>80;tools:casserole dish;days:1','552:1300',1,'<CANTR REPLACE NAME=project_cooking RAW=fricassee>',2,25,0),(1195,1717,'raws:large bones>1000,cake flour>500,eggs>30,raisins>50,brandy>30,wood>80;tools:pot;days:1','550:750',1,'<CANTR REPLACE NAME=project_cooking RAW=plum_pudding>',2,25,0),(1196,282,'raws:meat>500,mushrooms>200,dried dung>200;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=dried_dung>',2,25,0),(1197,282,'raws:meat>500,mushrooms>200,wood>100;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=wood>',2,25,0),(1198,570,'raws:meat>500,mushrooms>200,wood>100;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=wood>',2,25,0),(1199,147,'raws:meat>500,mushrooms>200,dried dung>200;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=dried_dung>',2,25,0),(1200,570,'raws:meat>500,mushrooms>200,dried dung>200;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=dried_dung>',2,25,0),(1201,147,'raws:meat>500,mushrooms>200,wood>100;days:1','560:700',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=mushroom_brochettes FUEL=wood>',2,25,0),(1202,0,'raws:poultry sausage>800,rye bread>200,eggs>10,wood>80;tools:casserole dish;days:1','494:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=tefteli>',2,25,0),(1203,282,'raws:cake flour>400,milk>100,sugar>100,dried dung>300;days:1','561:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=spit_cake FUEL=dried_dung>',2,25,0),(1204,282,'raws:cake flour>400,milk>100,sugar>100,wood>200;days:1','561:600',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=spit_cake FUEL=wood>',2,25,0),(1205,282,'raws:meat>800,nuts>200,dried dung>400;days:1','562:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=satay FUEL=dried_dung>',2,25,0),(1206,282,'raws:meat>800,nuts>200,wood>300;days:1','562:1000',1,'<CANTR REPLACE NAME=project_cooking_fueled RAW=satay FUEL=wood>',2,25,0),(1207,573,'raws:water>50;days:1','563:350',1,'<CANTR REPLACE NAME=project_machine_gathering RAW=snails>',2,19,0),(1208,574,'raws:water>50;days:1','563:350',1,'<CANTR REPLACE NAME=project_machine_gathering RAW=snails>',2,19,0),(1209,0,'raws:fresh dung>200;days:1;tools:shovel','331:100',1,'<CANTR REPLACE NAME=project_composting_1 IN1=fresh_dung>',2,21,0),(1210,1717,'raws:snails>1000,tarragon>15,garlic>15,wood>80;days:1','564:1000',1,'<CANTR REPLACE NAME=project_cooking RAW=escargot>',2,25,0),(1211,119,'raws:glass beads>300,wood>60,zinc>100;days:1','565:400',1,'<CANTR REPLACE NAME=project_manu_glass RAW=crystal_beads>',2,23,0);
/*!40000 ALTER TABLE `machines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maps`
--

DROP TABLE IF EXISTS `maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maps` (
  `x1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `y1` smallint(5) unsigned NOT NULL DEFAULT '0',
  `width` smallint(5) unsigned NOT NULL DEFAULT '0',
  `height` smallint(5) unsigned NOT NULL DEFAULT '0',
  `file` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `data` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maps`
--

LOCK TABLES `maps` WRITE;
/*!40000 ALTER TABLE `maps` DISABLE KEYS */;
INSERT INTO `maps` (`x1`, `y1`, `width`, `height`, `file`, `data`) VALUES
(579, 962, 84, 104, 'hidden/shai_region.png', 0x89504e470d0a1a0a0000000d4948445200000054000000680802000000e0b1404000000006624b474400ff00ff00ffa0bda793000000097048597300000b1200000b1201d2dd7efc0000000774494d4507d3030d0a1118a09658bc0000136c49444154789ced5c498f5cd7753ee7dce1bd1abaabab8b229b83448aa445c992ccd85610c542120708e2781323596491dfe1ac934d80fc8464974594851123b391091662cb4364393224ca326993e220f5dc5d5dd535bc778773b2b85d4f2d3996bbab2708e45934ba1e5e55ddef0cdf19eead875ffd6a1f1e56a1935ec049ca23f00fab3c02ffb0ca23f00fab3c02ffb0ca23f00fab4c051e459001e4b01773dca2f7fd0e55d2e9b7eb9d3b6d6eaedefe8db26c1cc1aa8e49f6075e6783f9e7bff6e0fc7f2c32ffcea9510de5d6db5f3ea2951d83ec15bc227feaf11f76ae7fedc7f6eebb031301dfe9d79e9e590110003cd2251e9dec093c517cf1f25fb5e0ef5e0ea7979d6510005c772a5cf84eedccaf161b5725d84fa20af604be5eef9e7fec1babff16d79f40b6e99a2c3b7a05b62ebff4e74f6e5f7377bfb8b9f4ac441d63e67d26f2c948227b02afb3c14f9e6cfce31fcc39f301c93372d7d38f3c2fd56ebcf4d20f2e43dd801a8c9b6b4b9fee2d5e1fae5d73c3c738d8a35cfc41654fe0596884509a9f776d8c208b4efdc3625b03010ad0c0cc7cefd473df7ddc9885e193e3fbbfde5d797abb7f36fa9c591dc1fa0f247b02efcb668e88003f97dbd34bf1e4fce4c238623fe2bd422ed83bd79ebf79f1f3ecb6cff0f6f972f5d3ae7faedf3b371a7682cf4014c809d3c49ec04b346d5239e2600f3723200901c07b4e969c554080bd3cdbe85c7ef38ce53925b3c33365f72294b37edc76fd7361fb1c17ed58ce4834c7ac8e3d8177ae315efaeca599d7df1eea6a7504a810ea8ae74cb0880890eb58033d6b7c0d61db675df6eb51d64a3d661807ec06b83dd608a071d33437700614729db0a5b043c66c5df14bd7472bcf16838e843ab245203c62e2c43d8eae17cebff5cc97feeccd211ac439eb855593704649b97569b07eb51876381a5fb4065be7bd6b0697078c90779be77ea42fbd721b37573c78c68fc40c0b8b082111624ed0d67c41c9a9e28c5ef9426ff9f9eed619f14d0e39081d511edd2b78227efcd26b339d77bd6bf8f16c59cc16c34e51cc9665234623bfc85d51c86e9b3337f8f16f75e77ebc06c538a2008ab080841898d96843443146175c0ce1b1cc5c6bd2bc2e16708187f3e3a51736579e1a762f87728ef9901d61afe00f2aca717369f6332fbf37ffc3f70b2d202cec821b15a3cc6622a2956666179c226dc83a5774b2c68c8ef366bc60f999ec31bdfe6babef7f6675f54abf77364673288bda7f63339d44abb72f5cabc922439448488464b52db0e80d7b8494ebac9135c850e4c81022c607c3f5d9faec2a37dfde0e6f5b796efe3b571effe6335a2fffecb7eebcf3a5f5b5ab07cf9dc7051e40e9713e7f6b750d1c3b240401160e1c5c708ad47cd73df760eb8de75bde2aadb58894a128bd354a3b74eb68bed5a5ef6e36e751bfb0f0edcf5d7d65f18d3fbef1c61f1e10ff51d7a152d5024afb110d87510287e178b83dde1ebb3100e4266f648d9961f8d4ad7e39e88fdd687bd40f31e42677c1adf5d61030b0ef8e3637cab545d9fef70dfbd78b8dda675e7e6ce19d032eeec88b70f9a01cf66b81c890563a4a14110272c1216094f8b333ea2fbfdc1cd78d8f7e500c7cf4565b6676d1b9e0420c852f420c91e3280c56fcf8db5df3c473ffa2943fc8da8e1a3ca2762aefc6e6bba30b7fffe6a0cf2099ce8c3280e0a22b5c312c87c362184124b7d6642c9cf4352a478372a050b1707fdc77c1b9e05d28430c5bc3ad5b3dee76fe67eef4cd832cee08631e914f9fbf71eae97f1dccde5874e53a6f3f18d190879a3411051786c53070a89b3a03fbe84b5fbae08445400a5fd46d1d017df45c32331b65326d0909112373cf0f5eddc4df7cee6fbb6b7fca219b04d7feca8123043f3b7fffd217ffe2bf07fede103c23e21c59566eb4bebddecc9b5a69a33ec85884c4c0cc9c2c1f38b0b02215389090512633592daf29543e7aab8cd176a554bdf99bb599a561f7e2742b3c42b7cf3a3ffdfeb0b835140f2488ce7bc53ae30c02c410e76a73ed46db2853f88290666bb399c97293e726070011d92eb6430c565942626611616600c87436539fcd4d46c62c335fbbfe4f4a05009ca20a3c2af0a8bc39f3d6ca38636112ca39ab73cd8e0c6fb316ad418732f446bd2214822222a52f99397234cacce4330a1500448965285d7065284b5f8ecad1d670ab3fea8fcae1c88d5df077b6f4a92bdf6cb5df9b6e9147e4f692b7ef71e7a734989b9562cecd75c27c8b5bc0b0a936577175a4468514851404840a81a00ca58b0e0058b8556f15be0086289185010001cb588632f8e059b86eebed667be44641f21b4378e2a957b6362f4e51fc1e097865dc85e7fef95649b558bf585e3d134ed7426ec008c9d978f62a5c5df36b4b7aa9a33b9efc48461b71234274e004c445d71d76090901010001adb29ebd88c4183393f9e07df421864c675ad177b7eabf77f13f677ff685eeda53fbed020f11fc0edf12f1d9736fb74edd0c2bf3e7c26397e2252b96859919221830064ceef2566895502a514edc6dbcfd16bf554011210240e0507d28222222012162236f18657ae31e0894be040040e8fae2d56d7ce9b9aff7bff527fb9d9a1d2278446400a8d7b79ebafa5a6fe573578779c6d62a4b44895b98390d848c9836b70100010584809670a927bd047eb724b737da88483dab5b6d7df42c4c443e7811a18c160bb375ea879dd337d7169fdfd78a0f99f08c29cf9dbbc531e3e1d97969d7a01662f0de8b88312699d11843440a55f26d042420402022433bc94f936e640d42020022ca7406085bc3adb11bd76c2dd779d288d1469112841b03baf2c2dfe4f9f649811700ccf3edd3a76f0e87358e8c8879962b52220200295d1191528a881031552c8c3ca4e118c74cdcc81a99ce329da52a80901429abedc88d4a5f0ecb6177d02d7c5184424410d128838808b8ead570fe27cf3eff8de47dc70a1e51b48ed68ee7e7efc6482134927b2ba5945200808869b988e89c0b21245d60d28122058a840869ae3657b7f51863e9cb66d66cd80600186d329d014019caade1960b8e850b578cdd3872149088f1f5f5f6c2b5ffaad77bc70d1e0010b9d1e8763af78a620611b5d6d6eed08f31d618a3b54644ef3d0010d1e45d18396ad01966c0c081591811d3b4838852b4e72637da2485a6294866b2cc6400900207006ebbe255bffed8c5d78e1bbc0820c673e76e28e598514492ab8b488cec7d8831c6185389b6eb5d1223a320221a6d48112286180207010181b11b871808c92adb6eb6e7ea73b9c911769c68b636db6ab4acb14484808cfcbd9e5dbdf27553dbabf10fcdedad1d9e3a75c7fb0c0098394e24b5f4defb1877983cfd93225f29a5496bd0edbc3d3f335fcb6a4aabdce4b9ce0387b11f17be30da244d75663aa929d04a1392679f620700925308c01ba1377bfd65a03db5ba8793ea88787efebd186b8899529016146344c484d07baf9442c474d15a9bb8c07bcf111bd46852d38001801c73224aed4de10b0141445224222cdc6eb6b5d22e381f3c028a1642520200208a0020086c2ebc4acddfe7fe856302af54e874ee174533f1790ae9e4e7c9c2e98a31060012d5c718933a0287118e36719381195881aa999ad126e539452ab5bab9c98d18456aae3197ea7c1151a490f9f36f6e23e2ebd75b09ff2a87d333ef43fffc2f6d750e0e5e10c1da41add61d8d5a229c0027b6f3dea7c226c14e1e91a81e11778c8f7e9996fbd4d7a4991905d384b396d50040506ab6962a8532944a29ad746e73452a4a54a45020d10be2cebe8008349a1b7bd95c3a287844d0da773aef233200a50d3d9a484298ca1befbdf77e87b199b5d631461619e951a9cb1ad51c38ad354c0ac134de35da6426132300508632c480888a94d1468b161000fcdfcfce0180d08e9d6b3a9c3f75ffe6cd5fdee11e82db13c5566bd1b95af279ad75c210634c2f6142814a29662142660e3114b174ca8df3c22bcfc8c4a4954640168e1c0989885c700060b525a2dce42252f9722a6f0040d48770ce28e8af7d6a2f2b3f2878113066d46cae8cc7b3154811492ecdccc9dac90b10894898a388448615bbba98bf275a7aaae7d01192800042829da8ce05e78243442d1a27f231eb21c036cfacbdf72bc7019e28763a77119959a5322e814f40932eaaeb00516b6d8c1111a0e199b98d818c363832b016c308041021004e4a1704ab6de498fe97c928f863f0d714dbadaba1983d0ef0d6160b0b3f29cbd954c055e5cdcea76bfd41199bfab9497b93656b667ce15979c2747eccadf70a08fd320fe4374bb5e2a91f7114a0444c0d0f4e7cfde3cd0e002dcd7ef93af0de4e9c4c8b3a2d855bad456bcbe1701611767338f30eed6bad93e75b6b2741c15946de37de78fd774514e057ea8df5d6dca2b145dd6e37305e9e59b673f77cad176caf277123e028aaa1332397170c63741153c3ffd153901af1b23183f73fb7c779de81c02b15ce9cb955963566018855bba6942acbb232784af2093933e7b903a83d78f06c0819b362c6623cd3ddb8040069b71791950aa402a9d2e4bdd6ccdae39d772f5efe7eded908b6f7a0c43b23b354d26650a5b000a0200022c053cd717bf9b7bbdb0b7b5cff94e0534ecdf341abb5361ab52b9cbbb80d931652e4ef8a05562a2e2f5f5c5aba14822162ad438c4a84aa7d6e110ac142b000f562d4dedebcb47aff85bb6f7d45eba2d1e8ce75ee5e6bdf7fa675df351687ba0f669ca938f6b6990fcf73eb3b6ffdd11e7d7e7af000a0549899d94424114d2489d212f289829088925e52169c3436c5eaea827356046354cca45455f8ffff2282de67de67e3716b7dfd12a220b2d21e55a9f478b6b5624c81ade51b6b5746fd337b873025f864a566735d2426ccc618bbb5d55c59d9b87245595b7575e97ea5d4440b88c888b10a4b11f885671b7ef1b78b28760a2007688db71700608af1f5345d1d112b159989a80c61a76e554ab537372ffee007e25c0821b5f43146e75c8c3184902659795e13e9cccfaf2955d91a994fe658d614961722562a30933185884a6627a28d2b57d62e5c5059968e9954a19e1c5e6b3d098a7abddedf3d6f12a11339c03e05f814a888284a39805895eb8119ad5544005099bacaf321842a40b476bbf718100540f6ebfc079769623e851c5104001112911002a4e5232262b2f36ec20380c47688c81c3f72387772cb71cb7484979c392a1599b394c6139f270ba701c6ce17685de5f934c0630e88648c8ff1836f3f7eb3c3d4632c22c9b271969595faaa7aae327ef2fc6a560993e935331b5318e33e1ce79f8c98070040646b474a8d8ac2007032759acc548e0093aa0e002aa5a4eb5a3b444ee3ecea230f03cefe641af0888028449e5989a8344549c59c88a4b354c9ff93f1955215ed31b3083023f3c9ff3e691ab717c118b5737996154ae994c94208d588365160c50222e2bd4f5726ed2d6a3d3e6c2cfb9629635e048838464a03b9e4f065593ae752579b2ea6bd8a4aaace0740351a5b4a855ffe4d4729fb068fc889e7ad1d9565bdea61d234ba420e00c9f3130b1863b22c4b63cca4856673432977f880f623fb8e7944d03a1045ad8bc99c9a126c98ec4355b92d29a2ca7f34a97f00b856dbb476ec5ce344925c927d5b5e0443d0312a00140166de4def3bdbaf933c9f483e8543d2c5c4358028b45a4b276bfc69c033ab1895b5a334b672ce1545914ad794dbaa4d8b64f02aff55637c228ad1743a776766560f788af220324d6303008850aff776c6c700559c57ff244929b056ab556732523a4ce530222f2cdc74ae3e1cce9f489e9f86ed1141a960ad43d4d5fe044ccecfa47b5256b393c65e2995fabc6ab64d44886a6666fdc9275fabd77b44fb385470583205784c1318a2522426932683efdea8aad82e497a6735c64ba91111bdafe779ffead56f6759fff82bdce9b7a889424560a974830fb731097cb5635b111e4cd831bdf23ecfb261a773fff88d3f2578ad5d9ef7455465f6dd0d4c829a141163acd87e67435e6bf8f0043e043b37f73ee2c7cef18e40a66c6c8882d67e34da19d1a78bc999ab496635b7ad5afa54e1014015f6e96644413c815f634f6ff918950856072392e73be752859fbaf7a497647f9854014905d53616001853f47a0b22c7fd7bd3e9d89e11a3f7f9878b3314817414e5e7ed0f133ac0c9b1acd4ea31338028e5b7b6ce1f7fa9379de511118842f56292e45004abb4678cd9dde726b4c9ffabe60f0088e268d42e8ad94f067811605659364afb36939416019808aa38afc23bfd75ce25da4b955fd5fc18e336371f4f23f06396292dcfacac1defae4cabe04fa892d999190053cf9fb450153913b68fcc7a63e3c9e30f78989af08aa2f9e0c1f53c1fa4196e251fa96d44d251042622444a8d40e282e41d793ee8f516cab2292731d799123cb3b97dfbc5dbb7bf10a3b5764c1452cdb2bb98ab4e26a496361dc8d8c97c00c00cc05936dcd8b8b87b8c7b9c32fd5e9df7f5f5f527b7b74f379bebb3b3cb8dc6a65205a2306b6655191f779dbaad5463bbddf97bf7969f79cab97a51cc9c544b3fbdca4530842c463b1ecf76bbe7ad2df2bc5fabf55aade55aadaf7501404a21a28a517baf005495d8ed70d8b9776fe3dac5c1a013427e7870f62787f62beab46d8c28c614c6944a396b47f57a3fcb06f5fa56a3b116a32e8a99b44b8522c4bedeeadf79f7c5e5e5a74fea711a87166c690f0b4044ea45d148a7ccf37c648c2bcb3ccbfa67cfbed36cae21a208214abddeb3b6dcdc7ce2041f2472c84c8398369e76623804cbacbc37219cba73e7c55a6dabd1e81a3366d622381cce9feca3b50e19fc6eea4a6d7f083a4deb99b3c1e0d468d4268a2214a339f187c71c6d8e11c194c093524434eff9c0cc31c891833fd2cf3fa07c321e627544f208fcc32a8fc03facf208fcc32a8fc03facf208fcc32aff07b2f5d3d5dbf95d910000000049454e44ae426082);
/*!40000 ALTER TABLE `maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message_seen`
--

DROP TABLE IF EXISTS `message_seen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_seen` (
  `player` mediumint(9) NOT NULL DEFAULT '0',
  `message` mediumint(9) NOT NULL DEFAULT '0',
  KEY `player` (`player`,`message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_seen`
--

LOCK TABLES `message_seen` WRITE;
/*!40000 ALTER TABLE `message_seen` DISABLE KEYS */;
/*!40000 ALTER TABLE `message_seen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) DEFAULT NULL,
  `language` tinyint(4) NOT NULL DEFAULT '1',
  `content` text CHARACTER SET utf8,
  `date` date DEFAULT NULL,
  `author` varchar(80) CHARACTER SET latin1 DEFAULT NULL,
  KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_birds`
--

DROP TABLE IF EXISTS `messenger_birds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messenger_birds` (
  `object_id` int(11) NOT NULL,
  `first_home` int(11) DEFAULT NULL,
  `first_home_root` int(11) DEFAULT NULL,
  `second_home` int(11) DEFAULT NULL,
  `second_home_root` int(11) DEFAULT NULL,
  `x` float DEFAULT NULL,
  `y` float DEFAULT NULL,
  `max_speed` float NOT NULL,
  `goal_which_home` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_birds`
--

LOCK TABLES `messenger_birds` WRITE;
/*!40000 ALTER TABLE `messenger_birds` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_birds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multi_logins`
--

DROP TABLE IF EXISTS `multi_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multi_logins` (
  `group_id` int(11) NOT NULL,
  `player` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_session` bigint(20) NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multi_logins`
--

LOCK TABLES `multi_logins` WRITE;
/*!40000 ALTER TABLE `multi_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `multi_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newevents`
--

DROP TABLE IF EXISTS `newevents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newevents` (
  `person` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`person`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newevents`
--

LOCK TABLES `newevents` WRITE;
/*!40000 ALTER TABLE `newevents` DISABLE KEYS */;
/*!40000 ALTER TABLE `newevents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newplayers`
--

DROP TABLE IF EXISTS `newplayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newplayers` (
  `id` mediumint(8) unsigned DEFAULT NULL,
  `firstname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` tinytext CHARACTER SET latin1,
  `age` smallint(5) unsigned DEFAULT NULL,
  `country` tinytext CHARACTER SET latin1,
  `language` tinyint(4) NOT NULL DEFAULT '1',
  `password` text CHARACTER SET utf8,
  `register` smallint(5) unsigned DEFAULT NULL,
  `ipinfo` tinytext CHARACTER SET utf8,
  `research` text CHARACTER SET utf8,
  `comment` text CHARACTER SET utf8,
  `reference` text CHARACTER SET utf8,
  `approved` tinyint(1) DEFAULT NULL,
  `refplayer` mediumint(8) unsigned DEFAULT '0',
  `referrer` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `cedata` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `type` smallint(6) NOT NULL DEFAULT '1',
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newplayers`
--

LOCK TABLES `newplayers` WRITE;
/*!40000 ALTER TABLE `newplayers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newplayers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_not_conv`
--

DROP TABLE IF EXISTS `notes_not_conv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_not_conv` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `contents` text CHARACTER SET latin1,
  `setting` tinyint(1) DEFAULT NULL,
  `encoding` varchar(20) CHARACTER SET latin1 NOT NULL DEFAULT 'unknown',
  `utf8title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `utf8contents` text CHARACTER SET utf8,
  `transfer` varbinary(40000) DEFAULT NULL,
  `transfertitle` varbinary(255) DEFAULT NULL,
  `converted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `encoding` (`encoding`),
  KEY `convstatus` (`encoding`,`converted`),
  KEY `TitleFTS` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_not_conv`
--

LOCK TABLES `notes_not_conv` WRITE;
/*!40000 ALTER TABLE `notes_not_conv` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes_not_conv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obj_notes`
--

DROP TABLE IF EXISTS `obj_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obj_notes` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `contents` text,
  `setting` tinyint(1) DEFAULT NULL,
  `encoding` varchar(20) NOT NULL DEFAULT 'unknown',
  `utf8title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `utf8contents` text CHARACTER SET utf8,
  `transfer` varbinary(40000) DEFAULT NULL,
  `transfertitle` varbinary(255) DEFAULT NULL,
  `converted` tinyint(4) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  KEY `encoding` (`encoding`),
  KEY `convstatus` (`encoding`,`converted`),
  KEY `TitleFTS` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obj_notes`
--

LOCK TABLES `obj_notes` WRITE;
/*!40000 ALTER TABLE `obj_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `obj_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obj_notes_log`
--

DROP TABLE IF EXISTS `obj_notes_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obj_notes_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(10) unsigned NOT NULL COMMENT 'id from table chars',
  `note_id` int(10) unsigned NOT NULL COMMENT 'id from table obj_notes',
  `action` enum('create','edit','copy','delete','delete_duplicate') CHARACTER SET utf8 NOT NULL COMMENT 'action type',
  `date` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `object_id` int(10) unsigned NOT NULL COMMENT 'id from table objects',
  `prev_title` text CHARACTER SET utf8,
  `prev_contents` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `char_id` (`char_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obj_notes_log`
--

LOCK TABLES `obj_notes_log` WRITE;
/*!40000 ALTER TABLE `obj_notes_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `obj_notes_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obj_properties`
--

DROP TABLE IF EXISTS `obj_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obj_properties` (
  `objecttype_id` mediumint(8) unsigned NOT NULL,
  `property_type` varchar(32) CHARACTER SET latin1 NOT NULL,
  `details` text CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`objecttype_id`,`property_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obj_properties`
--

LOCK TABLES `obj_properties` WRITE;
/*!40000 ALTER TABLE `obj_properties` DISABLE KEYS */;
INSERT INTO `obj_properties` VALUES (1,'Readable','true'),(1,'Sealable','true'),(7,'Buryable','{\"hoursNeeded\": 2}'),(12,'LocationLock','true'),(17,'Sharp','true'),(21,'VisibleFromDistance','{\"distance\": 20.99}'),(21,'VisibleOnlyFromWater','true'),(22,'VisibleFromDistance','{\"distance\": 20.99}'),(22,'VisibleOnlyFromWater','true'),(25,'Storage','{\"capacity\":1000}'),(31,'Sharp','true'),(32,'Sharp','true'),(33,'Sharp','true'),(34,'Sharp','true'),(35,'Sharp','true'),(36,'Storage','{\"capacity\":1000}'),(37,'NoteStorage','true'),(37,'Sealable','{\"canBeBroken\": true}'),(37,'Storage','{\"capacity\": 0}'),(38,'VisibleFromDistance','{\"distance\": 20.99}'),(39,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(43,'Sharp','true'),(46,'Sharp','true'),(48,'Sharp','true'),(53,'Sharp','true'),(64,'VisibleFromDistance','{\"distance\": 20.99}'),(70,'VisibleFromDistance','{\"distance\": 20.99}'),(82,'Sharp','true'),(85,'VisibleFromDistance','{\"distance\": 20.99}'),(97,'Sharp','true'),(100,'Readable','true'),(102,'Sharp','true'),(103,'Sharp','true'),(105,'Sharp','true'),(109,'Lighthouse','true'),(121,'Sharp','true'),(127,'VisibleFromDistance','{\"distance\": 20.99}'),(128,'VisibleFromDistance','{\"distance\": 20.99}'),(129,'VisibleFromDistance','{\"distance\": 20.99}'),(138,'LocationLock','true'),(152,'Storage','{\"capacity\":10000}'),(153,'Storage','{\"capacity\":5000}'),(155,'VisibleFromDistance','{\"distance\": 20.99}'),(156,'Storage','{\"capacity\":10000}'),(157,'Storage','{\"capacity\":8000}'),(158,'Storage','{\"capacity\":32000}'),(159,'Storage','{\"capacity\":120000}'),(169,'Sharp','true'),(170,'Storage','{\"capacity\":75000}'),(172,'Sharp','true'),(173,'Sharp','true'),(183,'IgnoreStoringRestrictions','true'),(183,'Storage','{\"capacity\":0}'),(190,'Sharp','true'),(191,'Sharp','true'),(207,'Sharp','true'),(226,'Sharp','true'),(227,'Sharp','true'),(268,'Storage','{\"capacity\":20000}'),(272,'Storage','{\"capacity\":60000}'),(275,'VisibleFromDistance','{\"distance\": 20.99}'),(278,'Storage','{\"capacity\":1000}'),(283,'Sharp','true'),(284,'Sharp','true'),(285,'Sharp','true'),(286,'Sharp','true'),(287,'Sharp','true'),(288,'Sharp','true'),(289,'VisibleFromDistance','{\"distance\": 20.99}'),(317,'Storage','{\"capacity\":1000}'),(328,'VisibleFromDistance','{\"distance\": 20.99}'),(329,'VisibleFromDistance','{\"distance\": 20.99}'),(336,'Readable','true'),(336,'Storage','{\"capacity\":10}'),(337,'Readable','true'),(337,'Storage','{\"capacity\":10}'),(342,'Sharp','true'),(372,'Storage','{\"capacity\":12200}'),(393,'Storage','{\"capacity\":2000}'),(410,'Sharp','true'),(412,'Sharp','true'),(421,'Sharp','true'),(424,'Sharp','true'),(426,'Sharp','true'),(428,'Sharp','true'),(439,'Sharp','true'),(440,'Sharp','true'),(447,'Sharp','true'),(456,'Sharp','true'),(483,'EnableSeeingOutside','{\"mustBeOpen\": true}'),(487,'BoostTraveling','{\"passive\": -0.35}'),(508,'Sharp','true'),(509,'Sharp','true'),(510,'Sharp','true'),(511,'Sharp','true'),(541,'Sharp','true'),(542,'Sharp','true'),(545,'Sharp','true'),(603,'AlterViewRange','200'),(604,'AlterViewRange','125'),(605,'AlterViewRange','175'),(606,'AlterViewRange','150'),(619,'Storage','{\"capacity\":800}'),(620,'Storage','{\"capacity\":4000}'),(621,'Storage','{\"capacity\":5000}'),(622,'Storage','{\"capacity\":15000}'),(623,'Storage','{\"capacity\":2000}'),(624,'Storage','{\"capacity\":1000}'),(633,'Storage','{\"capacity\":2000}'),(634,'Storage','{\"capacity\":5000}'),(641,'Sharp','true'),(642,'Sharp','true'),(644,'Sharp','true'),(645,'Sharp','true'),(647,'Sharp','true'),(648,'Sharp','true'),(649,'Sharp','true'),(657,'VisibleFromDistance','{\"distance\": 60}'),(719,'Storage','{\"capacity\":750}'),(720,'Storage','{\"capacity\":1000}'),(721,'Storage','{\"capacity\":600}'),(755,'VisibleFromDistance','{\"distance\": 20.99}'),(766,'Storage','{\"capacity\":4000}'),(771,'Storage','{\"capacity\":800}'),(772,'Storage','{\"capacity\":500}'),(773,'Storage','{\"capacity\":1000}'),(774,'Storage','{\"capacity\":300}'),(775,'Storage','{\"capacity\":400}'),(776,'Storage','{\"capacity\":300}'),(777,'Storage','{\"capacity\":70}'),(778,'Storage','{\"capacity\":40}'),(779,'Storage','{\"capacity\":2100}'),(812,'Storage','{\"capacity\":200}'),(821,'Storage','{\"capacity\":300}'),(822,'Storage','{\"capacity\":1000}'),(823,'Die','{\"numberOfSides\": 6}'),(824,'Die','{\"numberOfSides\": 6}'),(825,'Die','{\"numberOfSides\": 6}'),(831,'Die','{\"numberOfSides\": 6}'),(836,'Storage','{\"capacity\":1800}'),(837,'Storage','{\"capacity\":1200}'),(838,'VisibleFromDistance','{\"distance\": 20.99}'),(839,'VisibleFromDistance','{\"distance\": 20.99}'),(859,'Storage','{\"capacity\":1000}'),(860,'Storage','{\"capacity\":1000}'),(861,'Storage','{\"capacity\":1000}'),(862,'Storage','{\"capacity\":1000}'),(863,'Storage','{\"capacity\":1000}'),(864,'Storage','{\"capacity\":1000}'),(865,'Storage','{\"capacity\":1000}'),(866,'Storage','{\"capacity\":1000}'),(867,'Storage','{\"capacity\":1000}'),(868,'Storage','{\"capacity\":1000}'),(869,'Storage','{\"capacity\":1000}'),(870,'Storage','{\"capacity\":1000}'),(1223,'CustomEvent','{\"flykite\":{\"actorEventTag\":\"custom_flykite_actor\",\"othersEventTag\":\"custom_flykite_others\"}}'),(1224,'CustomEvent','{\"flykite2\":{\"actorEventTag\":\"custom_flykite2_actor\",\"othersEventTag\":\"custom_flykite2_others\"}}'),(1225,'CustomEvent','{\"flykite\":{\"actorEventTag\":\"custom_flykite_actor\",\"othersEventTag\":\"custom_flykite_others\"}}'),(1226,'CustomEvent','{\"flykite\":{\"actorEventTag\":\"custom_flykite_actor\",\"othersEventTag\":\"custom_flykite_others\"}}'),(1227,'CustomEvent','{\"flykite2\":{\"actorEventTag\":\"custom_flykite2_actor\",\"othersEventTag\":\"custom_flykite2_others\"}}'),(1228,'CustomEvent','{\"flykite2\":{\"actorEventTag\":\"custom_flykite2_actor\",\"othersEventTag\":\"custom_flykite2_others\"}}'),(1229,'CustomEvent','{\"flykite2\":{\"actorEventTag\":\"custom_flykite2_actor\",\"othersEventTag\":\"custom_flykite2_others\"}}'),(1235,'CustomEvent','{\"sparkler\":{\"actorEventTag\":\"custom_sparkler_actor\",\"othersEventTag\":\"custom_sparkler_others\"}}'),(1236,'CustomEvent','{\"sparkler2\":{\"actorEventTag\":\"custom_sparkler2_actor\",\"othersEventTag\":\"custom_sparkler2_others\"}}'),(1237,'CustomEvent','{\"sparkler3\":{\"actorEventTag\":\"custom_sparkler3_actor\",\"othersEventTag\":\"custom_sparkler3_others\"}}'),(1243,'Storage','{\"capacity\":20000}'),(1244,'Storage','{\"capacity\":20000}'),(1245,'Storage','{\"capacity\":20000}'),(1252,'Storage','{\"capacity\":64000}'),(1253,'Storage','{\"capacity\":64000}'),(1255,'Sharp','true'),(1257,'Sharp','true'),(1258,'Sharp','true'),(1259,'Sharp','true'),(1268,'Sharp','true'),(1270,'Storage','{\"capacity\":500}'),(1281,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1281,'StorageRestrictionGroup','\"animal\"'),(1282,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1282,'StorageRestrictionGroup','\"animal\"'),(1283,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1283,'StorageRestrictionGroup','\"animal\"'),(1284,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1284,'StorageRestrictionGroup','\"animal\"'),(1285,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1285,'StorageRestrictionGroup','\"animal\"'),(1286,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1286,'StorageRestrictionGroup','\"animal\"'),(1287,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1287,'StorageRestrictionGroup','\"animal\"'),(1288,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1288,'StorageRestrictionGroup','\"animal\"'),(1289,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1289,'StorageRestrictionGroup','\"animal\"'),(1290,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1290,'StorageRestrictionGroup','\"animal\"'),(1291,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1291,'StorageRestrictionGroup','\"animal\"'),(1292,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1292,'StorageRestrictionGroup','\"animal\"'),(1293,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1293,'StorageRestrictionGroup','\"animal\"'),(1294,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1294,'StorageRestrictionGroup','\"animal\"'),(1295,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1295,'StorageRestrictionGroup','\"animal\"'),(1296,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1296,'StorageRestrictionGroup','\"animal\"'),(1297,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1297,'StorageRestrictionGroup','\"animal\"'),(1298,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1298,'StorageRestrictionGroup','\"animal\"'),(1299,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1299,'StorageRestrictionGroup','\"animal\"'),(1300,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1300,'StorageRestrictionGroup','\"animal\"'),(1301,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1301,'StorageRestrictionGroup','\"animal\"'),(1302,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1302,'StorageRestrictionGroup','\"animal\"'),(1303,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1303,'StorageRestrictionGroup','\"animal\"'),(1304,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1304,'StorageRestrictionGroup','\"animal\"'),(1305,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1305,'StorageRestrictionGroup','\"animal\"'),(1306,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1306,'StorageRestrictionGroup','\"animal\"'),(1307,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1307,'StorageRestrictionGroup','\"animal\"'),(1308,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1308,'StorageRestrictionGroup','\"animal\"'),(1309,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1309,'StorageRestrictionGroup','\"animal\"'),(1355,'Storage','{\"capacity\":2000}'),(1356,'Storage','{\"capacity\":800}'),(1357,'Storage','{\"capacity\":800}'),(1358,'Storage','{\"capacity\":500}'),(1359,'Storage','{\"capacity\":3000}'),(1360,'CustomEvent','{\"throwball\":{\"actorEventTag\":\"custom_throwball_actor\",\"othersEventTag\":\"custom_throwball_others\"},\"kickball\":{\"actorEventTag\":\"custom_kickball_actor\",\"othersEventTag\":\"custom_kickball_others\"}}'),(1361,'CustomEvent','{\"throwball\":{\"actorEventTag\":\"custom_throwball_actor\",\"othersEventTag\":\"custom_throwball_others\"},\"kickball\":{\"actorEventTag\":\"custom_kickball_actor\",\"othersEventTag\":\"custom_kickball_others\"}}'),(1374,'Storage','{\"capacity\":800}'),(1375,'Storage','{\"capacity\":200}'),(1376,'Storage','{\"capacity\":300}'),(1377,'Storage','{\"capacity\":750}'),(1378,'Storage','{\"capacity\":1000}'),(1389,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1389,'StorageRestrictionGroup','\"animal\"'),(1390,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1390,'StorageRestrictionGroup','\"animal\"'),(1391,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1391,'StorageRestrictionGroup','\"animal\"'),(1392,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1392,'StorageRestrictionGroup','\"animal\"'),(1393,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1393,'StorageRestrictionGroup','\"animal\"'),(1394,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1394,'StorageRestrictionGroup','\"animal\"'),(1395,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1395,'StorageRestrictionGroup','\"animal\"'),(1396,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1396,'StorageRestrictionGroup','\"animal\"'),(1397,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1397,'StorageRestrictionGroup','\"animal\"'),(1398,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1398,'StorageRestrictionGroup','\"animal\"'),(1399,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1399,'StorageRestrictionGroup','\"animal\"'),(1400,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1400,'StorageRestrictionGroup','\"animal\"'),(1401,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1401,'StorageRestrictionGroup','\"animal\"'),(1402,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1402,'StorageRestrictionGroup','\"animal\"'),(1403,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1403,'StorageRestrictionGroup','\"animal\"'),(1404,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1404,'StorageRestrictionGroup','\"animal\"'),(1405,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1405,'StorageRestrictionGroup','\"animal\"'),(1406,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1406,'StorageRestrictionGroup','\"animal\"'),(1407,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1407,'StorageRestrictionGroup','\"animal\"'),(1408,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1408,'StorageRestrictionGroup','\"animal\"'),(1409,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1409,'StorageRestrictionGroup','\"animal\"'),(1410,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1410,'StorageRestrictionGroup','\"animal\"'),(1411,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1411,'StorageRestrictionGroup','\"animal\"'),(1412,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1412,'StorageRestrictionGroup','\"animal\"'),(1413,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1413,'StorageRestrictionGroup','\"animal\"'),(1414,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1414,'StorageRestrictionGroup','\"animal\"'),(1415,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1415,'StorageRestrictionGroup','\"animal\"'),(1416,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1416,'StorageRestrictionGroup','\"animal\"'),(1417,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1417,'StorageRestrictionGroup','\"animal\"'),(1418,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1418,'StorageRestrictionGroup','\"animal\"'),(1419,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1419,'StorageRestrictionGroup','\"animal\"'),(1420,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1420,'StorageRestrictionGroup','\"animal\"'),(1421,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1421,'StorageRestrictionGroup','\"animal\"'),(1422,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1422,'StorageRestrictionGroup','\"animal\"'),(1443,'Storage','{\"capacity\":400}'),(1444,'Storage','{\"capacity\":400}'),(1445,'Storage','{\"capacity\":400}'),(1446,'Storage','{\"capacity\":6000}'),(1447,'Storage','{\"capacity\":3000}'),(1449,'Storage','{\"capacity\":7000}'),(1471,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1471,'StorageRestrictionGroup','\"animal\"'),(1472,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1472,'StorageRestrictionGroup','\"animal\"'),(1473,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1473,'StorageRestrictionGroup','\"animal\"'),(1474,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1474,'StorageRestrictionGroup','\"animal\"'),(1475,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1475,'StorageRestrictionGroup','\"animal\"'),(1476,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1476,'StorageRestrictionGroup','\"animal\"'),(1477,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1477,'StorageRestrictionGroup','\"animal\"'),(1478,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1478,'StorageRestrictionGroup','\"animal\"'),(1479,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1479,'StorageRestrictionGroup','\"animal\"'),(1480,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1480,'StorageRestrictionGroup','\"animal\"'),(1481,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1481,'StorageRestrictionGroup','\"animal\"'),(1482,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1482,'StorageRestrictionGroup','\"animal\"'),(1483,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1483,'StorageRestrictionGroup','\"animal\"'),(1484,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1484,'StorageRestrictionGroup','\"animal\"'),(1485,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1485,'StorageRestrictionGroup','\"animal\"'),(1488,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1489,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1490,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1491,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1492,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1493,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1494,'CustomEvent','{\"custom_use\":{\"actorEventTag\":\"custom_use_actor\",\"othersEventTag\":\"custom_custom_use_others\"}}'),(1495,'CustomEvent','{\"red_flare\":{\"actorEventTag\":\"custom_red_flare_actor\",\"othersEventTag\":\"custom_red_flare_others\",\"distantEventTag\":\"custom_red_flare_distant\",\"distantRange\":\"45\",\"onlyOutside\":true}}'),(1496,'CustomEvent','{\"small_green_firework\":{\"actorEventTag\":\"custom_small_green_firework_actor\",\"othersEventTag\":\"custom_small_green_firework_others\",\"distantEventTag\":\"custom_small_green_firework_distant\",\"distantRange\":\"50\",\"onlyOutside\":true}}'),(1497,'CustomEvent','{\"small_orange_firework\":{\"actorEventTag\":\"custom_small_orange_firework_actor\",\"othersEventTag\":\"custom_small_orange_firework_others\",\"distantEventTag\":\"custom_small_orange_firework_distant\",\"distantRange\":\"50\",\"onlyOutside\":true}}'),(1498,'CustomEvent','{\"yellow_firework\":{\"actorEventTag\":\"custom_yellow_firework_actor\",\"othersEventTag\":\"custom_yellow_firework_others\",\"distantEventTag\":\"custom_yellow_firework_distant\",\"distantRange\":\"100\",\"onlyOutside\":true}}'),(1499,'CustomEvent','{\"blue_firework\":{\"actorEventTag\":\"custom_blue_firework_actor\",\"othersEventTag\":\"custom_blue_firework_others\",\"distantEventTag\":\"custom_blue_firework_distant\",\"distantRange\":\"100\",\"onlyOutside\":true}}'),(1500,'CustomEvent','{\"great_red_firework\":{\"actorEventTag\":\"custom_great_red_firework_actor\",\"othersEventTag\":\"custom_great_red_firework_others\",\"distantEventTag\":\"custom_great_red_firework_distant\",\"distantRange\":\"250\",\"onlyOutside\":true}}'),(1501,'CustomEvent','{\"great_gold_firework\":{\"actorEventTag\":\"custom_great_gold_firework_actor\",\"othersEventTag\":\"custom_great_gold_firework_others\",\"distantEventTag\":\"custom_great_gold_firework_distant\",\"distantRange\":\"250\",\"onlyOutside\":true}}'),(1502,'CustomEvent','{\"great_white_firework\":{\"actorEventTag\":\"custom_great_white_firework_actor\",\"othersEventTag\":\"custom_great_white_firework_others\",\"distantEventTag\":\"custom_great_white_firework_distant\",\"distantRange\":\"250\",\"onlyOutside\":true}}'),(1526,'Storage','{\"capacity\":400}'),(1570,'Storage','{\"capacity\": 100000}'),(1577,'Storage','{\"capacity\": 100000}'),(1578,'BoostTraveling','{\"active\": 0.35}'),(1579,'BoostTraveling','{\"passive\": -0.15}'),(1596,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1596,'StorageRestrictionGroup','\"animal\"'),(1597,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1597,'StorageRestrictionGroup','\"animal\"'),(1598,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1598,'StorageRestrictionGroup','\"animal\"'),(1599,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1599,'StorageRestrictionGroup','\"animal\"'),(1600,'CustomEvent','{\"petheldanimal\":{\"actorEventTag\":\"custom_petheldanimal_actor\",\"othersEventTag\":\"custom_petheldanimal_others\"},\"petanimal\":{\"actorEventTag\":\"custom_petanimal_actor\",\"othersEventTag\":\"custom_petanimal_others\"}}'),(1600,'StorageRestrictionGroup','\"animal\"'),(1601,'IgnoreLookingRestrictions','true'),(1601,'Storage','{\"capacity\":10000}'),(1602,'IgnoreLookingRestrictions','true'),(1602,'Storage ','{\"capacity\":10000}'),(1603,'IgnoreLookingRestrictions','true'),(1603,'Storage','{\"capacity\":10000}'),(1604,'IgnoreLookingRestrictions','true'),(1604,'Storage','{\"capacity\": 2000}'),(1618,'CustomEvent','{\"fan\":{\"actorEventTag\":\"custom_fan_actor\",\"othersEventTag\":\"custom_fan_others\"}}'),(1619,'CustomEvent','{\"fan\":{\"actorEventTag\":\"custom_fan_actor\",\"othersEventTag\":\"custom_fan_others\"}}'),(1620,'CustomEvent','{\"fan\":{\"actorEventTag\":\"custom_fan_actor\",\"othersEventTag\":\"custom_fan_others\"}}'),(1621,'CustomEvent','{\"fan\":{\"actorEventTag\":\"custom_fan_actor\",\"othersEventTag\":\"custom_fan_others\"}}'),(1628,'Storage','{\"capacity\":40000}'),(1629,'Storage','{\"capacity\":200}'),(1630,'Storage','{\"capacity\": 750}'),(1631,'Storage','{\"capacity\": 650}'),(1632,'Storage','{\"capacity\": 750}'),(1633,'Storage','{\"capacity\": 400}'),(1634,'Die','{\"numberOfSides\": 4}'),(1635,'Die','{\"numberOfSides\": 4}'),(1636,'Die','{\"numberOfSides\": 10}'),(1637,'Die','{\"numberOfSides\": 6}'),(1638,'Die','{\"numberOfSides\": 12}'),(1639,'Die','{\"numberOfSides\": 20}'),(1640,'Die','{\"numberOfSides\": 4}'),(1641,'Die','{\"numberOfSides\": 10}'),(1642,'Die','{\"numberOfSides\": 12}'),(1643,'Die','{\"numberOfSides\": 20}'),(1645,'Die','{\"numberOfSides\": 10}'),(1646,'Die','{\"numberOfSides\": 12}'),(1647,'Die','{\"numberOfSides\": 20}'),(1648,'Die','{\"numberOfSides\": 4}'),(1649,'Die','{\"numberOfSides\": 4}'),(1650,'Die','{\"numberOfSides\": 4}'),(1651,'Die','{\"numberOfSides\": 4}'),(1652,'Die','{\"numberOfSides\": 6}'),(1653,'Die','{\"numberOfSides\": 6}'),(1654,'Die','{\"numberOfSides\": 10}'),(1655,'Die','{\"numberOfSides\": 10}'),(1656,'Die','{\"numberOfSides\": 10}'),(1657,'Die','{\"numberOfSides\": 10}'),(1658,'Die','{\"numberOfSides\": 12}'),(1659,'Die','{\"numberOfSides\": 12}'),(1660,'Die','{\"numberOfSides\": 12}'),(1661,'Die','{\"numberOfSides\": 12}'),(1662,'Die','{\"numberOfSides\": 20}'),(1663,'Die','{\"numberOfSides\": 20}'),(1664,'Die','{\"numberOfSides\": 20}'),(1665,'Die','{\"numberOfSides\": 20}'),(1666,'Storage','{\"capacity\":750}'),(1667,'Storage','{\"capacity\":750}'),(1668,'Storage','{\"capacity\":250}'),(1669,'Storage','{\"capacity\":250}'),(1670,'Storage','{\"capacity\":4500}'),(1671,'Storage','{\"capacity\":4500}'),(1672,'Storage','{\"capacity\":400}'),(1673,'Storage','{\"capacity\":400}'),(1676,'Storage','{\"capacity\":20000}'),(1677,'Storage','{\"capacity\":500}'),(1678,'Storage','{\"capacity\":4000}'),(1679,'Storage','{\"capacity\":1000}'),(1680,'Storage','{\"capacity\":4500}'),(1681,'Storage','{\"capacity\":10000}'),(1682,'Storage','{\"capacity\":2000}'),(1683,'Storage','{\"capacity\":500}'),(1687,'IgnoreLookingRestrictions','true'),(1687,'Storage','{\"capacity\":20000}'),(1688,'Storage','{\"capacity\":2000}'),(1696,'CustomEvent','{\"sky_lantern\":{\"actorEventTag\":\"custom_sky_lantern_actor\",\"othersEventTag\":\"custom_sky_lantern_others\",\"distantEventTag\":\"custom_sky_lantern_distant\",\"distantRange\":\"45\",\"onlyOutside\":true}}'),(1698,'Buryable','{\"hoursNeeded\": 4}'),(1698,'IgnoreLookingRestrictions','true'),(1698,'Storage','{\"capacity\": 75000}'),(1699,'Buryable','{\"hoursNeeded\": 8, \"projectTag\": \"project_lighting_object\"}'),(1699,'Storage','{\"capacity\": 75000}'),(1700,'IgnoreLookingRestrictions','true'),(1700,'NoteStorage','true'),(1700,'Sealable','{\"canBeBroken\": false}'),(1700,'Storage','{\"capacity\":0}'),(1701,'IgnoreLookingRestrictions','true'),(1701,'NoteStorage','true'),(1701,'Sealable','{\"canBeBroken\": false}'),(1701,'Storage','{\"capacity\":0}'),(1702,'IgnoreLookingRestrictions','true'),(1702,'NoteStorage','true'),(1702,'Sealable','{\"canBeBroken\": false}'),(1702,'Storage','{\"capacity\":0}'),(1703,'IgnoreLookingRestrictions','true'),(1703,'NoteStorage','true'),(1703,'Sealable','{\"canBeBroken\": false}'),(1703,'Storage','{\"capacity\":0}'),(1704,'IgnoreLookingRestrictions','true'),(1704,'NoteStorage','true'),(1704,'Sealable','{\"canBeBroken\": false}'),(1704,'Storage','{\"capacity\":0}'),(1705,'IgnoreLookingRestrictions','true'),(1705,'NoteStorage','true'),(1705,'Sealable','{\"canBeBroken\": false}'),(1705,'Storage','{\"capacity\":0}'),(1706,'IgnoreLookingRestrictions','true'),(1706,'NoteStorage','true'),(1706,'Sealable','{\"canBeBroken\": false}'),(1706,'Storage','{\"capacity\":0}'),(1707,'IgnoreLookingRestrictions','true'),(1707,'NoteStorage','true'),(1707,'Sealable','{\"canBeBroken\": false}'),(1707,'Storage','{\"capacity\":0}'),(1708,'IgnoreLookingRestrictions','true'),(1708,'NoteStorage','true'),(1708,'Sealable','{\"canBeBroken\": false}'),(1708,'Storage','{\"capacity\":0}'),(1709,'IgnoreLookingRestrictions','true'),(1709,'NoteStorage','true'),(1709,'Sealable','{\"canBeBroken\": false}'),(1709,'Storage','{\"capacity\":0}'),(1710,'IgnoreLookingRestrictions','true'),(1710,'NoteStorage','true'),(1710,'Sealable','{\"canBeBroken\": false}'),(1710,'Storage','{\"capacity\":0}'),(1711,'IgnoreLookingRestrictions','true'),(1711,'NoteStorage','true'),(1711,'Sealable','{\"canBeBroken\": false}'),(1711,'Storage','{\"capacity\":0}'),(1712,'IgnoreLookingRestrictions','true'),(1712,'NoteStorage','true'),(1712,'Sealable','{\"canBeBroken\": false}'),(1712,'Storage','{\"capacity\":0}'),(1713,'IgnoreLookingRestrictions','true'),(1713,'NoteStorage','true'),(1713,'Sealable','{\"canBeBroken\": false}'),(1713,'Storage','{\"capacity\":0}'),(1723,'Storage','{\"capacity\":50}'),(1724,'Storage','{\"capacity\":150}'),(1725,'Storage','{\"capacity\":300}'),(1726,'Storage','{\"capacity\":900}'),(1728,'RoadFactor','1.0'),(1729,'RoadFactor','2.0'),(1730,'RoadFactor','1.2'),(1731,'RoadFactor','0.8'),(1732,'RoadFactor','4.0'),(1733,'RoadFactor','1.5'),(1734,'RoadFactor','4.0'),(1735,'RoadFactor','0.8'),(1736,'RoadFactor','0.6'),(1737,'RoadFactor','3.0');
/*!40000 ALTER TABLE `obj_properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obj_values`
--

DROP TABLE IF EXISTS `obj_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obj_values` (
  `id` int(11) NOT NULL,
  `unique_name` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `name` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
  `value` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obj_values`
--

LOCK TABLES `obj_values` WRITE;
/*!40000 ALTER TABLE `obj_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `obj_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectcategories`
--

DROP TABLE IF EXISTS `objectcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objectcategories` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parent` smallint(6) DEFAULT NULL,
  `name` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectcategories`
--

LOCK TABLES `objectcategories` WRITE;
/*!40000 ALTER TABLE `objectcategories` DISABLE KEYS */;
INSERT INTO `objectcategories` VALUES (1,NULL,'unmanufacturable',1),(2,NULL,'tools',0),(3,NULL,'raw material',1),(4,6,'transport',0),(5,NULL,'temporarily unmanufacturable',1),(6,NULL,'buildings',0),(7,NULL,'machinery',0),(8,NULL,'engines',0),(9,NULL,'vehicles',0),(10,NULL,'weapons',0),(12,NULL,'recycle bin',1),(13,NULL,'protection',0),(14,NULL,'furniture',0),(15,NULL,'storage',0),(16,NULL,'awaiting approval',1),(17,NULL,'awaiting programming',1),(18,NULL,'awaiting game progress',1),(19,NULL,'semi-finished',0),(20,NULL,'semi-finished (unmanufacturable)',1),(21,NULL,'electronics',0),(22,NULL,'musical-instruments',0),(23,10,'steel weapons',0),(24,10,'bronze weapons',0),(25,10,'ranged weapons',0),(26,NULL,'clothes',0),(27,6,'signs',0),(28,21,'radio transmitters',0),(29,21,'radio receivers',0),(30,7,'harvesters',0),(31,7,'drills',0),(32,7,'food preparation',0),(33,19,'weaponry',0),(34,9,'ships',0),(35,19,'vehicle parts',0),(36,19,'electronic parts',0),(37,26,'Hats',0),(38,26,'Jackets',0),(39,26,'Shirts',0),(40,26,'Trousers',0),(41,26,'Gloves',0),(42,26,'Shoes',0),(43,26,'Underpants',0),(44,26,'Robes',0),(45,26,'Masks',0),(46,26,'Earrings',0),(47,26,'Bracelets',0),(48,26,'Rings',0),(49,26,'Undershirts',0),(50,26,'Necklaces',0),(51,26,'Scarves',0),(52,26,'Skirts',0),(53,26,'Belts',0),(54,26,'Cloaks',0),(55,26,'Vests',0),(56,26,'Aprons',0),(57,26,'Socks',0),(58,26,'Dresses',0),(59,NULL,'Roleplay',0),(60,26,'Bags',0),(61,NULL,'domesticated animals',1),(62,26,'Hair_Accessories',0),(63,59,'Dice',0),(64,59,'Kites',0),(65,9,'Trains',0),(66,59,'Dolls',0),(67,59,'Beauty_products',0),(68,59,'Fireworks',0),(69,59,'Umbrellas',0),(70,6,'Ruins_Of_Buildings',1),(71,2,'Repair_Tools',0),(72,9,'Riding_Accessories',0),(73,59,'Hand_Mirrors',0),(74,14,'Fixed_Mirrors',0),(75,14,'Curtains',0),(76,14,'Resting',0),(77,14,'Decorative',0),(78,59,'Bouquets',0),(79,59,'Statuettes',0),(80,59,'Pipes',0),(81,59,'Utensils',0),(82,2,'Optical',0),(83,9,'Assembly_Lines',0),(84,19,'Buckles',0),(85,19,'Buttons',0),(86,19,'Bales',0),(87,19,'Logs',0),(88,15,'Portable_Storages',0),(89,15,'Fixed_Storages',0),(90,15,'Burial_Equipment',0),(91,NULL,'Books',0),(92,NULL,'Terrain_Areas',1);
/*!40000 ALTER TABLE `objectcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objects` (
  `location` mediumint(9) NOT NULL DEFAULT '0',
  `person` mediumint(8) NOT NULL DEFAULT '0',
  `attached` int(9) NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned DEFAULT NULL,
  `typeid` int(9) unsigned DEFAULT NULL,
  `weight` bigint(5) unsigned DEFAULT NULL,
  `length` mediumint(9) NOT NULL DEFAULT '0',
  `width` mediumint(9) NOT NULL DEFAULT '0',
  `height` mediumint(9) NOT NULL DEFAULT '0',
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `setting` tinyint(1) DEFAULT NULL,
  `specifics` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `deterioration` mediumint(9) DEFAULT '0',
  `expired_date` mediumint(9) NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'order visible on storage page',
  PRIMARY KEY (`id`),
  KEY `person` (`person`),
  KEY `location` (`location`),
  KEY `typeid` (`typeid`),
  KEY `attached` (`attached`),
  KEY `lockkeys` (`type`,`specifics`),
  KEY `expired_date` (`expired_date`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objects`
--

LOCK TABLES `objects` WRITE;
/*!40000 ALTER TABLE `objects` DISABLE KEYS */;
INSERT INTO `objects` VALUES (636,0,0,2,25,10000,0,0,0,1,2,NULL,0,0,0),(636,0,0,2,26,10000,0,0,0,2,2,NULL,0,0,0),(636,0,0,53,0,100,0,0,0,3,1,NULL,0,0,0),(20298,0,0,12,0,100,0,0,0,4,3,'locked',0,0,0),(636,0,0,185,0,215,0,0,0,5,1,NULL,0,0,0),(636,0,0,142,0,300,0,0,0,6,1,NULL,0,0,0),(636,0,0,46,0,110,0,0,0,7,1,NULL,0,0,0),(636,0,0,156,0,2150,0,0,0,8,3,NULL,0,0,0),(636,0,0,230,5,300,0,0,0,9,1,NULL,0,0,0),(636,0,0,230,6,110,0,0,0,10,1,NULL,0,0,0),(636,0,0,230,7,150,0,0,0,11,1,NULL,0,0,0),(636,0,0,2,8,10000,0,0,0,12,2,NULL,0,0,0),(636,0,0,130,0,50,0,0,0,13,1,NULL,0,0,0),(1028,0,0,520,0,100,0,0,0,14,3,'100',0,0,0),(1110,0,0,521,0,100,0,0,0,15,3,'100',0,0,0),(15736,0,0,522,0,100,0,0,0,16,3,'',0,0,0),(636,0,0,635,0,50,0,0,0,17,1,NULL,0,0,0);
/*!40000 ALTER TABLE `objects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objecttypes`
--

DROP TABLE IF EXISTS `objecttypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objecttypes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET latin1 NOT NULL COMMENT 'used for build requirements, NOT unique',
  `unique_name` text CHARACTER SET latin1 NOT NULL COMMENT 'used by translations engine',
  `show_instructions_outside` text CHARACTER SET latin1 NOT NULL COMMENT 'buttons visible when on the ground',
  `show_instructions_inventory` text CHARACTER SET latin1 NOT NULL COMMENT 'buttons visible when in inventory',
  `build_conditions` text CHARACTER SET latin1,
  `build_description` text CHARACTER SET latin1,
  `build_requirements` text CHARACTER SET latin1,
  `build_result` text CHARACTER SET latin1,
  `skill` tinyint(3) unsigned DEFAULT '0',
  `subtable` text CHARACTER SET latin1,
  `category` text CHARACTER SET latin1,
  `rules` text CHARACTER SET latin1,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `report` tinyint(4) DEFAULT NULL,
  `deter_rate_turn` mediumint(9) DEFAULT '0',
  `deter_rate_use` mediumint(9) DEFAULT '0',
  `repair_rate` mediumint(9) DEFAULT '0',
  `deter_visible` tinyint(1) DEFAULT '1',
  `project_weight` mediumint(9) DEFAULT NULL,
  `image_file_name` text CHARACTER SET latin1,
  `objectcategory` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(16)),
  KEY `unique_name` (`unique_name`(16)),
  KEY `objectcategory` (`objectcategory`)
) ENGINE=InnoDB AUTO_INCREMENT=1742 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objecttypes`
--

LOCK TABLES `objecttypes` WRITE;
/*!40000 ALTER TABLE `objecttypes` DISABLE KEYS */;
/*!40000 ALTER TABLE `objecttypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oldlocnames`
--

DROP TABLE IF EXISTS `oldlocnames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oldlocnames` (
  `id` mediumint(9) NOT NULL DEFAULT '0',
  `name` text CHARACTER SET latin1 NOT NULL,
  `usersname` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oldlocnames`
--

LOCK TABLES `oldlocnames` WRITE;
/*!40000 ALTER TABLE `oldlocnames` DISABLE KEYS */;
/*!40000 ALTER TABLE `oldlocnames` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `onetime_passwords`
--

DROP TABLE IF EXISTS `onetime_passwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onetime_passwords` (
  `player` int(11) DEFAULT NULL,
  `password` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onetime_passwords`
--

LOCK TABLES `onetime_passwords` WRITE;
/*!40000 ALTER TABLE `onetime_passwords` DISABLE KEYS */;
/*!40000 ALTER TABLE `onetime_passwords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pcstatistics`
--

DROP TABLE IF EXISTS `pcstatistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcstatistics` (
  `action` text CHARACTER SET latin1 NOT NULL,
  `turn` smallint(5) unsigned DEFAULT NULL,
  `actiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  KEY `turn` (`turn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pcstatistics`
--

LOCK TABLES `pcstatistics` WRITE;
/*!40000 ALTER TABLE `pcstatistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `pcstatistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pending_credits`
--

DROP TABLE IF EXISTS `pending_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pending_credits` (
  `newplayer` mediumint(8) unsigned DEFAULT NULL,
  `refplayer` mediumint(8) unsigned DEFAULT NULL,
  `amount` smallint(5) unsigned DEFAULT NULL,
  `pending` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pending_credits`
--

LOCK TABLES `pending_credits` WRITE;
/*!40000 ALTER TABLE `pending_credits` DISABLE KEYS */;
/*!40000 ALTER TABLE `pending_credits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_images`
--

DROP TABLE IF EXISTS `player_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8 NOT NULL,
  `uploader_id` int(10) unsigned NOT NULL COMMENT 'player id who uploaded the image',
  `date` datetime NOT NULL COMMENT 'when it was uploaded',
  `accepted` tinyint(1) NOT NULL COMMENT 'is awaiting for being accepted',
  `accepted_by` int(10) unsigned DEFAULT NULL COMMENT 'who accepted it',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_images`
--

LOCK TABLES `player_images` WRITE;
/*!40000 ALTER TABLE `player_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `player_logins`
--

DROP TABLE IF EXISTS `player_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `player_logins` (
  `player_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `onetime` bit(1) NOT NULL,
  `origin` text NOT NULL,
  PRIMARY KEY (`player_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `player_logins`
--

LOCK TABLES `player_logins` WRITE;
/*!40000 ALTER TABLE `player_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `player_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `firstname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `lastname` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` tinytext CHARACTER SET latin1,
  `nick` varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `forumnick` varchar(25) CHARACTER SET latin1 DEFAULT '',
  `age` smallint(5) unsigned DEFAULT NULL,
  `country` tinytext CHARACTER SET latin1,
  `language` tinyint(4) NOT NULL DEFAULT '1',
  `password` tinytext CHARACTER SET latin1,
  `register` smallint(5) unsigned DEFAULT NULL,
  `lastdate` smallint(5) unsigned DEFAULT NULL,
  `lasttime` tinyint(3) unsigned DEFAULT NULL,
  `admin` tinyint(1) DEFAULT NULL,
  `lastlogin` text CHARACTER SET latin1,
  `lastminute` smallint(6) NOT NULL DEFAULT '0',
  `timeleft` smallint(6) DEFAULT '0',
  `approval` mediumint(8) unsigned DEFAULT NULL,
  `onleave` smallint(6) DEFAULT NULL,
  `trouble` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rated_today` tinyint(3) unsigned DEFAULT '1',
  `exitpage` tinyint(3) unsigned DEFAULT '0',
  `auto_events` tinyint(1) DEFAULT '0',
  `recent_activity` smallint(5) unsigned DEFAULT '0',
  `credits` mediumint(8) unsigned DEFAULT '0',
  `htmlmail` tinyint(4) DEFAULT '1',
  `pictures` tinyint(1) NOT NULL DEFAULT '1',
  `charset` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `notes` text CHARACTER SET latin1,
  `options` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'bit 0 - disable JavaScript, bit 1 - disable progress bars',
  `refplayer` mediumint(8) unsigned DEFAULT '0',
  `terms_of_use` tinyint(4) NOT NULL DEFAULT '0',
  `referrer` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `unsub_countdown` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'check if player wants unsub lock',
  `profile_options` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'bitmask of profile-specific options',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

-- add player with login 'cantr_test and password 'test'
LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES (100,'cantr_test','firstname','lastname','test@test.test','cantr_test','cantr_test',1987,'England',1,'$2y$10$8hbaZrhEIbVd2.1aTMeJGeWJLVkGw/XzLMN3rN/kMPjo202RRN5eG',3170,3701,6,0,'09/08/2012 12:37 89.67.123.34 (89-67-123-34.dynamic.chello.pl)',766,1875,49984,0,0,1,1,3,0,64543,0,1,1,NULL,NULL,8,0,0,NULL,0,0);
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players_report`
--

DROP TABLE IF EXISTS `players_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_report` (
  `contents` varchar(2048) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players_report`
--

LOCK TABLES `players_report` WRITE;
/*!40000 ALTER TABLE `players_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `players_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polls`
--

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls` (
  `id` smallint(6) NOT NULL DEFAULT '0',
  `title` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `design` text CHARACTER SET latin1 NOT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `sample_size` smallint(6) DEFAULT NULL,
  `enddate` smallint(6) DEFAULT NULL,
  `report` text CHARACTER SET latin1,
  `minimum_playing` smallint(6) DEFAULT NULL,
  `votes_cast` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polls`
--

LOCK TABLES `polls` WRITE;
/*!40000 ALTER TABLE `polls` DISABLE KEYS */;
/*!40000 ALTER TABLE `polls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polls_sample`
--

DROP TABLE IF EXISTS `polls_sample`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_sample` (
  `poll_id` smallint(6) DEFAULT NULL,
  `player_id` mediumint(9) DEFAULT NULL,
  `answer` text CHARACTER SET latin1 NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polls_sample`
--

LOCK TABLES `polls_sample` WRITE;
/*!40000 ALTER TABLE `polls_sample` DISABLE KEYS */;
/*!40000 ALTER TABLE `polls_sample` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pqueue`
--

DROP TABLE IF EXISTS `pqueue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pqueue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` mediumint(8) NOT NULL DEFAULT '0',
  `player` mediumint(8) unsigned DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `new_default` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `new` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `from` (`from`),
  KEY `player` (`player`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pqueue`
--

LOCK TABLES `pqueue` WRITE;
/*!40000 ALTER TABLE `pqueue` DISABLE KEYS */;
/*!40000 ALTER TABLE `pqueue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pqueuebackup`
--

DROP TABLE IF EXISTS `pqueuebackup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pqueuebackup` (
  `contentbackup` text CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pqueuebackup`
--

LOCK TABLES `pqueuebackup` WRITE;
/*!40000 ALTER TABLE `pqueuebackup` DISABLE KEYS */;
/*!40000 ALTER TABLE `pqueuebackup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `location` mediumint(5) unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `turnsleft` mediumint(5) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned DEFAULT NULL,
  `subtype` int(8) unsigned DEFAULT NULL,
  `result` text CHARACTER SET utf8,
  `turnsneeded` mediumint(5) unsigned DEFAULT NULL,
  `reqneeded` text CHARACTER SET latin1,
  `reqleft` text CHARACTER SET latin1,
  `initiator` mediumint(8) unsigned DEFAULT NULL,
  `init_day` smallint(5) unsigned DEFAULT NULL,
  `init_turn` tinyint(3) unsigned DEFAULT NULL,
  `steps` smallint(6) NOT NULL DEFAULT '0',
  `max_participants` smallint(6) NOT NULL DEFAULT '0',
  `skill` smallint(5) unsigned DEFAULT '0',
  `uses_digging_slot` tinyint(3) unsigned DEFAULT '0',
  `weight` bigint(20) NOT NULL DEFAULT '0',
  `automatic` tinyint(1) DEFAULT '0',
  `result_description` text CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `location` (`location`),
  KEY `subtype` (`subtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `radios`
--

DROP TABLE IF EXISTS `radios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radios` (
  `item` int(11) unsigned NOT NULL,
  `type` int(11) NOT NULL,
  `repeater` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `frequency` smallint(6) unsigned NOT NULL,
  `location` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  PRIMARY KEY (`item`),
  KEY `repeater` (`repeater`,`frequency`,`x`,`y`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radios`
--

LOCK TABLES `radios` WRITE;
/*!40000 ALTER TABLE `radios` DISABLE KEYS */;
/*!40000 ALTER TABLE `radios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raws`
--

DROP TABLE IF EXISTS `raws`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raws` (
  `location` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`location`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raws`
--

LOCK TABLES `raws` WRITE;
/*!40000 ALTER TABLE `raws` DISABLE KEYS */;
INSERT INTO `raws` VALUES (636,8),(636,39),(636,80),(637,1),(637,25),(637,33),(637,58),(638,4),(638,15),(638,23),(638,48),(639,4),(639,15),(639,16),(639,124),(640,40),(640,41),(640,43),(640,56),(640,80),(640,119);
/*!40000 ALTER TABLE `raws` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rawtools`
--

DROP TABLE IF EXISTS `rawtools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rawtools` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `tool` smallint(5) unsigned DEFAULT NULL,
  `rawtype` smallint(5) unsigned DEFAULT NULL,
  `perday` smallint(5) unsigned DEFAULT NULL,
  `projecttype` tinyint(3) unsigned DEFAULT NULL,
  `categories` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rawtype` (`rawtype`),
  KEY `tool` (`tool`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rawtools`
--

LOCK TABLES `rawtools` WRITE;
/*!40000 ALTER TABLE `rawtools` DISABLE KEYS */;
INSERT INTO `rawtools` VALUES (1,31,2,200,1,NULL),(2,31,3,200,1,NULL),(3,31,4,200,1,NULL),(6,31,15,200,1,NULL),(7,31,16,200,1,NULL),(8,31,17,200,1,NULL),(9,31,18,200,1,NULL),(10,31,19,200,1,NULL),(11,31,20,200,1,NULL),(12,31,22,200,1,NULL),(13,31,23,200,1,NULL),(14,31,50,200,1,NULL),(15,29,5,200,1,NULL),(16,29,34,200,1,NULL),(17,29,36,200,1,NULL),(18,29,37,200,1,NULL),(19,29,48,200,1,NULL),(20,32,8,200,1,NULL),(21,33,11,200,1,NULL),(22,34,25,200,1,NULL),(23,34,35,200,1,NULL),(24,35,38,200,1,NULL),(25,35,39,200,1,NULL),(26,35,40,200,1,NULL),(27,35,41,200,1,NULL),(28,35,43,200,1,NULL),(29,35,45,200,1,NULL),(32,17,24,200,1,NULL),(33,121,8,160,1,NULL),(34,103,8,150,1,NULL),(35,17,29,200,1,NULL),(36,17,28,180,1,NULL),(37,130,32,150,1,NULL),(38,35,58,200,1,NULL),(39,29,59,200,1,NULL),(40,188,76,500,1,NULL),(41,188,77,500,1,NULL),(42,190,76,350,1,NULL),(43,190,77,350,1,NULL),(44,205,1,200,1,NULL),(45,206,1,500,1,NULL),(46,17,44,200,1,NULL),(47,17,31,200,1,NULL),(48,29,21,200,1,NULL),(49,207,114,200,1,NULL),(50,207,115,200,1,NULL),(51,29,119,200,1,NULL),(52,31,124,200,1,NULL),(53,226,29,180,1,NULL),(54,226,44,180,1,NULL),(55,34,140,200,1,NULL),(56,33,144,200,1,NULL),(57,17,42,200,1,NULL),(58,226,42,190,1,NULL),(59,121,145,113,1,NULL),(60,32,145,150,1,NULL),(61,103,145,110,1,NULL),(62,31,149,200,1,NULL),(63,53,37,455,1,NULL),(64,105,41,500,1,NULL),(65,105,58,500,1,NULL),(66,105,40,500,1,NULL),(67,105,43,500,1,NULL),(68,105,45,500,1,NULL),(69,105,39,500,1,NULL),(70,207,113,200,1,NULL),(71,188,154,500,1,NULL),(72,190,154,350,1,NULL),(73,188,155,500,1,NULL),(74,190,155,350,1,NULL),(75,226,28,160,1,NULL),(76,226,31,180,1,NULL),(77,207,164,200,1,NULL),(78,207,166,200,1,NULL),(79,207,169,200,1,NULL),(80,207,174,200,1,NULL),(81,207,177,200,1,NULL),(82,207,181,200,1,NULL),(83,207,184,200,1,NULL),(84,647,184,150,1,NULL),(85,207,187,200,1,NULL),(86,207,190,200,1,NULL),(87,207,195,200,1,NULL),(88,207,199,200,1,NULL),(89,207,202,200,1,NULL),(90,207,205,200,1,NULL),(91,207,208,200,1,NULL),(92,207,211,200,1,NULL),(93,207,234,200,1,NULL),(94,265,7,300,1,NULL),(95,17,26,200,1,NULL),(96,226,26,180,1,NULL),(97,17,256,250,1,NULL),(98,226,256,175,1,NULL),(99,17,244,200,1,NULL),(100,226,244,150,1,NULL),(101,32,255,200,1,NULL),(102,121,255,160,1,NULL),(103,103,255,150,1,NULL),(104,17,243,280,1,NULL),(105,31,279,200,1,NULL),(106,31,280,200,1,NULL),(107,31,281,200,1,NULL),(108,31,282,200,1,NULL),(109,31,284,200,1,NULL),(110,31,285,200,1,NULL),(111,241,32,200,1,NULL),(112,207,56,125,1,NULL),(113,35,56,200,1,NULL),(114,105,56,500,1,NULL),(115,515,32,175,1,NULL),(116,29,0,300,5,NULL),(117,17,30,200,1,NULL),(118,226,30,180,1,NULL),(119,226,243,260,1,NULL),(120,541,8,133,1,NULL),(121,542,8,125,1,NULL),(123,202,0,150,6,NULL),(124,203,0,200,6,NULL),(125,207,172,200,1,NULL),(126,207,173,200,1,NULL),(127,207,180,200,1,NULL),(128,207,198,200,1,NULL),(129,17,193,200,1,NULL),(130,226,193,180,1,NULL),(131,342,44,300,1,NULL),(132,342,28,300,1,NULL),(133,342,58,300,1,NULL),(134,342,33,300,1,NULL),(135,48,0,133,17,NULL),(136,97,0,200,17,NULL),(137,439,0,120,17,NULL),(138,440,0,150,17,NULL),(139,637,0,100,17,NULL),(140,638,0,100,17,NULL),(141,639,0,100,17,NULL),(142,644,29,200,1,NULL),(143,644,44,200,1,NULL),(144,644,256,250,1,NULL),(145,644,28,180,1,NULL),(146,644,243,280,1,NULL),(147,644,31,200,1,NULL),(148,644,193,200,1,NULL),(149,644,42,200,1,NULL),(150,644,30,200,1,NULL),(151,644,26,200,1,NULL),(152,641,255,200,1,NULL),(153,641,145,150,1,NULL),(154,641,8,200,1,NULL),(155,647,164,150,1,NULL),(156,647,166,150,1,NULL),(157,647,169,150,1,NULL),(158,647,172,150,1,NULL),(159,647,173,150,1,NULL),(160,647,174,150,1,NULL),(161,647,115,150,1,NULL),(162,647,177,150,1,NULL),(163,647,180,150,1,NULL),(164,647,181,150,1,NULL),(165,647,56,125,1,NULL),(166,647,187,150,1,NULL),(167,647,190,150,1,NULL),(168,647,195,150,1,NULL),(169,647,198,150,1,NULL),(170,647,199,150,1,NULL),(171,647,114,150,1,NULL),(172,647,234,150,1,NULL),(173,647,211,150,1,NULL),(174,647,113,150,1,NULL),(175,647,208,150,1,NULL),(176,647,205,150,1,NULL),(177,647,202,150,1,NULL),(178,642,50,200,1,NULL),(179,642,20,200,1,NULL),(180,642,16,200,1,NULL),(181,642,23,200,1,NULL),(182,642,17,200,1,NULL),(183,642,3,200,1,NULL),(184,642,284,200,1,NULL),(185,642,2,200,1,NULL),(186,642,124,200,1,NULL),(187,642,279,200,1,NULL),(188,642,22,200,1,NULL),(189,642,149,200,1,NULL),(190,642,18,200,1,NULL),(191,642,281,200,1,NULL),(192,642,280,200,1,NULL),(193,642,285,200,1,NULL),(194,642,282,200,1,NULL),(195,642,19,200,1,NULL),(196,642,4,200,1,NULL),(197,642,15,200,1,NULL),(198,640,37,200,1,NULL),(199,640,59,200,1,NULL),(200,640,119,200,1,NULL),(201,640,48,200,1,NULL),(202,640,36,200,1,NULL),(203,640,5,200,1,NULL),(204,640,21,200,1,NULL),(205,640,34,200,1,NULL),(206,31,141,200,1,NULL),(207,642,141,200,1,NULL),(208,31,142,200,1,NULL),(209,642,142,200,1,NULL),(210,644,244,200,1,NULL),(211,649,40,500,1,NULL),(212,649,45,500,1,NULL),(213,649,43,500,1,NULL),(214,649,39,500,1,NULL),(215,649,41,500,1,NULL),(216,649,56,500,1,NULL),(217,649,58,500,1,NULL),(218,648,38,200,1,NULL),(219,648,40,200,1,NULL),(220,648,45,200,1,NULL),(221,648,43,200,1,NULL),(222,648,39,200,1,NULL),(223,648,41,200,1,NULL),(224,648,56,200,1,NULL),(225,648,58,200,1,NULL),(226,645,35,200,1,NULL),(227,645,140,200,1,NULL),(228,645,25,200,1,NULL),(229,207,116,200,1,NULL),(230,647,116,150,1,NULL),(231,1259,0,150,25,NULL),(232,542,0,100,25,NULL),(233,1268,37,473,1,NULL),(234,447,145,110,1,NULL),(235,813,238,250,1,NULL),(236,1541,0,250,26,'Any'),(237,1540,0,300,26,'Any'),(238,1539,0,350,26,'Any'),(239,1538,0,400,26,'Sharp'),(240,1537,0,600,26,'Sharp'),(241,1581,35,200,1,NULL),(242,1581,140,200,1,NULL),(243,1581,25,200,1,NULL);
/*!40000 ALTER TABLE `rawtools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rawtypes`
--

DROP TABLE IF EXISTS `rawtypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rawtypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext CHARACTER SET latin1,
  `perday` smallint(5) unsigned DEFAULT NULL,
  `action` tinytext CHARACTER SET latin1,
  `nutrition` smallint(5) DEFAULT '0',
  `strengthening` smallint(5) DEFAULT '0',
  `energy` smallint(5) DEFAULT '0',
  `drunkenness` smallint(6) NOT NULL DEFAULT '0',
  `group` tinyint(4) NOT NULL DEFAULT '0',
  `tainting` smallint(5) unsigned DEFAULT NULL,
  `density` smallint(6) NOT NULL DEFAULT '0',
  `skill` smallint(5) unsigned DEFAULT '0',
  `reqtools` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=566 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rawtypes`
--

LOCK TABLES `rawtypes` WRITE;
/*!40000 ALTER TABLE `rawtypes` DISABLE KEYS */;
INSERT INTO `rawtypes` VALUES (1,'oil',75,'dig',0,0,0,0,0,0,114,20,0),(2,'gold',10,'dig',0,0,0,0,0,0,0,18,0),(3,'diamonds',15,'dig',0,0,0,0,0,0,0,18,0),(4,'stone',750,'dig',0,0,0,0,0,0,40,18,0),(5,'sand',2000,'dig',0,0,0,0,0,0,57,18,0),(6,'water',100,'pump',0,1,0,0,0,0,100,0,0),(7,'gas',100,'dig',0,0,0,0,0,0,0,18,0),(8,'wood',300,'collect',0,0,0,0,0,0,200,11,0),(9,'minerals',200,'dig',0,0,0,0,0,0,0,0,0),(10,'iron',0,'dig',0,0,0,0,0,0,0,0,0),(11,'rubber',50,'collect',0,0,0,0,0,0,0,11,0),(12,'alumina',0,'',0,0,0,0,0,0,0,0,0),(13,'salt',250,'dig',0,0,0,0,0,0,78,18,0),(14,'steel',0,'dig',0,0,0,0,0,0,0,0,0),(15,'silver',14,'dig',0,0,0,0,0,0,0,16,0),(16,'coal',250,'dig',0,0,0,0,0,0,114,18,0),(17,'copper',175,'dig',0,0,0,0,0,0,0,18,0),(18,'nickel',200,'dig',0,0,0,0,0,0,0,16,0),(19,'tin',160,'dig',0,0,0,0,0,0,0,16,0),(20,'zinc',120,'dig',0,0,0,0,0,0,0,16,0),(21,'soda',320,'dig',0,0,0,0,0,0,0,18,0),(22,'magnesium',120,'dig',0,0,0,0,0,0,0,18,0),(23,'cobalt',110,'dig',0,0,0,0,0,0,0,16,0),(24,'food',1600,'farm',65,0,0,0,0,20,0,0,0),(25,'potatoes',1600,'farm',30,0,0,0,0,15,115,1,0),(26,'tomatos',400,'farm',0,38,0,0,0,25,157,1,0),(27,'mushrooms',200,'collect',0,57,0,0,0,10,0,19,0),(28,'coconuts',50,'collect',0,150,0,0,0,10,0,11,0),(29,'apples',100,'farm',0,57,0,0,0,20,156,11,0),(30,'pineapples',50,'farm',0,132,0,0,0,20,0,1,0),(31,'grapes',75,'farm',0,112,0,0,0,20,0,1,0),(32,'nuts',250,'collect',0,93,0,0,0,5,0,19,0),(33,'sugar',150,'farm',0,8,0,0,0,2,143,1,0),(34,'spinage',700,'farm',73,30,0,0,0,30,0,1,0),(35,'carrots',800,'farm',40,0,0,0,0,25,181,1,0),(36,'onions',650,'farm',0,75,0,0,0,5,154,1,0),(37,'asparagus',550,'farm',84,0,0,0,0,12,0,1,0),(38,'rice',1400,'farm',20,0,0,0,0,10,139,1,0),(39,'wheat',900,'farm',0,0,0,0,0,8,130,1,0),(40,'corn',870,'farm',0,0,0,0,0,8,139,1,0),(41,'barley',875,'farm',0,0,0,0,0,8,154,1,0),(42,'papayas',75,'farm',0,142,0,0,0,28,0,11,0),(43,'rye',575,'farm',0,0,0,0,0,7,143,1,0),(44,'bananas',275,'collect',0,90,0,0,0,22,0,11,0),(45,'sorghum',645,'farm',0,0,0,0,0,9,180,1,0),(46,'bread',0,'',210,0,0,0,0,8,0,0,0),(47,'rye bread',0,'',170,0,0,0,0,0,0,0,0),(48,'limestone',1100,'dig',0,0,0,0,0,0,0,18,0),(49,'stainless steel',0,'',0,0,0,0,0,0,0,0,0),(50,'chromium',86,'dig',0,0,0,0,0,0,0,18,0),(51,'glass',0,'',0,0,0,0,0,0,41,0,0),(52,'mout whisky',0,'',0,60,0,0,0,0,0,0,0),(53,'grain whisky',0,'',0,50,0,0,0,0,0,0,0),(54,'blended whisky',0,'',0,55,0,0,0,0,0,0,0),(55,'mud',850,'dig',0,0,0,0,0,0,57,18,0),(56,'grass',400,'farm',0,0,0,0,0,0,0,1,0),(57,'hay',0,'',0,0,0,0,0,0,0,0,0),(58,'reed',225,'collect',0,0,0,0,0,0,0,19,0),(59,'bauxite',130,'dig',0,0,0,0,0,0,139,16,0),(69,'popcorn',0,'',0,90,0,0,0,10,0,0,0),(70,'cookies',0,'',0,120,0,0,0,0,0,0,0),(72,'wheat flour',0,'',0,0,0,0,0,0,150,0,0),(73,'rye flour',0,'',0,0,0,0,0,0,150,0,0),(74,'aluminium',0,'',0,0,0,0,0,0,0,0,0),(76,'cod',100,'catch',0,0,0,0,0,10,0,17,0),(77,'rainbow trout',100,'catch',0,0,0,0,0,10,0,17,0),(78,'baked trout',0,'',250,0,0,0,0,0,0,0,0),(79,'baked cod',0,'',250,0,0,0,0,0,0,0,0),(80,'cotton',200,'farm',0,0,0,0,0,2,0,1,0),(86,'sorghum flour',0,'',0,0,0,0,0,0,150,0,0),(87,'sorghum bread',0,'',230,0,0,0,0,0,200,0,0),(88,'blackberries',225,'collect',0,55,0,0,0,35,150,19,0),(89,'cotton yarn',0,'',0,0,0,0,0,1,125,0,0),(90,'thin rope',0,'',0,0,0,0,0,1,140,0,0),(91,'cotton cloth',0,'',0,0,0,0,0,1,110,0,0),(92,'silk cocoons',75,'collect',0,0,0,0,0,1,175,19,0),(93,'silk yarn',0,'',0,0,0,0,0,1,90,0,0),(94,'wool',300,'collect',0,0,0,0,0,1,135,0,0),(95,'wool yarn',0,'',0,0,0,0,0,1,135,0,0),(96,'blueberries',200,'collect',0,45,0,0,0,27,100,19,0),(97,'silk cloth',0,'',0,0,0,0,0,1,89,0,0),(98,'cotton fibers',0,'',0,0,0,0,0,1,130,0,0),(99,'platinum',10,'dig',0,0,0,0,0,0,25,16,0),(100,'hemp',850,'farm',0,0,0,0,0,4,190,1,0),(101,'animal hide',0,'',0,0,0,0,0,1,35,0,0),(102,'hide',0,'',0,0,0,0,0,1,30,0,0),(103,'leather',0,'',0,0,0,0,0,0,28,0,0),(104,'meat',0,'',0,0,0,0,0,20,0,0,0),(105,'feathers',0,'',0,0,0,0,0,8,0,0,0),(106,'fur',0,'',0,0,0,0,0,25,65,0,0),(107,'cooked meat',0,'',125,0,0,0,0,15,0,0,0),(108,'seaweed',135,'collect',30,30,0,0,0,1,54,19,0),(109,'grilled meat',0,'',111,0,0,0,0,20,0,0,0),(110,'pancake',0,'',125,0,0,0,0,0,15,0,0),(111,'pastry dough',0,'',0,0,0,0,0,0,64,0,0),(112,'pizza',0,'',500,0,0,0,0,0,55,0,0),(113,'tulips',200,'collect',0,0,0,0,0,20,40,19,0),(114,'roses',200,'collect',0,0,0,0,0,20,50,19,0),(115,'daisies',180,'collect',0,0,0,0,0,10,35,19,0),(116,'buttercups',180,'collect',0,0,0,0,0,10,35,19,0),(117,'sushi (fish)',0,'',105,0,0,0,0,0,90,0,0),(118,'sushi (meat)',0,'',110,0,0,0,0,0,90,0,0),(119,'clay',900,'dig',0,0,0,0,0,0,22,18,0),(120,'charcoal',0,'',0,0,0,0,0,0,34,0,0),(121,'obsidian',40,'dig',0,0,0,0,0,0,20,18,0),(122,'tusk',0,'',0,0,0,0,0,0,100,0,0),(123,'shell',0,'',0,0,0,0,0,0,40,0,0),(124,'hematite',750,'dig',0,0,0,0,0,0,10,16,0),(125,'ivory',0,'',0,0,0,0,0,0,25,0,0),(126,'boerenkool',900,'farm',65,0,0,0,0,5,140,1,0),(127,'small bones',0,'',0,0,0,0,0,5,25,0,0),(128,'large bones',0,'',0,0,0,0,0,5,22,0,0),(129,'hemp cloth',0,'',0,0,0,0,0,1,20,0,0),(130,'hemp yarn',0,'',0,0,0,0,0,2,30,0,0),(131,'tortoiseshell',0,'',0,0,0,0,0,0,15,0,0),(132,'snakeskin',0,'',0,0,0,0,0,1,50,0,0),(133,'crocodile hide',0,'',0,0,0,0,0,1,25,0,0),(134,'iron ore',0,'',0,0,0,0,0,0,7,0,0),(135,'medium rope',0,'',0,0,0,0,0,1,560,0,0),(136,'thick rope',0,'',0,0,0,0,0,1,1680,0,0),(137,'string',0,'',0,0,0,0,0,0,47,0,0),(138,'treated snakeskin',0,'',0,0,0,0,0,0,35,0,0),(139,'pearls',20,'collect',0,0,0,0,0,0,30,19,0),(140,'pumpkin',720,'farm',90,0,0,0,0,12,180,1,0),(141,'magnetite',750,'dig',0,0,0,0,0,0,25,18,0),(142,'taconite',1500,'dig',0,0,0,0,0,0,25,18,0),(143,'treated crocodile leather',0,'',0,0,0,0,0,0,30,0,0),(144,'maple sap',50,'collect',0,0,0,0,0,1,75,19,0),(145,'timber',2000,'farm',0,0,0,0,0,1,200,11,1),(146,'graphite',0,'',0,0,0,0,0,0,20,0,0),(147,'grease',0,'',0,0,0,0,0,0,30,0,0),(148,'blank',0,'',0,0,0,0,0,0,15,0,0),(149,'marble',265,'dig',0,0,0,0,0,0,15,18,0),(150,'brick',0,'',0,0,0,0,0,0,142,0,0),(151,'glass beads',0,'',0,0,0,0,0,0,100,23,0),(152,'ginseng',200,'collect',0,65,0,0,0,15,0,19,0),(154,'salmon',100,'catch',0,0,0,0,0,10,0,17,0),(155,'pike',100,'catch',0,0,0,0,0,10,0,17,0),(156,'baked salmon',0,'',250,0,0,0,0,0,0,0,0),(157,'baked pike',0,'',250,0,0,0,0,0,0,0,0),(158,'dried meat',0,'',168,0,0,0,0,1,0,0,0),(159,'smoked meat',0,'',182,0,0,0,0,20,0,0,0),(160,'smoked salmon',0,'',300,0,0,0,0,20,0,0,0),(161,'smoked cod',0,'',300,0,0,0,0,20,0,0,0),(162,'smoked pike',0,'',350,0,0,0,0,20,0,0,0),(163,'smoked trout',0,'',250,0,0,0,0,20,0,0,0),(164,'aloe vera',200,'collect',0,0,0,0,0,20,40,19,0),(165,'crushed aloe vera',0,'',0,100,0,0,0,2,0,0,0),(166,'arnica',240,'collect',0,0,0,0,0,30,0,19,0),(167,'dry arnica',0,'',0,0,0,0,0,55,0,0,0),(168,'crushed arnica',0,'',0,0,0,0,0,2,0,0,0),(169,'basil',350,'collect',0,0,0,0,0,15,0,19,0),(170,'dry basil',0,'',0,0,0,0,0,35,0,0,0),(171,'crushed basil',0,'',0,0,0,0,0,2,0,0,0),(172,'bergamot oil',300,'collect',0,0,0,0,0,10,0,19,0),(173,'camphor oil',280,'collect',0,0,0,0,0,10,0,19,0),(174,'clove',280,'collect',0,0,0,0,0,15,0,19,0),(175,'dry clove',0,'',0,0,0,0,0,40,0,0,0),(176,'crushed clove',0,'',0,0,0,0,0,2,0,0,0),(177,'dill',330,'collect',0,0,0,0,0,12,0,19,0),(178,'dry dill',0,'',0,0,0,0,0,38,0,0,0),(179,'crushed dill',0,'',0,0,0,0,0,2,0,0,0),(180,'eucalyptus oil',200,'collect',0,0,0,0,0,18,0,19,0),(181,'garlic',200,'collect',0,0,0,0,0,19,0,19,0),(182,'dry garlic',0,'',0,0,0,0,0,62,0,0,0),(183,'crushed garlic',0,'',0,0,0,0,0,2,0,0,0),(184,'ginger',290,'collect',0,0,0,0,0,20,0,19,0),(185,'dry ginger',0,'',0,0,0,0,0,34,0,0,0),(186,'crushed ginger',0,'',0,0,0,0,0,2,0,0,0),(187,'hamamelis',220,'collect',0,0,0,0,0,10,0,19,0),(188,'dry hamamelis',0,'',0,0,0,0,0,30,0,0,0),(189,'crushed hamamelis',0,'',0,0,0,0,0,2,0,0,0),(190,'lavender',270,'collect',0,0,0,0,0,19,0,19,0),(191,'dry lavender',0,'',0,0,0,0,0,42,0,0,0),(192,'crushed lavender',0,'',0,0,0,0,0,2,0,0,0),(193,'lemons',270,'collect',0,0,0,0,0,20,0,19,0),(194,'lemon juice',0,'',0,200,0,0,0,0,0,0,0),(195,'myrrh',240,'collect',0,0,0,0,0,20,0,19,0),(196,'dry myrrh',0,'',0,0,0,0,0,32,0,0,0),(197,'crushed myrrh',0,'',0,0,0,0,0,2,0,0,0),(198,'patchouli oil',350,'collect',0,0,0,0,0,8,0,19,0),(199,'peppermint',330,'collect',0,0,0,0,0,10,0,19,0),(200,'dry peppermint',0,'',0,0,0,0,0,18,0,0,0),(201,'crushed peppermint',0,'',0,0,0,0,0,2,0,0,0),(202,'sage',160,'collect',0,0,0,0,0,9,0,19,0),(203,'dry sage',0,'',0,0,0,0,0,26,0,0,0),(204,'crushed sage',0,'',0,0,0,0,0,2,0,0,0),(205,'tarragon',160,'collect',0,0,0,0,0,12,0,19,0),(206,'dry tarragon',0,'',0,0,0,0,0,22,0,0,0),(207,'crushed tarragon',0,'',0,0,0,0,0,2,0,0,0),(208,'thyme',200,'collect',0,0,0,0,0,6,0,19,0),(209,'dry thyme',0,'',0,0,0,0,0,30,0,0,0),(210,'crushed thyme',0,'',0,0,0,0,0,2,0,0,0),(211,'white willow',300,'collect',0,0,0,0,0,10,0,19,0),(212,'dry white willow',0,'',0,0,0,0,0,10,0,0,0),(213,'crushed white willow',0,'',0,0,0,0,0,2,0,0,0),(214,'herbal mixture (a)',0,'',0,0,0,0,0,0,0,0,0),(215,'herbal mixture (b)',0,'',0,0,0,0,0,0,0,0,0),(216,'herbal mixture (c)',0,'',0,0,0,0,0,0,0,0,0),(217,'herbal mixture (d)',0,'',0,0,0,0,0,0,0,0,0),(218,'herbal mixture (e)',0,'',0,0,0,0,0,0,0,0,0),(219,'herbal mixture (f)',0,'',0,0,0,0,0,0,0,0,0),(220,'herbal mixture (g)',0,'',0,0,0,0,0,0,0,0,0),(221,'herbal mixture (h)',0,'',0,0,0,0,0,0,0,0,0),(222,'herbal mixture (i)',0,'',0,0,0,0,0,0,0,0,0),(223,'herbal mixture (j)',0,'',0,0,0,0,0,0,0,0,0),(224,'healing liquid (a)',0,'',0,220,0,0,0,0,0,0,0),(225,'healing liquid (b)',0,'',0,0,9,0,0,0,0,0,0),(226,'healing liquid (c)',0,'',0,205,0,0,0,0,0,0,0),(227,'healing liquid (d)',0,'',0,225,0,0,0,0,0,0,0),(228,'healing liquid (e)',0,'',0,220,0,0,0,0,0,0,0),(229,'healing liquid (f)',0,'',0,250,0,0,0,0,0,0,0),(230,'healing liquid (g)',0,'',0,0,7,0,0,0,0,0,0),(231,'healing liquid (h)',0,'',0,270,0,0,0,0,0,0,0),(232,'healing liquid (i)',0,'',0,0,16,0,0,0,0,0,0),(233,'healing liquid (j)',0,'',0,0,5,0,0,0,0,0,0),(234,'yarrow',300,'collect',0,0,0,0,0,9,0,19,0),(235,'dry yarrow',0,'',0,0,0,0,0,44,0,0,0),(236,'crushed yarrow',0,'',0,0,0,0,0,2,0,0,0),(237,'propane',0,'',0,0,0,0,0,0,0,0,0),(238,'olives',400,'collect',80,0,0,0,0,3,85,11,0),(239,'olive paste',0,'',0,0,0,0,0,10,75,0,0),(240,'pomace',0,'',0,0,0,0,0,5,95,0,0),(241,'blended olive oil',0,'',0,0,0,0,0,8,95,0,0),(242,'olive oil',0,'',0,0,0,0,0,1,55,0,0),(243,'coffee cherries',280,'collect',0,0,0,0,0,8,0,19,0),(244,'tea leaves',240,'collect',0,0,0,0,0,4,0,19,0),(245,'roasted coffee',0,'',0,0,0,0,0,0,0,0,0),(246,'ground coffee',0,'',0,0,0,0,0,3,0,0,0),(247,'crushed tea',0,'',0,0,0,0,0,2,0,0,0),(248,'dry tea',0,'',0,0,0,0,0,35,0,0,0),(249,'coffee',0,'',0,0,13,0,0,0,100,0,0),(250,'tea',0,'',0,0,4,0,0,0,100,0,0),(251,'olive juice',0,'',0,0,0,0,0,1,60,0,0),(252,'glass bars',0,'',0,0,0,0,0,0,100,23,0),(253,'window glass',0,'',0,0,0,0,0,0,100,23,0),(254,'coke',0,'',0,0,0,0,0,0,100,0,0),(255,'resin',80,'collect',0,0,0,0,0,0,0,19,0),(256,'beeswax',170,'collect',0,0,0,0,0,0,0,19,0),(257,'bitumen',100,'dig',0,0,0,0,0,0,0,18,0),(258,'sealing wax',0,'',0,0,0,0,0,0,0,0,0),(259,'wax mixture',0,'',0,0,0,0,0,0,0,0,0),(260,'sinew',0,'',0,0,0,0,0,5,15,0,0),(261,'cotton string',0,'',0,0,0,0,0,1,100,0,0),(262,'hemp string',0,'',0,0,0,0,0,1,100,0,0),(263,'silk string',0,'',0,0,0,0,0,1,100,0,0),(264,'fermented coffee cherries',0,'',0,0,0,0,0,5,0,0,0),(265,'coffee beans',0,'',0,0,0,0,0,5,0,0,0),(266,'ground coffee beans',0,'',0,5,0,0,0,0,0,0,0),(267,'bronze',0,'',0,0,0,0,0,0,0,0,0),(268,'phosphorus',130,'dig',0,0,0,0,0,0,0,16,0),(269,'salted cod',0,'',350,0,0,0,0,10,0,0,0),(270,'salted pike',0,'',400,0,0,0,0,10,0,0,0),(271,'salted salmon',0,'',350,0,0,0,0,10,0,0,0),(272,'salted trout',0,'',300,0,0,0,0,10,0,0,0),(273,'salted meat',0,'',143,0,0,0,0,20,0,0,0),(274,'meat jerky',0,'',212,0,0,0,0,10,0,0,0),(275,'cucumbers',500,'farm',132,0,0,0,0,40,80,1,0),(276,'pickles',100,'collect',190,150,0,0,0,6,75,21,0),(277,'strawberries',215,'collect',0,75,0,0,0,30,0,19,0),(278,'raspberries',300,'collect',0,40,0,0,0,0,0,19,0),(279,'jade',12,'dig',0,0,0,0,0,0,0,16,0),(280,'sapphire',11,'dig',0,0,0,0,0,0,0,16,0),(281,'ruby',20,'dig',0,0,0,0,0,0,0,18,0),(282,'topaz',15,'dig',0,0,0,0,0,0,0,18,0),(283,'opal',10,'dig',0,0,0,0,0,0,0,16,0),(284,'emerald',8,'dig',0,0,0,0,0,0,0,18,0),(285,'turquoise',12,'dig',0,0,0,0,0,0,0,18,0),(286,'water (test)',0,'',0,0,0,0,0,0,0,0,0),(287,'milk',0,'',0,50,0,0,0,30,100,0,0),(288,'eggs',0,'',0,0,0,0,0,10,35,0,0),(289,'scrambled eggs',0,'',0,400,0,0,0,20,50,25,0),(290,'boiled eggs',0,'',0,375,0,0,0,15,35,25,0),(291,'ice cream',0,'',0,150,0,0,0,75,50,25,0),(292,'cheese curds',0,'',100,0,0,0,0,0,60,0,0),(293,'cheese',0,'',280,0,0,0,0,0,35,0,0),(294,'fresh dung',0,NULL,0,0,0,0,0,50,0,0,0),(295,'dried dung',0,'',0,0,0,0,0,10,0,0,0),(296,'potato salad',0,'',180,0,0,0,0,0,0,0,0),(297,'salad',0,'',300,76,0,0,0,0,0,0,0),(298,'granola',0,'',285,98,0,0,0,0,50,0,0),(299,'stew',0,'',350,0,0,0,0,0,50,0,0),(300,'porridge',0,'',330,0,0,0,0,0,65,0,0),(301,'wool cloth',0,'',0,0,0,0,0,1,100,0,0),(302,'salted asparagus',0,'',117,0,0,0,0,6,75,0,0),(303,'salted carrots',0,'',130,0,0,0,0,6,75,0,0),(304,'steamed rice',0,'',125,0,0,0,0,0,90,0,0),(305,'baked potatoes',0,'',166,0,0,0,0,0,75,0,0),(306,'mashed potatoes',0,'',180,0,0,0,0,0,65,0,0),(313,'dried cod',0,'',400,0,0,0,0,15,50,0,0),(314,'dried trout',0,'',375,0,0,0,0,15,50,0,0),(315,'grilled asparagus',0,'',196,0,0,0,0,20,75,0,0),(316,'roasted carrots',0,'',200,0,0,0,0,0,75,0,0),(317,'potato chips',0,'',125,0,0,0,0,2,200,0,0),(318,'rice cakes',0,'',142,0,0,0,0,2,200,0,0),(319,'meat pie',0,'',300,0,0,0,0,0,105,0,0),(320,'fries',0,'',320,0,0,0,0,15,100,0,0),(322,'rice balls',0,'',250,0,0,0,0,0,90,0,0),(323,'herbal mixture (k)',0,'',0,0,0,0,0,0,0,0,0),(324,'herbal mixture (l)',0,'',0,0,0,0,0,0,0,0,0),(325,'herbal mixture (m)',0,'',0,0,0,0,0,0,0,0,0),(326,'herbal mixture (n)',0,'',0,0,0,0,0,0,0,0,0),(327,'healing liquid (k)',0,'',0,200,0,0,0,0,0,0,0),(328,'healing liquid (l)',0,'',0,330,0,0,0,0,0,0,0),(329,'healing liquid (m)',0,'',0,210,0,0,0,0,0,0,0),(330,'healing liquid (n)',0,'',0,0,11,0,0,0,0,0,0),(331,'fertilizer',0,'',0,0,0,0,0,0,0,0,0),(332,'apple pie',0,'',250,114,0,0,0,0,20,0,0),(333,'fruit salad',0,'',375,140,0,0,0,0,70,0,0),(334,'alcohol',0,'',0,0,0,0,0,0,100,0,0),(335,'impure biodiesel',0,'',0,0,0,0,0,75,100,0,0),(336,'biodiesel',0,'',0,0,0,0,0,75,90,0,0),(338,'pretzels',0,'',330,0,0,0,0,3,0,0,0),(339,'raisins',0,'',0,125,0,0,0,5,10,0,0),(340,'cornmeal',0,'',0,0,0,0,0,0,150,0,0),(341,'tortillas',0,'',250,0,0,0,0,0,0,0,0),(342,'cornbread',0,'',285,60,0,0,0,0,0,0,0),(343,'grits',0,'',200,40,0,0,0,0,0,0,0),(344,'rice pudding',0,'',0,90,0,0,0,0,50,0,0),(345,'cheese sandwich',0,'',250,100,0,0,0,0,0,0,0),(346,'petrol',0,'',0,0,0,0,0,50,150,0,0),(347,'fish sandwich',0,'',500,0,0,0,0,0,0,0,0),(348,'dried pike',0,'',375,0,0,0,0,15,50,0,0),(349,'dried salmon',0,'',375,0,0,0,0,15,50,0,0),(350,'clay beads',0,'',0,0,0,0,0,0,75,0,0),(351,'ceramic clay',0,'',0,0,0,0,0,0,22,0,0),(352,'porcelain clay',0,'',0,0,0,0,0,0,22,0,0),(353,'sailcloth',0,'',0,0,0,0,0,0,22,0,0),(354,'croissants',0,'',0,120,0,0,0,0,0,0,0),(355,'couscous',0,'',0,90,0,0,0,0,0,0,0),(356,'honey',0,'',0,100,0,0,0,1,0,0,0),(357,'wine',0,'',0,312,0,188,0,0,10,0,0),(358,'grape juice',0,'',0,188,0,0,0,0,10,0,0),(359,'apple juice',0,'',0,114,0,0,0,0,10,0,0),(360,'cider',0,'',0,257,0,47,0,0,10,0,0),(361,'clarified honey',0,'',0,225,0,0,0,0,10,0,0),(362,'mead',0,'',0,350,0,188,0,0,10,0,0),(363,'polished rice',0,'',86,0,0,0,0,0,50,0,0),(364,'amazake',0,'',0,0,0,0,0,0,25,0,0),(365,'sake',0,'',0,110,0,188,0,0,10,0,0),(366,'malt',0,'',0,0,0,0,0,0,50,0,0),(367,'wort',0,'',0,0,0,0,0,0,25,0,0),(368,'beer',0,'',0,65,0,47,0,0,10,0,0),(369,'dried mushrooms',0,'',0,120,0,0,0,1,0,0,0),(370,'fish cakes',0,'',300,0,0,0,0,0,0,0,0),(371,'mushroom risotto',0,'',250,0,0,0,0,0,0,0,0),(372,'stewed tomatoes',0,'',0,75,0,0,0,0,0,0,0),(373,'carrot stew',0,'',200,0,0,0,0,0,0,0,0),(374,'saag aloo',0,'',300,0,0,0,0,0,0,0,0),(375,'kebabs',0,'',143,0,0,0,0,0,0,0,0),(376,'roasted potatoes',0,'',300,0,0,0,0,0,0,0,0),(377,'aspic',0,'',250,0,0,0,0,0,0,0,0),(378,'pierogi with cheese',0,'',500,0,0,0,0,0,0,0,0),(379,'broth',0,'',250,0,0,0,0,0,0,0,0),(380,'chowder',0,'',350,0,0,0,0,0,0,0,0),(381,'gumbo',0,'',400,0,0,0,0,0,0,0,0),(382,'tamales',0,'',300,0,0,0,0,0,0,0,0),(383,'congee',0,'',150,0,0,0,0,0,0,0,0),(384,'kisiel',0,'',0,100,0,0,0,0,0,0,0),(385,'burgers',0,'',350,0,0,0,0,0,0,0,0),(386,'tacos',0,'',350,0,0,0,0,0,0,0,0),(387,'pupusas',0,'',350,0,0,0,0,0,0,0,0),(388,'stone blocks',0,'',0,0,0,0,0,0,150,0,0),(389,'potato omelette',0,'',0,110,0,0,0,0,0,0,0),(390,'vegetable cakes',0,'',370,0,0,0,0,0,0,0,0),(391,'spinach soup with garlic',0,'',0,76,0,0,0,0,0,0,0),(392,'gingerbread',0,'',0,150,0,0,0,0,0,0,0),(393,'raspberry jam',0,'',0,175,0,0,0,0,0,0,0),(394,'piernik',0,'',0,400,0,0,0,0,0,0,0),(395,'tepache',0,'',0,178,0,16,0,0,0,0,0),(396,'mulled wine',0,'',0,200,0,188,0,0,0,0,0),(397,'denim',0,'',0,0,0,0,0,1,110,0,0),(398,'potato mash',0,'',0,0,0,0,0,0,100,0,0),(399,'grain spirit',0,'',0,100,0,375,0,0,100,0,0),(400,'rice spirit',0,'',0,165,0,375,0,0,100,0,0),(401,'potato spirit',0,'',0,100,0,375,0,0,100,0,0),(402,'apple brandy',0,'',0,385,0,375,0,0,100,0,0),(403,'brandy',0,'',0,468,0,416,0,0,100,0,0),(404,'rice paste',0,'',0,0,0,0,0,0,0,25,0),(405,'rice paper',0,'',0,0,0,0,0,0,0,4,0),(406,'explosive powder',0,'',0,0,0,0,0,0,0,0,0),(407,'fish flour',0,'',0,0,0,0,0,0,0,0,0),(408,'meat feed',0,'',0,0,0,0,0,8,130,0,0),(409,'vegetable feed',0,'',0,0,0,0,0,8,130,0,0),(410,'sauerkraut',0,'',137,0,0,0,0,0,75,0,0),(411,'beef',0,'',0,0,0,0,0,8,130,0,0),(412,'mutton',0,'',0,0,0,0,0,8,130,0,0),(413,'pork',0,'',0,0,0,0,0,8,130,0,0),(414,'dry roses',0,'',0,0,0,0,0,37,0,0,0),(415,'dry buttercups',0,'',0,0,0,0,0,18,0,0,0),(416,'dry daisies',0,'',0,0,0,0,0,18,0,0,0),(417,'poison (a)',0,'',0,-205,0,0,0,25,0,0,0),(418,'poison (b)',0,'',0,0,-205,0,0,25,0,0,0),(419,'poison (c)',0,'',-205,0,0,0,0,25,0,0,0),(420,'poison (d)',0,'',0,-330,0,0,0,75,0,0,0),(421,'poison (e)',0,'',0,0,-303,0,0,75,0,0,0),(422,'poison (f)',0,'',-303,0,0,0,0,75,0,0,0),(423,'maple syrup',0,'',0,200,0,0,0,0,10,0,0),(424,'lamb sausage',0,'',0,0,0,0,0,0,0,25,0),(425,'pork sausage',0,'',0,0,0,0,0,0,0,25,0),(426,'beef sausage',0,'',0,0,0,0,0,0,0,25,0),(427,'poultry sausage',0,'',0,0,0,0,0,0,0,25,0),(428,'ground meat',0,'',0,0,0,0,0,0,0,25,0),(429,'merguez',0,'',550,0,0,0,0,0,0,25,0),(430,'andouille',0,'',500,0,0,0,0,0,0,25,0),(431,'liverwurst',0,'',550,0,0,0,0,0,0,25,0),(432,'bangers and mash',0,'',550,0,0,0,0,0,0,25,0),(433,'mortadella',0,'',550,0,0,0,0,0,0,25,0),(434,'yun cheong',0,'',550,0,0,0,0,0,0,25,0),(435,'black pudding',0,'',550,0,0,0,0,0,0,25,0),(436,'meatloaf',0,'',550,0,0,0,0,0,0,25,0),(437,'keema',0,'',400,0,0,0,0,0,0,25,0),(438,'shawarma',0,'',550,0,0,0,0,0,0,25,0),(439,'cocido',0,'',500,0,0,0,0,0,0,25,0),(440,'coq-au-vin',0,'',0,180,0,0,0,0,0,25,0),(441,'chateaubriand',0,'',0,130,0,0,0,0,0,25,0),(442,'beef wellington',0,'',550,0,0,0,0,0,0,25,0),(443,'ham',0,'',420,0,0,0,0,0,0,25,0),(444,'menudo',0,'',0,140,0,0,0,0,0,25,0),(445,'pozole',0,'',0,140,0,0,0,0,0,25,0),(446,'matzoh balls soup',0,'',0,140,0,0,0,0,0,25,0),(447,'bigos',0,'',520,0,0,0,0,0,0,25,0),(448,'brisket',0,'',400,0,0,0,0,0,0,0,0),(449,'shortribs',0,'',420,0,0,0,0,0,0,0,0),(450,'rack of lamb',0,'',450,0,0,0,0,0,0,0,0),(451,'barbeque sauce',0,'',0,350,0,0,0,0,0,0,0),(452,'mint jelly',0,'',0,180,0,0,0,0,0,0,0),(453,'applesauce',0,'',0,90,0,0,0,0,0,0,0),(456,'aioli',0,'',0,200,0,0,0,0,0,0,0),(457,'hog roast',0,'',415,0,0,0,0,0,0,0,0),(458,'bone ash',0,'',0,0,0,0,0,0,0,0,0),(459,'bone china',0,'',0,0,0,0,0,0,0,0,0),(460,'poultry',0,'',0,0,0,0,0,8,130,0,0),(461,'wings',0,'',420,0,0,0,0,0,0,0,0),(462,'plain pasta',0,'',100,0,0,0,0,0,0,0,0),(463,'gourmet pasta',0,'',133,0,0,0,0,0,0,0,0),(464,'rice noodles',0,'',133,0,0,0,0,0,0,0,0),(465,'lasagna',0,'',333,0,0,0,0,0,0,0,0),(466,'macaroni and cheese',0,'',333,0,0,0,0,0,0,0,0),(467,'spaghetti with tomato sauce',0,'',285,0,0,0,0,0,0,0,0),(468,'poultry noodle wok',0,'',400,0,0,0,0,0,0,0,0),(469,'pork noodle wok',0,'',500,0,0,0,0,0,0,0,0),(470,'ramen',0,'',416,0,0,0,0,0,0,0,0),(471,'beef stroganoff',0,'',500,0,0,0,0,0,0,0,0),(472,'pasta with vegetables',0,'',416,0,0,0,0,0,0,0,0),(473,'pasta with meat',0,'',358,0,0,0,0,0,0,0,0),(474,'pasta with pesto',0,'',454,0,0,0,0,0,0,0,0),(475,'honey pasta',0,'',416,0,0,0,0,0,0,0,0),(476,'blueberry juice',0,'',0,125,0,0,0,0,10,0,0),(477,'blackberry juice',0,'',0,125,0,0,0,0,10,0,0),(478,'raspberry juice',0,'',0,125,0,0,0,0,10,0,0),(479,'strawberry juice',0,'',0,140,0,0,0,0,10,0,0),(480,'palm nectar',0,'',0,140,0,0,0,0,10,0,0),(481,'palm wine',0,'',0,270,0,90,0,0,10,0,0),(482,'blueberry wine',0,'',0,200,0,90,0,0,10,0,0),(483,'blackberry wine',0,'',0,200,0,90,0,0,10,0,0),(484,'raspberry wine',0,'',0,200,0,90,0,0,10,0,0),(485,'strawberry wine',0,'',0,175,0,90,0,0,10,0,0),(486,'sujuk',0,'',400,0,0,0,0,0,0,0,0),(487,'linguica',0,'',400,0,0,0,0,0,0,0,0),(488,'rookworst',0,'',400,0,0,0,0,0,0,0,0),(489,'stamppot',0,'',500,0,0,0,0,0,0,0,0),(490,'lamb curry',0,'',500,0,0,0,0,0,0,0,0),(491,'mushroom soup',0,'',0,165,0,0,0,0,0,0,0),(492,'haggis',0,'',450,0,0,0,0,0,0,0,0),(493,'strawberry cheesecake',0,'',357,0,0,0,0,0,0,0,0),(494,'tefteli',0,'',450,0,0,0,0,0,0,0,0),(495,'hard candy',0,'',50,0,0,0,0,0,0,0,0),(496,'maple sugar candy',0,'',60,0,0,0,0,0,0,0,0),(497,'lokum',0,'',50,0,0,0,0,0,0,0,0),(498,'candied ginger',0,'',85,0,0,0,0,0,0,0,0),(499,'lemon drops',0,'',0,0,5,0,0,0,0,0,0),(500,'nut brittle',0,'',125,0,0,0,0,0,0,0,0),(501,'pumpkin pie',0,'',333,0,0,0,0,0,0,0,0),(502,'pumpkin soup',0,'',200,0,0,0,0,0,0,0,0),(503,'pumpkin stew',0,'',370,0,0,0,0,0,0,0,0),(504,'pumpkin curry',0,'',345,0,0,0,0,0,0,0,0),(505,'pumpkin seeds',0,'',132,0,0,0,0,0,0,0,0),(506,'spinach pie',0,'',333,0,0,0,0,0,0,0,0),(507,'cream of onion soup',0,'',400,0,0,0,0,0,0,0,0),(508,'coffee candy',0,'',50,0,0,0,0,0,0,0,0),(509,'champagne',0,'',0,333,0,190,0,0,0,0,0),(510,'akvavit',0,'',0,150,0,380,0,0,0,0,0),(511,'amaretto',0,'',0,100,0,250,0,0,0,0,0),(512,'ginger beer',0,'',0,50,0,50,0,0,0,0,0),(513,'grog',0,'',0,80,0,166,0,0,0,0,0),(514,'rum',0,'',0,250,0,500,0,0,0,0,0),(515,'coffee liqueur',0,'',0,15,0,180,0,0,0,0,0),(516,'cream liqueur',0,'',0,100,0,130,0,0,0,0,0),(517,'limoncello',0,'',0,150,0,300,0,0,0,0,0),(518,'molasses',0,'',0,16,0,0,0,0,0,0,0),(519,'crushed nuts',0,'',0,0,0,0,0,0,0,0,0),(520,'ginger syrup',0,'',0,20,0,0,0,0,0,0,0),(521,'fried rice with meat',0,'',142,0,0,0,0,0,0,0,0),(522,'fried rice with vegetables',0,'',142,0,0,0,0,0,0,0,0),(523,'arancini',0,'',333,0,0,0,0,0,0,0,0),(524,'papaya salad',0,'',333,0,0,0,0,0,0,0,0),(525,'fish sauce',0,'',143,0,0,0,0,0,0,0,0),(526,'apple chutney',0,'',200,0,0,0,0,0,0,0,0),(527,'cucumber raita',0,'',200,0,0,0,0,0,0,0,0),(528,'paneer tikka',0,'',333,0,0,0,0,0,0,0,0),(529,'ginseng soup',0,'',400,0,0,0,0,0,0,0,0),(530,'ginseng tea',0,'',0,0,4,0,0,0,0,0,0),(531,'gravlax',0,'',333,0,0,0,0,0,0,0,0),(532,'ceviche',0,'',333,0,0,0,0,0,0,0,0),(533,'gefilte fish',0,'',333,0,0,0,0,0,0,0,0),(534,'grilled trout',0,'',333,0,0,0,0,0,0,0,0),(535,'barley flour',0,'',0,0,0,0,0,0,0,0,0),(536,'rice flour',0,'',0,0,0,0,0,0,0,0,0),(537,'pasta',0,'',0,0,0,0,0,0,0,0,0),(538,'cake flour',0,'',0,0,0,0,0,0,0,0,0),(539,'baklava',0,'',250,0,0,0,0,0,0,0,0),(540,'angel food cake',0,'',400,0,0,0,0,0,0,0,0),(541,'carrot cake',0,'',250,0,0,0,0,0,0,0,0),(542,'rice noodles with cod',0,'',333,0,0,0,0,0,0,0,0),(543,'tagliatelle with spinach',0,'',358,0,0,0,0,0,0,0,0),(544,'kale chips',0,'',0,0,5,0,0,0,0,0,0),(545,'asparagus soup',0,'',120,0,0,0,0,0,0,0,0),(546,'banana muffins',0,'',200,0,0,0,0,0,0,0,0),(547,'cabbage rolls',0,'',400,0,0,0,0,0,0,0,0),(548,'brandade',0,'',333,0,0,0,0,0,0,0,0),(549,'pineapple cake',0,'',200,0,0,0,0,0,0,0,0),(550,'plum pudding',0,'',500,0,0,0,0,0,0,0,0),(551,'tefteli',0,'',450,0,0,0,0,0,0,0,0),(552,'fricassee',0,'',250,0,0,0,0,0,0,0,0),(553,'basbousa',0,'',333,0,0,0,0,0,0,0,0),(554,'buccellato',0,'',333,0,0,0,0,0,0,0,0),(555,'bibingka',0,'',333,0,0,0,0,0,0,0,0),(556,'sarma',0,'',333,0,0,0,0,0,0,0,0),(557,'schnitzel',0,'',333,0,0,0,0,0,0,0,0),(558,'czernina',0,'',285,0,0,0,0,0,0,0,0),(559,'bannock',0,'',250,0,0,0,0,0,0,0,0),(560,'mushroom brochettes',0,'',222,0,0,0,0,0,0,0,0),(561,'spit cake',0,'',200,0,0,0,0,0,0,0,0),(562,'satay',0,'',153,0,0,0,0,0,0,0,0),(563,'snails',0,'',0,0,0,0,0,0,0,0,0),(564,'escargot',0,'',60,0,0,0,0,0,0,0,0),(565,'crystal beads',0,'',0,0,0,0,0,0,0,0,0);
/*!40000 ALTER TABLE `rawtypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recorded_translocations`
--

DROP TABLE IF EXISTS `recorded_translocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recorded_translocations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `from_location` int(11) NOT NULL DEFAULT '0',
  `from_object` int(11) NOT NULL DEFAULT '0',
  `from_character` int(11) NOT NULL DEFAULT '0',
  `to_location` int(11) NOT NULL DEFAULT '0',
  `to_object` int(11) NOT NULL DEFAULT '0',
  `to_character` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `day` mediumint(9) NOT NULL,
  `hour` tinyint(4) NOT NULL,
  `minute` tinyint(4) NOT NULL,
  `object_type` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object_id` (`object_id`),
  KEY `position_index` (`from_location`,`from_character`,`from_object`,`to_character`,`to_location`,`to_object`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recorded_translocations`
--

LOCK TABLES `recorded_translocations` WRITE;
/*!40000 ALTER TABLE `recorded_translocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `recorded_translocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` tinyint(4) NOT NULL,
  `name` tinytext CHARACTER SET latin1,
  `mass` mediumint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES (1,'Cantr',107),(2,'Yarlitskov',129),(3,'Pok',93),(4,'Testregio',0),(7,'Shai',5),(8,'Kwor',123),(9,'Weglor',55),(10,'Schwozar',738),(11,'Ozhelo',0),(12,'Ducquasdal',181),(13,'Xortikran',506),(14,'Oorc',255),(15,'Ssonigo',325),(16,'Koranbourgh',0),(17,'Rukoppe',6406),(18,'Rynkaugh',251),(19,'Ekhorque',41);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registration_attempts`
--

DROP TABLE IF EXISTS `registration_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registration_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(60) CHARACTER SET utf8 NOT NULL,
  `lastname` varchar(60) CHARACTER SET utf8 NOT NULL,
  `errors` text CHARACTER SET utf8,
  `success` bit(1) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration_attempts`
--

LOCK TABLES `registration_attempts` WRITE;
/*!40000 ALTER TABLE `registration_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `removed_players`
--

DROP TABLE IF EXISTS `removed_players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `removed_players` (
  `firstname` tinytext CHARACTER SET utf8,
  `lastname` tinytext CHARACTER SET utf8,
  `email` tinytext CHARACTER SET utf8,
  `lastlogin` text CHARACTER SET utf8,
  `trouble` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `reason` tinytext CHARACTER SET utf8,
  `id` int(6) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `removed_players`
--

LOCK TABLES `removed_players` WRITE;
/*!40000 ALTER TABLE `removed_players` DISABLE KEYS */;
/*!40000 ALTER TABLE `removed_players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `name` varchar(60) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `title` varchar(70) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `contents` mediumtext CHARACTER SET utf8 COLLATE utf8_bin,
  `email` varchar(80) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rivers`
--

DROP TABLE IF EXISTS `rivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rivers` (
  `id` smallint(6) DEFAULT NULL,
  `points` text CHARACTER SET latin1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rivers`
--

LOCK TABLES `rivers` WRITE;
/*!40000 ALTER TABLE `rivers` DISABLE KEYS */;
/*!40000 ALTER TABLE `rivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sailing`
--

DROP TABLE IF EXISTS `sailing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sailing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vessel` mediumint(8) unsigned DEFAULT NULL,
  `x` float DEFAULT NULL,
  `y` float DEFAULT NULL,
  `speed` smallint(5) unsigned DEFAULT NULL,
  `direction` smallint(5) unsigned DEFAULT NULL,
  `dockable` text CHARACTER SET latin1,
  `maxspeed` smallint(6) DEFAULT NULL,
  `turns` smallint(6) DEFAULT NULL,
  `docking` tinyint(1) DEFAULT '0',
  `speed_percent` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `resultant_direction` smallint(5) unsigned NOT NULL,
  `docking_target` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vessel` (`vessel`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sailing`
--

LOCK TABLES `sailing` WRITE;
/*!40000 ALTER TABLE `sailing` DISABLE KEYS */;
/*!40000 ALTER TABLE `sailing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sailinglogs`
--

DROP TABLE IF EXISTS `sailinglogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sailinglogs` (
  `aid` int(9) NOT NULL AUTO_INCREMENT,
  `xfrom` float DEFAULT NULL,
  `yfrom` float DEFAULT NULL,
  `xto` float DEFAULT NULL,
  `yto` float DEFAULT NULL,
  `vessel` mediumint(8) unsigned DEFAULT NULL,
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sailinglogs`
--

LOCK TABLES `sailinglogs` WRITE;
/*!40000 ALTER TABLE `sailinglogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `sailinglogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `same_player`
--

DROP TABLE IF EXISTS `same_player`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `same_player` (
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `player1` mediumint(8) unsigned NOT NULL,
  `player2` mediumint(8) unsigned NOT NULL,
  `admin` mediumint(8) unsigned NOT NULL,
  `deleted` tinyint(4) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `same_player`
--

LOCK TABLES `same_player` WRITE;
/*!40000 ALTER TABLE `same_player` DISABLE KEYS */;
/*!40000 ALTER TABLE `same_player` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seals`
--

DROP TABLE IF EXISTS `seals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seals` (
  `note` int(8) unsigned DEFAULT NULL,
  `seal` int(8) unsigned DEFAULT NULL,
  `name` text CHARACTER SET utf8,
  `anonymous` tinyint(4) NOT NULL DEFAULT '0',
  `broken` tinyint(4) NOT NULL DEFAULT '0',
  KEY `note` (`note`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seals`
--

LOCK TABLES `seals` WRITE;
/*!40000 ALTER TABLE `seals` DISABLE KEYS */;
/*!40000 ALTER TABLE `seals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servprocrunning`
--

DROP TABLE IF EXISTS `servprocrunning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servprocrunning` (
  `procname` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT 'Running server process name.',
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time of process start.',
  PRIMARY KEY (`procname`),
  UNIQUE KEY `procname` (`procname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servprocrunning`
--

LOCK TABLES `servprocrunning` WRITE;
/*!40000 ALTER TABLE `servprocrunning` DISABLE KEYS */;
/*!40000 ALTER TABLE `servprocrunning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `player` mediumint(8) unsigned DEFAULT NULL,
  `language` tinyint(4) NOT NULL DEFAULT '1',
  `mark` tinyint(1) DEFAULT NULL,
  `passthru` text CHARACTER SET latin1,
  `info` text CHARACTER SET latin1,
  `lastpage` tinytext CHARACTER SET latin1,
  `lasttime` tinytext CHARACTER SET latin1,
  `login_ip` varchar(32) CHARACTER SET latin1 DEFAULT NULL COMMENT 'user ip when logged in',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_chars`
--

DROP TABLE IF EXISTS `settings_chars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_chars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `person` int(11) NOT NULL,
  `data` text CHARACTER SET latin1,
  PRIMARY KEY (`id`),
  KEY `person` (`person`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_chars`
--

LOCK TABLES `settings_chars` WRITE;
/*!40000 ALTER TABLE `settings_chars` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings_chars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signs`
--

DROP TABLE IF EXISTS `signs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signs` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `location` mediumint(8) unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `signorder` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `project` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signs`
--

LOCK TABLES `signs` WRITE;
/*!40000 ALTER TABLE `signs` DISABLE KEYS */;
/*!40000 ALTER TABLE `signs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spawninglocations`
--

DROP TABLE IF EXISTS `spawninglocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spawninglocations` (
  `id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `language` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spawninglocations`
--

LOCK TABLES `spawninglocations` WRITE;
/*!40000 ALTER TABLE `spawninglocations` DISABLE KEYS */;
INSERT INTO `spawninglocations` VALUES (636,1),(636,2),(636,3),(636,4),(636,5),(636,6),(636,7),(636,8),(636,9),(636,10),(636,11),(636,12),(636,13),(636,14),(636,15),(636,16),(636,17),(636,18),(636,19);
/*!40000 ALTER TABLE `spawninglocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state_types`
--

DROP TABLE IF EXISTS `state_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state_types` (
  `id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `visible_self` tinyint(3) unsigned DEFAULT '0',
  `visible_other` tinyint(3) unsigned DEFAULT '0',
  `rand_minimum` smallint(5) unsigned DEFAULT '0',
  `rand_maximum` smallint(5) unsigned DEFAULT '10000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state_types`
--

LOCK TABLES `state_types` WRITE;
/*!40000 ALTER TABLE `state_types` DISABLE KEYS */;
INSERT INTO `state_types` VALUES (1,'Farming',0,0,1000,10000),(2,'Fighting',0,0,1000,10000),(3,'Building',0,0,1000,10000),(4,'Manufacturing',0,0,1000,10000),(5,'Resistance',0,0,3000,10000),(6,'Hunger',0,0,0,0),(7,'Thirst',0,0,0,0),(8,'Walking',0,0,5000,10000),(9,'Hunting',0,0,1000,10000),(10,'Tiredness',0,0,0,0),(11,'Foresting',0,0,1000,10000),(12,'Drunkenness',0,0,0,0),(13,'Physical strength',0,0,1000,10000),(14,'Health',0,0,10000,10000),(15,'Hygiene',0,0,0,0),(16,'Mining',0,0,1000,10000),(17,'Fishing',0,0,1000,10000),(18,'Digging',0,0,1000,10000),(19,'Collecting',0,0,1000,10000),(20,'Drilling',0,0,1000,10000),(21,'Gardening',0,0,1000,10000),(22,'Burying',0,0,1000,10000),(23,'Refining',0,0,1000,10000),(24,'Smelting',0,0,1000,10000),(25,'Cooking',0,0,1000,10000),(26,'Tailoring',0,0,1000,10000),(27,'Manufacturing machines',0,0,1000,10000),(28,'Manufacturing weapons',0,0,1000,10000),(29,'Manufacturing vehicles',0,0,1000,10000),(30,'Shipbuilding',0,0,1000,10000),(31,'Manufacturing tools',0,0,1000,10000),(32,'Carpenting',0,0,1000,10000),(33,'Animal husbandry',0,0,1000,10000);
/*!40000 ALTER TABLE `state_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `states` (
  `person` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL DEFAULT '0',
  `value` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`person`,`type`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `states`
--

LOCK TABLES `states` WRITE;
/*!40000 ALTER TABLE `states` DISABLE KEYS */;
/*!40000 ALTER TABLE `states` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `turn` smallint(5) unsigned DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `code` smallint(5) unsigned DEFAULT NULL,
  `statistic` int(10) unsigned DEFAULT NULL,
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statistics`
--

LOCK TABLES `statistics` WRITE;
/*!40000 ALTER TABLE `statistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `statistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stomach`
--

DROP TABLE IF EXISTS `stomach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stomach` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `person` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `food` smallint(5) unsigned NOT NULL DEFAULT '0',
  `weight` bigint(5) unsigned NOT NULL DEFAULT '0',
  `eaten_date` mediumint(9) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stomach`
--

LOCK TABLES `stomach` WRITE;
/*!40000 ALTER TABLE `stomach` DISABLE KEYS */;
/*!40000 ALTER TABLE `stomach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_answers`
--

DROP TABLE IF EXISTS `survey_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_answers` (
  `a_id` int(11) NOT NULL AUTO_INCREMENT,
  `of_question` int(11) NOT NULL,
  `a_type` int(11) NOT NULL,
  PRIMARY KEY (`a_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_answers`
--

LOCK TABLES `survey_answers` WRITE;
/*!40000 ALTER TABLE `survey_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_player_answers`
--

DROP TABLE IF EXISTS `survey_player_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_player_answers` (
  `pa_id` int(11) NOT NULL AUTO_INCREMENT,
  `of_ps` int(11) NOT NULL,
  `of_question` int(11) NOT NULL,
  `answer_option` int(11) NOT NULL,
  `answer_text` text CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`pa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_player_answers`
--

LOCK TABLES `survey_player_answers` WRITE;
/*!40000 ALTER TABLE `survey_player_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_player_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_player_surveys`
--

DROP TABLE IF EXISTS `survey_player_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_player_surveys` (
  `ps_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'its own id',
  `player_id` int(11) DEFAULT NULL COMMENT 'player id',
  `s_id` int(11) NOT NULL COMMENT 'id of connected survey',
  `date` datetime NOT NULL,
  `s_lang` int(11) NOT NULL,
  `submitted` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ps_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_player_surveys`
--

LOCK TABLES `survey_player_surveys` WRITE;
/*!40000 ALTER TABLE `survey_player_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_player_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_questions`
--

DROP TABLE IF EXISTS `survey_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_questions` (
  `q_id` int(11) NOT NULL AUTO_INCREMENT,
  `of_survey` int(11) NOT NULL,
  PRIMARY KEY (`q_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_questions`
--

LOCK TABLES `survey_questions` WRITE;
/*!40000 ALTER TABLE `survey_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_respondents`
--

DROP TABLE IF EXISTS `survey_respondents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_respondents` (
  `survey_id` int(11) DEFAULT NULL,
  `player_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Surveys filled in by a player';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_respondents`
--

LOCK TABLES `survey_respondents` WRITE;
/*!40000 ALTER TABLE `survey_respondents` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_respondents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `surveys` (
  `s_id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `s_language` int(11) NOT NULL COMMENT '0 = all languages',
  `player_ids` text CHARACTER SET utf8 COLLATE utf8_bin,
  PRIMARY KEY (`s_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temp_tab`
--

DROP TABLE IF EXISTS `temp_tab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_tab` (
  `name` varchar(50) CHARACTER SET latin1 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temp_tab`
--

LOCK TABLES `temp_tab` WRITE;
/*!40000 ALTER TABLE `temp_tab` DISABLE KEYS */;
/*!40000 ALTER TABLE `temp_tab` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `texts`
--

DROP TABLE IF EXISTS `texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `texts` (
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `language` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `content` text CHARACTER SET utf8,
  `grammar` text CHARACTER SET latin1,
  `translator` varchar(80) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `updated` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`name`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `texts`
--

LOCK TABLES `texts` WRITE;
/*!40000 ALTER TABLE `texts` DISABLE KEYS */;
/*!40000 ALTER TABLE `texts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timing`
--

DROP TABLE IF EXISTS `timing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timing` (
  `pagetype` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `thecount` mediumint(5) DEFAULT NULL,
  `sqlcount` int(7) unsigned DEFAULT NULL,
  `totaltime` float DEFAULT NULL,
  `sqltime` float DEFAULT NULL,
  `player` mediumint(6) unsigned DEFAULT NULL,
  `sqltagcount` int(7) unsigned DEFAULT NULL,
  `template` tinyint(1) unsigned DEFAULT '0',
  `day` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timing`
--

LOCK TABLES `timing` WRITE;
/*!40000 ALTER TABLE `timing` DISABLE KEYS */;
/*!40000 ALTER TABLE `timing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tlstatistics`
--

DROP TABLE IF EXISTS `tlstatistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tlstatistics` (
  `turn` smallint(3) unsigned NOT NULL DEFAULT '0',
  `player` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `timeleft` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`turn`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tlstatistics`
--

LOCK TABLES `tlstatistics` WRITE;
/*!40000 ALTER TABLE `tlstatistics` DISABLE KEYS */;
/*!40000 ALTER TABLE `tlstatistics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `track_referrals`
--

DROP TABLE IF EXISTS `track_referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `track_referrals` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(16) CHARACTER SET latin1 DEFAULT NULL,
  `reference` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `suspect_signup` tinyint(3) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `track_referrals`
--

LOCK TABLES `track_referrals` WRITE;
/*!40000 ALTER TABLE `track_referrals` DISABLE KEYS */;
/*!40000 ALTER TABLE `track_referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `travelhistory`
--

DROP TABLE IF EXISTS `travelhistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `travelhistory` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `person` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `location` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `arrival` tinyint(1) NOT NULL DEFAULT '0',
  `day` smallint(4) unsigned NOT NULL DEFAULT '0',
  `hour` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `vehicle` mediumint(8) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `person` (`person`,`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travelhistory`
--

LOCK TABLES `travelhistory` WRITE;
/*!40000 ALTER TABLE `travelhistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `travelhistory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `travels`
--

DROP TABLE IF EXISTS `travels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `travels` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locfrom` mediumint(5) unsigned DEFAULT NULL,
  `locdest` mediumint(5) unsigned DEFAULT NULL,
  `travleft` smallint(5) unsigned DEFAULT NULL,
  `travneeded` smallint(5) unsigned DEFAULT NULL,
  `person` mediumint(8) unsigned DEFAULT NULL,
  `connection` smallint(5) unsigned DEFAULT NULL,
  `type` smallint(5) unsigned DEFAULT '0',
  `speed_percent` tinyint(3) unsigned NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`),
  UNIQUE KEY `person` (`person`,`type`),
  KEY `connection` (`connection`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travels`
--

LOCK TABLES `travels` WRITE;
/*!40000 ALTER TABLE `travels` DISABLE KEYS */;
/*!40000 ALTER TABLE `travels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `troubleplayers`
--

DROP TABLE IF EXISTS `troubleplayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `troubleplayers` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `ids` varchar(80) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `names` text CHARACTER SET latin1 NOT NULL,
  `owner` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ids` (`ids`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `troubleplayers`
--

LOCK TABLES `troubleplayers` WRITE;
/*!40000 ALTER TABLE `troubleplayers` DISABLE KEYS */;
/*!40000 ALTER TABLE `troubleplayers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turn`
--

DROP TABLE IF EXISTS `turn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `turn` (
  `number` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'using this value is DEPRECATED. Use "day" instead',
  `part` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'using this value is DEPRECATED. Use "hour" instead',
  `day` smallint(5) unsigned DEFAULT NULL,
  `hour` tinyint(3) unsigned DEFAULT NULL,
  `minute` tinyint(3) unsigned DEFAULT NULL,
  `second` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`number`,`part`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turn`
--

LOCK TABLES `turn` WRITE;
/*!40000 ALTER TABLE `turn` DISABLE KEYS */;
INSERT INTO `turn` VALUES (6011,4,6011,4,16,48);
/*!40000 ALTER TABLE `turn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_daystats`
--

DROP TABLE IF EXISTS `uls_daystats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_daystats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL DEFAULT '0000-00-00',
  `bytes` int(11) NOT NULL DEFAULT '0',
  `filecount` int(11) NOT NULL DEFAULT '0',
  `uploads` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_daystats`
--

LOCK TABLES `uls_daystats` WRITE;
/*!40000 ALTER TABLE `uls_daystats` DISABLE KEYS */;
/*!40000 ALTER TABLE `uls_daystats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_ip`
--

DROP TABLE IF EXISTS `uls_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_ip` (
  `ip` char(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `bytes` bigint(20) NOT NULL DEFAULT '0',
  `filecount` int(4) NOT NULL DEFAULT '0',
  `date2` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_ip`
--

LOCK TABLES `uls_ip` WRITE;
/*!40000 ALTER TABLE `uls_ip` DISABLE KEYS */;
/*!40000 ALTER TABLE `uls_ip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_ipbans`
--

DROP TABLE IF EXISTS `uls_ipbans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_ipbans` (
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ip`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_ipbans`
--

LOCK TABLES `uls_ipbans` WRITE;
/*!40000 ALTER TABLE `uls_ipbans` DISABLE KEYS */;
/*!40000 ALTER TABLE `uls_ipbans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_lastreq`
--

DROP TABLE IF EXISTS `uls_lastreq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_lastreq` (
  `file` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `count` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `size` int(8) NOT NULL DEFAULT '0',
  `reset` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `thmb` tinyint(1) NOT NULL DEFAULT '0',
  `player` mediumint(9) NOT NULL DEFAULT '0',
  KEY `file` (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_lastreq`
--

LOCK TABLES `uls_lastreq` WRITE;
/*!40000 ALTER TABLE `uls_lastreq` DISABLE KEYS */;
/*!40000 ALTER TABLE `uls_lastreq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_referers`
--

DROP TABLE IF EXISTS `uls_referers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_referers` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_referers`
--

LOCK TABLES `uls_referers` WRITE;
/*!40000 ALTER TABLE `uls_referers` DISABLE KEYS */;
INSERT INTO `uls_referers` VALUES (1,'other',1),(2,'tweakers.net',1),(3,'fantasy-realm.nl',1),(4,'cantr.eu',1);
/*!40000 ALTER TABLE `uls_referers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uls_settings`
--

DROP TABLE IF EXISTS `uls_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uls_settings` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `value` char(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uls_settings`
--

LOCK TABLES `uls_settings` WRITE;
/*!40000 ALTER TABLE `uls_settings` DISABLE KEYS */;
INSERT INTO `uls_settings` VALUES (1,'200'),(2,'10737418240'),(3,'21474836480'),(4,'10737418240'),(5,'1'),(6,'1'),(7,'18400000000'),(8,'1048576'),(9,'on'),(10,''),(11,'0'),(12,'0'),(13,'650'),(14,'1000000'),(15,'english'),(16,'0');
/*!40000 ALTER TABLE `uls_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `undelivered_emails`
--

DROP TABLE IF EXISTS `undelivered_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `undelivered_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text CHARACTER SET latin1 NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`(256))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `undelivered_emails`
--

LOCK TABLES `undelivered_emails` WRITE;
/*!40000 ALTER TABLE `undelivered_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `undelivered_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unreported_turns`
--

DROP TABLE IF EXISTS `unreported_turns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unreported_turns` (
  `player` mediumint(8) unsigned DEFAULT NULL,
  `turnnumber` smallint(5) unsigned DEFAULT NULL,
  KEY `player` (`player`),
  KEY `turnnumber` (`turnnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unreported_turns`
--

LOCK TABLES `unreported_turns` WRITE;
/*!40000 ALTER TABLE `unreported_turns` DISABLE KEYS */;
/*!40000 ALTER TABLE `unreported_turns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unvalidated_email`
--

DROP TABLE IF EXISTS `unvalidated_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unvalidated_email` (
  `email` text CHARACTER SET latin1,
  `code` text CHARACTER SET latin1,
  `player` mediumint(9) DEFAULT NULL,
  `expires` date DEFAULT NULL,
  KEY `player` (`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unvalidated_email`
--

LOCK TABLES `unvalidated_email` WRITE;
/*!40000 ALTER TABLE `unvalidated_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `unvalidated_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vegetation_groups`
--

DROP TABLE IF EXISTS `vegetation_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vegetation_groups` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(15) CHARACTER SET latin1 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vegetation_groups`
--

LOCK TABLES `vegetation_groups` WRITE;
/*!40000 ALTER TABLE `vegetation_groups` DISABLE KEYS */;
INSERT INTO `vegetation_groups` VALUES (0,'none');
/*!40000 ALTER TABLE `vegetation_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `violence`
--

DROP TABLE IF EXISTS `violence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `violence` (
  `turn` smallint(5) unsigned DEFAULT NULL,
  `turnpart` tinyint(3) unsigned DEFAULT NULL,
  `minute` tinyint(3) NOT NULL DEFAULT '0',
  `second` tinyint(3) NOT NULL DEFAULT '0',
  `perpetrator` mediumint(8) unsigned DEFAULT NULL,
  `victim` mediumint(8) unsigned DEFAULT NULL,
  `type` int(4) unsigned DEFAULT NULL,
  `info` text CHARACTER SET latin1,
  KEY `perpetrator` (`perpetrator`,`victim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `violence`
--

LOCK TABLES `violence` WRITE;
/*!40000 ALTER TABLE `violence` DISABLE KEYS */;
/*!40000 ALTER TABLE `violence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `votinglinks`
--

DROP TABLE IF EXISTS `votinglinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votinglinks` (
  `uid` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(2047) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `language` int(2) unsigned NOT NULL DEFAULT '0',
  `enabled` int(2) unsigned DEFAULT '0',
  `order` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `votinglinks`
--

LOCK TABLES `votinglinks` WRITE;
/*!40000 ALTER TABLE `votinglinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `votinglinks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `watches`
--

DROP TABLE IF EXISTS `watches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `watches` (
  `player` mediumint(9) NOT NULL DEFAULT '0',
  `email` varchar(30) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`player`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `watches`
--

LOCK TABLES `watches` WRITE;
/*!40000 ALTER TABLE `watches` DISABLE KEYS */;
/*!40000 ALTER TABLE `watches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather_cells`
--

DROP TABLE IF EXISTS `weather_cells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weather_cells` (
  `row` int(11) NOT NULL,
  `col` int(11) NOT NULL,
  `coefficient` tinyint(4) NOT NULL,
  `season` tinyint(4) NOT NULL,
  `climate` tinyint(4) NOT NULL,
  `relative_wind_x` double NOT NULL DEFAULT '0',
  `relative_wind_y` double NOT NULL DEFAULT '0',
  `humidity` int(11) NOT NULL DEFAULT '0',
  `insolation` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`row`,`col`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather_cells`
--

LOCK TABLES `weather_cells` WRITE;
/*!40000 ALTER TABLE `weather_cells` DISABLE KEYS */;
/*!40000 ALTER TABLE `weather_cells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather_pressure_areas`
--

DROP TABLE IF EXISTS `weather_pressure_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weather_pressure_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `x` smallint(6) unsigned NOT NULL,
  `y` smallint(6) unsigned NOT NULL,
  `v_x` smallint(6) NOT NULL,
  `v_y` smallint(6) NOT NULL,
  `influence` tinyint(4) NOT NULL,
  `mobile` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`),
  KEY `x` (`x`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather_pressure_areas`
--

LOCK TABLES `weather_pressure_areas` WRITE;
/*!40000 ALTER TABLE `weather_pressure_areas` DISABLE KEYS */;
/*!40000 ALTER TABLE `weather_pressure_areas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `weather_seasons`
--

DROP TABLE IF EXISTS `weather_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `weather_seasons` (
  `id` int(11) NOT NULL,
  `rightmost_column` int(11) NOT NULL,
  `deviation` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `weather_seasons`
--

LOCK TABLES `weather_seasons` WRITE;
/*!40000 ALTER TABLE `weather_seasons` DISABLE KEYS */;
INSERT INTO `weather_seasons` VALUES (1,69,-0.5),(2,39,-0.5),(3,9,-0.5),(4,99,-0.5);
/*!40000 ALTER TABLE `weather_seasons` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-15  0:02:32