<?php
require "common.php";

if($_POST && isset($_POST['submit'])) {
  if(empty($_POST['username'])) {
    echo "ingresa tu usuario";
  }
  else if(empty($_POST['password'])) {
    echo "ingresa tu contraseña";
  }
  else {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
      $sql = "SELECT * FROM users u INNER JOIN participants p
      ON u.participantId = p.participantId WHERE username = :username LIMIT 1";
      $statement = $connection->prepare($sql);
      $statement->bindParam(":username", $username, PDO::PARAM_STR);
      $statement->execute();


      if($statement->rowCount() === 0) {
        echo "no hay usuario " . $username;
      }
      else {
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if(password_verify($password, $row['password'])) {

          $_SESSION['username'] = $username;
          $_SESSION['nickname'] = $row['nickname'];
          $_SESSION['gender'] = $row['gender'];
          $_SESSION['loggedIn'] = true;

          $participantId = $row['participantId'];

          $_SESSION['participantId'] = $participantId;

          $sql = "SELECT name FROM roles r INNER JOIN participantRoles pr
          ON r.roleId = pr.roleId WHERE pr.participantId = :participantId;";

          $statement = $connection->prepare($sql);
          $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
          $statement->execute();

          $resultsRoles = $statement->fetchAll();

          $roleNames = array_map('getNameString', $resultsRoles); //getNameString defined in common.php

          if(in_array('administrator', $roleNames)){
            $_SESSION['isAdministrator'] = $username;
          }
          else if (in_array('coordinator', $roleNames)) {
            $_SESSION['isCoordinator'] = $username;
          }
          if(in_array('teacher', $roleNames)) {
            $_SESSION['isTeacher'] = $username;
          }

          if(in_array('technician', $roleNames)) {
            $_SESSION['isTechnician'] = $username;
          }

          $courses = array();
          $coordinatorProgramId = null;

          // if($_SESSION['isTeacher']) {
          //
          //   //load permissions
          //
          //   $sql = "SELECT * FROM courses c INNER JOIN teachers t
          //   ON c.teacherId = t.teacherId WHERE t.userId = :userId;";
          //   $statement = $connection->prepare($sql);
          //   $statement->bindParam(":userId", $_SESSION['userId'], PDO::PARAM_INT);
          //   $statement->execute();
          //
          //   $resultsCourses = $statement->fetchAll();
          //
          //   foreach($resultsCourses as $course) {
          //     array_push($courses, array('courseId' => $course['courseId'], 'courseName' => $course['name'],
          //   'startDate'=>$course['startDate'], 'endDate' => $course['endDate']));
          //   }
          //
          //
          //   //load current courses
          //
          //   // $sql = "SELECT * FROM courses c INNER JOIN teachers t ON c.teacherId = t.teacherId
          //   // WHERE t.userId = :userId
          //   // AND NOW() < ADDDATE(c.endDate, INTERVAL 1 MONTH)
          //   // AND NOW() > SUBDATE(c.startDate, INTERVAL 1 MONTH);";
          //   // $statement = $connection->prepare($sql);
          //   // $statement->bindParam(":userId", $_SESSION['userId'], PDO::PARAM_INT);
          //   // $statement->execute();
          //   //
          //   // $resultsCourses = $statement->fetchAll();
          //   //
          //   // foreach($resultsCourses as $course) {
          //   //   array_push($courses, array( 'name' => $course['name'], 'courseId' => $course['courseId']));
          //   // }
          //
          // } else if($_SESSION['isCoordinator']) {
          //     $sql = "SELECT programId, name FROM coordinators c INNER JOIN programs p
          //     ON c.programId= p.programId WHERE c.userId = :userId;";
          //     $statement = $connection->prepare($sql);
          //     $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
          //     $statement->execute();
          //
          //     $coordinator = $statement->fetch(PDO:FETCH_ASSOC);
          //
          //     $coordinatorProgramId = $coordinator['programId'];
          //     $coordinatorProgramName = $coordinator['name'];
          // }
          //
          // $_SESSION['teacherCourses'] = $courses;
          // $_SESSION['coordinatorProgramId'] = $coordinatorProgramId;
          // $_SESSION['coordinatorProgramName'] = $coordinatorProgramName;

          header("location: /index.php");
          die();
        }
        else {
          echo "you entered the wrong password"; //TRANSLATE!!!
        }
      }

    } catch (PDOException $error) {
      handleError($error);
    }
  }
}

require "templates/header.php";

 ?>
<div id="login-page">
  <form method="post" class="submit-form">
    <h2>Iniciar Sesión</h2>
    <label for="username">Usuario: </label>
    <input type="text" id="username" name="username" /><br>
    <label for="password">Contraseña: </label>
    <input type="password" id="password" name="password"/><br>
    <input class="orange-submit" type="submit" name="submit" value="Entrar">
  </form>
</div>
