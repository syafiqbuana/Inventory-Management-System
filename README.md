# 🇮🇩 Inventory Management System - Dinas Kependudukan Dan Pencatatan Sipil Kab. Banyumas

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel)
![Filament](https://img.shields.io/badge/Filament-3-FDAE4B?style=for-the-badge&logo=filament)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)

A robust, enterprise-grade Inventory Management System built specifically for **Dinas Kependudukan Dan Pencatatan Sipil (Dukcapil) Region Banyumas, Central Java**. This system is designed to handle complex stock mutations, fiscal period automation, and rigorous asset tracking using Laravel 11 and Filament Admin 3.

---

## 📋 Table of Contents
1. [Key Features](#-key-features)
2. [System Logic & Workflow](#-system-logic--workflow)
   - [The Period System](#1-the-automated-period-system)
   - [Purchase Strategy](#2-purchase-logic)
3. [User Roles & Permissions](#-user-roles--permissions)
4. [Tech Stack](#-tech-stack)
5. [Installation Guide](#-installation-guide)
6. [Reporting](#-reporting)

---

## 🚀 Key Features

### 📦 Inventory Management
* **Master Data Management:**
    * **CRUD Category:** Manage item categories (e.g., Electronics, Stationery, Furniture).
    * **CRUD Item Type:** Manage units of measurement (e.g., Pcs, Unit, Pack, Box).
* **Item Management:**
    * Create items manually via form input.
    * **Excel Import:** Bulk import items using `.xlsx` files. A standardized template is available for download directly within the Item Resource.
* **Stock Tracking:** Real-time calculation of Initial Stock, Incoming (Purchase), Outgoing (Usage), and Final Stock.

### 💰 Purchasing (Pengadaan)
* **Dynamic Purchase Flow:** The system distinguishes between restocking existing inventory and acquiring assets with new valuations.
* **Stock Updates:** Purchases for items with consistent pricing automatically update existing stock levels.

### 📤 Item Usage (Penggunaan)
* **Multi-Item Cart:** Users can select multiple items to "check out" in a single usage transaction.
* **Printable Notes:** Admins can generate and print an official usage note (Bon Penggunaan) immediately after the transaction is recorded.

---

## 🧠 System Logic & Workflow

### 1. The Automated Period System
This project uses a strict time-period system to ensure historical data integrity.

* **Auto-Seeding:** The initial period is created automatically via system seeders.
* **Period Locking:** Every row in the `Item`, `Purchase`, and `Usage` tables is bound to a specific `period_id`. Users can **only** edit or delete records if the associated period is currently **Active**.
* **Close Period Mechanism:**
    1. User triggers the "Close Period" action.
    2. The system calculates the `Remaining Stock` ($Initial + Purchase - Usage$) for all items in the current period.
    3. The current period is marked as **Closed** (becoming Read-Only).
    4. A **New Period** is automatically created.
    5. Items are carried over to the new period, where the old `Remaining Stock` becomes the new `Initial Stock`.

### 2. Purchase Logic
To handle asset valuation accurately, the purchase feature operates in two modes:

* **Standard Restock (Same Unit Price):**
    * Used when buying existing items at the same price.
    * **Action:** Increases the quantity of the existing item record.
* **New Procurement / Price Change (Different Unit Price):**
    * Used when the item price changes or a completely new item is bought.
    * **Action:** User must use the **"Pengadaan Barang Baru"** tab. This creates a new row in the Item table to preserve the value of old stock vs. new stock.

---

## 👥 User Roles & Permissions

The system implements a hierarchical Role-Based Access Control (RBAC):

| Role | Capabilities |
| :--- | :--- |
| **Super Admin** | **Full Access.** Can create/manage Admin and Staff accounts. Has access to all system settings. |
| **Admin** | Can create/manage **Staff** accounts. Can manage Inventory, Purchases, Usage, and Reports. |
| **Staff** | Operational access only. Can manage Inventory, Purchases, and Usage. Cannot manage users. |

---

## 📊 Reporting

### Item Mutation Report
A comprehensive report generator is included to track the flow of goods.
* **Data Points:**
    * Initial Stock (Stok Awal)
    * Total Purchases (Masuk)
    * Total Usage (Keluar)
    * Remaining Stock (Sisa Stok)
* **Format:** Viewable in the table and exportable.

---

## 🛠 Tech Stack

* **Framework:** [Laravel 11](https://laravel.com)
* **Admin Panel:** [FilamentPHP V3](https://filamentphp.com)
* **Database:** MySQL / MariaDB
* **Excel Engine:** Laravel Excel / Maatwebsite
* **PDF/Printing:** CSS Print Media / Barryvdh DomPDF

---

## 💻 Installation Guide

1.  **Clone the Repository**
    ```bash
    git clone [https://github.com/your-repo/dukcapil-inventory.git](https://github.com/your-repo/dukcapil-inventory.git)
    cd dukcapil-inventory
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install && npm run build
    ```

3.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Update your `.env` file with your database credentials.*

4.  **Database Migration & Seeding**
    **Crucial Step:** You must run the seeder to initialize the roles and the first active Period.
    ```bash
    php artisan migrate --seed
    ```

5.  **Storage Linking**
    ```bash
    php artisan storage:link
    ```

6.  **Run the Application**
    ```bash
    php artisan serve
    ```

---

## 📄 License

This software is developed for internal use by **Dinas Kependudukan Dan Pencatatan Sipil Region Banyumas**.
