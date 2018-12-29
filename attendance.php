<?php

require "common.php";

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
    $participantName = $row['firstName'] . " " . $row['lastName'];
    if(!isset($attendanceTable[$participantName])) {
      $attendanceTable[$participantName] = array();
    }
    $date = $row['sessionDate'];
    if(array_search($date, $dates) === false) {
      array_push($dates, $date);
    }
    $attendanceTable[$participantName][$date] = array();
    $attendanceTable[$participantName][$date]['attended'] = $row['attended'];
    $attendanceTable[$participantName][$date]['attendanceId'] = $row['attendanceId'];
  }

  function dateStringOrderHelper($a, $b) {
    return strtotime($a) - strtotime($b);
  }
  usort($dates, 'dateStringOrderHelper');

} catch (PDOException $error) {
    handleError($error);
}

include "templates/header.php";


 ?>
 <main>
   <h1>Actualizar Asistencia</h1>
   <div id="attendance">
     <form method="post" action="/actions/updateAttendance.php?courseId=<?php echo escape($courseId);?>">
       <div id="attendanceTableWrapper">
         <table id="attendanceTable">
           <thead>
             <tr class="attendance-head-row">
               <th class="fixed-column"> </th>
               <?php foreach($dates as $date) { ?>
                 <th><?php echo escape(date('d/m', strtotime($date))); ?></th>
               <?php } ?>
             </tr>
           </thead>
           <tbody>
             <?php foreach($attendanceTable as $participantName => $participant) { ?>
               <tr>
                 <th class="fixed-column"><?php echo escape($participantName); ?></th>
                 <?php foreach($participant as $sessionInfo) { ?>
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

 <?php include "templates/sidebar.php";
 include "templates/footer.php"; ?>
