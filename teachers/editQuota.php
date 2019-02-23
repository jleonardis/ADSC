<?php

require "../common.php";

include "../templates/header.php";

checkLogIn();

if(!isset($_GET['courseId']) || !isset($_GET['quotaId'])) {
  echo "No hay cuota seleccionada";
  die();
}

$courseId = $_GET['courseId'];
$quotaId = $_GET['quotaId'];

if(!hasPermission($courseId)) {
  echo $invalidPermissionMessage;
  die();
}

try
{
  $sql = "SELECT name, amount, description, pq.maxPayment as maxPayment
  FROM quotas q
  LEFT JOIN
    (
      SELECT MAX(amountPaid) as maxPayment
      FROM
      (
        SELECT SUM(amountPaid) as amountPaid
        FROM participantQuotas
        WHERE quotaId = :quotaId
        GROUP BY quotaId, participantId
      ) sums
    ) pq
  ON 1 = 1
  WHERE q.quotaId = :quotaId";

  $statement = $connection->prepare($sql);
  $statement->bindParam(':quotaId', $quotaId, PDO::PARAM_INT);
  $statement->execute();

  if($statement->rowCount() === 0) {
    echo "esa cuota ya no existe";
    die();
  }

  $quota = $statement->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $error) {
  handleError($error);
  die();
}
 ?>
<main>
  <div class="form-parent">
    <form class="submit-form" method="post" action="/actions/updateQuota.php?quotaId=<?php echo escape($quotaId); ?>&courseId=<?php echo escape($courseId); ?>">
      <h2>Editar Cuota</h2>
      <label for="name">Nombre: </label>
      <input type="text" name="name" id="name"
      value="<?php echo escape($quota['name']); ?>"><br>
      <label for="description">Descripción: </label>
      <input type="text" name="description" id="description"
      value="<?php echo escape($quota['description']);?>"><br>
      <label for="description">Monto: </label>
      <input type="number" name="amount" id="description" min=<?php echo escape($quota['maxPayment']); ?>
      value="<?php echo escape($quota['amount']);?>"><br>
      <input type="submit" value="Actualizar" class="orange-submit">
      <?php if($quota['maxPayment'] == 0) { ?>
      <button id="removeQuota" type="button" class="orange-submit delete-button"
      data-href="/actions/removeQuota.php?quotaId=<?php echo escape($quotaId) ?>&courseId=<?php echo escape($courseId); ?>">
      Eliminar</button>
    <?php } ?>
    </form>
  </div>
</main>

<?php include "../templates/sidebar.php"; ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/deleteButton.js"></script>
<?php include "../templates/footer.php"; ?>
