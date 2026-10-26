<?php

namespace Tests\Unit;

use App\Libraries\QueueResetSchedule;
use App\Models\QueueModel;
use App\Models\TerminalModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class QueueResetScheduleTest extends CIUnitTestCase
{
    private function timestamp(string $local): int
    {
        return (new \DateTimeImmutable($local, new \DateTimeZone('Asia/Manila')))->getTimestamp();
    }

    public function testDailyCutoffUsesPhilippineTimeAndNewSchedulesWaitForTheirNextOccurrence(): void
    {
        $now = $this->timestamp('2026-10-26 17:59:00');
        $this->assertSame('2026-10-25 18:00:00', QueueResetSchedule::cutoff($now, '18:00'));
        $this->assertSame('2026-10-26 18:00:00', QueueResetSchedule::cutoff($this->timestamp('2026-10-26 18:00:00'), '18:00'));
        $this->assertNull(QueueResetSchedule::cutoff($now, '18:00', false));
        $this->assertNull(QueueResetSchedule::cutoff($now, '12:00', true, $this->timestamp('2026-10-26 15:00:00')));
        $this->assertSame('2026-10-27 12:00:00', QueueResetSchedule::cutoff($this->timestamp('2026-10-27 12:00:00'), '12:00', true, $now));
    }

    public function testClearingOnlyRemovesUnfinishedTripsBeforeTheCutoffAndRunsOnce(): void
    {
        $db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, status TEXT, position INTEGER, arrival_time TEXT, estimated_departure TEXT, boarding_start TEXT)');
        $db->query('CREATE TABLE audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, details TEXT, timestamp TEXT)');
        foreach ([1=>'waiting', 2=>'boarding', 3=>'departed', 4=>'canceled', 5=>'waiting'] as $id=>$status) {
            $db->table('queue')->insert(['id'=>$id,'status'=>$status,'position'=>$id,'arrival_time'=>$id===5?'2026-10-26 18:00:00':'2026-10-26 09:00:00']);
        }
        $model = new QueueModel($db);
        $config = ['time'=>'18:00','enabled'=>true];
        $this->assertSame([], $model->resetForNewDay($this->timestamp('2026-10-26 17:59:00'), $config)['canceled_ids']);
        $this->assertSame([1,2], $model->resetForNewDay($this->timestamp('2026-10-26 18:00:00'), $config)['canceled_ids']);
        $this->assertSame([], $model->resetForNewDay($this->timestamp('2026-10-26 18:01:00'), $config)['canceled_ids']);
        $this->assertSame('departed', $db->table('queue')->where('id',3)->get()->getRowArray()['status']);
        $this->assertSame('waiting', $db->table('queue')->where('id',5)->get()->getRowArray()['status']);
        $this->assertSame(1, $db->table('audit_logs')->countAllResults());
        $db->close();
    }

    public function testTerminalWritesStoreUppercaseNames(): void
    {
        $db = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, location TEXT, capacity INTEGER, created_at TEXT)');
        $terminals = new TerminalModel($db);
        $id = $terminals->insert(['name'=>'  villaba terminal  ','location'=>'Leyte','capacity'=>10]);
        $this->assertSame('VILLABA TERMINAL', $terminals->find($id)['name']);
        $terminals->update($id, ['name'=>'San José']);
        $this->assertSame('SAN JOSÉ', $terminals->find($id)['name']);
        $this->assertSame('Leyte', $terminals->find($id)['location']);
        $db->close();
    }
}
