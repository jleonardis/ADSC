<?php

require "../common.php";

checkLogIn();
if(!hasAdminPermission()){
  echo $invalidPermissionMessage;
  die();
}

try {

    $sql = "SELECT program_name, p.programId, CASE WHEN course_name is NULL AND program_name IS NULL THEN 'Gran Total' WHEN course_name IS NULL THEN 'Total de Programa' ELSE course_name END as course_name,
    c.courseId, IFNULL(janTotal, 0) as janTotal, IFNULL(febTotal, 0) as febTotal, IFNULL(marTotal, 0) as marTotal, IFNULL(aprTotal, 0) as aprTotal,
    IFNULL(mayTotal, 0) as mayTotal, IFNULL(janTotal, 0) + IFNULL(febTotal, 0) + IFNULL(marTotal, 0) + IFNULL(aprTotal, 0) + IFNULL(mayTotal, 0) as total
  FROM
  	(
  	SELECT programId, courseList.courseId, jan.totalPaid as janTotal, feb.totalPaid as febTotal,
  	  mar.totalPaid as marTotal, apr.totalPaid as aprTotal, may.totalPaid as mayTotal
  	FROM
  		(
  		SELECT p.programId, c.courseId
  		FROM programs p
  		LEFT JOIN courses c ON c.programId = p.programId
		WHERE p.name <> 'TESTING'
        UNION DISTINCT
        SELECT 0, 0
  		) courseList
  	LEFT JOIN
   		(
		SELECT SUM(pq.amountPaid) as totalPaid, c.courseId as 'courseId'
        FROM courses c
        LEFT JOIN quotas q ON q.courseId = c.courseId
        JOIN participantQuotas pq ON pq.quotaId = q.quotaId
		WHERE MONTH(pq.paymentDate) = 1 AND (pq.discount IS NULL OR pq.discount = 0)
        GROUP BY c.courseId
		WITH ROLLUP
   		) jan ON jan.courseId = courseList.courseId
  	LEFT JOIN
  		(
  		SELECT SUM(pq.amountPaid) as totalPaid, IFNULL(c.courseId, 0) as 'courseId'
        FROM courses c
        LEFT JOIN quotas q ON q.courseId = c.courseId
        JOIN participantQuotas pq ON pq.quotaId = q.quotaId
		WHERE MONTH(pq.paymentDate) = 2 AND (pq.discount IS NULL OR pq.discount = 0)
        GROUP BY c.courseId
        WITH ROLLUP
   		) feb ON feb.courseId = courseList.courseId
  	LEFT JOIN
  		(
  		SELECT SUM(pq.amountPaid) as totalPaid, IFNULL(c.courseId, 0) as 'courseId'
        FROM courses c
        LEFT JOIN quotas q ON q.courseId = c.courseId
        JOIN participantQuotas pq ON pq.quotaId = q.quotaId
		WHERE MONTH(pq.paymentDate) = 3 AND (pq.discount IS NULL OR pq.discount = 0)
        GROUP BY c.courseId
        WITH ROLLUP
   		) mar ON mar.courseId = courseList.courseId
  	LEFT JOIN
  		(
  		SELECT SUM(pq.amountPaid) as totalPaid, IFNULL(c.courseId, 0) as 'courseId'
        FROM courses c
        LEFT JOIN quotas q ON q.courseId = c.courseId
        JOIN participantQuotas pq ON pq.quotaId = q.quotaId
		WHERE MONTH(pq.paymentDate) = 4 AND (pq.discount IS NULL OR pq.discount = 0)
        GROUP BY c.courseId
        WITH ROLLUP
   		) apr ON apr.courseId = courseList.courseId
  	LEFT JOIN
  		(
  		SELECT SUM(pq.amountPaid) as totalPaid, IFNULL(c.courseId, 0) as 'courseId'
        FROM courses c
        LEFT JOIN quotas q ON q.courseId = c.courseId
        JOIN participantQuotas pq ON pq.quotaId = q.quotaId
		WHERE MONTH(pq.paymentDate) = 5 AND (pq.discount IS NULL OR pq.discount = 0)
        GROUP BY c.courseId
        WITH ROLLUP
   		) may ON may.courseId = courseList.courseId
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
      ) c ON c.courseId = numbers.courseId ";

  if(isCoordinator()) {

    $sql .= "JOIN (
        SELECT programId
        FROM programCoordinators
        WHERE coordinatorId = :participantId
      ) pc ON pc.programId = p.programId";
    $sql .= " ORDER BY -p.programId desc, -c.courseId desc;";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':participantId', $_SESSION['participantId'], PDO::PARAM_INT);

  } else {

    $sql .= "ORDER BY -p.programId desc, -c.courseId desc;";
    $statement = $connection->prepare($sql);
  }

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
             <th>Curso<br>(Hacer clíc para editar pagos)</th>
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
              <?php if($row['course_name'] === "Total de Programa" || $row['course_name'] === "Gran Total") {
                echo "<td><strong>" . escape($row['course_name']) . "</strong></td>";
              }  else {
                  echo "<td><a href='/admin/editPayments.php?courseId=" . $row['courseId'] . "'><div>" . escape($row['course_name']) . "</div></a></td>";
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

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<?php include "../templates/footer.php" ?>
