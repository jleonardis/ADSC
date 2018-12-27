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

function connectToDatabase($dsn, $username, $password, $options) {

  try {

    return new PDO($dsn, $username, $password, $options);

  } catch(PDOException $error) {

    handleError($error);

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

  emailOwner();
  echo $error->getMessage();

}

//to be implemented
function emailOwner(){}

function hasPermission($courseId = 0) {
  //as of now, admin can do anything. we could adjust this in the future
  if(isAdministrator()) {
    return true;
  }
  if($courseId) {
    $permissions = $_SESSION['permissions'];
    foreach($permissions as $perm) {
      if($perm == $courseId) {
        return true;
      }
    }
  }
  return false;
}

function isAdministrator() {
  if(!isset($_SESSION['isAdministrator']) || !$_SESSION['isAdministrator']) {
    return false;
  }
  return true;
}

function logout() {
  $_SESSION = array();
  session_destroy();
}

function displayActionStatus($getHeaderTag, $successMessage) {
  if($_GET[$getHeaderTag] == 1) {
    ?><p class="action-status action-success"> <?php echo $successMessage; ?></p><?php
  }
  else {
    ?><p class="action-status action-failure">Algo falló. Acción no cumplido.</p><?php
  }
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
