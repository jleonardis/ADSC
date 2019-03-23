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
      'teacherId' => $teacherId,
      'divisionId' => postTernary('divisionId')
    );

    if(isset($_POST['programId'])) {
      $courseArray['programId'] = $_POST['programId'];
    }

    $allDaysOfWeek = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

    //manually go through the days of the days of the week
    //add any days the class will fall on and also record the integer value for
    //the starting day (this may be cleaner but less concise with a case statement)
    //these integer values will be used below
    $courseDays = array();
    $courseDayNames = array();

    foreach($allDaysOfWeek as $key => $value) {
      if(isset($_POST[$value])) {
        array_push($courseDays, $key + 1);
        array_push($courseDayNames, ucfirst($value));
      }
    }
    $daysOfWeek = implode(',', $courseDayNames);

    $courseArray['daysOfWeek'] = $daysOfWeek;

    try {

      $connection->beginTransaction();

      $sql = "SELECT startDate, endDate, daysOfWeek FROM courses_View
      WHERE courseId = :courseId;";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->execute();
      //we'll use these old values to see if our new values are different
      $course = $statement->fetch(PDO::FETCH_ASSOC);

      $sql = makeUpdateQuery('courseId', $courseArray, 'courses');

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



      $oldStartDate = $course['startDate'];
      $oldEndDate = $course['endDate'];
      $oldDaysOfWeek = $course['daysOfWeek'];
      //update course sessions if they've changed the length or schedule
      $startDate = isset($_POST['startDate']) ? $_POST['startDate'] : $oldStartDate;
      $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : $oldEndDate;

      if($startDate !== $oldStartDate || $endDate !== $oldEndDate || $daysOfWeek !== $oldDaysOfWeek){

        $sql = "UPDATE courseSessions SET alive = 0
        WHERE sessionDate >= NOW() AND courseId = :courseId;";
        $statement = $connection->prepare($sql);
        $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $statement->execute();

        if(count($courseDays) === 0){
          $sql =  "INSERT INTO courseSessions (courseId, sessionDate) VALUES
                  (:courseId, :startDate)";
          if($startDate !== $endDate) {
            $sql .= ", (:courseId, :endDate)";
          }
          $sql .= " ON DUPLICATE KEY UPDATE alive = 1;";
        }

        else {

          $sql = sprintf("INSERT INTO courseSessions (courseId, sessionDate)
            SELECT :courseId, selected_date
            FROM
                (
                  SELECT ADDDATE(SUBDATE(CURDATE(), 365), thousands.i*1000 + hundreds.i*100 + tens.i*10 + ones.i)
                   AS selected_date
                  FROM (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) ones,
                        (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) tens,
                        (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) hundreds,
                        (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) thousands
                ) all_days
            WHERE selected_date BETWEEN :startDate AND :endDate
              AND DAYOFWEEK(selected_date) IN (%s)
            ON DUPLICATE KEY UPDATE alive = 1;", implode(", ", $courseDays));
          }

            $statement = $connection->prepare($sql);
            $statement->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $statement->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

            $statement->execute();

            $sql = "INSERT IGNORE INTO attendance (participantId, sessionId, attended)
            SELECT DISTINCT pc.participantId, cs.sessionId, :attended
            FROM
                (
                  SELECT sessionId
                  FROM courseSessions
                  WHERE sessionId >= LAST_INSERT_ID()
                ) cs
            CROSS JOIN
                (
                  SELECT participantId
                  FROM currentParticipantCourses_View
                  WHERE courseId = :courseId
                ) pc";
            $statement = $connection->prepare($sql);
            $attended = 'absent';
            $statement->bindParam(":attended", $attended, PDO::PARAM_STR);
            $statement->bindParam(":courseId", $courseId, PDO::PARAM_INT);

            $statement->execute();
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
