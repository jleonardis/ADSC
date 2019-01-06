<?php

require "../common.php";

checkLogIn();

if(isset($_GET['courseId']) && hasPermission($_GET['courseId'])) {

  $courseId = $_GET['courseId'];

  try {

    $sql = "UPDATE courses SET alive = 0 WHERE courseId = :courseId;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);

    $statement->execute();

    header("location: /courseList.php");
    die();

  } catch (PDOException $error) {

    handleError($error);
    die();

  }

}

 ?>
