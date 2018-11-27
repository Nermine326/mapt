<?PHP
// on démarre une session 
if (session_start()!= true){session_start();}

//On génére un jeton unique s'il n'existe pas
if(!isset($_SESSION['token']))//la variable de session n'est pas encore initialisée
{
	$token = uniqid(rand(), true);
	//on le stocke dans la variable de session
	$_SESSION['token'] = $token;
	//On enregistre aussi le timestamp correspondant au moment de la création du token
	$_SESSION['token_time'] = time();
}
else
{
	$token=$_SESSION['token'];//prend la valeur du token de la session existante
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html' charset='utf-8'>
<TITLE>Sauvegarde bd mysql</TITLE>
<script type="text/javascript" src="js/traite_dump.js"></script>
</head>
<body>
<b>Effectuer une sauvegarde d'une base de données Mysql dans un fichier .sql contenu dans une archive .zip t&eacute;l&eacute;chargeable (h&eacute;bergeur &gt;&gt;&gt; local)</b>
<?PHP
// appel la procédure 6 - action=6
echo '<hr /><input type="button" value="V&eacute;rifie la connexion au serveur Mysql" name="button_0" id="button_0" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=6&connectDB=1\',\'affichage\',1)">';
echo ' - <input type="button" value="Basculer vers la proc&eacute;dure de restauration" name="button_0" id="button_0" style="width:300px;" onclick="window.location.href=\'restore_mysqli.php\'">';

// appel la procédure 1 - action=1
echo '<hr /><input type="button" value="Choix de la base de donn&eacute;es" name="button_1" id="button_1" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=1&connectDB=1\',\'affichage\',1)"> &lt;&lt;&lt; D&eacute;marrer la proc&eacute;dure en 4 phases';

// liste des 4 phases
echo '<ul><li>Phase 1 sur 4 - Choix de la base de donn&eacute;es et choix du mode de sauvegarde (structure seulement ou structure + donn&eacute;es)</li><li>Phase 2 sur 4 - Choix du nom du fichier de sauvegarde .sql et sauvegarde dans ce fichier</li><li>Phase 3 sur 4 - Construction de l\'archive .zip qui contiendra le fichier .sql avec un lien pour t&eacute;l&eacute;charger cette archive .zip</li><li>Phase 4 sur 4 - T&eacute:l&eacute;chargement de l\'archive .zip qui contiendra le fichier .sql via le lien propos&eacute;</li></ul>';

// appel la procédure 9 - action=9
echo '<input type="button" value="Vision du journal des backups" name="button_2" id="button_2" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=9&connectDB=1\',\'affichage\',1)">';

// appel la procédure 5 - action=5
echo '<br /><br /><input type="button" value="Reset l\'affichage de la proc&eacute;dure" name="button_2" id="button_2" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=5&connectDB=1\',\'affichage\',1)"> &lt;&lt;&lt; Affiche le contenu du dossier backup s\'il n\'est pas vide';

// appel la procédure 7 - action=7
echo '<br /><br /><input type="button" value="Supprime les fichiers du dossier backup" name="button_3" id="button_3" style="width:250px;" onclick="appel_dump_xhr(\'dump_xhr_mysqli.php?action=7&connectDB=1\',\'affichage\',1)"> &lt;&lt;&lt; A la fin de la proc&eacute;dure, par s&eacute;curit&eacute;, il est fortement conseill&eacute; de supprimer les fichiers de ce dossier';

// transmet la valeur du token par post
echo '<input type="hidden" name="token" id="token" value="'.$token.'">';

echo '<hr />';

echo '<font color="blue">Affichage du d&eacute;roulement de la proc&eacute;dure :</font><br />';
echo '<div id="affichage"></div>';
?>
</body></html>