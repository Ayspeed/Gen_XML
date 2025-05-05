# xml\_generator

Ce projet Symfony permet de générer automatiquement :

* Les entités Doctrine (avec annotations et getters/setters)
* Les FormTypes Symfony
* Les templates Twig pour créer de nouveaux objets

à partir d'un fichier de configuration XML situé dans `config/orm.xml`.

---

## Prérequis

* PHP ≥ 8.1
* Composer
* [Symfony CLI](https://symfony.com/download) (optionnel mais recommandé)
* Base de données (MySQL, PostgreSQL, SQLite, etc.)

---

## Installation

1. **Cloner le dépôt**

   ```bash
   git clone <url-du-repo> xml_generator
   cd xml_generator
   ```

2. **Installer les dépendances**

   ```bash
   composer install
   ```

3. **Configurer la base de données**

   Copier le fichier `.env` et adapter la variable `DATABASE_URL` :

   ```dotenv
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
   ```

4. **Créer le schéma Doctrine (optionnel)**

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:schema:update --force
   ```

---

## Usage

1. **Placer le fichier XML**

   Déposer votre fichier de configuration `orm.xml` dans le dossier `config/`.

2. **Lancer la génération**

   Cette commande lit `config/orm.xml` et génère :

   * Entités dans `src/Entity/`
   * FormTypes dans `src/Form/`
   * Templates Twig dans `templates/{entité}/new.html.twig`

   ```bash
   php bin/console app:generate-from-xml
   ```

3. **Vider le cache**

   ```bash
   php bin/console cache:clear
   ```

4. **Démarrer le serveur web**

   Avec Symfony CLI :

   ```bash
   symfony server:start
   ```

   Ou avec PHP built-in server :

   ```bash
   php -S localhost:8000 -t public
   ```

5. **Accéder aux formulaires**

   * Créer un **Client** :  `http://localhost:8000/client/new`
   * Créer une **Command** : `http://localhost:8000/command/new`
   * Créer un **Produit** : `http://localhost:8000/produit/new`

---

## Structure du projet

```
config/
├─ orm.xml      ← Fichier de définition XML
src/
├─ Command/
│  └─ GenerateFromXmlCommand.php  ← Commande de génération
├─ Entity/
│  └─ ... (entités générées)
├─ Form/
│  └─ ... (FormTypes générés)
├─ Controller/
│  └─ FormController.php          ← Contrôleur d'affichage
templates/
├─ client/
│  └─ new.html.twig
├─ command/
│  └─ new.html.twig
├─ produit/
│  └─ new.html.twig
public/
├─ index.php
```

---

## Personnalisation

* Modifier `config/orm.xml` pour définir de nouvelles classes ou champs.
* Relancer `php bin/console app:generate-from-xml` pour mettre à jour le code.