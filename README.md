# voisin_Sidibe_Souleymane

Installer Xampp
Copier le dossier du projet dans le htdocs de xampp

Configuration du virtualHost dans Apache

Editer le fichier apache conf extra httpd vhosts conf qui se trouve dans votre repertoire Apache  et y ajoutez  

<VirtualHost *:80>
    ##ServerAdmin webmaster@dummy-host2.example.com
    DocumentRoot "C:\xampp\htdocs\voisin\public"
    ServerName rest-api-voisin
    ##ErrorLog "logs/dummy-host2.example.com-error.log"
    ##CustomLog "logs/dummy-host2.example.com-access.log" common
</VirtualHost>

Ajouter la ligne suivante dans le fichier hosts : 
127.0.0.1   rest-api-application

Et lancer Xampp
