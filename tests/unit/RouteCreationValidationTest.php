<?php

namespace Tests\Unit;

use App\Controllers\Admin\Routes;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/** Invalid requests must be rejected before models or a database are needed. */
class RouteCreationValidationHarness extends Routes
{
    public function __construct() {}
}

final class RouteCreationValidationTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function tearDown(): void
    {
        session()->remove(['errors', '_ci_old_input']);
        parent::tearDown();
    }

    private function submit(array $post): array
    {
        session()->remove(['errors', '_ci_old_input']);
        service('validation')->reset();
        $this->withUri('http://localhost/admin/routes/store');
        $this->request->setMethod('POST')->setGlobal('post', $post);
        $this->request->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/admin/routes/create');
        $response = $this->controller(RouteCreationValidationHarness::class)->execute('store')->response();
        $this->assertSame(302, $response->getStatusCode());
        return session()->getFlashdata('errors');
    }

    public function testMissingVehicleTypesExplainsWhatToSelectInsteadOfRequestingAFare(): void
    {
        foreach ([[], ['form_action' => 'create_route']] as $intent) {
            $errors = $this->submit($intent + ['terminal_id' => '1', 'destination' => 'ISABEL']);
            $this->assertSame(['vehicle_types' => 'Select at least one vehicle type for this route.'], $errors);
            $this->assertArrayNotHasKey('fare', $errors);
        }
    }

    public function testEmptyFareFormUsesAnActionableFareMessage(): void
    {
        $errors = $this->submit(['form_action' => 'assign_fare', 'destination' => 'ISABEL', 'fare' => '']);
        $this->assertSame('Enter the regular fare amount in pesos.', $errors['fare']);
        $this->assertArrayNotHasKey('vehicle_types', $errors);
    }

    public function testZeroFareStaysInFareValidationEvenWithVehicleTypesSelected(): void
    {
        $errors = $this->submit(['destination' => 'ISABEL', 'fare' => '0', 'vehicle_types' => ['van']]);
        $this->assertSame('The regular fare must be greater than ₱0.00.', $errors['fare']);
    }
}
