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
      'startDate' => $_POST['startDate'],
      'endDate' => $_POST['endDate'],
      'description' => $_POST['description'],
      'teacherId' => $teacherId
    );

    if(isset($_POST['programId'])) {
      $courseArray['programId'] = $_POST['programId'];
    }

    $sql = makeUpdateQuery('courseId', $courseArray, 'courses');

    try {

      $connection->beginTransaction();

      $statement = $connection->prepare($sql);
      $statement->execute($courseArray);

      if($teacherId) {

        $new_permission = array(
          'participantId' => $teacherId,
          'courseId' => $courseId
        );

        $sql = makeInsertQuery($new_permission, 'permissions');
        $statement = $connection->prepare($sql);
        $statement->execute($new_permission);

        if(isset($_GET['currentTeacherId']) && $_GET['currentTeacherId']) {
          //remove old teacher's permission
          $currentTeacherId = $_GET['currentTeacherId'];
          $sql = "DELETE FROM permissions WHERE courseId = :courseId
          AND participantId = :participantId;";
          $statement = $connection->prepare($sql);
          $statement->bindParam(':courseId', $courseId);
          $statement->bindParam(':participantId', $currentTeacherId, PDO::PARAM_INT);
          $statement->execute();
        }
      }

      $connection->commit();
      header("location: ../coursePage.php?courseId=" . $courseId . "&courseUpdated=1");
      die();
    }
    catch (PDOException $error){
      $connection->rollBack();
      handleError($error);
      die();
    }
  }
}
 ?>
