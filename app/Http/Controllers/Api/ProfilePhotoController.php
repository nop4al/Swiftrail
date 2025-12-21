<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfilePhotoController extends ApiBaseController
{
    /**
     * Upload user profile photo
     * POST /api/v1/profile/photo
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            // Store new avatar
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $avatarUrl = 'storage/' . $path;

            // Update user avatar
            $user->update(['avatar' => $avatarUrl]);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo uploaded successfully',
                'data' => [
                    'avatar' => $user->avatar,
                    'avatar_url' => asset($user->avatar),
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user profile photo
     * DELETE /api/v1/profile/photo
     */
    public function deletePhoto(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage(),
            ], 500);
        }
    }
}
