# api-platform
Demo application built with [API Platform](https://api-platform.com/).

## Prerequisites

Before you begin, ensure you have met the following requirements:

- **Docker:** This project relies on Docker for containerization.  
If you don't have Docker installed, please follow the installation instructions [here](https://docs.docker.com/get-docker/).

## Getting Started

Follow these steps to get your project up and running:

> **_NOTE:_**  Port **80** and **3306** should not be allocated during the installation process.  
> Make sure these ports are available before running the commands.

1. Clone the repository: `git clone https://github.com/danielpapp/api-platform.git`
2. Navigate to the project directory: `cd api-platform`
3. Build the Docker image and start container: `docker compose up -d`
4. Enter the Docker container: `docker compose exec php bash`
   1. Install PHP dependencies: `composer install`
   2. Create the database: `php bin/console doctrine:database:create`
   3. Run migrations: `php bin/console doctrine:migrations:migrate`
   4. (Optional) Load data fixtures: `php bin/console doctrine:fixtures:load`

Your project should now be accessible at [http://localhost/api](http://localhost/api).
