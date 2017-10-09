<?php
	require "libreria.php";
	session_start();
	try{
		if (!controllo_admin()) 
			header('Location:lista_ingredienti.php');
		
		$dbconn = connessione();

		/*azione per cancellare un ingrediente dal magazzino*/
		if($_GET['comando']=='elimina_ingrediente'){
			$sql = $dbconn->prepare('DELETE FROM ingredienti WHERE id = ?');
			$sql->execute(array($_GET['ingrediente']));
			header('Location:lista_ingredienti.php');
		}

		/*se il nome è vuoto, torna alla schermata precedente*/
		elseif(empty($_GET['nome']) || !isset($_GET['quantita']) || $_GET['quantita']<0){
			header('Location:modifica_ingrediente.php?ingrediente=' . $_GET['ingrediente'] . '&errore=dati');
		}

		/*se l'ingrediente è nuovo, salvalo e vai alla pagina di modifica*/
		elseif(empty($_GET['ingrediente'])){
			$sql = $dbconn->prepare('INSERT INTO ingredienti(ingrediente, quantita) VALUES (?, ?) RETURNING id');
			$sql->execute(array($_GET['nome'], $_GET['quantita']));
			$r = $sql->fetch();
			header('Location:modifica_ingrediente.php?ingrediente='.$r[0]);
		}
		
		/*se è una modifica, salvala e torna alla pagina di modifica*/
		else {
			$sql = $dbconn->prepare('UPDATE ingredienti SET ingrediente=?, quantita=? WHERE id=?');
			$sql->execute(array($_GET['nome'], $_GET['quantita'], $_GET['ingrediente']));
			header('Location:modifica_ingrediente.php?ingrediente='.$_GET['ingrediente']);
		}

	} catch (PDOException $e) { echo $e->getMessage(); }
?>