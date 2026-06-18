## Application description
  - This is a simple application for managing: 
        Customers, customer-activities and activity-comments
  - Users with role "admin" and "operator" can login to Customers admin panel
  - Login credentials:
    * Admin: admin@admin.com password
    * Operator: operator1@crm.com password
    * Customer: john.doe@acme.com password

## Developer Notes
    - Docker architecture for Nginx, MySql, PHP, pHpMyAdmin
    - Used design patterns: 
        * Service-Repository pattern 
            CustomerService [
                UserRepository, 
                ActivityRepository, 
                CommentRepository
            ] // to decouple Logic from Presenter
        * Middleware [AuthenticationListener, CsrfMiddlewareListener]
        * Dependency Injection
            services:
                # Model
                # Middleware listeners
                # Repositories
                # Services
        * Modal view Components
    - Unit Testing: at "- Run all tests" section
    - To Login with "admin/operator" role - http://127.0.0.1:8080/sign/in

## Project Structure
```text
star/
└── api/
    └── app/
        ├── Core/
        ├── Middleware/
        │   ├── AuthenticationListener.php
        │   └── CsrfMiddlewareListener.php
        ├── Model/
        │   └── UserAuthenticator.php
        ├── Presentation/
        │   ├── Components/
        │   │   ├── ActivityDetailsModal/
        │   │   └── ActivityModal/
        │   ├── Error/
        │   ├── Home/
        │   │   ├── default.latte
        │   │   └── HomePresenter.php
        │   └── Sign/
        ├── Repository/
        │   ├── ActivityRepository.php
        │   ├── CommentRepository.php
        │   └── UserRepository.php
        ├── Service/
        │   └── CustomerService.php
        └── Bootstrap.php
```

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

At "/var/www/star/api$" directory:
  * `docker compose exec -u 0 app chown -R www-data:www-data /var/www/star/api/temp`
  * `docker compose exec -u 0 app chown -R www-data:www-data /var/www/star/api/log`
  * `docker compose exec app composer install`
  * `docker compose exec app vendor/bin/phinx migrate`
  * `docker compose exec app vendor/bin/phinx seed:run`

```
$ make up
```

- Check Logs:
   `cat log/exception.log | tail -n 50`

- Clear Cache:
   `sudo rm -rf temp/cache/*`

- Run all tests: (visual resulting with "TEST SUITE SUMMARY")
  * `sudo rm -rf temp/tests/*`
  * `docker compose exec app php tests/run.php`
  
## app
http://127.0.0.1:8080/

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

## TODO list
    - Enum activitiy-comment "type"
    - Date format in "created_at" in View
    - Paginate Activity list: * Max 50 recent records shown
    - Decouple JS & CSS from "Sign/default.latte"
