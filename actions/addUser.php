<?php

require "../common.php";

//change to only work for admins
if(isset($_POST['submit']) and hasPermission()) {

  $username = $_POST['username'];

  $new_user = array(
    'username' => $_POST['username'],
    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT)
  );

  $sql = makeInsertQuery($new_user, 'users');

  try {

    $connection->beginTransaction();

    $statement = $connection->prepare($sql);
    $statement->execute($new_user);
    $participantId = $connection->lastInsertId();

    $roles = array();

    if($_POST['isAdministrator']) {

      $new_administrator = array(
        'username' => $username,
        'participantId' => $participantId
      );

      $sql = makeInsertQuery($new_administrator, 'administrators');
      $statement = $connection->prepare($sql);
      $statement->execute($new_administrator);

      array_push($roles, 'administrator');
    }

    else if($_POST['isCoordinator']) {

      $new_coordinator = array(
        'username' =>   $userId,
        'participantId' => $participantId
      );

      $sql = makeInsertQuery($new_coordinator, 'coordinators');
      $statement = $connection->prepare($sql);
      $statement->execute($new_coordinator);

      array_push($roles, 'coordinator');

      $programs = array();

      //come back to add coordinator programs
    }

    if($new_user['isTeacher']) {

      $new_teacher = array(
        'username' =>   $new_user['username'],
        'participantId' => $participantId
      );

      $sql = makeInsertQuery($new_teacher, 'teachers');
      $statement = $connection->prepare($sql);
      $statement->execute($new_teacher);

      array_push($roles, 'teacher');
    }

    $sql = "INSERT INTO participantRoles (particpantId, roleId)
    SELECT ':participantId', r.roleId
    FROM roles AS r
    WHERE r.name = :name;";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);

    foreach($roles as $role) {
      $statement->bindParam(':name', $role, PDO::PARAM_STR);
      $statement->execute();
    }

    $connection->commit();
    header("location: ../admin/registration.php?userAdded=1");

  } catch(PDOException $error) {

    $connection->rollBack();
    handleError($error);
    header("location: ../admin/registration.php?userAdded=0");

  }

  die();
}
else
{
  header('location: index.php');
  die();
}
