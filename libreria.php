<?php
// Questo file viene sempre incluso all'inizio dei files .php

// da eseguire per produrre stampe di errore visibili nelle pagine di risposta
error_reporting(E_ALL & ~E_NOTICE);

// alcune funzioni di utilita' comune alle varie pagine
  // verifica se esiste una sessione attiva (l'utente si e' autenticato)
  // altrimenti ri-indirizza alla pagina di "ingresso" (index.php)
  function controllo_accesso() {
    session_start();
    if (empty($_SESSION['login'])) 
      return false;
    return true;
  }

  // crea una connessione al database
  function connessione() {
    $dbconn = new PDO('pgsql:host=localhost;port=5432;dbname=pizzeria','postgres','Password1');
    $dbconn -> setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbconn;
  }

  function controllo_admin() {
    session_start();
    if (!empty($_SESSION['login']) && $_SESSION['login']==1)
      return true;
    return false;
  }

  function lista_ingredienti($dbconn) {
    return $dbconn->query("SELECT id, ingrediente, quantita FROM ingredienti ORDER BY ingrediente ASC");
  }
  
  function lista_pizze($dbconn) {
    return $dbconn->query("SELECT id, nome, prezzo FROM pizze");
  }

  function lista_utenti($dbconn) {
    return $dbconn->query("SELECT id, nome, cognome, indirizzo, username FROM utenti");
  }
  
  function validateDate($date)
  {
      $d = DateTime::createFromFormat('Y-m-d', $date);
      return $d && $d->format('Y-m-d') === $date;
  }

  function validateTime($time)
  {
      return preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $time);
  }
?>

