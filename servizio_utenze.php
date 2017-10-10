<?php
	require "libreria.php";
	session_start();
	try{

		/*accesso consentito solo all'admin o all'utente che modifica il proprio account*/
		if (!controllo_admin() && $_SESSION['login'] != $_POST['utente'])
			header('Location:gestione_utenze.php');
		
		$dbconn = connessione();

		/*azione per modificare la password dell'utenza*/
		if($_POST['comando']=='Cambia password'){
			if($_POST['nuova'] == $_POST['conferma']){
				$update = $dbconn->prepare("UPDATE utenti SET password = md5(?) WHERE id = ?");
				$update->execute(array($_POST['nuova'], $_POST['utente']));
				header('Location:modifica_utente.php?utente=' . $_POST['utente']);
			}
			else
				header('Location:modifica_utente.php?utente=' . $_POST['utente'] . '&errore=password');
		}

		/*azione per eliminare l'utenza*/
		elseif($_GET['comando']=='elimina_utente'){
				$sql = $dbconn->prepare('DELETE FROM utenti WHERE id = ?');
				$sql->execute(array($_GET['utente']));
				header('Location:gestione_utenze.php');
		}

		/*se mancano dati, torna alla schermata precedente*/
		elseif(empty($_POST['nome']) || empty($_POST['cognome']) || empty($_POST['indirizzo'])){
			error_log($_POST['nome'] + $_POST['cognome'] + $_POST['indirizzo'],0);
			header('Location:modifica_utente.php?utente=' . $_POST['utente'] . '&errore=dati');
		}

		/*se l'utente non è valorizzato, torna indietro (non dovrebbe mai accadere)*/
		elseif(empty($_POST['utente'])){
			header('Location:gestione_utenze.php');
		}
			
		/*se è una modifica, salvala e torna alla pagina di modifica*/
		else {
			$sql = $dbconn->prepare('UPDATE utenti SET nome=?, cognome=?, indirizzo=?, telefono=? WHERE id=?');
			$sql->execute(array($_POST['nome'], $_POST['cognome'], $_POST['indirizzo'], $_POST['telefono'], $_POST['utente']));
			header('Location:modifica_utente.php?utente='.$_POST['utente']);
		}
	} catch (PDOException $e) { echo $e->getMessage(); }
?>