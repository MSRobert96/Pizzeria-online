<?php
	require "libreria.php";
	session_start();
	try{
		if (!controllo_accesso()) 
			header('Location:index.php');
		
		if (!controllo_admin() && $_SESSION['login'] != $_GET['utente'])
			header('Location:gestione_ordini.php');

		$dbconn = connessione();

		/*se l'admin cambia lo stato dell'ordine*/
		if($_GET['status'] == 'consegnato'){
			$sql = $dbconn->prepare('UPDATE ordini SET annullato=false, consegnato=true WHERE id=?');
			$sql->execute(array($_GET['ordine']));
		}
		elseif($_GET['status'] == 'annullato'){
			$sql = $dbconn->prepare('UPDATE ordini SET consegnato=false, annullato=true WHERE id=?');
			$sql->execute(array($_GET['ordine']));
		}

		/*azione se la quantita di pizze da aggiungere non è valida*/
		if ($_GET['comando']=="Aggiungi pizze" && !empty($_GET['quantita']) && $_GET['quantita']<=0)
			header('Location:modifica_ordine.php?ordine='.$_GET['ordine'].'&errore=quantita');
		
		/*azione se si vuole aggiungere delle pizze ad un ordine*/
		elseif($_GET['comando']=="Aggiungi pizze" && !empty($_GET['quantita']) && $_GET['quantita']>0){
			$controllo = $dbconn->prepare('SELECT CASE WHEN ? in (SELECT id FROM pizze_ordinabili()) THEN true ELSE false END');
			$controllo->execute(array($_GET['pizza']));
			if ($controllo->fetch()[0]) {
				$sql = $dbconn->prepare('SELECT aggiungi_pizze_a_ordine(?, ?, ?)');
				$sql->execute(array($_GET['ordine'], $_GET['pizza'], $_GET['quantita']));
				if($sql->fetch()[0])
					header('Location:modifica_ordine.php?ordine='.$_GET['ordine']);
				else
					header('Location:modifica_ordine.php?ordine='.$_GET['ordine'].'&errore=pizza');
			}
			else
			header('Location:modifica_ordine.php?ordine='.$_GET['ordine'].'&errore=ingredienti');
		}

		/*azione se si vuole togliere delle pizze da un ordine*/
		elseif($_GET['comando']=="togli_pizze"){
			$sql = $dbconn->prepare('DELETE FROM ordine_pizza WHERE ordine=? AND pizza=?');
			$sql->execute(array($_GET['ordine'], $_GET['pizze']));
			header('Location:modifica_ordine.php?ordine='.$_GET['ordine']);
		}

		/*se i dati del'ordine non sono validi, torna alla schermata precedente*/
		elseif(empty($_GET['giorno']) || empty($_GET['ora']) || empty($_GET['indirizzo'] || empty($_GET['utente']))) {
			header('Location:modifica_ordine.php?ordine=' . $_GET['ordine'] . '&errore=dati');
		}

		/*azione per salvare un nuovo ordine*/
		elseif(empty($_GET['ordine'])){
			$sql = $dbconn->prepare('SELECT crea_ordine(?, ?, ?, ?)');
			$sql->execute(array($_GET['utente'], $_GET['giorno'], $_GET['ora'], $_GET['indirizzo']));
			$r = $sql->fetch();
			header('Location:modifica_ordine.php?ordine='.$r[0]);
		}

		/*se è una modifica, salvala e torna alla pagina di modifica*/
		else {
			$sql = $dbconn->prepare('UPDATE ordini SET utente=?, giorno=?, ora=?, indirizzo=? WHERE id=?');
			$sql->execute(array($_GET['utente'], $_GET['giorno'], $_GET['ora'], $_GET['indirizzo'], $_GET['ordine']));
			header('Location:modifica_ordine.php?ordine='.$_GET['ordine']);
			error_log($_GET['utente'],0);
		}

	} catch (PDOException $e) { echo $e->getMessage(); }
?>