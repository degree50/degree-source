# Degree Platform

## System Requirements

- Linux machine
    - recommended specs
        - Ubuntu
        - at least 500 GB of storage (Videos will take up a lot of space)
        - at least 16 GB of RAM
        - modern standard CPU specs
- Docker with Docker Compose plugin
    - see here for an example of how to set up docker on
      ubuntu: https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository

## Configuration

### Instance Configuration

To configure your instance of the Degree Platform we use a `.env` file to store our environment variables.
Create a `.env` file in the root of the project. Use the `.env.example` file as a template.

Important:

- For SAML SSO login a SAML SP (Service Provider) and IDP (Identity Provider) must be configured.
- For sending emails an SMTP server must be configured.

### Imprint, Privacy Policy and Terms of Use

To configure the Imprint, Privacy Policy and Terms of Use you can edit the following files:

- `app/templates/Standard/DataPrivacy.html.twig`
- `app/templates/Standard/Imprint.html.twig`
- `app/templates/Standard/TermsOfUse.html.twig`

### Logo

To change the logo you can replace the file `app/assets/images/logo.svg`.

### Styling

To change colors or other styles have a look at `app/assets/scss/GeneralStyles/Variables.scss`.
Remember to recompile the assets to see the changes.

## Development

Look at the Makefile for the different commands you can run to develop the platform.

### Running the Platform Locally

Be sure to have the `.env` file in the root of the project based on the `.env.example` file.
To start the platform locally you can run the following commands:

- `docker compose build`
- `docker compose up -d && docker compose logs -f`

> The platform will be available at `http://localhost:8080`.

#### Fixtures

To load fixtures into the database you can run the following command:

- `make import-fixtures`

This will also create 4 users:

- `admin@sandstorm.de / password`
- `student@sandstorm.de / password` or `student2@sandstorm.de / password`
- `dozent@sandstorm.de / password`

#### Symfony Console

The Symfony Console can be executed via `./symfony-console`.
For example, to create a new database migration you can run:

- `./symfony-console doctrine:migrations:generate`

### Build the Frontend

This project uses FontAwesomePro. Check out `app/.npmrc.example`.

You will also need to install the following tools depending on your system:

- Node Version Manager (nvm) (https://github.com/nvm-sh/nvm)
- Yarn (https://yarnpkg.com/)

```
cd app
nvm use
yarn
yarn build
```

> Use `yarn watch` to watch for changes and rebuild the assets when developing.

#### Running Tests Locally

- Start the platform locally (see above)
- start e2e-testrunner
    - `cd e2e-testrunner`
    - `nvm use`
    - `npm i`
    - `node index.js`
- start tests
    - `make test`

Tests can also be run more selectively:

- `make test-integration`
- `make test-e2e`
- `make test-debug`
    - this runs all features or scenarios with the tag `@debug`
