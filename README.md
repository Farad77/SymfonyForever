# SymfonyForever — MMO 3D Navigateur (Île de La Réunion)

MMORPG 3D multijoueur dans le navigateur construit sur **Symfony 7.4** (backend autoritaire 100 % HTTP + Doctrine ORM + Symfony Security & Lock) et **Three.js / TypeScript** (rendu 3D temps réel).

## Fonctionnalités

- **Monde 3D de La Réunion** : heightmap 16-bit et carte des biomes partagées entre PHP (`ReunionTerrainService`) et Three.js (`TerrainSampler`) : Lagon de l'Ouest (Saint-Gilles), Cirques de Mafate, Salazie et Cilaos, Sud Sauvage, Piton des Neiges (3 070 m), Plaine des Sables et Piton de la Fournaise (Cratère Dolomieu).
- **Réseau Multijoueur 100 % HTTP** : synchronisation unifiée (`POST /api/world/sync` à ~3 Hz) + simulation de zone opportuniste verrouillée (`Symfony\Component\Lock` à 1 Hz, sans démon à maintenir) + interpolation 3D fluide à 60 FPS.
- **3 Classes (Trinité MMO)** & **12 Compétences** :
  - **Tank — Gardien du Basalte** : génération d'aggro ×2,2, *Heurt de Bouclier*, *Provocation du Piton (Taunt)*, *Peau de Basalte (-40 % dégâts)*, *Secousse Tellurique (AoE)*.
  - **DPS — Lame de Lave** : *Frappe Incandescente*, *Marque de Braise (DoT)*, *Fureur du Volcan (Exécution < 35 % PV)*, *Pluie de Scories (AoE)*.
  - **Heal — Tisaneur des Cirques** : *Éclat Solaire*, *Tisane de Faham (Soin direct)*, *Brume de Salazie (HoT)*, *Bénédiction du Lagon (Soin de groupe)*.
- **Leveling, Statistiques & Groupes** : montée de niveau avec croissance automatique par classe + 3 points de stats libres par niveau (`VIT`, `STR`, `INT`, `DEF`, `SPI`), table de menace, IA des monstres (aggro, poursuite, leash, respawn), groupes jusqu'à 5 joueurs (+10 % XP/allié) et chat (`Zone`, `Global`, `Groupe`, `Combat`).

## Contrôles en Jeu

- **ZQSD** (ou flèches) : Se déplacer · **Espace** : Sauter
- **Clic droit maintenu** : Orienter la caméra · **Molette** : Zoom
- **Clic gauche** ou **Tab** : Cibler un ennemi / joueur · **F1–F5** : Cibler soi ou un membre du groupe
- **1–4** : Lancer les compétences · **C** : Fiche personnage & attribution des stats · **Entrée** : Chat

## Installation & Lancement

### Avec Docker

Avec Docker Desktop (ou Docker Engine et Docker Compose) démarré :

```bash
docker compose up --build -d
```

Ouvrez [http://localhost:8080](http://localhost:8080). Au premier lancement,
le schéma SQLite et le monde sont initialisés automatiquement. Les données et
les sessions sont conservées dans le volume Docker `app_data`.

Pour consulter les logs ou arrêter l'application :

```bash
docker compose logs -f app
docker compose down
```

`docker compose down` conserve les données ; `docker compose down -v` les
supprime. PostgreSQL reste disponible avec le profil optionnel `postgres` pour
une configuration personnalisée ; l'application Docker utilise SQLite.
Pour une exposition en production, configurez un `APP_SECRET` propre au
déploiement au lieu de la valeur par défaut du projet.

### Installation locale

```bash
composer install
npm install
php bin/console doctrine:schema:update --force
php bin/console app:world:generate-map
php bin/console app:world:seed
npm run build
symfony local:server:start --no-tls --port=8000
```

Ouvrez ensuite [http://127.0.0.1:8000](http://127.0.0.1:8000) dans un ou plusieurs navigateurs (ou fenêtres privées) pour tester le multijoueur !

## Tests Automatisés

```bash
php vendor/bin/phpunit
npm run typecheck
```
