<?php
	require "libreria.php";
	if(
		empty($_POST['nome']) ||
		empty($_POST['cognome']) ||
		empty($_POST['indirizzo']) ||
		empty($_POST['username']) || 
		empty($_POST['password'])
	) {
		header('Location:registrazione.php?errore=datimancanti');
	} elseif($_POST['password'] != $_POST['conferma']) {
		header('Location:registrazione.php?errore=password');
	}
	else
		try {
			$dbconn = connessione();
			$statement = $dbconn->prepare('SELECT COUNT(*) FROM Utenti WHERE username = ?');
			$statement->execute(array($_POST['username']));
			$rec = $statement->fetch();
			if ($rec[0] == 1){
				header('Location:registrazione.php?errore=utenteesistente');
			}
			else {
				$statement = $dbconn->prepare('SELECT nuovo_utente(?,?,?,?,?,?)');
				$statement->execute(
					array(
						$_POST['username'],
						$_POST['password'],
						$_POST['nome'],
						$_POST['cognome'],
						$_POST['indirizzo'],
						$_POST['telefono']
					)
				);

				session_start();
				
				if (controllo_admin()){
					/*se è la creazione di un'utenza da parte dell'admin, torna alla pagina di amministrazione*/
					header('Location:gestione_utenze.php');
				}
				else {
					/*se è una registrazione, effettua il login e vai all'area riservata*/
					$_SESSION['login'] = $statement->fetch()[0];
					header('Location:home.php');
				}
			}
		} catch (PDOException $e) { echo $e->getMessage(); }
?>