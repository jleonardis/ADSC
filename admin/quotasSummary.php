<?php

require "../common.php";

checkLogIn();
if(!hasAdminPermission()){
  echo $invalidPermissionMessage;
  die();
}

try {

    $sql = "SELECT program_name, p.programId, CASE WHEN course_name is NULL AND program_name IS NULL THEN 'Gran Total' WHEN course_name IS NULL THEN 'Total de Programa' ELSE course_name END as course_name,
    c.courseId, janTotal, febTotal, marTotal, aprTotal, mayTotal,
  	janTotal + febTotal + marTotal + aprTotal + mayTotal as total
  FROM
  	(
  	SELECT programId, courseList.courseId, IFNULL(SUM(jan.amountPaid), 0) as  janTotal, IFNULL(SUM(feb.amountPaid), 0) as febTotal,
  	  IFNULL(SUM(mar.amountPaid), 0) as marTotal, IFNULL(SUM(apr.amountPaid), 0) as aprTotal, IFNULL(SUM(may.amountPaid), 0) as mayTotal
  	FROM
  		(
  		SELECT p.programId, c.courseId, q.quotaId
  		FROM programs p
  		LEFT JOIN courses c ON c.programId = p.programId
          LEFT JOIN quotas q ON q.courseId = c.courseId
  		) courseList
  	LEFT JOIN
   		(
   		SELECT amountPaid, quotaId
      FROM participantQuotas
   		WHERE MONTH(paymentDate) = 1
   		) jan ON jan.quotaId = courseList.quotaId
  	LEFT JOIN
  		(
  		SELECT amountPaid, quotaId
  		FROM participantQuotas
  		WHERE MONTH(paymentDate) = 2
  		) feb ON feb.quotaId = courseList.quotaId
  	LEFT JOIN
  		(
  		SELECT amountPaid, quotaId
  		FROM participantQuotas
  		WHERE MONTH(paymentDate) = 3
  		) mar ON mar.quotaId = courseList.quotaId
  	LEFT JOIN
  		(
  		SELECT amountPaid, quotaId
  		FROM participantQuotas
  		WHERE MONTH(paymentDate) = 4
  		) apr ON apr.quotaId = courseList.quotaId
  	LEFT JOIN
  		(
  		SELECT amountPaid, quotaId
  		FROM participantQuotas
  		WHERE MONTH(paymentDate) = 5
  		) may ON may.quotaId = courseList.quotaId
  	GROUP BY courseList.programId, courseList.courseId WITH ROLLUP
      ) numbers
  LEFT JOIN
  	(
      SELECT name as program_name, programId
      FROM programs
      ) p ON p.programId = numbers.programId
  LEFT JOIN
  	(
      SELECT name as course_name, courseId
      FROM courses
      ) c ON c.courseId = numbers.courseId
  ORDER BY -p.programId desc, -c.courseId desc;
  ";

  $statement = $connection->prepare($sql);
  $statement->execute();

  $report = $statement->fetchAll();


} catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";
 ?>
 <a href="<?php echo escape($_SERVER['HTTP_REFERER']) ?>" class="back-button-icon" id="back-button"><img src="/images/back-icon.png"></a>
 <h1>Resumen de Cuotas</h1>
 <div id="quotas" class="scrollDiv">
     <div class="scrollTableWrapper">
       <table class="scrollTable">
         <thead>
           <tr>
             <th>Programa</th>
             <th>Curso</th>
             <th>Enero</th>
             <th>Febrero</th>
             <th>Marzo</th>
             <th>Abril</th>
             <th>Mayo</th>
             <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $programName = "";
          $programDisplayName = "";
          foreach($report as $row) {
            ?><tr><?php
            if($row['program_name'] !== $programName) {
              $programName = $row['program_name'];
              $programDisplayName = $programName;
            }
            else {
              $programDisplayName = "";
            }
              ?> <td><strong><?php echo escape($programDisplayName); ?></strong></td>
              <td><?php if($row['course_name'] === "Total de Programa" || $row['course_name'] === "Gran Total") {
                echo "<strong>" . escape($row['course_name']) . "</strong>";
              }  else {
                  echo escape($row['course_name']);
                } ?></td>
              <td><?php echo "Q" . escape($row['janTotal']); ?> </td>
              <td><?php echo "Q" . escape($row['febTotal']); ?> </td>
              <td><?php echo "Q" . escape($row['marTotal']); ?> </td>
              <td><?php echo "Q" . escape($row['aprTotal']); ?> </td>
              <td><?php echo "Q" . escape($row['mayTotal']); ?> </td>
              <td><?php echo "Q" . escape($row['total']); ?> </td>
        <?php } ?>
      </tr>
    </tbody>
  </table>
</div>
</div>

<?php include "../templates/footer.php" ?>
