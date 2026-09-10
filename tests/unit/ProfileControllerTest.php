<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use App\Controllers\Profile;

/**
 * @internal
 */
final class ProfileControllerTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    public function testUploadAvatarUnauthorizedWhenNotLoggedIn(): void
    {
        $session = service('session');
        $session->set(['isLoggedIn' => false, 'id' => null]);

        $result = $this->withUri('http://localhost/profile/upload-avatar')
            ->controller(Profile::class)
            ->execute('uploadAvatar');

        $this->assertEquals(401, $result->response()->getStatusCode());
        $json = json_decode($result->response()->getBody(), true);
        $this->assertFalse($json['success']);
    }

    public function testRemoveAvatarUnauthorizedWhenNotLoggedIn(): void
    {
        $session = service('session');
        $session->set(['isLoggedIn' => false, 'id' => null]);

        $result = $this->withUri('http://localhost/profile/remove-avatar')
            ->controller(Profile::class)
            ->execute('removeAvatar');

        $this->assertEquals(401, $result->response()->getStatusCode());
        $json = json_decode($result->response()->getBody(), true);
        $this->assertFalse($json['success']);
    }

    public function testUploadAvatarRejectsMissingFile(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'admin',
        ]);

        $result = $this->withUri('http://localhost/profile/upload-avatar')
            ->controller(Profile::class)
            ->execute('uploadAvatar');

        $this->assertEquals(400, $result->response()->getStatusCode());
        $json = json_decode($result->response()->getBody(), true);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('No image file was uploaded', $json['message']);
    }
}
