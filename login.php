<?php
require "common.php";
require "templates/header.php";

if($_POST && isset($_POST['submit'])) {
  if(empty($_POST['username'])) {
    echo "ingresa tu username";
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
        echo "no hay usuario con username " . $username;
      }
      else {
        $row = $result[0];
        if(password_verify($password, $row['password'])) {
          $_SESSION['username'] = $username;
          $_SESSION['firstName'] = $row['firstName'];
          $_SESSION['isAdministrator'] = $row['isAdministrator'];
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

          header("location: index.php");
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
 ?>

<h1>Log In</h1>

<form method="post">
  <label for="username">Username: </label>
  <input type="text" id="username" name="username" /><br>
  <label for="password">Password: </label>
  <input type="password" id="password" name="password"/><br>
  <input type="submit" name="submit" value="Submit">
</form>
