<?php

require "../common.php";

checkLogin();

if(isset($_GET) && isset($_GET["courseId"])) {
  $courseId = $_GET['courseId'];
} else {
  echo "no hay curso seleccionado";
}

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

try {

  $sql = "SELECT * FROM attendance a INNER JOIN courseSessions cs
    ON a.sessionId = cs.sessionId INNER JOIN participants p
    ON a.participantId = p.participantId INNER JOIN participantCourses pc
    ON p.participantId = pc.participantId WHERE cs.courseId = :courseId
    AND cs.sessionDate <= NOW() AND cs.alive;";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $results = $statement->fetchAll();

  $attendanceTable = array();
  $dates = array();
  $dateInfos = array();

  foreach($results as $row) {
    $participantId = $row['participantId'];
    $participantName = $row['firstName'] . " " . $row['lastName'];
    if(!isset($attendanceTable[$participantId])) {
      $attendanceTable[$participantId] = array();
      $attendanceTable[$participantId]['participantName'] = $participantName;
      $attendanceTable[$participantId]['dates'] = array();
    }
    $date = $row['sessionDate'];
    $sessionId = $row['sessionId'];
    $dateInfo = array('date' => $date, 'sessionId' => $sessionId);
    if(array_search($date, $dates) === false) {
      array_push($dates, $date);
      array_push($dateInfos, array('date' => $date, 'sessionId' => $sessionId));
    }
    $attendanceTable[$participantId]['dates'][$date] = array();
    $attendanceTable[$participantId]['dates'][$date]['attended'] = $row['attended'];
    $attendanceTable[$participantId]['dates'][$date]['attendanceId'] = $row['attendanceId'];
  }

  function dateStringOrderHelper($a, $b) {
    $dateA = $a['date'];
    $dateB = $b['date'];
    return strtotime($dateA) - strtotime($dateB);
  }
  usort($dateInfos, 'dateStringOrderHelper');

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
               <?php foreach($dateInfos as $date) { ?>

                 <th><?php echo escape(date('d/m', strtotime($date['date']))); ?><br>
                 <?php if(hasAdminPermission()){ ?>
                   <div class="remove-session" data-href="/actions/removeCourseSession.php?courseId=<?php echo escape($courseId);?>&sessionId=<?php echo escape($date['sessionId']);?>">X</div>
                 <?php } ?>
                 </th>
               <?php } ?>
             </tr>
           </thead>
           <tbody>
             <?php foreach($attendanceTable as $participantId => $participant) { ?>
               <tr>
                 <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                 <?php foreach($dateInfos as $date) {
                   $sessionInfo = $participant['dates'][$date['date']];?>
                   <td>
                     <select name="<?php echo escape($sessionInfo['attendanceId']); ?>">
                       <option value="absent" <?php echo ($sessionInfo['attended']==='absent')?'selected':'';?>>No</option>
                       <option value="present" <?php echo ($sessionInfo['attended']==='present')?'selected':'';?>>Sí</option>
                       <option value="excused" <?php echo ($sessionInfo['attended']==='excused')?'selected':'';?>>Permiso</option>
                     </select>
                  </td>
                 <?php } ?>
               </tr>
             <?php } ?>
           </tbody>
         </table>
       </div>
       <input type="submit" name="submit" id="submit" class="orange-submit" value="Actualizar">
     </form>
   </div>
   <div id="addCourseSession">
   <form method="post" action="/actions/addCourseSession.php?courseId=<?php echo escape($courseId);?>">
     <h3>Agregar Sesión</h3>
     <label for="date">Fecha: </label>
     <input type="date" name="date" id="date"><br>
     <input class="orange-submit" type="submit" value="Agregar" name="submit">
   </form>
 </div>
 </main>

 <?php include "../templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/attendance.js"></script>
 <?php include "../templates/footer.php"; ?>
