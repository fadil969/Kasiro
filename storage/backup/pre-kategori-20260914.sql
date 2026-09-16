-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: kasiro_db
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
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `status` varchar(10) NOT NULL DEFAULT 'Aktif',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','$2y$12$s5M3SVrbDQDY1f7BW9Pjsuso.Jd6nCPXlMcLMl0.qg6ORBGVi/tyy','admin','Aktif',NULL,'2026-09-12 00:58:47','2026-09-13 00:21:00'),(2,'Kasir Satu','kasir','$2y$12$ORpT3nzZvO8ky5RFryfc0uXxsAZuD1QLTFnJzRH5lunBLabuxOoh6','user','Aktif',NULL,'2026-09-12 00:58:47','2026-09-13 00:21:01'),(5,'Fadil','fadilgcr','$2y$12$dklDGpzrfx6WcuXYzT7IT.Xx02Tj4qLDZEaos3cMBQC9VFeISS.Yy','user','Aktif',NULL,'2026-09-12 02:12:56','2026-09-12 02:12:56');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category` varchar(20) NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `cost` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(10) NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_category_index` (`category`),
  KEY `menus_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
INSERT INTO `menus` VALUES (1,'Nasi Goreng','Makanan',15000,7000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(2,'Capjay','Makanan',18000,8000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(3,'Bakmi Godog','Makanan',14000,6000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(4,'Nasi Goreng Spesial','Makanan',22000,10000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(5,'Mie Goreng','Makanan',15000,7000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(7,'Es Teh','Minuman',5000,1500,'Aktif','2026-09-13 00:21:01','2026-09-13 00:27:56'),(8,'Es Jeruk','Minuman',6000,2000,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(9,'Kopi Panas','Minuman',7000,2500,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(10,'Es Kopi Susu','Minuman',8000,3000,'Nonaktif','2026-09-13 00:21:01','2026-09-13 00:21:01'),(11,'Air Mineral','Minuman',4000,1500,'Aktif','2026-09-13 00:21:01','2026-09-13 00:21:01');
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_12_075804_add_role_to_users_table',2),(5,'2026_09_12_083345_add_username_status_to_users_table',3),(6,'2026_09_13_000000_drop_framework_tables_not_needed',4),(7,'2026_09_13_100000_drop_email_from_users_table',5),(8,'2026_09_13_110000_create_menus_table',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-14  9:52:52
