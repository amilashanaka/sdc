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
  status int DEFAULT 0, -- 1=active, 0=inactive
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO device_types (name, status) VALUES 
('sc28', 1),
('sc11si', 1),
('sc11basic', 0),
('sc24', 1),
('sc26', 1);
 

  
 
  


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
    f1 int DEFAULT 0,  -- Priority level 0=Info, 1=Warning, 2=Error 
    f2 varchar(255) DEFAULT NULL, -- log message
    error int DEFAULT 0, -- associated error code
    module INT DEFAULT 0, -- module id
     created_date datetime DEFAULT CURRENT_TIMESTAMP,
    status int DEFAULT 0, -- 1=active, 0=archived
    PRIMARY KEY (id) USING BTREE
);

INSERT INTO logs (f1, f2, error, module, status) VALUES 
(0, 'System initialized successfully', 0, 0, 1),
(1, 'Warning: High temperature detected', 100, 1, 1),
(2, 'Error: Sensor failure', 200, 2, 1);

 

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
  f1 int DEFAULT 0, -- code
  f2 varchar(250) DEFAULT NULL, -- message
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO error_codes (f1, f2) VALUES 
(100, 'Sample Error Message 1'),
(200, 'Sample Error Message 2'),
(300, 'Sample Error Message 3');
  
DROP TABLE IF EXISTS  channels;

CREATE TABLE channels(
  id int NOT NULL AUTO_INCREMENT,
  f1 varchar(50) DEFAULT NULL, -- channel name
  f2 int DEFAULT 0, -- channel squence number
  f3 int DEFAULT 0, -- desimation factor
  f4 int DEFAULT 0, -- sample rate 
  status int DEFAULT 0, -- 1=active, 0=inactive
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO channels (f1, f2, f3, f4, status) VALUES
('Channel 1', 1, 1, 1000, 1),
('Channel 2', 2, 2, 500, 1),
('Channel 3', 3, 4, 250, 1),
('Channel 4', 4, 8, 125, 1),
('Channel 5', 5, 16, 62, 1),
('Channel 6', 6, 32, 31, 1),
('Channel 7', 7, 64, 15, 1),
('Channel 8', 8, 128, 7, 1),
('Channel 9', 9, 4, 250, 0),
('Channel 10', 10, 8, 125, 0),
('Channel 11', 11, 16, 62, 0),
('Channel 12', 12, 32, 31, 0),
('Channel 13', 13, 64, 15, 0),
('Channel 14', 14, 128, 7, 0),
('Channel 15', 15, 2, 500, 1),
('Channel 16', 16, 1, 1000, 0);



DROP TABLE IF EXISTS  modules;

CREATE TABLE modules(
  id int NOT NULL AUTO_INCREMENT,  
  f1 varchar(50) DEFAULT NULL,  -- module name 
  f2 int DEFAULT 0,   -- module squence number
  f3 TEXT DEFAULT NULL,  -- module description
  f5 VARCHAR(100) DEFAULT NULL, -- module base address
  f6 VARCHAR(50) DEFAULT NULL,  -- module icon
  status int DEFAULT 0,  -- 1=active, 0=inactive
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO modules (f1, f2, f3, f5, f6, status) VALUES
('ADC Core', 1, NULL, '0x43C00000', 'fa-wave-square', 1),
('DMA Engine', 2, NULL, '0x40400000', 'fa-exchange-alt', 1),
('Digital Filter', 3, NULL, '0x43C10000', 'fa-filter', 1),
('Trigger', 4, NULL, '0x41200000', 'fa-bolt', 1),
('SPI Controller', 5, NULL, '0x44A00000', 'fa-broadcast-tower', 1),
('TLS Server', 6, NULL, 'PS (software)', 'fa-lock', 1),
('AXI Interconnect', 7, NULL, 'Fabric', 'fa-sitemap', 1),
('Clocking Wizard', 8, NULL, '0x43C20000', 'fa-clock', 1),
('Processor Sys Reset', 9, NULL, 'PS7', 'fa-power-off', 1),
('AXI GPIO', 10, NULL, '0x41220000', 'fa-toggle-on', 1),
('Interrupt Controller', 11, NULL, '0x41800000', 'fa-project-diagram', 1),
('DMA FIFO / BRAM', 12, NULL, '0x43C30000', 'fa-memory', 1);

DROP TABLE IF EXISTS  health;

CREATE TABLE health(
  id int NOT NULL AUTO_INCREMENT,
  f1 varchar(50) DEFAULT NULL, -- health name
  f2 int DEFAULT 0, -- health squence number
  f3 TEXT DEFAULT NULL, -- health description
  f4 DECIMAL(10,2)  DEFAULT 0.00, -- health value
  f5 VARCHAR(100) DEFAULT NULL, -- health icon
  f6 VARCHAR(50) DEFAULT NULL, -- icon color
  status int DEFAULT 0, -- 1=active, 0=inactive
  PRIMARY KEY ( id ) USING BTREE
);

INSERT INTO health (f1, f2, f3, f4, f5, f6, status) VALUES
('Temperature', 1, 'Device temperature in Celsius', 25.00, 'fa-thermometer-half', '#ef4444', 1),
('Voltage', 2, 'Device voltage in Volts', 3.30, 'fa-bolt', '#f59e0b', 1),
('Current', 3, 'Device current in Amperes', 0.50, 'fa-plug', '#10b981', 1),
('Power', 4, 'Device power in Watts', 12.00, 'fa-bolt', '#3b82f6', 1),
('Clock', 5, 'Device clock frequency in MHz', 100.00, 'fa-clock', '#f43f5e', 1);


