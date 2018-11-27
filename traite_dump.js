//la fonction appel_dump_xhr reçoit 3 ou 4 paramètres 
//page_php = "nom de la page php qui recevra les données par "POST"
//id = nom du <div id = "le nom de l'id"></div> dans la page principale
//num_form = un nombre qui permet de traiter les paramètres via le switch de ce script
//nom_fichier = le nom du fichier uploader uniquement pour la procédure d'upload 

function appel_dump_xhr(page_php,id,num_form,nom_fichier)
{
  	var xhr = null; 
	 
	if(window.XMLHttpRequest) 
		{
			xhr = new XMLHttpRequest(); 
		}
	
	else 
	{ // XMLHttpRequest non supporté par le navigateur 
	   alert("Votre navigateur ne supporte pas les objets XMLHttpRequest..."); 
	   xhr = false;
	   return; 
	}
    
	document.body.style.cursor = "wait";
	switch (num_form)
	{
		case 1:
		var params="valeur=1";
		break;
		
		case 2:
		var params="nom_base="+document.getElementById("choix_base").value;
		if (document.getElementById("choix_base").value=="")
			{
			alert("Aucune base de données n'a été sélectionnée !");
			document.body.style.cursor = "default";
			return;
			break;
			}
		var mode=3;
		if (document.getElementById("choix_struct_1").checked){mode=1;}
		params=params+"&mode="+mode;
		break;
		
		case 3:
		var params="nom_base="+document.getElementById("nom_base_2").value+"&mode="+document.getElementById("mode_2").value+"&nom_fichier_dump="+document.getElementById("nom_fichier_dump").value;
		break;
		
		case 4:
		var params="nom_base="+document.getElementById("nom_base_2").value+"&mode="+document.getElementById("mode_2").value+"&nom_fichier_dump="+document.getElementById("nom_fichier_dump_2").value;
		break;
		
		case 5:
		if (confirm("Attention, vous allez vider le journal des backups - Confirmer ?"))
		{
			var params="valeur=1";
			break;
		}
		else
		{
			return;
			break;
		}
		break;
		
		case 6:
		var params="nom_base="+document.getElementById("choix_base").value;
		if (document.getElementById("choix_base").value=="")
			{
			alert("Aucune base de données n'a été sélectionnée !");
			document.body.style.cursor = "default";
			return;
			break;
			}
		break;
		
		case 7:
		var params="nom_base="+document.getElementById("choix_base").value;
		break;
		
		case 8:
		var params="nom_fichier_zip="+document.getElementById("nom_fichier_zip").value;
		break;
		
		case 9:
		var params="nom_fichier="+nom_fichier;
		break;
		
		case 10:
		var params="nom_fichier_sql="+document.getElementById("nom_fichier_sql").value;
		break;
	}
	
	// ajoute un jeton de sécurité
	var token="&token="+document.getElementById("token").value;
	params=params+token;
	
	xhr.open('POST',page_php,true);
   	xhr.ontimeout = timeoutRaised;
	xhr.onreadystatechange = function ()
		{
		if (this.readyState == 4 && this.status == 200)	
			{
			var e = document.getElementById(id);
			//On ajoute le contenu de la réponse dans le Dom du document
			e.innerHTML = xhr.responseText;
			//On évalue le javascript contenu dans les dom
			var scripts = e.getElementsByTagName('script');
			for(var i=0; i < scripts.length;i++)
				{
				//Sous IE il faut faire un execScript pour que les fonctions soient définies en global		
				if (window.execScript)
					{
					//On replace les éventuels commentaires html car IE n'aime pas ça
					window.execScript(scripts[i].text.replace('<!--',''));
					
					}			
				//Sous les autres navigateurs on fait un window.eval			
				else
					{				
					window.eval(scripts[i].text);
					}		
				}	
			}
		}
	xhr.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	xhr.setRequestHeader("Content-length", params.length);
   	xhr.setRequestHeader("Connection", "close");
   	xhr.send(params);
   
   	document.body.style.cursor = "default";
}

function timeoutRaised()
{
    alert("timeout");
}

