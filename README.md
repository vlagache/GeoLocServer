# GeoLocServer
Partie Serveur de l'application GeoLoc - Symfony 4.3.1
***
__Déploiement__


- cloner le repository dans le dossier de votre choix
```
git clone https://github.com/vlagache/GeoLocServer.git
```
-  installer composer : https://getcomposer.org/download/ 
- 
```
composer install
```
pour installer les dependances du projet dans le dossier vendor
- créer une fichier config.ini à la racine de votre projet et mettre dedans : 
```
apiKeyGoogle = votre clé pour l'Api Google Geocoding
```
( pour avoir une clé d'api Google : https://developers.google.com/maps/documentation/javascript/geocoding#ReverseGeocoding )

- Obtenir une clé privé pour les services Firebase au format JSON ( https://firebase.google.com/docs/admin/setup )  et
mettre ce fichier .json à la racine de votre projet

- Configurer le fichier .env

```
DATABASE_URL= votre base de données
GOOGLE_APPLICATION_CREDENTIALS='../nom du fichier json pour les services firebases'

```

