## Technologies Used

   - Docker
   - Nginx:1.18-alpine
   - MySql:8.0
   - phpMyAdmin 2
   - Laravel v10.50.2 (PHP v8.4.21) 
   - Node 22

## Project Installation

After 'git clone':

Make sure you have the right path in "docker-compose.yml:
```
services:
  # php (app)
  app:
    build:
      context: ./docker/php
    volumes:
      - ./app:/var/www/crm/app/public
    networks:
      - news-network

  # nginx
  nginx:
    build:
      context: ./docker/nginx
    ports:
      - '8080:80'
    volumes:
      - ./app:/var/www/crm/app/public
```
```
$ make init
```
```
$ make up
```
- From the ***/var/www/crm/app*** directory:
    * `sudo chown -R $USER:www-data storage bootstrap/cache`
    * `sudo chmod -R 775 storage bootstrap/cache`

- From the ***/var/www/crm*** directory:
   * `docker exec -it crm-app-1 composer install`
   * `docker exec -it crm-app-1 php artisan key:generate`
   * `docker exec -it crm-app-1 php artisan migrate`
   * `docker exec -it crm-app-1 php artisan migrate:fresh --seed`
   * `docker exec -it crm-app-1 npm install`
   * `docker exec -it crm-app-1 npm run dev`
   * Admin: admin@admin.com password
   * Manager: user@user.com password (all registered users via "web" have role "manager")
    * Customer: all customers' have same "password"
    
###NOTE: ***"app" and "frontend" test in different Browsers***
( By the reason that Auth cookie is stored in browser for both "app" and "frontend" )

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
  - Project architecture comes with Docker environment and Laravel Breeze Dashboard for "web" and "api".
  - Admin and Managers can use both "web" and "api", Customers only "api"
  - For authentication, we use Laravel Sanctum (spatie/laravel-permission)
  - For media files storage, we use Laravel Media Library (spatie/laravel-medialibrary)
  