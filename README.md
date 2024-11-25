# Degree Platform

> English version of this document can be found [here](README_EN.md).

## Systemvoraussetzungen

- Linux Server
    - Aktuelles Ubuntu (zB LTS)
    - Mindestens 500 GB Speicher (Videos benötigen viel Speicherplatz)
    - Mindestens 16 GB RAM
    - Moderne, standard CPUs
- Docker mit Docker Compose Plugin
    - Installations-Anleitung: https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository
    - Docker wird üblicherweise mit Root-Rechten ausgeführt. Mehr Informationen: 
https://docs.docker.com/engine/install/linux-postinstall/#manage-docker-as-a-non-root-user
- Alle weiteren Abhängigkeiten werden in den Docker-Containern automatisch installiert.

### Warum Docker?

Mit Docker haben wir die Möglichkeit, die Degree Platform auf jedem System zu starten, das Docker unterstützt. Alle
Abhängigkeiten sind in einem Container gebündelt, sodass keine Installation von PHP, Composer, MySQL, etc. auf dem
Host-System erforderlich ist. Dies erleichtert die Entwicklung und den Betrieb der Plattform enorm.

Alle notwendigen Dienste (Datenbank, Redis, etc.) werden ebenfalls in Containern ausgeführt.

Wenn Docker auf dem Host-System installiert wird, werden auch alle notwendigen Tools installiert, die Docker braucht. 
Die Anleitung für die Installation von Docker auf einem Ubuntu Server finden Sie 
[hier](https://docs.docker.com/engine/install/ubuntu/).

#### Docker Glossar

- Dockerfile
  - Eine Textdatei, die die Schritte enthält, um ein Docker Image zu erstellen.
- Docker Image
  - Der Blueprint für einen Container.
- Docker Container
  - Eine Instanz eines Docker Images. Der Container ist das, was Docker letztendlich ausführt und in
    dem die Anwendung läuft.
- Docker Volume
  - Ein Docker Volume ist ein Speicherbereich, der von einem oder mehreren Containern genutzt wird.
- Docker Compose
  - Ein Tool, um mehrere Docker-Container zu definieren und zu orchestrieren.

#### Übersicht von Docker-Befehlen

- `docker compose build` 
  - Baut die Docker-Container anhand der `docker-compose.yml`-Datei.
- `docker compose up -d` 
  - Lädt fehlende Docker Images herunter, startet die Docker-Container im Hintergrund und erstellt Docker-Volumes.
- `docker compose down`
  - Stoppt laufende Docker-Container.
  - Die Option `-v` entfernt auch die Docker-Volumes. Achtung: Alle Daten gehen verloren!
- `docker compose logs`
  - Zeigt die Logs der Docker-Container an.
  - Die Option `-f` zeigt die Logs live an.
- `docker compose ps`
  - Zeigt die laufenden Docker-Container an.
- `docker compose exec <container-name> <command>`
  - Führt ein Kommando in einem laufenden Docker-Container aus.
  - Beispiel: Mit `docker compose exec <container-name> bash` können Sie eine Shell im Container öffnen.

## Konfiguration

### Instanz Konfiguration

Um eine Instanz der Degree Platform zu konfigurieren, verwenden wir eine `.env`-Datei, um unsere Umgebungsvariablen zu
setzen. Diese Datei wird von Docker Compose beim Starten der Plattform eingelesen.

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

### Platform Lokal Starten

Stellen Sie sicher, dass die `.env`-Datei im Root-Verzeichnis des Projekts basierend auf der `.env.example`-Datei
vorhanden ist.

Um die Plattform lokal zu starten, können Sie die folgenden Befehle ausführen:

- Docker Container erstellen: `docker compose build`
- Docker Container starten und Live-Logs anzeigen: `docker compose up -d && docker compose logs -f`

> Die Plattform läuft hier: `http://localhost:8080`.

#### Fixtures

Um Datenbank-Fixtures zu laden, können Sie den folgenden Befehl ausführen:

- `make import-fixtures`

Dabei werden unter anderem 4 Benutzer erstellt:

- `admin@sandstorm.de / password`
- `student@sandstorm.de / password` oder `student2@sandstorm.de / password`
- `dozent@sandstorm.de / password`

> Info zum `make`-Befehl:
> 
> Mit `make` können Sie Befehle in einem Makefile ausführen. Das Makefile enthält zum Beispiel Befehle, die häufig 
> verwendet werden. Für Informationen zu den Befehlen schauen Sie in das Makefile an im Root-Verzeichnis des Projekts.

#### Symfony Console

Die Symfony Console im Docker Container auszuführen, nutze `./symfony-console`.
Um beispielsweise eine neue Datenbankmigration zu erstellen, können Sie folgenden Befehl ausführen:

- `./symfony-console doctrine:migrations:generate`

> Info zum `./symfony-console`-Befehl:
> 
> Der Befehl `./symfony-console` führt die Symfony Console im Docker Container aus. 
> 
> Die Symfony Console ist ein Tool, das von Symfony bereitgestellt wird, um Befehle auszuführen, die mit der Anwendung 
> zusammenhängen.
> 
> Beispiel: `doctrine:migrations:generate` ist ein Befehl, der von Doctrine Migrations bereitgestellt wird, um eine neue
> Datenbankmigration zu erstellen. 
> 
> Viele Symfony-Bundles bieten eigene Befehle an, die über die Symfony Console ausgeführt werden können.

### Frontend Kompilieren

Das Projekt nutzt FontAwesomePro. Siehe `app/.npmrc.example`.

Um die Assets zu kompilieren, müssen Sie die folgenden Tools installieren:

- Node Version Manager (nvm) (https://github.com/nvm-sh/nvm)
  - Wenn Sie `nvm` nicht verwenden möchten, können Sie auch die in der `.nvmrc`-Datei angegebene Node.js-Version manuell
    installieren.
- Yarn (https://yarnpkg.com/)

Frontend Kompilieren:
- In das `app/`-Verzeichnis wechseln: `cd app`
- Sicherstellen, dass die korrekte Node.js Version genutzt wird: `nvm use`
- Abhängigkeiten installieren: `yarn`
- Assets kompilieren: `yarn build`

> Info zu `nvm`:
> 
> `nvm` ist ein Tool, das es ermöglicht, mehrere Versionen von Node.js auf einem System zu verwalten. Mit `nvm use`
> können Sie die in der `.nvmrc` Datei definierte Node.js Version verwenden.

> Info zu `yarn`:
> 
> `yarn` ist ein Paketmanager-Alternative zu `npm`.
> 
> Mit `yarn` bzw. `yarn install` werden die Abhängigkeiten installiert.
> 
> Mit `yarn build` werden die Assets kompiliert. (siehe `app/package.json`)
> 
> Nutze `yarn watch` um automatisch Änderungen zu erkennen und die Assets neu zu kompilieren.

#### Tests Lokal Ausführen

Die Tests bestehen aus Integration-Tests und End-to-End-Tests.

Zum einen wird die Plattform mit Docker gestartet (System-Under-Test genannt).
Zum anderen wird ein Testrunner gestartet, der die Tests ausführt.

- Plattform lokal starten (siehe oben)
- e2e-testrunner starten:
  - Neue Shell öffnen 
  - In das `e2e-testrunner`-Verzeichnis wechseln: `cd e2e-testrunner`
  - Sicherstellen, dass die korrekte Node.js Version genutzt wird: `nvm use`
  - Abhängigkeiten installieren: `npm i`
  - Testrunner Starten: `node index.js`
  - Den Testrunner laufen lassen und nicht beenden, solange die Tests laufen sollen
- tests starten:
  - Neue Shell öffnen
  - Tests starten: `make test` (aus dem Root-Verzeichnis des Projekts)

Tests können auch gezielt ausgeführt werden:

- `make test-integration`
  - führt nur die Integration-Tests aus
- `make test-e2e`
  - führt nur die End-to-End-Tests aus
- `make test-debug`
  - Führt alle Features und Scenarios mit `@debug`-Annotation aus
