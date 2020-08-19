# ESE API

- Project for ESE suggestion engine API
- Backlog: [link](https://est-rouge.backlog.com/projects/ESE)

## Getting Started

### Prerequisites

- Install docker native and start docker app
**`https://docs.docker.com/docker-for-mac/install`**

###### 2. Clone source code from Git into your computer. 
  - Because default docker native just share folder on user folder, for ignore potential error, you can clone source into folder inside home user folder, Example: /Users/username/projects/api 
  - After clone and checkout develop branch

###### 3. Check port
Before run docker, please check port 80, 443, 3306 on your machine, if these ports are open, please turn them off for ignore conflict port when run docker
###### 4. Using terminal to access root source folder 
**`/Users/username/projects/ese-search-api/docker`** 
###### 5. run: **`docker-compose up -d`** and wait docker build image
###### 6. After docker build success, you can check container start success by run **`docker ps -a`**
###### 7. Develop environment of spotify using 4 container:
 - Api server container (link to port 80 and 443 )
 - Mysql Database container ( link to port 3306 ) Mysql user is: root / password
 - API Swagger container
###### 8. Set virtual enpoint to /etc/hosts file
  **`127.0.0.1 api.ese.local docs.api.ese.local`**
###### 9. Connect to workspace
**```docker-compose exec workspace bash```**

- Install vendor
```bash
composer install
```

- Create env file and update
```bash
mv .env.example .env
php artisan key:generate
```

- Migrate database
```bash
php artisan migrate
```
Ignore show status of migration
```bash
php artisan migrate --no-status
```


### Running the tests

Prepare for unit test

```bash
docker-compose exec workspace bash
```

Execute test

```bash
php artisan test
```

Execute test with group

```bash
php artisan test --group=featureSearch65Words
```

### Dummy Data
Add seeder data master to database. Once you have written your seeder, you may need to regenerate Composer's autoloader using the dump-autoload command:
```
composer dump-autoload
```
```
php artisan db:seed --class=DummyDataSeeder
```

You're done finishing deployment to server.

## Coding conventions

You may follow this practice to apply code convention into this project
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)

#### Required
Before push your code, please you may run two commands to check:

- Coding conventions
- Possible bugs
- Suboptimal code
- Overcomplicated expressions
- Unused parameters, methods, properties

**[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)**

This follow PHP Standards Recommendations: PSR1, PSR2
```
./vendor/bin/phpcs -n --standard=phpcs.xml
```

**[PHPMD](https://github.com/phpmd/phpmd)**
```
./vendor/bin/phpmd app text phpmd.xml
```
