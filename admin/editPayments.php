<?php

require "../common.php";

checkLogIn();
if(!hasAdminPermission()) {
  echo $invalidPermissionMessage;
  die();
}

if(isset($_GET['courseId'])) {
  $courseId = $_GET['courseId'];
} else {
  echo "no hay curso seleccionado";
  die();
}

try {

  $sql = "SELECT DISTINCT q.name, CONCAT_WS(' ', pc.firstName, pc.lastName) as 'participantName',
    q.amount, pq.receiptNumber, pq.amountPaid, pq.participantQuotaId, pq.paymentDate
          FROM quotas q
          JOIN participantQuotas pq
            ON pq.quotaId = q.quotaId
          JOIN currentParticipantCourses_View pc
            ON pc.participantId = pq.participantId
          WHERE q.courseId = :courseId
          ORDER BY q.name";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $payments = $statement->fetchAll();

} catch(PDOException $error) {

  handleError($error);
  die();

}

include "../templates/header.php";

?>
<div class="back-button-icon" id="back-button"><img src="/images/back-icon.png"></div>
<main>
  <div class="form-parent">
    <form id="main-form" class="submit-form" method="post" action="/actions/updatePayments.php?courseId=<?php echo escape($courseId); ?>">
      <h2>Editar Pagos</h2>
      <div class="grid-container">
        <div><strong>Nombre de Cuota</strong></div>
        <div><strong>Nombre de Participante</strong></div>
        <div><strong>Número de Recibo</strong></div>
        <div><strong>Monto de Pago</strong></div>
        <div><strong>Fecha de Pago</strong></div>
          <?php foreach($payments as $payment) { ?>
            <div><?php echo escape($payment['name']); ?></div>
            <div><?php echo escape($payment['participantName']); ?></div>
            <div><input type="text" name="receiptNumber-<?php echo escape($payment['participantQuotaId']); ?>" value="<?php echo escape($payment['receiptNumber']);?>"></div>
            <div><input type="number" name="amountPaid-<?php echo escape($payment['participantQuotaId']); ?>" value="<?php echo escape($payment['amountPaid']);?>"
              min = "0" max="<?php echo escape($payment['amount']); ?>"></div>
            <div><input type="date" name="paymentDate-<?php echo escape($payment['participantQuotaId']); ?>" value="<?php echo escape($payment['paymentDate']); ?>"></div>
          <?php } ?>
        </div>
      <input type="submit" class="orange-submit" value="Guardar Cambios">
    </div>
  </form>
  </main>

  <?php include "../templates/sidebar.php"; ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <?php include "../templates/footer.php"; ?>
