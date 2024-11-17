<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct()
    {
        // Pastikan endpoint ini hanya bisa diakses oleh pengguna yang sudah terautentikasi
        $this->middleware('auth:api');
    }

    /**
     * Mengupdate profil pengguna, termasuk gambar profil dan nama.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        // Validasi input yang diterima
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // validasi gambar
        ]);

        // Mengambil data pengguna yang sedang login
        $user = Auth::user();

        // Update nama pengguna
        $user->name = $request->name;

        // Jika ada gambar profil yang diupload
        if ($request->hasFile('profile_image')) {
            // Menghapus gambar lama (jika ada)
            if ($user->profile_image) {
                Storage::delete(str_replace('storage', 'public', $user->profile_image));
            }

            // Simpan gambar di storage
            $path = $request->file('profile_image')->store('public/profile_images');
            
            // Simpan path gambar ke kolom profile_image
            $user->profile_image = Storage::url($path);
        }

        // Simpan perubahan ke database
        $user->save();

        // Kembalikan response sukses dengan data pengguna yang telah diperbarui
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }
}
