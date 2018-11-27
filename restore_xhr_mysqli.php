<?PHP
// teste l'existence du token
if (is_file("verif_token.php")){include("verif_token.php");}else{exit();}
if ($permission != true){echo "Session expir&eacute;e !";exit();}

if (is_file("fonctions.inc.php")){include("fonctions.inc.php");}else{exit();}

// oriente vers la partie du script concernée via switch en fonction de la valeur d'action
if (isset($_GET['action'])){$action=$_GET['action'];}else{exit();}
if (!is_numeric($action)){exit();}// vérifie si $action est un numérique - sinon sort du script

// se connecte à mysql si c'est nécessaire - $connectDB = 1 dans ce cas
if (isset($_GET['connectDB'])){$connectDB=$_GET['connectDB'];}else{exit();}
if (!is_numeric($connectDB)){exit();}// vérifie si $connectDB est un numérique - sinon sort du script

// empêche l'affichage des erreurs - à mettre en remarque pendant le développement
ini_set('display_errors', '0');

// se connecte à la base de données si c'est nécessaire
if ($connectDB > 0)
	{
	if (is_file("connexion.inc.mysql.php")){include("connexion.inc.mysql.php");}
	// les variables $hote, $user, $password, $base, $nom_backup sont incluses dans connexion.inc.mysql.php
	$connect = mysqli_connect($hote, $user, $password) or exit('Erreur dans le fichier '.__FILE__.'<br>Ligne '.__LINE__.'<br>MySQL : '.utf8_encode(mysqli_error())); 
	mysqli_query($connect,"SET NAMES UTF8");
	}
// vérifie si $base est vide si oui $base="" une chaîne vide donc listage des bases 
if (empty($base))
	{
	$base="";
	}

// vérifie la présence du répertoire chargé de recevoir les fichiers de sauvegarde
if (empty($nom_backup) || $nom_backup=="")
	{
		echo 'Impossible de continuer, le nom du dossier de sauvegarde n\'est pas d&eacute;fini !';
		echo '<br />V&eacute;rifier le fichier de connexion';
		exit();
	}

// construit le chemin relatif vers le dossier backup
$dir_backup=$str_retour_rep.$nom_backup.'/';//$nom_backup est issu du fichier de connexion

// vérifie existence du dossier de sauvegarde
if (!is_dir($dir_backup))
	{
		echo '<br />Attention le dossier '.$nom_backup.' n\'existait pas.';
		//création du dossier de sauvegarde et vérification de la création
		$result = @mkdir($dir_backup, 0755);
		if ($result == 1) {
    		echo "<br />Le dossier ".$nom_backup." a &eacute;t&eacute; cr&eacute;&eacute;";
		} else {
    		echo "<br />Le dossier ".$nom_backup." n'a pas &eacute;t&eacute; cr&eacute;&eacute;";
			echo '<br />Il faut cr&eacute;er ce dossier '.$nom_backup.' en tant que sous dossier via ftp';
			echo '<br />Impossible de continuer ...';
			echo '<br />Cause possible : votre h&eacute;bergeur n\'autorise pas ce genre d\'action';
			exit();
		}
	}

