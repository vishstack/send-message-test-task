# Send Message Test Task Project README

## Introduction
Welcome to the Send Message Test Task project! This README provides essential information for setting up and running the API. Whether you're a developer or a client, this guide will help you get started.

## Project Overview
This project is an API that facilitates communication between users. It allows sending messages (via SMS or email) and retrieving conversation logs. The codebase is written in PHP using the Laravel framework.

## Getting Started
### Prerequisites
Before you proceed, ensure you have the following installed:
- PHP
- Composer
- MySQL

### Installation
1. Clone the repository:
   ```
   git clone https://github.com/vishstack/send-message-test-task.git
   cd send-message-test-task
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Create a `.env` file by copying `.env.example`:
   ```
   cp .env.example .env
   ```

4. Configure your database connection in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Run database migrations:
   ```
   php artisan migrate
   ```

## Running the API
To start the development server, run:
```
php artisan serve
```
The API will be accessible at `http://localhost:8000`.

## API Endpoints
- **Send a Message:**
  - Endpoint: `POST /api/messages`
  - Parameters:
    - `from_user_id`: ID of the sender (required)
    - `to_user_id`: ID of the recipient (required)
    - `type`: Message type ('sms' or 'email', required)
    - `message`: Content of the message (required)

- **Retrieve Conversation Logs:**
  - Endpoint: `GET /api/conversation-logs`
  - Parameters:
    - `user1`: First user ID or email (required)
    - `user2`: Second user ID or email (optional)
    - `per_page`: Number of items per page (optional, default is 5)
    - `page`: Page number (optional, default is 1)
