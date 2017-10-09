<?php
	require "libreria.php";
	session_start();
	try{
		if (!controllo_admin()) 
			header('Location:listino.php');
		
		$dbconn = connessione();

		/*azione se si vuole aggiungere un ingrediente ad una pizza*/
		if($_GET['comando']=='Aggiungi ingrediente'){
			$sql = $dbconn->prepare('SELECT aggiungi_ingrediente_a_pizza(?, ?)');
			$sql->execute(array($_GET['pizza'], $_GET['ingrediente']));
			$r = $sql->fetch();
			if($r[0]) header('Location:modifica_pizza.php?pizza='.$_GET['pizza']);
			else header('Location:modifica_pizza.php?pizza='.$_GET['pizza'].'&errore=ingrediente');
		}

		/*azione se si vuole togliere un ingrediente da una pizza*/
		elseif($_GET['comando']=='togli_ingrediente'){
			$sql = $dbconn->prepare('DELETE FROM pizza_ingrediente WHERE pizza=? AND ingrediente=?');
			$sql->execute(array($_GET['pizza'], $_GET['ingrediente']));
			header('Location:modifica_pizza.php?pizza='.$_GET['pizza']);
		}

		/*azione per cancellare una pizza dal listino*/
		elseif($_GET['comando']=='elimina_pizza'){
			$sql = $dbconn->prepare('DELETE FROM pizze WHERE id = ?');
			$sql->execute(array($_GET['pizza']));
			header('Location:listino.php');
		}

		/*se nome o prezzo non sono validi, torna alla schermata precedente*/
		elseif(empty($_GET['nome']) || empty($_GET['prezzo']) || $_GET['prezzo']<=0) {
			header('Location:modifica_pizza.php?pizza=' . $_GET['pizza'] . '&errore=dati');
		}

		/*azione per salvare un nuova pizza*/
		elseif(empty($_GET['pizza'])){
			$sql = $dbconn->prepare('SELECT aggiungi_pizza(?, ?)');
			$sql->execute(array($_GET['nome'], $_GET['prezzo']));
			$r = $sql->fetch();
			header('Location:modifica_pizza.php?pizza='.$r[0]);
		}

		/*se è una modifica, salvala e torna alla pagina di modifica*/
		else {
			$sql = $dbconn->prepare('UPDATE pizze SET nome=?, prezzo=? WHERE id=?');
			$sql->execute(array($_GET['nome'], $_GET['prezzo'], $_GET['pizza']));
			header('Location:modifica_pizza.php?pizza='.$_GET['pizza']);
		}

	} catch (PDOException $e) { echo $e->getMessage(); }
?>