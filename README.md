# Laravel Booking Events Restful API

A RESTful API built with Laravel that allows users to create, manage, and book events. Users can create events, make reservations, leave reviews, and manage their bookings.

## Features

- **User Authentication**: JWT-based authentication system using Sanctum
- **Event Management**: Create, Read, Update, Replace and Delete Events
- **Reservations**: Book and Cancel event reservations
- **Reviews System**: Leave and manage Reviews for Attended Events
- **Role-Based Access**: Different permissions for Event Creators and Attendees
- **Input Validation**: Comprehensive request validation using Customized Requests
- **API Resources**: Transformed and consistent API responses
- **Database Relationships**: Efficient relationships between Users, Events, Reservations, and Reviews

## Prerequisites

- PHP >= 8.1
- MySQL/MariaDB
- Docker (optional, for using Laravel Sail)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd booking-events-api
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env.example .env
```
Update the `.env` file with your database credentials and other configuration settings.

4. Generate application key:
```bash
php artisan key:generate
```

5. Run migrations and seeders:
```bash
php artisan migrate --seed
```

## Running with Docker (Laravel Sail)

1. Start the containers:
```bash
./vendor/bin/sail up -d
```

2. Run migrations and seeders:
```bash
./vendor/bin/sail artisan migrate --seed
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user

### Events
- `GET /api/events` - List all events
- `POST /api/events` - Create a new event
- `GET /api/events/{id}` - Get event details
- `PATCH /api/events/{id}` - Update an event
- `PUT /api/events/{id}` - Replace an event
- `DELETE /api/events/{id}` - Delete an event

### Reservations
- `POST /api/events/{id}/reserve` - Make a reservation
- `DELETE /api/events/{id}/cancel` - Cancel a reservation

### Reviews
- `GET /api/events/{id}/reviews` - List of Reviews one Event at a time 
- `POST /api/events/{id}/reviews` - Create a review
- `PATCH /api/reviews/{id}` - Update a review
- `PUT /api/reviews/{id}` - Replace a review
- `DELETE /api/reviews/{id}` - Delete a review

## Request Validation

The API implements comprehensive validation for all requests:

- Event creation/updates require title, description, date_time, location, price, atendee limit, and reservation deadline
- Reservations are validated against event capacity and user eligibility
- Reviews can only be submitted by users who attended the event
- Event creators cannot make reservations for their own events

## Database Structure

### Key Tables
- `users` - User accounts and authentication
- `events` - Event details and metadata
- `event_user` - Reservation pivot table
- `reviews` - Event reviews and ratings

## Testing

Run the test suite:
```bash
php artisan test
```

Or with Sail:
```bash
./vendor/bin/sail test
```

## Security

- JWT Authentication (Sanctum)
- Customized Request validation
- CORS protection
- Login Rate Limiting
- SQL injection prevention

## Error Handling (Bootstrap/app.php/->withExceptions() method)

The API uses consistent error responses:
- Validation errors (422)
- Authentication errors (401)
- Authorization errors (403)
- Resource not found (404)
- Server errors (500)

## API Response Format 
## Traits/ApiResponses.php
## Resources/EventList//Event//Reservation//ReviewResource.php

Successful response:
```json
{
    "message": "Operation successful",
    "data": {
        // Resource data
    }
    "status": //statusCode,
}
```

Error response:
```json
{
    "message": "Error message",
    "status": //statusCode,
}
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).