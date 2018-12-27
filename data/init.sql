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
	nickname VARCHAR(100),
	gender VARCHAR(10),
	isActive BOOLEAN,
	age INT(11),
	dob DATE,
	village VARCHAR(200),
	languages VARCHAR(255),
	email VARCHAR(100),
	imageLocation VARCHAR(255)
);

CREATE TABLE teachers (
	teacherId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	gender VARCHAR(10),
	village VARCHAR(200),
	email VARCHAR(200)
);

CREATE TABLE programs (

	programId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255),
	description VARCHAR(255)
);

CREATE TABLE courses (
	courseId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT(11) UNSIGNED,
	teacherId INT(11) UNSIGNED,
	name VARCHAR(255),
	startDate DATE,
	endDate	DATE,
	description VARCHAR(255),
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (teacherId) REFERENCES teachers (teacherId)
);


CREATE TABLE participantCourses (

	participantId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
);

CREATE TABLE permissions (
	userId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	FOREIGN KEY (userId) REFERENCES users (userId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
);
