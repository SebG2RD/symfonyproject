# Projet Symfony

## Prerequis

- PHP >= 8.2
- Composer
- Base de donnees MySQL (par defaut dans `.env`) ou PostgreSQL via Docker
- Symfony CLI (optionnel) pour lancer le serveur local

## Installation

1) Installer les dependances PHP

```bash
composer install
```

2) Configurer les variables d'environnement

Copier les variables sensibles dans `.env.local` puis ajuster la base de donnees.

Exemple MySQL (par defaut) :

```
DATABASE_URL="mysql://root:@localhost:3306/blog_symfony?serverVersion=8.0.32&charset=utf8mb4"
```

Exemple PostgreSQL (si vous utilisez Docker) :

```
DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

3) Creer la base puis appliquer les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4) Charger les fixtures (optionnel)

```bash
php bin/console doctrine:fixtures:load
```

5) Lancer le serveur

```bash
symfony server:start
```

Ou avec le serveur PHP integre :

```bash
php -S localhost:8000 -t public
```

## Services Docker (optionnel)

Le projet fournit une configuration Docker Compose pour PostgreSQL et Mailpit.

```bash
docker compose up -d
```

- PostgreSQL expose sur `5432`
- Mailpit expose sur `8025` (UI) et `1025` (SMTP)

Si vous utilisez PostgreSQL, mettez a jour `DATABASE_URL` (voir ci-dessus).

## Tests

```bash
php bin/phpunit
```

## Endpoints utiles

- Accueil : `http://localhost:8000/`
- API Platform : `http://localhost:8000/api`
