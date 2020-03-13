# docker-symfony
Docker compose setup that I use when developing symfony app's.

## Setup
Assuming you have Composer installed on your host computer, run `composer install symfony/website-skeleton`
to setup your app. After composer has setup the app, copy all files in this repo to your app's base dir before preceding.

- Change `xdebug.remote_host=1.2.3.4` in `php-cli/xdebug.ini` & `php-fpm/xdebug.ini` to your local ip address. Or remove this setting all together.

- Copy `.docker/env.dist` to `.docker/.env` and modify accordingly. `COMPOSE_PROJECT_NAME=` should be unique, as docker will use this to name the containers.

- run `make build` from the app's base dir to build the containers.

- run `make start` to start the containers.

## Notes
- Use `make stop` rather than `make down` to stop the containers. Otherwise, you'll have to rebuild the
containers every time you want to spin them back up using `make up`.

- A database container was not included as I find it easier to have a docker database container running
on the host that is not tied to any individual project/app. 

- As I have MariaDb running in a separate docker container outside my project(s), I have included 
the `mariadb_app` network in the `docker-compose.yml` file. Remove:
    ```
    networks:
       ...
        db:
            external:
                name: mariadb_app
        ...
    ```
   and all `networks: -db` references in `docker-compose.yml` if not using my mariadb docker container.

- I use this setup in a linux environment (Debian) and have not tested it on windows or mac.

- Xdebug uses port 9001 as PHP-FPM uses 9000 to talk to nginx.

- The phpstan.neon file provided assumes you are running Symfony 5+. If using a different
version of Symfony, you may have to modify `container_xml_path: ` in `phpstan.neon`

Enjoy!
