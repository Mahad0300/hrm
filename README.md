<div align="center">

  <!-- Header Banner / Hero -->
  <p align="center">
    <img src="https://capsule-render.vercel.app/api?type=waving&color=7c3aed&height=200&section=header&text=HRM%20Portal%20v2.5&fontSize=48&fontColor=ffffff&desc=Enterprise%20Human%20Resource%20%26%20Biometric%20Attendance%20Engine&descSize=16&descAlignY=72" width="100%" alt="HRM Portal Header Banner" />
  </p>

  <!-- Status & Tech Badges -->
  <p align="center">
    <a href="https://github.com/akamaanullah/hrm-v2"><img src="https://img.shields.io/badge/Version-v2.5%20Release-7c3aed?style=for-the-badge&logo=rocket&logoColor=white" alt="Version 2.5" /></a>
    <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version" /></a>
    <a href="https://www.zkteco.com/"><img src="https://img.shields.io/badge/Biometric-ZKTeco%20K60%2FADMS-16a34a?style=for-the-badge&logo=fingerprint&logoColor=white" alt="Biometric Integration" /></a>
    <a href="https://github.com/akamaanullah/chtrox"><img src="https://img.shields.io/badge/Connected%20App-ChatRox-65a30d?style=for-the-badge&logo=chatgpt&logoColor=white" alt="ChatRox Connected" /></a>
    <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/Database-MySQL-00758F?style=for-the-badge&logo=mysql&logoColor=white" alt="Database" /></a>
    <a href="https://amaanullah.com"><img src="https://img.shields.io/badge/Developer-Amaanullah-84cc16?style=for-the-badge&logo=codeforces&logoColor=white" alt="Developer" /></a>
  </p>

  <p align="center">
    <strong>A high-performance enterprise Human Resource Management (HRM), Payroll Processing, and ZKTeco Biometric Attendance Portal built with Custom PHP OOP/MVC.</strong>
  </p>

  <!-- Quick Portfolio & Case Study Links -->
  <p align="center">
    <a href="https://amaanullah.com/projects/hrm-employee-management-payroll-portal"><img src="https://img.shields.io/badge/HRM_Portal_Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" /></a>
    <a href="https://amaanullah.com/projects/chatrox-real-time-messaging-professional-networking"><img src="https://img.shields.io/badge/ChatRox_Case_Study-0284c7?style=flat-square&logo=googlechrome&logoColor=white" /></a>
    <a href="https://github.com/akamaanullah/chtrox"><img src="https://img.shields.io/badge/ChatRox_Repo-65a30d?style=flat-square&logo=github&logoColor=white" /></a>
    <a href="https://amaanullah.com"><img src="https://img.shields.io/badge/Website-amaanullah.com-10b981?style=flat-square&logo=safari&logoColor=white" /></a>
  </p>

</div>

<br/>

---

## 📌 Table of Contents

<div align="center">

  <p align="center">
    <a href="#-whats-new-in-version-25"><img src="https://img.shields.io/badge/⚡_WHATS_NEW_V2.5-7c3aed?style=for-the-badge" /></a>
    <a href="#-system-architecture--visual-diagrams"><img src="https://img.shields.io/badge/🏗️_ARCHITECTURE-0284c7?style=for-the-badge" /></a>
    <a href="#-core-features--modules-breakdown"><img src="https://img.shields.io/badge/🚀_MODULES-16a34a?style=for-the-badge" /></a>
  </p>
  <p align="center">
    <a href="#-tech-stack--libraries"><img src="https://img.shields.io/badge/🧰_TECH_STACK-ec4899?style=for-the-badge" /></a>
    <a href="#-repository-directory-structure"><img src="https://img.shields.io/badge/📁_DIRECTORY_TREE-f59e0b?style=for-the-badge" /></a>
    <a href="#-installation--local-setup"><img src="https://img.shields.io/badge/📦_SETUP_GUIDE-10b981?style=for-the-badge" /></a>
    <a href="#-developer--contact-info"><img src="https://img.shields.io/badge/👨‍💻_DEVELOPER_INFO-6366f1?style=for-the-badge" /></a>
  </p>

