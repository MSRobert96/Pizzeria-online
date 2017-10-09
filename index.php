<?php
	require "libreria.php";
	if(controllo_accesso())
		header('Location:home.php');
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
			<h1>Pizzeria UniVe</h1>
		</nav>
		<main>
			<h3>Effettua l'accesso</h3>
			<form action="verifica_login.php" method="post">
				<table>
					<tr><td><strong>Username</strong></td><td><input type="text" name="username" required></td></tr>
					<tr><td><strong>Password</strong></td><td><input type="password" name="password" required></td></tr>
				</table>
				<?php if ($_GET['errore']=='si') { ?>
					<br/><strong class="warning">* credenziali errate!</strong><br/>
				<?php } ?>
				<input class="conferma" type="submit" value="Login">
			</form>
			<hr>
			<h3>Servizi disponibili senza login:</h3>
			<ul>
				<li><a href="registrazione.php">Registra un nuovo account</a></li>
				<li><a href="listino.php">Visualizza il nostro listino</a></li>
				<li><a href="lista_ingredienti.php">Visualizza la lista dei nostri ingredienti</a></li>
			</ul>
		</main>
	</body>
</html>