-- MySQL dump 10.13  Distrib 5.7.12, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mydb
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.19-MariaDB

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
-- Table structure for table `exercises`
--

DROP TABLE IF EXISTS `exercises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exercises` (
  `exercise_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `record` float unsigned DEFAULT NULL,
  PRIMARY KEY (`exercise_id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercises`
--

LOCK TABLES `exercises` WRITE;
/*!40000 ALTER TABLE `exercises` DISABLE KEYS */;
/*!40000 ALTER TABLE `exercises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invitations` (
  `invitation_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `workout_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`invitation_id`),
  KEY `fk_coach_id_idx` (`from_user_id`),
  KEY `fk_invitation_user2_idx` (`to_user_id`),
  KEY `fk_invitation_workout_idx` (`workout_id`),
  CONSTRAINT `fk_invitation_user1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_invitation_user2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_invitation_workout` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`workout_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invitations`
--

LOCK TABLES `invitations` WRITE;
/*!40000 ALTER TABLE `invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `message_id_UNIQUE` (`message_id`),
  KEY `fk_messages_users1_idx` (`from_user_id`),
  KEY `fk_messages_users2_idx` (`to_user_id`),
  CONSTRAINT `fk_messages_users1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_messages_users2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'ROLE_ADMIN'),(2,'ROLE_COACH'),(3,'ROLE_ATHLETE');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username_UNIQUE` (`login`),
  KEY `fk_users_roles1_idx` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (15,'administrator','$2y$13$Wg/8S/Xvfq31QnOpppCkTeUmlM78.hgYigfXFdICjbwaVXDV0.oha',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_data`
--

DROP TABLE IF EXISTS `users_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_data` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(128) NOT NULL,
  `surname` varchar(128) NOT NULL,
  `location` varchar(128) NOT NULL,
  `birthdate` date NOT NULL,
  `height` int(11) unsigned DEFAULT NULL,
  `weight` float unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  KEY `fk_users_data_users_idx` (`user_id`),
  CONSTRAINT `fk_users_data_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_data`
--

LOCK TABLES `users_data` WRITE;
/*!40000 ALTER TABLE `users_data` DISABLE KEYS */;
INSERT INTO `users_data` VALUES (15,'Administrator','Administrator','Kraków','2017-09-09',NULL,NULL);
/*!40000 ALTER TABLE `users_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_has_workout_routines`
--

DROP TABLE IF EXISTS `users_has_workout_routines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_has_workout_routines` (
  `user_id` int(11) NOT NULL,
  `workout_routine_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`workout_routine_id`),
  KEY `fk_users_has_workout_routines_workout_routines1_idx` (`workout_routine_id`),
  KEY `fk_users_has_workout_routines_users1_idx` (`user_id`),
  CONSTRAINT `fk_users_has_workout_routines_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_workout_routines_workout_routines1` FOREIGN KEY (`workout_routine_id`) REFERENCES `workout_routines` (`workout_routine_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_has_workout_routines`
--

LOCK TABLES `users_has_workout_routines` WRITE;
/*!40000 ALTER TABLE `users_has_workout_routines` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_has_workout_routines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_has_workouts`
--

DROP TABLE IF EXISTS `users_has_workouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_has_workouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `workout_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_users_has_workouts_workouts1_idx` (`workout_id`),
  KEY `fk_users_has_workouts_users1_idx` (`user_id`),
  CONSTRAINT `fk_users_has_workouts_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_workouts_workouts1` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`workout_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_has_workouts`
--

LOCK TABLES `users_has_workouts` WRITE;
/*!40000 ALTER TABLE `users_has_workouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_has_workouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workout_comments`
--

DROP TABLE IF EXISTS `workout_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workout_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `workout_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`comment_id`),
  UNIQUE KEY `comment_id_UNIQUE` (`comment_id`),
  KEY `fk_comments_workouts1_idx` (`workout_id`),
  KEY `fk_comments_users1_idx` (`user_id`),
  CONSTRAINT `fk_comments_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_workouts1` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`workout_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workout_comments`
--

LOCK TABLES `workout_comments` WRITE;
/*!40000 ALTER TABLE `workout_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `workout_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workout_days`
--

DROP TABLE IF EXISTS `workout_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workout_days` (
  `workout_day_id` int(11) NOT NULL AUTO_INCREMENT,
  `weekday` tinyint(1) NOT NULL,
  `workout_routine_id` int(11) NOT NULL,
  PRIMARY KEY (`workout_day_id`),
  UNIQUE KEY `workout_day_id_UNIQUE` (`workout_day_id`),
  KEY `fk_workout_days_workout_routines1_idx` (`workout_routine_id`),
  CONSTRAINT `fk_workout_days_workout_routines1` FOREIGN KEY (`workout_routine_id`) REFERENCES `workout_routines` (`workout_routine_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workout_days`
--

LOCK TABLES `workout_days` WRITE;
/*!40000 ALTER TABLE `workout_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `workout_days` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workout_days_has_exercises`
--

DROP TABLE IF EXISTS `workout_days_has_exercises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workout_days_has_exercises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sets` int(11) unsigned NOT NULL,
  `reps` int(11) unsigned NOT NULL,
  `weight` float unsigned DEFAULT NULL,
  `workout_day_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_workout_days_has_exercises_exercises1_idx` (`exercise_id`),
  KEY `fk_workout_days_has_exercises_workout_days1_idx` (`workout_day_id`),
  CONSTRAINT `fk_workout_days_has_exercises_exercises1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`exercise_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_workout_days_has_exercises_workout_days1` FOREIGN KEY (`workout_day_id`) REFERENCES `workout_days` (`workout_day_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workout_days_has_exercises`
--

LOCK TABLES `workout_days_has_exercises` WRITE;
/*!40000 ALTER TABLE `workout_days_has_exercises` DISABLE KEYS */;
/*!40000 ALTER TABLE `workout_days_has_exercises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workout_routines`
--

DROP TABLE IF EXISTS `workout_routines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workout_routines` (
  `workout_routine_id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(10) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `number_of_days` int(11) unsigned NOT NULL,
  `is_public` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`workout_routine_id`),
  UNIQUE KEY `workout_routines_id_UNIQUE` (`workout_routine_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workout_routines`
--

LOCK TABLES `workout_routines` WRITE;
/*!40000 ALTER TABLE `workout_routines` DISABLE KEYS */;
/*!40000 ALTER TABLE `workout_routines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workouts`
--

DROP TABLE IF EXISTS `workouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workouts` (
  `workout_id` int(11) NOT NULL AUTO_INCREMENT,
  `due_date` datetime NOT NULL,
  `sets_amount` int(11) unsigned DEFAULT NULL,
  `reps_amount` int(11) unsigned DEFAULT NULL,
  `weight_amount` float unsigned DEFAULT NULL,
  PRIMARY KEY (`workout_id`),
  UNIQUE KEY `workout_id_UNIQUE` (`workout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workouts`
--

LOCK TABLES `workouts` WRITE;
/*!40000 ALTER TABLE `workouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `workouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workouts_has_exercises`
--

DROP TABLE IF EXISTS `workouts_has_exercises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workouts_has_exercises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sets` int(11) unsigned NOT NULL,
  `reps` int(11) unsigned NOT NULL,
  `weight` float unsigned DEFAULT NULL,
  `workout_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_workouts_has_workout_exercises_workouts1_idx` (`workout_id`),
  KEY `_idx` (`exercise_id`),
  CONSTRAINT `fk_workouts_has_workout_exercises_exercises` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`exercise_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_workouts_has_workout_exercises_workouts1` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`workout_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workouts_has_exercises`
--

LOCK TABLES `workouts_has_exercises` WRITE;
/*!40000 ALTER TABLE `workouts_has_exercises` DISABLE KEYS */;
/*!40000 ALTER TABLE `workouts_has_exercises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'mydb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-09-10 19:09:17
