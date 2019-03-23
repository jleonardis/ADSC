CREATE TABLE participants (
	participantId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstName VARCHAR(200) NOT NULL,
	lastName VARCHAR(200) NOT NULL,
	nickname VARCHAR(100) NOT NULL,
	gender VARCHAR(10) NOT NULL,
	isActive BOOLEAN,
	dob DATE NOT NULL,
	dpi VARCHAR(255) UNIQUE NOT NULL,
	village VARCHAR(200),
	languages VARCHAR(255),
	email VARCHAR(100),
	phoneNumber VARCHAR(20),
	phoneNumber_2 VARCHAR(20),
	imageLocation VARCHAR(255),
	maritalStatus VARCHAR(30),
	educationLevel VARCHAR(30),
	comments TEXT
) ENGINE InnoDB;

CREATE TABLE users (
	userId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(30) UNIQUE,
	password VARCHAR(255),
	participantId INT UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId)
) ENGINE InnoDB;

CREATE TABLE roles (
	roleId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100),
	description VARCHAR(255)
) ENGINE InnoDB;

INSERT INTO roles (name) VALUES ('administrator');
INSERT INTO roles (name) VALUES ('coordinator');
INSERT INTO roles (name) VALUES ('teacher');
INSERT INTO roles (name) VALUES ('technician');
INSERT INTO roles (name) VALUES ('student');

CREATE TABLE participantRoles (
	participantId INT UNSIGNED,
	roleId INT UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (roleId) REFERENCES roles (roleId),
	UNIQUE KEY(participantId, roleId)
) ENGINE InnoDB;

CREATE TABLE programs (
	programId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) UNIQUE,
	description VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1
) ENGINE InnoDB;

CREATE TABLE divisions (
	divisionId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT UNSIGNED,
	name VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	UNIQUE KEY(programId, name)
) ENGINE InnoDB;

CREATE TABLE programCoordinators (
	programId INT UNSIGNED,
	coordinatorId INT UNSIGNED,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (coordinatorId) REFERENCES participants (participantId),
	UNIQUE KEY(programId, coordinatorId)
) ENGINE InnoDB;

CREATE TABLE courses (
	courseId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	programId INT UNSIGNED,
	teacherId INT UNSIGNED,
	divisionId INT UNSIGNED,
	name VARCHAR(255) UNIQUE,
	startDate DATE,
	endDate	DATE,
	description VARCHAR(255),
	daysOfWeek SET('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (programId) REFERENCES programs (programId),
	FOREIGN KEY (teacherId) REFERENCES participants (participantId),
	FOREIGN KEY (divisionId) REFERENCES divisions (divisionId)
) ENGINE InnoDB;

CREATE TABLE participantCourses (
	participantId INT UNSIGNED,
	courseId INT UNSIGNED,
	enrollDate DATE,
	dropOutDate DATE,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId)
) ENGINE InnoDB;

CREATE TABLE permissions (
	participantId INT UNSIGNED,
	courseId INT UNSIGNED,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(participantId, courseId)
) ENGINE InnoDB;

CREATE TABLE courseSessions (
	sessionId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT UNSIGNED,
	alive BOOLEAN NOT NULL DEFAULT 1,
	sessionDate DATE,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, sessionDate)
) ENGINE InnoDB;

CREATE TABLE attendance (
	attendanceId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	sessionId INT UNSIGNED,
	participantId INT UNSIGNED,
	attended ENUM('absent', 'present', 'excused') NOT NULL DEFAULT 'absent',
	FOREIGN KEY (sessionId) REFERENCES courseSessions (sessionId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(sessionId, participantId)
) ENGINE InnoDB;

CREATE TABLE assignments (
	assignmentId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT UNSIGNED,
	name VARCHAR(255),
	description VARCHAR(255),
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(coBurseId, name)
) ENGINE InnoDB;

CREATE TABLE grades (
	gradeId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	assignmentId INT UNSIGNED,
	participantId INT UNSIGNED,
	grade DECIMAL(5,2),
	FOREIGN KEY (assignmentId) REFERENCES assignments (assignmentId),
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	UNIQUE KEY(assignmentId, participantId)
) ENGINE InnoDB;

CREATE TABLE quotas (
	quotaId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	courseId INT UNSIGNED NOT NULL,
	name VARCHAR(255),
	description VARCHAR(255),
	amount DECIMAL(7,2) NOT NULL,
	alive BOOLEAN NOT NULL DEFAULT 1,
	FOREIGN KEY (courseId) REFERENCES courses (courseId),
	UNIQUE KEY(courseId, name)
) Engine InnoDB;

CREATE TABLE participantQuotas (
	participantQuotaId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	participantId INT UNSIGNED NOT NULL,
	quotaId INT UNSIGNED NOT NULL,
	amountPaid DECIMAL(7,2) DEFAULT 0 NOT NULL,
	paymentDate DATE NOT NULL,
	FOREIGN KEY (participantId) REFERENCES participants (participantId),
	FOREIGN KEY (quotaId) REFERENCES quotas (quotaId)
) Engine InnoDB;

CREATE VIEW currentParticipantCourses_View AS
	SELECT participantId, courseId, firstName, lastName, gender,
		enrollDate, dpi IS NOT NULL AS hasDPI
	FROM participantCourses pc
	JOIN participants p
		USING (participantId)
	WHERE pc.dropOutDate IS NULL;

CREATE VIEW coursesStartDateEndDate_View AS
	SELECT courseId, MIN(sessionDate) AS startDate, MAX(sessionDate) AS endDate
	FROM courseSessions
	WHERE alive
	GROUP BY courseId;

CREATE VIEW courses_View AS
	SELECT courseId, programId, teacherId, name, description, daysOfWeek,
		cs.startDate as startDate, cs.endDate as endDate, alive
	FROM courses c
	LEFT JOIN coursesStartDateEndDate_View cs
	ON c.courseId = cs.courseId;
