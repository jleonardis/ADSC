CREATE TABLE participants (
	participantId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200) NOT NULL,
	lastName VARCHAR(200) NOT NULL,
	nickname VARCHAR(100) NOT NULL,
	gender VARCHAR(10) NOT NULL,
	isActive BOOLEAN,
	dob DATE NOT NULL,
	dpi BIGINT UNIQUE NOT NULL,
	village VARCHAR(200),
	languages VARCHAR(255),
	email VARCHAR(100),
	phoneNumber BIGINT,
	phoneNumber_2 BIGINT,
	imageLocation VARCHAR(255),
	comments TEXT
) ENGINE InnoDB;

CREATE TABLE users (
	userId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(30) UNIQUE,
	password VARCHAR(255),
	participantId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId)
) ENGINE InnoDB;

CREATE TABLE roles (
	roleId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100),
	description VARCHAR(255)
) ENGINE InnoDB;

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
) ENGINE InnoDB;

CREATE TABLE programs (
	programId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	description VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1
) ENGINE InnoDB;

CREATE TABLE programCoordinators (
	programId INT(11) UNSIGNED,
	coordinatorId INT(11) UNSIGNED,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (coordinatorId) REFERENCES participants (participantId),
	UNIQUE KEY(programId, coordinatorId)
) ENGINE InnoDB;

CREATE TABLE courses (
	courseId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT(11) UNSIGNED,
	teacherId INT(11) UNSIGNED,
	name VARCHAR(255) UNIQUE,
	startDate DATE,
	endDate	DATE,
	description VARCHAR(255),
	daysOfWeek VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (teacherId) REFERENCES participants (participantId)
) ENGINE InnoDB;

CREATE TABLE participantCourses (
	participantId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	enrollDate DATE,
	dropOutDate DATE,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
) ENGINE InnoDB;

CREATE TABLE permissions (
	participantId INT(11) UNSIGNED,
	courseId INT(11) UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(participantId, courseId)
) ENGINE InnoDB;

CREATE TABLE courseSessions (
	sessionId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT(11) UNSIGNED,
	alive BOOLEAN NOT NULL DEFAULT 1,
	sessionDate DATE,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, sessionDate)
) ENGINE InnoDB;

CREATE TABLE attendance (
	attendanceId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	sessionId INT(11) UNSIGNED,
	participantId INT(11) UNSIGNED,
	attended ENUM('absent', 'present', 'excused') NOT NULL DEFAULT 'absent',
	FOREIGN KEY (sessionId) REFERENCES courseSessions (sessionId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(sessionId, participantId)
) ENGINE InnoDB;

CREATE TABLE assignments (
	assignmentId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT(11) UNSIGNED,
	name VARCHAR(255),
	description VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, name)
) ENGINE InnoDB;

CREATE TABLE grades (
	gradeId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	assignmentId INT(11) UNSIGNED,
	participantId INT(11) UNSIGNED,
	grade DECIMAL(5,2),
	FOREIGN KEY (assignmentId) REFERENCES assignments (assignmentId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(assignmentId, participantId)
) ENGINE InnoDB;

CREATE TABLE quotas (
	quotaId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT(11) UNSIGNED NOT NULL,
	name VARCHAR(255),
	description VARCHAR(255),
	amount DECIMAL(7,2) NOT NULL,
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, name)
) Engine InnoDB;

CREATE TABLE participantQuotas (
	participantQuotaId INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	participantId INT(11) UNSIGNED NOT NULL,
	quotaId INT(11) UNSIGNED NOT NULL,
	amountPaid DECIMAL(7,2) DEFAULT 0 NOT NULL,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (quotaId) REFERENCES quotas (quotaId),
	UNIQUE KEY(participantId, quotaId)
) Engine InnoDB;
