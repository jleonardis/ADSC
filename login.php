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
      $sql = "SELECT * FROM users WHERE username = :username";
      $statement = $connection->prepare($sql);
      $statement->bindParam(":username", $username, PDO::PARAM_STR);
      $statement->execute();

      $result = $statement->fetchAll();

      if(count($result) == 0) {  //count isn't efficient but result is expected to be small
        echo "no hay usuario " . $username;
      }
      else {
        $row = $result[0];
        if(password_verify($password, $row['password'])) {
          $_SESSION['username'] = $username;
          $_SESSION['firstName'] = $row['firstName'];
          $_SESSION['gender'] = $row['gender'];
          $_SESSION['isAdministrator'] = $row['isAdministrator'];
          $_SESSION['isCoordinator'] = $row['isCoordinator'];
          $_SESSION['isTeacher'] = $row['isTeacher'];
          $_SESSION['userId'] = $row['userId'];
          $_SESSION['loggedIn'] = true;

          //load permissions

          $sql = "SELECT * FROM permissions WHERE userId = :userId";
          $statement = $connection->prepare($sql);
          $statement->bindParam(":userId", $_SESSION['userId'], PDO::PARAM_INT);
          $statement->execute();

          $resultsPermissions = $statement->fetchAll();
          $permissions = array();

          foreach($resultsPermissions as $permission) {
            array_push($permissions, $permission['courseId']);
          }

          $_SESSION['permissions'] = $permissions;

          $courses = array();

          if($_SESSION['isTeacher']) {

            $sql = "SELECT * FROM courses c INNER JOIN teachers t ON c.teacherId = t.teacherId
            WHERE t.userId = :userId
            AND NOW() < ADDDATE(c.endDate, INTERVAL 1 MONTH)
            AND NOW() > SUBDATE(c.startDate, INTERVAL 1 MONTH);";
            $statement = $connection->prepare($sql);
            $statement->bindParam(":userId", $_SESSION['userId'], PDO::PARAM_INT);
            $statement->execute();

            $resultsCourses = $statement->fetchAll();

            foreach($resultsCourses as $course) {
              echo "here";
              array_push($courses, array( 'name' => $course['name'], 'courseId' => $course['courseId']));
            }

          }

          $_SESSION['courses'] = $courses;

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
