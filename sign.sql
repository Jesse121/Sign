
CREATE DATABASE IF NOT EXISTS sign;

USE sign;

CREATE TABLE signInfo(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	number VARCHAR(11) NOT NULL,
	username VARCHAR(11) NOT NULL,
	time INT NOT NULL,
	counts INT(11) NOT NULL DEFAULT 0,
	scores INT(11) NOT NULL DEFAULT 0
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