</div>

<br/>

---

## ⚡ What's New in Version 2.5

- 📟 **Dynamic ZKTeco Biometric Machine Integration**: No developer required! Configure, test, sync, or disconnect any ZKTeco biometric attendance machine (K60/ID series) live from the Admin GUI.
- 📡 **Dual Protocol Sync Engine (UDP/TCP + ADMS Cloud Push)**: Supports both direct IP socket communication via `ZKLib` and live HTTP push events from ADMS/iclock hardware webservers.
- 💬 **ChatRox Workspace Connected App**: Centralized third-party integrations dashboard allowing self-hosted ChatRox configuration via Domain or custom IP & Port.
- ⏱️ **Grace Period & Smart Late-In Engine**: Shift-aware attendance evaluation logic (e.g. 8:00 AM shift with 15-minute grace marks `08:15:00` as ON TIME; `08:16:00` triggers LATE IN).
- 📊 **Interactive KPI Management & Template System**: Build evaluation templates, score employees, generate KPI reports, and visualize performance benchmarks.
- 💰 **Automated Payroll Engine & Printable Payslips**: Process monthly payroll cycles, compute earnings, allowances, and tax deductions, and print professional employee payslips.
- 📢 **Broadcast Announcements & Event Calendar**: Organization-wide communication hub for HR announcements, notifications, and scheduled events.
- 🔒 **Role-Based Access Governance (RBAC)**: Strict role-separated portals for Admin, HR, Managers, and Employees with session validation.

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 🏗️ System Architecture & Visual Diagrams

### High-Level Architecture Overview

```mermaid
graph TD
    User[👨‍💼 Admin / HR / Employee Browser Client]
    Device[📟 ZKTeco Biometric Hardware Machine]
    
    subgraph Frontend [Vanilla JS & CSS Dashboard]
        UI[Portal UI - Admin / HR / User]
        Apex[ApexCharts Analytics & KPIs]
        Modals[Dynamic Modal Engine]
    end

    subgraph Backend [PHP 8.x Custom OOP/MVC Architecture]
        Router[App Router & Auth Middleware]
        Controllers[API & Page Controllers]
        ZK_Driver[App/Helpers/ZKLib - Socket Protocol Driver]
        Push_Endpoint[assets/api/biometric_push.php - ADMS Cloud Push Endpoint]
        Sync_Daemon[bin/biometric-sync.php - CLI Sync Daemon]
    end

    subgraph Database & Integrations
        MySQL[(🗄️ MySQL Database)]
        ChatRox[💬 ChatRox Messaging & Spreadsheet Sync]
    end

    User --> UI
    UI --> Router
    Router --> Controllers
    Controllers --> MySQL
    Controllers <--> ChatRox

    Device -->|UDP / TCP Socket Port 4370| ZK_Driver
    Device -->|ADMS HTTP POST Live Push| Push_Endpoint
    
    ZK_Driver <--> Sync_Daemon
    Sync_Daemon --> MySQL
    Push_Endpoint --> MySQL
```

### Attendance Ingestion & Biometric Processing Flow

```mermaid
sequenceDiagram
    autonumber
    actor Employee as Staff Member
    participant Machine as ZKTeco Biometric Device
    participant SyncEngine as Biometric Sync Daemon / ADMS Push
    participant DB as MySQL Database
    participant AdminUI as HRM Admin Dashboard

    Employee->>Machine: Fingerprint / RFID Card Punch
    alt UDP/TCP Polling Mode
        SyncEngine->>Machine: Initiate ZK Socket Handshake (Comm Key / Port)
        Machine-->>SyncEngine: Return Raw Punch Logs
    else ADMS HTTP Cloud Push Mode
        Machine->>SyncEngine: HTTP POST /assets/api/biometric_push.php
    end

    SyncEngine->>DB: Query Employee Shift Rules & Grace Period
    SyncEngine->>DB: Upsert Attendance Record (Clock-In / Clock-Out / Working Hours / Late In Status)
    SyncEngine-->>AdminUI: Broadcast WebSocket / Refresh Ingestion Logs
```

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 🚀 Core Features & Modules Breakdown

