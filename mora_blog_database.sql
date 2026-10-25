-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: mora-blog-second
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
-- Table structure for table `about_settings`
--

DROP TABLE IF EXISTS `about_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `about_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_title` varchar(255) NOT NULL DEFAULT 'About Us',
  `breadcrumb_title` varchar(255) NOT NULL DEFAULT 'About',
  `hero_headline` text DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT 'images/about.jpg',
  `main_story` longtext DEFAULT NULL,
  `mission_title` varchar(150) DEFAULT 'Mission',
  `mission_content` longtext DEFAULT NULL,
  `organization_title` varchar(150) DEFAULT 'Organization',
  `organization_content` longtext DEFAULT NULL,
  `responsibilities_title` varchar(150) DEFAULT 'Responsibilities',
  `responsibilities_content` longtext DEFAULT NULL,
  `funding_title` varchar(150) DEFAULT 'Funding',
  `funding_content` longtext DEFAULT NULL,
  `vision_title` varchar(150) DEFAULT 'Lumens',
  `vision_content` longtext DEFAULT NULL,
  `distribution_title` varchar(150) DEFAULT 'Lumen Distribution',
  `distribution_content` longtext DEFAULT NULL,
  `community_title` varchar(150) DEFAULT 'Direct Signup Program',
  `community_content` longtext DEFAULT NULL,
  `jobs_title` varchar(150) DEFAULT 'Jobs',
  `jobs_content` longtext DEFAULT NULL,
  `team_title` varchar(150) DEFAULT 'Team',
  `team_content` longtext DEFAULT NULL,
  `show_topics` tinyint(1) DEFAULT 1,
  `topics_title` varchar(150) DEFAULT 'Topics',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `about_settings`
--

LOCK TABLES `about_settings` WRITE;
/*!40000 ALTER TABLE `about_settings` DISABLE KEYS */;
INSERT INTO `about_settings` VALUES (1,'About Us','About','Mora.org connects people to low-cost financial services to fight poverty and develop individual potential. Interested? WeΓÇÖre hiring! Explore jobs at Mora.org','uploads/about/about_5ec0d0588ca3d961684d96b984e70db6.jpg','The mission of the Stellar Development Foundation (SDF) is to promote global financial access, literacy, and inclusion. SDF accomplishes this by expanding worldwide access to low-cost financial services through the development and maintenance of technology and partnerships. SDFΓÇÖs vision is an open and affordable financial system where people of all income levels can access simple-to-use, secure, and low cost finan-cial services. SDF also aims to empower developers with useful technology to create financial products and services for their communities.','Mission','SDF employs full-time and part-time employees, as well as contractors, to develop and execute programs that promote global financial access, literacy, and inclusion. Aside from personnel costs, other operational expenses include the costs of office rent, office amenities, hardware, software, and partnership and educational programs.\r\n\r\nWebsites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Organization','Websites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Responsibilities','Websites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Funding','SDF employs full-time and part-time employees, as well as contractors, to develop and execute programs that promote global financial access, literacy, and inclusion. Aside from personnel costs, other operational expenses include the costs of office rent, office amenities, hardware, software, and partnership and educational programs.','Lumens','Websites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Lumen Distribution','Websites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Direct Signup Program','Websites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action','Jobs','SDF employs full-time and part-time employees, as well as contractors, to develop and execute programs that promote global financial access, literacy, and inclusion. Aside from personnel costs, other operational expenses include the costs of office rent, office amenities, hardware, software, and partnership and educational programs.\r\n\r\nWebsites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.','Team','SDF employs full-time and part-time employees, as well as contractors, to develop and execute programs that promote global financial access, literacy, and inclusion. Aside from personnel costs, other operational expenses include the costs of office rent, office amenities, hardware, software, and partnership and educational programs.\r\n\r\nWebsites exist to make engagement between companies or individuals and their website visitors happen more easily and effectively. The most effective way for a website to indicate which action theyΓÇÖd like visitors to take is whatΓÇÖs called a Call to Action\r\n\r\nIn this Divi tutorial, weΓÇÖll be showing you how to create a vibrant CTA section using Divi and Photoshop. You can use this design on any website by changing the images and colors that are being used.',1,'Topics','2026-10-21 19:08:39');
/*!40000 ALTER TABLE `about_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advertisements`
--

DROP TABLE IF EXISTS `advertisements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertisements`
--

LOCK TABLES `advertisements` WRITE;
/*!40000 ALTER TABLE `advertisements` DISABLE KEYS */;
INSERT INTO `advertisements` VALUES (5,'Shoes','uploads/advertisements/db36f7cec488ad1cd40cf0f507182eab.jpg','https://echosabove.com/products/away-day-forest-green?gad_source=1&gad_campaignid=24133560388&gbraid=0AAAABC-hx-SMtFzXDB6a_nIAibRgsIXCl&gclid=CjwKCAjwoaLWBhAWEiwAnyitu9FFLBVhvYp4uOUJjkATsZ3gaQUWSR1wxPW0_FzXiOenwmjLYmyFDhoCXKwQAvD_BwE','active','2026-10-09 20:42:01');
/*!40000 ALTER TABLE `advertisements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authors`
--

LOCK TABLES `authors` WRITE;
/*!40000 ALTER TABLE `authors` DISABLE KEYS */;
INSERT INTO `authors` VALUES (1,'Amit Mahor','images/post/author-1.jpg','Mora Blog administrator and content contributor.','2026-10-05 19:29:32');
/*!40000 ALTER TABLE `authors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Business','business'),(2,'Travel','travel'),(3,'Fashion','fashion'),(4,'Technology','technology'),(5,'Gaming','gaming'),(6,'Watch It','watch-it');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,'Amit','amit@example.com','This is a very useful and informative post.','2026-10-05 19:34:29'),(2,1,'Rahul','rahul@example.com','Great article. I really enjoyed reading it.','2026-10-05 19:34:29'),(3,1,'Priya','priya@example.com','Very interesting information. Thanks for sharing.','2026-10-05 19:34:29'),(4,1,'Ankit','ankitmahor@gmail.com','Good News','2026-10-05 19:45:42'),(5,5,'Arun Pratap','Arunpratap@gmail.com','Badiya News','2026-10-05 19:46:27'),(6,5,'Anup Mahor','anup@gmail.com','Good News','2026-10-05 19:53:38'),(7,1,'Amit Mahor','amitmahor613@gmail.com','Nice Video','2026-10-06 23:06:59'),(8,1,'Amit Mahor','anamikamahor5@gmail.com','Nice','2026-10-07 23:46:29'),(9,5,'Amit Mahor','anamikamahor5@gmail.com','Nice','2026-10-08 19:51:42'),(10,7,'Shivani','shivani123@gmail.com','Wow Nice','2026-10-11 20:19:45');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `request_type` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,'Amit Mahor','anamikamahor5@gmail.com','Xd Code Club','Travel Post','Hii, I want to Travel Post','read','2026-10-06 20:12:27'),(2,'Amit Mahor','amitmahor613@gmail.com','Xd Code Club','Tech Post','I want show my technology post','read','2026-10-10 00:37:32');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,10,'T-Shirt 16',100.00,1,100.00),(2,2,7,'Blue T Shirt Woman',19.00,1,19.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `registered_user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(15) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash_on_delivery',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'MB-20261015-561B0524',2,'Amit Mahor','amitmahor613@gmail.com','7879194316','Ambah','Ambah','MP','476111',100.00,'processing','cash_on_delivery','2026-10-15 23:39:44'),(2,'MB-20261015-11A9B326',2,'Amit Mahor','amitmahor613@gmail.com','7879194316','Ambah Morena MP','Ambah','MP','476111',19.00,'pending','cash_on_delivery','2026-10-16 02:26:59');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `idx_password_resets_user_id` (`user_id`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `registered_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (3,2,'3103e125454cdcc6878984160c92f85b97675cd8e396838e99fcaad98f07fd5a','2026-10-21 20:56:54',NULL,'2026-10-21 20:26:54'),(4,3,'6c7f61e17e5cb564c7eb7b1cdc18069050fda11ba2ef947b8f32f76c3ac14c50','2026-10-21 20:57:18','2026-10-21 20:28:11','2026-10-21 20:27:18');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registered_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_registered_user_id` (`registered_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'Welcome to Mora','welcome-to-mora-blog','This is our first dynamic blog post.','This is the complete content of our first dynamic blog post. Later this content will be managed from the admin panel.','images/sample.jpg',1,1,'published','2026-10-02 19:56:14','2026-10-02 21:31:48',NULL),(2,'Beautiful Places to Visit','beautiful-places-to-visit','Explore some beautiful travel destinations.','This is a travel post about beautiful places to visit.','images/post/travel-post-1.jpg',2,1,'published','2026-10-02 22:11:00','2026-10-02 22:11:00',NULL),(3,'Watch It Demo Post','watch-it-demo-post','This is a demo Watch It post.','This is the complete content of the Watch It demo post.','images/post/wathc-1.jpg',6,1,'published','2026-10-02 23:25:23','2026-10-02 23:25:23',NULL),(4,'Watch It Second Demo Post','watch-it-second-demo-post','This is a second demo Watch It post.','This is the complete content of the Watch It secind demo post.','images/post/wathc-1.jpg',6,1,'published','2026-10-02 23:27:23','2026-10-02 23:27:23',NULL),(5,'Latest Fashion Trends','latest-fashion-trends','Discover the latest fashion trends and styles.','This is a sample fashion article for Mora Blog.','images/post/fashion-1.jpg',3,1,'published','2026-10-02 23:35:45','2026-10-02 23:35:45',NULL),(7,'For Business','for-business','Exapmle for Business Post 2','Exapmle for Business Post 2','uploads/posts/post_6ac7bccdbe3eb0.66935502.jpg',1,3,'published','2026-10-08 21:24:53','2026-10-10 20:50:58',NULL),(8,'Acharya Prashant Book Joyful','acharya-prashant-book-joyful','It is a Amazing Book for Youth\'s. It gives a path of joyful life .','It is a Amazing Book for Youth\'s. It gives a path of joyful life .','uploads/posts/post_6accea6fd43451.33585619.jpeg',6,4,'published','2026-10-11 20:24:45','2026-10-12 19:40:55',NULL),(9,'Gamming for India','gamming-for-india','Play games for fun','Play games for fun','uploads/posts/e622ae776841acc0704c0a2419112559.jpg',5,4,'published','2026-10-15 20:33:21','2026-10-15 20:39:25',2),(10,'Traveling Place','',NULL,'Kerel Famous Place','uploads/posts/b87ffb6562213a1c6c118b82360a7e2d.jpg',2,NULL,'published','2026-10-15 20:56:18','2026-10-15 20:56:41',2),(15,'Travel Places','travel-places',NULL,'Beautiful Place of Keral','uploads/posts/d59ed79cd8ae2ab0119b2b37f4227de3.jpg',6,NULL,'rejected','2026-10-15 21:31:50','2026-10-15 21:32:11',2);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Stockholm T-shirt',86.00,'images/shop/shop-1.jpg','T-shirt','Comfortable Stockholm T-shirt.','active','2026-10-08 19:58:29'),(2,'T-shirt Rest in Pizza',19.00,'images/shop/shop-2.jpg','T-shirt','Stylish Rest in Pizza T-shirt.','active','2026-10-08 19:58:29'),(3,'T-shirt Pocket Local',79.00,'images/shop/shop-3.jpg','T-shirt','Premium pocket local T-shirt.','active','2026-10-08 19:58:29'),(4,'T-shirt Rest in Pizza',9.00,'images/shop/shop-4.jpg','T-shirt','Affordable casual T-shirt.','active','2026-10-08 19:58:29'),(5,'Key to the City',39.00,'images/shop/shop-5.jpg','Concept','Key to the City product.','active','2026-10-08 19:58:29'),(6,'Clean White Shirt',36.00,'images/shop/shop-6.jpg','Cloth','Clean white shirt.','active','2026-10-08 19:58:29'),(7,'Blue T Shirt Woman',19.00,'uploads/products/f08383c6fc14ad20ed0a9dde2414f983.jpg','Cloth','Blue T-shirt for women.','active','2026-10-08 19:58:29'),(8,'T-shirt Double Daffy',90.00,'uploads/products/b7ee90a60eb84de9d135bbb5a59e745d.jpg','T-shirt','Double Daffy printed T-shirt.','active','2026-10-08 19:58:29'),(9,'T-shirt Vinyl Spin',78.00,'uploads/products/bb50253e934e7c78889562cd2f22776b.jpg','T-shirt','Vinyl Spin T-shirt.','active','2026-10-08 19:58:29'),(10,'T-Shirt 16',100.00,'uploads/products/b3476391d0bce1b4d6d8b722e92a686c.jpg','Fashion','Buy One Get Only One','active','2026-10-09 21:43:02');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registered_users`
--

DROP TABLE IF EXISTS `registered_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registered_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `can_post` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 1,
  `verification_token_hash` char(64) DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registered_users`
--

LOCK TABLES `registered_users` WRITE;
/*!40000 ALTER TABLE `registered_users` DISABLE KEYS */;
INSERT INTO `registered_users` VALUES (1,'Amit Mahor','amitmahor613@sololearn.temp','$2y$10$2ZOd8QXFDdRQTXgl7zumpO8SmqHf4e.k/PUOYQFazdsOczAw39oJK',NULL,'active','2026-10-06 20:33:06',1,1,NULL,NULL),(2,'Amit Mahor','amitmahor613@gmail.com','$2y$10$cyYMVyg6rzNUSa3h3al5sOPDF530EymP4WDI0WLMNGJh/0W1p7tKu',NULL,'active','2026-10-15 20:11:50',1,1,NULL,NULL),(3,'Amit Mahor','amitmahor16@gmail.com','$2y$10$cOwWGBTOBwhwZ2envEDrLOpNwxYsVe/QAjItPGwOiWL7rZuDbGXaO',NULL,'active','2026-10-19 22:30:48',0,1,NULL,NULL),(7,'Amit Mahor','vp.xdcodeclub@gmail.com','$2y$10$LPPDzpUQdtZJQirzdfPYUO34iingTCtO/tZcdPDeujj6G/6x7Oy6S',NULL,'active','2026-10-23 16:47:10',0,1,NULL,NULL);
/*!40000 ALTER TABLE `registered_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `site_description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `working_days` varchar(100) DEFAULT NULL,
  `closing_day` varchar(100) DEFAULT NULL,
  `copyright_year` varchar(10) DEFAULT NULL,
  `developer_name` varchar(100) DEFAULT NULL,
  `developer_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'Mora Blog','uploads/site-settings/logo_55bfbf8df495a185adb742d3b6aa085d.png','Established in 2026 as the first cloud mining provider, Blog has become a multi-functional Blog Categories, trusted by over a million users.','Morena Madhya Pradesh','Monday-Saturday','Sunday','2026','Amit Mahor','https://ssjdeveloper.com/','active','2026-10-06 19:20:48','2026-10-21 19:20:07',0);
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `social_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `platform` varchar(50) NOT NULL,
  `url` varchar(500) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
INSERT INTO `social_links` VALUES (1,'facebook','https://facebook.com/','active','2026-10-05 22:39:50'),(2,'twitter','https://twitter.com/','active','2026-10-05 22:39:50'),(3,'pinterest','https://pinterest.com/','active','2026-10-05 22:39:50'),(4,'linkedin','https://linkedin.com/','active','2026-10-05 22:39:50'),(5,'google','https://google.com/','active','2026-10-05 22:39:50'),(6,'instagram','https://instagram.com/amit_mahor_16','active','2026-10-05 22:39:50');
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscribers`
--

DROP TABLE IF EXISTS `subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscribers`
--

LOCK TABLES `subscribers` WRITE;
/*!40000 ALTER TABLE `subscribers` DISABLE KEYS */;
INSERT INTO `subscribers` VALUES (1,'anamikamahor5@gmail.com','active','2026-10-06 19:33:08'),(2,'amitmahor613@gmail.com','active','2026-10-06 19:35:57'),(3,'mahorsahab244@gmail.com','active','2026-10-06 23:10:09');
/*!40000 ALTER TABLE `subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'author',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Amit','admin@mora-blog.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC7F8Z8xZ8xZ8xZ8xZ8u','admin','2026-10-02 19:55:49'),(2,'Administrator','admin@mora.com','$2y$10$OQSKFVhbNSMap.x.hwRu1ORt25bEhzn/40iLH9f2slGxjpXFiic.G','admin','2026-10-08 20:37:48'),(3,'Demo Author','author@mora.com','$2y$10$WuPxn3R9gSSUJ1qQn/31JO71apXkw9BzSQWJyLr6kBgxnVXC1hUxi','author','2026-10-08 20:52:35'),(4,'Amit Mahor','amitmahor613@gmail.com','$2y$10$bHp8jbT5xSUC3wh1gKXcRe5xImsjKpj5OXxkKHT23S3.gixTUki46','author','2026-10-11 20:21:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `videos`
--

DROP TABLE IF EXISTS `videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `videos`
--

LOCK TABLES `videos` WRITE;
/*!40000 ALTER TABLE `videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `videos` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-23 22:30:58
