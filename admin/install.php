<?php

require "../common.php";

try {
  $sql = file_get_contents("../data/init.sql");
  $statement = $connection->prepare($sql);
  $statement->execute();

  $admin = array(
    'firstName' => 'admin',
    'lastName' => '',
    'nickname' => 'admin',
    'gender' => 'F',
    'dob' => '2019-01-01',
    'dpi' => 0
  );

  $sql = makeInsertQuery($admin, 'participants');

  $statement = $connection->prepare($sql);
  $statement->execute($admin);

  $new_user = array(
    'username' => 'admin',
    'password' => password_hash('avocado', PASSWORD_DEFAULT),
    'participantId' => 1
  );

  $sql = makeInsertQuery($new_user, 'users');

  $statement = $connection->prepare($sql);
  $statement->execute($new_user);

  $sql = "INSERT INTO participantRoles (participantId, roleId) VALUES (1, 1);";

  $statement = $connection->prepare($sql);
  $statement->execute();

  echo "Database and table users created successfully.";

} catch(PDOException $error) {
  handleError($error);
}
