<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DepartureHistoryTimeTest extends CIUnitTestCase
{
    public function testHistoryUsesTheNextFiveMinuteMarkIncludingClockAndDateRollover(): void
    {
        $cases = [
            '2026-10-04 07:01:00' => '2026-10-04 07:05:00',
            '2026-10-04 07:04:59' => '2026-10-04 07:05:00',
            '2026-10-04 07:05:00' => '2026-10-04 07:05:00',
            '2026-10-04 07:09:00' => '2026-10-04 07:10:00',
            '2026-10-04 07:22:00' => '2026-10-04 07:25:00',
            '2026-10-04 07:25:01' => '2026-10-04 07:30:00',
            '2026-10-04 07:58:00' => '2026-10-04 08:00:00',
            '2026-10-04 23:59:00' => '2026-10-05 00:00:00',
            '2026-10-31 23:59:59' => '2026-11-01 00:00:00',
            '2026-12-31 23:59:59' => '2027-01-01 00:00:00',
        ];
        foreach ($cases as $actual => $recorded) {
            $this->assertSame($recorded, history_departure_time($actual, 'Y-m-d H:i:s'), $actual);
        }
        $this->assertSame('07:25', history_departure_time('2026-10-04 07:22:00'));
        $this->assertSame('—', history_departure_time(null));
        $this->assertSame('—', history_departure_time(''));
        $this->assertSame('—', history_departure_time('not a date'));
        $this->assertNull(history_departure_date_boundary('2026-02-30'));
        $this->assertNull(history_departure_date_boundary('invalid'));
    }

    public function testDateFiltersFollowTheRecordedDayAndPreserveActualDepartureTimes(): void
    {
        $db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        try {
            $db->query('CREATE TABLE history_times (id INTEGER PRIMARY KEY, departure_time TEXT)');
            $times = [
                1 => '2026-10-03 23:55:00',
                2 => '2026-10-03 23:55:01',
                3 => '2026-10-04 00:00:00',
                4 => '2026-10-04 07:22:00',
                5 => '2026-10-04 23:55:00',
                6 => '2026-10-04 23:55:01',
            ];
            foreach ($times as $id => $time) $db->table('history_times')->insert(['id' => $id, 'departure_time' => $time]);
            $selected = $db->table('history_times')
                ->where('departure_time >', history_departure_date_boundary('2026-10-04'))
                ->where('departure_time <=', history_departure_date_boundary('2026-10-04', true))
                ->orderBy('id')->get()->getResultArray();
            $this->assertSame([2, 3, 4, 5], array_map('intval', array_column($selected, 'id')));
            foreach ($selected as $row) {
                $this->assertSame('2026-10-04', history_departure_time($row['departure_time'], 'Y-m-d'));
            }
            $this->assertSame('2026-10-04 07:22:00', $db->table('history_times')->where('id', 4)->get()->getRowArray()['departure_time']);
        } finally {
            $db->close();
        }
    }

    public function testHistoryPagesAndPrintReportShowTheSameRoundedTimeAndDate(): void
    {
        session()->set(['isLoggedIn' => true, 'id' => 123, 'role' => 'admin']);
        foreach (['admin/history/index', 'shared/history', 'admin/history/print_history'] as $template) {
            foreach (['2026-10-04 07:22:00' => ['07:25', 'Oct 04, 2026'], '2026-10-04 23:59:00' => ['00:00', 'Oct 05, 2026']] as $actual => [$time, $day]) {
                $row = ['id' => 1, 'plate_number' => 'HISTORY-ROUND', 'operator_name' => 'Operator', 'driver_name' => 'Driver',
                    'vehicle_type' => 'van', 'vehicle_photo' => null, 'origin' => 'Terminal', 'destination' => 'Ormoc',
                    'current_passengers' => 10, 'departure_time' => $actual];
                $html = view($template, ['title' => 'Departure History', 'departures' => [$row], 'results' => [$row],
                    'stats' => [], 'destinations' => [], 'destinationVehicleTypes' => [], 'vehicleTypes' => [], 'pager' => null,
                    'search' => '', 'destination' => '', 'vehicle_type' => '', 'from_date' => '', 'to_date' => ''], ['saveData' => false]);
                $previousErrors = libxml_use_internal_errors(true);
                try {
                    $document = new \DOMDocument();
                    $document->loadHTML($html);
                    $rows = (new \DOMXPath($document))->query('//tr[contains(., "HISTORY-ROUND")]');
                    $this->assertSame(1, $rows->length, $template);
                    $text = $rows->item(0)->textContent;
                    $this->assertStringContainsString($time, $text, $template);
                    $this->assertStringContainsString($day, $text, $template);
                    $this->assertStringNotContainsString(substr($actual, 11, 5), $text, $template);
                } finally {
                    libxml_clear_errors();
                    libxml_use_internal_errors($previousErrors);
                }
            }
        }
    }
}
