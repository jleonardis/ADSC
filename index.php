<?php
require "common.php";
checkLogIn();

if(isCoordinator()) {
	header("location: /courseList.php"); //hacky fix to bring coordinator to a reasonable landing page. come back later
	die();
}
include "templates/header.php";

if(isAdministrator()) {
	try {

	  //retrieve program options to populate dropdown
	  $sql = "SELECT programId, name FROM programs";
	  $statement = $connection->prepare($sql);
	  $statement->execute();
	  $resultsPrograms = $statement->fetchAll();

	  //retrieve all courses
	  $sql = "SELECT courseId, name, startDate, endDate, programId
		FROM courses_View WHERE alive ORDER BY endDate";
	  $statement = $connection->prepare($sql);
	  $statement->execute();
	  $resultsCourses = $statement->fetchAll();
	}
	catch(PDOException $error) {
		handleError($error);
		die();
	}
}

if(isTeacher() || isTechnician()) {

		try {
			$sql = "SELECT courseId, name, startDate, endDate
			FROM courses_View c INNER JOIN participants p
			ON c.teacherId = p.participantId WHERE p.participantId = :participantId AND
			c.alive = 1;";
			$statement = $connection->prepare($sql);
			$statement->bindParam(':participantId', $_SESSION['participantId'], PDO::PARAM_INT);
			$statement->execute();

			$courses = $statement->fetchAll();
		}
		catch(PDOException $error) {
			handleError($error);
			die();
		}
		$currentTeacherCourses = array();
		$otherTeacherCourses = array();

		$now = new DateTime();
		$month = new DateInterval('P1M');

		foreach($courses as $course) {
			$startDate = new DateTime($course['startDate']);
			$endDate = new DateTime($course['endDate']);
			if($startDate->sub($month) < $now && $endDate->add($month) > $now){
				array_push($currentTeacherCourses, $course);
			} else {
				array_push($otherTeacherCourses, $course);
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
		      <tr class="course-link course-row course-row-<?php echo escape($row["programId"])?>" data-href="coursePage.php?courseId=<?php echo $row["courseId"];?>" hidden>
		        <td><?php echo escape($row["name"]); ?></td>
		        <td><?php echo escape(date('d/m/Y', strtotime($row['startDate']))); ?></td>
		        <td><?php echo escape(date('d/m/Y', strtotime($row['endDate']))); ?></td>
		      </tr>
		    <?php } ?>
		  </tbody>
		</table>

		</div>
	<?php }
	 else if (isCoordinator()) {?>
		<h1>Cursos de <?php echo escape($programName);?></h1>
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
					<tr class="course-link" data-href="coursePage.php?courseId=<?php echo $course["courseId"];?>">
						<td><?php echo escape($course['name']);?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['startDate'])));?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['endDate'])));?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php if(count($otherProgramCourses) > 0) { ?>
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
					<tr class="course-link" data-href="coursePage.php?courseId=<?php echo $course["courseId"];?>">
						<td><?php echo escape($course['name']);?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['startDate'])));?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['endDate'])));?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }
}
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
					<tr class="course-link" data-href="coursePage.php?courseId=<?php echo $course["courseId"];?>">
						<td><?php echo escape($course['name']);?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['startDate'])));?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['endDate'])));?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		<p>Por el momento, no tienes ningun curso actual.</p>
	<?php }
	if(count($otherTeacherCourses) !== 0) {?>
		<table>
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Inicio</th>
					<th>Finalización</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($otherTeacherCourses as $course) { ?>
					<tr class="course-link" data-href="coursePage.php?courseId=<?php echo $course["courseId"];?>">
						<td><?php echo escape($course['name']);?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['startDate'])));?></td>
						<td><?php echo escape(date('d/m/Y', strtotime($course['endDate'])));?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php }
}?>

</main>

<?php include "templates/sidebar.php"; ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/courseList.js"></script>

<?php include "templates/footer.php"; ?>
