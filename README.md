# Inventory Management System

## Overview
This Inventory Management System is built using **Laravel 11** and **Filament 3**, designed to efficiently manage various inventory items for businesses or individual users.

## Features
- **User Management:** Role-based access control with user registration and authentication.
- **Inventory Tracking:** Add, edit, delete, and view inventory items.
- **Categories:** Organize inventory items into categories for easier management.
- **Reporting:** Generate detailed reports on inventory status and history.
- **Filament Dashboard:** Easy-to-use administration interface powered by Filament 3.

## Installation
### Prerequisites
- PHP >= 8.1
- Composer
- Laravel 11

### Steps to Install
1. Clone the repository:
   ```bash
   git clone https://github.com/syafiqbuana/inventaris2.git
   cd inventaris2
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the `.env.example` file to `.env` and set your environment variables.
   ```bash
   cp .env.example .env
   ```
4. Generate an application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Serve the application:
   ```bash
   php artisan serve
   ```

## Usage
- Access the application by navigating to `http://localhost:8000`
- Log in to the admin interface to manage inventory items.

## Contribution
Contributions are welcome! Please open an issue or submit a pull request if you have suggestions or improvements.

## License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.