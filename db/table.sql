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


DROP TABLE IF EXISTS settings;

CREATE TABLE settings (
 id int NOT NULL AUTO_INCREMENT,
    f1 varchar(50) DEFAULT NULL, -- Device Id 
    f2 varchar(250) DEFAULT NULL,  -- device secret key 
    f3 varchar(50) DEFAULT NULL, -- device office 
    f4 varchar(50) DEFAULT NULL, -- device location
    device_type INT DEFAULT 0, -- device type: 

    PRIMARY KEY (id) USING BTREE
);

INSERT INTO settings (f1, f2, f3, f4, device_type) VALUES 
('SDC', 'Sample Secret Key', 'Sample Office', 'Sample Location', 0);

 

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
