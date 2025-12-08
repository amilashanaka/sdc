CREATE DATABASE IF NOT EXISTS sdc;
USE sdc;
 
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `f1` varchar(250) DEFAULT NULL,
  `f2` varchar(250) DEFAULT NULL,
  `f3` varchar(250) DEFAULT NULL,
  `f4` varchar(50) DEFAULT NULL,
  `f5` varchar(250) DEFAULT NULL,
  `f6` varchar(50) DEFAULT NULL,
  `f7` varchar(50) DEFAULT NULL,
  `f8` varchar(250) DEFAULT NULL,
  `f9` tinyint(1) NULL DEFAULT 0,
  `f10` varchar(50) DEFAULT NULL,
  
  `img1` text DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_date` datetime(0) NULL DEFAULT NULL,
  `updated_by` int NULL DEFAULT NULL,
  `updated_date` datetime(0) NULL DEFAULT NULL,
  `status` int NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ;

INSERT INTO users (f1, f2, f3, f4) 
VALUES ('admin', 'admin@example.com', '$2y$10$MCq3kqg5TpP5rvviemVayuO4Hvfxh3/JJ4mylf6IsX7rhT3gagTee', 'Administrator');
 