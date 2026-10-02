<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($query) =>
                $query->where(fn($q) => 
                    $q->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('email', 'like', '%' . $request->q . '%')
                )
            )
            ->when($request->filled('role'), fn ($query) =>
                $query->where('role', $request->role)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }


    public function create()
    {
        $allowedRoles = ['dosen', 'mahasiswa'];

        return view('users.create', compact('allowedRoles'));
    }


    public function store(StoreUserRequest $request)
    {  
        // Kita hanya mengambil data yang SUDAH divalidasi
        $validated = $request->validated();
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        // TODO: Akan direfaktor menjadi UserPolicy@view di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $authUser->id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        return view('users.show', compact('user'));
    }


    public function edit(User $user)
    {
        // TODO: Akan direfaktor menjadi UserPolicy@update di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $authUser->id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        return view('users.edit', compact('user'));
    }


    public function update(UpdateUserRequest $request, User $user)
    {
        // TODO: Akan direfaktor menjadi UserPolicy@update di minggu 7
        $authUser = auth()->user();
        abort_unless(
            $authUser->role === 'admin'
            || $authUser->id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        // Hanya mengambil input yang sah melewati aturan UpdateUserRequest
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // role diisi manual
        $user->role = $validated['role'];
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Data pengguna berhasil diperbarui.');

    }


    public function destroy(User $user)
    {
        // TODO: Akan direfaktor menjadi UserPolicy@delete di minggu 7
        abort_unless(
            auth()->user()->role === 'admin',
            403,
            'Hanya admin yang dapat menghapus pengguna.'
        );

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}
