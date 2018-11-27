<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Installation gestion sauvegardes ou restaurations de base de donnees</title>
</head>

<body>
<b>Installation des scripts backup et restauration des bases de donn&eacute;es</b>
<hr />

<?PHP
if(!isset($_GET["etape"])){$etape = 0;}else{$etape = $_GET['etape'];}
$dossier=explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$dossierParent = array_pop($dossier);
echo '<br />Nom du dossier : '.$dossierParent.' &lt;&lt;&lt; ce dossier doit &ecirc;tre prot&eacute;g&eacute (utiliser un .htaccess du serveur web Apache)<br />';
$fichier=array("connexion.inc.mysql.php","dump_mysqli.php","dump_xhr_mysqli.php","install_dump_mysqli.php","verif_token.php","js/traite_dump.js","journal_dump.txt", "restore_mysqli.php","restore_xhr_mysqli.php","upload.php","test_upload.php","fonctions.inc.php");


switch($etape)
{
	case 0:	
	echo 'Avant de proc&eacute;der &agrave; l\'installation, il est n&eacute;cessaire d\'avoir l\'arborescence suivante :';
	echo '<br />Le dossier principal <u>'.$dossierParent.'</u> contient le sous-dossiers <u>js</u> ainsi que les fichiers : ';
	for ($ii=0;$ii<sizeof($fichier);$ii++)
		{
			echo '<br />'.$fichier[$ii];
		}
	echo '<br />Le sous-dossier <u>js</u> contient le fichier :';
	echo '<br />traite_dump.js';
	echo '<br />L\'&eacute;tape 4 v&eacute;rifie la pr&eacute;sence des fichiers n&eacute;cessaires';
	echo '<hr />';
	echo "<H4>Etape 1 sur 7 - construction du fichier de connexion à la base de données mysql</H4>";
	if (file_exists($fichier[0]))
		{
		chmod ($fichier[0], 0777);
		include ($fichier[0]);
		}
		
	else{
		$hote="";
		$user="";
		$password="";
		$base="";
		$nom_backup="";
		}
		
		echo "<form method='POST' action='install_dump_mysqli.php?etape=1' name='config_mysql'>";
		echo "Entrer les valeurs qui permettent la connexion à la base de données mysql - création ou mise à jour du fichier : ".$fichier[0];  
		echo "<br />Attention, tous les champs sont obligatoires";
		if ($hote == ""){$hote = "localhost:3306";}
		echo '<br />Nom du serveur mysql : <input TYPE="text" NAME="nom_serveur_mysql" SIZE="20" maxlength="20" value ="'.$hote.'"> max 20 caract&egrave;res - localhost:3306 par défaut';
		echo "<br />Nom de l'utilisateur mysql : <input TYPE='text' NAME='nom_utilisateur_mysql' SIZE='20' maxlength='20' value ='".$user."'> max 20 caract&egrave;res";
		echo "<br />Mot de passe mysql : <input TYPE='text' NAME='mot_passe_mysql' SIZE='20' maxlength='20' value ='".$password."'> max 20 caract&egrave;res";
		echo "<br />Nom de la base de données mysql : <input TYPE='text' NAME='nom_base_mysql' SIZE='20' maxlength='20' value ='".$base."'> max 20 caract&egrave;res - laisser vide si le listage des bases est autoris&eacute; par l'h&eacute;bergeur";
		if ($nom_backup == ""){$nom_backup="backup_dump";}
		echo "<br />Nom du dossier backup : <input TYPE='text' NAME='nom_dossier' SIZE='20' maxlength='20' value ='".$nom_backup."'> max 20 caract&egrave;res - backup_dump par d&eacute;faut";
		echo "<br /><br /><input type='reset' value='Recommencer'><input TYPE='Submit' VALUE='Valider vos données'>";
		echo "</form>";
		break;
		
		case 1:
		if(isset($_POST["nom_serveur_mysql"]) == ""){header("location:install.php");}
		if(isset($_POST["nom_utilisateur_mysql"]) == ""){header("location:install.php");}
		if(isset($_POST["mot_passe_mysql"]) == ""){header("location:install.php");}
		if(isset($_POST["nom_base_mysql"]) == ""){header("location:install.php");}
		if(isset($_POST["nom_dossier"]) == ""){header("location:install.php");}
		$hote = $_POST["nom_serveur_mysql"];
		$user = $_POST["nom_utilisateur_mysql"];
		$password = $_POST["mot_passe_mysql"];
		$base = $_POST["nom_base_mysql"];
		$nom_backup = $_POST["nom_dossier"];
		//supprime le fichier s'il existe
		if (file_exists($fichier[0])){unlink($fichier[0]);}
		// crèe le fichier et inscrit les 7 lignes
		$lignes[0]="<?PHP \n";
		$lignes[1]="\$hote = '".$hote."';\n";
		$lignes[2]="\$user = '".$user."';\n";
		$lignes[3]="\$password = '".$password."';\n";
		$lignes[4]="\$base = '".$base."';\n";
		$lignes[5]="\$nom_backup = '".$nom_backup."';\n";
		$lignes[6]="?>\n";
		$fp = fopen ($fichier[0], "w+"); 
		for ($ii=0 ; $ii<7 ; $ii++)
			{
			fwrite ($fp, $lignes[$ii]);
			} 
		fclose($fp);
		chmod ($fichier[0], 0604);
		
		echo '<H4>Etape 2 sur 7 - V&eacute;rification de la connexion avec le serveur Mysql</H4>';
		
		if (is_file($fichier[0])){include($fichier[0]);}else{echo 'Le fichier de connexion est introuvable !';exit();}
		
		if($connect = mysqli_connect($hote, $user, $password))
			{
			mysqli_query($connect,"SET NAMES UTF8"); 
			echo 'Connexion r&eacute;ussie';// Si la connexion a réussi, rien ne se passe.
			}
		else // Mais si elle rate…
			{
			echo 'Erreur de connexion avec le serveur mysql';
			exit(); // On affiche un message d'erreur.
			}
		mysqli_close($connect);
		echo ' - &gt;&gt;&gt; <input type="button" value="Continuer" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=2\'">';
		break;
		
		case 2:
		echo '<H4>Etape 3 sur 7 - V&eacute;rification de l\'existence du dossier backup</H4>';
		if (is_file("connexion.inc.mysql.php")){include("connexion.inc.mysql.php");}
		// construit le chemin relatif vers le dossier backup
		
		$dir_backup=$nom_backup;//$nom_backup est issu du fichier de connexion

		// vérifie existence du dossier de sauvegarde
		if (!is_dir($dir_backup))
			{
			echo '<br />Attention le dossier '.$nom_backup.' n\'existait pas.';
			//création du dossier de sauvegarde et vérification de la création
			$result = @mkdir($dir_backup, 0755);
			if ($result == 1)
				{
    			echo '<br />Le dossier backup dont le nom est "'.$nom_backup.'" a &eacute;t&eacute; cr&eacute;&eacute;';
				}
			else
				{
    			echo '<br />Le dossier backup dont le nom est "'.$nom_backup.'" n\'a pas &eacute;t&eacute; cr&eacute;&eacute;';
				echo '<br />Il faut cr&eacute;er ce dossier '.$nom_backup.' en tant que sous-dossier du dossier '.$dossierParent.' contenant ce script via ftp';
				echo '<br />Impossible de continuer ...';
				echo '<br />Cause possible : votre h&eacute;bergeur n\'autorise pas ce genre d\'action';
				exit();
				}
			}
		else
			{
				echo '<br />Le dossier backup dont le nom est "'.$nom_backup.'" existait d&eacute;j&agrave;';
			}
		
		echo ' &gt;&gt;&gt; <input type="button" value="Continuer" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=4\'">';
		break;
		
		
	 case 4:
	 echo "<H4>Etape 4 sur 7 - V&eacute;rification de la pr&eacute;sence des 12 fichiers n&eacute;cessaires</H4>";
	 
	 for($ii=0;$ii<12;$ii++)
	 {	 
	 	$jj=$ii+1;
		if (file_exists($fichier[$ii]))
		{
			echo '<br />'.$jj.' - Le fichier '.$fichier[$ii].' est pr&eacute;sent - <font color="green">ok</font>';
			if (is_writable($fichier[$ii]))
			{
				echo ' - Acc&egrave;s en &eacute;citure est permis';
			}
			else
			{
				echo ' - Acc&egrave;s en &eacute;citure n\'est pas permis';
			}
		}
		else
		{
			echo '<br />'.$jj.' - Attention, le fichier '.$fichier[$ii].' n\'est pas pr&eacute;sent ou ne se trouve pas au bon emplacement dans la structure des dossiers et sous-dossiers - <font color="red">faute</font>';
		}
	 }
	 // vider le journal des backups
	if (is_file("journal_dump.txt") && filesize("journal_dump.txt")>0)
	  	{
			$fp = fopen("journal_dump.txt","w");
			$txt="";
			fwrite($fp, $txt); 
	  		fclose($fp);
		}
	 
	 echo '<br /><br /><input type="button" value="Continuer" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=5\'">';
	 break;
	 
	case 5:
	echo "<H4>Etape 5 sur 7 - Test d'upload</H4>";
	if (is_file($fichier[0])){include($fichier[0]);}else{echo 'Le fichier de connexion est introuvable !';exit();}
	// formulaire pour uploader - appel upload.php - la réponse est dirigée vers iframe "uploadFrame"
	echo '<br /><br />Test d\'upload d\'un fichier &gt;&gt;&gt; choisir un fichier avec une extension .sql ou .zip sur votre ordinateur (maximum de 500 ko) - aucune restauration de BD ne sera effectu&eacute;e ! (simple test d\'upload)<br />';
	echo '<form name="upload" enctype="multipart/form-data" method="post" action="test_upload.php" target="uploadFrame">';
  	echo '<input type="file" name="fichier" size="80" accept="application/x-zip-compressed" >';
  	echo '<input type="hidden" name="folder" value="'.$nom_backup.'" >';
  	
	
	echo '<input type="submit" name="bouton_submit" style="width:320px;" value="T&eacute;l&eacute;verser le fichier">';
  	echo '</form>';
	echo'<iframe id="uploadFrame" name="uploadFrame" width="800" height="150" border="0" frameborder="0" ></iframe>';
	 echo '<br /><br />ou <input type="button" value="Continuer" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=6\'">';
	break;
	 
	 case 6:
	 if (is_file($fichier[0])){include($fichier[0]);}else{echo 'Le fichier de connexion est introuvable !';exit();}
	 echo "<H4>Etape 6 sur 7</h4>";
	 echo'Supprime le contenu du dossier backup<br />';
	 deltree($nom_backup,$nom_backup);
	 echo '<br /><br /><input type="button" value="Continuer" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=7\'">';
	 break;
	 
	 
	 case 7:
	 echo "<H4>Etape 7 sur 7</h4>";
	 echo 'Fin de l\'installation';
	 echo '<br /><br /><input type="button" value="Retour &agrave; l sur 7" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'install_dump_mysqli.php?etape=0\'">';
	 echo '<br /><br /><input type="button" value="Tester la sauvegarde" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'dump_mysqli.php\'">';
	 echo '<br /><br /><input type="button" value="Tester la restauration" name="button_1" id="button_1" style="width:200px;" onclick="window.location.href=\'restore_mysqli.php\'">';
	 echo '<br /><br /><font color="blue">Il est recommand&eacute; de supprimer ce fichier ['.basename( __FILE__ ).'] du r&eacute;pertoire distant (le conserver en local) apr&egrave;s l\'installation</font>'; 
	 break;	
		
}// fin de switch

