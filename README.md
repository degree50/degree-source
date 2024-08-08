# Degree Platform

> English version of this document can be found [here](README_EN.md).

## Systemvoraussetzungen

- Linux Server
    - aktuelles Ubuntu (zB LTS)
    - mindestens 500 GB Speicher (Videos benötigen viel Speicherplatz)
    - mindestens 16 GB RAM
    - moderne, standard CPUs
- Docker mit Docker Compose Plugin
    - Installations-Anleitung: https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository

## Konfiguration

### Instanz Konfiguration

Um eine Instanz der Degree Platform zu konfigurieren, verwenden wir eine `.env`-Datei, um unsere Umgebungsvariablen zu
setzen.
Erstellen Sie eine `.env`-Datei im Root-Verzeichnis des Projekts. Nutzen Sie die `.env.example`-Datei als Vorlage.

Wichtig:

- Für Login via SAML SSO muss ein SAML SP (Service Provider) und IDP (Identity Provider) konfiguriert werden.
- Für E-Mail-Versand muss ein SMTP-Server konfiguriert werden.

### Imprint, Privacy Policy and Terms of Use

Um das Impressum, die Datenschutzerklärung und die Nutzungsbedingungen zu ändern, können Sie die folgenden Dateien
bearbeiten:

- `app/templates/Standard/DataPrivacy.html.twig`
- `app/templates/Standard/Imprint.html.twig`
- `app/templates/Standard/TermsOfUse.html.twig`

### Logo

Zum Austauschen des Logos muss die Datei `app/assets/images/logo.svg` ersetzt werden.

### Styles

Zum Ändern von visuellen Aspekten (Farben, Schriftart, etc) kann die
Datei `app/assets/scss/GeneralStyles/Variables.scss` angepasst werden.
Damit die Änderungen angewendet werden, müssen die Assets neu kompiliert werden (siehe unten).

## Development

Schauen Sie sich die Makefile an, um die verschiedenen Befehle zu sehen, die Sie zur Entwicklung der Plattform ausführen
können.

### Platform Local Starten

Stellen Sie sicher, dass die `.env`-Datei im Root-Verzeichnis des Projekts basierend auf der `.env.example`-Datei
vorhanden ist.

Um die Plattform lokal zu starten, können Sie die folgenden Befehle ausführen:

- `docker compose build`
- `docker compose up -d && docker compose logs -f`

> Die Plattform läuft hier: `http://localhost:8080`.

#### Fixtures

Um Datenbank-Fixtures zu laden, können Sie den folgenden Befehl ausführen:

- `make import-fixtures`

Dabei werden unter anderem 4 Benutzer erstellt:

- `admin@sandstorm.de / password`
- `student@sandstorm.de / password` oder `student2@sandstorm.de / password`
- `dozent@sandstorm.de / password`

#### Symfony Console

Die Symfony Console im Docker Container auszuführen, nutze `./symfony-console`.
Um beispielsweise eine neue Datenbankmigration zu erstellen, können Sie folgenden Befehl ausführen:

- `./symfony-console doctrine:migrations:generate`

### Frontend Kompilieren

Das Projekt nutzt FontAwesomePro. Siehe `app/.npmrc.example`.

Um die Assets zu kompilieren, müssen Sie die folgenden Tools installieren:

- Node Version Manager (nvm) (https://github.com/nvm-sh/nvm)
- Yarn (https://yarnpkg.com/)

```
cd app
nvm use
yarn
yarn build
```

> Nutze `yarn watch` um automatisch Änderungen zu erkennen und die Assets neu zu kompilieren.

#### Tests Local Ausführen

- Plattform lokal starten (siehe oben)
- e2e-testrunner starten
    - `cd e2e-testrunner`
    - `nvm use`
    - `npm i`
    - `node index.js`
- tests starten
    - `make test`

Tests können auch gezielt ausgeführt werden:

- `make test-integration`
- `make test-e2e`
- `make test-debug`
    - Führt alle Features oder Scenarios mit `@debug`-Annotation aus