// orientation en fonction de la valeur de $action
switch($action)
{
	case 1:
	// choix de la base de données à sauvegarder - clique dans la liste et choix du mode de sauvegarde
	
	echo '<h4>Phase 1 sur 5</h4>';
	
	echo 'Choix de la base de donn&eacute;es &agrave; restaurer - cliquer sur le nom d\'une des bases de la liste';
	
	if ($base=="")// quand $base=="" on tente de lister les bases de données - permission ???
		{
		$set = mysqli_query($connect,'SHOW DATABASES'); 
		$dbs = array(); 
		echo '<br />Liste des bases de donn&eacute;es<br /><select id="choix_base" size="5">';
		$i=0;
		while($db = mysqli_fetch_row($set))
			{
 			$dbs[] = $db[0];
			echo '<option value="'.$db[0].'">'.$db[0].'</option>';
			$i++;
			}
		echo '</select>';
		mysqli_free_result($set);
		if ($i==0)
			{
				echo '<br />Le listage des bases n\'est pas possible - Fin de proc&eacute;dure';
				echo '<br />Cause possible : la permission n\'est pas accord&eacute;e par l\'h&eacute;bergeur';
				exit();
			}
		}
	else
		{
			echo '<br />Pas de choix possible, la seule base est : '.$base;
			echo '<input type="hidden" id="choix_base" value="'.$base.'">';
		}
	
	echo '<br /><br />Cliquer sur ce bouton quand vous avez effectu&eacute; votre choix &gt;&gt;&gt; <input type="button" value="Phase 2 sur 5 - confirme mon choix" name="envoyer" onclick="appel_dump_xhr(\'restore_xhr_mysqli.php?action=2&connectDB=1\',\'affichage\',6)">';
	
	break;
	
	case 2:
    // récapitulatif + choix du nom de fichier de sauvegarde
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	// met le nom de la base dans la variable de session
	$_SESSION['nom_base']=$nom_base;
	
	echo '<h4>Phase 2 sur 5</h4>';
	
	echo 'Nom de la base &agrave; restaurer = <strong>'.$nom_base.'</strong>';
	
	$strrequete = "SHOW TABLES FROM ".$nom_base;
	$result = mysqli_query($connect,$strrequete);
	$i=0;
	while ($row = mysqli_fetch_row($result)) {
   		echo "<br />Table : {$row[0]}";
		$i++;
		}
	if ($i>0) 
		{
   		echo "<br />La base de donn&eacute;es contient encore des tables - IMPOSSIBLE de restaurer sauf si vous supprimez ces tables";
   		echo '<br /><br />Faut-il supprimer ces tables avant de restaurer ?';
		echo '<br /><font color="blue">Attention, disposez-vous de l\'archive zip de restauration ?</font>'; 
		echo '<input type="hidden" id="choix_base" value="'.$nom_base.'">';	
		echo '<br /><br />Si oui, alors vous pouvez continuer &gt;&gt;&gt; <input type="button" value="Confirmer la suppression de ces tables" name="envoyer" onclick="appel_dump_xhr(\'restore_xhr_mysqli.php?action=11&connectDB=1\',\'affichage\',7);">';
		exit();
		}

	echo '<br />Upload de l\'archive zip contenant le fichier .sql &agrave; partir de votre ordinateur';
	echo '<br />ou upload direct du fichier .sql &agrave; partir de votre ordinateur';
	echo '<br />Faites votre choix de fichier avec extension .zip ou .sql (maximum 500 ko)';
	
	// formulaire pour uploader - appel upload.php - la réponse est dirigée vers iframe "uploadFrame"
	echo '<form name="upload" enctype="multipart/form-data" method="post" action="upload.php" target="uploadFrame">';
  	echo '<input type="file" name="file_zip" style="border: 1px solid #000;width:650px;" size="80" accept="application/x-zip-compressed" >';
  	echo '<input type="hidden" name="folder" value="'.$dir_backup.'" >';
  	// transmet la valeur du token par post
	echo '<input type="hidden" name="token" id="token" value="'.$token.'">';
	echo '<input type="submit" name="bouton_submit" style="width:320px;" value="T&eacute;l&eacute;verser le fichier et passer &agrave; la phase 3 sur 5">';
  	echo '</form>';
	
	break;
	
	case 3:
	// procède au téléchargement de l'archive zip 
	// le nom du fichier uploadé est récupéré du post transmis par la réponse de upload.php dans iframe
	if (isset($_POST['nom_fichier'])){$nom_fichier_zip=($_POST['nom_fichier']);}else{exit();}
	if (strrchr($nom_fichier_zip,"Erreur"))
		{
			echo 'Le fichier n\'a pas &eacute;t&eacute; upload&eacute; !';
			echo '<br />'.$nom_fichier_zip.' - <a href=\'http://php.net/manual/fr/features.file-upload.errors.php\'>Infos sur les erreurs</a>';
			exit();
		}
	if (strrchr($nom_fichier_zip,"Faute"))
		{
			echo 'Le fichier n\'a pas &eacute;t&eacute; upload&eacute; !';
			echo '<br />'.$nom_fichier_zip;
			exit();
		}	
	// Vérifie si le fichier a l'extension .zip}
	$extension = strrchr($nom_fichier_zip, ".");
	
	echo '<h4>Phase 3 sur 5</h4>';
	
	echo '<br />Le fichier ['.$nom_fichier_zip.'] a &eacute;t&eacute; &quot;upload&eacute;&quot; dans le dossier '.$nom_backup.' du site';
	if ($extension == ".zip")
		{
			echo '<br />Ce fichier est une archive .zip, il faut extraire le fichier .sql contenu dans cette archive';
			echo '<br /><br />Cliquer sur ce bouton pour extraire le fichier .sql de l\'archive .zip &gt;&gt;&gt; ';
		}
	if ($extension == ".sql")
		{
			echo '<br />Ce fichier n\'est pas une archive .zip (extension .sql permise) - pas d\'extraction !';
			echo '<br /><br />Cliquer sur ce bouton pour passer &agrave; la phase suivante &gt;&gt;&gt; ';
		}
	if ($extension != ".sql" && $extension != ".zip")
		{
			echo '<br />Le fichier a une extension non permise ! Fin de proc&eacute;dure.';
			exit();
		}
	echo '<input type="hidden" id="nom_fichier_zip" value="'.$nom_fichier_zip.'">';	
		
	echo '<input type="button" value="Phase 4 sur 5 - confirme mon choix" name="envoyer" onclick="appel_dump_xhr(\'restore_xhr_mysqli.php?action=4&connectDB=1\',\'affichage\',8);">';
	break;
	
	case 4:
	// extraction du fichier sql
	
	if (isset($_POST['nom_fichier_zip'])){$nom_fichier_zip=htmlspecialchars($_POST['nom_fichier_zip']);}else{exit();}
	
	echo '<h4>Phase 4 sur 5</h4>';
	
	if (!is_file($dir_backup.$nom_fichier_zip))
		{
			echo 'Attention, il n\'y a pas de fichier dans le dossier '.$dir_backup;
			exit();
		}
	
	// vérifie si le fichier est vide
	$octet=filesize($dir_backup.$nom_fichier_zip); 
	if ($octet==0) 
		{ 
		echo 'Le fichier '.$nom_fichier_zip.' est vide &gt;&gt;&gt; Fin de la proc&eacute;dure<br />';
		verifie_efface_fichiers($dir_backup);
		break; 
		}
	
	// Vérifie si le fichier a l'extension .zip
	$extension = strrchr($nom_fichier_zip, ".");
	if ($extension == ".zip")
		{
			$zip = new ZipArchive;
			if ($zip->open($dir_backup.$nom_fichier_zip) === TRUE)
				{
    				$zip->extractTo($dir_backup);
    				$nom_fichier_sql=$zip->getNameIndex(0);
					$zip->close();
    				@unlink($dir_backup.$nom_fichier_zip);
					echo 'Nom du fichier extrait : '.$nom_fichier_sql; 
					echo '<br />Le fichier .sql a &eacute;t&eacute; extrait et l\'archive .zip a &eacute;t&eacute; supprim&eacute;e.';
				}
		
			else 
				{
    				echo '<br />Echec de l\'extraction !';
				}
		}
	
	if ($extension == ".sql")$nom_fichier_sql=$nom_fichier_zip;
	
	echo '<input type="hidden" id="nom_fichier_sql" value="'.$nom_fichier_sql.'">';
	echo ' La restauration de la base '.$_SESSION['nom_base'].' s\'effectuera &agrave; partir du fichier '.$nom_fichier_sql.'<br />';
	echo '<br /><br />Possibilit&eacute; 1 - Passer &agrave; la phase de restauration &gt;&gt;&gt; <input type="button" value="Phase 5 sur 5 - confirme mon choix" name="envoyer" onclick="appel_dump_xhr(\'restore_xhr_mysqli.php?action=8&connectDB=1\',\'affichage\',10);">';
	echo '<br /><br />Possibilit&eacute; 2 - Passer &agrave; la phase de restauration (si les commandes system sont disponibles) &gt;&gt;&gt; <input type="button" value="Phase 5 sur 5 - confirme mon choix" name="envoyer" onclick="appel_dump_xhr(\'restore_xhr_mysqli.php?action=12&connectDB=1\',\'affichage\',10);">';
	break;
	
		
	case 8:
	// restauration 1ère possibilité 
	if (isset($_POST['nom_fichier_sql'])){$nom_fichier_sql=htmlspecialchars($_POST['nom_fichier_sql']);}else{exit();}
	
	echo '<h4>Phase 5 sur 5</h4>';
	
	if (isset($_SESSION['nom_base'])){$nom_base=$_SESSION['nom_base'];}else{exit();}
	$ligne="";
	$ii=0;
	$ptvir=';';
	echo '<br />Nom du fichier .sql de restauration : '.$nom_fichier_sql;
	
	//Ouverture du fichier en lecture seule
	$handle = fopen($dir_backup.$nom_fichier_sql, 'r');
	//Si on a réussi à ouvrir le fichier
	if ($handle)
		{	
		//Tant que l'on est pas à la fin du fichier	
		while (!feof($handle))
			{		
			//On lit la ligne courante
			$buffer = fgets($handle);
			if (strpos($buffer,'-- ')===false && strpos($buffer,'/*!')===false)
				{
					$ligne=$ligne.$buffer;	
					if (strpos($ligne,";\n")>0)
						{
							
							mysqli_select_db($connect,$nom_base) or exit('Erreur dans le fichier '.__FILE__.'<br>Ligne '.__LINE__.'<br>MySQL : '.mysqli_error()); 
							//echo '<br />'.$ligne;
							$requete = mysqli_query($connect,$ligne); 
							$ligne="";
						}
				}
			}
		//On ferme le fichier
		fclose($handle);
		}
	
	
	echo '<br />Fin de la restauration de la base';
	liste_tables($connect,$nom_base,$dir_backup);
	
	break;
	
	
	
	case 11:
	// supprime les tables de la BD à restaurer
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	
	$db_selected = mysqli_select_db($connect,$nom_base); 
	if (!$db_selected){die ('Impossible de selectionner la base de donnees : ' . mysqli_error());} 
	
	$strrequete = "SHOW TABLES FROM ".$nom_base;
	$result = mysqli_query($connect,$strrequete);
	
	while ($row = mysqli_fetch_row($result)) {
   		
		$strrequete_1 = "DROP TABLE ".$row[0];
		mysqli_query($connect,$strrequete_1);
		echo "<br />Table : {$row[0]} a &eacute;t&eacute; supprim&eacute;e";
		}
	echo '<br />Suppression termin&eacute;e, vous pouvez reprendre la phase 1 sur 5';
	break;
	
	case 12:
	// restauration 2me possibilité 
	if (isset($_POST['nom_fichier_sql'])){$nom_fichier_sql=htmlspecialchars($_POST['nom_fichier_sql']);}else{exit();}
	echo '<h4>Phase 5 sur 5</h4>';
	if (isset($_SESSION['nom_base'])){$nom_base=$_SESSION['nom_base'];}else{exit();}
	echo '<br />Nom du fichier .sql de restauration : '.$nom_fichier_sql;
	
	system("mysql --host=".$hote." --user=".$user." --password=".$password." ".$nom_base." < ".$dir_backup.$nom_fichier_sql);

	echo '<br />Fin de la restauration de la base';
	liste_tables($connect,$nom_base,$dir_backup);
	break;
	
	
	default:
	exit();
}// fin de switch
?> 
