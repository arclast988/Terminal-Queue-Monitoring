<?php

namespace Tests\Unit;

use App\Libraries\DepartureRuleDefaults;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DepartureRuleDefaultsTest extends CIUnitTestCase
{
    public function testEmptyDatabaseGetsExactlyMorningAndAfternoonAndRerunsAreSafe(): void
    {
        $db = $this->database();
        $migration = new \App\Database\Migrations\SimplifyDefaultDepartureRules(Database::forge($db));
        $migration->up();
        $migration->up();
        $rules = $db->table('departure_rules')->orderBy('time_from')->get()->getResultArray();
        $this->assertCount(2, $rules);
        $this->assertSame(['Morning', 'Afternoon'], array_column($rules, 'label'));
        $this->assertSame([20, 25], array_map('intval', array_column($rules, 'wait_minutes')));
        $this->assertSame(['05:00:00', '12:00:00'], array_column($rules, 'time_from'));
        $this->assertSame(['12:00:00', '18:00:00'], array_column($rules, 'time_to'));
        $this->assertSame([null, null], array_column($rules, 'round_number'));
        $db->close();
    }

    public function testOnlyUntouchedLegacySeedSetIsReplacedAndCustomRulesSurvive(): void
    {
        $db = $this->database();
        $legacy = [
            ['00:00:00', '05:00:00', 60, 'Late Night / Early Morning'],
            ['05:00:00', '09:00:00', 30, 'Morning Rush'],
            ['09:00:00', '12:00:00', 40, 'Mid-Morning'],
            ['12:00:00', '15:00:00', 40, 'Afternoon'],
            ['15:00:00', '18:00:00', 30, 'Afternoon Rush'],
            ['18:00:00', '21:00:00', 40, 'Evening'],
            ['21:00:00', '23:59:59', 60, 'Late Night'],
        ];
        foreach ($legacy as [$from, $to, $wait, $label]) {
            $db->table('departure_rules')->insert(['terminal_id' => 1, 'time_from' => $from, 'time_to' => $to, 'wait_minutes' => $wait, 'label' => $label]);
        }
        $db->table('departure_rules')->insert(['terminal_id' => 1, 'route_id' => 9, 'time_from' => '06:00:00', 'time_to' => '18:00:00', 'wait_minutes' => 17, 'label' => 'Custom route']);
        (new \App\Database\Migrations\SimplifyDefaultDepartureRules(Database::forge($db)))->up();
        $this->assertSame(3, $db->table('departure_rules')->countAllResults());
        $this->assertSame(17, (int) $db->table('departure_rules')->where('route_id', 9)->get()->getRowArray()['wait_minutes']);
        $db->close();
    }

    public function testAnEditedScheduleIsNeverReplaced(): void
    {
        $db = $this->database();
        $db->table('departure_rules')->insert(DepartureRuleDefaults::rules()[0] + ['terminal_id' => 1, 'round_number' => 3, 'updated_at' => '2026-10-20 10:00:00']);
        (new \App\Database\Migrations\SimplifyDefaultDepartureRules(Database::forge($db)))->up();
        $this->assertSame(1, $db->table('departure_rules')->countAllResults());
        $this->assertSame(3, (int) $db->table('departure_rules')->get()->getRowArray()['round_number']);
        $db->close();
    }

    private function database()
    {
        require_once APPPATH . 'Database/Migrations/2026-10-26-000000_SimplifyDefaultDepartureRules.php';
        $db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $db->table('terminals')->insert(['id' => 1, 'name' => 'VILLABA']);
        $db->query('CREATE TABLE departure_rules (id INTEGER PRIMARY KEY AUTOINCREMENT, terminal_id INTEGER, route_id INTEGER, time_from TEXT, time_to TEXT, wait_minutes INTEGER, label TEXT, created_at TEXT, updated_at TEXT, day_of_week INTEGER, days_of_week TEXT, round_number INTEGER)');
        return $db;
    }
}
