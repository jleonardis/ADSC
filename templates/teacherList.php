<?php

$sql = "SELECT * FROM teachers;";

$statement = $connection->prepare($sql);
$statement->execute();

$resultsTeachers = $statement->fetchAll();

?>

<div id="chooseTeacher">
<label for="teacher">Maestr@: </label>
<input type="text" name="teacher" id="teacher" readonly><br>
<input class="orange-search" type="text" id="searchBox">
<button class="orange-submit" id="search">Buscar</button>
<form method="post" action="actions/addTeacherToCourse.php?co">
 <input class="orange-submit" type="submit" name="submit" id="submit" value="Agregar Maestr@" hidden>
  <table id="addParticipantTable">
      <thead>
        <tr class="table-head" hidden>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Seleccionar</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($resultsTeachers as $teacher) {?>
          <tr class="table-row" hidden>
            <td class="table-cell"><?php echo escape($teacher['firstName']);?></td>
            <td class="table-cell"><?php echo escape($teacher['lastName']);?></td>
            <td class="table-cell"><button class="teacher-select" id="teacher-<?php echo escape($teacher['teacherId']);?>"><?php echo escape($teacher['firstName'] . " " . $teacher['lastName']);?></button></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
</form>
</div>
