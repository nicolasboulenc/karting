# Kart-Stats

***

##To Do

  * Faire un script pour charger tous les csvs en utlisant header(add_times.php)
  * Warning: unlink(images/drivers/44.jpg) [function.unlink]: No such file or directory in D:\Dev\www\karting-2.0\profile.php on line 84
  * Ajouter le nombre de personnes qui suivent le pilote ds la page de profil
  * De meme pour la page des pilotes

  * Code Maintenance
  >* changer toutes les pages pour ne pas utiliser les cookie mais Auth
  >* Verifier que la classe database log tout le sql...

  * Graphs
  >* Gerer les data labels
  >* Gerer les dates

  * Securite
  >* Envoyer erreur 404 si id n'existe pas (driver, track, etc)
  >* Tester tous les $_request meme, incluant ceux qui ne sont pas attendu
  >* Escaper sql?
  * Corriger probleme de graph/temps historique ou le kart est code en dur dans la requete
  * Look into cookie domain option in order to avoid conflict with other websites

_21-Oct-2014_
  * Finir validation dans input_session.php


_10-Oct-2014_
  * Re-fait toute l'insertion de temps et calcul de stats
  * Corrige affichage d'entete de pilote si pas de sessions du tout.
  * Corrige messages avec bootstrap.
  * Corrige message d'erreur sur la page de login.

_09-Oct-2014_
  * Supprime KendoUI charts and replace Charts.js.

_06-Oct-2014_
  * Change page de stats, header = supprimer meileur classement
  >* Supprime les graphs de meilleur sessions et meilleur temps
  >* Supprime les rankings
  >* Renome meilleur session en meilleur temps moyen
  >* De meme pour les stats par session
  >* Inverse les colonnes meilleur temps et meilleur session

_20-Aug-2012_
  * Modifier la page de profil pour avoir tout dans le meme fichier et supprimer user_update.
  * Elaguer kendoui repertoire
  * Faire le tri dans repertoire img et css

_25-Jul-2012_
  * Corriger lorsque l'on clique sur register puis sur login, apres login le site nous renvoit vers la page de register.
  * Deplacer html templates dans includes
  * Cree un repertoire admin pour gerer access via .htaccess
  * Ajoute gestion des erreur dans le login
  * Ajoute gestion des erreur dans l'enregistrement
  * Ajoute tout le html dans template, avec style display: none.

_24-Aug-2010_
  * Corrige probleme de date de modification au bas des pages.
  * Corrige probleme de graph success sur page de circuits.

_05-Apr-2010_
  * Corrige le bug des succes medailles.
  * Change la gestions des succes, circuit maintenant en base.

_04-Apr-2010_
  * Corrige bug ou les sessions n'etaient pas arrangees pas date.
  * Corrige bug meilleur classement.

_03-Apr-2010_
  * Modifie charts.func.php en classe statique.
  * Tous les graphs sont maintenant generes a partir de la page chart.php.
  * Modifie icone bar_chart.png.
  * Ajoute graph de ratio des succes par circuit (x=succes, y=ratio des pilotes qui ont le succes).
  * Ajoute l'historique du meilleur temps par circuit.
  * Modife classe pChart pour gerer les retours a la ligne sur l'axe des x.
  * Modife le graph de progression pour utiliser le retour a la ligne.

_20-Mar-2010_
  * Page circuits terminee.
  * Creation de la partie admin disponible a partir du site.
  * Suppression des graphs a partir de la section admin.
  * Code php modifie, classes pour drivers et tracks.
  * Modifier source pour que les fonctions stats renvoient des int/float ensuite formatte.

_13-Mar-2010_
  * Ajoute les labels sur les graphs de sessions.
  * Corrige format date dans le titre des graphs de session.
  * Corrige format date de l'axe x dans le graph de progression.
  * Change le position de la legende dans les charts de ligne; maintenant en haut a droite.
  * Cree image par default pour les circuit qui n'ont pas de photo.
  * Ajoute nombres de sessions a cote du temps par circuit/kart dans la partie "Statistiques par Circuit".
  * Ajoute nombre de pilotes dans les classements par circuit/kart dans la partie "Statistiques par Circuit".
  * Ajoute nombre de tours a cote du temps par session dans la partie "Statistiques par Session".
  * Ajoute nombre de pilotes dans les classements par session dans la partie "Statistiques par Session".
  * Change le curseur de selection de texte dans la page pilote; maintenant default.
  * Ajoute ratio des pilotes qui ont le succes dans la partie "Succes".
  * Ordre et description des succes passe en variables config.

_07-Mar-2010_
  * Images pilotes redimensionees.

_06-Mar-2010_
  * Mise a jour du design du site.
  * Mise a jour du design de graphs.
  * Mise a jour de l classe pChart pour gerer la grille verticale en option.
  * Les graphs ne sont que s'il n'existaient pas - images/charts/md5().png.
  * Ajout de gestion de version a partir du fichier todo.txt et variable Config::$Version.
  * La date de modification des scriptes s'affiche maintenant au bas de la page.
  * Netoyage de css.

_24-Aug-2009_
  * Corrige probleme de decimales dans le formatage du temps moyen (tools.func.php>FormatMillisec).
  * Corrige lien casse lorsque le pilote n'as pas de photo, affiche "Mistery M..png" (html.func.php>DisplayDrivers).
  * Limite nombre de success, plus afifichage du ratio de reussite sur la page pilote.
  * Variable d'acces aux images pilotes dispo a partir de config.class.php.
  * Variable d'acces aux images circuits dispo a partir de config.class.php.

***

##Git Stuff


  * Tutorial to setup GitHub
	http://blog.developpez.com/philippe/p11266/divers/utiliser_github_for_windows_avec_bitbuck

  * Clone the project
    `git clone https://nicolasboulenc@bitbucket.org/nicolasboulenc/cthulhu_game.git` [Enter]

  * Create a folder and init git
    `git init` [Enter]

  * Update your local repository to the newest commit
    `git pull` [Enter]

  * Add files to Git by executing the following command
    `git add .` [Enter]

  * Create a commit which can be pushed to the remote you just added
    `git commit -m 'added new files'` [Enter]

  * Push the commit the remote
    `git push origin master` [Enter]

  * Add a remote to this project
    `git remote add origin ssh://git@altssh.bitbucket.org:443/nicolasboulenc/cthulhu_game.git` [Enter]

*Happy coding!*
