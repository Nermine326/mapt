<?PHP
// teste l'existence du token
if (is_file("verif_token.php")){include("verif_token.php");}else{exit();}
if ($permission != true){echo "Session expir&eacute;e !";exit();}
?>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html' charset='utf-8'>

<title>Upload page</title>
</head>
<body>
<?php
if(!isset($_FILES['file_zip']))echo'stop';
$nomFichier = $_FILES['file_zip']['name'];
$faute="";
if ($nomFichier=="")
	{
		$faute="Le nom du fichier est vide !<br />";
		//exit();
	}
// Extension du fichier seulement .zip ou .sql
$extension = strrchr($nomFichier, ".");
if ($extension != ".zip" && $extension != ".sql")
	{
		$faute=$faute."L'extension doit être .sql ou .zip !<br />";
		//exit();
	}

// Taille maximale de fichier, valeur en bytes					
$MAX_SIZE = 500000;
// Poids du fichier en ko
$poidsFichier = $_FILES["file_zip"]["size"] ;
if ($poidsFichier > $MAX_SIZE)
	{
		$faute=$faute."Le poids du fichier &gt; 500 ko !<br />";
		//exit();
	}

// code erreur de upload
$codeErreur = $_FILES["file_zip"]["error"] ;
	
// nom temporaire du fichier uploadé dans le dossier temporaire du serveur web
$nomTemporaire = $_FILES['file_zip']['tmp_name'] ;
// nom du chemin relatif du dossier de réception sur le site du fichier uploadé
$dir_backup=htmlspecialchars($_POST["folder"]);

// transfert du fichier uploadé du dossier temporaire vers le dossier du site web avec nouveau nom pour le ficiher uploadé
if ($faute=="")$uploadOk = move_uploaded_file($nomTemporaire,$dir_backup.$nomFichier);
	
if(!$uploadOk || $faute!="")
	{
		// en cas d'erreur la variable $nomFichier contient alors le code erreur
		if ($codeErreur > 0)
		{
			$nomFichier='Erreur : '.$codeErreur.'<br />'.$faute;
		}
		else
		{
			$nomFichier='Faute : '.$faute;
		}
		
	}

// appel la fonction appel_dump_xhr() avec le retour sur la page parente de iframe invisible
echo '<script language="javascript" type="text/javascript">window.top.window.appel_dump_xhr("restore_xhr_mysqli.php?action=3&connectDB=1","affichage",9,"'.$nomFichier.'")</script>';
?>
</body>
</html>
