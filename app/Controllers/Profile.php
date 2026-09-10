<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profile extends BaseController
{
    /**
     * Upload and update the authenticated user's profile avatar.
     */
    public function uploadAvatar()
    {
        $userId = session()->get('id');
        if (!$userId || !session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ]);
        }

        $avatarFile = $this->request->getFile('avatar');

        if (!$avatarFile || !$avatarFile->isValid()) {
            $err = $avatarFile ? $avatarFile->getErrorString() : 'No image file was uploaded.';
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => $err,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate file size (max 4MB)
        $maxSizeBytes = 4 * 1024 * 1024;
        if ($avatarFile->getSize() > $maxSizeBytes) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'The image exceeds the 4MB maximum allowed size.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate MIME type
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif'];
        $mime = $avatarFile->getMimeType();
        if (!in_array(strtolower($mime), $allowedMimes, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only JPG, PNG, WEBP, and GIF images are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate that the file is genuinely an image via getimagesize
        $imageInfo = @getimagesize($avatarFile->getTempName());
        if ($imageInfo === false) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Uploaded file is not a valid image.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        // Delete old custom avatar if it exists
        if (!empty($user['profile_image'])) {
            $oldPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $user['profile_image']);
            if (is_file($oldPath) && str_starts_with(realpath($oldPath) ?: '', realpath($uploadDir) ?: '')) {
                @unlink($oldPath);
            }
        }

        // Generate safe random filename
        $ext = $avatarFile->guessExtension() ?: 'jpg';
        $newFilename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!$avatarFile->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded image.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/avatars/' . $newFilename;

        $userModel->update($userId, ['profile_image' => $relPath]);
        session()->set('profile_image', $relPath);

        $this->logActivity('Profile', 'Updated profile picture');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Profile image updated successfully.',
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Remove custom profile avatar and revert to default.
     */
    public function removeAvatar()
    {
        $userId = session()->get('id');
        if (!$userId || !session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ]);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (!empty($user['profile_image'])) {
            $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
            $oldPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $user['profile_image']);
            if (is_file($oldPath) && str_starts_with(realpath($oldPath) ?: '', realpath($uploadDir) ?: '')) {
                @unlink($oldPath);
            }

            $userModel->update($userId, ['profile_image' => null]);
            session()->remove('profile_image');

            $this->logActivity('Profile', 'Removed custom profile picture');
        }

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Profile picture removed. Reverted to default avatar.',
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }
}
