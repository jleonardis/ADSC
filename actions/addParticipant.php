<?php

require "../common.php";

//change to only work for admins
checkLogin();



if(isset($_POST['submit']) && hasPermission()) {

  if(isset($_FILES['picture']) && isset($_FILES['picture']['error'])) {
     if($_FILES['picture']['error'] == 2) {
       echo "Esa foto es muy grande. Ve para atras y intenta con una mas pequeña.";
       die();
     }
  }

  $new_participant = array(
    'firstName' => $_POST['firstName'],
    'lastName' => $_POST['lastName'],
    'nickname' => $_POST['nickname'],
    'gender' => postTernary('gender'),
    'age' => postTernary('age'),
    'dob' => postTernary('dob'),
    'village' => postTernary('village'),
    'languages' => ''
  );
  $languages = array();
  foreach($_POST as $key => $value) {
    if(substr($key, 0, 8) == 'language' && $value){
      array_push($languages, $value);
    }
  }
  $new_participant['languages'] = implode(", ", $languages);

  $sql = makeInsertQuery($new_participant, "participants");

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_participant);

    if(isset($_FILES['picture'])) {
      $picture = $_FILES['picture'];
      $fileNameArray = explode('.', $picture['name']); //used in line below
      $participantId = $connection->lastInsertId();
      $location = 'uploads/participantPhotos/participant-' . $participantId . '.' . end($fileNameArray);
      if(move_uploaded_file($picture['tmp_name'], '../' . $location)) {
        $sql = "UPDATE participants SET imageLocation = :location WHERE participantId = :participantId";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':location', $location, PDO::PARAM_STR);
        $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
        $statement->execute();
      }
    }
    if(isset($_POST['isAdministrator']) || isset($_POST['isCoordinator']) || isset($_POST['isTeacher'])) {
      $new_user = array(
        'username' => $_POST['username'],
        'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'participantId' => $participantId
      );

      $sql = makeInsertQuery($new_user, 'users');

      $statement = $connection->prepare($sql);
      $statement->execute($new_user);

      $roles = array();

      if(isset($_POST['isAdministrator'])) {
        array_push($roles, 'administrator');
      }

      else if(isset($_POST['isCoordinator'])) {
        array_push($roles, 'coordinator');

        if(isset($_POST['programs'])) {
          $sql = "INSERT INTO programCoordinators (coordinatorId, programId)
          VALUES (:coordinatorId, :programId);";

          $statement = $connection->prepare($sql);
          $statement->bindParam(':coordinatorId', $participantId, PDO::PARAM_INT);

          foreach($_POST['programs'] as $programId) {
            $statement->bindParam(':programId', $programId, PDO::PARAM_INT);
            $statement->execute();
          }
        }
      }

      if(isset($_POST['isTeacher'])) {
        array_push($roles, 'teacher');
      }

      $sql = "INSERT INTO participantRoles (participantId, roleId)
      SELECT :participantId AS participantId, r.roleId
      FROM roles AS r
      WHERE r.name = :name;"; //safe because $participantId isn't user input

      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);

      foreach($roles as $role) {
        $statement->bindParam(':name', $role, PDO::PARAM_STR);
        $statement->execute();
      }

      header("location: ../admin/registration.php?userAdded=1");
    }

    header("location: ../admin/participantRegistration.php?participantAdded=1");
    die();

  } catch(PDOException $error) {

    handleError($error);
    die();

  }
}

else {
  header('location: index.php');
  die();
}
