<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

final class ImageUploadValidationTest extends CIUnitTestCase
{
    public function testImageExtensionComesFromContentInsteadOfClientFilename(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'vehicle-image-');
        try {
            file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aEJkAAAAASUVORK5CYII=', true));
            $upload = new UploadedFile($path, 'picture.php', 'image/png', filesize($path), UPLOAD_ERR_OK);
            $validator = new class extends BaseController {
                public function extension(UploadedFile $file): ?string
                {
                    return $this->validatedImageExtension($file);
                }
            };

            $this->assertSame('png', $validator->extension($upload));
        } finally {
            @unlink($path);
        }
    }

    public function testFilenameAndClaimedMimeCannotMakeTextAnImage(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'vehicle-image-');
        try {
            file_put_contents($path, '<?php echo "not an image";');
            $upload = new UploadedFile($path, 'picture.png', 'image/png', filesize($path), UPLOAD_ERR_OK);
            $validator = new class extends BaseController {
                public function extension(UploadedFile $file): ?string
                {
                    return $this->validatedImageExtension($file);
                }
            };

            $this->assertNull($validator->extension($upload));
        } finally {
            @unlink($path);
        }
    }
}
