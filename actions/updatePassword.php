<?php

require "../common.php";
checkLogin();

if(isset($_GET['participantId']) && ($_GET['participantId'] === $_SESSION['participantId'] || hasAdminPermission())){

  $participantId = $_GET['participantId'];
  $dpi = preg_replace('/[^\da-z]/i', "", $_POST['dpi']);
  $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

  try {

    $connection->beginTransaction();

    $sql = "SELECT dpi FROM participants WHERE participantId = :participantId LIMIT 1;";
    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if($dpi === $result['dpi']){
      $sql = "UPDATE users SET password = :password WHERE participantId = :participantId;";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':password', $newPassword, PDO::PARAM_STR);
      $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
      $statement->execute();
    }
    else {
      include "../templates/header.php";
      echo "Ingresaste el DPI/CUI equivocado.";
      die();
    }

    $connection->commit();

    header("location: /participantPage.php?participantId=" . $participantId);
    die();
  } catch(PDOException $error) {

    handleError($error);
    die();

  }
} else {
  echo "algo falló";
}
