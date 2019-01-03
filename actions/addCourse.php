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

  $courseSessions = array(); //we'll use this to add coursesessions later
  $courseDays = array();
  $courseDayNames = array();
  $startDay = strtolower(date('l', strtotime($new_course['startDate']))); //gets the day of the week of the start date
  $daysOfWeek = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
  $spanishDaysOfWeek = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado');

  //manually go through the days of the days of the week
  //add any days the class will fall on and also record the integer value for
  //the starting day (this may be cleaner but less concise with a case statement)
  //these integer values will be used below

  foreach($daysOfWeek as $key => $value) {
    if(isset($_POST[$value])) {
      array_push($courseDays, $key);
      array_push($courseDayNames, $spanishDaysOfWeek[$key]);
    }
    if($startDay === $value) {
      $startInt = $key;
    }
  }

  $new_course['daysOfWeek'] = implode(", ", $courseDayNames);

  $sql = makeInsertQuery($new_course, 'courses');

  try {

    $statement = $connection->prepare($sql);
    $statement->execute($new_course);
    $courseId = $connection->lastInsertId(); //for use in courseSessions Inserts

  } catch(PDOException $error) {

    handleError($error);
    header("location: ../courseList.php?courseAdded=0");

  }

  //add rows to courseSessions for each class meeting
  $startDate = new DateTime($new_course['startDate']);
  $endDate = new DateTime($new_course['endDate']);

  //add classes for first week (up until and including saturday)
  $lastInt = $startInt; // this is necessary because DateTime::add changes the underlying object
  $lastDate = clone $startDate;
  foreach($courseDays as $day) {
    if($day >= $startInt) {
      $newDate = $lastDate->add(new DateInterval('P' . ($day - $lastInt) . 'D'));
      array_push($courseSessions, $newDate);
      $lastInt = $day;
      $lastDate = clone $newDate;
    }
  }

  //now set the date to sunday. here we'll begin adding all other sessions
  $firstSunday = $lastDate->add(new DateInterval('P' . (7 - $lastInt) . 'D')); //save for later use

  $week = new DateInterval('P7D');
  foreach($courseDays as $dayNum) {
    $interval = new DateInterval('P' . $dayNum . 'D');
    for($date = (clone $firstSunday)->add($interval); $date <= $endDate; $date = $date->add($week)) {
      array_push($courseSessions, clone $date);
    }
  }

  //helper function to sort date array
  function dateOrderHelper($a, $b) {
    return strtotime($a->format('Y-m-d')) - strtotime($b->format('Y-m-d'));
  }

  usort($courseSessions, 'dateOrderHelper');

  try {
    $sql = "INSERT INTO courseSessions (courseId, sessionDate) VALUES (:courseId, :sessionDate);";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
    foreach($courseSessions as $session) {
      $dateString = $session->format('Y-m-d');
      $statement->bindParam(':sessionDate', $dateString, PDO::PARAM_STR);
      $statement->execute();
    }

    header("location: ../coursePage.php?courseId=" . $courseId);
    die();
  } catch (PDOException $error) {
    handleError($error);
    die();
  }
}

else {
  header('location: courseList.php');
  die();
}
