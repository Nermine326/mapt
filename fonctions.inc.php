<?php
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

function liste_tables($connect,$nom_base,$dir_backup)
{
echo '<br />Nom de la base restaur&eacute;e : <strong>'.$nom_base.'</strong>';
echo '<br />Liste des tables restaur&eacute;es :';
$strrequete = "SHOW TABLES FROM ".$nom_base;
	$result = mysqli_query($connect,$strrequete);
	$i=0;
	while ($row = mysqli_fetch_row($result)) {
   		$i++;
		echo '<br />'.$i.' - Table : '.$row[0];
		}
	echo '<br />';
	if ($i==0)
		{
			echo '<br /><font color="blue">Attention, aucune table n\'a &eacute;t&eacute; restaur&eacute;e !</font><br />';
		}
	
	deltree($dir_backup,$dir_backup);
	
	//ajout dans le fichier de journalisation
	if (is_file("journal_dump.txt"))
	  	{
	  		$fp = fopen("journal_dump.txt","a"); 
	  		$date = date("d-m-Y");
			$heure = date("H:i");
			//$struct="complet (structure + données)";
			//if ($mode != 3)$struct="structure (sans les données)";
			$txt = 'Restauration du '.$date.' à '.$heure.' de la base '.$nom_base.' - '.$i.' tables restaurées'.chr(13).chr(13);
	  		fwrite($fp, $txt); 
	  		fclose($fp);
		}
}

function dumpMySQL($hote, $user, $password, $nom_base, $mode, $nom_fichier_dump, $dir_backup)
{
    $connect = mysqli_connect($hote, $user, $password);
    mysqli_select_db($connect,$nom_base);
 
    $entete = "-- ----------------------\n";
    $entete .= "-- dump de la base ".$nom_base." au ".date("d-M-Y")."\n";
    $entete .= "-- ----------------------\n\n";
    $creations = "";
    $insertions = "";
 
    $listeTables = mysqli_query($connect,"show tables");
    while($table = mysqli_fetch_array($listeTables))
    {
        // si l'utilisateur a demandé la structure ou la totale
        if($mode == 1 || $mode == 3)
        {
            $creations .= "-- -----------------------------\n";
            $creations .= "-- creation de la table ".$table[0]."\n";
            $creations .= "-- -----------------------------\n";
            $listeCreationsTables = mysqli_query($connect,"show create table ".$table[0]);
            while($creationTable = mysqli_fetch_array($listeCreationsTables))
            {
              $creations .= $creationTable[1].";\n";
            }
        }
        // si l'utilisateur a demandé l'insertion des données
        mysqli_free_result($listeCreationsTables);
		if($mode > 1)
        {
            $donnees = mysqli_query($connect,"SELECT * FROM ".$table[0]);
            $insertions .= "-- -----------------------------\n";
            $insertions .= "-- insertions dans la table ".$table[0]."\n";
            $insertions .= "-- -----------------------------\n";
            while($nuplet = mysqli_fetch_array($donnees))
            {
                $insertions .= "INSERT INTO `".$table[0]."` VALUES(";
                for($i=0; $i < mysqli_num_fields($donnees); $i++)
                {
                  if($i != 0){$insertions .=  ", ";}
                  if (!is_numeric($nuplet[$i])){$insertions .="'";}
				  $insertions .= addslashes($nuplet[$i]);
				  if (!is_numeric($nuplet[$i])){$insertions .="'";}
				}
                $insertions .=  ");\n";
            }
            $insertions .= "\n";
			mysqli_free_result($donnees);
				
				
        }
    }
	mysqli_free_result( $listeTables);
    mysqli_close($connect);
 	
    $fichierDump = fopen($dir_backup.$nom_fichier_dump, "w+");
    fwrite($fichierDump, $entete);
    fwrite($fichierDump, $creations);
    fwrite($fichierDump, $insertions);
    fclose($fichierDump);
    echo "Sauvegarde r&eacute;alis&eacute;e avec succ&egrave;s !";
	
}
function verifie_efface_fichiers($dir)
{
	$i=0;
 	if($dh = opendir($dir))
		{
     	while(($file = readdir($dh))!== false)
			{
         	if(file_exists($dir.$file)) 
				{
				 $i++;
				 if ($i == 3)echo 'Contenu du dossier backup :<br />';
				 if ($file != "." && $file !=".."){echo $file.'<br />';}
				
				}
     		}
        closedir($dh);
		}
	
		if ($i > 2)
			{	
			$j=$i-2;
			echo 'Il y a '.$j.' fichier(s) pr&eacute;sent(s) dans le dossier backup du site';
			echo '<br /><font color="blue">Par s&eacute;curit&eacute;, il est conseill&eacute; de supprimer le ou les fichier(s) de ce dossier !</font>';
			}
		
}
?>