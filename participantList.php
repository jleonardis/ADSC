<?php
require "common.php";

checkLogIn();

require "templates/header.php";

$sql = "SELECT * FROM participants;";
$statement = $connection->prepare($sql);
$statement->execute();

$resultsAllParticipants = $statement->fetchAll();

?>

<h2>Listado de Participantes</h2>
<div id="participantList">
<input class="orange-search" type="text" id="searchBox">
<button class="orange-submit" id="search">Buscar</button>
 <table id="participantTable">
     <thead>
       <tr class="table-head">
         <th>Nombre</th>
         <th>Apodo</th>
				 <th>Genero</th>
         <th>Edad</th>
				 <th>Email</th>
       </tr>
     </thead>
     <tbody>
       <?php foreach($resultsAllParticipants as $participant) {?>
         <tr class="table-row participant-row" data-href="participantPage.php?participantId=<?php echo $participant["participantId"];?>">
           <td class="table-cell"><?php echo escape($participant['firstName'] . " " . $participant['lastName']);?></td>
           <td class="table-cell"><?php echo escape($participant['nickName']);?></td>
					 <td class="table-cell"><?php echo escape($participant['gender']);?></td>
					 <td class="table-cell">20</td>
					 <td class="table-cell"><?php echo escape($participant['email']);?></td>
         </tr>
       <?php } ?>
     </tbody>
   </table>
</div>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/participantSearch.js"></script>


<?php require "templates/footer.php"; ?>
