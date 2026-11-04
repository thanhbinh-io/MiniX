<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /**
     * Display the messages inbox and active conversation thread.
     */
    public function index(Request $request): Response
    {
        $currentUser = $request->user();

        // 1. Get list of distinct user IDs that current user has exchanged messages with
        $contactIds = Message::where('sender_id', $currentUser->id)
            ->orWhere('receiver_id', $currentUser->id)
            ->latest()
            ->get()
            ->map(function ($msg) use ($currentUser) {
                return (int) ($msg->sender_id === $currentUser->id ? $msg->receiver_id : $msg->sender_id);
            })
            ->unique()
            ->values();

        // Fetch user contacts with last message and unread count
        $contacts = User::whereIn('id', $contactIds)
            ->get()
            ->map(function ($contact) use ($currentUser) {
                $lastMessage = Message::where(function ($q) use ($currentUser, $contact) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $contact->id);
                })->orWhere(function ($q) use ($currentUser, $contact) {
                    $q->where('sender_id', $contact->id)->where('receiver_id', $currentUser->id);
                })->latest()->first();

                $unreadCount = Message::where('sender_id', $contact->id)
                    ->where('receiver_id', $currentUser->id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'id'               => $contact->id,
                    'name'             => $contact->name,
                    'username'         => $contact->username,
                    'avatar_url'       => $contact->avatar_url,
                    'status'           => $contact->status,
                    'last_message'     => $lastMessage?->message,
                    'last_message_at'  => $lastMessage?->created_at_human,
                    'last_message_raw' => $lastMessage?->created_at,
                    'unread_count'     => $unreadCount,
                ];
            })
            ->sortByDesc('last_message_raw')
            ->values();

        // 2. Active contact selection
        $activeContact = null;
        $activeMessages = [];

        $targetUsername = $request->query('user');
        $targetId = $request->query('contact_id');

        if ($targetUsername) {
            $activeContact = User::where('username', $targetUsername)->first();
        } elseif ($targetId) {
            $activeContact = User::find($targetId);
        } elseif ($contacts->isNotEmpty()) {
            $activeContact = User::find($contacts->first()['id']);
        }

        if ($activeContact && $activeContact->id !== $currentUser->id) {
            // Mark incoming messages as read
            Message::where('sender_id', $activeContact->id)
                ->where('receiver_id', $currentUser->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            // Fetch conversation messages (up to 100 latest)
            $activeMessages = Message::where(function ($q) use ($currentUser, $activeContact) {
                $q->where('sender_id', $currentUser->id)->where('receiver_id', $activeContact->id);
            })->orWhere(function ($q) use ($currentUser, $activeContact) {
                $q->where('sender_id', $activeContact->id)->where('receiver_id', $currentUser->id);
            })
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($m) => [
                'id'               => $m->id,
                'sender_id'        => $m->sender_id,
                'receiver_id'      => $m->receiver_id,
                'message'          => $m->message,
                'is_read'          => $m->is_read,
                'created_at_human' => $m->created_at_human,
                'is_mine'          => (int) $m->sender_id === (int) $currentUser->id,
            ]);
        }

        return Inertia::render('Messages/Index', [
            'contacts'       => $contacts,
            'activeContact'  => $activeContact ? [
                'id'         => $activeContact->id,
                'name'       => $activeContact->name,
                'username'   => $activeContact->username,
                'avatar_url' => $activeContact->avatar_url,
                'status'     => $activeContact->status,
            ] : null,
            'messages'       => $activeMessages,
        ]);
    }

    /**
     * Send a direct message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string|min:1|max:2000',
        ]);

        $sender = $request->user();
        $receiver = User::findOrFail($request->receiver_id);

        if ($sender->id === $receiver->id) {
            return back()->withErrors(['message' => 'Bạn không thể gửi tin nhắn cho chính mình.']);
        }

        if ($receiver->status === 'suspended') {
            return back()->withErrors(['message' => 'Tài khoản người nhận hiện đang bị đình chỉ.']);
        }

        if ($sender->hasBlocked($receiver) || $receiver->hasBlocked($sender)) {
            return back()->withErrors(['message' => 'Không thể gửi tin nhắn do quan hệ chặn tài khoản.']);
        }

        // Check recipient DM privacy settings
        if ($receiver->getSetting('allow_dms_from') === 'following' && !$receiver->isFollowing($sender)) {
            return back()->withErrors(['message' => 'Người dùng này chỉ cho phép nhận tin nhắn từ những người họ đang theo dõi.']);
        }

        $message = Message::create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'message'     => $request->message,
            'is_read'     => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ]);
        }

        return back();
    }

    /**
     * Get candidate recipients for sharing via direct message.
     */
    public function recipients(Request $request)
    {
        $currentUser = $request->user();
        $query = trim((string) $request->query('q', ''));

        // Exclude blocked users, users who blocked me, and myself
        $blockedIds = $currentUser->blockedUsers()->pluck('users.id')
            ->merge($currentUser->blockedByUsers()->pluck('users.id'))
            ->push($currentUser->id)
            ->unique();

        if ($query !== '') {
            $recipients = User::whereNotIn('id', $blockedIds)
                ->where('status', '!=', 'suspended')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get();
        } else {
            // Prioritize: Following users first, then followers and recent chat contacts
            $followingIds = $currentUser->following()->pluck('users.id');
            $followerIds = $currentUser->followers()->pluck('users.id');

            $contactIds = Message::where('sender_id', $currentUser->id)
                ->orWhere('receiver_id', $currentUser->id)
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn ($msg) => (int) ($msg->sender_id === $currentUser->id ? $msg->receiver_id : $msg->sender_id))
                ->unique();

            $combinedIds = $followingIds
                ->merge($followerIds)
                ->merge($contactIds)
                ->diff($blockedIds)
                ->values();

            if ($combinedIds->isNotEmpty()) {
                $recipients = User::whereIn('id', $combinedIds)
                    ->where('status', '!=', 'suspended')
                    ->limit(20)
                    ->get();
            } else {
                // Fallback to active users if no follows/contacts yet
                $recipients = User::whereNotIn('id', $blockedIds)
                    ->where('status', '!=', 'suspended')
                    ->limit(10)
                    ->get();
            }
        }

        return response()->json([
            'recipients' => $recipients->map(fn ($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'avatar_url' => $user->avatar_url,
            ]),
        ]);
    }
}

