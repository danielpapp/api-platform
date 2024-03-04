# Symfony API for User Handling

## Overview

Symfony demo application designed for efficient user management, providing endpoints for retrieving and creating user data.

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

Your project should now be accessible at [http://localhost](http://localhost).

## Supported Formats

This API supports both JSON and YAML formats. You can choose your preferred format using the `Accept` header or by specifying the content type in the request.

### Request Headers

- `Accept`: Set the `Accept` header to specify the desired response format.

    - For JSON (default):
      ```
      Accept: application/json
      ```

    - For YAML:
      ```
      Accept: application/x-yaml
      ```

- `Content-Type`: If you're sending data in the request body, you can specify the content type using the `Content-Type` header.

  - For JSON:
    ```
    Content-Type: application/json
    ```

  - For YAML:
    ```
    Content-Type: application/x-yaml
    ```

## Endpoints

### 1. Create a New User

**Endpoint:**

```http
POST /api/users
```

**Description:**

Create a new user with the provided details.

**Request Body:**

```json
{
   "email": "john.doe@example.com",
   "firstName": "John",
   "lastName": "Doe",
   "plainPassword": "secret"
}
```

**Response:**

```json
{
   "id": 1,
   "email": "john.doe@example.com",
   "firstName": "John",
   "lastName": "Doe",
   "createdAt": "2024-03-01T12:00:00Z"
}
```

### 2. Get User Information

**Endpoint:**

```http
GET /api/users/{id}
```

**Description:**

Retrieve detailed information about a user based on the provided `id`.

**Parameters:**

- `id` (integer): Unique identifier for the user.

**Example:**

```http
GET /api/users/1
```

**Response:**

```json
{
   "id": 1,
   "firstName": "John",
   "lastName": "Doe",
   "email": "john.doe@example.com",
   "createdAt": "2024-03-01T12:00:00Z"
}
```

### 3. Get List of Users

**Endpoint:**

```http
GET /api/users
```

**Description:**

Retrieve a list of all users.

**Response:**

```json
{
   "items": [
      {
         "id": 1,
         "email": "john.doe@example.com",
         "firstName": "John",
         "lastName": "Doe",
         "createdAt": "2024-03-01T12:00:00Z"
      },
      {
         "id": 2,
         "email": "jane.doe@example.com",
         "firstName": "Jane",
         "lastName": "Doe",
         "createdAt": "2024-03-01T12:30:00Z"
      },
      // ... additional users
   ],
   "nextCursor": "new-base64-encoded-cursor"
}
```

### Pagination

For endpoints that return paginated results, the API follows a standard response format that includes pagination information.

#### Pagination Response Structure

```json
{
   "items": [
      // ... paginated items
   ],
   "nextCursor": "base64-encoded-cursor"
}
```

- `items`: An array containing the paginated items.
- `nextCursor`: A base64-encoded cursor value representing the identifier of the last item in the current set. This can be used in subsequent requests to fetch the next set of results.

#### Example Usage

For example, when querying a list of users:

```http
GET /api/users?cursor=base64-encoded-cursor
```

You will receive a pagination response:

```json
{
   "items": [
      {
         "id": 1,
         "email": "john.doe@example.com",
         "firstName": "John",
         "lastName": "Doe",
         "createdAt": "2024-03-01T12:00:00Z"
      },
      // ... additional users
   ],
   "nextCursor": "new-base64-encoded-cursor"
}
```

To retrieve the next set of users, make another request with the updated cursor:

```http
GET /api/users?cursor=new-base64-encoded-cursor
```
