<?php

require "../common.php";

//change to only work for admins
if(isset($_POST['submit']) and hasPermission()) {

  $new_user = array(
    'firstName' => $_POST['firstName'],
    'lastName' => $_POST['lastName'],
    'username' => $_POST['username'],
    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
    'gender' => $_POST['gender']
  );
  $new_user['isAdministrator'] = 0;
  $new_user['isCoordinator'] = 0;
  if(isset($_POST['isAdministrator']) && $_POST['isAdministrator'] == "administrator") {
    $new_user['isAdministrator'] = 1;
  } else if(isset($_POST['isCoordinator'])) {
    $new_user['isCoordinator'] = 1;
  }

  if(isset($_POST['isTeacher'])) {
    $new_user['isTeacher'] = 1;
  }
  else {
    $new_user['isTeacher'] = 0;
  }
  $sql = makeInsertQuery($new_user, 'users');

  try {

    $connection->beginTransaction();

    $statement = $connection->prepare($sql);
    $statement->execute($new_user);

    if($new_user['isTeacher']) {

      $userId = $connection->lastInsertId();
      
      $new_teacher = array(
        'firstName' => $new_user['firstName'],
        'lastName' => $new_user['lastName'],
        'userId' =>   $userId,
        'gender' => $_POST['gender'],
        'email' => $_POST['email']
      );

      $sql = makeInsertQuery($new_teacher, 'teachers');
      $statement = $connection->prepare($sql);
      $statement->execute($new_teacher);
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
