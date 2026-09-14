-- Active: 1717229315063@@127.0.0.1@3306@daq
 
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `f1` varchar(250) DEFAULT NULL, -- username
  `f2` varchar(250) DEFAULT NULL, -- email
  `f3` varchar(250) DEFAULT NULL, -- password
  `f4` tinyint(1) NULL DEFAULT 0, -- role
  `f5` varchar(250) DEFAULT NULL, -- full name
  `f6` varchar(50) DEFAULT NULL, -- phone
  `f7` LONGTEXT DEFAULT NULL, -- address

  `img1` text DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_date` datetime(0) NULL DEFAULT NULL,
  `updated_by` int NULL DEFAULT NULL,
  `updated_date` datetime(0) NULL DEFAULT NULL,
  `status` int NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ;

INSERT INTO users (f1, f2, f3, f4) 
VALUES ('admin', 'admin@example.com', '$2y$10$MCq3kqg5TpP5rvviemVayuO4Hvfxh3/JJ4mylf6IsX7rhT3gagTee', 1),
       ('user', 'user@example.com', '$2y$10$MCq3kqg5TpP5rvviemVayuO4Hvfxh3/JJ4mylf6IsX7rhT3gagTee', 2);


DROP TABLE IF EXISTS `products`;

CREATE TABLE `products`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `f1` varchar(250) DEFAULT NULL, -- product name
  `f2` text DEFAULT NULL, -- description
  `f3` decimal(10,2) DEFAULT NULL, -- price
  `f4` int DEFAULT NULL, -- stock
  `f5` varchar(250) DEFAULT NULL, -- category
  
  `img1` text DEFAULT NULL,
  `created_by` int NULL DEFAULT NULL,
  `created_date` datetime(0) NULL DEFAULT NULL,
  `updated_by` int NULL DEFAULT NULL,
  `updated_date` datetime(0) NULL DEFAULT NULL,
  `status` int NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ;