## Technologies Used

   - Docker
   - Nginx:1.18-alpine
   - MySql:8.0
   - phpMyAdmin 2
   - Nette Framework 3.2
   - Node 22

## Project Installation

After 'git clone':

Make sure you have the right path in "docker-compose.yml:
```
services:
  # PHP (Nette API backend)
  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - ./api:/var/www/star/api
    networks:
      - star-network

  # Node container for running frontend/asset tasks
  node:
    image: node:22-bookworm-slim
    volumes:
      - ./api:/var/www/star/api
    working_dir: /var/www/star/api
    profiles: ["cli"]
    networks:
      - star-network
```
```
$ make init
```
```
$ make up
```

- From the ***/var/www/star*** directory:
   * `docker compose exec -u 0 app chown -R www-data:www-data /var/www/star/api`
    * ``docker compose exec app vendor/bin/phinx migrate``
    * ``docker compose exec app vendor/bin/phinx seed:run``
    
- Admin credentials:
   * Admin: admin@admin.com password
    
- Run test:
  `docker compose exec app vendor/bin/tester -p php tests/AdminLoginTest.php`

  
## app
http://127.0.0.1:8080/

## frontend
http://127.0.0.1:8082/

## phpmyadmin
http://127.0.0.1:8081/
   - `db`
   - `root`
   - `root`

## Command Glossary
   - `make init`
   - `make down`
   - `make up`
   - `make restart` - rebuild and start containers
   - `docker ps` - list all running containers
   - `docker exec -it <NAME> sh` - Enter container
   - `docker logs -f <container_id>` - see incoming logs for container
   - Show tree Directory/Files in a container: 
     ``` $ find . -print | sed -e 's;[^/]*/;|____;g;s;____|; |;g'```

## Development Notes

  