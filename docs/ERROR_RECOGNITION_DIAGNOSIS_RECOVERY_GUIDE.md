# Palompon Transit Terminal Management System (PTTM)
## Error Recognition, Diagnosis & Recovery Guide
### An In-Depth Usability Manual Based on Jakob Nielsen's Heuristic #9

---

### Executive Summary & Academic Framework

Jakob Nielsen's **9th Usability Heuristic** states:
> *"Error messages should be expressed in plain language (no codes), precisely indicate the problem, and constructively suggest a solution."*
> — **Nielsen Norman Group (NN/g)**

In high-stakes, fast-paced municipal transit environments like the Palompon Transit Terminal, user errors by dispatchers, administrators, or commuters can disrupt trip queues, create passenger confusion, or corrupt operational ledgers. 

The PTTM System implements a comprehensive **Three-Pillar Defensive Architecture**:
1. **Error Prevention**: Guardrails that eliminate error-prone conditions before an action is submitted.
2. **Clear Recognition & Precise Diagnosis**: Plain-language alerts explaining *what* happened, *why* it happened, and *which* entity is affected.
3. **Constructive & Reversible Recovery**: Action-oriented remedies, including 1-click **Undo** capabilities, automatic input clamping, transparent network failover, and self-service password recovery.

---

### Table of Contents
1. [The Three Pillars of Heuristic #9 in PTTM](#1-the-three-pillars-of-heuristic-9-in-pttm)
2. [Dispatcher & Queue Operational Errors](#2-dispatcher--queue-operational-errors)
   - [Accidental Trip Cancellation $\rightarrow$ 1-Click Undo Recovery](#21-accidental-trip-cancellation---1-click-undo-recovery)
   - [Premature Re-Queuing Cooldown Violations](#22-premature-re-queuing-cooldown-violations)
   - [Passenger Over-Capacity Clamping & Alerts](#23-passenger-over-capacity-clamping--alerts)
   - [Duplicate Queue Entry Prevention](#24-duplicate-queue-entry-prevention)
   - [Unauthorized Route Dispatch Attempts](#25-unauthorized-route-dispatch-attempts)
3. [Administrator Form & Data Management Errors](#3-administrator-form--data-management-errors)
   - [The "No-Change Guard" Architecture (`no-change-guard.js`)](#31-the-no-change-guard-architecture-no-change-guardjs)
   - [Plate Number Collision Detection (`check-plate`)](#32-plate-number-collision-detection-check-plate)
   - [Route Distance & Fare Matrix Validation](#33-route-distance--fare-matrix-validation)
   - [Overlapping Departure Rule Time Conflicts](#34-overlapping-departure-rule-time-conflicts)
4. [Commuter & Public Facing Errors](#4-commuter--public-facing-errors)
   - [Zero-Result Search Query Guidance](#41-zero-result-search-query-guidance)
   - [Invalid Feedback / Issue Report Form Inputs](#42-invalid-feedback--issue-report-form-inputs)
5. [Network, Connectivity & System Fallbacks](#5-network-connectivity--system-fallbacks)
   - [WebSocket Disconnections & Exponential Backoff](#51-websocket-disconnections--exponential-backoff)
   - [Graceful HTTP Polling Fallback](#52-graceful-http-polling-fallback)
   - [Session & CSRF Token Expiration](#53-session--csrf-token-expiration)
6. [Administrator Troubleshooting & Log Inspection Matrix](#6-administrator-troubleshooting--log-inspection-matrix)

---

### 1. The Three Pillars of Heuristic #9 in PTTM

```
+-----------------------------------------------------------------------------------+
|                        HEURISTIC #9 USABILITY ARCHITECTURE                        |
+-----------------------------------------------------------------------------------+
|  1. RECOGNIZE                   2. DIAGNOSE                     3. RECOVER         |
|  Plain-Language Signal          Precise Problem Statement       Actionable Remedy  |
|                                                                                   |
|  - Red/Amber Alert Banners     - Identifies specific plate     - 1-Click "Undo"   |
|  - High-Contrast Icons          - States rule violated          - Auto-clamping   |
|  - Accessible Typography        - Shows exact countdown         - One-tap Retry   |
|  - Zero cryptic SQL/HTTP codes  - Pinpoints conflicting field   - Self-service OTP|
+-----------------------------------------------------------------------------------+
```

---

### 2. Dispatcher & Queue Operational Errors

#### 2.1 Accidental Trip Cancellation $\rightarrow$ 1-Click Undo Recovery
- **The Problem**: In a noisy, fast-paced terminal, a dispatcher may accidentally click "Cancel Trip" on a vehicle row instead of "Start Boarding" or "Depart".
- **Traditional System Flaw**: Most systems delete the record permanently or require re-entering the vehicle from scratch, losing its arrival timestamp and queue priority.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: An amber toast notification immediately informs the dispatcher:  
    `"Trip for ABC-1234 has been canceled."`
  - **Diagnosis**: The vehicle is moved to `canceled` status, but preserved in the database.
  - **Recovery**: A prominent **[Undo Cancel]** action button appears directly on the notification banner and inside the queue interface.
  - **Result**: Clicking **Undo Cancel** sends an AJAX request (`POST /staff/queue/undoCancel/<id>`), restoring the trip to `waiting` status, recalculating queue positions in atomic transaction, and re-broadcasting it to all public monitors.

#### 2.2 Premature Re-Queuing Cooldown Violations
- **The Problem**: An operator returns to the terminal immediately after departing and pressures staff to add them back into the queue before other vehicles have had an opportunity to load.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: An inline warning alert prevents queue insertion.
  - **Diagnosis**: Instead of a generic error like *"Action Forbidden"* or code `403`, the system states:  
    `"Vehicle ABC-1234 departed recently. Please wait about 14 more minute(s) before adding it back."`
  - **Recovery**: The dispatcher can inform the driver of the exact remaining wait time. The vehicle's entry in the Available Vehicles pool automatically re-enables as soon as the 30-minute timer expires.

#### 2.3 Passenger Over-Capacity Clamping & Alerts
- **The Problem**: A dispatcher clicks the passenger "+" button too rapidly or mistypes a count of `24` on a 14-seater UV Express van.
- **PTTM Heuristic #9 Implementation**:
  - **Prevention & Clamping**: The client-side and server-side code (`Queue::setPassengers`) clamps the value strictly:
    $$\text{count} = \max(0, \min(\text{input}, \text{capacity}))$$
  - **Recognition & Diagnosis**: The count stops precisely at `14 / 14 (FULL)` with a visual red indicator.
  - **Recovery**: The dispatcher does not need to backspace or re-submit; the software prevents the illegal count automatically.

#### 2.4 Duplicate Queue Entry Prevention
- **The Problem**: Attempting to add a vehicle that is already in `waiting` or `boarding` status.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: A dismissible warning banner: `"ABC-1234 is already in the queue!"`
  - **Recovery**: The system filters out currently active vehicle IDs from the available selection pool (`whereNotIn('vehicles.id', $activeQueuedVehicleIds)`), eliminating double-entry at the UI level.

#### 2.5 Unauthorized Route Dispatch Attempts
- **The Problem**: A dispatcher assigned exclusively to the *Palompon-Ormoc* route attempts to check in or modify a vehicle bound for *Tacloban*.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition & Diagnosis**: `"You don't have access to the route for ABC-1234."`
  - **Recovery**: The attempt is logged as an unauthorized action attempt in security audit logs (`app/Controllers/Staff/Queue.php`), and the dispatcher is guided to contact an administrator for route assignment adjustments.

---

### 3. Administrator Form & Data Management Errors

#### 3.1 The "No-Change Guard" Architecture (`no-change-guard.js`)
- **The Problem**: An administrator opens a route or vehicle edit modal, makes no alterations, but accidentally hits "Save Changes". This typically triggers unnecessary database writes, creates spurious audit log entries, and broadcasts false update events across WebSockets.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition & Diagnosis**: The script takes a baseline snapshot of form state when loaded. If submitted with identical values, the submit event is intercepted and a friendly modal/banner states:
    > *"No changes detected — You didn't change anything, so nothing was updated."*
  - **Recovery**: A clean, single-click "OK" dismisses the modal without submitting. As soon as the administrator begins typing in any field, the notice immediately auto-clears.

#### 3.2 Plate Number Collision Detection (`check-plate`)
- **The Problem**: Typing an already registered vehicle plate number when registering new fleet units.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition & Diagnosis**: Real-time asynchronous validation triggers on the plate input field. If a duplicate is detected, a red warning badge indicates:  
    `"Plate number ABC-1234 is already registered to Operator: Juan Dela Cruz."`
  - **Recovery**: The administrator can click an instant link to view or edit the existing vehicle record rather than creating a duplicate.

#### 3.3 Route Distance & Fare Matrix Validation
- **The Problem**: Entering a negative base fare, zero distance, or negative rate per kilometer.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: Form fields are highlighted in soft red with explicit error messages below the field:  
    `"The distance field must contain a number greater than 0."`  
    `"Base fare must be at least ₱0.00."`
  - **Recovery**: The form retains all previously entered valid fields so the user only corrects the invalid numbers without starting over.

#### 3.4 Overlapping Departure Rule Time Conflicts
- **The Problem**: Creating two conflicting headway rules for the same terminal, route, and overlapping hours (e.g., Rule A: 08:00–10:00, Rule B: 09:00–11:00).
- **PTTM Heuristic #9 Implementation**:
  - **Recognition & Diagnosis**: The system validates interval boundaries and flags:  
    `"A departure rule already covers 09:00:00 to 10:00:00 for this route."`
  - **Recovery**: Highlights the conflicting rule with a direct link to edit its end time.

---

### 4. Commuter & Public Facing Errors

#### 4.1 Zero-Result Search Query Guidance
- **The Problem**: A commuter enters a misspelled search term (e.g., *"Ormok"* instead of *"Ormoc"* or an unregistered plate number) and encounters a blank screen.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: Instead of an empty white page or cryptic error, a friendly illustration with a magnifying glass appears.
  - **Diagnosis**: `"No active trips found matching 'Ormok'."`
  - **Recovery**: The interface constructively provides:
    1. A spelling suggestion (*"Did you mean Ormoc?"*).
    2. Quick-action filter chips for popular active destinations: **[Ormoc] [Tacloban] [Isabel] [Naval]**.
    3. A button to view all full daily timetables on the **Schedules** page.

#### 4.2 Invalid Feedback / Issue Report Form Inputs
- **The Problem**: Submitting a support inquiry with an invalid email address (`user@domain` without TLD) or missing explanation.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition & Diagnosis**: Inline feedback highlights the specific missing element:  
    `"Please provide a valid email address so we can reply to your report."`
  - **Recovery**: Keeps the commuter's written message intact in the textarea; never erases typed content on validation failure.

---

### 5. Network, Connectivity & System Fallbacks

#### 5.1 WebSocket Disconnections & Exponential Backoff
- **The Problem**: Terminal Wi-Fi drops momentarily, or mobile data fluctuates as a commuter travels through cellular dead zones.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: A small, unobtrusive connection pill displays `Reconnecting...` in amber.
  - **Diagnosis**: Detailed console logging (`logWarn('WS', 'Connection lost. Scheduling retry.')`).
  - **Recovery**: The client library (`ws-client.js`) initiates automatic exponential backoff:
    $$\text{Retry Delay} = \min(15000\text{ms}, \text{delay} \times 1.5)$$
    When the connection is restored, the pill seamlessly switches to `Live Sync Connected` in green, immediately fetching the latest queue snapshot.

#### 5.2 Graceful HTTP Polling Fallback
- If the WebSocket server is offline or blocked by a commuter's strict enterprise proxy, the system **automatically enables background HTTP polling** (`/api/queue-status`) every 20 seconds. Commuters continue to receive updated queue data without experiencing a broken interface.

#### 5.3 Session & CSRF Token Expiration
- **The Problem**: An administrator leaves a form open over lunch, returns, and submits after the CSRF token has expired.
- **PTTM Heuristic #9 Implementation**:
  - **Recognition**: A clear banner states:  
    `"Your session has expired. Please refresh the page and try again."`
  - **Recovery**: Protects against cross-site request forgery while preserving input state in session storage where applicable.

---

### 6. Administrator Troubleshooting & Log Inspection Matrix

When system-level errors occur, administrators can consult this quick diagnosis and recovery matrix:

| Error Symptom | Root Cause Diagnosis | Immediate Recovery Action |
| :--- | :--- | :--- |
| **"Queue updates not appearing in real-time"** | WebSocket daemon process has terminated. | 1. Check `writable/logs/ws.log`.<br>2. Restart daemon: `php spark ws:serve` or `bash start_system.sh`.<br>3. Verify browser network tab shows active WS connection to `/ws`. |
| **"Database connection failed"** | PostgreSQL service stopped or invalid credentials in `.env`. | 1. Inspect `writable/logs/log-YYYY-MM-DD.log`.<br>2. Run `systemctl status postgresql` (Linux) or check Services (Windows).<br>3. Verify port `5432`, user `jeepney_user`, and database `jeepneynvans`. |
| **"Vehicle cannot be added to queue"** | Vehicle status is `maintenance` or `inactive`, or 30-min cooldown is active. | 1. Check vehicle status in **Vehicle Register** (`/admin/vehicles`).<br>2. Review the cooldown banner for remaining minutes. |
| **"Dispatcher cannot view assigned route"** | Route mapping missing from dispatcher user account. | 1. Navigate to `/admin/users`.<br>2. Edit the dispatcher account and check the missing route checkbox. |
| **"Email notifications / password reset fails"** | SMTP authentication failure in `app/Config/Email.php` or `.env`. | 1. Verify `email.SMTPUser` and Google App Password (`email.SMTPPass`).<br>2. Check `writable/logs/` for SMTP handshake timeouts. |
