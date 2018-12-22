<?php

require "common.php";

//change to only work for admins
if(isset($_POST['submit']) and checkPermission()) {

  $new_course = array(
    'name' => $_POST['courseName'],
    'programId' => $_POST['program'],
    'startDate' => $_POST['startDate'],
    'endDate' => $_POST['endDate']
  );

  $sql = sprintf("INSERT INTO %s (%s) VALUES (%s);",
        "courses",
        implode(", ", array_keys($new_course)),
        ":" . implode(", :", array_keys($new_course))
         );

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_course);
    header("location:courseList.php?courseAdded=1");

  } catch(PDOException $error) {

    handleError($error);
    header("location:courseList.php?courseAdded=0");

  }


  die();
}
