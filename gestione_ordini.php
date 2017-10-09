<?php
	require "libreria.php";

	if (!controllo_accesso())
		header('Location:index.php');
	
	try {
		$dbconn = connessione();
		$ordini_filtrati = $dbconn->prepare("SELECT o.id, u.id as id_utente, u.nome, u.cognome, o.giorno, o.ora, o.indirizzo, o.consegnato, o.annullato FROM ordini o INNER JOIN utenti u ON o.utente = u.id WHERE u.id = ? ORDER BY giorno DESC, ora DESC, o.id DESC");
		$dettaglio = $dbconn->prepare("SELECT o.id, p.nome as pizza, o.quantita FROM ordine_pizza o INNER JOIN pizze p ON o.pizza = p.id WHERE ordine = ?");
		$prezzo = $dbconn->prepare("SELECT SUM(prezzo*quantita) as prezzo FROM ordine_pizza o INNER JOIN pizze p ON o.pizza = p.id WHERE o.ordine = ?");
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
			<h1>Gestione ordini</h1>
		</nav>
		<main>
			<p><a href="modifica_ordine.php">Crea una nuova ordinazione</a></p>
			<?php
				if (controllo_admin()) {
					$ordini = $dbconn->query("SELECT o.id, u.id as id_utente, u.nome, u.cognome, o.giorno, o.ora, o.indirizzo, o.consegnato, o.annullato FROM ordini o INNER JOIN utenti u ON o.utente = u.id ORDER BY giorno DESC, ora DESC, o.id DESC");
				} else {
					$ordini_filtrati->execute(array($_SESSION['login']));
					$ordini = $ordini_filtrati->fetchAll();
				}
				foreach($ordini as $r){
					$dettaglio->execute(array($r['id']));
					$prezzo->execute(array($r['id']));
					$p = $prezzo->fetch();
			?>
			
				<fieldset style="border-radius: 10px; background: <?php if ($r['consegnato']) echo '#ccffcc'; elseif($r['annullato']) echo '#ffcccc'; else echo '#f9f9c7';?>">
					<legend>
						<strong>#<?=$r['id'] . " - " . $r['nome'] . " " . $r['cognome']?> -</strong>
						<a href="modifica_ordine.php?ordine=<?=$r['id']?>" title="Modifica">
							<img alt="Modifica" src="edit_icon.ico" width="20" height="20">
						</a>
					</legend>
					<strong>Giorno e ora: </strong><?=$r['giorno'] . " " . $r['ora']?><br>
					<strong>Indirizzo: </strong><?=$r['indirizzo'] ?>
					<ul>
						<?php foreach($dettaglio as $o)
							echo '<li>' . $o['quantita'] . ' x ' . $o['pizza'] . '</li>';
						?>
					</ul>
					<strong>Totale: </strong><?=$p['prezzo'] ?>€
				</fieldset>
				<br>
			<?php } ?>
			</table>
			<p><a href="index.php">Torna alla pagina iniziale</a></p>
		</main>
	</body>
</html>