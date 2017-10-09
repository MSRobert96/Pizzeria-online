<?php
	require "libreria.php";
	
	try {
		$dbconn = connessione();
		$ingredienti = $dbconn->prepare("SELECT id, ingrediente, quantita FROM ingredienti WHERE ingrediente ilike '%' || ? || '%' OR ?::text IS NULL ORDER BY ingrediente ASC");
		$ingredienti->execute(array($_GET['filtro_nome'], $_GET['filtro_nome']));
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
			<h1>Lista ingredienti</h1>
		</nav>
		<main>
			<table>
				<tr>
					<td class="filter-area">
						<form action="lista_ingredienti.php">
							<p>Cerca per nome:</p>
							<input type="text" name="filtro_nome" value="<?=$_GET['filtro_nome']?>" placeholder="Nome ingrediente">
							<input type="submit" value="Filtra">
						</form>
						<p><a href="lista_ingredienti.php">Cancella filtro</a></p>
						<hr>
						<a href="index.php">Torna alla pagina iniziale</a>
					</td>
					<td style="vertical-align: top">
						<?php if(controllo_admin()){ ?>
							<p><a href="modifica_ingrediente.php">Aggiungi ingrediente</a></p>
						<?php } ?>
						<table class="elenco">
							<tr>
								<td><strong>Ingrediente</strong></td>
								<?php if(controllo_admin()){ ?>
									<td><strong>Quantita</strong></td>
									<td></td>
								<?php } ?>
							</tr>
							<?php
								foreach($ingredienti as $r){
									/*crea una tabella pizza-ingredienti-prezzo*/
									echo "<tr>";
									echo "<td>" . $r['ingrediente'] . "</td>";

									if(controllo_admin()){ ?>
										<td><?=$r['quantita']?></td>
										<td>
											<a href="modifica_ingrediente.php?ingrediente=<?=$r['id']?>" title="Modifica">
												<img alt="Modifica" src="edit_icon.ico" width="20" height="20">
											</a>
										</td>
									<?php }
									echo '</tr>';
								}
							?>
						</table>
					</td>
				</tr>
			</table>
		</main>
	</body>
</html>