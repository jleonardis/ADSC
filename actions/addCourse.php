<?php

require "../common.php";

if(isset($_POST['submit']) and hasPermission()) {

  $teacherId = null;
  foreach($_POST as $key => $value) {
    if($value == 'on') {
      $teacherId = $key;
    }
  }
  $new_course = array(
    'name' => $_POST['courseName'],
    'programId' => $_POST['program'],
    'startDate' => $_POST['startDate'],
    'endDate' => $_POST['endDate'],
    'teacherId' => $teacherId
  );

  $sql = makeInsertQuery($new_course, 'courses');

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_course);
    header("location: ../courseList.php?courseAdded=1");

  } catch(PDOException $error) {

    handleError($error);
    header("location: ../courseList.php?courseAdded=0");

  }
}

else {
  header('location: courseList.php');
  die();
}