| Module | Description | Key Tech | Case Study Link |
| :--- | :--- | :--- | :--- |
| **Dynamic Biometric Integration** | GUI configuration for ZKTeco devices with live socket testing, auto-sync daemon, and ADMS HTTP push URL. | `PHP OOP` `ZKLib` `Sockets` | [<img src="https://img.shields.io/badge/Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/hrm-employee-management-payroll-portal) |
| **ChatRox Connected App** | Integrated spreadsheet sharing and messaging link between HRM and ChatRox workspace. | `PHP API` `REST` `JSON` | [<img src="https://img.shields.io/badge/Case_Study-0284c7?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/chatrox-real-time-messaging-professional-networking) |
| **Attendance & Grace Period Engine** | Automatic Shift matching, grace period calculation, late-in flags, and working hours calculation. | `MySQL` `PHP DateTime` | [<img src="https://img.shields.io/badge/Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/hrm-employee-management-payroll-portal) |
| **KPI & Performance Management** | KPI templates creation, department evaluation forms, employee rating matrices, and progress reporting. | `ApexCharts` `JS` `PHP` | [<img src="https://img.shields.io/badge/Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/hrm-employee-management-payroll-portal) |
| **Payroll Cycle & Payslip Printing** | Monthly salary calculations, tax/allowance adjustments, approval workflow, and printable PDF/HTML payslips. | `PHP DOM` `CSS Print` | [<img src="https://img.shields.io/badge/Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/hrm-employee-management-payroll-portal) |
| **Recruitment & Applicant Funnel** | Job posting management, applicant tracking system (ATS), candidate evaluation, and onboarding queue. | `PHP MVC` `File Uploads` | [<img src="https://img.shields.io/badge/Case_Study-7c3aed?style=flat-square&logo=googlechrome&logoColor=white" />](https://amaanullah.com/projects/hrm-employee-management-payroll-portal) |

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 🧰 Tech Stack & Libraries

<div align="center">

  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" />
  <img src="https://img.shields.io/badge/ApexCharts-65a30d?style=for-the-badge&logo=chartdotjs&logoColor=white" />
  <img src="https://img.shields.io/badge/ZKTeco-16a34a?style=for-the-badge&logo=fingerprint&logoColor=white" />
  <img src="https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white" />
  <img src="https://img.shields.io/badge/GitHub-181717?style=for-the-badge&logo=github&logoColor=white" />

</div>

<br/>

* **Primary Stack**: PHP 8.x (Custom OOP MVC) • MySQL 5.7+ / MariaDB
* **Biometric Driver**: `App/Helpers/ZKLib.php` (UDP/TCP binary socket protocol driver for ZKTeco devices)
* **Frontend UI**: Vanilla HTML5 • ES6 JavaScript • Vanilla CSS3 Design Tokens • Lucide Icons • ApexCharts
* **Connected Integrations**: ChatRox Real-Time Workspace Messaging • ZKTeco ADMS HTTP Web Push

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 📁 Repository Directory Structure

```
hrm-6-8-26/
├── app/                        # Core Application Backend (MVC)
│   ├── Controllers/            # Route Handlers & Controllers
│   ├── Core/                   # Router, Database, View Renderer, Auth
│   ├── Helpers/                # ZKLib Driver, Date & Calculation Helpers
│   ├── Middleware/             # Role & Session Authorization
│   └── Models/                 # Database Query Models
├── bin/                        # CLI Scripts & Background Daemons
│   ├── biometric-sync.php      # Dynamic Biometric Machine Sync Engine
│   └── test-zk.php             # Live Machine Socket Diagnostic Tool
├── config/                     # Application & Environment Configurations
│   └── config.php              # Global Database & App Settings
├── database/                   # Database Schemas & Migrations
│   └── new-hrm-schema.sql      # Database Schema & Structure
├── public/                     # Public Web Root & Assets
│   ├── assets/
│   │   ├── api/                # Connected Apps & Push Receivers
│   │   │   ├── biometric_push.php   # ADMS HTTP Web Push Endpoint
│   │   │   └── connected_apps_api.php # Connected Apps Controller
│   │   ├── css/                # Stylesheets
│   │   ├── images/             # System Logos (ZKTeco, Chatrox, User Avatars)
│   │   └── js/                 # Client Scripts (KPIs, Attendance, Dashboards)
│   └── index.php               # Application Entrypoint
├── storage/                    # Logs, Uploads, & Cache
├── uploads/                    # Employee Profiles, Resumes, ID Cards
└── views/                      # View Templates
    ├── admin/                  # Admin Management Pages & Connected Apps
    ├── hr/                     # HR Operations Views
    ├── partials/               # Shared Headers, Footers, & Sidebars
    └── user/                   # Employee Portal Pages
```

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 📦 Installation & Local Setup

### 1. Prerequisites
* Web Server (Apache with `mod_rewrite` enabled or Nginx)
* PHP 8.0 or higher (with `pdo_mysql`, `sockets` extensions enabled)
* MySQL 5.7+ or MariaDB 10.3+
* Composer

### 2. Environment Configuration
Clone the repository and set up environment variables:
```bash
git clone https://github.com/akamaanullah/hrm-v2.git
cd hrm-v2
cp .env.example .env
```

Update your `.env` file:
```env
DB_HOST=127.0.0.1
DB_NAME=hrm
DB_USER=root
DB_PASS=your_password
BASE_URL=http://localhost/hrm-6-8-26
```

### 3. Database Setup
1. Create a database named `hrm`.
2. Import the database schema from `database/new-hrm-schema.sql`.

### 4. Install Dependencies
```bash
composer install
```

### 5. Biometric Machine Connection Setup
1. Open Admin Portal -> **Settings** -> **Connected Apps**.
2. Click **Configure** on the **Attandance Machine** card.
3. Enter your ZKTeco device IP (e.g. `192.168.1.200`), socket port (`4370`), and Comm Key.
4. Click **Test Connection** to verify socket communication.
5. (Optional) Run the background sync daemon:
   ```bash
   php bin/biometric-sync.php --daemon --interval 10
   ```

<div align="right"><a href="#-table-of-contents"><img src="https://img.shields.io/badge/⬆_BACK_TO_TOP-0f172a?style=flat-square" /></a></div>

---

## 🌐 SEO & Software Engineering Services by Amaanullah

HRM Portal is engineered by **[Amaanullah](https://amaanullah.com)** as part of an enterprise Web & Systems Architecture showcase, specializing in:
* **Human Resource & Enterprise Systems**: Custom HRM, Payroll automation, Shift scheduling, and Biometric device integration.
* **Real-time Hardware & Software Integrations**: ZKTeco socket protocol drivers, ADMS HTTP Push receivers, and WebSockets.
* **Full-Stack Application Development**: High-performance PHP MVC platforms, Custom Dashboards, and RESTful APIs.

For technical consulting, custom software development, or enterprise solution implementation, visit **[amaanullah.com](https://amaanullah.com)**.

---

## 👨‍💻 Developer & Contact Info

* **Developer Name**: Amaanullah
* **Official Website**: [https://amaanullah.com](https://amaanullah.com)
* **Primary Email**: [akamaanullah@gmail.com](mailto:akamaanullah@gmail.com)
* **Official Contact Email**: [info@amaanullah.com](mailto:info@amaanullah.com)
* **GitHub Profile**: [github.com/akamaanullah](https://github.com/akamaanullah)

---

## 📄 License
This project is released under the **MIT License**.
