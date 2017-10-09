<?php
	require "libreria.php";
	try {
		$dbconn = connessione();
	} catch (PDOException $e) { echo $e->getMessage(); }

	$ingredienti = $dbconn->prepare("SELECT id, ingrediente FROM ingredienti_pizza(?)");
	$pizze = $dbconn->prepare("SELECT id, nome, prezzo FROM pizze_con_ingredienti(?) WHERE NOME ilike '%' || ? || '%' OR ?::text IS NULL");

	$in = '{}';
	if (!empty($_GET['i'])){
		$in = '{' . implode(",", $_GET['i']) . '}';
	}
	$pizze->execute(array($in, $_GET['filtro_nome'], $_GET['filtro_nome']));

	session_start();
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
			<h1>Listino pizze</h1>
		</nav>
		<main>
			<table>
				<tr>
					<td class="filter-area">
						<form action="listino.php">
							<p>Cerca per nome:</p>
							<input type="text" name="filtro_nome" value="<?=$_GET['filtro_nome']?>" placeholder="Nome pizza">
							
							<p>Cerca per ingredienti:</p>
							<div style="height:200px; overflow:auto; border:1px solid #ccc;">
								<?php
									foreach(lista_ingredienti($dbconn) as $r){
										/*crea checkbox per l'ingrediente*/
										echo "<label><input type='checkbox' name='i[]' value='" . $r['id'] . "'";
										/*pre-seleziona i filtri già scelti*/
										if (!empty($_GET['i']) && in_array($r['id'], $_GET['i']))
											echo "checked";
										echo ">" . $r['ingrediente'] . "</label>";
										echo "<br/>";
									}
								?>
							</div>
							<p><a href="listino.php">Cancella tutti i filtri</a></p>
							<input class="conferma" type="submit" value="Applica filtri">
						</form>
						<hr>
						<a href="index.php">Torna alla pagina iniziale</a>
					</td>
					<td style="vertical-align: top">
						<table class="elenco">
							<?php
								if(controllo_admin())
									echo '<a href="modifica_pizza.php?comando=crea">Aggiungi pizza</a>';
					
								foreach($pizze as $r){
									/*crea una tabella pizza-ingredienti-prezzo*/
									$ingredienti->execute(array($r['id']));
									echo "<tr>";
									echo "<td><strong>" . $r['nome'] . "</strong></td>";
									echo "<td>" . implode(", ", array_column($ingredienti->fetchAll(), 'ingrediente')) . "</td>";
									echo "<td>" . $r['prezzo'] . "€</td>";

									/*Se l'utente è amministratore, mostra comando di modifica*/
									if(controllo_admin()){ ?>
										<td>
											<a href="modifica_pizza.php?pizza=<?php echo $r['id']?>" title="Modifica">
												<img alt="Modifica" src="edit_icon.ico" width="20" height="20">
											</a>
										</td>
									<?php }
									echo "</tr>\n";
								} 
							?>
						</table>
					</td>
				</tr>
			</table>
		</main>
	</body>
</html>