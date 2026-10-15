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
12. [System Branding, Operational Rules & Data Retention](#12-system-branding-operational-rules--data-retention)

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
| **Departure Rules Configuration** | Full Access | Full Access | Create, Edit, Delete within assigned destinations and terminals | View Rules & Live Countdown |
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

#### 5.1 Registering a Vehicle & Photo Upload
1. Navigate to **Management** $\rightarrow$ **Vehicle Register**.
2. Click **+ Add Vehicle** (or click Edit on an existing vehicle).
3. Fill in the technical and operational parameters:
   - **Plate Number**: Standard LTO plate format (e.g., `ABC-1234` or `HAA-5678`). The system performs a live uniqueness check (`admin/vehicles/check-plate`).
   - **Vehicle Type**: Select from registered dynamic types (PUJ Jeepney, UV Express Van, Modern Minibus, Bus).
   - **Seating Capacity**: Total passenger capacity excluding the driver (e.g., 14 for UV Express, 18 for PUJ, 22 for Minibus).
   - **Default Route**: Primary destination route assigned to this vehicle.
   - **Operator / Franchise Owner Name**: Name of the franchise holder or cooperative.
   - **Default Driver Name**: Primary authorized driver.
   - **Vehicle Registration Photo Upload**:
     - Drag-and-drop or select an official vehicle image file (`.jpg`, `.jpeg`, `.png`, `.webp` up to 2MB).
     - The interface generates an immediate real-time thumbnail preview and includes a one-click **Remove Photo** action.
     - When saved, the photo is stored securely in `/uploads/vehicles/` and displayed prominently on dispatcher queue cards, fleet registries, and passenger trip searches.
   - **Status**: Set to `Active`.
4. Click **Save Vehicle**. The vehicle is immediately available in the dispatcher's "Ready to Queue" pool.

#### 5.2 Managing Vehicle Statuses
- **Active**: Vehicle is in good mechanical order and permitted to enter the queue.
- **Maintenance**: Vehicle is undergoing repairs. It is automatically hidden from the dispatcher's queue check-in list to prevent accidental dispatching.
- **Inactive / Suspended**: Franchise suspended or retired.

---

### 6. Dynamic Vehicle Types & Capacity Settings
**URL**: `/admin/routes` (Vehicle Types Tab) or `/admin/vehicle-types`

Administrators and Super Administrators can dynamically add, update, and manage vehicle types:
- **Slug / Identifier**: Unique slug (e.g., `puj`, `van`, `minibus`, `bus`).
- **Display Label**: Human-readable label (e.g., `PUJ (Jeepney)`, `UV Express Van`, `Modern Minibus`).
- **Brand Color & Icon**: Select an accent color (e.g., `#B71C1C`, `#0284c7`) and FontAwesome icon (e.g., `fa-bus`, `fa-van-shuttle`) used across badges and public monitors.
- **Default Capacity**: Default seat baseline applied automatically to new registrations.
- **Active Status**: Toggle active or inactive to control whether the type appears in queue filters and registration dropdowns.

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

Departure rules set target vehicle departure intervals by terminal, destination, weekday, and round. Administrators and superadministrators manage all rules. Dispatchers can create, edit, and delete rules for assigned destinations and terminal-wide defaults at terminals where they dispatch, using `/staff/departure-rules`.

#### 8.1 How Departure Rules Work
The scheduler uses the selected round and applicable weekdays to calculate the **Estimated Departure Time (ETD)**:
- **Destination and Day Priority**: Destination rules take priority over terminal-wide defaults; specific-day rules take priority over every-day defaults for the same round and destination.
- **Shared Rounds**: All vehicle types serving the same destination at the same terminal share the active round. Dispatchers switch configured rounds using **Queue Management > Round**. A round is a rule period, not a vehicle's trip count.
- **Boarding and Departure Times**: Automatic boarding uses five-minute clock boundaries. **Start boarding now** starts the next vehicle immediately, using the configured interval and rounding the departure target up to a five-minute clock mark. A late boarding vehicle stays first until its departure is recorded.
- **Round Changes**: Switching rounds changes waiting and boarding trips while preserving elapsed boarding time.
- **Manual Departure**: A full vehicle may leave early, but the dispatcher must record **Depart**. A full count or expired timer does not automatically record a departure.
- **New Service Day**: At midnight in Asia/Manila, rounds return to Round 1, unfinished trips are canceled, and saved daily vehicle starting order applies again.

#### 8.2 Adding a Departure Rule
1. Navigate to **Management** $\rightarrow$ **Departure Rules**.
2. Click **+ Add Departure Rule**.
3. Select the required **Terminal** and optional **Destination**. Leave the destination blank for a terminal-wide default; a destination rule uses that destination's terminal.
4. Check the operating weekdays or **Every day**, and explicitly choose a **Round** from 1 to 999.
5. Set the **Start Time** and **End Time**. Admin forms use 24-hour inputs; dispatcher forms use AM/PM pickers. The end must be later than the start; split a period crossing midnight into separate rules.
6. Set the interval using the duration input on admin forms or minutes on dispatcher forms, and optionally enter a **Rule Label**.
7. Click **Save Rule**. A duplicate round in the same destination or terminal scope on overlapping selected days must be edited or assigned another round.

See [Dispatcher days, rounds, and boarding windows](DISPATCH_ROUNDS.md) for the detailed scheduling behavior.

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
If the WebSocket process stops while internet access remains available, background HTTP refreshes continue and the live connection retries automatically. Refresh timing varies by page and connection state. A device that is fully offline cannot receive fresh queue information.

#### 11.2 Database Migrations & Seeds
```bash
# Run latest migrations
php spark migrate
# Re-seed test or initial user records
php spark db:seed UserSeeder
```

---

### 12. System Branding, Operational Rules & Data Retention
**URL**: `/admin/settings` (Super Administrator only)

Super Administrators have granular control over system identity, theme palettes, and critical operational intervals:

#### 12.1 Vehicle Queue Re-Entry Cooldown Interval
- **Purpose**: Prevents premature re-queuing and enforces fair vehicle rotation among transit operators.
- **Default**: 30 minutes.
- **Customization**: Can be adjusted in minutes or hours (e.g., 15m, 30m, 45m, 1h, 2h, 4h) or set to `0` to disable the cooldown entirely for rapid turnaround.
- **Enforcement**: Departed vehicles remain hidden from dispatchers and rejected by API checks until the cooldown expires.

#### 12.2 System Audit Logs Retention Policy
- **Purpose**: Governs how many days security logs are retained before automated maintenance purge.
- **Default**: 60 days.
- **Customization**: Configurable from 1 to 3,650 days (presets: 30, 60, 90, 180, 365 days).
- **Automated Prune**: Evaluated every 6 hours and during report generation.

#### 12.3 Departure History Retention Policy
- **Purpose**: Governs how many days completed trip ledgers are preserved before being archived/purged.
- **Default**: 60 days.
- **Customization**: Configurable from 1 to 3,650 days (presets: 30, 60, 90, 180, 365 days).
- **Automated Prune**: Evaluated automatically during maintenance routines and history views.

