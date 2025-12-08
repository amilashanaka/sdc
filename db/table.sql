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
INSERT INTO `admins` VALUES (2, 2, 'Staff1', '$2y$10$ggccOXt2ySOm.b58qPWG6eJf0bpztQbzTu4tqN9EyToDrnKvFiYJm', NULL, NULL, 'Staff1', NULL, '07488414038', 'staff1@gmail.com', 'NO 43, New york village,Thalgasgoda', 'N/A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-08-12 17:41:55', '2025-08-22 18:56:10', 1);
INSERT INTO `admins` VALUES (3, 2, 'staff2', '$2y$10$0n/4j0i6H1jw/OX59rAqvuYRZIapDoBYI.66DEDJbSk55FJxZEp6G', NULL, NULL, 'staff2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '../uploads/admin/profile/3/17558888311224.jpg', NULL, 4, '2025-08-12 17:46:16', '2025-08-12 17:57:54', 0);
INSERT INTO `admins` VALUES (4, 2, 'testing1', '$2y$10$aktPAMZnjjwJtnBuHMXbperi5sAB/T3E8axrVDx/bJKcy8scG6gA.', NULL, NULL, 'testing1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2025-08-12 17:46:57', '2025-08-12 17:47:44', 0);
INSERT INTO `admins` VALUES (5, 2, 'user3', '$2y$10$KOETvAXFQtz/UEzIOwMJhOjvSX7eN4aBJ9vs6e4MfbpoTla19d68K', NULL, NULL, 'testing 3', NULL, '01122442526', 'user3@gmail.com', '23/1 Galle rd, matara', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, '2025-08-22 13:17:55', '2025-08-23 17:55:32', 1);
INSERT INTO `admins` VALUES (6, 2, 'RYM', '$2y$10$a63ORM7BpOoRichC9ed2Q.3VrmlE0QYvxnDj.X5Nw8wBIH7vuUHbG', NULL, NULL, 'RYM', NULL, '01122334456', 'RYM1@gmail.com', 'Delhi, India', NULL, NULL, NULL, NULL, NULL, NULL, '../uploads/admin/profile/6/17559601046543.jpg', NULL, 1, '2025-08-22 18:59:34', '2025-08-26 07:10:07', 1);

 
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

INSERT INTO `settings` VALUES (1, 'TENX', 'TENX ANALYTIX', '(+1) 96 716 6879', 'contact@site.com', '96 Oaktree Crescent, Bradley Stoke, Bristol, Avon,', 'https://www.facebook.com/10XAnalytix/', 'https://www.facebook.com/10XAnalytix/', 'https://www.instagram.com/tenxanalytix?igsh=OHJjNTFpOG45d3d2&utm_source=qr', 'https://t.me/tenxanalytixVVIP', NULL, NULL, './uploads/settings/17580975108089.png', NULL, NULL, NULL, '2025-08-05 06:22:59', NULL, NULL, 1);

 

drop TABLE if EXISTS packages;

CREATE TABLE packages (
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


INSERT INTO `packages` VALUES (1, 0, 'Free Channel', 0, NULL, '1-3 Signals Per Day', 'Major Forex and Gold', 'Free Trading Resources', 'Chart Analysis', 'Community Access', 'Get Started Free', NULL, NULL, '2025-08-21 18:42:13', 1, '2025-08-22 13:22:51', 1);
INSERT INTO `packages` VALUES (2, 0, 'VIP Channel', 10, NULL, '3-6 Signals Per Day', 'Unlimited Learning Access', '24/7 Technical Support', 'Advanced Chart Analysis', 'Scalp & Intraday Signals', 'Start VIP Now', NULL, NULL, '2025-08-21 18:42:13', 1, '2025-08-22 13:22:55', 1);
INSERT INTO `packages` VALUES (3, 0, 'VVIP Channel', 20, NULL, '6-15 Signals Per Day', 'Gold, Forex & Indices', 'Priority 24/7 Support', 'Professional Chart Analysis', 'Live Trade Results', 'Go Premium', NULL, NULL, '2025-08-21 18:42:13', 1, '2025-08-22 13:22:58', 1);
INSERT INTO `packages` VALUES (5, 0, 'testing - VIP Channel', 20, '<p><br></p>', '6-15 Signals Per Day', 'Gold, Forex & Indices', 'Priority 24/7 Support', 'Professional Chart Analysis', 'Live Trade Results', 'testing', NULL, NULL, '2025-08-28 10:30:06', 1, '2025-08-28 10:39:10', 0);

