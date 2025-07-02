<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Chat;

class ChatController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param string $conversationUuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, string $conversationUuid)
    {
        $validated = $request->validate([
            'sender' => 'required|integer|exists:users,id',
            'receiver' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:255'
        ]);

        $checkConversation = Conversation::find($conversationUuid);
        if (!$checkConversation) return response()->json(['message' => 'Cannot be found your conversation, please try again.'], 404);

        $validated['conversation_id'] = $conversationUuid;

        Chat::create($validated);

        return response()->json(['message' => 'Message sent successfully'], 201);
    }
}
