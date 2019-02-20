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
      //'startDate' => $_POST['startDate'],
      //'endDate' => $_POST['endDate'],
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
      // $sql = "INSERT INTO courseSessions (courseId, sessionDate)
      //   SELECT :courseId, selected_date
      //   FROM
      //       (
      //         SELECT ADDDATE(SUBDATE(CURDATE(), 7 * 365), thousands.i*1000 + hundreds.i*100 + tens.i*10 + ones.i)
      //          AS selected_date
      //         FROM (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) ones,
      //               (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) tens,
      //               (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) hundreds,
      //               (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) thousands) t
      //         WHERE selected_date BETWEEN :startDate AND :endDate
      //           AND DAY_OF_WEEK(selected_date) IN ()
      //       ) AS course_dates
          


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
