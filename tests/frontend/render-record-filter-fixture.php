<?php
// Reuse the production-view harness and supply deterministic filter responses.
ob_start();
require __DIR__ . '/render-responsive-fixture.php';
ob_end_clean();
$query = json_decode($argv[9] ?? '{}', true) ?: [];
$todayPage = $fixturePage === 'staff-departures';
$rows = [];
foreach ([[1,'ORMOC-1','ORMOC','jeepney'],[2,'BATO-2','BATO','van'],[3,'ORMOC-3','ORMOC','van']] as [$id,$plate,$dest,$type]) {
    $rows[] = array_replace($item, ['id'=>$id,'queue_id'=>$id,'plate_number'=>$plate,'origin'=>'VILLABA','destination'=>$dest,
        'vehicle_type'=>$type,'status'=>$todayPage?'departed':'waiting','current_passengers'=>$id,'capacity'=>12,
        'position'=>$id,'estimated_departure'=>date('Y-m-d').' 12:00:00','departure_time'=>date('Y-m-d').' 12:00:00']);
}
$type = $query[$todayPage ? 'vehicle_type' : 'type'] ?? '';
$destination = $query['destination'] ?? '';
$search = $query['q'] ?? '';
$rows = array_values(array_filter($rows, static fn($row) => (!$type || $row['vehicle_type']===$type)
    && (!$destination || strcasecmp($destination,$row['destination'])===0)
    && (!$search || stripos($row['plate_number'],$search)!==false)));
$fixtureData['schedules'] = $rows;
$fixtureData['departures'] = ($query['page'] ?? '') === '2' ? array_slice($rows,-1) : $rows;
$fixtureData['vehicle_type'] = $type;
$fixtureData['destination'] = $destination;
$fixtureData['search'] = $search;
$fixtureData['filters'] = ['q'=>$search,'vehicle_type'=>$type,'destination'=>$destination,'sort_order'=>record_sort_order($query['sort_order'] ?? null)];
$fixtureData['destinations'] = [['destination'=>'ORMOC'],['destination'=>'BATO']];
$fixtureData['all_destinations'] = ['ORMOC','BATO'];
$fixtureData['active_dest_counts'] = ['ORMOC'=>2,'BATO'=>1];
$fixtureData['total_active_count'] = 3;
$fixtureData['vehicleTypes'] = [
    ['slug'=>'jeepney','name'=>'Jeepney','icon'=>'fa-bus','color'=>'#1565c0'],
    ['slug'=>'van','name'=>'Van','icon'=>'fa-van-shuttle','color'=>'#b71c1c'],
];
if ($todayPage) {
    $fixtureData['pager'] = new class($query, count($rows)) {
        public function __construct(private array $query, private int $count) {}
        public function getPageCount(): int { return $this->count>1 ? 2 : 1; }
        public function links(): string {
            return '<nav class="modern-pagination-nav"><ul class="pagination"><li><a href="/staff/departures?'.htmlspecialchars(http_build_query(array_replace($this->query,['page'=>'2'])), ENT_QUOTES).'">2</a></li></ul></nav>';
        }
    };
}
echo \App\Libraries\InitialStyles::prepare($fixtureRenderer->render($views[$fixturePage]));
