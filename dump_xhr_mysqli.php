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
$dir_backup=$nom_backup.'/';//$nom_backup est issu du fichier de connexion

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

	echo '<h4>Phase 1 sur 4</h4>';

	echo 'Choix de la base de donn&eacute;es &agrave; sauvegarder - cliquer sur le nom d\'une des bases de la liste';
	
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
		mysqli_free_result($set);	
		echo '</select>';
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
	echo '<br /><br />Choix du mode de sauvegarde - cocher un des 2 boutons';
	echo '<br />Sauvegarder uniquement la structure des tables &gt;&gt;&gt;<input type="radio" name="choix_struct_donnees" id="choix_struct_1">';
	echo ' ou sauvegarder la structure et les donn&eacute;es des tables &gt;&gt;&gt; <input type="radio" name="choix_struct_donnees" id="choix_struct_2" checked>';
	echo '<br /><br />Cliquer sur ce bouton quand vous avez effectu&eacute; votre choix &gt;&gt;&gt; <input type="button" value="Phase 2 sur 4 - confirme mon choix" name="envoyer" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=2&connectDB=1\',\'affichage\',2)">';
	
	break;
	
	case 2:
    // récapitulatif + choix du nom de fichier de sauvegarde
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	if (isset($_POST['mode'])){$mode=htmlspecialchars($_POST['mode']);}else{exit();}

	echo '<h4>Phase 2 sur 4</h4>';

	echo 'Nom de la base &agrave; sauvegarder = <strong>'.$nom_base.'</strong>';
	$str_mode='Structure + donn&eacute;es'; 
	if ($mode=="1"){$str_mode='Structure uniquement';}
	echo '<br />Mode de sauvegarde des tables = <strong>'.$str_mode.'</strong>';
	$nom_fichier_dump=$nom_base.'_dump_'.time();
	echo '<br />Nom du fichier de sauvegarde = <input type="text" id="nom_fichier_dump" value="'.$nom_fichier_dump.'" size="40"><strong>.sql</strong> - (sera enregistr&eacute dans le dossier '.$nom_backup.' avec l\'extension .sql)';
	echo '<input type="hidden" id="nom_base_2" value="'.$nom_base.'">';
	echo '<input type="hidden" id="mode_2" value="'.$mode.'">';
	echo '<br /><br />Cr&eacute;ation du fichier de sauvegarde :';
	echo '<table border="0"><tr><td height="50">M&eacute;thode 1 - Sans utiliser mysqldump</td><td> - cliquer sur ce bouton pour confirmer &gt;&gt;&gt; <input type="button" value="Phase 3 sur 4 - confirme mes choix" name="envoyer" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=3&connectDB=1\',\'affichage\',3)"></td></tr>';
	echo '<tr><td>M&eacute;thode 2 - Avec mysqldump (si disponible)</td><td> - cliquer sur ce bouton pour confirmer &gt;&gt;&gt; <input type="button" value="Phase 3 sur 4 - confirme mes choix" name="envoyer" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=8&connectDB=1\',\'affichage\',3)"></td></tr></table>';
	break;
	
	case 3:
	// procède à la sauvegarde - création du fichier sql
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	if (isset($_POST['mode'])){$mode=htmlspecialchars($_POST['mode']);}else{exit();}
	if (isset($_POST['nom_fichier_dump'])){$nom_fichier_dump=htmlspecialchars($_POST['nom_fichier_dump']).'.sql';}else{exit();}

	echo '<h4>Phase 3 sur 4</h4>';
	
	//appel la fonction dumpMySQL
	dumpMySQL($hote, $user, $password, $nom_base, $mode, $nom_fichier_dump, $dir_backup);
	
	echo '<br />Le fichier '.$nom_fichier_dump.' est contenu actuellement dans le dossier '.$nom_backup.' du site';
	echo '<input type="hidden" id="nom_fichier_dump_2" value="'.$nom_fichier_dump.'">';
	echo '<input type="hidden" id="nom_base_2" value="'.$nom_base.'">';
	echo '<input type="hidden" id="mode_2" value="'.$mode.'">';
	echo '<br /><br />Cliquer sur ce bouton pour construire le fichier archive .zip &gt;&gt;&gt; <input type="button" value="Phase 4 sur 4 - Construction du fichier archive .zip" name="envoyer" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=4&connectDB=1\',\'affichage\',4)">';
	break;
	
	case 4:
	// création du fichier zip archive qui contient le fichier sql
	if (isset($_POST['nom_fichier_dump'])){$file=htmlspecialchars($_POST['nom_fichier_dump']);}else{exit();}
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	if (isset($_POST['mode'])){$mode=htmlspecialchars($_POST['mode']);}else{exit();}

	echo '<h4>Phase 4 sur 4</h4>T&eacute;l&eacute;chargement du fichier '.$file.'.zip<br />';
	
	// vérifie si le fichier est vide
	$octet=filesize($dir_backup.$file); 
	if ($octet==0) 
		{ 
		echo 'Le fichier '.$file.' est vide &gt;&gt;&gt; Fin de la proc&eacute;dure<br />';
		verifie_efface_fichiers($dir_backup);
		echo '<br />Cause possible : l\'outil mysqldump n\'est pas disponible.';
		break; 
		} 
	$zip = new ZipArchive(); 
    if($zip->open($dir_backup.$file.'.zip', ZipArchive::CREATE) === true)
      {
        echo 'Le fichier archive &quot;'.$file.'.zip&quot; a &eacute;t&eacute; cr&eacute;&eacute; et enregistr&eacute; dans le dossier backup<br/>';
		// Ajout d’un fichier.
		$zip->addFile($dir_backup.$file,$file);
		$zip->close();
      }
      else
      {
        echo 'Impossible de cr&eacute;er le fichier zip';
	  	break;
	  }
	  // propose le téléchargement du zip
	  echo '<br /><font color="blue">Attention, un peu de patience est n&eacute;cessaire pour obtenir la proposition de t&eacute;l&eacute;chargement ...</font>';
	  echo '<script type="text/javascript">window.location.href="'.$dir_backup.$file.'.zip"</script>';
	  
	  //ajout dans le fichier de journalisation
	  if (is_file("journal_dump.txt"))
	  	{
	  		$fp = fopen("journal_dump.txt","a"); 
	  		$date = date("d-m-Y");
			$heure = date("H:i");
			$struct="complet (structure + données)";
			if ($mode != 3)$struct="structure (sans les données)";
			$txt = 'backup du '.$date.' à '.$heure.' de la base '.$nom_base.' dans le fichier '.$file.chr(13).'contenu dans l\'archive '. $file.'.zip en mode '.$struct.chr(13).' '.chr(13);
	  		fwrite($fp, $txt); 
	  		fclose($fp);
		}
	  
	  echo '<br /><br />Quand le t&eacute;l&eacute;chargement est termin&eacute;, il faut SUPPRIMER les fichiers du dossier '.$nom_backup.' du site';
	  echo '<br />Cliquer sur ce bouton pour confirmer la fin du t&eacute;l&eacute;chargement et supprimer les fichiers du dossier '.$nom_backup.' &gt;&gt;&gt; <input type="button" value="Confirme et Supprime" name="button_3" id="button_3" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=7&connectDB=1\',\'affichage\',1)"> ';
	  echo '<br /><br />Les fichiers suivants sont actuellement pr&eacute;sents dans le dossier '.$nom_backup.' du site :<br />';
	verifie_efface_fichiers($dir_backup);
	break;
	
	case 5:
	// reset affichage de la procédure
	verifie_efface_fichiers($dir_backup);
	echo '&nbsp;';
	break;
	
	case 6:
	// vérifie la connexion à la bd
	echo '<br />La connexion au serveur Mysql ('.$hote.') a r&eacute;ussi';
	if (!empty($base))
		{
			echo '<br />Une seule base de donn&eacute;es est concern&eacute;e : '.$base;
		}
	else
		{
			echo '<br />Un choix dans la liste des bases de donn&eacute;es sera n&eacute;cessaire (si votre h&eacute;bergeur le permet)';
		}
	echo '<br />Le nom du dossier de sauvegarde sur le site est : '.$nom_backup;
	echo '<br />Le chemin relatif de cette page vers le dossier de sauvegarde est : '.$dir_backup;
	
	
	break;
	
	case 7:
	//efface les fichiers se trouvant dans le dossier backup
	deltree($dir_backup,$dir_backup);
	break;
	
	case 8:
	// récapitulatif + choix du nom de fichier de sauvegarde
	if (isset($_POST['nom_base'])){$nom_base=htmlspecialchars($_POST['nom_base']);}else{exit();}
	if (isset($_POST['mode'])){$mode=htmlspecialchars($_POST['mode']);}else{exit();}
	if (isset($_POST['nom_fichier_dump'])){$nom_fichier_dump=htmlspecialchars($_POST['nom_fichier_dump']).'.sql';}else{exit();}
	$struc="";
	if ($mode==1){$struc="-d";}

	echo '<h4>Phase 3 sur 4</h4>Utilisation de l\'outil mysqldump (s\'il est disponible)';

	system('mysqldump '.$struc.' --host='.$hote.' --user='.$user.' --password='.$password.' '.$nom_base.' > '.$dir_backup.$nom_fichier_dump);
	echo '<input type="hidden" id="nom_fichier_dump_2" value="'.$nom_fichier_dump.'">';
	echo '<input type="hidden" id="nom_base_2" value="'.$nom_base.'">';
	echo '<input type="hidden" id="mode_2" value="'.$mode.'">';
	echo '<br /><br />Cliquer sur ce bouton pour construire le fichier archive .zip &gt;&gt;&gt; <input type="button" value="Phase 4 sur 4 - Construction du fichier archive .zip" name="envoyer" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=4&connectDB=1\',\'affichage\',4)">';
	break;
	
	case 9:
	// vision du journal des backups
	echo 'Vision du journal des backups - ';
	if (is_file("journal_dump.txt"))
	  	{
			if (filesize("journal_dump.txt")>0)
				{
					$ii=rand();// nécessaire pour la mise à jour de l'aperçu - problème antémémoire du navigateur
					// bouton pour vider le contenu du fichier
					echo '<input type="button" value="vider le journal" name="button_0" id="button_0" style="width:120px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=10&connectDB=1\',\'affichage\',5)"><br />'; 
					echo '<table>';
					echo '<tr><td><iframe align="left" width="900" height="200" frameborder="1" scrolling="yes" src="journal_dump.txt?val='.$ii.'" id="iframe1"></iframe></td></tr>';
					echo '</table>';
				}
			else
				{
					echo 'Le fichier journal est vide !';
				}
		}
	
	break;
	
	case 10:
	// vider le journal des backups
	if (is_file("journal_dump.txt"))
	  	{
			$fp = fopen("journal_dump.txt","w");
			$txt="";
			fwrite($fp, $txt); 
	  		fclose($fp);
		}
	echo 'Le journal des backups a &eacute;t&eacute; vid&eacute.';
	break;
	
	
	//case 11:
	// le nom du fichier uploadé est récupéré du post transmis par la réponse de upload.php dans iframe
	//if (isset($_POST['nom_fichier'])){$nom_fichier=$_POST['nom_fichier'];}else{exit();}
	//echo 'TEST : <br />'.$nom_fichier;
	//break;
	
	default:
	exit();
}// fin de switch
?>