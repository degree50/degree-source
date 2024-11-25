# Degree Platform

## System Requirements

- Linux machine
    - recommended specs
        - Ubuntu
        - At least 500 GB of storage (Videos will take up a lot of space)
        - At least 16 GB of RAM
        - Modern standard CPU specs
- Docker with Docker Compose plugin
    - See here for an example of how to set up docker on
      Ubuntu: https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository
    - Docker is usually run with root privileges. More information:
      https://docs.docker.com/engine/install/linux-postinstall/#manage-docker-as-a-non-root-user
- All other dependencies will be installed automatically in the Docker containers.

### Why Docker?

With Docker, we have the ability to start the Degree Platform on any system that supports Docker. All dependencies are
bundled in a container, so there is no need to install PHP, Composer, MySQL, etc. on the host system. This greatly
simplifies the development and operation of the platform.

All necessary services (database, Redis, etc.) are also run in containers.

When Docker is installed on the host system, all necessary tools that Docker needs are also installed. The instructions
for installing Docker on an Ubuntu server can be found [here](https://docs.docker.com/engine/install/ubuntu/).

#### Docker Glossary

- Dockerfile
  - A text file that contains the steps to create a Docker image.
- Docker Image
  - The blueprint for a container.
- Docker Container
  - An instance of a Docker image. The container is what Docker ultimately runs and in which the application runs.
- Docker Volume
  - A Docker volume is a storage area used by one or more containers.
- Docker Compose
  - A tool to define and orchestrate multiple Docker containers.

#### Overview of Docker Commands

- `docker compose build`
  - Builds the Docker containers based on the `docker-compose.yml` file.
- `docker compose up -d`
  - Downloads missing Docker images, starts the Docker containers in the background, and creates Docker volumes.
- `docker compose down`
  - Stops running Docker containers.
  - The `-v` option also removes the Docker volumes. Caution: All data will be lost!
- `docker compose logs`
  - Displays the logs of the Docker containers.
  - The `-f` option displays the logs live.
- `docker compose ps`
  - Displays the running Docker containers.
- `docker compose exec <container-name> <command>`
  - Executes a command in a running Docker container.
  - Example: With `docker compose exec <container-name> bash` you can open a shell in the container.

## Configuration

### Instance Configuration

To configure your instance of the Degree Platform we use a `.env` file to store our environment variables. This file
will be used by Docker Compose to set up the environment for the platform.

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

- Build Docker Containers: `docker compose build`
- Start Docker Containers and show live logs: `docker compose up -d && docker compose logs -f`

> The platform will be available at `http://localhost:8080`.

#### Fixtures

To load fixtures into the database you can run the following command:

- `make import-fixtures`

This will also create 4 users:

- `admin@sandstorm.de / password`
- `student@sandstorm.de / password` or `student2@sandstorm.de / password`
- `dozent@sandstorm.de / password`

> Information about the `make` command:
> 
> `make` is used to run commands from the Makefile. The Makefile contains commands that are used frequently during
> development. You can see the available commands in the Makefile in the root of the project.

#### Symfony Console

The Symfony Console can be executed via `./symfony-console`.
For example, to create a new database migration you can run:

- `./symfony-console doctrine:migrations:generate`

> Information about the `symfony-console` script:
> 
> The `symfony-console` script is a wrapper for the Symfony Console. It executes the Symfony Console commands in the
> Docker container.
> 
> Example: `./symfony-console doctrine:migrations:generate` is a Command from the Doctrine Migrations Bundle to generate
> a new migration.
> 
> Many Symfony Bundles provide commands that can be executed via the Symfony Console. You can see the available commands
> by running `./symfony-console`.

### Build the Frontend

This project uses FontAwesomePro. Check out `app/.npmrc.example`.

You will also need to install the following tools depending on your system:

- Node Version Manager (nvm) (https://github.com/nvm-sh/nvm)
  - If you do not want to use `nvm`, you can also manually install the Node.js version specified in the `.nvmrc` file.
- Yarn (https://yarnpkg.com/)

- Enter `app/` directory: `cd app`
- Make sure you use the correct Node.js Version: `nvm use`
- Install dependencies: `yarn`
- Build assets: `yarn build`

> Information about `nvm`:
> 
> `nvm` is a tool to manage multiple Node.js versions. It allows you to switch between different versions of Node.js.
> 
> `nvm use` is used to switch to the Node.js version specified in the `.nvmrc` file.

> Information about `yarn`:
> 
> `yarn` is a package manager alternative to `npm`.
> 
> Use `yarn` or `yarn install` to install the dependencies.
> 
> Use `yarn build` to compile the assets. (see `app/package.json`)
> 
> Use `yarn watch` to watch for changes and rebuild the assets when developing.

#### Running Tests Locally

The tests consist of integration tests and end-to-end tests.

First we start the platform locally via Docker (this is called "System under test").
Next we start the test runner which will execute the tests.

- Start the platform locally (see above)
- Start e2e-testrunner:
  - Open new shell
  - Enter `e2e-testrunner/` directory: `cd e2e-testrunner`
  - Make sure the correct Node.js Version is used: `nvm use`
  - Install dependencies: `npm i`
  - Start test runner: `node index.js`
  - Let the test runner run and do not stop it as long as the tests should run
- start tests:
  - Open new shell
  - Start tests: `make test` (run from the root directory of the project)

Tests can also be run more selectively:

- `make test-integration`
  - Only run integration tests
- `make test-e2e`
  - Only run end-to-end tests
- `make test-debug`
    - this runs all features and scenarios with the tag `@debug`
