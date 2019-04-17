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

  $sql = "SELECT par.participantId as participantId, firstName, lastName,
      sessionDate, sessions.sessionId as sessionId, attended, attendanceId
    FROM
      (
        SELECT sessionId, sessionDate
        FROM courseSessions
        WHERE courseId = :courseId
          AND alive
      ) sessions
    LEFT JOIN
      (
        SELECT pc.participantId as participantId, firstName, lastName,
          attended, attendanceId, sessionId
        FROM currentParticipantCourses_View pc
        JOIN attendance a
          ON a.participantId = pc.participantId
        WHERE pc.courseId = :courseId
      ) par
    ON par.sessionId = sessions.sessionId;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $hasSessions = $statement->rowCount() !== 0;
  $results = $statement->fetchAll();
  $attendanceTable = array();
  $dates = array();
  $dateInfos = array();
  $hasParticipants = ($hasSessions && !is_null($results[0]['participantId'])); //will be null if course has no participants

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
   <div class="back-button-icon" id="back-button"><img src="/images/back-icon.png"></div>
   <h1>Horario y Asistencia</h1>
   <div id="attendance" class="scrollDiv">
     <form id="main-form" method="post" action="/actions/updateAttendance.php?courseId=<?php echo escape($courseId);?>">
       <div id="attendanceTableWrapper" class="scrollTableWrapper">
         <table id="attendanceTable" class="scrollTable">
           <thead>
             <tr class="attendance-head-row">
               <?php if($hasParticipants) { ?>
                  <th class="fixed-column"> </th>
                <?php } ?>
               <?php foreach($dateInfos as $date) { ?>

                 <th><?php echo escape(date('d/m', strtotime($date['date']))); ?><br>
                 <?php if(hasAdminPermission()){ ?>
                   <input type="submit" value="X" class="remove-session" title="Eliminar Sesión"
                   formaction="/actions/updateAttendance.php?courseId=<?php echo escape($courseId);?>&sessionId=<?php echo escape($date['sessionId']);?>&removeSession=1"><br>
                 <?php } ?>
                 </th>
               <?php } ?>
             </tr>
           </thead>
           <?php if($hasParticipants) { ?>
           <tbody>
             <tr>
               <th class="fixed-column">Tod@s Presente: </th>
               <?php foreach($dateInfos as $date){ ?>
                 <td><input type="checkbox" class="select-all-checkbox" data-date="<?php echo escape($date['date']); ?>"></td>
               <?php } ?>
             </tr>
             <?php foreach($attendanceTable as $participantId => $participant) { ?>
               <tr>
                 <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                 <?php foreach($dateInfos as $date) {
                   $sessionInfo = $participant['dates'][$date['date']];?>
                   <td>
                     <select class="<?php echo escape($date['date']); ?>" name="<?php echo escape($sessionInfo['attendanceId']); ?>">
                       <option value="absent" <?php echo ($sessionInfo['attended']==='absent')?'selected':'';?>>No</option>
                       <option value="present" <?php echo ($sessionInfo['attended']==='present')?'selected':'';?>>Sí</option>
                       <option value="excused" <?php echo ($sessionInfo['attended']==='excused')?'selected':'';?>>Permiso</option>
                     </select>
                  </td>
                 <?php } ?>
               </tr>
             <?php } ?>
           </tbody>
         <?php } ?>
         </table>
       </div>
       <?php if($hasParticipants) { ?>
       <input type="submit" class="orange-submit" value="Guardar Cambios">
     <?php } ?>
     </form>
   </div>
   <div id="addCourseSession">
   <form method="post" action="/actions/addCourseSession.php?courseId=<?php echo escape($courseId);?>">
     <h3>Agregar Sesión</h3>
     <label for="date">Fecha: </label>
     <input type="date" name="date" id="date"><br>
     <input class="orange-submit" type="submit" value="Agregar">
   </form>
 </div>
 </main>

 <?php include "../templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/attendance.js"></script>
 <?php include "../templates/footer.php"; ?>
