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

  $sql = "SELECT par.participantId as participantId, firstName, lastName, description,
  amount, name, q.quotaId as quotaId, IFNULL(pq.amountPaid, 0) as amountPaid
    FROM
      (
        SELECT name, description, amount, quotaId, courseId
        FROM quotas
        WHERE courseId = :courseId
          AND alive
      ) q
    LEFT JOIN
      (
        SELECT participantId, firstName, lastName, courseId
        FROM currentParticipantCourses_View
        WHERE courseId = :courseId
      ) par
    ON par.courseId = q.courseId
    LEFT JOIN
      (
        SELECT SUM(amountPaid) as amountPaid, quotaId, participantId
        FROM participantQuotas
        GROUP BY quotaId, participantId
      ) pq
    ON pq.quotaId = q.quotaId AND pq.participantId = par.participantId;";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $hasQuotas = $statement->rowCount() != 0;
  $quotasTable = array();
  $quotaNames = array();
  $quotaInfos = array();

  if($hasQuotas) {

    $results = $statement->fetchAll();
    $hasParticipants = !is_null($results[0]['participantId']);

    $amountsDue = array(); //this is passed to js at end of page

    foreach($results as $row) {
      $participantId = $row['participantId'];
      $participantName = $row['firstName'] . " " . $row['lastName'];
      if(!isset($quotasTable[$participantId])) {
        $quotasTable[$participantId] = array();
        $quotasTable[$participantId]['participantName'] = $participantName;
        $quotasTable[$participantId]['quotas'] = array();
        $amountsDue[$participantId] = array();
      }
      $name = $row['name'];
      if(array_search($name, $quotaNames) === false) {
        array_push($quotaInfos, array('name' => $name,
          'description' => $row['description'], 'amount' => $row['amount'], 'quotaId' => $row['quotaId']));
        array_push($quotaNames, $name);
      }
      $quotasTable[$participantId]['quotas'][$name] = array();
      $quotasTable[$participantId]['quotas'][$name]['amountPaid'] = $row['amountPaid'];

      $amountsDue[$participantId][$row['quotaId']] = $row['amount'] - $row['amountPaid'];
    }
  }

} catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

?>

<main>
  <div class="back-button-icon"><a href="/coursePage.php?courseId=<?php echo escape($courseId);?>"><img src="/images/back-icon.png"></a></div>
  <h1>Pagos de Cuotas</h1>
  <?php if($hasQuotas) { ?>
  <div class="scrollDiv assignments">
      <div class="scrollTableWrapper">
        <table class="scrollTable">
          <thead>
            <tr>
              <th class="fixed-column"> </th>
              <?php foreach($quotaInfos as $name) { ?>
                <th<?php if ($name['description']) {?> title="<?php echo escape($name['description']); ?>"
              <?php } ?>><span><?php echo escape($name['name']); ?></span><br>
                <span>Monto: <?php echo escape($name['amount']);?></span>
                <a href="/teachers/editQuota.php?quotaId=<?php echo escape($name['quotaId']); ?>&courseId=<?php echo escape($courseId); ?>">
                  <button style="color: red;">Editar Cuota</button></a></th>
              <?php } ?>
            </tr>
          </thead>
          <?php if($hasParticipants) { ?>
          <tbody>
            <?php foreach($quotasTable as $participantId => $participant) { ?>
              <tr>
                <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                <?php foreach($quotaInfos as $quotaName) {
                  $quotaInfo = $participant['quotas'][$quotaName['name']];?>
                  <td <?php if ($quotaInfo['amountPaid'] === $quotaName['amount']) {?>
                    style="background-color: #3c9935; color: white"
                  <?php } ?>>
                    Q<?php echo escape($quotaInfo['amountPaid']);?></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        <?php } ?>
        </table>
      </div>
      <form method="post" class="submit-form" action="/actions/makeQuotaPayment.php?courseId=<?php echo escape($courseId); ?>">
        <h2>Realizar Pago</h2>
        <label for="quotaId">Cuota: </label>
        <select name="quotaId" id="quotaId" required>
          <option value="">--Elige Cuota--</option>
          <?php foreach($quotaInfos as $quota) { ?>
            <option value="<?php echo escape($quota['quotaId']); ?>"><?php echo escape($quota['name']); ?></option>
          <?php } ?>
        </select><br>
        <label for="participantId">Participante: </label>
        <select name="participantId" id="participantId" required>
          <option value="">--Elige Participante--</option>
          <?php foreach($quotasTable as $participantId => $participant) { ?>
            <option value="<?php echo escape($participantId); ?>"><?php echo escape($participant['participantName']);?></option>
          <?php } ?>
        </select><br>
        <label for="amountToPay">Monto: </label>
        <input type="number" name="amountToPay" id="amountToPay" required><br>
        <label for="quotaDate">Fecha: </label>
        <input type="date" name="quotaDate" id="quotaDate" required><br>
        <?php if(hasAdminPermission()) { ?>
          <label for="descuento">Es descuento?: </label>
          <input type="checkbox" value="discount" name="discount" id="discount"><br>
        <?php } ?>
        <label for="receiptNumber">Numero de Recibo: </label>
        <input type="text" name="receiptNumber" id="receiptNumber" class="short-input">
        <input type="submit" class="orange-submit" value="Realizar">
      </form>
  </div>
<?php }
else { ?>
  <p>Este curso no tiene ninguna cuota.</p>
<?php } ?>
  <div id="addQuota">
    <h2>Agregar Cuota</h2>
    <form method="post" action="/actions/addQuota.php?courseId=<?php echo escape($courseId);?>" class="submit-form">
      <label for="name">Nombre: </label>
      <input type="text" id="name" name="name"><br>
      <label for="amount">Monto: </label>
      <input type="number" id="amount" name="amount" required><br>
      <label for="description">Descripción: </label>
      <textarea id="description" name="description" maxlength="255"></textarea><br>
      <input type="submit" class="orange-submit" value="agregar">
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php"; ?>
<script>var amountsDue = <?php echo json_encode($amountsDue); ?></script>
<script src="/js/quotasPage.js"></script>
<?php include "../templates/footer.php"; ?>
