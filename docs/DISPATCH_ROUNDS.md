# Dispatcher days, rounds, and boarding windows

Each departure rule has checked weekdays and an explicit round assignment from 1 to 999. Check Monday, Tuesday, and Wednesday to display Monday–Wednesday; check Monday, Thursday, and Sunday to display those separate days. Every day selects all seven weekdays. Choose Round 1 for one rule and Round 2 for another; the next round is offered as more rounds are configured. Dispatchers can switch an explicitly configured round at any time on its selected days. If no time window matches, its configured interval still applies; where several rules share a round, the matching time window is preferred, then the most recently edited applicable rule. Specific-day rules take precedence over every-day defaults for the same round and destination. Destination rules take precedence over terminal defaults.

For example, configure Monday Round 1 for 20 minutes and Monday Round 2 for 25 minutes. The dispatcher can select Round 2 whenever operations need it; a round is a route-wide rule period, not a count of a vehicle's trips. All vehicle types serving the same destination at the same terminal share the round. Each day's active round starts at Round 1.

Queue Operations keeps the route filter pills and compact outlined **Select trips to cancel** and **Round** buttons together in one card. **Round** opens a scrollable dialog with a route filter and each destination's active round and rule interval. Round changes apply immediately and show feedback in the dialog. The Add to Queue dialog has its own route filter, and Manage Queue offers a destination selector. Queue entries show their round. Changing rounds updates waiting and boarding vehicles. A boarding timer uses the new interval from its original start, preserving time already elapsed: switching from 20 to 40 minutes after 30 minutes of boarding changes “Overdue 10 min” to 10 minutes remaining.

The round picker shows intervals only from matching configured departure rules. A round with no rule configured for the current day and route is labeled **No active rule** and cannot be selected until its rule is configured. The system's legacy 30-minute fallback is never presented as a configured rule.

Guest departure rules show **Active now** only for the winning rule of the dispatcher-selected round at each destination, respecting selected weekdays, route scope and terminal defaults. Each rule shows its round and days; active rules list the destinations using them. The badge updates when dispatchers switch rounds without requiring the guest to reload.

Automatic boarding starts at a five-minute clock boundary: :00, :05, :10, :15, and so on. If a vehicle departs at 06:43, the next vehicle's boarding starts at 06:45. With a 20-minute rule it departs at 07:05. A late boarding vehicle continues to occupy the head of the route until the dispatcher records its departure. Boarding never overlaps another boarding vehicle in that route.

Dispatchers can use **Start boarding now** for the next vehicle at any minute during busy periods. This starts its timer immediately using the active round's departure interval. Repeated clicks do not restart a timer, and a vehicle cannot skip another vehicle already ahead. Automatic boarding remains on the five-minute grid.

Dispatcher clocks, queues, schedules, announcements, history, and departure rule forms display AM/PM. Rule forms keep the clock button and **Set time** picker with hour, minute and AM/PM choices; display fields cannot accept free text. Dispatcher rule forms use an interval in minutes; administrators retain 24-hour clock and duration inputs. Stored timestamps continue to use the normal database format.

**Select trips to cancel** opens a route-filtered checklist with vehicle type, plate, operator, driver, and destination. Only checked trips are canceled. The whole selection is validated under the queue ordering lock; stale or unauthorized selections cannot partially cancel trips. Retried cancellations succeed without affecting other vehicles. Queue feedback appears in the page or current dialog with a dismiss control, preserves server explanations, and always releases pending buttons on failure.

Route dropdowns in Manage Queue, Select trips to cancel, Round, and Add to Queue support typing to search. Clearing a filter restores all routes (or the first queue in Manage Queue). **Refresh selection** reads only the latest active trips in the dispatcher's assigned routes, keeps checked trips still present, and shows loading or retry feedback directly in the dialog.

New vehicle registrations appear in an open Add to Queue dialog without losing existing selections or search/filter values. Dispatchers can create, edit, and delete rules for their assigned destinations and terminal-wide defaults at the terminals where they dispatch. The same rule forms and actions are available to dispatchers, administrators, and superadministrators.

Departure History and its printed reports round departure timestamps up to five-minute clock marks. For example, 07:22 is recorded in history as 07:25; 07:25:00 stays 07:25. The displayed date and date filters follow the rounded time when a departure crosses midnight. The original timestamp remains available internally for boarding schedules, cooldowns, and retention; existing history receives the same display without changing stored records.

## Deployment

Run `php spark migrate` before serving the updated application, and restart the existing `ws:serve` process. The migration adds day/round fields, boarding windows, and persisted route round selections, then recalculates waiting vehicles on the five-minute grid. Departed history and ongoing boarding timers are preserved. The WebSocket server advances boarding at clock boundaries; dispatcher pages also provide a fallback while open. Updated scripts and styles use content-based asset versions.

The weekday migration adds `days_of_week` for grouped selections, preserves existing single-day rules, and assigns previously unassigned rules to Round 1. New rules require an explicit round. Restarted services use the updated selection logic; current boarding times remain preserved.
# Daily vehicle order

Vehicle registration and editing include **Daily starting order**. Positions are shared by vehicle types heading to the same destination from the same terminal. Choosing an occupied position shifts the other vehicles; a blank position appends the vehicle. Add to Queue displays the current route order.

Each departure moves that vehicle behind the other vehicles in its destination for the current day. The saved starting order stays unchanged. At **12:00 AM Asia/Manila**, the saved order returns, the departed badges and cooldown from yesterday clear, and unfinished waiting or boarding trips are automatically canceled. These trips stay in history and must be added as a new trip rather than restored into the next day.

The WebSocket process performs the rollover at its midnight boundary, even with no browser open. Queue/API requests also reconcile the day if the process was offline, and open dispatcher pages refresh through the boundary tick or polling fallback. Deployments must run the new `AddVehicleDispatchOrder` migration (the standard startup script runs migrations).

