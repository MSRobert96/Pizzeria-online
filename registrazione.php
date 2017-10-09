<?php
	require "libreria.php";
	
	try {
		$dbconn = connessione();
	} catch (PDOException $e) { echo $e->getMessage(); }

	if(!controllo_admin() && controllo_accesso())
		header('Location:index.php');
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
			<h1>Crea un nuovo account</h1>
		</nav>
		<main>
			<h3>Compila i seguenti campi per creare un nuovo account:</h3>
			<?php
				if($_GET['errore'] == 'datimancanti')
					echo '<p class="warning">* dati mancanti!</p>';
				else if($_GET['errore'] == 'utenteesistente')
					echo '<p class="warning">* nome utente già in uso!</p>';
				else if($_GET['errore'] == 'password')
					echo '<p class="warning">* le password non corrispondono!</p>';
			?>
			<form action="verifica_registrazione.php" method="post">
				<table>
					<tr><td>Nome</td><td><input type="text" name="nome" required></td></tr>
					<tr><td>Cognome</td><td><input type="text" name="cognome" required></td></tr>
					<tr><td>Indirizzo</td><td><input type="text" name="indirizzo" required></td></tr>
					<tr><td>Telefono</td><td><input type="text" name="telefono"></td></tr>
					<tr><td>Username</td><td><input type="text" name="username" required></td></tr>
					<tr><td>Password</td><td><input type="password" name="password" required></td></tr>
					<tr><td>Ripeti password</td><td><input type="password" name="conferma" required></td></tr>
				</table>
				<br>
				<input class="conferma" type="submit" value="Registra">
			</form>
			<hr>
			<a href="index.php">Torna alla pagina principale</a>
		</main>
	</body>
</html>