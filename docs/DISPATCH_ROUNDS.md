# Dispatcher days, rounds, and boarding windows

Departure Rules now supports Monday–Sunday and any round from 1 to 999. Leave the day or round blank to provide a general default. For a round that can start at any time, set its time range to 00:00–23:59. A rule for a specific day and round takes precedence over general rules for that destination. Destination rules take precedence over terminal defaults.

For example, configure Monday Round 1 for 20 minutes and Monday Round 2 for 25 minutes. The dispatcher can select Round 2 whenever operations need it; a round is a route-wide rule period, not a count of a vehicle's trips. All vehicle types serving the same destination at the same terminal share the round. Each day's active round starts at Round 1.

Queue Operations contains route filters and the active round selector. The Add to Queue dialog has its own route filter, and Manage Queue offers a destination selector. Queue entries show their round. Changing rounds updates waiting vehicles and preserves the departure time of a vehicle already boarding.

Automatic boarding starts at a five-minute clock boundary: :00, :05, :10, :15, and so on. If a vehicle departs at 06:43, the next vehicle's boarding starts at 06:45. With a 20-minute rule it departs at 07:05. A late boarding vehicle continues to occupy the head of the route until the dispatcher records its departure. Boarding never overlaps another boarding vehicle in that route.

New vehicle registrations appear in an open Add to Queue dialog without losing existing selections or search/filter values. Dispatchers can create, edit, and delete rules for their assigned destinations; terminal defaults remain controlled by administrators.

## Deployment

Run `php spark migrate` before serving the updated application, and restart the existing `ws:serve` process. The migration adds day/round fields, boarding windows, and persisted route round selections, then recalculates waiting vehicles on the five-minute grid. Departed history and ongoing boarding timers are preserved. The WebSocket server advances boarding at clock boundaries; dispatcher pages also provide a fallback while open. Updated scripts and styles use content-based asset versions.
