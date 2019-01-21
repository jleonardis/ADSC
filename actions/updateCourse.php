<?php

include "../common.php";

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];

  if(isset($_POST['submit'])) {

    $teacherId = postTernary("teacherId");

    $courseArray = array(
      'courseId' => $courseId,
      'name' => $_POST['courseName'],
      // 'startDate' => $_POST['startDate'],
      // 'endDate' => $_POST['endDate'],
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

      $currentTeacherId = isset($_GET['currentTeacherId'])?$_GET['currentTeacherId']:NULL;

      if($teacherId && $currentTeacherId && $teacherId !== $currentTeacherId) {
          //remove old teacher's permission
          $sql = "DELETE FROM permissions WHERE courseId = :courseId
          AND participantId = :participantId;";
          $statement = $connection->prepare($sql);
          $statement->bindParam(':courseId', $courseId);
          $statement->bindParam(':participantId', $currentTeacherId, PDO::PARAM_INT);
          $statement->execute();
        }
      if($teacherId && (!$currentTeacherId || $teacherId !== $currentTeacherId)) {
        $new_permission = array(
          'participantId' => $teacherId,
          'courseId' => $courseId
        );

        $sql = makeInsertQuery($new_permission, 'permissions');
        $statement = $connection->prepare($sql);
        $statement->execute($new_permission);
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
