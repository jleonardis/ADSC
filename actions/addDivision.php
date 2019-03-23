<?php

require "../common.php";

if(isset($_POST['submit']) && hasAdminPermission()) {


  $new_division = array(
    'name' => $_POST['name'],
    'programId' => $_POST['program'],
    'description' => $_POST['description'],
  );

  $sql = makeInsertQuery($new_division, 'divisions');

  try {

    $connection->beginTransaction();

    $statement = $connection->prepare($sql);
    $statement->execute($new_division);

    $connection->commit();
    header("location: ../courseList.php");
    die();

  } catch(PDOException $error) {
    $connection->rollBack();
    handleError($error);
    die();
  }
}