////////////////////////////////////// FONTIONS //////////////////////////////////////////////
// supprime tout le contenu du dossier_principal sans supprimer le dossier_principal - fonction récurrente !
function deltree($dossier_principal,$dossier){
        //$dossier_principal = le dossier qui ne doit jamais être supprimé
		//$dossier prend le nom du sous-dossier du dossier principal - si le sous-dossier est vide, il est supprimé
		if(($dir=opendir($dossier))===false)
            {
			return;// sortie de fonction
			}
        
		while($name=readdir($dir))
			{
            if($name==='.' || $name==='..')continue;// le sous-deossier est vide, sortie de boucle et on le supprime
            $full_name=$dossier.'/'.$name;// construit le chemin 
            
			// si c'est un dossier, on rappelle la fonction pour ouvrir le sous-dossier
			if(is_dir($full_name))deltree($dossier_principal,$full_name);
            // si ce n'est pas un dossier on supprime son contenu de fichiers
			else @unlink($full_name);//on supprime le fichier
            }
 
        closedir($dir);
 		
		if ($dossier_principal!==$dossier)// on préserve le dossier principal qui ne sera pas supprimé
			{
				@rmdir($dossier);// on supprime le sous-dossier
				deltree($dossier_principal,$dossier_principal);// on rappelle la fonction
			}
echo 'Suppression du contenu de '.$dossier_principal.' effectu&eacute;e.';				
}
?>
</body></html>
</body>
</html>