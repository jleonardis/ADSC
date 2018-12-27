<?php
require "common.php";

checkLogIn();

require "templates/header.php";

try {
  $sql = "SELECT * FROM participants ORDER BY isActive DESC;";
  $statement = $connection->prepare($sql);
  $statement->execute();

  $resultsAllParticipants = $statement->fetchAll();
} catch (PDOException $error) {
  handleError($error);
  die();
}


?>

<h2>Buscar Participantes</h2>
<div id="participantList">
<input class="orange-search" type="text" id="searchBox">
<button class="orange-submit" id="search">Buscar</button>
 <table id="participantTable">
     <thead>
       <tr class="search-head" hidden>
         <th>Nombre</th>
         <th>Apodo</th>
				 <th>Genero</th>
         <th>Edad</th>
				 <th>Email</th>
       </tr>
     </thead>
     <tbody class="search-group">
       <?php foreach($resultsAllParticipants as $participant) {?>
         <tr class="search-row participant-row" data-href="participantPage.php?participantId=<?php echo $participant["participantId"];?>" hidden>
           <td class="table-cell"><?php echo escape($participant['firstName'] . " " . $participant['lastName']);?></td>
           <td class="table-cell"><?php echo escape($participant['nickname']);?></td>
					 <td class="table-cell"><?php echo escape($participant['gender']);?></td>
					 <td class="table-cell">20</td>
					 <td class="table-cell"><?php echo escape($participant['email']);?></td>
         </tr>
       <?php } ?>
     </tbody>
   </table>
</div>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <script src="js/search.js"></script>


<?php require "templates/footer.php"; ?>
