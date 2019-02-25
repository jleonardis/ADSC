<?php

require "../common.php";

if(isset($_POST['submit']) && hasPermission(0, $_POST['program'])) {

  $teacherId = postTernary("teacherId");

  $new_course = array(
    'name' => $_POST['courseName'],
    'programId' => $_POST['program'],
    'description' => $_POST['description'],
    'startDate' => $_POST['startDate'],
    'endDate' => $_POST['endDate'],
    'teacherId' => $teacherId
  );

  $courseSessions = array(); //we'll use this to add coursesessions later
  $courseDays = array();
  $courseDayNames = array();
  $startDay = strtolower(date('l', strtotime($new_course['startDate']))); //gets the day of the week of the start date
  $daysOfWeek = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

  //manually go through the days of the days of the week
  //add any days the class will fall on and also record the integer value for
  //the starting day (this may be cleaner but less concise with a case statement)
  //these integer values will be used below

  foreach($daysOfWeek as $key => $value) {
    if(isset($_POST[$value])) {
      array_push($courseDays, $key);
      array_push($courseDayNames, $value);
    }
    if($startDay === $value) {
      $startInt = $key;
    }
  }

  $new_course['daysOfWeek'] = implode(",", $courseDayNames);

  $sql = makeInsertQuery($new_course, 'courses');

  try {

    $connection->beginTransaction();

    $statement = $connection->prepare($sql);
    $statement->execute($new_course);
    $courseId = $connection->lastInsertId(); //for use in later Inserts

    if($teacherId) {
      //update permissions table so teacher has access to course
      $new_permission = array(
        'participantId' => $teacherId,
        'courseId' => $courseId
      );
      $sql = makeInsertQuery($new_permission, 'permissions');
      $statement = $connection->prepare($sql);
      $statement->execute($new_permission);
    }

  } catch(PDOException $error) {

    $connection->rollBack();
    handleError($error);
    die();

  }

  //add rows to courseSessions for each class meeting
  // $startDate = new DateTime($new_course['startDate']);
  // $endDate = new DateTime($new_course['endDate']);
  // if(count($courseSessions) === 0) {
  //   array_push($courseSessions, $startDate);
  //   array_push($courseSessions, $endDate);
  // }
  // //add classes for first week (up until and including saturday)
  // $lastInt = $startInt; // this is necessary because DateTime::add changes the underlying object
  // $lastDate = clone $startDate;
  // foreach($courseDays as $day) {
  //   if($day >= $startInt) {
  //     $newDate = $lastDate->add(new DateInterval('P' . ($day - $lastInt) . 'D'));
  //     array_push($courseSessions, $newDate);
  //     $lastInt = $day;
  //     $lastDate = clone $newDate;
  //   }
  // }
  //
  // //now set the date to sunday. here we'll begin adding all other sessions
  // $firstSunday = $lastDate->add(new DateInterval('P' . (7 - $lastInt) . 'D')); //save for later use
  //
  // $week = new DateInterval('P7D');
  // foreach($courseDays as $dayNum) {
  //   $interval = new DateInterval('P' . $dayNum . 'D');
  //   for($date = (clone $firstSunday)->add($interval); $date <= $endDate; $date = $date->add($week)) {
  //     array_push($courseSessions, clone $date);
  //   }
  // }
  //
  // //helper function to sort date array
  // function dateOrderHelper($a, $b) {
  //   return strtotime($a->format('Y-m-d')) - strtotime($b->format('Y-m-d'));
  // }
  //
  // usort($courseSessions, 'dateOrderHelper');
  //
  // try {
  //   $sql = "INSERT INTO courseSessions (courseId, sessionDate) VALUES (:courseId, :sessionDate);";
  //
  //   $statement = $connection->prepare($sql);
  //   $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  //   foreach($courseSessions as $session) {
  //     $dateString = $session->format('Y-m-d');
  //     $statement->bindParam(':sessionDate', $dateString, PDO::PARAM_STR);
  //     $statement->execute();
  //   }

  $startDate = $_POST['startDate'];
  $endDate = $_POST['endDate'];

  if(count($courseDays) === 0){
    $sql =  "INSERT INTO courseSessions (courseId, sessionDate) VALUES
            (:courseId, :startDate)";
    if($startDate !== $endDate) {
      $sql .= ", (:courseId, :endDate);";
    }
    else {
      $sql .= ";";
    }
  }
  else {
    $shiftedCourseDays = array_map(function($elem) {
      return $elem + 1;
    }, $courseDays);

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
        AND DAYOFWEEK(selected_date) IN (%s);", implode(", ", $shiftedCourseDays));

  }

  try {
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId);
    $statement->bindParam(':startDate', $startDate);
    if(!(count($courseDays) === 0 && $startDate === $endDate)){
      $statement->bindParam(':endDate', $endDate);
    }

    $statement->execute();

    $connection->commit();
    header("location: ../coursePage.php?courseId=" . $courseId);
    die();
  } catch (PDOException $error) {
    handleError($error);
    die();
  }
}
