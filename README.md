# Dental Clinic Appointment Manager

## Overview
This project is a web application designed to manage appointments in a dental clinic efficiently. The main goal is to prevent scheduling conflicts, such as:

- Avoiding double bookings for the same time slot.
- Preventing a patient from booking multiple appointments on the same day unknowingly.
- Managing doctor schedules to ensure availability.

Additionally, the app includes a basic feature for doctor-patient communication, such as simple chat functionality.

## Technologies Used
- **Backend:** Symfony (PHP framework), MVC architecture
- **Frontend:** HTML, CSS, Bootstrap, Twig templates
- **Database:** MySQL / MariaDB
- **Dependency Management:** Composer

## Environment Setup
Environment variables are configured in `.env` files. The app loads variables in the following order of precedence:

1. `.env` – default values
2. `.env.local` – local overrides (not committed)
3. `.env.$APP_ENV` – environment-specific defaults
4. `.env.$APP_ENV.local` – environment-specific local overrides (not committed)

Example `.env` configuration:

```env
APP_ENV=prod
APP_SECRET=acc21c17c82aed5fa539704f75c8a62a

# Database configuration
DATABASE_URL="mysql://root:@127.0.0.1:3306/pfe?serverVersion=10.4.28-MariaDB&charset=utf8mb4"

# Messenger configuration
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
Note: Do not define production secrets in committed .env files. Refer to Symfony Secrets Documentation for secure handling.
```
Installation
Clone the repository:
`git clone https://github.com/RafaelElebiyo/denticare_webapp`

Navigate to the project folder:
`cd denticare_webapp`

Install dependencies using Composer:
`composer install`

Configure your .env.local with database credentials and other overrides.

Create and migrate the database:
`
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
`
Start the Symfony server:
`
symfony server:start
`
Access the app at http://localhost:8000.

## Features
Appointment Management: Schedule appointments without conflicts.

Patient Management: Avoid double bookings for the same patient.

Doctor Schedules: Basic management of doctors’ availability.

Communication: Simple chat functionality between patients and dentists.

Responsive Design: Built with Bootstrap for mobile and desktop compatibility.

Project Structure
```
├── config/          # Symfony configuration files
├── src/             # Application source code (MVC)
├── templates/       # Twig templates for views
├── public/          # Public assets (CSS, JS, images)
├── migrations/      # Database migrations
├── .env             # Environment variables
├── composer.json    # PHP dependencies
└── README.md
```
