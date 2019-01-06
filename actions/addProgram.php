<?php

require "../common.php";

if(isset($_POST['submit']) && hasPermission()) {


  $new_program = array(
    'name' => $_POST['name'],
    'description' => $_POST['description'],
  );

  $sql = makeInsertQuery($new_program, 'programs');

  try {

    $connection->beginTransaction();

    $statement = $connection->prepare($sql);
    $statement->execute($new_program);

    $coordinatorId = postTernary('coordinator');
    if($coordinatorId) {
      $programId = $connection->lastInsertId();
      $new_program_coordinator = array(
        'programId' => $programId,
        'coordinatorId' => $coordinatorId
      );

      $sql = makeInsertQuery($new_program_coordinator, 'programCoordinators');
      $statement = $connection->prepare($sql);
      $statement->execute($new_program_coordinator);
    }

    $connection->commit();
    header("location: ../courseList.php?programAdded=1");
    die();

  } catch(PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }
}
