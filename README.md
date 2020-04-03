# Projet ResOp - Réserve Opérationnelle

# Présentation

*ResOp* est un outil de gestion, planification et projection de moyens humains et opérationnels dans un cadre de crise.

Ce projet a été lancé par la Cellule Ressources & Anticipation de la Croix-Rouge Française à Paris dans le cadre du dispositif COVID-75.
Son but est de recenser les disponibilités des bénévoles afin de connaître le plus précisément possible les ressources sollicitables
et d'avoir de la visibilité sur les engagements à réaliser.

![Planning mockup](https://raw.githubusercontent.com/crf-devs/resop/master/doc/img/planning-mockup.png)

# Roadmap

La première version du projet a été développée en 48 heures par une équipe de bénévoles pour les besoins précis du dispositif COVID-75.

Dans une seconde version, il serait intéressant d'adapter ResOp aux autres opérations et structures de la Croix-Rouge Française,
puis aux autres associations de réponse à l'urgence.

# Disclaimer

This project has been developped in a few days, and many things can be improved. If you want to contribute, feel free to join the Discord server,
or to have a look to open issues.

# Contributing

[![Discord](https://discordapp.com/api/guilds/690879735957553152/widget.png)](https://discord.gg/ZyzeSq5)
[![Latest version](https://img.shields.io/github/v/tag/crf-devs/resop)](https://github.com/crf-devs/resop/releases)
[![CI Status](https://github.com/crf-devs/resop/workflows/CI/badge.svg?branch=master)](https://github.com/crf-devs/resop/actions?query=branch%3Amaster)
[![Code Coverage](https://codecov.io/gh/crf-devs/resop/branch/master/graph/badge.svg)](https://codecov.io/gh/crf-devs/resop)

You can join the [ResOp Discord server](https://discord.gg/ZyzeSq5) if you want more information, or if you want to contribute to the project.

If you want to contribute, you can easily start with any [`good first issue` tagged issue](https://github.com/crf-devs/resop/labels/good%20first%20issue)!

# Install

## Requirements

* git
* make
* docker >= 18.06
* docker-compose >= 1.23

### Linux

```bash
git clone git@github.com:crf-devs/resop.git && cd resop
make
```

### OS X

```bash
git clone git@github.com:crf-devs/resop.git && cd resop

make pre-configure
make configure

# Caution: you need to uncomment all `:cached` lines in the `the docker-compose.override.yml` file

make all
```

### Windows

* Install [WSL2](https://docs.microsoft.com/en-us/windows/wsl/wsl2-install)
* Enable [Docker support  for WSL2](https://docs.docker.com/docker-for-windows/wsl-tech-preview/)
* Checkout project directly within WSL, using a native windows directory as a project root will cause massive performances issues and issues with watchers ( i.e : yarn encore ).
* Run Linux build steps from WSL

Note : PHPStorm do not currently provide a good native integration, with WSL2, you will mainly need to open the directory from WSL directory, usually the name is \\wsl$\ located at same level at c/. See : [IDEABKL-7908](https://youtrack.jetbrains.com/issue/IDEABKL-7908) and [IDEA-171510](https://youtrack.jetbrains.com/issue/IDEA-171510)

### Without Docker

*Disclaimer: This method is not recommended, please prefer the Docker way if you can.*

If you don't want to use the provided Docker stack (such a weird idea, but why not), you can easily run the stack as in old times, with you own php & node & prostgres services

**Requirements**

- PHP 7.4
- Node >= 13, Yarn >= 1.22
- PostgreSQL 11
- [Symfony CLI](https://symfony.com/download)

**Install**

Create a `.env.local` file and set the database url:

```
DATABASE_URL=postgresql://<USER>:<PASSWORD>@<HOST>/<DB>?serverVersion=11&charset=utf8
```

From the `Makefile`, copy & run the following tasks commands manualy (you must remove the `bin/tools` or `bin/node-tools` prefix):

- vendors
- webpack-build-dev
- init-db

Then, you can serve the project:

```bash
symfony server:start
```

If you want to watch JS & CSS changes, run:

```bash
yarn encore dev --watch
```

That's it :) If you need any other command, you can look for it in the `Makefile`.

### With Vagrant + VirtualBox

If you want to run the stack with Vagrant, you can have a look to [this PR](https://github.com/crf-devs/resop/pull/186) in order to find a working config.

## Run

After the `make` command, go to [http://resop.vcap.me:7500/](http://resop.vcap.me:7500/),
or [https://resop.vcap.me:7583/](https://resop.vcap.me:7583/) for https (self signed certificate).

If you want to run a symfony or a php command: `bin/tools <command>`, example: `bin/tools bin/console`

### Run : after a first install

You don't need to build all the stack every time. If there is no new vendor, you can simply run:

```bash
make start
```

## Access

The project is using a Traefik proxy in order to allow access to all the HTTP services of the project. This service is listening on the 7500 port of the host.
The `*.vcap.me` domain names are binded on localhost. In order to use them offline, you only have to add a
`127.0.0.1 adminer.vcap.me resop.vcap.me traefik.vcap.me` line on your `/etc/hosts` file.

### Stack

- [http://resop.vcap.me:7500](http://resop.vcap.me:7500)
- [http://adminer.vcap.me:7500](http://adminer.vcap.me:7500)
- [http://traefik.vcap.me:7500](http://traefik.vcap.me:7500)

Caution: the traefik proxy will only serve healthy containers. The api container can be unaccessible before the first healthcheck (5s).

### HTTPS

The nginx container is available over HTTPS. This url must be used in order to use Facebook, Gmaps, camera...

- [https://resop.vcap.me:7543](https://resop.vcap.me:7543) ou [https://resop.vcap.me:7583](https://resop.vcap.me:7583)

## Before commiting

Please always run the following commands before commiting, or the CI won't be happy ;-)

```bash
make fix-cs
make test
```

Hint: you can run `make fix-cs-php` instead of `make fix-cs` if you are one of those backend devs who don't touch any css or js file.

### Tests

```bash
make test # Run all tests except coverage
make test-cs # php-cs-fixer
make test-advanced # phpstan
make test-unit # phpunit
make test-unit-coverage # phpunit + phpdbg
```

## PHP

### Tools & commands

As the php-fpm docker container doesn't contain any dev tool as composer, all dev commands must be run on the `tools` container. For example:

```bash
bin/tools composer
bin/tools bin/console cache:clear
bin/tools # to open a shell on the tools container
```

### Blackfire

In order to profile the php app with [Blackfire](https://blackfire.io/), you need to have a Blackfire account, then:
- Add your [credentials](https://blackfire.io/my/settings/credentials) in the `.env` file
- Uncomment the `blackfire` service in the `docker-compose.override.yml` file
- Uncomment the blackfire env var for the `backend_php` service in the `docker-compose.override.yml` file
- `docker-compose up -d --force-recreate backend_php blackfire`
- That's it, you can [profile](https://blackfire.io/docs/cookbooks/profiling-http) the app!

## Node

A node container is available in order to run `yarn` commands for `webpack encore`:

```bash
bin/node-tools yarn encore dev

webpack-build-dev
make webpack-watch-dev
```

## Built docker images

Every time a new commit is pushed onto master, the following Docker images are built:

- [crfdevs/resop:fpm-master](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=fpm)
- [crfdevs/resop:nginx-master](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=nginx)

Every time a new release is created, the following Docker images are built:

- [crfdevs/resop:fpm-1.1.1](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=fpm-)
- [crfdevs/resop:fpm-release-latest](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=fpm-release-latest)
- [crfdevs/resop:nginx-1.1.1](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=nginx-)
- [crfdevs/resop:nginx-release-latest](https://hub.docker.com/repository/docker/crfdevs/resop/tags?page=1&name=nginx-release-latest)

## Usage in production

This is an example of docker-compose.yml file with parameters you should use for production:

```yaml
services:
  nginx:
    image: crfdevs/resop:nginx-release-latest
    depends_on:
      - fpm
    environment:
      - "FPM_ENDPOINT=fpm:9001"

  fpm:
    image: crfdevs/resop:npm-release-latest
    environment:
      - INIT_DB=true # Run migrations on start
      - DANGEROUSLY_LOAD_FIXTURES=false # Reset DB and load fixtures on start
      - "DATABASE_URL=postgresql://<USER>:<PASSWORD>@<URL>/<DB>?serverVersion=11&charset=utf8"
```

If you want to initialize the DB with production data, run the following command in the fpm container:

```bash
bin/console app:load-organizations
```
