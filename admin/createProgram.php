<?php
require "../common.php";

checkLogin();
if(!hasPermission()){
  echo $invalidPermissionMessage;
  die();
}

try {

  $sql = "SELECT p.participantId as participantId, p.firstName as firstName,
  p.lastName as lastName FROM participants p INNER JOIN participantRoles pr
  ON p.participantId = pr.participantId INNER JOIN roles r
  ON pr.roleId = r.roleId WHERE r.name = 'coordinator'";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $coordinators = $statement->fetchAll();
}
catch (PDOException $error) {
  handleError($error);
  die();
}

include "../templates/header.php";

?>
<main>
<div class="form-parent">
  <form class="submit-form" method="post" action="/actions/addProgram.php">
    <h2>Agregar Programa</h2>
    <label for="name">Nombre: </label>
    <input type="text" name="name" id="name"><br>
    <label for="description">Descripción: </label>
    <textarea maxlength="255" id="description" name="description"></textarea><br>
    <label for="coordinator">Coordinador(a): </label>
    <select id="coordinator" name="coordinator">
      <option value="">--Elige Coordinador(a)--</option>
      <?php foreach($coordinators as $coordinator) { ?>
        <option value="<?php echo escape($coordinator['participantId']); ?>"><?php echo escape($coordinator['firstName'] . ' ' . $coordinator['lastName']); ?></option>
      <?php } ?>
    </select><br>
    <input type="submit" name="submit" id="submit" class="orange-submit" value="Agregar">
  </form>
</div>
</main>
<?php
include "../templates/sidebar.php";
include "../templates/footer.php"; ?>
