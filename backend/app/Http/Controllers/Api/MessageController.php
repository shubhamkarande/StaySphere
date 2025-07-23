<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function getConversations(Request $request)
    {
        $conversations = Conversation::forUser($request->user()->id)
            ->with(['listing', 'guest', 'host', 'latestMessage'])
            ->recent()
            ->paginate(20);

        return response()->json([
            'data' => $conversations->items(),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ]
        ]);
    }

    public function getOrCreateConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:listings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = \App\Models\Listing::findOrFail($request->listing_id);
        $userId = $request->user()->id;

        // Find existing conversation
        $conversation = Conversation::where('listing_id', $listing->id)
            ->where(function ($query) use ($userId, $listing) {
                $query->where('guest_id', $userId)
                      ->orWhere('host_id', $userId);
            })
            ->first();

        // Create new conversation if doesn't exist
        if (!$conversation) {
            $conversation = Conversation::create([
                'listing_id' => $listing->id,
                'guest_id' => $userId !== $listing->host_id ? $userId : null,
                'host_id' => $listing->host_id,
                'last_message_at' => now(),
            ]);
        }

        return response()->json([
            'data' => $conversation->load(['listing', 'guest', 'host', 'latestMessage'])
        ]);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $conversation = Conversation::where('id', $conversationId)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $messages = Message::where('conversation_id', $conversationId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return response()->json([
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $conversation = Conversation::where('id', $conversationId)
            ->forUser($request->user()->id)
            ->firstOrFail();

        $receiverId = $conversation->getOtherParticipant($request->user()->id)->id;

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $request->user()->id,
            'receiver_id' => $receiverId,
            'message' => $request->message,
        ]);

        // Update conversation timestamp
        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender', 'receiver'])
        ], 201);
    }

    public function markAsRead(Request $request, $conversationId)
    {
        $conversation = Conversation::where('id', $conversationId)
            ->forUser($request->user()->id)
            ->firstOrFail();

        // Mark all messages in conversation as read for current user
        $conversation->markAllAsRead($request->user()->id);

        return response()->json([
            'message' => 'Messages marked as read'
        ]);
    }
}