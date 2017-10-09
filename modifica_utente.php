<?php
	require "libreria.php";

	session_start();

	if(isset($_GET['utente']))
		$utente = $_GET['utente'];
	else
		$utente = $_SESSION['login'];

	if (!controllo_accesso())
		header('Location:index.php');
	elseif (!controllo_admin() && $utente!=$_SESSION['login'])
		header('Location:modifica_utente.php');

	$dbconn = connessione();
	$user = $dbconn->prepare("SELECT * FROM utenti WHERE id = ?");
	$user->execute(array($utente));
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
			<h1>Modifica dati utente</h1>
		</nav>
		<main>
			<h3>Dati dell'account</h3>
			<form action="servizio_utenze.php" method="post">
				<input type="hidden" name="utente" value="<?=$utente?>">
				<table>
					<tr>
						<td><strong>Nome</strong></td>
						<td><input type="text" name="nome" value="<?=$u['nome']?>" required></td>
					</tr>
					<tr>
						<td><strong>Cognome</strong></td>
						<td><input type="text" name="cognome" value="<?=$u['cognome']?>" required></td>
					</tr>
					<tr>
						<td><strong>Indirizzo</strong></td>
						<td><input type="text" name="indirizzo" value="<?=$u['indirizzo']?>" required></td>
					</tr>
					<tr>
						<td><strong>Telefono</strong></td>
						<td><input type="text" name="telefono" value="<?=$u['telefono']?>"></td>
					</tr>
				</table>
				<?php if($_GET['errore'] == 'dati') { ?>
					<p class="warning">* dati non validi, devi compilare nome, cognome e indirizzo</p>
				<?php } ?>
				<input type="submit" value="Salva dati" name="comando">
			</form>
			
			<?php if (controllo_admin()) { ?>
				<p><a class="warning" href="servizio_utenze.php?comando=elimina_utente&utente=<?=$_POST['utente']?>">
					Elimina utente e tutti i suoi dati, comprese le ordinazioni
				</a></p>
			<?php } ?>
			<hr>

			<h3>Modifica la password di accesso</h3>
				
			<form action="servizio_utenze.php" method="post">
				<input type="hidden" name="utente" value="<?=$utente?>">
				<table>
					<tr>
						<td>Nuova password</td>
						<td><input type="password" name="nuova" required></td>
					</tr>
					<tr>
						<td>Ripeti nuova password</td>
						<td><input type="password" name="conferma" required></td>
					</tr>
				</table>
				<?php if($_GET['errore'] == 'password') { ?>
					<p class="warning">* le password non corrispondono</p>
				<?php } ?>
				<input type="submit" value="Cambia password" name="comando">
			</form>
			
			<hr>
			
			<?php if (controllo_admin()) { ?>
				<p><a href="gestione_utenze.php">Torna alla pagina di gestione utenze</a></p>
			<?php } else { ?>
				<p><a href="index.php">Torna alla pagina principale</a></p>
			<?php } ?>
		</main>
	</body>
</html>