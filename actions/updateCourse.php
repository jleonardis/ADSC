<?php

include "../common.php";

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];

  if(isset($_POST['submit'])) {

    $teacherId = null;
    foreach($_POST as $key => $value) {
      if($value == 'on') {
        $teacherId = $key;
        break;
      }
    }

    $courseArray = array(
      'courseId' => $courseId,
      'name' => $_POST['courseName'],
      'programId' => $_POST['programId'],
      'startDate' => $_POST['startDate'],
      'endDate' => $_POST['endDate'],
      'teacherId' => $teacherId
    );

    $sql = makeUpdateQuery('courseId', $courseArray, 'courses');

    try {

      $statement = $connection->prepare($sql);
      $statement->execute($courseArray);

      header("location: ../coursePage.php?courseId=" . $courseId . "&courseUpdated=1");
      die();
    }
    catch (PDOException $error){
      handleError($error);
      header("location: ../admin/editCourse.php?courseId=" . $courseId . "&courseUpdated=0");
      die();
    }
  }
}
 ?>
