# Palompon Transit Terminal Management System (PTTM)
## Dispatcher & Terminal Staff User Manual

---

### Table of Contents
1. [Introduction for Dispatchers](#1-introduction-for-dispatchers)
2. [Shift Login & Terminal Setup](#2-shift-login--terminal-setup)
3. [Understanding the Queue Interface](#3-understanding-the-queue-interface)
4. [Checking Vehicles into the Queue](#4-checking-vehicles-into-the-queue)
5. [The 30-Minute Departure Cooldown Rule](#5-the-30-minute-departure-cooldown-rule)
6. [Managing Vehicle Status: Waiting to Boarding](#6-managing-vehicle-status-waiting-to-boarding)
7. [Live Passenger Loading Counter](#7-live-passenger-loading-counter)
8. [Updating Assigned Drivers on Duty](#8-updating-assigned-drivers-on-duty)
9. [Dispatching a Vehicle (Departure)](#9-dispatching-a-vehicle-departure)
10. [Cancelling a Trip & 1-Click Undo Cancel](#10-cancelling-a-trip--1-click-undo-cancel)
11. [Monitoring Departure Rules & Headway Timers](#11-monitoring-departure-rules--headway-timers)
12. [Publishing Terminal Announcements](#12-publishing-terminal-announcements)

---

### 1. Introduction for Dispatchers
As a **Terminal Dispatcher / Staff Member**, you play a central role in keeping the Palompon Transit Terminal running smoothly. Your daily responsibilities include:
- Registering arriving vehicles into their assigned route queues in First-In, First-Out (FIFO) order.
- Moving vehicles into the active boarding bay when it is their turn.
- Tracking passenger boarding counts in real-time.
- Dispatching vehicles once full or when scheduled departure timers expire.
- Ensuring safety, passenger convenience, and route compliance.

All actions you take on the dispatcher portal reflect **instantly** on the public terminal screens and passengers' smartphones via the real-time sync system.

---

### 2. Shift Login & Terminal Setup
1. Open any modern browser (Chrome, Edge, Firefox, or Safari on Desktop, Tablet, or Smartphone).
2. Go to the terminal address: `http://<terminal-url>/login`.
3. Enter your **Username** and **Password**.
4. Upon authentication, you will be redirected to the **Staff Dashboard** (`/staff/dashboard`).
5. Verify your assigned routes displayed on the dashboard cards. If a route you need to manage is missing, request an Administrator to add that route to your account in User Management.

---

### 3. Understanding the Queue Interface
**URL**: `/staff/queue`

The Queue Management screen is divided into two primary zones:
1. **Ready Vehicles Panel (Available Pool)**:
   - Lists all certified, active vehicles assigned to your authorized routes that are currently physically present at the terminal and ready to be queued.
   - Shows plate number, vehicle type (PUJ, UV Express, Minibus), operator name, default driver, and last departure status.
2. **Active Queue Board**:
   - Displays vehicles currently queued for departure, sorted by route and departure priority.
   - Columns show **Queue Position (#1, #2, etc.)**, **Plate Number**, **Status Badge**, **Passengers Loaded**, **Assigned Driver**, **Estimated Departure (ETD)**, and **Action Controls**.

---

### 4. Checking Vehicles into the Queue

#### 4.1 Step-by-Step Check-In
1. Navigate to **Queue Management** (`/staff/queue`).
2. In the **Available Vehicles** panel, find the vehicle that has arrived at the terminal staging area.
3. You can select a single vehicle or check multiple vehicle checkboxes for bulk queue entry.
4. Click **+ Add to Queue** (or check-in button).
5. The system performs the following automatic checks:
   - Verifies the vehicle is not already active in the queue.
   - Verifies that the vehicle has satisfied the 30-minute departure cooldown.
   - Matches the current time to the active **Departure Rule** for that route to assign an accurate **Estimated Departure Time (ETD)**.
   - Appends the vehicle to the queue in strict chronological FIFO order.
6. A success alert will display: `"[Plate Number] added to queue."`

---

### 5. The 30-Minute Departure Cooldown Rule

To prevent accidental double-queuing and ensure fair rotation among transport operators, the system enforces a **30-minute cooldown**:
- Once a vehicle departs (`status = departed`), it cannot be immediately re-entered into the queue.
- If an operator arrives early and you try to add them before 30 minutes have elapsed, the system prevents the action and displays a helpful notice:
  > *"Vehicle [Plate] departed recently. Please wait about X more minute(s) before adding it back."*
- You do not need to calculate the time manually; the system automatically tracks the elapsed minutes and unlocks the vehicle as soon as the cooldown expires.

---

### 6. Managing Vehicle Status: Waiting to Boarding

A vehicle in the queue moves through three operational states:
$$\text{Waiting} \longrightarrow \text{Boarding} \longrightarrow \text{Departed}$$

#### 6.1 Status Definitions:
- **Waiting (Yellow / Amber Badge)**: Vehicle is parked in the staging area awaiting its turn. Passengers can see its upcoming position.
- **Boarding (Blue / Green Badge)**: Vehicle is positioned at the active terminal boarding bay. Passengers are actively boarding and paying fares.
- **Departed (Gray / Completed)**: Vehicle has left the terminal.

#### 6.2 Transitioning to Boarding:
1. When the current boarding vehicle leaves, locate the **#1 Waiting** vehicle for that destination route.
2. Click the **Start Boarding** button.
3. The status shifts to `Boarding`.
4. **Key Rule**: The departure headway countdown recalculates from the exact moment boarding begins, giving passengers and operators a fresh, accurate countdown window.

---

### 7. Live Passenger Loading Counter

Accurate passenger counts ensure commuters know seat availability before walking to the bay.

1. On any vehicle in `Boarding` status, locate the **Passenger Counter** field.
2. Use the **+** and **-** buttons (or type the exact number) to update passenger boardings.
3. **Automatic Safeguards**:
   - The counter is debounced to avoid network congestion while you click.
   - The system **clamps** passenger counts strictly between `0` and the vehicle's certified **Maximum Seating Capacity** (e.g., maximum 14 for UV Express Vans). You can never accidentally enter 15 passengers on a 14-seat van.
   - When the count reaches maximum capacity, the badge displays **FULL**, alerting passengers to queue for the next vehicle.

---

### 8. Updating Assigned Drivers on Duty

Occasionally, a relief driver takes the wheel or a driver swap occurs before departure:
1. On the vehicle row in the queue, click the **Driver Name** (or click the edit pencil icon).
2. Enter the name of the new driver on duty (2–100 characters).
3. Click **Save** (or press Enter).
4. The system updates the driver in the active queue and logs the change to the permanent audit trail.
5. The public screen immediately updates with the driver's name.

---

### 9. Dispatching a Vehicle (Departure)

A vehicle is dispatched when:
- It reaches full seating capacity, OR
- Its scheduled departure interval (ETD) expires, OR
- Minimum load requirement is satisfied during off-peak hours.

#### 9.1 How to Dispatch:
1. Click the **Depart Vehicle** (or Dispatched) button on the boarding vehicle.
2. The system:
   - Timestamps the official departure time (`departure_time = NOW`).
   - Removes the vehicle from the active queue.
   - Automatically advances the next vehicle in line to Position #1.
   - Logs the completed trip to the Departure History ledger.
   - Broadcasts the departure to the public dashboard.

---

### 10. Cancelling a Trip & 1-Click Undo Cancel

If a vehicle experiences mechanical failure, tire puncture, or emergency:

#### 10.1 Cancelling a Trip:
1. Click the **Cancel Trip** button on the affected vehicle row.
2. Confirm the cancellation prompt.
3. The vehicle is removed from the active queue so commuters do not wait for a non-operational trip.

#### 10.2 Accidental Cancellation? Use 1-Click "Undo Cancel":
If you clicked **Cancel** by mistake:
1. Do not re-register the vehicle from scratch!
2. A temporary recovery alert will display on your screen with an **Undo Cancel** button.
3. Click **Undo Cancel**.
4. The system automatically restores the vehicle to its exact prior queue position, restores its arrival timestamp, and re-broadcasts it to the public monitor without disrupting other vehicles.

---

### 11. Monitoring Departure Rules & Headway Timers
**URL**: `/staff/departure-rules`

Dispatchers can view the active departure interval rules configured by the terminal administration:
- See peak hours vs off-peak hours for each destination.
- View standard waiting intervals (e.g., 20 mins for Ormoc, 30 mins for Tacloban).
- Use these schedules as your guide for dispatch timing.

---

### 12. Publishing Terminal Announcements
**URL**: `/admin/announcements`

Dispatchers have permission to post live announcements:
1. Navigate to **Announcements**.
2. Click **+ New Announcement**.
3. Enter messages such as:
   - *"Weather Advisory: Heavy rain along Kananga-Ormoc road. Minor travel delays expected."*
   - *"Notice: Temporary boarding bay reassignment for Naval route to Bay 3."*
4. Click **Publish**. It displays on the public marquee immediately.
