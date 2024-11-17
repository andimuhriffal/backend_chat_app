<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendsController extends Controller
{
    // Mendapatkan daftar pengguna yang terdaftar, selain pengguna yang sedang login
    public function index()
    {
        try {
            $users = User::where('id', '!=', Auth::id())
                ->select('id', 'name', 'email') // Berikan informasi yang diperlukan saja
                ->get();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            Log::error("Error fetching users: " . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch users.'], 500);
        }
    }

    // Mengirim pesan
    public function sendMessage(Request $request)
    {
        // Validasi input
        $request->validate([
            'receiver_id' => 'required|exists:users,id|different:' . Auth::id(), // Pastikan receiver_id valid dan berbeda dari pengirim
            'content' => 'required|string',
        ]);

        // Pastikan penerima adalah pengguna terdaftar
        $receiver = User::find($request->receiver_id);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found or not registered.'], 404);
        }

        // Simpan pesan ke database
        try {
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'content' => $request->content,
            ]);

            // Kirim pesan melalui event atau broadcasting (misal dengan Pusher)
            broadcast(new MessageSent($message)); // Event broadcasting

            return response()->json($message, 201);
        } catch (\Exception $e) {
            Log::error("Error sending message: " . $e->getMessage());
            return response()->json(['error' => 'Message could not be sent.'], 500);
        }
    }

    // Mendapatkan pesan
    public function getMessages(Request $request)
    {
        // Validasi input receiver_id
        $request->validate([
            'receiver_id' => 'required|exists:users,id', // Pastikan receiver_id valid dan ada di database
        ]);

        // Ambil receiver_id dari parameter query
        $receiverId = $request->query('receiver_id');

        try {
            // Ambil pesan antara pengguna login dan penerima tertentu
            $messages = Message::where(function ($query) use ($receiverId) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $receiverId)
                    ->orWhere('sender_id', $receiverId)
                    ->where('receiver_id', Auth::id());
            })
                ->orderBy('created_at', 'asc') // Urutkan berdasarkan waktu
                ->paginate(10); // Batasi hasil untuk pagination

            // Format ulang hasil pagination agar lebih mudah diolah di frontend
            return response()->json([
                'data' => $messages->items(), // Pesan pada halaman ini
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'total_items' => $messages->total(),
                    'per_page' => $messages->perPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching messages: " . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch messages',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Mendapatkan informasi teman (nama dan gambar profil)
    public function getFriendInfo($friendId)
    {
        try {
            $friend = User::find($friendId); // Cari teman berdasarkan ID
            if ($friend) {
                return response()->json([
                    'name' => $friend->name,
                    'profile_image' => $friend->profile_image ?? '', // Ambil gambar profil, jika ada
                ], 200);
            } else {
                return response()->json(['error' => 'Friend not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching friend info: " . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch friend information'], 500);
        }
    }
}
