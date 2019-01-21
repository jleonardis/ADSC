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

  $sql = "SELECT * FROM quotas q INNER JOIN participantQuotas pq
    ON q.quotaId = pq.quotaId INNER JOIN participants p
    ON pq.participantId = p.participantId WHERE q.courseId = :courseId";
  $statement = $connection->prepare($sql);
  $statement->bindParam(':courseId', $courseId, PDO::PARAM_INT);
  $statement->execute();

  $hasQuotas = $statement->rowCount() != 0;
  $quotasTable = array();
  $quotaNames = array();
  $quotaInfos = array();

  if($hasQuotas) {

    $results = $statement->fetchAll();

    foreach($results as $row) {
      $participantId = $row['participantId'];
      $participantName = $row['firstName'] . " " . $row['lastName'];
      if(!isset($quotasTable[$participantId])) {
        $quotasTable[$participantId] = array();
        $quotasTable[$participantId]['participantName'] = $participantName;
        $quotasTable[$participantId]['quotas'] = array();
      }
      $name = $row['name'];
      if(array_search($name, $quotaNames) === false) {
        array_push($quotaInfos, array('name' => $name, 'description' => $row['description'], 'amount' => $row['amount']));
        array_push($quotaNames, $name);
      }
      $quotasTable[$participantId]['quotas'][$name] = array();
      $quotasTable[$participantId]['quotas'][$name]['amountPaid'] = $row['amountPaid'];
      $quotasTable[$participantId]['quotas'][$name]['participantQuotaId'] = $row['participantQuotaId'];
    }
  }

} catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

?>

<main>
  <h1>Pagos de Cuotas</h1>
  <?php if($hasQuotas) { ?>
  <div class="scrollDiv assignments">
    <form method="post" action="/actions/updateQuotas.php?courseId=<?php echo escape($courseId);?>">
      <div class="scrollTableWrapper">
        <table class="scrollTable">
          <thead>
            <tr>
              <th class="fixed-column"> </th>
              <?php foreach($quotaInfos as $name) { ?>
                <th<?php if ($name['description']) {?> title="<?php echo escape($name['description']); ?>"
              <?php } ?>><span><?php echo escape($name['name']); ?></span><br>
                <span>Monto: <?php echo escape($name['amount']);?></span></th>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach($quotasTable as $participantId => $participant) { ?>
              <tr>
                <th class="fixed-column"><?php echo escape($participant['participantName']); ?></th>
                <?php foreach($quotaInfos as $quotaName) {
                  $quotaInfo = $participant['quotas'][$quotaName['name']];?>
                  <td <?php if ($quotaInfo['amountPaid'] === $quotaName['amount']) {?>
                    style="background-color: #3c9935"
                  <?php } ?>>
                    <input type="number" max="<?php echo escape($quotaName['amount']); ?>" name="<?php echo escape($quotaInfo['participantQuotaId']); ?>" value ="<?php echo escape($quotaInfo['amountPaid']);?>"></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <input type="submit" name="submit" id="submit" class="orange-submit" value="Actualizar">
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
      <input type="number" id="amount" name="amount"><br>
      <label for="description">Descripción: </label>
      <textarea id="description" name="description" maxlength="255"></textarea><br>
      <input type="submit" name="submit" id="submit" class="orange-submit">
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php";
include "../templates/footer.php"; ?>
