<div id="sidebar">
  <div id="navigate">
    <?php if (isAdministrator() || isCoordinator()) { ?>
    <h2>Ir a</h2>
    <a href="/courseList.php"><button class="orange-submit">Listado de Cursos</button></a>
    <a href="/participantList.php"><button class="orange-submit">Listado de Participantes</button></a>
  </div>
<?php } ?>
  <?php if(isTeacher() && $_SERVER['PHP_SELF'] !== '/index.php') { ?>
    <div id="teacher-courses">
      <h2>Mis Cursos</h2>
      <ul>
      <?php
      $sql = "SELECT courseId, name FROM courses WHERE teacherId = :participantId
      AND alive = 1;";
      $statement = $connection->prepare($sql);
      $statement->bindParam(':participantId', $_SESSION['participantId'], PDO::PARAM_INT);
      $statement->execute();

      if($statement->rowCount() !== 0) {
        $courses = $statement->fetchAll();
        foreach($courses as $course) { ?>
          <li><a href="/coursePage.php?courseId=<?php echo escape($course['courseId']); ?>"><strong><?php echo escape($course['name']); ?><strong></a></li>
        <?php }
        }
      else {?>
        <p>Por el momento, no estas asignad<?php echo escape(getGenderEnding($_SESSION['gender'])); ?> a ningun curso.</p>
      <?php } ?>
    </ul>
  </div>
<?php } ?>
  <?php if (isAdministrator() || isCoordinator()) { ?>
    <div id="admin-actions">
      <h2>Acciones de Administrador</h2>
      <a href="/admin/participantRegistration.php"><button class="orange-submit">Agregar Participante</button></a>
      <?php if (isAdministrator()) { ?>
      <a href="/admin/createProgram.php"><button class="orange-submit">Crear Programa</button></a>
      <?php } ?>
      <a href="/admin/createCourse.php"><button class="orange-submit">Crear Curso</button></a>
    </div>
  <?php } ?>
  <div id="account">
    <h2>Administración de Cuenta</h2>
    <a href="/participantPage.php?participantId=<?php echo escape($_SESSION['participantId']); ?>"><button class="orange-submit">Ver Perfil</button></a>
    <a href="/user/updatePassword.php?participantId=<?php echo escape($_SESSION['participantId']); ?>"><button class="orange-submit">Cambiar Contraseña</button></a>
  </div>
  </div>
