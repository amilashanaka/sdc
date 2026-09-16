-- Active: 1717229315063@@127.0.0.1@3306@daq
 
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `f1` varchar(250) DEFAULT NULL, -- username
  `f2` text DEFAULT NULL, -- password
  PRIMARY KEY (`id`) USING BTREE
) ;

INSERT INTO users (f1, f2) 
VALUES ('admin', '$2y$10$MCq3kqg5TpP5rvviemVayuO4Hvfxh3/JJ4mylf6IsX7rhT3gagTee')


DROP TABLE IF EXISTS  device_types;

CREATE TABLE device_types(
  id int NOT NULL AUTO_INCREMENT,
  name varchar(50) DEFAULT NULL,
  PRIMARY KEY ( id ) USING BTREE
);

 
INSERT INTO device_types (name) VALUES 
('sc28'), ('sc11basic'), ('sc11si'), ('sc26'), ('sc24');
 


DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
 id int NOT NULL AUTO_INCREMENT,
    f1 varchar(50) DEFAULT NULL, -- Device Id 
    f2 varchar(250) DEFAULT NULL,  -- device secret key 
    f3 varchar(50) DEFAULT NULL, -- device office 
    f4 varchar(50) DEFAULT NULL, -- device local Ip Address
    f5 VARCHAR(50) DEFAULT NULL, -- device Firmware Version
    f6 VARCHAR(50) DEFAULT NULL, -- device Serial Number

    device_type INT DEFAULT 0, -- device type: 

    PRIMARY KEY (id) USING BTREE
);

INSERT INTO settings (f1, f2, f3, f4, device_type, f5, f6) VALUES 
('SDC', 'Sample Secret Key', 'Sample Office', '192.168.2.99', 1, '1.0.0', 'SN123456');

 

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
    status int DEFAULT 0,
    PRIMARY KEY (id) USING BTREE
);

INSERT INTO logs (f1, f2, f3, f4, f5, f6, f7, f8, f9, f10, f11, status) VALUES 
(0, 'Sample Log', 100.00, 'This is a sample log entry.', 'Value1', 'Value2', 'Value3', 'Value4', 'Value5', 'Value6', 'Sample Image Path', 1);   

DROP TABLE IF EXISTS  flags;

CREATE TABLE flags(
  id int NOT NULL AUTO_INCREMENT,
  run_mode int DEFAULT 0, -- current device mode: 0=RUN, 1=DEBUG
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO flags (run_mode) VALUES (0); -- Default to RUN mode

DROP TABLE IF EXISTS  error_codes;

CREATE TABLE error_codes(
  id int NOT NULL AUTO_INCREMENT,
  code int DEFAULT 0,
  message varchar(250) DEFAULT NULL,
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO error_codes (code, message) VALUES 
(0, 'No error'), (1, 'Error 1'), (2, 'Error 2'), (3, 'Error 3'), (4, 'Error 4'), (5, 'Error 5'), (6, 'Error 6'), (7, 'Error 7'), (8, 'Error 8'), (9, 'Error 9'), (10, 'Error 10');
