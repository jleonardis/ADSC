<?php

require "../common.php";

if(isset($_POST['submit']) && hasPermission()) {


  $new_program = array(
    'name' => $_POST['name'],
    'description' => $_POST['description'],
    'coordinatorId' => postTernary('coordinator')
  );

  $sql = makeInsertQuery($new_program, 'programs');

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_program);
    header("location: ../courseList.php?courseAdded=1");

  } catch(PDOException $error) {

    handleError($error);
    die();
    header("location: ../courseList.php?courseAdded=0");

  }
}
