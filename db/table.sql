-- Active: 1717229315063@@127.0.0.1@3306@sdc
 


 DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    f1 int NULL DEFAULT 0,
    f2 VARCHAR(250) DEFAULT NULL,
    f3 VARCHAR(250) DEFAULT NULL,
    f4 VARCHAR(50) DEFAULT NULL,
    f5 VARCHAR(50) DEFAULT NULL,
    f6 VARCHAR(50) DEFAULT NULL,
    f7 VARCHAR(50) DEFAULT NULL,
    f8 VARCHAR(50) DEFAULT NULL,
    f9 VARCHAR(50) DEFAULT NULL,
    f10 VARCHAR(50) DEFAULT NULL,
    f11 VARCHAR(50) DEFAULT NULL,
    f12 VARCHAR(50) DEFAULT NULL,
    f13 VARCHAR(50) DEFAULT NULL,
    f14 VARCHAR(50) DEFAULT NULL,
    f15 VARCHAR(50) DEFAULT NULL,
    f16 VARCHAR(50) DEFAULT NULL,
    img1 VARCHAR(250) DEFAULT NULL,
    created_by int NULL DEFAULT NULL,
    updated_by int NULL DEFAULT NULL,
    created_date datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
    updated_date datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
    status int NOT NULL DEFAULT 1,
    UNIQUE (f2)
);

INSERT INTO `admins` VALUES (1, 1, 'admin', '$2y$10$MCq3kqg5TpP5rvviemVayuO4Hvfxh3/JJ4mylf6IsX7rhT3gagTee', NULL, NULL, 'Super Admin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-05-16 05:44:34', '2024-05-16 05:46:09', 1);

 
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




