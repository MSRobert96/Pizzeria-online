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
	$ingrediente = $dbconn->prepare('SELECT id, ingrediente, quantita FROM ingredienti WHERE id = ?');
	$pizze_filtrate = $dbconn->prepare('SELECT id, nome, prezzo FROM pizze_con_ingredienti(?) ORDER BY nome ASC');
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
				//se si vuole modificare un ingrediente, prendine i dati da db
				if(!empty($_GET['ingrediente'])){
					$ingrediente->execute(array($_GET['ingrediente']));
					$r=$ingrediente->fetch();
					echo 'Modifica ingrediente ' . $r['ingrediente'];
				}
				else
					echo 'Aggiungi un nuovo ingrediente tra quelli disponibili';
			?></h1>
		</nav>
		<main>
			<h3>Ingrediente</h3>
			<form action="servizio_ingredienti.php" method="get">
				<!--input nascosto che determina l'azione da eseguire al salvataggio: modifica o creazione-->
				<input type="hidden" name="ingrediente" value="<?=$_GET['ingrediente']?>">
				<table>
					<tr>
						<td><strong>Ingrediente</strong></td>
						<td><input type="text" name="nome" placeholder="nome ingrediente" <?='value="'.$r['ingrediente'].'"';?> required></td>
					</tr>
					<tr>
						<td><strong>Quantita</strong></td>
						<td><input type="number" name="quantita" <?='value="'.$r['quantita'].'"';?> min="0" required></td>
					</tr>
				</table>

				<?php if($_GET['errore'] == 'dati') { ?>
					<p class="warning">* il nome dell\'ingrediente non è valido o la quantità inserita non è permessa!</p>
				<?php } ?>

				<input class="conferma" type="submit" value="Salva ingrediente" name="comando">
			</form>

			<?php if(!empty($_GET['ingrediente'])){ ?>
				<p><a class="warning" href="servizio_ingredienti.php?comando=elimina_ingrediente&ingrediente=<?=$_GET['ingrediente']?>">Elimina ingrediente</a></p>
				<p>Attenzione! Eliminando l'ingrediente, esso verrà rimosso da tutte le pizze!</p>
				<hr>
				<h3>Pizze con questo ingrediente (seleziona per modificare)</h3>
				<ul>
					<?php
						/*elenco delle pizze con l'ingrediente*/
						$pizze_filtrate->execute(array("{" . $_GET['ingrediente'] ."}"));
						foreach($pizze_filtrate as $i){
							echo "<li><a href=\"modifica_pizza.php?pizza=" . $i['id'] . "\">" . $i['nome'] . "</a></li>";
						}
					?>
				</ul>
			<?php } ?>
			<p><a href="lista_ingredienti.php">Torna alla lista</a></p>
		</main>
	</body>
</html>