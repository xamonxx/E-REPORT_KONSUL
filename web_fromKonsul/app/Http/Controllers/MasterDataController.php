<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\NeedsCategory;
use App\Models\StatusCategory;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'categories');

        // Only query data for the active tab — avoid wasting queries on hidden tabs
        $emptyPaginator = fn(string $pageName = 'page') => new LengthAwarePaginator([], 0, 10, 1, ['pageName' => $pageName]);

        $categories = $tab === 'categories'
            ? NeedsCategory::orderBy('name')->paginate(10, ['*'], 'categories_page')
            : $emptyPaginator('categories_page');

        $statuses = $tab === 'statuses'
            ? StatusCategory::orderBy('sort_order')->paginate(10, ['*'], 'statuses_page')
            : $emptyPaginator('statuses_page');

        $users = $emptyPaginator('users_page');
        $accounts = collect();
        if ($tab === 'users') {
            $userQuery = User::with('account')->orderBy('name');
            if ($request->filled('search_user')) {
                $search = $request->search_user;
                $userQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('account', function($aq) use ($search) {
                          $aq->where('name', 'like', "%{$search}%");
                      });
                });
            }
            $users = $userQuery->paginate(10, ['*'], 'users_page')->appends([
                'tab' => $tab,
                'search_user' => $request->search_user
            ]);
            $accounts = Account::orderBy('name')->get();
        }

        return view('master-data.index', compact('tab', 'categories', 'statuses', 'users', 'accounts'));
    }

    // ── Needs Categories ─────────────────────────────────
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:needs_categories,name']);
        NeedsCategory::create(['name' => $request->name]);
        return back()->with('success', 'Kategori kebutuhan berhasil ditambahkan!');
    }

    public function updateCategory(Request $request, NeedsCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:needs_categories,name,' . $category->id]);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroyCategory(NeedsCategory $category)
    {
        if ($category->consultations()->withTrashed()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kategori yang masih digunakan (meskipun berada di trash).');
        }
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus!');
    }

    // ── Status Categories ────────────────────────────────
    public function storeStatus(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:status_categories,name',
            'color' => 'required|string|max:7',
        ]);
        $maxOrder = StatusCategory::max('sort_order') ?? 0;
        StatusCategory::create([
            'name' => $request->name,
            'color' => $request->color,
            'sort_order' => $maxOrder + 1,
        ]);
        return back()->with('success', 'Status berhasil ditambahkan!');
    }

    public function updateStatus(Request $request, StatusCategory $status)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:status_categories,name,' . $status->id,
            'color' => 'required|string|max:7',
        ]);
        $status->update(['name' => $request->name, 'color' => $request->color]);
        return back()->with('success', 'Status berhasil diperbarui!');
    }

    public function destroyStatus(StatusCategory $status)
    {
        if ($status->consultations()->withTrashed()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus status yang masih digunakan (meskipun berada di trash).');
        }
        $status->delete();
        return back()->with('success', 'Status berhasil dihapus!');
    }

    // ── User Management ──────────────────────────────────
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', new Enum(UserRole::class)],
            'account_id' => 'required_if:role,' . UserRole::Admin->value . '|nullable|exists:accounts,id',
        ], [
            'account_id.required_if' => 'Account / Cabang wajib dipilih untuk pengguna dengan role Admin.',
        ]);

        $role = UserRole::from($request->role);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'account_id' => $role === UserRole::SuperAdmin ? null : $request->account_id,
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan!');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === UserRole::SuperAdmin && User::where('role', UserRole::SuperAdmin)->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin terakhir pada sistem.');
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus!');
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', "Password untuk {$user->name} berhasil direset!");
    }
}
