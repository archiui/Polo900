CollectiveAccess README
-----------------------

[![Build Status](https://secure.travis-ci.org/collectiveaccess/providence.png?branch=master)](http://travis-ci.org/collectiveaccess/providence)

Thank you for downloading Providence version 1.6.2!

Version 1.6.1 is a maintenance release. A list of fixes can be found at http://clangers.collectiveaccess.org/jira/browse/PROV-1519?jql=fixVersion%20%3D%20%221.6.1%22
 
Providence is the “back-end” cataloging component of CollectiveAccess, a web-based suite of applications providing a framework for management, description, and discovery of complex digital and physical collections.  Providence is highly configurable and supports a variety of metadata standards, data types, and media formats.  

CollectiveAccess is freely available under the open source GNU Public License, meaning it’s not only free to download and use but that users are encouraged to share and distribute code.

Note that 1.6 is the first version of CollectiveAccess compatible with PHP 7.


----Useful Links:----

   Web site: http://collectiveaccess.org
   
   Documentation: http://docs.collectiveaccess.org/wiki/Main_Page
   
   Demo: http://demo.collectiveaccess.org/

   Installation instructions: http://docs.collectiveaccess.org/wiki/Installing_Providence

   Upgrade instructions: http://docs.collectiveaccess.org/wiki/Upgrading_Providence

   Release Notes for 1.6:  http://docs.collectiveaccess.org/wiki/Release_Notes_for_Providence_1.6
   
   Fixed in version 1.6.1: http://clangers.collectiveaccess.org/jira/browse/PROV-1519?jql=fixVersion%20%3D%20%221.6.1%22

   Forum: http://www.collectiveaccess.org/support/forum

   Bug Tracker: http://clangers.collectiveaccess.org/jira
   
   Requisiti di sistema per l'installazione: https://docs.collectiveaccess.org/wiki/Requirements


----Other modules:----

   Pawtucket: https://github.com/collectiveaccess/pawtucket2 (The public access front-end application for Providence)


Istruzioni
-----------------------
* clonare il repository
* cp setup.php-dist setup.php
* settare i puntamenti al db su setup.php
* andare sul sito e far partire l'installer (http://nomeinstallazione/install.php)
* selezionare come profilo polodel900
* Loggati come amministrazione
* Settari i ruoli e i gruppi di accesso all'amministratore da "Gestisci -> profili di accesso"
* Settare le varie ACL desiderate.


Abilitare ACL
-----------------------
Per abilitare il meccanismo delle ACL nell'installazione bisogna modificare il file di configurazione: ```app/conf/app.conf```.
I passaggi necessari sono:
* copiare il file ```app/conf/app.conf``` in ```app/conf/local/```
* aprire il suddetto file
* modificare le opzioni come segue:
```
ca_objects_preferred_label_type_list = tipologia_titolo
perform_item_level_access_checking = 1
default_item_access_level = __CA_ACL_EDIT_DELETE_ACCESS__
```
* aggiungere le impostazioni seguenti:
```
ca_item_access_level = __CA_ACL_NO_ACCESS__
set_access_user_groups_for_ca_objects = 1
set_access_user_groups_for_ca_entities = 0
set_access_user_groups_for_ca_places = 0
set_access_user_groups_for_ca_occurrences = 0
set_access_user_groups_for_ca_collections = 0
set_access_user_groups_for_ca_loans = 0
set_access_user_groups_for_ca_movements = 0
set_access_user_groups_for_ca_object_lots = 0
set_access_user_groups_for_ca_object_representations = 0
set_access_user_groups_for_ca_representation_annotations = 0
set_access_user_groups_for_ca_storage_locations = 0
set_access_user_groups_for_ca_tour_stops = 0
set_access_user_groups_for_ca_tours = 0

access_from_parent = 1
```
* copiare il file ```app/conf/assets.conf``` in ```app/conf/local/```
* aprire il suddetto file
* inserire le seguenti righe nella sezione "packages":
```
 treejs = {
	    jstree = jstree.js,
	    themes = themes/proton/style.css,
	    bootstrap = libs/bootstrap/css/bootstrap-grids.min.css
	} 
```
* inserire le seguenti righe nella sezione "loadSets":
```
	treejs = [
        treejs/jstree, treejs/themes
	]
```

Copiare le impostazione di Polo900
-----------------------
Se si ha la necessità di copiare la configurazione identica dell'installazione di Polo del 900 si può semplicemente copiare il file ```app.conf``` dentro la cartella ```app/conf/local/```. Ovviamente se si sceglie questa strada non serve abilitare le **ACL** inquanto già abilitate nel file  ```app.conf```
