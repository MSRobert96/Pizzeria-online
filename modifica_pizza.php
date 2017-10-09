<?php
	require "libreria.php";
	//se non si è entrati com amministratore, reindirizza al listino
	if (!controllo_admin()){
		header('Location:listino.php');
	}
	
	try {
		$dbconn = connessione();
	} catch (PDOException $e) { echo $e->getMessage(); }
	
	//prepara le query per estrarre la pizza e i suoi ingredienti
	$pizza = $dbconn->prepare('SELECT id, nome, prezzo FROM pizze WHERE id = ?');
	$ingredienti = $dbconn->prepare('SELECT id, ingrediente FROM ingredienti_pizza(?)');
	$ingredienti_aggiungibili = $dbconn->prepare('SELECT id, ingrediente FROM ingredienti WHERE id NOT IN (SELECT id FROM ingredienti_pizza(?)) ORDER BY ingrediente ASC');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Pizzeria</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="style.css" rel="stylesheet">
	</head>
	<body>
		<nav>
			<h1><?php
				//se si vuole modificare una pizza, prendine i dati da db
				if(isset($_GET['pizza'])){
					echo 'Modifica pizza ' . $r['nome'];
					$pizza->execute(array($_GET['pizza']));
					$r=$pizza->fetch();
				} else {
					echo 'Crea una nuova pizza per il listino';
				}
			?></h1>
		</nav>
		<main>
			<h3>Pizza</h3>
			<form action="servizio_pizze.php" method="get">
				<!--input nascosto che determina l'azione da eseguire al salvataggio: modifica o creazione-->
				<input type="hidden" name="pizza" value="<?=$_GET['pizza']?>">
				<table>
					<tr>
						<td><strong>Nome</strong></td>
						<td><input type="text" name="nome" value="<?=$r['nome'];?>" required></td>
					</tr>
					<tr>
						<td><strong>Prezzo</strong></td>
						<td><input type="text" name="prezzo" value="<?=$r['prezzo'];?>" required></td>
					</tr>
				</table>

				<?php if($_GET['errore'] == 'dati') { ?>
					<p class="warning">* nome o prezzo non validi!</p>		
				<?php } ?>

				<input class="conferma" type="submit" value="Salva nome e prezzo" name="comando">
			</form>

			<?php if(isset($_GET['pizza'])){ ?>
				<p><a class="warning" href="servizio_pizze.php?comando=elimina_pizza&pizza=<?=$_GET['pizza']?>">Elimina pizza</a></p>
				<hr>

				<h3>Ingredienti</h3>
				<form action="servizio_pizze.php" method="get">
					<input type="hidden" name="pizza" value="<?=$r['id'];?>">
					<table>
						<?php
							/*elenco degli ingredienti della pizza da modificare*/
							$ingredienti->execute(array($r['id']));
							foreach($ingredienti as $i){
								echo "<tr>";
								echo "<td>" . $i['ingrediente'] . "</td>";
								echo "<td><a href=\"servizio_pizze.php?comando=togli_ingrediente&pizza=" . $r['id'] . "&ingrediente=" . $i['id']."\">elimina</a></td>";
								echo "</tr>";
							}
						?>
					</table>

					<!--tendina per selezionare un ingrediente da aggiungere-->
					<select name="ingrediente">
						<?php
							$ingredienti_aggiungibili->execute(array($r['id']));
							foreach($ingredienti_aggiungibili as $s){
								echo "<option value='" . $s['id'] . "'>" . $s['ingrediente'] . "</option>";
							}
						?>
					</select>
						
					<!-- eventuale messaggio di errore in salvataggio -->
					<?php if($_GET['errore'] == 'ingrediente') { ?>
						<p class="warning">* ingrediente non presente in magazzino!</p>
					<?php } ?>
					
					<input type="submit" value="Aggiungi ingrediente" name="comando">
				</form>
			<?php } ?>

			<hr>

			<p><a href="listino.php">Torna al listino</a></p>
		</main>
	</body>
</html>