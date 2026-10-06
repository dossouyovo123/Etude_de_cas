# API de gestion des demandes d'actes administratifs

Étude de cas DEP/ASIN 2026 - José Mario DOSSOU-YOVO

## Prérequis
- PHP >= 8.2, Composer, extension SQLite activée

## Installation et démarrage
```bash
git clone <URL_DU_DEPOT> && cd <dossier>
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```
L'API est disponible sur http://127.0.0.1:8000/api

## Tests
```bash
php artisan test
```

## Règles de gestion
- NPI : exactement 10 chiffres
- Types d'acte : acte_naissance, casier_judiciaire, certificat_residence
- Nombre de copies : entre 1 et 5
- Cycle de vie : deposee -> en_cours -> validee | rejetee
- Un statut final ne peut plus changer ; un rejet exige un motif

## Endpoints
| Méthode | Route | Description |
|---|---|---|
| POST | /api/demandes | Déposer une demande (statut initial : deposee) |
| GET | /api/demandes/{id} | Détail d'une demande |
| GET | /api/usagers/{npi}/demandes?statut= | Demandes d'un usager, plus récente d'abord, 20 par page |
| PATCH | /api/demandes/{id}/statut | Faire avancer une demande |
| GET | /api/demandes/statistiques | Nombre de demandes par statut |

### Exemple : dépôt
```bash
curl -X POST http://127.0.0.1:8000/api/demandes \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"npi":"1234567890","type_acte":"acte_naissance","nombre_copies":2}'
```

### Exemple : rejet
```bash
curl -X PATCH http://127.0.0.1:8000/api/demandes/1/statut \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"statut":"rejetee","motif":"Pièces manquantes"}'
```

Codes de retour : 201 création, 200 succès, 404 introuvable, 409 transition interdite, 422 données invalides.

## Choix techniques
- Laravel + SQLite : démarrage sans serveur de base de données
- Enums PHP pour les statuts et types d'acte ; la machine d'états est dans `StatutDemande`
- Validation via Form Requests, réponses d'erreur en JSON avec messages en français

## État d'avancement
- Fonctionne : dépôt, consultation avec filtre, cycle de vie, pagination, statistiques, tests
- Bonus réalisés : (à compléter)
- Manque : (à compléter : authentification, par exemple, et pourquoi)