<?php

namespace App\Http\Controllers;

use App\DTO\Admin\UpdateRoleDTO;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /** Key used to store the original admin while impersonating. */
    public const SESSION_IMPERSONATED_ADMIN = 'impersonated_admin';

    public function __construct(
        protected AdminService $adminService
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', $this->adminService->dashboard());
    }

    public function users(): Response
    {
        $users = $this->adminService->listUsers()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at?->format('d M Y'),
            ])
            ->values()
            ->toArray();

        return Inertia::render('Admin/Users', [
            'users' => $users,
        ]);
    }

    public function impersonate(Request $request, int $id): RedirectResponse
    {
        $user = $this->adminService->getUser($id);

        if ($user->isAdmin()) {
            return redirect()->route('admin.users')
                ->with('error', 'Tidak dapat login sebagai admin lain.');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Anda sudah login sebagai user ini.');
        }

        $request->session()->put(self::SESSION_IMPERSONATED_ADMIN, [
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'label' => 'Admin',
        ]);
        $request->session()->regenerate();

        Auth::login($user, true);

        return redirect()->intended($user->homePath() ?? '/');
    }

    public function stopImpersonation(Request $request): RedirectResponse
    {
        $original = $request->session()->pull(self::SESSION_IMPERSONATED_ADMIN);

        if ($original) {
            $owner = $this->adminService->findUser($original['id']);

            if ($owner) {
                Auth::login($owner, true);
            }
        }

        $request->session()->regenerate();

        if ($original && ($original['label'] ?? '') === 'Seller') {
            return redirect()->intended('/dashboard');
        }

        return redirect()->route('admin.users');
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = $this->adminService->getUser($id);

        $dto = UpdateRoleDTO::fromRequest($request);

        $this->adminService->setRole($user, $dto);

        return redirect()->route('admin.users')
            ->with('success', "Role {$user->name} diperbarui.");
    }

    public function destroyUser(Request $request, int $id): RedirectResponse
    {
        if ($id === $request->user()->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user = $this->adminService->getUser($id);
        $this->adminService->deleteUser($user);

        return redirect()->route('admin.users')
            ->with('success', "User {$user->name} dihapus.");
    }
}
