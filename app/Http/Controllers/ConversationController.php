<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Conversation;
use App\Models\Thesis;

class ConversationController extends Controller
{
    /**
     * @param string $studyId
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function initializeConversation(string $studyId)
    {
        $checkStudyId = Thesis::find($studyId);
        if (!$checkStudyId) {
            return response()->json([
                'message' => 'Topic could not be found, please try again',
            ], 404);
        }

        $existingConversation = Conversation::where('topic', $studyId)->first();
        if ($existingConversation) {
            return response()->json([
                'message' => 'You already have a conversation on this topic.',
            ], 409);
        }

        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'topic' => $studyId,
        ]);

        return response()->json([
            'message' => 'Conversation started successfully.',
        ], 201);
    }
}
