# Palompon Transit Terminal Management System (PTTM)
## Commuter & Passenger User Guide

---

### Table of Contents
1. [Welcome to Palompon Transit Terminal](#1-welcome-to-palompon-transit-terminal)
2. [Accessing the Terminal Monitor on Mobile & PC](#2-accessing-the-terminal-monitor-on-mobile--pc)
3. [Reading the Live Terminal Queue](#3-reading-the-live-terminal-queue)
4. [Understanding Vehicle Status Badges](#4-understanding-vehicle-status-badges)
5. [Checking Seat Availability & Departure Countdown](#5-checking-seat-availability--departure-countdown)
6. [Looking Up Route Fares & Statutory Discounts](#6-looking-up-route-fares--statutory-discounts)
7. [Daily Departure Schedules](#7-daily-departure-schedules)
8. [Searching for Trips with Autocomplete](#8-searching-for-trips-with-autocomplete)
9. [Public Advisories, Weather Alerts & Announcements](#9-public-advisories-weather-alerts--announcements)
10. [Frequently Asked Questions (FAQ)](#10-frequently-asked-questions-faq)
11. [Getting Help, Submitting Feedback & Reporting Issues](#11-getting-help-submitting-feedback--reporting-issues)

---

### 1. Welcome to Palompon Transit Terminal
The **Palompon Transit Terminal Monitoring System (PTTM)** provides real-time public travel information for commuters traveling to and from Palompon, Leyte. Whether you are traveling by **PUJ (Jeepney)**, **UV Express Van**, or **Modern Minibus**, this guide helps you check queues, plan departure times, calculate official fares, and stay updated on terminal operations.

---

### 2. Accessing the Terminal Monitor on Mobile & PC
- **Website URL**: Open your mobile or desktop browser and navigate to the terminal homepage (`http://<terminal-domain>/` or `/guest`).
- **No App Installation Required**: The system runs directly in any modern web browser (Google Chrome, Safari, Samsung Internet, Edge, Mozilla Firefox).
- **Responsive Layout**: Designed specifically to adapt to smartphones, tablets, and large overhead display monitors inside the terminal passenger lounge.

---

### 3. Reading the Live Terminal Queue
**URL**: `/` or `/guest`

On the homepage dashboard, you will find the **Live Terminal Monitor**:
1. **Vehicle Type Tabs / Filters**:
   - Filter trips by **All**, **PUJ (Jeepney)**, **UV Express Van**, or **Minibus**.
2. **Route Cards**:
   - Organized by destination (e.g., **Ormoc City**, **Tacloban City**, **Isabel**, **Naval**, **Kananga**).
3. **Queue Information per Vehicle**:
   - **Queue Order**: Position #1 is the active boarding trip; Position #2 and higher are queued behind it.
   - **Plate Number**: Registered license plate of the vehicle.
   - **Operator & Driver Name**: Franchise operator and driver currently on duty.
   - **Seating Progress**: Visual bar showing occupied seats out of total capacity.
   - **Estimated Departure (ETD)**: Clock time or countdown minutes until the vehicle departs.

---

### 4. Understanding Vehicle Status Badges

| Badge | Color | What It Means for Commuters |
| :--- | :--- | :--- |
| **Boarding** | **Blue / Green** | The vehicle is physically at the boarding bay. Passengers are boarding now. Head to the bay to secure your seat! |
| **Waiting** | **Yellow / Amber** | The vehicle has checked into the terminal and is waiting in line. It will move to Boarding as soon as the current vehicle departs. |
| **Next in Line** | **Orange** | Position #1 among waiting vehicles. This vehicle will board immediately next. |
| **Full** | **Red** | All seats on the vehicle have been occupied. It will depart momentarily. Commuters should prepare for the next vehicle. |
| **Departed** | **Slate / Gray** | The vehicle has officially dispatched and left the terminal grounds. |

---

### 5. Checking Seat Availability & Departure Countdown
- **Passenger Seat Progress Bar**:
  - Example: `11 / 14 Seats (3 remaining)`
  - When the bar turns green, plenty of seats remain.
  - When it turns red with `FULL`, boardings for that vehicle have closed.
- **Estimated Departure Time (ETD)**:
  - Calculated based on official terminal departure intervals and current boarding progress.
  - *Note*: A vehicle may depart earlier than its estimated time if all seats are filled before the clock runs out!

---

### 6. Looking Up Route Fares & Statutory Discounts
**URL**: `/fares`

Under LTFRB and LGU regulations, official fares are strictly regulated by route distance and vehicle classification.

#### 6.1 Checking Your Fare:
1. Click **Fares** in the top navigation bar or quick links.
2. Select your destination route (e.g., *Palompon to Ormoc City*).
3. The page displays the official breakdown:
   - **Origin & Destination**: Palompon $\rightarrow$ Ormoc.
   - **Total Distance**: Official highway distance in kilometers.
   - **Vehicle Type**: PUJ, Van, or Minibus.
   - **Regular Fare (₱)**: Standard adult passenger fare.
4. **Statutory 20% Discount Calculator**:
   - Under Philippine law, eligible passengers receive a **20% discount**:
     - **Students**: Valid for currently enrolled elementary, high school, and college students (present valid school ID).
     - **Senior Citizens**: Filipino citizens 60 years old and above (present OSCA Senior Citizen ID).
     - **Persons with Disability (PWD)**: Registered PWDs (present valid National PWD ID).
   - Use the interactive discount calculator on the Fares page to instantly verify your discounted rate before paying the conductor.

---

### 7. Daily Departure Schedules
**URL**: `/schedules`

To plan future trips during the day:
1. Click **Schedules** in the navigation bar.
2. View regular operating hours, typical departure frequencies, and first trip / last trip information for each certified route.
3. Status indicators show if a scheduled trip is currently on time, boarding, or departed.

---

### 8. Searching for Trips with Autocomplete
**URL**: `/search`

If you are in a rush or looking for a specific vehicle:
1. Use the **Search bar** on the homepage or visit `/search`.
2. Type any of the following:
   - **Destination**: e.g., `Ormoc`, `Tacloban`, `Isabel`.
   - **Plate Number**: e.g., `ABC-1234` or partial plate numbers like `123`.
   - **Vehicle Type**: e.g., `Van` or `Jeepney`.
3. The live autocomplete dropdown suggests matches immediately as you type. Click any result to jump straight to that vehicle's live status card.

---

### 9. Public Advisories, Weather Alerts & Announcements
- **Rolling Marquee**: Look at the red advisory banner across the very top of the screen for live terminal alerts.
- **Announcement Modal**: Click the **Bullhorn Icon** on the left side of the marquee to open the complete list of active terminal bulletins, weather advisories (e.g., typhoon warnings), or temporary route detours.

---

### 10. Frequently Asked Questions (FAQ)

**Q1: Can I buy tickets or book seats online through this website?**  
*No.* The Palompon Terminal Monitor is a real-time public information service. Tickets and cash fares are handled directly in person at the terminal bays or with the vehicle conductor.

**Q2: What are the terminal operating hours?**  
The Palompon Terminal typically operates daily from **4:00 AM to 8:00 PM**. Individual vehicle departures depend on passenger demand and scheduled route intervals.

**Q3: Does the website refresh automatically?**  
*Yes!* The terminal monitor uses real-time synchronization. Whenever a dispatcher checks in a vehicle, updates passenger counts, or logs a departure, your screen updates instantly without refreshing.

**Q4: Is there an official Android or iOS app?**  
The website is a progressive web-compatible responsive site. You do not need to download an app from an app store. Simply open your phone's browser, bookmark the URL, or choose "Add to Home Screen" for one-tap access.

---

### 11. Getting Help, Submitting Feedback & Reporting Issues
We welcome commuter feedback to continuously improve terminal services!
- **Help Center**: Click **Help Center** in the footer for quick operational guidelines.
- **Report an Issue**: Click **Report Issue** in the footer to report inaccurate schedules, missing vehicles, or fare overcharging directly to terminal supervisors.
- **Contact Us**: Click **Contact Us** in the footer or email the terminal administration at `arclast988@gmail.com`.
- **Physical Assistance**: Visit the **Palompon Terminal Office** located at the terminal grounds, Rizal Street, Palompon, Leyte.
