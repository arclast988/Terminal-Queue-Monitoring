# Palompon Transit Terminal Management System (PTTM)
## Dispatcher & Terminal Staff User Manual

---

### Table of Contents
1. [Introduction for Dispatchers](#1-introduction-for-dispatchers)
2. [Shift Login & Terminal Setup](#2-shift-login--terminal-setup)
3. [Understanding the Queue Interface](#3-understanding-the-queue-interface)
4. [Checking Vehicles into the Queue](#4-checking-vehicles-into-the-queue)
5. [The Departure Cooldown Rule](#5-the-departure-cooldown-rule)
6. [Managing Vehicle Status: Waiting to Boarding](#6-managing-vehicle-status-waiting-to-boarding)
7. [Live Passenger Loading Counter](#7-live-passenger-loading-counter)
8. [Updating Assigned Drivers on Duty](#8-updating-assigned-drivers-on-duty)
9. [Dispatching a Vehicle (Departure)](#9-dispatching-a-vehicle-departure)
10. [Cancelling a Trip & 1-Click Undo Cancel](#10-cancelling-a-trip--1-click-undo-cancel)
11. [Configuring Departure Rules, Days & Rounds](#11-configuring-departure-rules-days--rounds)
12. [Publishing Terminal Announcements](#12-publishing-terminal-announcements)
13. [Account Security & Self-Service Password Change](#13-account-security--self-service-password-change)
14. [Quick Help Guide & Support](#14-quick-help-guide--support)
15. [Frequently Asked Questions](#15-frequently-asked-questions)

---

### 1. Introduction for Dispatchers
As a **Terminal Dispatcher / Staff Member**, you play a central role in keeping the Palompon Transit Terminal running smoothly. Your daily responsibilities include:
- Adding arriving vehicles to their assigned destination queues and managing the waiting order.
- Configuring departure rules for assigned destinations and terminal-wide defaults at terminals where you dispatch.
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
   - Lists active registered vehicles assigned to your authorized routes that are not already queued and have completed any applicable cooldown. Confirm that a vehicle is physically present before adding it.
   - Uses the saved **Daily starting order**, then today's departure rotation, for each destination and terminal. Different vehicle types serving that destination share the order.
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
   - Verifies that the vehicle has satisfied the post-departure cooldown period (default 30 minutes, or custom interval configured by Superadmin).
   - Matches the current time to the active **Departure Rule** for that route to assign an accurate **Estimated Departure Time (ETD)**.
   - Appends selected vehicles to the destination's waiting queue in selection order. Use **Manage Queue** to adjust waiting positions; a boarding vehicle stays first.
6. A success alert will display: `"[Plate Number] added to queue."`

---

### 5. The Departure Cooldown Rule

To prevent accidental double-queuing and ensure fair rotation among transport operators, the system enforces a post-departure cooldown period (default **30 minutes**, configurable by the Superadmin in System Settings):
- Once a vehicle departs (`status = departed`), it cannot be immediately re-entered into the queue until the cooldown expires.
- If an operator arrives early and you try to add them before the cooldown has elapsed, the system prevents the action and displays a helpful notice:
  > *"Vehicle [Plate] departed recently. Please wait about X more minute(s) before adding it back."*
- You do not need to calculate the time manually; the system automatically tracks the elapsed minutes and unlocks the vehicle as soon as the cooldown expires. (If the Superadmin has disabled the cooldown by setting it to 0 minutes, vehicles can be re-queued immediately).
- At midnight in **Asia/Manila**, yesterday's departure badges and cooldown no longer apply. Unfinished waiting and boarding trips are canceled, daily rounds return to Round 1, and the saved vehicle starting order applies again. Add a new trip for the new day instead of restoring yesterday's trip.

---

### 6. Managing Vehicle Status: Waiting to Boarding

A vehicle in the queue moves through three operational states:
$$\text{Waiting} \longrightarrow \text{Boarding} \longrightarrow \text{Departed}$$

#### 6.1 Status Definitions:
- **Waiting (Yellow / Amber Badge)**: Vehicle is parked in the staging area awaiting its turn. Passengers can see its upcoming position.
- **Boarding (Blue / Green Badge)**: Vehicle is positioned at the active terminal boarding bay. Passengers are actively boarding and paying fares.
- **Departed (Gray / Completed)**: Vehicle has left the terminal.

#### 6.2 Transitioning to Boarding:
1. When the current boarding vehicle leaves, the next vehicle automatically starts boarding at its assigned five-minute clock boundary (for example, 6:45 AM after a 6:43 AM departure).
2. During busy periods, use **Start boarding now** to start the next vehicle immediately. It cannot skip a vehicle ahead or overlap an existing boarding vehicle.
3. The active round's interval determines the departure target, rounded up to a five-minute clock mark. Repeated boarding clicks do not restart the timer.
4. A full vehicle or expired timer still needs the dispatcher to record **Depart**; neither automatically records a departure.

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
4. The system restores an eligible canceled trip from today to `Waiting`, preserves its arrival timestamp, reinserts it into the queue, and recalculates departure targets. The vehicle and route must still be active, the vehicle must not already be queued, and terminal capacity must be available. Yesterday's trips cannot be restored into today.

---

### 11. Configuring Departure Rules, Days & Rounds
**URL**: `/staff/departure-rules`

Dispatchers can **create, edit, and delete** rules for their assigned destinations and terminal-wide defaults at terminals where they dispatch. Rules outside those assignments cannot be managed.

1. Open **Departure Rules** and choose **Add Rule**, or edit an existing rule.
2. Choose the terminal and destination. Leave **Destination** blank for a terminal-wide default; the terminal is required.
3. Select the operating weekdays or **Every day**, and explicitly choose a **Round** from 1 to 999.
4. Use the time picker to set the start and end times with **AM/PM**. The end must be later than the start; use separate rules for a period crossing midnight.
5. Enter the interval in minutes, optionally add a label, and save.
6. To remove a rule, use its **Delete** action and confirm. A duplicate round for the same destination or terminal scope on overlapping selected days must be edited or assigned another round.

In **Queue Management**, open **Round** and select the destination's configured round. Rounds are shared by vehicle types serving the same destination at the same terminal; they are not a vehicle's trip count. A round with **No active rule** cannot be selected: its rule must match the current weekday, time window, and destination. Destination rules take priority over terminal defaults, and specific-day rules over every-day defaults for the same round and destination.

Switching rounds updates waiting and boarding trips. Boarding time already elapsed is preserved. For example, switching from 20 to 40 minutes after 30 minutes of boarding leaves 10 minutes remaining, subject to the five-minute departure clock marks.

See [Dispatcher days, rounds, and boarding windows](DISPATCH_ROUNDS.md) for detailed examples.

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

---

### 13. Account Security & Self-Service Password Change
**URL**: `/change-password`

Dispatchers can update their login credentials without needing administrator intervention:
1. Open your profile menu from the top bar and click **Change Password**.
2. Click **Send Verification Code**. The system sends a 6-digit verification code to your registered email address (valid for 10 minutes).
3. Enter your **Current Password**, **New Password**, and the **6-Digit Verification Code**.
4. Click **Update Password**.
5. *Security Protection*: Accounts lock automatically for 15 minutes after 5 consecutive incorrect login attempts to protect against brute-force attacks.

---

### 14. Quick Help Guide & Support
**URL**: `/staff/help`

For operational assistance during your shift, access the interactive **Dispatcher Quick Help Guide** directly from the top navigation or sidebar. It provides step-by-step procedures, troubleshooting tips, and answers to common queue scenarios.

### 15. Frequently Asked Questions

**Can dispatchers configure departure rules?**

Yes. Open `/staff/departure-rules` to create, edit, or delete rules for your assigned destinations and terminal-wide defaults at terminals where you dispatch. Choose weekdays, a round, a time window, and an interval in minutes.

**How do I change the active round?**

Open **Queue Management > Round** and choose a configured round for the destination. This updates that destination's waiting and boarding trips across vehicle types while preserving elapsed boarding time.

**Why can I not save or select a round?**

Check your route assignment, selected weekdays, required round, time window, and interval. A duplicate day/round in the same scope must be edited or given another round. **No active rule** means a rule must match that destination, the current weekday, and the current time before selecting the round.

**Does FULL or an overdue timer automatically depart a vehicle?**

No. Record **Depart** when the vehicle actually leaves. Automatic boarding advances only the next vehicle when its destination has no vehicle still boarding.
