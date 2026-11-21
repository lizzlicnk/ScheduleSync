-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: schedulesync
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asignatura`
--

DROP TABLE IF EXISTS `asignatura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignatura` (
  `id_asignatura` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombreAsignatura` varchar(100) NOT NULL,
  `clave` varchar(20) NOT NULL,
  `cuatrimestre` tinyint(3) unsigned NOT NULL,
  `cantidadHoras` smallint(5) unsigned NOT NULL,
  `id_carrera` bigint(20) unsigned NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_asignatura`),
  UNIQUE KEY `asignatura_clave_unique` (`clave`),
  KEY `asignatura_id_carrera_foreign` (`id_carrera`),
  CONSTRAINT `asignatura_id_carrera_foreign` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignatura`
--

LOCK TABLES `asignatura` WRITE;
/*!40000 ALTER TABLE `asignatura` DISABLE KEYS */;
INSERT INTO `asignatura` VALUES (1,'3','342',6,7,1,0),(2,'33','332',6,3,5,0),(3,'hola cambio PA','12324',6,3,15,0),(4,'nombreAsig CAMBIO NUEVO','clave as',1,15,2,0),(5,'PRUEBA cambio 2','1',4,8,9,0),(6,'PRUEBA','2',8,5,11,0),(7,'prueba','3',5,54,6,0),(8,'pruba modal clave repetida','POO',1,1,9,0),(9,'hola','hola',7,5,6,0),(10,'prueba','p1',1,10,8,0),(11,'prueba 2','p2',2,9,8,0),(12,'prueba 3','p3',1,12,6,0),(13,'prueba 4','p4',1,12,10,0),(16,'prueba 5','p5',1,1,14,0),(17,'prueba 6','p6',1,10,14,0),(18,'a','a',1,1,3,0),(19,'1','12',1,1,3,0),(20,'aaaaa','aaaaaaaa',1,12,3,0),(21,'22','223',1,2,3,0),(22,'PROGRAMACION ITI','PRO3RO',3,10,16,1),(23,'Desarrollo Humano y Valores','DHV2DO',2,14,16,1),(24,'Química Básica','QUB1RO',1,2,18,1),(25,'Inglés IV','ING4TO',4,6,16,1),(26,'Matemáticas para Ingeniería I','MPI5TO',5,6,16,1),(27,'Sistemas Operativos','SIO6TO',6,7,16,1),(28,'Ingeniería de Requisitos','ING7TO',7,1,16,1),(29,'Administración de Proyectos de Tecnologías de la Información','APT8VO',8,4,16,1),(30,'Diseño de Interfaces','DSI8VO',8,3,16,1),(31,'Sistemas embébidos','SIE9NO',9,5,16,1),(32,'Estadía','Est10MO',10,15,16,1),(33,'Valores del Ser','VDS1RO',1,3,18,1),(34,'Inteligencia Emocional','INE2do',2,6,18,1),(35,'Mantenimiento Electrónico','MAE2Do',2,3,18,1),(36,'Circuitos en Corriente Directa','CCD3ro',3,3,18,1),(37,'Álgebra Lineal','ALL2DO',2,10,18,1),(38,'Cálculo vectorial','CAV2do',2,4,18,1),(39,'Tópicos de Ingeniería en Electrónica y Telecomunicaciones','TET1RRO',1,10,18,1),(40,'Fundamentos de Química','FUQ1RO',1,10,18,1);
/*!40000 ALTER TABLE `asignatura` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignatura_preferida`
--

DROP TABLE IF EXISTS `asignatura_preferida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asignatura_preferida` (
  `id_asignPreferida` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_periodo` bigint(20) unsigned NOT NULL,
  `id_docente` bigint(20) unsigned NOT NULL,
  `id_asignatura` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_asignPreferida`),
  UNIQUE KEY `uq_asignatura_preferida` (`id_periodo`,`id_docente`,`id_asignatura`),
  KEY `asignatura_preferida_id_docente_foreign` (`id_docente`),
  KEY `asignatura_preferida_id_asignatura_foreign` (`id_asignatura`),
  CONSTRAINT `asignatura_preferida_id_asignatura_foreign` FOREIGN KEY (`id_asignatura`) REFERENCES `asignatura` (`id_asignatura`),
  CONSTRAINT `asignatura_preferida_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_docente`),
  CONSTRAINT `asignatura_preferida_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignatura_preferida`
--

LOCK TABLES `asignatura_preferida` WRITE;
/*!40000 ALTER TABLE `asignatura_preferida` DISABLE KEYS */;
INSERT INTO `asignatura_preferida` VALUES (95,1,2,24),(98,1,2,25),(99,1,2,28),(100,1,2,32),(97,1,2,33),(96,1,2,39),(94,1,2,40),(92,1,3,24),(93,1,3,33),(118,1,4,24),(120,1,4,33),(119,1,4,39),(117,1,4,40),(110,1,6,24),(112,1,6,33),(111,1,6,39),(109,1,6,40),(106,1,7,24),(108,1,7,33),(107,1,7,39),(105,1,7,40),(102,1,8,24),(104,1,8,33),(103,1,8,39),(101,1,8,40),(114,1,9,24),(116,1,9,33),(115,1,9,39),(113,1,9,40),(121,5,2,23),(122,5,2,26),(123,5,2,29),(124,5,2,30),(76,222,2,36),(74,223,3,24),(75,223,3,25),(128,224,2,37),(73,224,3,23),(78,224,4,23),(77,224,4,37),(125,224,8,37),(127,224,9,34),(126,224,9,37),(13,225,2,27),(14,225,2,31),(12,225,2,36);
/*!40000 ALTER TABLE `asignatura_preferida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-0716d9708d321ffb6a00818614779e779925365c','i:1;',1795159471),('laravel-cache-0716d9708d321ffb6a00818614779e779925365c:timer','i:1795159471;',1795159471),('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba','i:3;',1795149281),('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1795149281;',1795149281),('laravel-cache-91032ad7bbcb6cf72875e8e8207dcfba80173f7c','i:1;',1795148157),('laravel-cache-91032ad7bbcb6cf72875e8e8207dcfba80173f7c:timer','i:1795148157;',1795148157),('laravel-cache-9e6a55b6b4563e652a23be9d623ca5055c356940','i:1;',1795158034),('laravel-cache-9e6a55b6b4563e652a23be9d623ca5055c356940:timer','i:1795158034;',1795158034),('laravel-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5','i:1;',1792380860),('laravel-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5:timer','i:1792380860;',1792380860),('laravel-cache-recuperacion_pin:735b82886e7745ae875ee128d2780ebc84b474deee8b30535de79587ba3d9fad','a:3:{s:11:\"codigo_hash\";s:60:\"$2y$12$8WMD93KkEYiETKhbPPBrOOzIsbIBx6Ql85i8eK2jv9PCQBtaXdFkC\";s:9:\"expira_en\";i:1795149826;s:8:\"intentos\";i:1;}',1795149826);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrera`
--

DROP TABLE IF EXISTS `carrera`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carrera` (
  `id_carrera` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombreCarrera` varchar(100) NOT NULL,
  `claveCarrera` varchar(45) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_carrera`),
  UNIQUE KEY `carrera_clavecarrera_unique` (`claveCarrera`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrera`
--

LOCK TABLES `carrera` WRITE;
/*!40000 ALTER TABLE `carrera` DISABLE KEYS */;
INSERT INTO `carrera` VALUES (1,'dsbvysdfgihdsf','ITI',0),(2,'FDDFSDFS','FDSDSF',0),(3,'fdsrdgfdfgfdgfgd','333',0),(4,'43324342324','4345',0),(5,'34453563','342543',0),(6,'324324453453','4565y546453w',0),(7,'342345','34435435',0),(8,'3243','23',0),(9,'2332','32',0),(10,'3342','343',0),(11,'44','43',0),(12,'a','322',0),(13,'3432','332',0),(14,'prueba 1','1',0),(15,'767','77',0),(16,'INGENERIA EN TECNOLOGIAS DE LA INFORMACION','ITI-2',1),(17,'INGENERIA EN SISTEMAS ELECTRONICOS','ISE',1),(18,'INGENERIA EN ELETRONICA Y TELECOMUNCIACIONES','IET',1),(19,'INGENERIA EN TECNOLOGIAS DE LA INFORMACION E INOVACION DIGITAL','IID',1);
/*!40000 ALTER TABLE `carrera` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_horario`
--

DROP TABLE IF EXISTS `detalle_horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_horario` (
  `id_detalle` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dia` varchar(15) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `aula` varchar(20) DEFAULT NULL,
  `id_horario` bigint(20) unsigned NOT NULL,
  `id_docente` bigint(20) unsigned NOT NULL,
  `id_asignatura` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `detalle_horario_id_horario_foreign` (`id_horario`),
  KEY `detalle_horario_id_docente_foreign` (`id_docente`),
  KEY `detalle_horario_id_asignatura_foreign` (`id_asignatura`),
  CONSTRAINT `detalle_horario_id_asignatura_foreign` FOREIGN KEY (`id_asignatura`) REFERENCES `asignatura` (`id_asignatura`),
  CONSTRAINT `detalle_horario_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_docente`),
  CONSTRAINT `detalle_horario_id_horario_foreign` FOREIGN KEY (`id_horario`) REFERENCES `horario` (`id_horario`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_horario`
--

LOCK TABLES `detalle_horario` WRITE;
/*!40000 ALTER TABLE `detalle_horario` DISABLE KEYS */;
INSERT INTO `detalle_horario` VALUES (41,'Lunes','08:00:00','12:00:00','Aula',9,2,29),(42,'Martes','09:00:00','12:00:00','Aula',9,2,30),(57,'Martes','10:00:00','16:00:00','Aula',2,2,25),(62,'Martes','08:00:00','10:00:00','Aula',5,6,24),(63,'Jueves','08:00:00','18:00:00','Aula',5,9,40),(64,'Miércoles','08:00:00','18:00:00','Aula',5,7,39),(65,'Lunes','10:00:00','13:00:00','Laboratorio',5,8,33),(67,'Sábado','07:00:00','09:00:00','Laboratorio',7,3,24),(68,'Jueves','09:00:00','19:00:00','Laboratorio',6,6,40),(69,'Lunes','09:00:00','11:00:00','Laboratorio',6,9,24),(70,'Martes','09:00:00','19:00:00','Aula',6,8,39),(71,'Miércoles','09:00:00','12:00:00','Aula',6,8,33);
/*!40000 ALTER TABLE `detalle_horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disponibilidadprofesor`
--

DROP TABLE IF EXISTS `disponibilidadprofesor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disponibilidadprofesor` (
  `id_disponibilidad` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dia` int(11) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `estadoConfirmacion` int(11) NOT NULL DEFAULT 0,
  `fechaConfirmacion` datetime DEFAULT NULL,
  `id_docente` bigint(20) unsigned NOT NULL,
  `id_periodo` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_disponibilidad`),
  KEY `disponibilidadprofesor_id_docente_foreign` (`id_docente`),
  KEY `disponibilidadprofesor_id_periodo_foreign` (`id_periodo`),
  CONSTRAINT `disponibilidadprofesor_id_docente_foreign` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id_docente`),
  CONSTRAINT `disponibilidadprofesor_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disponibilidadprofesor`
--

LOCK TABLES `disponibilidadprofesor` WRITE;
/*!40000 ALTER TABLE `disponibilidadprofesor` DISABLE KEYS */;
INSERT INTO `disponibilidadprofesor` VALUES (103,1,'07:00:00','09:00:00',1,'2026-11-09 18:12:02',2,225),(104,1,'15:00:00','21:00:00',1,'2026-11-09 18:12:02',2,225),(105,2,'07:00:00','09:00:00',1,'2026-11-09 18:12:02',2,225),(106,2,'15:00:00','21:00:00',1,'2026-11-09 18:12:02',2,225),(107,3,'11:00:00','13:00:00',1,'2026-11-09 18:12:02',2,225),(108,3,'15:00:00','21:00:00',1,'2026-11-09 18:12:02',2,225),(109,4,'11:00:00','13:00:00',1,'2026-11-09 18:12:02',2,225),(110,4,'15:00:00','21:00:00',1,'2026-11-09 18:12:02',2,225),(111,5,'07:00:00','10:00:00',1,'2026-11-09 18:12:02',2,225),(112,5,'15:00:00','21:00:00',1,'2026-11-09 18:12:02',2,225),(113,6,'07:00:00','10:00:00',1,'2026-11-09 18:12:02',2,225),(272,1,'07:00:00','09:00:00',1,'2026-11-10 00:24:22',3,224),(273,2,'08:00:00','09:00:00',1,'2026-11-10 00:24:22',3,224),(274,2,'10:00:00','11:00:00',1,'2026-11-10 00:24:22',3,224),(275,3,'08:00:00','12:00:00',1,'2026-11-10 00:24:22',3,224),(276,3,'17:00:00','19:00:00',1,'2026-11-10 00:24:22',3,224),(277,4,'10:00:00','11:00:00',1,'2026-11-10 00:24:22',3,224),(278,4,'17:00:00','19:00:00',1,'2026-11-10 00:24:22',3,224),(279,5,'07:00:00','11:00:00',1,'2026-11-10 00:24:22',3,224),(280,6,'09:00:00','11:00:00',1,'2026-11-10 00:24:22',3,224),(281,1,'07:00:00','09:00:00',1,'2026-11-10 00:25:58',3,223),(282,2,'07:00:00','09:00:00',1,'2026-11-10 00:25:58',3,223),(283,2,'17:00:00','20:00:00',1,'2026-11-10 00:25:58',3,223),(284,3,'13:00:00','16:00:00',1,'2026-11-10 00:25:58',3,223),(285,3,'17:00:00','18:00:00',1,'2026-11-10 00:25:58',3,223),(286,3,'19:00:00','20:00:00',1,'2026-11-10 00:25:58',3,223),(287,1,'07:00:00','09:00:00',1,'2026-11-10 00:41:04',2,222),(288,1,'19:00:00','21:00:00',1,'2026-11-10 00:41:04',2,222),(289,2,'07:00:00','09:00:00',1,'2026-11-10 00:41:04',2,222),(290,2,'19:00:00','21:00:00',1,'2026-11-10 00:41:04',2,222),(291,3,'11:00:00','13:00:00',1,'2026-11-10 00:41:04',2,222),(292,1,'07:00:00','09:00:00',1,'2026-11-10 01:04:13',4,224),(293,2,'07:00:00','09:00:00',1,'2026-11-10 01:04:13',4,224),(294,2,'18:00:00','21:00:00',1,'2026-11-10 01:04:13',4,224),(295,3,'18:00:00','21:00:00',1,'2026-11-10 01:04:13',4,224),(320,1,'11:00:00','21:00:00',1,'2026-11-18 16:41:40',3,1),(321,2,'11:00:00','21:00:00',1,'2026-11-18 16:41:40',3,1),(322,3,'11:00:00','21:00:00',1,'2026-11-18 16:41:40',3,1),(323,4,'11:00:00','21:00:00',1,'2026-11-18 16:41:40',3,1),(324,5,'11:00:00','21:00:00',1,'2026-11-18 16:41:40',3,1),(325,6,'11:00:00','15:00:00',1,'2026-11-18 16:41:40',3,1),(326,1,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(327,2,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(328,3,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(329,4,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(330,5,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(331,6,'07:00:00','10:00:00',1,'2026-11-19 05:16:16',2,1),(332,1,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(333,2,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(334,3,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(335,4,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(336,5,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(337,6,'07:00:00','08:00:00',1,'2026-11-19 06:28:20',8,1),(338,1,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(339,2,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(340,3,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(341,4,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(342,5,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(343,6,'07:00:00','08:00:00',1,'2026-11-19 08:11:03',7,1),(344,1,'07:00:00','08:00:00',1,'2026-11-19 08:11:16',6,1),(345,2,'07:00:00','08:00:00',1,'2026-11-19 08:11:16',6,1),(346,3,'07:00:00','08:00:00',1,'2026-11-19 08:11:16',6,1),(347,1,'07:00:00','08:00:00',1,'2026-11-19 08:11:29',9,1),(348,6,'14:00:00','15:00:00',1,'2026-11-19 08:11:42',4,1),(349,1,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(350,2,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(351,3,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(352,4,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(353,4,'19:00:00','21:00:00',1,'2026-11-20 00:33:56',2,5),(354,5,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(355,5,'19:00:00','21:00:00',1,'2026-11-20 00:33:56',2,5),(356,6,'07:00:00','08:00:00',1,'2026-11-20 00:33:56',2,5),(357,1,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(358,1,'20:00:00','21:00:00',1,'2026-11-20 07:00:41',8,224),(359,2,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(360,2,'20:00:00','21:00:00',1,'2026-11-20 07:00:41',8,224),(361,3,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(362,3,'20:00:00','21:00:00',1,'2026-11-20 07:00:41',8,224),(363,4,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(364,4,'20:00:00','21:00:00',1,'2026-11-20 07:00:41',8,224),(365,5,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(366,5,'20:00:00','21:00:00',1,'2026-11-20 07:00:41',8,224),(367,6,'07:00:00','08:00:00',1,'2026-11-20 07:00:41',8,224),(368,1,'07:00:00','09:00:00',1,'2026-11-20 07:23:48',9,224),(369,2,'07:00:00','09:00:00',1,'2026-11-20 07:23:48',9,224),(370,5,'19:00:00','21:00:00',1,'2026-11-20 07:23:48',9,224),(371,4,'20:00:00','21:00:00',1,'2026-11-20 07:23:48',9,224),(372,1,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224),(373,1,'19:00:00','21:00:00',1,'2026-11-21 19:13:18',2,224),(374,2,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224),(375,2,'19:00:00','21:00:00',1,'2026-11-21 19:13:18',2,224),(376,3,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224),(377,3,'19:00:00','21:00:00',1,'2026-11-21 19:13:18',2,224),(378,4,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224),(379,4,'19:00:00','21:00:00',1,'2026-11-21 19:13:18',2,224),(380,5,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224),(381,5,'19:00:00','21:00:00',1,'2026-11-21 19:13:18',2,224),(382,6,'07:00:00','09:00:00',1,'2026-11-21 19:13:18',2,224);
/*!40000 ALTER TABLE `disponibilidadprofesor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente`
--

DROP TABLE IF EXISTS `docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente` (
  `id_docente` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `matricula` varchar(20) NOT NULL,
  `especialidad` varchar(100) NOT NULL,
  `tipoDocente` int(11) NOT NULL,
  `cargaAcademica` int(11) NOT NULL DEFAULT 20,
  `id_usuario` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_docente`),
  UNIQUE KEY `docente_matricula_unique` (`matricula`),
  UNIQUE KEY `docente_id_usuario_unique` (`id_usuario`),
  CONSTRAINT `docente_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente`
--

LOCK TABLES `docente` WRITE;
/*!40000 ALTER TABLE `docente` DISABLE KEYS */;
INSERT INTO `docente` VALUES (1,'aaaaaa','base de datos',1,20,8),(2,'dsfdgeq','fdsdfsfd',2,20,7),(3,'325343654','34323454',2,20,3),(4,'dsfdgdfg','dfsfgddfg',1,0,4),(5,'HOLAA','Matematico',1,20,10),(6,'PRO2','ahdshds',1,20,16),(7,'PRO1','FDSDSF',2,20,15),(8,'PROF4','dsadgfdf',1,20,18),(9,'PROFESOR3','Programador',2,20,17);
/*!40000 ALTER TABLE `docente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo`
--

DROP TABLE IF EXISTS `grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo` (
  `id_grupo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombreGrupo` varchar(10) NOT NULL,
  `cuatrimestre` int(11) NOT NULL,
  `id_periodo` bigint(20) unsigned NOT NULL,
  `id_carrera` bigint(20) unsigned NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_grupo`),
  UNIQUE KEY `grupo_nombre_periodo_unico` (`nombreGrupo`,`id_periodo`),
  KEY `grupo_id_periodo_foreign` (`id_periodo`),
  KEY `grupo_id_carrera_foreign` (`id_carrera`),
  CONSTRAINT `grupo_id_carrera_foreign` FOREIGN KEY (`id_carrera`) REFERENCES `carrera` (`id_carrera`),
  CONSTRAINT `grupo_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo`
--

LOCK TABLES `grupo` WRITE;
/*!40000 ALTER TABLE `grupo` DISABLE KEYS */;
INSERT INTO `grupo` VALUES (2,'32-6A',6,1,9,0),(3,'32-2A',2,1,9,0),(4,'32-2G',2,2,9,0),(5,'1-1B',1,3,14,0),(6,'32-8H',8,12,9,0),(7,'IET-1B',1,17,18,1),(8,'ITI-2-4C',4,1,16,1),(9,'ISE-2K',2,6,17,1),(10,'IID-8L',8,1,19,1),(11,'IET-1A',1,1,18,1),(12,'IET-9F',9,2,18,1),(13,'IET-1B',1,1,18,1),(14,'IET-1C',1,1,18,1),(15,'IET-1D',1,1,18,1),(16,'ITI-2-8A',8,5,16,1);
/*!40000 ALTER TABLE `grupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario`
--

DROP TABLE IF EXISTS `horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horario` (
  `id_horario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombreHorario` varchar(45) NOT NULL,
  `estado` int(11) NOT NULL,
  `borrador` int(11) DEFAULT NULL,
  `id_grupo` bigint(20) unsigned NOT NULL,
  `id_periodo` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_horario`),
  KEY `horario_id_grupo_foreign` (`id_grupo`),
  KEY `horario_id_periodo_foreign` (`id_periodo`),
  CONSTRAINT `horario_id_grupo_foreign` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`),
  CONSTRAINT `horario_id_periodo_foreign` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario`
--

LOCK TABLES `horario` WRITE;
/*!40000 ALTER TABLE `horario` DISABLE KEYS */;
INSERT INTO `horario` VALUES (2,'Otoño 2026-ITI-2-4C',1,1,8,1),(5,'Otoño 2026-IET-1A',1,1,11,1),(6,'Otoño 2026-IET-1B',1,1,13,1),(7,'Otoño 2026-IET-1C',1,0,14,1),(9,'Invierno2027-ITI-2-8A',0,1,16,5);
/*!40000 ALTER TABLE `horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_16_191349_crear_tabla_docente',2),(5,'2026_10_17_045813_crear_tabla_carrera',3),(6,'2026_10_17_050705_crear_tabla_carrera_correctamente',4),(8,'2026_10_19_050953_create_periodo_table',5),(9,'2026_10_19_191242_create_grupo_table',6),(10,'2026_10_19_194637_agregar_unico_nombre_periodo_a_grupo',7),(11,'2026_10_19_233418_create_asignatura_table',8),(12,'2026_10_19_233425_create_asignatura_preferida_table',8),(13,'2026_10_21_195416_agregar_estado_a_grupo',9),(14,'2026_10_28_210512_create_disponibilidad_profesors_table',10),(15,'2026_11_10_011257_crear_tabla_horario',11),(16,'2026_11_10_011309_crear_tabla_detalle_horario',11),(17,'2026_11_19_082728_agregar_borrador_a_horario',12),(18,'2026_11_20_020349_crear_tabla_notificacion',13),(19,'2026_11_20_054321_modificar_estado_tabla_notificacion',14),(20,'2026_11_20_062246_modificar_mensaje_tabla_notificacion',15);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificacion`
--

DROP TABLE IF EXISTS `notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notificacion` (
  `id_notificacion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `medio` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0,
  `fechaEnvio` datetime NOT NULL,
  `id_usuario` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_notificacion`),
  KEY `notificacion_id_usuario_foreign` (`id_usuario`),
  CONSTRAINT `notificacion_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificacion`
--

LOCK TABLES `notificacion` WRITE;
/*!40000 ALTER TABLE `notificacion` DISABLE KEYS */;
INSERT INTO `notificacion` VALUES (1,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',1),(2,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',5),(3,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',6),(4,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,1,'2026-11-20 07:00:41',9),(5,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',11),(6,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',12),(7,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',13),(8,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',14),(9,1,'Prof 4 4 ha registrado su disponibilidad académica para el periodo .',1,0,'2026-11-20 07:00:41',20),(10,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',1),(11,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',5),(12,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',6),(13,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',9),(14,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',11),(15,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',12),(16,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',13),(17,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',14),(18,1,'Profe 3 3 ha registrado su disponibilidad académica para el periodo Invierno 2100.',1,0,'2026-11-20 07:23:48',20);
/*!40000 ALTER TABLE `notificacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodo`
--

DROP TABLE IF EXISTS `periodo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodo` (
  `id_periodo` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `estacion` varchar(30) NOT NULL,
  `anio` int(11) NOT NULL,
  PRIMARY KEY (`id_periodo`),
  UNIQUE KEY `periodo_estacion_anio_unico` (`estacion`,`anio`)
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodo`
--

LOCK TABLES `periodo` WRITE;
/*!40000 ALTER TABLE `periodo` DISABLE KEYS */;
INSERT INTO `periodo` VALUES (2,'Invierno',2026),(5,'Invierno',2027),(8,'Invierno',2028),(11,'Invierno',2029),(14,'Invierno',2030),(17,'Invierno',2031),(20,'Invierno',2032),(23,'Invierno',2033),(26,'Invierno',2034),(29,'Invierno',2035),(32,'Invierno',2036),(35,'Invierno',2037),(38,'Invierno',2038),(41,'Invierno',2039),(44,'Invierno',2040),(47,'Invierno',2041),(50,'Invierno',2042),(53,'Invierno',2043),(56,'Invierno',2044),(59,'Invierno',2045),(62,'Invierno',2046),(65,'Invierno',2047),(68,'Invierno',2048),(71,'Invierno',2049),(74,'Invierno',2050),(77,'Invierno',2051),(80,'Invierno',2052),(83,'Invierno',2053),(86,'Invierno',2054),(89,'Invierno',2055),(92,'Invierno',2056),(95,'Invierno',2057),(98,'Invierno',2058),(101,'Invierno',2059),(104,'Invierno',2060),(107,'Invierno',2061),(110,'Invierno',2062),(113,'Invierno',2063),(116,'Invierno',2064),(119,'Invierno',2065),(122,'Invierno',2066),(125,'Invierno',2067),(128,'Invierno',2068),(131,'Invierno',2069),(134,'Invierno',2070),(137,'Invierno',2071),(140,'Invierno',2072),(143,'Invierno',2073),(146,'Invierno',2074),(149,'Invierno',2075),(152,'Invierno',2076),(155,'Invierno',2077),(158,'Invierno',2078),(161,'Invierno',2079),(164,'Invierno',2080),(167,'Invierno',2081),(170,'Invierno',2082),(173,'Invierno',2083),(176,'Invierno',2084),(179,'Invierno',2085),(182,'Invierno',2086),(185,'Invierno',2087),(188,'Invierno',2088),(191,'Invierno',2089),(194,'Invierno',2090),(197,'Invierno',2091),(200,'Invierno',2092),(203,'Invierno',2093),(206,'Invierno',2094),(209,'Invierno',2095),(212,'Invierno',2096),(215,'Invierno',2097),(218,'Invierno',2098),(221,'Invierno',2099),(224,'Invierno',2100),(1,'Otoño',2026),(4,'Otoño',2027),(7,'Otoño',2028),(10,'Otoño',2029),(13,'Otoño',2030),(16,'Otoño',2031),(19,'Otoño',2032),(22,'Otoño',2033),(25,'Otoño',2034),(28,'Otoño',2035),(31,'Otoño',2036),(34,'Otoño',2037),(37,'Otoño',2038),(40,'Otoño',2039),(43,'Otoño',2040),(46,'Otoño',2041),(49,'Otoño',2042),(52,'Otoño',2043),(55,'Otoño',2044),(58,'Otoño',2045),(61,'Otoño',2046),(64,'Otoño',2047),(67,'Otoño',2048),(70,'Otoño',2049),(73,'Otoño',2050),(76,'Otoño',2051),(79,'Otoño',2052),(82,'Otoño',2053),(85,'Otoño',2054),(88,'Otoño',2055),(91,'Otoño',2056),(94,'Otoño',2057),(97,'Otoño',2058),(100,'Otoño',2059),(103,'Otoño',2060),(106,'Otoño',2061),(109,'Otoño',2062),(112,'Otoño',2063),(115,'Otoño',2064),(118,'Otoño',2065),(121,'Otoño',2066),(124,'Otoño',2067),(127,'Otoño',2068),(130,'Otoño',2069),(133,'Otoño',2070),(136,'Otoño',2071),(139,'Otoño',2072),(142,'Otoño',2073),(145,'Otoño',2074),(148,'Otoño',2075),(151,'Otoño',2076),(154,'Otoño',2077),(157,'Otoño',2078),(160,'Otoño',2079),(163,'Otoño',2080),(166,'Otoño',2081),(169,'Otoño',2082),(172,'Otoño',2083),(175,'Otoño',2084),(178,'Otoño',2085),(181,'Otoño',2086),(184,'Otoño',2087),(187,'Otoño',2088),(190,'Otoño',2089),(193,'Otoño',2090),(196,'Otoño',2091),(199,'Otoño',2092),(202,'Otoño',2093),(205,'Otoño',2094),(208,'Otoño',2095),(211,'Otoño',2096),(214,'Otoño',2097),(217,'Otoño',2098),(220,'Otoño',2099),(223,'Otoño',2100),(3,'Primavera',2026),(6,'Primavera',2027),(9,'Primavera',2028),(12,'Primavera',2029),(15,'Primavera',2030),(18,'Primavera',2031),(21,'Primavera',2032),(24,'Primavera',2033),(27,'Primavera',2034),(30,'Primavera',2035),(33,'Primavera',2036),(36,'Primavera',2037),(39,'Primavera',2038),(42,'Primavera',2039),(45,'Primavera',2040),(48,'Primavera',2041),(51,'Primavera',2042),(54,'Primavera',2043),(57,'Primavera',2044),(60,'Primavera',2045),(63,'Primavera',2046),(66,'Primavera',2047),(69,'Primavera',2048),(72,'Primavera',2049),(75,'Primavera',2050),(78,'Primavera',2051),(81,'Primavera',2052),(84,'Primavera',2053),(87,'Primavera',2054),(90,'Primavera',2055),(93,'Primavera',2056),(96,'Primavera',2057),(99,'Primavera',2058),(102,'Primavera',2059),(105,'Primavera',2060),(108,'Primavera',2061),(111,'Primavera',2062),(114,'Primavera',2063),(117,'Primavera',2064),(120,'Primavera',2065),(123,'Primavera',2066),(126,'Primavera',2067),(129,'Primavera',2068),(132,'Primavera',2069),(135,'Primavera',2070),(138,'Primavera',2071),(141,'Primavera',2072),(144,'Primavera',2073),(147,'Primavera',2074),(150,'Primavera',2075),(153,'Primavera',2076),(156,'Primavera',2077),(159,'Primavera',2078),(162,'Primavera',2079),(165,'Primavera',2080),(168,'Primavera',2081),(171,'Primavera',2082),(174,'Primavera',2083),(177,'Primavera',2084),(180,'Primavera',2085),(183,'Primavera',2086),(186,'Primavera',2087),(189,'Primavera',2088),(192,'Primavera',2089),(195,'Primavera',2090),(198,'Primavera',2091),(201,'Primavera',2092),(204,'Primavera',2093),(207,'Primavera',2094),(210,'Primavera',2095),(213,'Primavera',2096),(216,'Primavera',2097),(219,'Primavera',2098),(222,'Primavera',2099),(225,'Primavera',2100);
/*!40000 ALTER TABLE `periodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('FWkFWscBT3WgaZfou3pdFXMMRQCcuYD6f00tZscB',9,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoibXRnTVJkNEVEeXBkVmZNZ2EydUFrRkhLYlFtaVRNUkVub3JzYnlyNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZXNwYWxkby1iYXNlLWRhdG9zIjtzOjU6InJvdXRlIjtzOjE0OiJyZXNwYWxkby5pbmRleCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjk7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjQ6IjljMjZmNTBmNzMzYmI5ZjE0ODNjZWMwYjRlMjM0MWRmYmMyOTYwNTRmNGEyNjc3Zjc2ZWMwMTU0NDVhYzZmNjIiO30=',1795299471);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id_usuario` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombreUsuario` varchar(60) NOT NULL,
  `apellidoPaterno` varchar(60) NOT NULL,
  `apellidoMaterno` varchar(60) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `fechaRegistro` datetime NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario_correo_unique` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Lizbeth','Barreto','Basave','bblo230123@gmail.com','$2y$12$QcdzAyEEHlgxNj4LdEKMpuvM/Ogk9uN8QEvieXBS22Lr4FggWS4rK',2,1,'2026-10-10 04:56:32','2026-10-21 21:44:40',NULL),(2,'Administrador','Prueba','Sistema','admin@upemor.edu.mx','$2y$12$HKiZdxwd3btJtQGJvuJIi.10.U8lNk7zajOJPGP3oijAayOAjgVLK',1,0,'2026-10-10 05:16:43','2026-10-16 18:29:39','2026-10-17 00:46:38'),(3,'Pepe','Toño',NULL,'a@gmail.com','$2y$12$F56kdlIcHAfu57TXyulfZOPZXMGR7W9WFgIOocaMBqQBCN7gHuFVq',3,1,'2026-10-13 02:40:00','2026-10-16 18:51:57',NULL),(4,'prueba','hola',NULL,'prueba@gmail.com','$2y$12$DNdQTf9/a/8oHW8Vpg.hNOuj/o2NJvZLoWBKV72lshXCenHP9k0Vq',3,1,'2026-10-14 01:48:07','2026-11-10 01:00:17',NULL),(5,'prueba','2',NULL,'prueba2@gmail.com','$2y$12$wNAhXVgFRpii/lzKnl0UEOp8e9Xy1kFr5sHcUF/ev7KVqpPVmw56e',1,1,'2026-10-14 02:57:25','2026-10-14 03:05:31',NULL),(6,'prueba','3',NULL,'prueba3@gmail.com','$2y$12$LgVXe6gRwC5ipFLx3Pr7oethEfZLJYQKGoKwBTO7aEBkFTcuRD4RG',2,1,'2026-10-14 03:06:15','2026-11-20 01:46:25',NULL),(7,'docente2','4',NULL,'prueba4@gmail.com','$2y$12$34milOdZ1I1cuAGHxScERe0dDHotYEdfuP4aqJMYh5emjBLwLnovK',3,1,'2026-10-14 03:19:49','2026-11-02 20:32:03',NULL),(8,'Docente','Sistema',NULL,'admin1@upemor.edu.mx','$2y$12$LTzuMKgAjVHmMxzJ091Mwu6spWx3ahoPBC8iLDIOdCvGQhYsag7GC',3,0,'2026-10-16 12:53:38','2026-10-17 04:24:04','2026-11-03 01:33:17'),(9,'liz','a',NULL,'ad@gmail.com','$2y$12$VMlCb6w.fjgp.pd02HazJuuNaTXpLs.iPUBU6bR9Yd32mVrJCsb1a',1,1,'2026-10-16 18:56:10','2026-11-21 21:52:32',NULL),(10,'prueba 5','a',NULL,'z@gmail.com','$2y$12$g80jJ5urb1vueDSi2SDzMuvcap7c5TzTs1BV4a8qV74hsYC9GbZ56',3,0,'2026-10-17 03:04:11','2026-11-10 00:58:51','2026-11-14 03:48:14'),(11,'prueba 7','A',NULL,'x@gmail.com','$2y$12$kjvdahoA2tgjU2LBWVQDA.YsnIz5734svxbXP5zYdBGl6rEK035DW',1,1,'2026-10-18 17:39:42',NULL,NULL),(12,'prueba 8','a',NULL,'c@gmail.com','$2y$12$69ZenTjQOOqFh4HOOUe6AepbN0s2DYH.vuMTUbjxnwzuEjMO590Mm',1,1,'2026-10-18 17:40:05',NULL,NULL),(13,'233','3223',NULL,'223@gmail.com','$2y$12$m/Z/ed9pIVwKkDBHC65VO.WOiXIatFkkZITOK5jzLdQl2HLcGFVFO',1,1,'2026-10-18 21:12:39',NULL,NULL),(14,'v','v','v','v@gmail.com','$2y$12$W9ImQxEJPYQR.i4yowz7me3gqy7ohY54LYcFjzSl.nQFGF2YTr5/a',1,1,'2026-10-19 21:49:23',NULL,NULL),(15,'Profe 1','1',NULL,'1@gmail.com','$2y$12$YNCkqPCsFqHWJIyY8gCcQeIJDpZGjKdOpfZGdfSjdTfsyjbUBQkqK',3,1,'2026-11-13 21:55:07',NULL,NULL),(16,'profe 2','2',NULL,'2@gmail.com','$2y$12$OiRp8kbv1rmg6d7dLTHSquEyedgZwfZR7/XMAUtL48dMgzCTPcjB6',3,1,'2026-11-13 21:55:27',NULL,NULL),(17,'Profe 3','3',NULL,'3@gmail.com','$2y$12$JWvpoO4F.Eixq7UrzZa4Au0peXgLQ7y7fwKtLs.dj1HgLDIX.CQSm',3,1,'2026-11-13 22:03:00','2026-11-20 07:23:18',NULL),(18,'Prof 4','4',NULL,'4@gmail.com','$2y$12$b9g6hjEIOD3aJXQadQZLi.HkzB.6AOCvzORq9bdcgCWGuVUJ3E1vq',3,1,'2026-11-13 22:03:14','2026-11-20 06:59:22',NULL),(19,'Prof 5','5',NULL,'5@gmail.com','$2y$12$SOatSKDKYCWSxhbtwIuhQe2Vxjk0hILYKvQIDaLZ3wn.0pzZVL146',3,1,'2026-11-13 23:11:24',NULL,NULL),(20,'Lizbeth','Barreto',NULL,'BBLO230123@upemor.edu.mx','$2y$12$E20dW10XD5kcLVjQKGkQ7.o45tH2DXDYa7ob6SZkRf01ohhyiYD0O',1,1,'2026-11-20 04:14:20','2026-11-20 04:14:32',NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'schedulesync'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-21 16:18:08
