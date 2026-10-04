<?php

namespace Tests\Unit;

use App\Libraries\RateLimitLock;
use CodeIgniter\Test\CIUnitTestCase;

final class RateLimitLockTest extends CIUnitTestCase
{
    public function testConcurrentWorkersDoNotLoseRateLimitUpdates(): void
    {
        $file = tempnam(WRITEPATH . 'cache', 'limiter-test-');
        $worker = tempnam(WRITEPATH . 'cache', 'limiter-worker-');
        file_put_contents($file, '0');
        file_put_contents($worker, '<?php define("WRITEPATH", $argv[1]); require $argv[2]; for ($i=0;$i<20;$i++) { \App\Libraries\RateLimitLock::run(["regression-counter"], function () use ($argv) { $count=(int)file_get_contents($argv[3]); usleep(1000); file_put_contents($argv[3], (string)($count+1)); }); }');
        $processes = [];
        try {
            for ($i=0;$i<4;$i++) {
                $pipes = [];
                $process = proc_open([PHP_BINARY,$worker,WRITEPATH,APPPATH.'Libraries/RateLimitLock.php',$file], [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
                $this->assertIsResource($process);
                fclose($pipes[0]);
                $processes[] = [$process,$pipes];
            }
            foreach ($processes as [$process,$pipes]) {
                $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                fclose($pipes[1]); fclose($pipes[2]);
                $this->assertSame(0, proc_close($process), $output);
            }
            $this->assertSame('80', file_get_contents($file));
        } finally {
            unlink($file); unlink($worker);
        }
        // Exceptions must also release locks for a later request.
        try { RateLimitLock::run(['regression-counter'], static fn () => throw new \RuntimeException('expected')); } catch (\RuntimeException) {}
        $this->assertSame('released', RateLimitLock::run(['regression-counter'], static fn () => 'released'));
    }
}
