<div id="sidebar">
  <div id="navigate">
    <h2>Ir a</h2>
    <a href="/courseList.php"><button class="orange-submit">Listado de Cursos</button></a>
    <a href="/participantList.php"><button class="orange-submit">Listado de Participantes</button></a>
  </div>
  <?php if(isTeacher()) { ?>
    <div id="teacher-courses">
      <h2>Mis Cursos</h2>
      <ul>
      <?php
      if(count($_SESSION['courses'])) {
      foreach($_SESSION['courses'] as $course) { ?>
        <li><a href="/coursePage.php?courseId=<?php echo escape($course['courseId']); ?>"><strong><?php echo escape($course['name']); ?><strong></a></li>
      <?php }
      }
      else {?>
        <p>Por el momento, no tienes ningun curso.</p>
      <?php } ?>
    </ul>
  </div>
<?php } ?>
  <?php if (isAdministrator()) { ?>
    <div id="admin-actions">
      <h2>Acciones de Administrador</h2>
      <a href="/admin/participantRegistration.php"><button class="orange-submit">Agregar Participante</button></a>
      <a href="/admin/registration.php"><button class="orange-submit">Crear Cuenta</button></a>
      <a href="/admin/createProgram.php"><button class="orange-submit">Crear Programa</button></a>
    </div>
  <?php } ?>
  </div>
