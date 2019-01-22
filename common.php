<?php

/**
 * Escape HTML for output
 *
 */
require "config.php";
session_start();

$connection = connectToDatabase($dsn, $username, $password, $options);

$invalidPermissionMessage = "no tienes permiso para usar esta pagina";

function escape($html) {
  return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function getNameString($role) { //callback function for filter to turn sql result into names array
  return $role['name'];
}

function connectToDatabase($dsn, $username, $password, $options) {

  try {

    return new PDO($dsn, $username, $password, $options);

  } catch(PDOException $error) {

    handleError($error);

  }
}

function getGenderEnding($gender) {
  switch($gender){
    case 'M':
      return 'o';
    case 'F':
      return 'a';
    default:
      return '@';
    }
}

function translateRoleName($name, $gender) {
  $ending = getGenderEnding($gender);
  switch($name) {
    case 'administrator':
      return 'Administrador' . ($ending === 'a' ? 'a' : '');

    case 'coordinator':
      return 'Coordinador' . ($ending === 'a' ? 'a' : '');

    case 'teacher':
      return 'Maestr' . $ending;

    case 'technician':
      return 'Tecnic' . $ending;

    case 'student':
      return 'Alumn' . $ending;

    default:
      return '';
  }
}

function checkLogIn() {
  //if this isn't the login page and the user isn't logged in, take them to the login page
  if(!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn'] ) {
  	header("location: /login.php");
  	die();
  }
}

function handleError($error) {
  echo "Hubo un error de sistema. Habla con un supervisor. \n";
  emailOwner();

}

//to be implemented
function emailOwner(){}

function hasAdminPermission() {
  return (isAdministrator() || isCoordinator());
}

function hasPermission($courseId = 0, $programId = 0, $participantPermissionId = 0) {
  global $connection;
  //as of now, admin can do anything. we could adjust this in the future
  $participantId = $_SESSION['participantId'];
  if(isAdministrator()) {
    $sql = "SELECT EXISTS (SELECT 1 FROM participantRoles pr INNER JOIN
    roles r ON pr.roleId = r.roleId WHERE pr.participantId = :participantId
  AND r.name = 'administrator' LIMIT 1) AS result;";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    if($result['result']) {
      return true;
    }
  }
  if($programId) {
    if(isCoordinator()) {
      $sql = "SELECT EXISTS (SELECT 1 FROM programCoordinators WHERE
        programId = :programId AND coordinatorId = :participantId LIMIT 1) AS result;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':programId', $programId, PDO::PARAM_INT);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);
      if($result['result']) {
        return true;
      }
    }
  }
  if($courseId) {
    if(isCoordinator()) {
      $sql = "SELECT EXISTS (SELECT 1 FROM programCoordinators pc
      INNER JOIN courses c ON pc.programId = c.programId
    WHERE c.courseId = :courseId AND pc.coordinatorId = :participantId LIMIT 1) AS result;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);

      if($result['result']) {
        return true;
      }
    }
    if(isTeacher()) {
      $sql = "SELECT EXISTS (SELECT 1 FROM permissions WHERE courseId = :courseId
      AND participantId = :participantId LIMIT 1) AS result;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);

      if($result['result']) {
        return true;
      }
    }
  }
  if($participantPermissionId) {

    if($participantId === $participantPermissionId) {
      return true;
    }
    if(hasAdminPermission()){
      return true;
    }
    if(isTeacher()) {
      $sql = "SELECT EXISTS (SELECT 1 FROM permissions p INNER JOIN courses c
      ON p.courseId = c.courseId INNER JOIN participantCourses pc
      ON pc.courseId = c.courseId WHERE p.participantId = :participantId
      AND pc.participantId = :participantPermissionId) AS result;";

      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->bindParam(':participantPermissionId', $participantPermissionId, PDO::PARAM_INT);

      $statement->execute();

      $result = $statement->fetch(PDO::FETCH_ASSOC);

      if($result['result']) {
        return true;
      }
    }
  }
  return false;
}

function isAdministrator() {
  if(!isset($_SESSION['isAdministrator']) || $_SESSION['isAdministrator'] !== $_SESSION['username']) {
    return false;
  }
  return true;
}

function isCoordinator() {
  if(!isset($_SESSION['isCoordinator']) || $_SESSION['isCoordinator'] !== $_SESSION['username']){
    return false;
  }
  return true;
}

function isTeacher() {
  if(!isset($_SESSION['isTeacher']) || $_SESSION['isTeacher'] !== $_SESSION['username']) {
    if(!isTechnician()){
      return false;
    }
  }
  return true;
}

function isTechnician() {
  if(!isset($_SESSION['isTechnician']) || $_SESSION['isTechnician'] !== $_SESSION['username']){
    return false;
  }
  return true;
}

function logout() {
  $_SESSION = array();
  session_destroy();
}

function displayActionStatus($getHeaderTag, $successMessage) {
  if(isset($_GET) && isset($_GET[$getHeaderTag])) {
    if($_GET[$getHeaderTag] == 1) {
    ?><p class="action-status action-success"> <?php echo $successMessage; ?></p><?php
    }
    else {
      ?><p class="action-status action-failure">Algo falló. Acción no cumplido.</p><?php
    }
  }
}

function postTernary($name) {
  return isset($_POST[$name]) && $_POST[$name] ? $_POST[$name] : null;
}

function getAge($dob) {
  $today = new DateTime();
  $years = $today->format('Y') - $dob->format('Y');
  $age = $today->format('md') >= $dob->format('md') ? $years : $years - 1;
  return $age;
}

//DATABASE FUNCTIONS
function makeInsertQuery($array, $tableName) {

  return sprintf("INSERT INTO %s (%s) VALUES (%s);",
          $tableName,
          implode(array_keys($array), ", "),
          ":" . implode(array_keys($array), ", :"));
}

function makeUpdateQuery($idName, $array, $tableName) {

  $keysArray = array_keys($array);
  $firstKey = array_shift($keysArray);
  $query = sprintf("UPDATE " . $tableName . " SET %s = :%s",
                $firstKey,
                $firstKey);
  foreach($keysArray as $key) {
    $query .= ", " . $key . "= :" . $key;
  }
  $query .= " WHERE :" . $idName . "=" . $idName . ";";

  return $query;

}

function executeSelectQuery($query, $variables) {

  $statement = $connection->prepare($query);
  $statement->execute($variables);

  return $statement;
}
