<?php

require "../common.php";

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];

  if(isset($_POST['submit'])) {

    $teacherId = postTernary("teacherId");

    $courseArray = array(
      'courseId' => $courseId,
      'name' => $_POST['courseName'],
      'description' => $_POST['description'],
      'startDate' => $_POST['startDate'],
      'endDate' => $_POST['endDate'],
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

      //add course sessions with this cool sql query
      $sql = "SELECT * FROM
        (
          SELECT ADDDATE(SUBDATE(CURDATE(), 1))
        )
(select adddate(SUBDATE(CURDATE(), 100), t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) selected_date from
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3) v
 where selected_date between '2019-02-10' and '2030-03-10'
	AND dayofweek(selected_date) IN (2, 4, 6);"


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
