<?php
	require "libreria.php";

	if (!controllo_accesso())
		header('Location:index.php');
	
	try {
		$dbconn = connessione();
		$ordine = $dbconn->prepare("SELECT o.id, u.id as id_utente, u.nome, u.cognome, o.giorno, o.ora, o.indirizzo, o.consegnato, o.annullato FROM ordini o INNER JOIN utenti u ON o.utente = u.id WHERE o.id = ?");
		$dettaglio = $dbconn->prepare("SELECT p.id, p.nome as pizza, o.quantita FROM ordine_pizza o INNER JOIN pizze p ON o.pizza = p.id WHERE ordine = ?");
		$prezzo = $dbconn->prepare("SELECT SUM(prezzo*quantita) as prezzo FROM ordine_pizza o INNER JOIN pizze p ON o.pizza = p.id WHERE o.ordine = ?");
	
		$pizze_ordinabili = $dbconn->query("SELECT id, nome, prezzo FROM pizze_ordinabili()");

		$giorno = date("Y-m-d");
		$ora = '20:00';

		$readonly = '';

		if(isset($_GET['ordine'])) {
			$ordine->execute(array($_GET['ordine']));
			$dettaglio->execute(array($_GET['ordine']));
			$prezzo->execute(array($_GET['ordine']));

			$o = $ordine->fetch();
			$pr = $prezzo->fetch();


			if(($o['consegnato'] || $o['annullato']) && !controllo_admin())
				$readonly = 'disabled';

			$giorno = DateTime::createFromFormat('Y-m-d', $o['giorno']);
			$giorno = $giorno->format('Y-m-d');
			$ora = DateTime::createFromFormat('H:i:s', $o['ora']);
			$ora = $ora->format("H:i");

			if(!controllo_admin() && ($o['id_utente']!=$_SESSION['login']))
				header('Location:gestione_ordini.php');
		}
		

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
			<h1><?php
				if(isset($_GET['ordine'])) {
					echo 'Modifica ordine #' . $_GET['ordine'];
				} else {
					echo 'Crea una nuova ordinazione';
				}
			?></h1>
		</nav>
		<main>
			<h3>Ordine</h3>
			<form action="servizio_ordini.php" method="get">
				<!--input nascosto che determina l'azione da eseguire al salvataggio: modifica o creazione-->
				<input type="hidden" name="ordine" value="<?=$_GET['ordine']?>">
				<table>
					<?php if(controllo_admin()){ ?>
						<tr>
							<td><strong>Utente</strong></td>
							<td><select name="utente">
								<?php foreach(lista_utenti($dbconn) as $u){
									echo '<option value="' . $u['id'] . '"';
									if (isset($_GET['ordine']) && $o['id_utente']==$u['id'])
										echo ' selected';
									echo '>' . $u['cognome'] . ' ' . $u['nome'] . ' (' . $u['username'] . ')</option>';
								} ?>
							</select></td>
						</tr>
					<?php } else {
						echo '<input type="hidden" name="utente" value="' . $_SESSION['login'] . '">';
					} ?>

					<tr>
						<td><strong>Giorno</strong></td>
						<td><input type="date" name="giorno" value="<?=$giorno?>" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" placeholder="aaaa-mm-dd" <?=$readonly?>></td>
					</tr>
					<tr>
						<td><strong>Ora</strong></td>
						<td><input type="time" name="ora" value="<?=$ora?>" required pattern="(2[0-3]|[01][0-9]):([0-5][0-9])" placeholder="hh:mm" <?=$readonly?>></td>
					</tr>
					<tr>
						<td><strong>Indirizzo</strong></td>
						<td><input type="text" name="indirizzo" value="<?=$o['indirizzo']?>" required <?=$readonly?>></td>
					</tr>

					<?php if(controllo_admin())	{ ?>
						<tr>
							<td><input type="radio" name="status" value="consegnato" <?=($o['consegnato']) ? 'checked' : ''?>>Consegnato</td>
							<td><input type="radio" name="status" value="annullato" <?=($o['annullato']) ? 'checked' : ''?>>Annullato</td>
						</tr>
					<?php } elseif($o['consegnato']){ ?>
						<tr><td colspan="2"><p class="warning">L'ordinazione è stata consegnata</p></td></tr>
					<?php } elseif($o['annullato']){ ?>
						<tr><td colspan="2"><p class="warning">L'ordinazione è stato annullato</p></td></tr>
					<?php } ?>
				</table>

				<?php if($_GET['errore'] == 'dati') { ?>
					<p class="warning">* dati non validi!</p>
				<?php } ?>

				<input class="conferma" type="submit" value="Salva ordine" name="comando" <?=$readonly?>>
			</form>

			<?php if(isset($_GET['ordine'])){ ?>
				<hr>

				<h3>Pizze</h3>
				<form action="servizio_ordini.php" method="get">
					<input type="hidden" name="ordine" value="<?=$o['id'];?>">
					<table>
						<?php foreach($dettaglio as $d){ ?>
							<tr>
								<td><?=$d['quantita']?> x <?=$d['pizza']?></td>
								<td><a href="servizio_ordini.php?ordine=<?=$o['id']?>&comando=togli_pizze&pizze=<?=$d['id']?>">elimina</a></td>
							</tr>
						<?php } ?>
					</table>

					<!--tendina per selezionare delle pizze da aggiungere-->
					<select name="pizza" <?=$readonly?>>
						<?php foreach($pizze_ordinabili as $p) { ?>
							<option value="<?=$p['id']?>"><?=$p['nome']?></option>
						<?php } ?>
					</select>
					<input type="number" name="quantita" min="1" value="1" <?=$readonly?>>
					
					<p>Alcune pizze potrebbero mancare tra le opzioni a causa di terminazione degli ingredienti necessari.</p>

					<!-- eventuale messaggio di errore in salvataggio -->
					<?php if($_GET['errore'] == 'pizza') { ?>
						<p class="warning">* pizza inesistente!</p>
					<?php } elseif($_GET['errore'] == 'ingredienti') { /*non dovrebbe mai accadere*/?>
						<p class="warning">* ci dispiace, abbiamo terminato gli ingredienti per la pizza scelta!</p>
					<?php } ?>
					
					<input type="submit" value="Aggiungi pizze" name="comando" <?=$readonly?>>
				</form>

				<h3>Totale: <?=$pr['prezzo']?>€</h3>

				<hr>

			<?php } ?>
			<p><a href="gestione_ordini.php">Torna agli ordini</a></p>
		</main>
	</body>
</html>