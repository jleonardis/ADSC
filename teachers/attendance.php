<?php

require "../common.php";

checkLogin();
if(!hasPermission()) {
  echo $invalidPermissionMessage;
}


if(isset($_GET) && isset($_GET["courseId"])) {
  $courseId = $_GET['courseId'];
} else {
  echo "no hay curso seleccionado";
}

try {

  $sql = "SELECT * FROM attendance a INNER JOIN courseSessions cs
    ON a.sessionId = cs.sessionId INNER JOIN participants p
    ON a.participantId = p.participantId WHERE cs.courseId = :courseId;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $results = $statement->fetchAll();

  $attendanceTable = array();
  $dates = array();

  foreach($results as $row) {
    $participantId = $row['participantId'];
    $participantName = $row['firstName'] . " " . $row['lastName'];
    if(!isset($attendanceTable[$participantId])) {
      $attendanceTable[$participantId] = array();
      $attendanceTable[$participantId]['participantName'] = $participantName;
      $attendanceTable[$participantId]['dates'] = array();
    }
    $date = $row['sessionDate'];
    if(array_search($date, $dates) === false) {
      array_push($dates, $date);
    }
    $attendanceTable[$participantId]['dates'][$date] = array();
    $attendanceTable[$participantId]['dates'][$date]['attended'] = $row['attended'];
    $attendanceTable[$participantId]['dates'][$date]['attendanceId'] = $row['attendanceId'];
  }

  function dateStringOrderHelper($a, $b) {
    return strtotime($a) - strtotime($b);
  }
  usort($dates, 'dateStringOrderHelper');

} catch (PDOException $error) {
    handleError($error);
}

include "../templates/header.php";


 ?>
 <main>
   <h1>Actualizar Asistencia</h1>
   <div id="attendance" class="scrollDiv">
     <form method="post" action="/actions/updateAttendance.php?courseId=<?php echo escape($courseId);?>">
       <div id="attendanceTableWrapper" class="scrollTableWrapper">
         <table id="attendanceTable" class="scrollTable">
           <thead>
             <tr class="attendance-head-row">
               <th class="fixed-column"> </th>
               <?php foreach($dates as $date) { ?>
                 <th><?php echo escape(date('d/m', strtotime($date))); ?></th>
               <?php } ?>
             </tr>
           </thead>
           <tbody>
             <?php foreach($attendanceTable as $participantId => $participant) { ?>
               <tr>
                 <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                 <?php foreach($dates as $date) {
                   $sessionInfo = $participant['dates'][$date];?>
                   <td>
                     <input type="hidden" name="<?php echo escape($sessionInfo['attendanceId']); ?>" value="0">
                     <input type="checkbox" name="<?php echo escape($sessionInfo['attendanceId']); ?>" value ="1" <?php
                   if($sessionInfo['attended']) {
                     echo "checked";
                   } ?>></td>
                 <?php } ?>
               </tr>
             <?php } ?>
           </tbody>
         </table>
       </div>
       <input type="submit" name="submit" id="submit" class="orange-submit" value="Actualizar">
     </form>
   </div>
 </main>

 <?php include "../templates/sidebar.php";
 include "../templates/footer.php"; ?>
