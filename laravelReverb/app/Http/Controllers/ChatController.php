<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class ChatController extends Controller
{
    // Show chat page
    public function index()
    {
        $messages = Message::all(); // fetch all messages
        return view('chat', compact('messages'));
    }

    // Send a new message
    public function send(Request $request)
    {
        dd($request->all());
        $request->validate([
            'sender' => 'required|string',
            'receiver' => 'required|string',
            'text' => 'required|string|max:500',
        ]);

        $message = Message::create([
            'sender' => $request->sender,
            'receiver' => $request->receiver,
            'text' => $request->text,
        ]);

        return redirect('/chat'); // refresh chat page
    }
}
