<?php 
  require "libreria.php";
  try {
    // si controlla la validita' delle credenziali con la funzione definita nel database
    $dbconn = connessione();
    $statement = $dbconn->prepare('SELECT verifica_login(?, ?)');
    $statement->execute(array($_POST['username'],$_POST['password']));
    $rec = $statement->fetch();
    // se le credenziali sono valide, reindirizza alla home privata
    if ($rec[0]!=-1) {
      session_start();  // si crea una nuova sessione
      $_SESSION['login'] = $rec[0]; // si inserisce il nome utente nella sessione
      header('Location:home.php');
    } else {
      header('Location:index.php?errore=si');
    }
  } catch (PDOException $e) { echo $e->getMessage(); }
?>