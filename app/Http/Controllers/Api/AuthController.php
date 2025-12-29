<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use App\Mail\ResetPasswordCodeMail;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangeUserPasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['role']) && $data['role'] === 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('auth.admin_cannot_register'),
                'data' => null,
            ], 403);
        }

        $user = $this->createUser($data);

        $this->notifyAdmin(
            "طلب موافقة مستخدم جديد",
            "المستخدم {$user->name} يحتاج إلى موافقتك",
            ["type" => "user_approval", "user_id" => $user->id]
        );



        return response()->json([
            'success' => true,
            'message' => __('auth.account_pending_approval'),
            'data' => null,
        ], 201);
    }

    private function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'admin_position_id' => $data['admin_position_id'] ?? null ,
            'id_number' => $data['id_number'],
            'phone' => $data['phone'],
            'backup_phone' => $data['backup_phone'] ?? null,
            'role' => $data['role'],
            'admin_position' => $data['admin_position'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'accept_terms' => $data['accept_terms'] ?? false,
            'is_approved' => false,
            'status' => 'pending', 
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::withTrashed()->where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_credentials'),
                'data' => null,
            ], 401);
        }

        if ($user->trashed()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.account_removed'),
                'data' => null,
            ], 403);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_credentials'),
                'data' => null,
            ], 401);
        }

        $user = Auth::user();

        if ($user->role === 'admin') {
            return response()->json([
                'success' => true,
                'message' => __('auth.user_login_successful'),
                'data' => $this->getUserDataWithToken($user),
            ]);
        }

        switch ($user->status) {
            case 'approved':
                return response()->json([
                    'success' => true,
                    'message' => __('auth.user_login_successful'),
                    'data' => $this->getUserDataWithToken($user),
                ]);
            case 'rejected':
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => __('auth.account_rejected'),
                    'data' => null,
                ], 403);
            case 'pending':
            default:
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => __('auth.account_pending_approval'),
                    'data' => null,
                ], 403);
        }
    }

    private function getUserDataWithToken(User $user): array
    {
        $accessToken = $user->createToken('API Access Token', ['*'], now()->addHours(2))->plainTextToken;
        $refreshToken = RefreshToken::createToken($user);
        $userData = (new UserResource($user))->resolve();

        return [
            'user' => $userData,
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'tokenType' => 'Bearer',
            'accessExpiresIn' => 7200,
            'refreshExpiresIn' => 2592000,
        ];
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $hashedToken = hash('sha256', $request->refresh_token);

        $refreshToken = RefreshToken::where('token', $hashedToken)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => __('auth.refresh_token_invalid'),
                'data' => null,
            ], 401);
        }

        $user = $refreshToken->user;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_not_found'),
                'data' => null,
            ], 404);
        }

        $refreshToken->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.token_refreshed_successfully'),
            'data' => $this->getUserDataWithToken($user),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_not_found'),
                'data' => null,
            ], 404);
        }

        $resetCode = mt_rand(100000, 999999);

        $user->reset_code = $resetCode;
        $user->reset_code_expires_at = now()->addMinutes(15);
        $user->save();

        Mail::to($user->email)->send(new ResetPasswordCodeMail($resetCode));

        return response()->json([
            'success' => true,
            'message' => __('auth.reset_code_sent'),
           // 'data' => ['resetCode' => $resetCode],
        ]);
    }

    public function verifyResetCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'reset_code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->reset_code !== $request->reset_code || $user->reset_code_expires_at < now()) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_reset_code'),
                'data' => null,
            ], 422);
        }

        $resetToken = Str::random(60);
        Cache::put("reset_token:{$resetToken}", $user->email, 900);

        $user->reset_code = null;
        $user->reset_code_expires_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('auth.code_verified_successfully'),
            'data' => ['resetToken' => $resetToken],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'reset_token' => 'required|string|size:60',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $email = Cache::get("reset_token:{$request->reset_token}");

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => __('auth.invalid_or_expired_token'),
                'data' => null,
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_not_found'),
                'data' => null,
            ], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        Cache::forget("reset_token:{$request->resetToken}");

        return response()->json([
            'success' => true,
            'message' => __('auth.password_reset_success'),
            'data' => null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.logout_success'),
            'data' => null,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.account_deleted_successfully'),
            'data' => null,
        ]);
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.fcm_token_saved'),
        ]);
    }

    public function changeUserPassword(ChangeUserPasswordRequest $request, $userId ): JsonResponse
    {

       $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('auth.user_not_found'),
                'data' => null,
            ], 404);
        }

        if (!in_array($user->role, ['delegate', 'contributor'])) {
            return response()->json([
                'success' => false,
                'message' => __('auth.not_allowed_to_change_password'),
                'data' => null,
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => __('auth.password_changed_successfully'),
            'data' => null,
        ]);
    }

}    
