<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Fungsi login
    public function login(Request $req)
    {
        // Validasi input
        $req->validate([
            'email' => 'required',
            'password' => 'required',
            'device_name' => 'nullable|string' // Pastikan device_name valid
        ]);

        // Mencari user berdasarkan email
        $user = User::where('email', $req->email)->first();

        // Periksa apakah user ditemukan dan password cocok
        if (!$user || ! Hash::check($req->password, $user->password)) {
            return response()->json([
                'message' => "failed"
            ], 401); // Menambahkan status code 401 untuk login gagal
        }

        // Gunakan device_name dari request atau nama default jika tidak ada
        $deviceName = $req->device_name ?: 'default_device'; // Pastikan menggunakan string

        // Membuat token untuk user
        $token = $user->createToken($deviceName)->plainTextToken;

        // Mengembalikan respons sukses dengan token
        return response()->json([
            'message' => 'success',
            'data' => [
                'token' => $token
            ]
        ], 200); // Menambahkan status code 200 untuk sukses
    }

    // Fungsi register
    public function register(Request $req)
    {
        // Validasi input
        $validator = Validator::make($req->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422); // Mengembalikan kode status 422 jika validasi gagal
        }

        // Membuat user baru
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password), // Enkripsi password
        ]);

        // Gunakan device_name dari request atau nama default jika tidak ada
        $deviceName = $req->device_name ?: 'default_device'; // Pastikan menggunakan string

        // Membuat token untuk user yang baru
        $token = $user->createToken($deviceName)->plainTextToken;

        // Mengembalikan respons sukses dengan token
        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'token' => $token
            ]
        ], 201); // Menambahkan status code 201 untuk sukses pendaftaran
    }

    // Fungsi logout
    // Fungsi logout
    public function logout(Request $req)
    {
        // Periksa apakah user terautentikasi
        $user = Auth::user();

        // Jika user tidak terautentikasi, kirimkan respons error
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401); // Status code 401 untuk unauthenticated
        }

        // Menghapus semua token yang terkait dengan pengguna
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json([
            'message' => 'Logout successful'
        ], 200); // Status code 200 untuk logout sukses
    }
}
