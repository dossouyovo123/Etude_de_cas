# API de gestion des demandes d'actes administratifs

Étude de cas DEP/ASIN 2026 - José Mario DOSSOU-YOVO

API REST (Laravel) permettant de déposer, consulter et faire avancer des demandes d'actes administratifs : acte de naissance, casier judiciaire, certificat de résidence.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Extension PHP SQLite activée

## Installation et démarrage

Depuis le dossier du projet (celui qui contient le fichier `artisan`) :

```bash
composer install
copy .env.example .env        # Linux/Mac : cp .env.example .env
php artisan key:generate
php artisan migrate           # répondre "yes" si la création de database.sqlite est proposée
php artisan serve
```

L'API est disponible sur http://127.0.0.1:8000/api

## Tests

```bash
php artisan test
```

## Règles de gestion

- NPI : exactement 10 chiffres
- Type d'acte : `acte_naissance`, `casier_judiciaire` ou `certificat_residence`
- Nombre de copies : entre 1 et 5
- Cycle de vie : `deposee` -> `en_cours` -> `validee` ou `rejetee`
- Un statut final (`validee`, `rejetee`) ne peut plus changer
- Un rejet exige un motif
- Toute valeur invalide ou action interdite est refusée avec un message clair

## Endpoints

| Méthode | Route | Description |
|---|---|---|
| POST | /api/demandes | Déposer une demande (statut initial : deposee) |
| GET | /api/demandes/{id} | Détail d'une demande |
| GET | /api/usagers/{npi}/demandes?statut= | Demandes d'un usager, de la plus récente à la plus ancienne, 20 par page, filtre facultatif par statut |
| PATCH | /api/demandes/{id}/statut | Faire avancer une demande |
| GET | /api/demandes/statistiques | Nombre de demandes par statut |

Codes de retour : 201 création, 200 succès, 404 introuvable, 409 transition interdite, 422 données invalides.

Ajouter l'en-tête `Accept: application/json` à chaque requête.

### Exemple : déposer une demande

```json
POST /api/demandes
{
  "npi": "1234567890",
  "type_acte": "acte_naissance",
  "nombre_copies": 2
}
```

PowerShell :

```powershell
$h = @{ "Accept" = "application/json" }
$body = '{"npi":"1234567890","type_acte":"acte_naissance","nombre_copies":2}'
Invoke-RestMethod -Method Post -Uri http://127.0.0.1:8000/api/demandes -Headers $h -ContentType "application/json" -Body $body
```

### Exemple : faire avancer une demande

```powershell
Invoke-RestMethod -Method Patch -Uri http://127.0.0.1:8000/api/demandes/1/statut -Headers $h -ContentType "application/json" -Body '{"statut":"en_cours"}'
```

Rejet (motif obligatoire) :

```powershell
Invoke-RestMethod -Method Patch -Uri http://127.0.0.1:8000/api/demandes/1/statut -Headers $h -ContentType "application/json" -Body '{"statut":"rejetee","motif":"Pièces manquantes"}'
```

### Exemple : consulter les demandes d'un usager

```powershell
Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/usagers/1234567890/demandes?statut=en_cours" -Headers $h
```

## Écran de consultation

La liste des demandes d'un usager est aussi affichable dans le navigateur :

http://127.0.0.1:8000/usagers/1234567890/demandes

## Choix techniques

- Laravel et SQLite : le jury peut démarrer l'application sans serveur de base de données.
- Une seule table `demandes` : l'usager n'est identifié que par son NPI, et les types d'acte et statuts sont des valeurs fixes.
- Énumérations PHP (`StatutDemande`, `TypeActe`) : la machine d'états du cycle de vie est portée par `StatutDemande`.
- Validation par des Form Requests, avec des messages d'erreur en français et des réponses d'erreur en JSON.

## État d'avancement

Fonctionne :
- dépôt d'une demande avec validation du NPI, du type d'acte et du nombre de copies
- consultation des demandes d'un usager, triées de la plus récente à la plus ancienne, avec filtre par statut
- cycle de vie complet avec refus des transitions interdites et motif obligatoire pour un rejet

Bonus réalisés :
- pagination à 20 demandes par page
- nombre de demandes par statut
- écran simple de consultation
- tests automatisés des règles de gestion (voir `tests/Feature/DemandeApiTest.php`)
