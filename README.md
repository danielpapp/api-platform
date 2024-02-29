# api-platform
Demo application built with [API Platform](https://api-platform.com/).

## Installation

> **_NOTE:_**  Port **80** and **3306** should not be allocated during the installation process.  
> Make sure these ports are available before running the commands.


### 1. Build images and start containers
```bash
docker compose up -d
```

### 2. Install php dependencies
```bash
docker compose exec -T php composer install
```

### 3. Create database
```bash
docker compose exec -T php bin/console doctrine:database:create
```

### 4. Run migrations
```bash
docker compose exec -T php bin/console doctrine:migrations:migrate
```

You can now visit [http://localhost/api](http://localhost/api) to see the API dashboard.

### 5. (Optional) Run data fixtures
```bash
docker compose exec -T php bin/console doctrine:fixtures:load
```

## Testing
```bash
docker compose exec -T php bin/phpunit
```
