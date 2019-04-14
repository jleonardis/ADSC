<?php

require "../common.php";

checkLogIn();

if(!(isset($_GET['participantId']) && ($_GET['participantId'] === $_SESSION['participantId'] || hasAdminPermission()))){
  echo $invalidPermissionMessage;
  die();
}
$participantId = $_GET['participantId'];
require "../templates/header.php";

 ?>

 <main>
   <div class="form-parent">
     <form class="submit-form" method="post" action="../actions/updatePassword.php?participantId=<?php echo escape($participantId) ?>">
       <h2>Cambiar Contraseña</h2>
       <label for="dpi">DPI/CUI: </label>
       <input type="text" id="dpi" name="dpi"/><br>
       <label for="password">Contraseña Nueva: </label>
       <input type="password" id="password" name="password" class="password-box"><br>
       <label for="password-repeat">Repite Contraseña:</label>
       <input type="password" id="password-repeat" class="password-box"><br>
       <span id="password-warning" style="color: red" hidden>  Contraseñas no coinciden</span><br>
       <input type="submit" class="orange-submit" name="submit" value="Cambiar">
     </form>
   </div>
 </main>

<?php require "../templates/sidebar.php"; ?>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="/js/passwordCheck.js"></script>
 <?php require "../templates/footer.php"; ?>
