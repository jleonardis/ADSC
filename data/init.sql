CREATE TABLE users (
	username VARCHAR(100) PRIMARY KEY,
	password VARCHAR(255)
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
	username VARCHAR(100),
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	gender VARCHAR(10),
	village VARCHAR(200),
	FOREIGN KEY (username) REFERENCES users (username)
);

CREATE TABLE programs (

	programId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	coordinatorId INT(11) UNSIGNED,
	name VARCHAR(255),
	description VARCHAR(255),
	FOREIGN KEY (coordinatorId) REFERENCES users (userId)
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

CREATE TABLE courseSessions (
	sessionId INT(11) UNSIGNED PRIMARY KEY,
	courseId INT(11) UNSIGNED,
	date DATE,
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
)
