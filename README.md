# 🚀 Application Dockerisée Symfony

Cette application est **dockerisée**.  
Elle est conçue pour être **indépendante de toute configuration spécifique à votre PC** 🐳




---

## 📁 Fichiers Docker à la racine du projet

- `Dockerfile`
- `compose.yaml`

---

## 🖼️ Images Docker utilisées

- **PHP** : `php:8.2`
- **MySQL** : `mysql:8.0`
- **Node.js** : `node:20`
- **NGINX** : `nginx:stable`
- **phpMyAdmin** : `phpmyadmin/phpmyadmin`

---

## 🛠️ Lancer le projet avec `make` (si `Makefile` installé)

Toutes les commandes commencent par `make` :  
Exemple : `make up`

---

## 🔧 Étapes d'installation

### 1️⃣ Lancer Docker :
```bash
docker-compose up -d --build
```

---

### 2️⃣ Accéder au terminal Symfony dans Docker :
```bash
docker-compose exec php bash
```

> Toutes les commandes Symfony/Composer/Node doivent être lancées **dans ce terminal**

---

### 3️⃣ Installer les dépendances PHP :
```bash
composer install
```

---

### 4️⃣ Installer les dépendances Node :
```bash
npm install
```

---

### 5️⃣ Exécuter les migrations de base de données :
```bash
php bin/console doctrine:migrations:migrate
```

---

### ❌ Pour quitter le terminal Docker :
```bash
exit
```

---

## 🌐 Ports exposés

| Service       | URL                        | Identifiants                   |
|---------------|----------------------------|--------------------------------|
| phpMyAdmin    | http://localhost:8082      | **user**: root<br>**pass**: root |
| Application   | http://localhost:8085      | via instructions in l'app     |

---

## 📌 Instructions supplémentaires

### 🔧 Création admin & produits
Suivez les instructions de l'application pour :
- Créer un **utilisateur admin**
- Ajouter **catégories** et **produits**

---

## ⛔ Arrêter Docker :
```bash
docker-compose down
```

---

## ⚠️ Si les ports sont déjà utilisés

Vous pouvez les modifier dans `compose.yaml` :

```yaml
phpmyadmin:
  ports:
    - "8083:80"  # <- exemple

nginx:
  ports:
    - "8086:80"  # <- exemple
```

Puis relancez avec :

```bash
docker-compose down -v --remove-orphans
docker-compose up -d --build
```

---

## 🛠️ Erreur Tailwind (build manquant)

Si vous obtenez cette erreur :

```
An exception has been thrown during the rendering of a template 
("Built Tailwind CSS file does not exist: run 
"php bin/console tailwind:build" to generate it") 
in base.html.twig at line 20.
```

➡️ Dans le terminal Docker :
```bash
php bin/console tailwind:build
```

---

## 🔁 Important !

- ❗ **Si vous exécutez les commandes Symfony depuis le terminal bash local**, vous devrez **reconstruire l’image** :
```bash
docker-compose up -d --build
```

- ✅ **Si vous utilisez le terminal Docker (`docker-compose exec php bash`)**, vos changements sont **pris en compte directement**, **sans rebuild**.

---

## ✅ Fin de l'installation 🎉
