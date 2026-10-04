<?php

namespace Tests\Unit;

use App\Controllers\Home;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class GuestDepartureRulesTest extends CIUnitTestCase
{
    public function testGuestRulesAreOrderedByRoundBeforeStartTime(): void
    {
        $db = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $connections = $property->getValue();
        $property->setValue(null, array_replace($connections, ['default'=>$db, 'tests'=>$db]));
        try {
            $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
            $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT)');
            $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, route_id INTEGER, status TEXT)');
            $db->query('CREATE TABLE dispatch_rounds (id INTEGER PRIMARY KEY, service_date TEXT)');
            $db->query('CREATE TABLE departure_rules (id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER, round_number INTEGER, time_from TEXT, time_to TEXT, label TEXT, wait_minutes INTEGER, days_of_week TEXT, day_of_week INTEGER)');
            $db->table('terminals')->insert(['id'=>1,'name'=>'Villaba']);
            $db->table('routes')->insert(['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC']);
            foreach ([3,2,1] as $round) {
                $db->table('departure_rules')->insert(['id'=>$round,'terminal_id'=>1,'route_id'=>1,'round_number'=>$round,
                    'time_from'=>$round === 1 ? '05:00:00' : '00:00:00', 'time_to'=>'23:59:00', 'label'=>'Rule', 'wait_minutes'=>20]);
            }
            $rules = (new \ReflectionMethod(Home::class,'getDepartureRules'))->invoke(new Home());
            $this->assertSame([1,2,3],array_column($rules,'round_number'));
            $this->assertSame('05:00:00',$rules[0]['time_from']);
        } finally {
            $property->setValue(null,$connections);
            $db->close();
        }
    }
}
