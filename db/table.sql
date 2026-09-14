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


DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
 id int NOT NULL AUTO_INCREMENT,
    f1 varchar(50) DEFAULT NULL,
    f2 varchar(250) DEFAULT NULL,
    f3 varchar(50) DEFAULT NULL,
    f4 varchar(50) DEFAULT NULL,
    f5  varchar(50) DEFAULT NULL,
    f6  varchar(250) DEFAULT NULL,
    f7  varchar(250) DEFAULT NULL,
    f8  varchar(250) DEFAULT NULL,
    f9  varchar(250) DEFAULT NULL,
    f10  varchar(250) DEFAULT NULL,
    f11  varchar(250) DEFAULT NULL,
    img1 varchar(255) DEFAULT NULL, -- Column for the first image
    img2 varchar(255) DEFAULT NULL, -- Column for the second image
    img3 varchar(255) DEFAULT NULL, -- Column for the third image


    created_by int DEFAULT NULL,
    created_date datetime DEFAULT CURRENT_TIMESTAMP,
    updated_by int DEFAULT NULL,
    updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status int DEFAULT 0,
    PRIMARY KEY (id) USING BTREE
);

INSERT INTO `settings` VALUES (1,'SDC','Spicer Consulting ','+44(0)1234 765773  ','enq@spicerconsulting.com','<div class=\"dmBussinessInfoContactCompanyName font','https://www.facebook.com/10XAnalytix/','https://www.facebook.com/10XAnalytix/','https://www.instagram.com/tenxanalytix?igsh=OHJjNTFpOG45d3d2&utm_source=qr','https://t.me/tenxanalytixVVIP',NULL,NULL,'./uploads/settings/17652049668517.png','./uploads/settings/17652055694709.png','./uploads/settings/17652069132033.png',NULL,'2025-08-05 06:22:59',NULL,NULL,1);


drop TABLE if EXISTS logs;

CREATE TABLE logs (
    id int NOT NULL AUTO_INCREMENT,
    f1 int DEFAULT 0,  
    f2 varchar(50) DEFAULT NULL,
    f3 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    f4  text DEFAULT NULL,
    f5 varchar(50) DEFAULT NULL,
    f6 varchar(50) DEFAULT NULL,
    f7 varchar(50) DEFAULT NULL,
    f8 varchar(50) DEFAULT NULL,
    f9 varchar(50) DEFAULT NULL,
    f10 varchar(50) DEFAULT NULL,
    f11 varchar(255) DEFAULT NULL,
    created_by int DEFAULT NULL,
    created_date datetime DEFAULT CURRENT_TIMESTAMP,
    updated_by int DEFAULT NULL,
    updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status int DEFAULT 0,
    PRIMARY KEY (id) USING BTREE
);

INSERT INTO logs (f1, f2, f3, f4, f5, f6, f7, f8, f9, f10, f11, status) VALUES 
(0, 'Sample Log', 100.00, 'This is a sample log entry.', 'Value1', 'Value2', 'Value3', 'Value4', 'Value5', 'Value6', 'Sample Image Path', 1);   

DROP Table if EXISTS run_mode;

CREATE TABLE run_mode (
    id int NOT NULL AUTO_INCREMENT,
    f1 int DEFAULT 0, -- DEBUG mode: 0=RUN, 1=DEBUG
    created_by int DEFAULT NULL,
    created_date datetime DEFAULT CURRENT_TIMESTAMP,
    updated_by int DEFAULT NULL,
    updated_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status int DEFAULT 1,
    PRIMARY KEY (id) USING BTREE
);

INSERT INTO run_mode (f1, status) VALUES (0, 1); -- Default to RUN mode
