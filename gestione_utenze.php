<?php
	require "libreria.php";

	if (!controllo_admin())
		header('Location:home.php');

	try {
		$dbconn = connessione();
	} catch (PDOException $e) { echo $e->getMessage(); }
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
			<h1>Gestione utenze</h1>
		</nav>
		<main>
			<p><a href="registrazione.php">Aggiungi un utente</a></p>
			<table class="elenco">
				<tr>
				<td><strong>Nome</strong></td>
				<td><strong>Cognome</strong></td>
				<td><strong>Indirizzo</strong></td>
				<td><strong>Telefono</strong></td>
				<td><strong>Username</strong></td>
				<td></td>
				</tr>
			
			<?php
				$utenti = $dbconn->query("SELECT id, nome, cognome, indirizzo, telefono, username FROM Utenti");
				foreach($utenti as $r){
			?>
				<tr>
					<td><?=$r['nome']?></td>
					<td><?=$r['cognome']?></td>
					<td><?=$r['indirizzo']?></td>
					<td><?=$r['telefono']?></td>
					<td><?=$r['username']?></td>
					<td>
						<a href="modifica_utente.php?utente=<?=$r['id']?>" title="Modifica">
							<img alt="Modifica" src="edit_icon.ico" width="20" height="20">
						</a>
					</td>
				</tr>
			<?php } ?>
			</table>
			<p><a href="index.php">Torna alla pagina iniziale</a></p>
		</main>
	</body>
</html>