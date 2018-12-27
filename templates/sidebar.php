<div id="sidebar">
  <div id="navigate">
    <h2>Ir a</h2>
    <a href="/courseList.php"><button class="orange-submit">Listado de Cursos</button></a>
    <a href="/participantList.php"><button class="orange-submit">Listado de Participantes</button></a>
  </div>
  <?php if (isAdministrator()) { ?>
    <div id="admin-actions">
      <h2>Acciones de Administrador</h2>
      <a href="/admin/participantRegistration.php"><button class="orange-submit">Agregar Participante</button></a>
      <a href="/admin/registration.php"><button class="orange-submit">Crear Cuenta</button></a>
      <a href="/admin/createProgram.php"><button class="orange-submit">Crear Programa</button></a>
    </div>
  <?php } ?>
  </div>
