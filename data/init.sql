use jakeleon_amigos;

CREATE TABLE users (
	userId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	username VARCHAR(30),
	password VARCHAR(255),
	gender VARCHAR(10),
	isAdministrator BOOLEAN,
	isCoordinator BOOLEAN,
	isTeacher BOOLEAN
);

CREATE TABLE participants (
	participantId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	nickName VARCHAR(100),
	gender VARCHAR(10),
	age INT(11),
	dob DATE,
	email VARCHAR(100),
);

CREATE TABLE programs (
	
	programId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	description VARCHAR(255)
);

CREATE TABLE courses (
	courseId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT(11) UNSIGNED,
	name VARCHAR(255),
	startDate DATE,
	endDate	DATE,
	description VARCHAR(255),
	FOREIGN KEY (programId) REFERENCES programs (programId)
);


CREATE TABLE participantCourses (
	
	participantId INT(11),
	courseId INT(11),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
);

CREATE TABLE permissions (
	userId INT(11),
	courseId INT(11),
	FOREIGN KEY (userId) REFERENCES users (userId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
);

CREATE TABLE teachers (
	teacherId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	gender VARCHAR(10),
	village VARCHAR(200),
	email VARCHAR(200)
);

CREATE TABLE 
