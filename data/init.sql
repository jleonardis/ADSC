CREATE DATABASE amigos;

use amigos;

CREATE TABLE users (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstname VARCHAR(30),
	lastname VARCHAR(30),
	age INT(3),
	location VARCHAR(50),
	date TIMESTAMP
);
