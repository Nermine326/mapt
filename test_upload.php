<html>
<head>
<meta http-equiv='Content-Type' content='text/html' charset='utf-8'>
<title>Test Upload page</title>
</head>
<body>
<?php
// empêche l'affichage des erreurs - à mettre en remarque pendant le développement
ini_set('display_errors', '0');

if(!isset($_FILES['fichier']))
	{
	echo'Le poids du fichier est trop important (limit&eacute; par l\'h&eacute;bergeur du site web)<br />Upload pas permis !';
	exit();
	}

// récupération du nom du fichier uploadé
$nomFichier = $_FILES['fichier']['name'];
$faute=0;
$info ='Nom du fichier upload&eacute; : '.$nomFichier;

if ($nomFichier=="")
	{
		$info = $info.'<br />Le nom du fichier est vide !';
		$faute=1;
	}

// Extension du fichier seulement .zip ou .sql
$extension = strrchr($nomFichier, ".");
if ($extension != ".zip" && $extension != ".sql")
	{
		$info = $info.'<br />Attention, seules les extensions .zip ou .sql sont admises !';
		$faute=1;
	}

// Taille maximale de fichier, valeur en bytes					
$MAX_SIZE = 500000;// 500 ko = 0,5 Mo = 500 000 o

// Poids du fichier en octets
$poidsFichier = $_FILES["fichier"]["size"] ;
if ($poidsFichier > $MAX_SIZE)
	{
		$MAX_SIZE=$MAX_SIZE/1000;
		$info = $info.'<br />Fichier de poids trop important (maximum = '.$MAX_SIZE.' ko)  !';
		$faute=1;
	}

// code erreur de upload
$codeErreur = $_FILES["fichier"]["error"] ;
	
// nom temporaire du fichier uploadé dans le dossier temporaire du serveur web
$nomTemporaire = $_FILES['fichier']['tmp_name'] ;
$info = $info.'<br />Nom du fichier temporaire et chemin du dossier temporaire sur le serveur : '.$nomTemporaire; 

// nom du chemin relatif du dossier de réception sur le site du fichier uploadé
$dir_backup=htmlspecialchars($_POST["folder"]).'/';
$info = $info.'<br />Chemin relatif du dossier backup sur le serveur : '.$dir_backup; 

// transfert du fichier uploadé du dossier temporaire vers le dossier du site web avec nouveau nom pour le ficiher uploadé
$uploadOk=false;
if ($faute==0)$uploadOk = move_uploaded_file($nomTemporaire,$dir_backup.$nomFichier);
	
if(!$uploadOk || $faute>0)
	{
		echo $info;
		if ($codeErreur>0)echo '<br />Erreur : '.$codeErreur.' - <a href=\'http://php.net/manual/fr/features.file-upload.errors.php\'>Infos sur les erreurs</a>';
		echo '<br />L\'upload n\'a pas &eacute;t&eacute; effectu&eacute; !';
	}
else
	{
		$info=$info.'<br />OK UPLOAD FONCTIONNE';
		echo $info;
	}
	
?>
</body>
</html>
