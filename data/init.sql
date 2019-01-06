CREATE TABLE participants (
	participantId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200),
	lastName VARCHAR(200),
	nickname VARCHAR(100),
	gender VARCHAR(10),
	isActive BOOLEAN,
	age INT(11),
	dob DATE,
	dpi BIGINT UNIQUE,
	village VARCHAR(200),
	languages VARCHAR(255),
	email VARCHAR(100),
	phoneNumber BIGINT,
	phoneNumber_2 BIGINT,
	imageLocation VARCHAR(255),
	comments TEXT,
	UNIQUE KEY(firstName, lastName, nickname)
);

CREATE TABLE users (
	username VARCHAR(100) PRIMARY KEY,
	password VARCHAR(255),
	participantId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId)
);

CREATE TABLE roles (
	roleId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100),
	description VARCHAR(255)
);

INSERT INTO roles (name) VALUES ('administrator');
INSERT INTO roles (name) VALUES ('coordinator');
INSERT INTO roles (name) VALUES ('teacher');
INSERT INTO roles (name) VALUES ('technician');
INSERT INTO roles (name) VALUES ('student');

CREATE TABLE participantRoles (
	participantId INT(11) UNSIGNED,
	roleId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (roleId) REFERENCES roles (roleId),
	UNIQUE KEY(participantId, roleId)
);

CREATE TABLE programs (
	programId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	description VARCHAR(255),
	alive BOOLEAN NOT NULL SET DEFAULT 1
);

CREATE TABLE programCoordinators (
	programId INT(11) UNSIGNED,
	coordinatorId INT(11) UNSIGNED,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (coordinatorId) REFERENCES participants (participantId),
	UNIQUE KEY(programId, coordinatorId)
);

CREATE TABLE courses (
	courseId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT(11) UNSIGNED,
	teacherId INT(11) UNSIGNED,
	name VARCHAR(255),
	startDate DATE,
	endDate	DATE,
	description VARCHAR(255),
	daysOfWeek VARCHAR(255),
	alive BOOLEAN NOT NULL SET DEFAULT 1,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (teacherId) REFERENCES participants (participantId)
);

CREATE TABLE participantCourses (
	participantId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(participantId, courseId)
);

CREATE TABLE permissions (
	participantId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(participantId, courseId)
);

CREATE TABLE courseSessions (
	sessionId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT(11) UNSIGNED,
	alive BOOLEAN NOT NULL SET DEFAULT 1,
	sessionDate DATE,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, sessionDate)
);

CREATE TABLE attendance (
	attendanceId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	sessionId INT(11) UNSIGNED,
	participantId INT(11) UNSIGNED,
	attended ENUM('absent', 'present', 'excused') NOT NULL SET DEFAULT 'absent',
	FOREIGN KEY (sessionId) REFERENCES courseSessions (sessionId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(sessionId, participantId)
);

CREATE TABLE assignments (
	assignmentId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT(11) UNSIGNED,
	name VARCHAR(255),
	description VARCHAR(255),
	alive BOOLEAN NOT NULL SET DEFAULT 1,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, name)
);

CREATE TABLE grades (
	gradeId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	assignmentId INT(11) UNSIGNED,
	participantId INT(11) UNSIGNED,
	grade DECIMAL(5,2),
	FOREIGN KEY (assignmentId) REFERENCES assignments (assignmentId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(assignmentId, participantId)
);
