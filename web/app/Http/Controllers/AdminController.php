<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
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

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:buyer,seller,admin'],
        ]);

        $this->adminService->setRole($user, $validated['role']);

        return redirect()->route('admin.users')
            ->with('success', "Role {$user->name} diperbarui.");
    }

    public function destroyUser(Request $request, int $id): RedirectResponse
    {
        if ($id === $request->user()->id) {
            return redirect()->route('admin.users')
                ->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $this->adminService->deleteUser($user);

        return redirect()->route('admin.users')
            ->with('success', "User {$user->name} dihapus.");
    }
}
