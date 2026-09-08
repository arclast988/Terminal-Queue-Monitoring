# Palompon Transit Terminal Management System (PTTM)
## Administrator & Super Administrator User Manual

---

### Table of Contents
1. [System Overview & Architecture](#1-system-overview--architecture)
2. [User Roles & Permissions](#2-user-roles--permissions)
3. [User & Staff Management](#3-user--staff-management)
4. [Terminal & Bay Management](#4-terminal--bay-management)
5. [Fleet & Vehicle Registry](#5-fleet--vehicle-registry)
6. [Vehicle Types & Capacity Settings](#6-vehicle-types--capacity-settings)
7. [Route & Fare Matrix Management](#7-route--fare-matrix-management)
8. [Departure Rules & Headway Scheduling](#8-departure-rules--headway-scheduling)
9. [Public Announcements & Emergency Advisories](#9-public-announcements--emergency-advisories)
10. [Audit Trails & Departure History Reports](#10-audit-trails--departure-history-reports)
11. [System Maintenance & Daemons](#11-system-maintenance--daemons)

---

### 1. System Overview & Architecture
The **Palompon Transit Terminal Management System (PTTM)** is a specialized terminal operations platform developed for the Municipality of Palompon, Leyte. The system monitors and coordinates public utility vehicles (PUJs / Jeepneys, UV Express Vans, and Modern Minibuses) connecting Palompon to key regional destinations including Ormoc City, Tacloban City, Isabel, Naval, Kananga, and surrounding municipalities.

The architecture comprises:
- **Backend Application**: CodeIgniter 4 (PHP 8+) MVC Framework.
- **Database**: PostgreSQL with atomic transactions and role-based data isolation.
- **Real-Time Communication**: WebSocket Server daemon (`public/ws-server.php`) integrated with client-side bi-directional sync (`ws-client.js`) and transparent HTTP polling fallback.
- **Frontend Layer**: Vanilla CSS Design System with accessible tokenization, high-contrast states, and responsive viewports (Desktop, Tablet, Mobile).

---

### 2. User Roles & Permissions

| Feature / Module | Super Administrator | Administrator | Dispatcher (Staff) | Commuter (Public) |
| :--- | :---: | :---: | :---: | :---: |
| **System Dashboard & Statistics** | Full Access | Full Access | Shift View | Public Queue View |
| **User & Staff Account Management** | Create, Edit, Delete, Reset | View & Reset | No Access | No Access |
| **Terminal & Bay Configuration** | Full Access | Full Access | View Only | View Only |
| **Vehicle Registration & Approval** | Full Access | Full Access | View Only | View Only |
| **Route & Fare Matrix Management** | Full Access | Full Access | View Only | View Fares Only |
| **Departure Rules Configuration** | Full Access | Full Access | View Only | Live Countdown |
| **Terminal Queue Operations** | Override / View | Override / View | Full Management | Live Monitor |
| **Announcements & Advisories** | Create, Edit, Delete | Create, Edit, Delete | Create & Edit | View Only |
| **Audit Logs & Security Trails** | Full Access + Print | Full Access + Print | No Access | No Access |
| **Departure History Reports** | Full Filter & Print | Full Filter & Print | Shift History | Search Departures |

---

### 3. User & Staff Management
**URL**: `/admin/users`

Administrators can configure system users, assign role privileges, and map dispatchers to specific terminal routes.

#### 3.1 Creating a New User Account
1. Navigate to **Management** $\rightarrow$ **Users** from the top navigation bar.
2. Click the **+ Add New User** button.
3. Fill out the user details:
   - **Username**: Unique identifier (3–30 characters, alphanumeric and underscore).
   - **Full Name**: Official full name of the staff member or administrator.
   - **Email**: Valid municipal or corporate email address for password recovery and notifications.
   - **Role**: Select `Admin` or `Staff` (Super Admin accounts can only be provisioned by existing Super Admins).
   - **Assigned Routes** *(Staff only)*: Select one or more routes the dispatcher is authorized to operate. Restricting routes ensures dispatchers only check in and manage queues under their jurisdiction.
   - **Initial Password**: Minimum 8 characters with a mix of uppercase, lowercase, numbers, and symbols.
4. Click **Create User**. The new account is active immediately.

#### 3.2 Modifying and Deactivating Users
- **Editing**: Click **Edit** beside any user to update their name, assigned routes, or email.
- **Password Reset**: If a user forgets their password, click **Reset Password** to assign a temporary password or prompt an OTP reset to their registered email.
- **Deactivation**: To revoke access without corrupting audit history, toggle the account status to **Inactive**. Inactive accounts cannot log in, but all historical queue and audit logs created by that account remain intact.

---

### 4. Terminal & Bay Management
**URL**: `/admin/terminals`

Terminals represent physical dispatch stations and boarding bays within the Palompon transport network.

1. Navigate to **Management** $\rightarrow$ **Terminals**.
2. To create a new terminal or bay, click **+ Add Terminal**.
3. Input the **Terminal Name** (e.g., `Palompon Central Terminal`), **Location / Address**, and **Description**.
4. Set the terminal status to `Active`. Routes and queues can now link to this terminal as an origin.

---

### 5. Fleet & Vehicle Registry
**URL**: `/admin/vehicles`

The vehicle register holds certified public utility vehicles authorized to operate within the Palompon Terminal.

#### 5.1 Registering a Vehicle
1. Navigate to **Management** $\rightarrow$ **Vehicle Register**.
2. Click **+ Add Vehicle**.
3. Fill in the mandatory technical parameters:
   - **Plate Number**: Standard LTO plate format (e.g., `ABC-1234` or `HAA-5678`). The system performs a live uniqueness check (`admin/vehicles/check-plate`).
   - **Vehicle Type**: Select from registered types (PUJ Jeepney, UV Express Van, Modern Minibus).
   - **Seating Capacity**: Total passenger capacity excluding the driver (e.g., 14 for UV Express, 18 for PUJ, 22 for Minibus).
   - **Default Route**: Primary route assigned to this vehicle.
   - **Operator / Franchise Owner Name**: Name of the franchise holder or cooperative.
   - **Default Driver Name**: Primary authorized driver.
   - **Status**: Set to `Active`.
4. Click **Save Vehicle**. The vehicle is immediately available in the dispatcher's "Ready to Queue" pool.

#### 5.2 Managing Vehicle Statuses
- **Active**: Vehicle is in good mechanical order and permitted to enter the queue.
- **Maintenance**: Vehicle is undergoing repairs. It is automatically hidden from the dispatcher's queue check-in list to prevent accidental dispatching.
- **Inactive / Suspended**: Franchise suspended or retired.

---

### 6. Vehicle Types & Capacity Settings
**URL**: `/admin/routes` (Vehicle Types Tab) or via settings

Administrators can adjust vehicle classification parameters:
- **Identifier**: `puj`, `van`, `minibus`.
- **Display Label**: Human-readable label (e.g., `PUJ (Jeepney)`, `UV Express Van`).
- **Default Capacity**: Default seat baseline applied to new registrations.
- **Icon / Badge**: Visual styling used across commuter queue displays.

---

### 7. Route & Fare Matrix Management
**URL**: `/admin/routes`

This module maintains all authorized destinations, route distances, base fares, and statutory discount percentages under LTFRB regulations.

#### 7.1 Creating / Editing a Route
1. Click **+ Add Route**.
2. Specify the **Origin Terminal** (Palompon) and **Destination** (e.g., `Ormoc City`, `Tacloban City`, `Isabel`).
3. Enter the official **Distance (Kilometers)**.
4. Configure the fare matrix:
   - **Base Fare (₱)**: Flat fare covering the first 4 kilometers.
   - **Rate per Additional Km (₱)**: Incremental fare per kilometer thereafter.
5. Click **Save Route**.

#### 7.2 Configuring Fare Discounts
Under LTFRB mandates, the system supports percentage and fixed discounts:
- **Student Discount**: 20% statutory concession upon presentation of valid school ID.
- **Senior Citizen Discount**: 20% statutory concession upon presentation of OSCA ID.
- **Person with Disability (PWD)**: 20% statutory concession upon presentation of PWD ID.
- The system automatically calculates discounted rates on the public **Route Fares** page (`/fares`) so commuters and conductors have 100% price transparency.

---

### 8. Departure Rules & Headway Scheduling
**URL**: `/admin/departure-rules`

Departure rules dictate target vehicle departure intervals (headways) based on time of day, terminal, and route.

#### 8.1 How Departure Rules Work
When a vehicle enters the queue or transitions to **Boarding**, the system matches the current local time against active departure rules for that route and calculates the **Estimated Departure Time (ETD)**:
- **Peak Hours Rule** (e.g., 06:00 AM – 09:00 AM & 04:30 PM – 07:00 PM): Shorter wait time (e.g., 15–20 minutes) due to high passenger volume.
- **Off-Peak Rule** (e.g., 09:01 AM – 04:29 PM): Standard headway interval (e.g., 30–45 minutes).
- **Minimum Passengers Threshold**: Minimum passenger load required before early departure is triggered.

#### 8.2 Adding a Departure Rule
1. Navigate to **Management** $\rightarrow$ **Departure Rules**.
2. Click **+ Add Departure Rule**.
3. Select the **Route** and target **Terminal**.
4. Set the **Start Time** and **End Time** (e.g., `06:00:00` to `09:00:00`).
5. Enter **Wait Minutes** (e.g., `20`).
6. Enter an intuitive **Rule Label** (e.g., `Morning Peak - Ormoc Express`).
7. Click **Save Rule**.

---

### 9. Public Announcements & Emergency Advisories
**URL**: `/admin/announcements`

Announcements are broadcast live to all connected commuters and staff screens:
- Displays on the rolling **Advisory Marquee** at the top of all guest pages.
- Populates the **Announcements Modal** accessible by clicking the bullhorn icon.
- Can be published by both Admins and Staff.

#### 9.1 Publishing an Announcement
1. Navigate to **Announcements** in the top navigation.
2. Click **+ New Announcement**.
3. Type the **Message Text** (concise, clear, and urgent if related to weather, typhoon signals, road closures, or port delays).
4. Set the **Priority**: `Normal` (informational) or `High` (urgent safety/delay advisory).
5. Set the **Status** to `Active`.
6. Click **Publish**. The announcement appears instantly across all devices without requiring a page reload.

---

### 10. Audit Trails & Departure History Reports
**URLs**: `/admin/logs` & `/admin/history`

#### 10.1 Audit Logs (`/admin/logs`)
Provides a tamper-resistant security trail of every mutation within the system:
- User identity & Role.
- Action type (e.g., `Add to queue`, `Start Boarding`, `Depart Vehicle`, `Cancel Trip`, `Undo Cancel Trip`, `Update Driver`).
- Action details (Vehicle plate, operator name, destination, previous value $\rightarrow$ new value).
- Timestamp and client IP address.
- Includes a **Print Logs** feature formatted for administrative reporting and compliance audits.

#### 10.2 Departure History (`/admin/history`)
Tracks all historical trips that have departed from the terminal:
- Filter by date range, route, vehicle type, or specific plate number.
- Summarizes daily vehicle counts, passenger totals, and on-time performance.
- Features a **Print Report** button generating an official departure ledger for LGU records.

---

### 11. System Maintenance & Daemons

#### 11.1 WebSocket Server Management
The real-time sync server runs on PHP CLI:
```bash
# Start the WebSocket server (in background)
php spark ws:serve
# or via dedicated script
bash start_system.sh
```
If the WebSocket process stops, the web application automatically switches to 20-second HTTP polling without interrupting user sessions.

#### 11.2 Database Migrations & Seeds
```bash
# Run latest migrations
php spark migrate
# Re-seed test or initial user records
php spark db:seed UserSeeder
```
