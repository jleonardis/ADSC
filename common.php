<?php

/**
 * Escape HTML for output
 *
 */
require "config.php";
session_start();
$connection = connectToDatabase($dsn, $username, $password, $options);

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
  	header("location: login.php");
  	die();
  }
}

function handleError($error) {

  emailOwner();
  echo $error->getMessage();

}

//to be implemented
function emailOwner(){}

function checkPermission() {
  if(!isset($_SESSION['isAdministrator']) || !$_SESSION['isAdministrator']) {
    return false;
  }
  return true;
}


//DATABASE FUNCTIONS
function makeInsertQuery($array, $tableName) {

  return sprintf("INSERT INTO %s (%s) VALUES (%s);",
          $tableName,
          implode(array_keys($array), ", "),
          ":" . implode(array_keys($array), ", :"));
}
