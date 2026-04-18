<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::withCount('consultations')
            ->with('admins');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('account_id')) {
            $query->where('id', $request->account_id);
        }

        if ($request->filled('category')) {
            $query->where('description', $request->category);
        }

        $accounts = $query->orderBy('name')->paginate(15)->appends($request->query());

        $categories = Account::select('description')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->distinct()
            ->pluck('description');

        return view('accounts.index', compact('accounts', 'categories'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name',
            'description' => 'nullable|string|max:255',
            'target_leads' => 'nullable|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('accounts', 'public');
        }

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Akun interior baru berhasil ditambahkan!');
    }

    public function edit(Account $account)
    {
        $admins = User::where('role', UserRole::Admin)
            ->where(function($q) use ($account) {
                $q->whereNull('account_id')
                  ->orWhere('account_id', $account->id);
            })
            ->get();
        return view('accounts.edit', compact('account', 'admins'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:accounts,name,' . $account->id,
            'description' => 'nullable|string|max:255',
            'target_leads' => 'nullable|integer|min:1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($account->logo_path) {
                Storage::disk('public')->delete($account->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('accounts', 'public');
        }

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Data akun berhasil diperbarui!');
    }

    public function destroy(Account $account)
    {
        // Unassign admins
        $account->admins()->update(['account_id' => null]);

        // Delete all related consultations
        $account->consultations()->delete();

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Akun berhasil dihapus! Data lead terkait telah dihapus secara permanen dari sistem.');
    }
}
