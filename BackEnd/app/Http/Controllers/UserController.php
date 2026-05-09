<?php

namespace App\Http\Controllers;

use App\Models\PointsHistory;
use App\Models\RecyclingSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'user'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'avatar_url'   => $user->avatar_url,
                'total_points' => $user->total_points,
                'role'         => $user->role,
                'is_verified'  => $user->is_verified,
                'created_at'   => $user->created_at,
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'name'     => 'sometimes|string|max:100',
            'password' => 'sometimes|string|min:6|confirmed',
            'preferred_language' => 'sometimes|in:en,id',
            'theme_preference'   => 'sometimes|in:dark,light',
        ]);

        $data = $request->only(['name', 'preferred_language', 'theme_preference']);
        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $user->update($data);
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function pointsHistory(Request $request): JsonResponse
    {
        $history = PointsHistory::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function sessions(Request $request): JsonResponse
    {
        $sessions = RecyclingSession::with('machine')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('started_at')
            ->paginate(10);

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }
}
