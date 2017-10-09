<?php
	require "libreria.php";

	if (!controllo_accesso())
		header('Location:index.php');

	$dbconn = connessione();
	$user = $dbconn->prepare("SELECT * FROM utenti WHERE id = ?");
	$user->execute(array($_SESSION['login']));
	$u = $user->fetch();
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
			<h1>Area riservata</h1>
		</nav>
		<main>
			<h3>Accedi ai servizi disponibili:</h3>
			<?php if(controllo_admin()){ ?>
				<li><a href="gestione_utenze.php">Gestione utenze</a></li>
				<li><a href="listino.php">Gestione listino</a></li>
				<li><a href="lista_ingredienti.php">Gestione ingredienti</a></li>
				<li><a href="gestione_ordini.php">Gestione ordini</a></li>
			<?php } else { ?>
				<li><a href="listino.php">Visualizza il nostro listino!</a></li>
				<li><a href="lista_ingredienti.php">Visualizza gli ingredienti della nostra pizzeria!</a></li>
				<li><a href="gestione_ordini.php">Visualizza le tue ordinazioni</a></li>
			<?php } ?>

			<hr>

			<h3>I tuoi dati:</h3>
			<table>
				<tr>
				<td><strong>Nome</strong></td>
					<td><?=$u['nome']?></td>
				</tr>
				<tr>
				<td><strong>Cognome</strong></td>
					<td><?=$u['cognome']?></td>
				</tr>
				<tr>
				<td><strong>Indirizzo</strong></td>
					<td><?=$u['indirizzo']?></td>
				</tr>
				<tr>
				<td><strong>Telefono</strong></td>
					<td><?=$u['telefono']?></td>
				</tr>
				<tr>
				<td><strong>Username</strong></td>
					<td><?=$u['username']?></td>
				</tr>
			</table>
			<br>
			<a href="modifica_utente.php">Modifica i tuoi dati</a>
			<hr>
			<a href="logout.php">LOGOUT</a>
				
		</main>
	</body>
</html>