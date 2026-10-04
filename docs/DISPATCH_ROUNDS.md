# Dispatcher days, rounds, and boarding windows

Each departure rule has checked weekdays and an explicit round assignment from 1 to 999. Check Monday, Tuesday, and Wednesday to display Monday–Wednesday; check Monday, Thursday, and Sunday to display those separate days. Every day selects all seven weekdays. Choose Round 1 for one rule and Round 2 for another; the next round is offered as more rounds are configured. For a round that can start at any time, set its time range to 00:00–23:59. Specific-day rules take precedence over every-day defaults for the same round and destination. Destination rules take precedence over terminal defaults.

For example, configure Monday Round 1 for 20 minutes and Monday Round 2 for 25 minutes. The dispatcher can select Round 2 whenever operations need it; a round is a route-wide rule period, not a count of a vehicle's trips. All vehicle types serving the same destination at the same terminal share the round. Each day's active round starts at Round 1.

Queue Operations contains route filter pills and a card for each destination showing its active round and current rule interval. The Add to Queue dialog has its own route filter, and Manage Queue offers a destination selector. Queue entries show their round. Changing rounds updates waiting vehicles and preserves the departure time of a vehicle already boarding.

Automatic boarding starts at a five-minute clock boundary: :00, :05, :10, :15, and so on. If a vehicle departs at 06:43, the next vehicle's boarding starts at 06:45. With a 20-minute rule it departs at 07:05. A late boarding vehicle continues to occupy the head of the route until the dispatcher records its departure. Boarding never overlaps another boarding vehicle in that route.

New vehicle registrations appear in an open Add to Queue dialog without losing existing selections or search/filter values. Dispatchers can create, edit, and delete rules for their assigned destinations and terminal-wide defaults at the terminals where they dispatch. The same rule forms and actions are available to dispatchers, administrators, and superadministrators.

Departure History and its printed reports round departure timestamps up to five-minute clock marks. For example, 07:22 is recorded in history as 07:25; 07:25:00 stays 07:25. The displayed date and date filters follow the rounded time when a departure crosses midnight. The original timestamp remains available internally for boarding schedules, cooldowns, and retention; existing history receives the same display without changing stored records.

## Deployment

Run `php spark migrate` before serving the updated application, and restart the existing `ws:serve` process. The migration adds day/round fields, boarding windows, and persisted route round selections, then recalculates waiting vehicles on the five-minute grid. Departed history and ongoing boarding timers are preserved. The WebSocket server advances boarding at clock boundaries; dispatcher pages also provide a fallback while open. Updated scripts and styles use content-based asset versions.

The weekday migration adds `days_of_week` for grouped selections, preserves existing single-day rules, and assigns previously unassigned rules to Round 1. New rules require an explicit round. Restarted services use the updated selection logic; current boarding times remain preserved.
