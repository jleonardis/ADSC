<?php
require "common.php";
checkLogIn();

include "templates/header.php";

if(isAdministrator()) {
	try {

	  //retrieve program options to populate dropdown
	  $sql = "SELECT programId, name FROM programs";
	  $statement = $connection->prepare($sql);
	  $statement->execute();
	  $resultsPrograms = $statement->fetchAll();

	  //retrieve all courses
	  $sql = "SELECT * FROM courses ORDER BY endDate";
	  $statement = $connection->prepare($sql);
	  $statement->execute();
	  $resultsCourses = $statement->fetchAll();
	}
	catch(PDOException $error) {
		handleError($error);
		die();
	}
}
else if(isCoordinator()) {

	try {
		$sql = "SELECT * FROM courses c INNER JOIN coordinators coord
		ON c.programId = coord.programId INNER JOIN programs p
		ON coord.programId = p.programId WHERE coord.username = :username;";
		$statement = $connection->prepare($sql);
		$statement->bindParam(':username', $_SESSION['username'], PDO::PARAM_INT);
		$statement->execute();

		$currentProgramCourses = array();
		$otherProgramCourses = array();
		$programName = "";

		if($statement->rowCount() !== 0) {
			$courses = $statement->fetchAll();
			$programName = $courses[0]['p.name'];
			foreach($courses as $course) {
				if(strtotime('-1 month', $course['startDate']) < strtotime() &&
				strtotime('+1 month', $course['endDate']) > strtotime()) {
					array_push($currentCourses, $course);
				} else {
					array_push($otherCourses, $course);
				}
			}
		}
	}
	catch(PDOException $error) {
		handleError($error);
		die();
	}
}

if(isTeacher()) {

		try {
			$sql = "SELECT * FROM courses c INNER JOIN teachers t
			ON c.teacherId = t.teacherId WHERE t.username = :username;";
			$statement = $connection->prepare($sql);
			$statement->bindParam(':username', $_SESSION['username'], PDO::PARAM_INT);
			$statement->execute();

			$courses = $statement->fetchAll();
		}
		catch(PDOException $error) {
			handleError($error);
			die();
		}
		$currentTeacherCourses = array();
		$otherTeacherCourses = array();

		foreach($courses as $course) {
			if(strtotime('-1 month', $course['startDate']) < strtotime() &&
			strtotime('+1 month', $course['endDate']) > strtotime()) {
				array_push($currentCourses, $course);
			} else {
				array_push($otherCourses, $course);
			}
		}
	}


?>
<main>
	<?php if (isAdministrator()) { ?>
		<div id="courseList">
		<h2>Buscar Cursos</h2>

		<select class="orange-dropdown" id="programSelect">
		  <option value="">--Elige un programa--</option>
		  <?php foreach($resultsPrograms as $row) { ?>
		    <option value=<?php echo escape($row["programId"]); ?>><?php echo escape($row["name"]); ?></option>
		  <?php } ?>
		</select>

		<table id="courseTable">
		  <thead>
		    <tr>
		      <th class="table-head" hidden>Curso</th>
		      <th class="table-head" hidden>Inicio</th>
		      <th class="table-head" hidden>Finalización</th>
		    </tr>
		  </thead>
		  <tbody>

		    <?php foreach($resultsCourses as $row) { ?>
		      <tr class="course-row course-row-<?php echo escape($row["programId"])?>" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>" hidden>
		        <td><?php echo escape($row["name"]); ?></td>
		        <td><?php echo escape($row["startDate"]); ?></td>
		        <td><?php echo escape($row["endDate"]); ?></td>
		      </tr>
		    <?php } ?>
		  </tbody>
		</table>

		</div>
	<?php }
	 else if (isCoordinator()) {?>
		<h1><?php echo escape($programName);?></h1>
		<h2>Cursos Actuales</h2>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Inicio</th>
					<th>Finalización</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($currentProgramCourses as $course) { ?>
					<tr>
						<td><?php echo escape($course['courseName']);?></td>
						<td><?php echo escape($course['startDate']);?></td>
						<td><?php echo escape($course['endDate']);?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<h2>Otros Cursos</h2>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Inicio</th>
					<th>Finalización</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($otherProgramCourses as $course) { ?>
					<tr>
						<td><?php echo escape($course['courseName']);?></td>
						<td><?php echo escape($course['startDate']);?></td>
						<td><?php echo escape($course['endDate']);?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }
	if(isTeacher()) { ?>
	<h2>Cursos Actuales</h2>
	<?php if(count($currentTeacherCourses) !== 0) { ?>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Inicio</th>
					<th>Finalización</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($currentTeacherCourses as $course) { ?>
					<tr>
						<td><?php echo escape($course['courseName']);?></td>
						<td><?php echo escape($course['startDate']);?></td>
						<td><?php echo escape($course['endDate']);?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		<p>Por el momento, no tienes ningun curso actual.</p>
	<?php }
	if(count($otherCourses) !== 0) {?>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Inicio</th>
					<th>Finalización</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($otherCourses as $course) { ?>
					<tr>
						<td><?php echo escape($course['courseName']);?></td>
						<td><?php echo escape($course['startDate']);?></td>
						<td><?php echo escape($course['endDate']);?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }
}?>

</main>

<?php if(isAdministrator()){ ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courses.js"></script>
<?php } ?>

<?php include "templates/sidebar.php";
include "templates/footer.php"; ?>
