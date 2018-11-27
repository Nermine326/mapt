<?php
if (session_start()!= true){session_start();}
$permission=false;
//On va vérifier :
//Si le jeton est présent dans la session et dans le formulaire
if(isset($_SESSION['token']) && isset($_SESSION['token_time']) && isset($_POST['token']))
{
	//Si le jeton de la session correspond à celui du formulaire
	if($_SESSION['token'] == $_POST['token'])
	{
		//On stocke le timestamp qu'il était il y a 10 minutes
		$timestamp_ancien = time() - (10*60);
		
		//Si le jeton n'est pas expiré
		if($_SESSION['token_time'] >= $timestamp_ancien)
		{
			$permission=true;
			$token=$_POST['token'];
		}
	}
}
else
{echo "Session expir&eacute;e !";}
?>