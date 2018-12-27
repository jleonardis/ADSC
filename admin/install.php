<?php

require "../common.php";

try {
  $sql = file_get_contents("../data/init.sql");
  $statement = $connection->prepare($sql);
  $statement->execute();
  
  $admin = array(
    'firstName' => 'admin',
    'lastName' => '',
    'username' => 'admin',
    'password' => password_hash('install', PASSWORD_DEFAULT),
    'isAdministrator' => 1,
    'isCoordinator' => 0,
    'isTeacher' => 0
  );

  $sql = makeInsertQuery($admin, 'users');

  $statement = $connection->prepare($sql);
  $statement->execute($admin);

  echo "Database and table users created successfully.";

} catch(PDOException $error) {
  echo $error-getMessage();
}
