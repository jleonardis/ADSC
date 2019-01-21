<?php

include "../common.php";

if(isset($_GET['participantId'])) {
  $participantId = $_GET['participantId'];

  if(isset($_POST['submit']) && hasAdminPermission()) {

    if(isset($_FILES['picture']) && isset($_FILES['picture']['error'])) {
       if($_FILES['picture']['error'] == 2) {
         echo "Esa foto es muy grande. Ve para atras y intenta con una mas pequeña.";
         die();
       }
    }

    $participantArray = array(
      'participantId' => $participantId,
      'firstName' => $_POST['firstName'],
      'lastName' => $_POST['lastName'],
      'nickname' => $_POST['nickname'],
      'dpi' => preg_replace("/[^0-9]/", "", $_POST['dpi']), //this to remove all hyphens and spaces
      'email'=> postTernary('email'),
      'phoneNumber' => (isset($_POST['phoneNumber']) && $_POST['phoneNumber']?preg_replace("/[^0-9]/", "", $_POST['phoneNumber']):null),
      'phoneNumber_2' => (isset($_POST['phoneNumber_2']) && $_POST['phoneNumber_2']?preg_replace("/[^0-9]/", "", $_POST['phoneNumber_2']):null),
      'dob' => postTernary('dob'),
      'comments' => postTernary('comments'),
      'village' => postTernary('village'),
      'languages' => ''
    );

    $languages = array();
    foreach($_POST as $key => $value) {
      if(substr($key, 0, 8) == 'language' && $value){
        array_push($languages, $value);
      }
    }
    $participantArray['languages'] = implode(", ", $languages);

    $sql = makeUpdateQuery('participantId', $participantArray, 'participants');

    try {

      $connection->beginTransaction();

      $statement = $connection->prepare($sql);
      $statement->execute($participantArray);

      if(isset($_FILES['picture'])) {
        $picture = $_FILES['picture'];
        $fileNameArray = explode('.', $picture['name']); //used in line below
        $location = 'uploads/participantPhotos/participant-' . $participantId . '.' . end($fileNameArray);
        if(move_uploaded_file($picture['tmp_name'], '../' . $location)) {
          $sql = "UPDATE participants SET imageLocation = :location WHERE participantId = :participantId";
          $statement = $connection->prepare($sql);
          $statement->bindParam(':location', $location, PDO::PARAM_STR);
          $statement->bindParam(':participantId', $participantId, PDO::PARAM_INT);
          $statement->execute();
        }
      }

      $connection->commit();
      header("location: /participantPage.php?participantId=" . $participantId . "&participantUpdated=1");
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
