<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CallRequest;
use App\Models\Message;
use App\Models\GroupCall;
use App\Models\GroupCallParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCallController extends Controller
{
    /**
     * Show dashboard with users list
     */
    public function dashboard()
    {
        $currentUserId = Auth::id();
        
        // Get all users except current user
        $users = User::where('id', '!=', $currentUserId)->get();
        
        // Get latest message time for each user (both sent and received)
        $latestMessages = Message::where(function($query) use ($currentUserId) {
                $query->where('sender_id', $currentUserId)
                      ->orWhere('receiver_id', $currentUserId);
            })
            ->selectRaw('
                CASE 
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END as other_user_id,
                MAX(created_at) as last_message_time
            ', [$currentUserId])
            ->groupBy('other_user_id')
            ->pluck('last_message_time', 'other_user_id');
        
        // Sort users by latest message time (most recent first)
        $users = $users->sortByDesc(function($user) use ($latestMessages) {
            return $latestMessages[$user->id] ?? null;
        })->values();
        
        // Get unread message counts for each user
        $unreadCounts = Message::where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id')
            ->toArray();
        
        return view('dashboard', compact('users', 'unreadCounts'));
    }

    /**
     * Show calls history page
     */
    public function calls()
    {
        $userId = Auth::id();
        $allCalls = collect();
        
        // Get individual call requests (one-to-one calls) - exclude group calls
        $individualCalls = CallRequest::where(function($query) use ($userId) {
            $query->where('caller_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })
        ->whereIn('status', ['accepted', 'ended'])
        ->where(function($query) {
            // Exclude group calls (room_id starting with 'group_')
            $query->where('room_id', 'not like', 'group_%');
        })
        ->with(['caller:id,name,profile_picture', 'receiver:id,name,profile_picture'])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($call) use ($userId) {
            // Determine call type from room_id
            $callType = strpos($call->room_id, 'audio_') === 0 ? 'audio' : 'video';
            
            // Get other user
            $otherUser = $call->caller_id == $userId 
                ? $call->receiver 
                : $call->caller;
            
            // Determine call direction
            $isOutgoing = $call->caller_id == $userId;
            
            return [
                'id' => $call->id,
                'room_id' => $call->room_id,
                'type' => $callType,
                'is_group' => false,
                'other_user' => $otherUser,
                'is_outgoing' => $isOutgoing,
                'status' => $call->status,
                'created_at' => $call->created_at,
                'answered_at' => $call->answered_at,
                'ended_at' => $call->ended_at,
            ];
        });
        
        $allCalls = $allCalls->merge($individualCalls);
        
        // Get group calls where user is a participant (including those who joined or were invited)
        // We include all participants regardless of status so all users see the group call in history
        $groupCalls = GroupCall::whereHas('participants', function($query) use ($userId) {
            // Include user if they were ever a participant (joined or left)
            $query->where('user_id', $userId);
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($groupCall) use ($userId) {
            // Get all participants except current user (include both joined and left for display)
            // But for display name, we'll show all participants who were ever in the call
            $participants = $groupCall->participants()
                ->where('user_id', '!=', $userId)
                ->with('user:id,name,profile_picture')
                ->get()
                ->map(function($p) {
                    return $p->user;
                })
                ->filter();
            
            // Get first 2 participants for display
            $firstTwoParticipants = $participants->take(2);
            $remainingCount = $participants->count() - 2;
            
            // Build display name
            $displayName = '';
            if ($firstTwoParticipants->count() > 0) {
                $names = $firstTwoParticipants->pluck('name')->toArray();
                $displayName = implode(' & ', $names);
                if ($remainingCount > 0) {
                    $displayName .= ' & ' . $remainingCount . ' other' . ($remainingCount > 1 ? 's' : '');
                }
            } else {
                $displayName = 'Group Call';
            }
            
            // Check if call is active (has active participants and not ended)
            $activeParticipants = $groupCall->activeParticipants()->count();
            // Call is active only if: status is 'active', has active participants, hasn't ended, and was recently updated
            // If call hasn't been updated in last 15 minutes, consider it inactive
            $lastUpdated = $groupCall->updated_at ?? $groupCall->created_at;
            $minutesSinceUpdate = $lastUpdated ? now()->diffInMinutes($lastUpdated) : 999;
            $isActive = $groupCall->status === 'active' 
                && is_null($groupCall->ended_at)
                && $activeParticipants > 0
                && $minutesSinceUpdate < 15; // Call must have been active in last 15 minutes
            
            return [
                'id' => 'group_' . $groupCall->id,
                'room_id' => $groupCall->room_id,
                'type' => $groupCall->type,
                'is_group' => true,
                'group_call_id' => $groupCall->id,
                'display_name' => $displayName,
                'participants' => $participants,
                'participant_count' => $participants->count() + 1, // +1 for current user
                'is_outgoing' => $groupCall->created_by == $userId,
                'status' => $groupCall->status,
                'is_active' => $isActive,
                'created_at' => $groupCall->created_at,
                'started_at' => $groupCall->started_at,
                'ended_at' => $groupCall->ended_at,
            ];
        });
        
        $allCalls = $allCalls->merge($groupCalls);
        
        // Sort all calls by created_at descending
        $calls = $allCalls->sortByDesc(function($call) {
            return $call['created_at'] ?? $call['started_at'] ?? now();
        })->values();
        
        // If AJAX request, return JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'calls' => $calls->map(function($call) {
                    $callData = [
                        'id' => $call['id'],
                        'room_id' => $call['room_id'],
                        'type' => $call['type'],
                        'is_group' => $call['is_group'] ?? false,
                        'is_outgoing' => $call['is_outgoing'],
                        'status' => $call['status'],
                        'created_at' => $call['created_at']?->format('Y-m-d H:i:s'),
                        'display_name' => $call['is_group'] ?? false 
                            ? ($call['display_name'] ?? 'Group Call')
                            : ($call['other_user']['name'] ?? 'Unknown'),
                    ];
                    
                    if ($call['is_group'] ?? false) {
                        $callData['participants'] = $call['participants']->map(function($p) {
                            return [
                                'id' => $p->id,
                                'name' => $p->name,
                                'profile_picture' => $p->profile_picture
                            ];
                        });
                        $callData['is_active'] = $call['is_active'] ?? false;
                    } else {
                        $callData['other_user'] = [
                            'id' => $call['other_user']->id ?? null,
                            'name' => $call['other_user']->name ?? 'Unknown',
                            'profile_picture' => $call['other_user']->profile_picture ?? null
                        ];
                    }
                    
                    return $callData;
                })
            ]);
        }
        
        return view('calls', compact('calls'));
    }

    /**
     * Show video call page
     */
    public function showCall(Request $request, $userId = null)
    {
        $roomId = $request->get('room', uniqid('room_', true));
        $otherUser = null;
        $callRequest = null;
        
        if ($userId) {
            $otherUser = User::findOrFail($userId);
        }
        
        // Get call request info if room_id exists
        if ($roomId && $roomId !== '' && $roomId !== 'room_' . uniqid('', true)) {
            $callRequest = CallRequest::where('room_id', $roomId)
                ->where(function($query) {
                    $query->where('caller_id', Auth::id())
                          ->orWhere('receiver_id', Auth::id());
                })
                ->with(['caller:id,name,profile_picture', 'receiver:id,name,profile_picture'])
                ->first();
            
            if ($callRequest && !$otherUser) {
                $otherUser = $callRequest->caller_id == Auth::id() 
                    ? $callRequest->receiver 
                    : $callRequest->caller;
            }
            
            // Ensure call is accepted before allowing video call
            if ($callRequest && $callRequest->status === 'pending') {
                // If user is receiver, they need to accept first
                if ($callRequest->receiver_id == Auth::id()) {
                    return redirect()->route('dashboard')->with('error', 'Please accept the call first.');
                }
                // If user is caller, wait for receiver to accept
                // Allow them to see the call page but show waiting message
            }
        }
        
        return view('video-call', [
            'roomId' => $roomId,
            'otherUser' => $otherUser,
            'callRequest' => $callRequest
        ]);
    }

    /**
     * Show group video call page
     */
    public function showGroupVideoCall(Request $request)
    {
        $roomId = $request->get('room');
        
        if (!$roomId) {
            return redirect()->route('dashboard')->with('error', 'Room ID required');
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->first();
        
        if (!$groupCall) {
            return redirect()->route('dashboard')->with('error', 'Group call not found');
        }
        
        // Check if user is a participant
        $isParticipant = $groupCall->participants()->where('user_id', Auth::id())->exists();
        
        if (!$isParticipant) {
            return redirect()->route('dashboard')->with('error', 'You are not a participant in this call');
        }
        
        $participants = $groupCall->activeParticipants()
            ->with('user:id,name,profile_picture')
            ->get()
            ->map(function($p) {
                return $p->user;
            });
        
        return view('group-video-call', [
            'roomId' => $roomId,
            'groupCall' => $groupCall,
            'participants' => $participants,
        ]);
    }

    /**
     * Show group audio call page
     */
    public function showGroupAudioCall(Request $request)
    {
        $roomId = $request->get('room');
        
        if (!$roomId) {
            return redirect()->route('dashboard')->with('error', 'Room ID required');
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->first();
        
        if (!$groupCall) {
            return redirect()->route('dashboard')->with('error', 'Group call not found');
        }
        
        // Check if user is a participant
        $isParticipant = $groupCall->participants()->where('user_id', Auth::id())->exists();
        
        if (!$isParticipant) {
            return redirect()->route('dashboard')->with('error', 'You are not a participant in this call');
        }
        
        $participants = $groupCall->activeParticipants()
            ->with('user:id,name,profile_picture')
            ->get()
            ->map(function($p) {
                return $p->user;
            });
        
        return view('group-audio-call', [
            'roomId' => $roomId,
            'groupCall' => $groupCall,
            'participants' => $participants,
        ]);
    }

    /**
     * Show audio call page
     */
    public function showAudioCall(Request $request, $userId = null)
    {
        $roomId = $request->get('room', uniqid('audio_', true));
        $otherUser = null;
        $callRequest = null;
        
        if ($userId) {
            $otherUser = User::findOrFail($userId);
        }
        
        // Get call request info if room_id exists
        if ($roomId && $roomId !== '' && strpos($roomId, 'audio_') === 0) {
            $callRequest = CallRequest::where('room_id', $roomId)
                ->where(function($query) {
                    $query->where('caller_id', Auth::id())
                          ->orWhere('receiver_id', Auth::id());
                })
                ->with(['caller:id,name,profile_picture', 'receiver:id,name,profile_picture'])
                ->first();
            
            if ($callRequest && !$otherUser) {
                $otherUser = $callRequest->caller_id == Auth::id() 
                    ? $callRequest->receiver 
                    : $callRequest->caller;
            }
            
            // Ensure call is accepted before allowing audio call
            if ($callRequest && $callRequest->status === 'pending') {
                if ($callRequest->receiver_id == Auth::id()) {
                    return redirect()->route('dashboard')->with('error', 'Please accept the call first.');
                }
            }
        }
        
        return view('audio-call', [
            'roomId' => $roomId,
            'otherUser' => $otherUser,
            'callRequest' => $callRequest
        ]);
    }

    /**
     * Check call status
     */
    public function checkCallStatus(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
        ]);

        $callRequest = CallRequest::where('room_id', $validated['room_id'])
            ->where(function($query) {
                $query->where('caller_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
            })
            ->with(['caller:id,name', 'receiver:id,name'])
            ->first();

        if (!$callRequest) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Call not found'
            ]);
        }

        $otherUser = $callRequest->caller_id == Auth::id() 
            ? $callRequest->receiver 
            : $callRequest->caller;

        // Check if other user is in the call (simple check - if status is accepted, both are connected)
        $isOtherUserReady = $callRequest->status === 'accepted';

        return response()->json([
            'status' => $callRequest->status,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
            ],
            'other_user_ready' => $isOtherUserReady,
            'ended_at' => $callRequest->ended_at,
        ]);
    }

    /**
     * Store WebRTC offer (simple signaling)
     */
    public function storeOffer(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
            'offer' => 'required|string',
        ]);

        // Store in cache for 5 minutes
        $key = 'webrtc_offer_' . $validated['room_id'] . '_' . Auth::id();
        \Cache::put($key, $validated['offer'], 300);

        return response()->json(['success' => true]);
    }

    /**
     * Get WebRTC offer
     */
    public function getOffer(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
        ]);

        // Get other user's offer
        $callRequest = CallRequest::where('room_id', $validated['room_id'])
            ->where(function($query) {
                $query->where('caller_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
            })
            ->first();

        if (!$callRequest) {
            return response()->json(['offer' => null]);
        }

        $otherUserId = $callRequest->caller_id == Auth::id() 
            ? $callRequest->receiver_id 
            : $callRequest->caller_id;

        $key = 'webrtc_offer_' . $validated['room_id'] . '_' . $otherUserId;
        $offer = \Cache::get($key);

        return response()->json(['offer' => $offer]);
    }

    /**
     * Store WebRTC answer
     */
    public function storeAnswer(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
            'answer' => 'required|string',
        ]);

        $key = 'webrtc_answer_' . $validated['room_id'] . '_' . Auth::id();
        \Cache::put($key, $validated['answer'], 300);

        return response()->json(['success' => true]);
    }

    /**
     * Get WebRTC answer
     */
    public function getAnswer(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
        ]);

        $callRequest = CallRequest::where('room_id', $validated['room_id'])
            ->where(function($query) {
                $query->where('caller_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
            })
            ->first();

        if (!$callRequest) {
            return response()->json(['answer' => null]);
        }

        $otherUserId = $callRequest->caller_id == Auth::id() 
            ? $callRequest->receiver_id 
            : $callRequest->caller_id;

        $key = 'webrtc_answer_' . $validated['room_id'] . '_' . $otherUserId;
        $answer = \Cache::get($key);

        return response()->json(['answer' => $answer]);
    }

    /**
     * Store ICE candidate
     */
    public function storeIceCandidate(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
            'candidate' => 'required|string',
        ]);

        $key = 'webrtc_ice_' . $validated['room_id'] . '_' . Auth::id();
        $candidates = \Cache::get($key, []);
        $candidates[] = $validated['candidate'];
        \Cache::put($key, $candidates, 300);

        return response()->json(['success' => true]);
    }

    /**
     * Get ICE candidates
     */
    public function getIceCandidates(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
        ]);

        $callRequest = CallRequest::where('room_id', $validated['room_id'])
            ->where(function($query) {
                $query->where('caller_id', Auth::id())
                      ->orWhere('receiver_id', Auth::id());
            })
            ->first();

        if (!$callRequest) {
            return response()->json(['candidates' => []]);
        }

        $otherUserId = $callRequest->caller_id == Auth::id() 
            ? $callRequest->receiver_id 
            : $callRequest->caller_id;

        $key = 'webrtc_ice_' . $validated['room_id'] . '_' . $otherUserId;
        $candidates = \Cache::get($key, []);

        return response()->json(['candidates' => $candidates]);
    }

    /**
     * Initiate a call request
     */
    public function initiateCall(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $receiver = User::findOrFail($validated['receiver_id']);
        
        // Check if already has pending call
        $existingCall = \App\Models\CallRequest::where('receiver_id', $validated['receiver_id'])
            ->where('status', 'pending')
            ->where('caller_id', Auth::id())
            ->first();

        if ($existingCall) {
            return response()->json([
                'success' => true,
                'room_id' => $existingCall->room_id,
                'message' => 'Call already initiated'
            ]);
        }

        $roomId = 'room_' . uniqid();
        
        $callRequest = \App\Models\CallRequest::create([
            'caller_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'room_id' => $roomId,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'call_request_id' => $callRequest->id,
            'message' => 'Call request sent'
        ]);
    }

    /**
     * Check for incoming call requests
     */
    public function checkIncomingCalls()
    {
        $incomingCalls = \App\Models\CallRequest::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->with('caller:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($call) {
                // Determine call type from room_id - strict check
                // Check if room_id starts with 'audio_' (position 0)
                $callType = 'video'; // Default to video
                if ($call->room_id) {
                    if (strpos($call->room_id, 'audio_') === 0) {
                        $callType = 'audio';
                    } elseif (strpos($call->room_id, 'group_') === 0) {
                        // Group calls might have type in the call itself, but for individual calls
                        // we only check audio_ prefix
                        $callType = 'video';
                    }
                }
                
                return [
                    'id' => $call->id,
                    'room_id' => $call->room_id,
                    'type' => $callType,
                    'caller' => $call->caller,
                    'created_at' => $call->created_at,
                ];
            });

        return response()->json([
            'calls' => $incomingCalls,
            'count' => $incomingCalls->count()
        ]);
    }

    /**
     * Accept call request
     */
    public function acceptCall(Request $request)
    {
        $validated = $request->validate([
            'call_request_id' => 'required|exists:call_requests,id',
        ]);

        $callRequest = \App\Models\CallRequest::where('id', $validated['call_request_id'])
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $roomId = $callRequest->room_id;
        
        // Update call request status
        $callRequest->update([
            'status' => 'accepted',
            'answered_at' => now(),
        ]);

        // If it's a group call, make sure user is added as participant
        if (strpos($roomId, 'group_') === 0) {
            $groupCall = GroupCall::where('room_id', $roomId)->first();
            if ($groupCall) {
                $participant = $groupCall->participants()->where('user_id', Auth::id())->first();
                if (!$participant) {
                    // Add user as participant
                    GroupCallParticipant::create([
                        'group_call_id' => $groupCall->id,
                        'user_id' => Auth::id(),
                        'status' => 'joined',
                        'joined_at' => now(),
                    ]);
                } elseif ($participant->status === 'left') {
                    // Re-join if they left before
                    $participant->update([
                        'status' => 'joined',
                        'joined_at' => now(),
                        'left_at' => null,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'room_id' => $callRequest->room_id,
            'message' => 'Call accepted',
            'is_group_call' => strpos($roomId, 'group_') === 0,
        ]);
    }

    /**
     * Reject call request
     */
    public function rejectCall(Request $request)
    {
        $validated = $request->validate([
            'call_request_id' => 'required|exists:call_requests,id',
        ]);

        $callRequest = \App\Models\CallRequest::where('id', $validated['call_request_id'])
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $roomId = $callRequest->room_id;
        
        // If it's a group call, remove user from participants
        if (strpos($roomId, 'group_') === 0) {
            $groupCall = GroupCall::where('room_id', $roomId)->first();
            if ($groupCall) {
                $participant = $groupCall->participants()->where('user_id', Auth::id())->first();
                if ($participant) {
                    // Mark participant as left
                    $participant->update([
                        'status' => 'left',
                        'left_at' => now(),
                    ]);
                }
            }
        }

        // Update call request status to rejected
        $callRequest->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Call rejected'
        ]);
    }

    /**
     * End call
     */
    public function endCall(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
        ]);

        $roomId = $validated['room_id'];
        $userId = Auth::id();

        // Check if it's a group call
        if (strpos($roomId, 'group_') === 0) {
            $groupCall = GroupCall::where('room_id', $roomId)->first();
            
            if ($groupCall) {
                // Mark user as left in group call participants
                $participant = $groupCall->participants()->where('user_id', $userId)->first();
                
                if ($participant && $participant->status === 'joined') {
                    $participant->update([
                        'status' => 'left',
                        'left_at' => now(),
                    ]);
                }
                
                // Update call requests for this user
                CallRequest::where('room_id', $roomId)
                    ->where(function($query) use ($userId) {
                        $query->where('caller_id', $userId)
                              ->orWhere('receiver_id', $userId);
                    })
                    ->update([
                        'status' => 'ended',
                        'ended_at' => now(),
                    ]);
                
                // Check if no active participants left, end the group call
                $activeCount = $groupCall->activeParticipants()->count();
                if ($activeCount === 0) {
                    $groupCall->update([
                        'status' => 'ended',
                        'ended_at' => now(),
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Left group call successfully',
                    'is_group_call' => true,
                    'active_participants' => $activeCount,
                ]);
            }
        }

        // Handle one-to-one call
        $callRequest = \App\Models\CallRequest::where('room_id', $roomId)
            ->where(function($query) use ($userId) {
                $query->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if ($callRequest) {
            $callRequest->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Call ended',
            'is_group_call' => false,
        ]);
    }

    /**
     * Add user to existing call (convert to group call if needed)
     */
    public function addUserToCall(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'call_type' => 'required|in:video,audio',
        ]);

        $roomId = $validated['room_id'];
        $userId = $validated['user_id'];
        $callType = $validated['call_type'];
        $currentUserId = Auth::id();

        // Check if user is trying to add themselves
        if ($userId == $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add yourself to the call'
            ], 400);
        }

        // Check if it's already a group call
        $groupCall = GroupCall::where('room_id', $roomId)->first();

        if ($groupCall) {
            // Already a group call, just add the participant
            $existingParticipant = $groupCall->participants()->where('user_id', $userId)->first();
            
            if ($existingParticipant) {
                // If already joined, just return success (don't show error)
                if ($existingParticipant->status === 'joined') {
                    return response()->json([
                        'success' => true,
                        'message' => 'User is already in the call',
                        'group_call_id' => $groupCall->id,
                        'room_id' => $roomId,
                        'already_joined' => true,
                    ]);
                } else {
                    // Re-join if they left before
                    $existingParticipant->update([
                        'status' => 'joined',
                        'joined_at' => now(),
                        'left_at' => null,
                    ]);
                    
                    // Create call invitation for re-joining user
                    CallRequest::updateOrCreate(
                        [
                            'caller_id' => $currentUserId,
                            'receiver_id' => $userId,
                            'room_id' => $roomId,
                        ],
                        [
                            'status' => 'pending',
                        ]
                    );
                }
            } else {
                // Add new participant
                GroupCallParticipant::create([
                    'group_call_id' => $groupCall->id,
                    'user_id' => $userId,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);
                
                // Create call invitation for the added user (avoid duplicates)
                CallRequest::updateOrCreate(
                    [
                        'caller_id' => $currentUserId,
                        'receiver_id' => $userId,
                        'room_id' => $roomId,
                    ],
                    [
                        'status' => 'pending',
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'User added to call successfully. They will receive a call invitation.',
                'group_call_id' => $groupCall->id,
                'room_id' => $roomId,
            ]);
        } else {
            // It's a one-to-one call, convert to group call
            $callRequest = CallRequest::where('room_id', $roomId)
                ->where(function($query) use ($currentUserId) {
                    $query->where('caller_id', $currentUserId)
                          ->orWhere('receiver_id', $currentUserId);
                })
                ->whereIn('status', ['pending', 'accepted'])
                ->first();

            if (!$callRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Call not found or not active'
                ], 404);
            }

            // Get all current participants
            $currentParticipants = [$callRequest->caller_id, $callRequest->receiver_id];
            
            // Create group call
            $newGroupCall = GroupCall::create([
                'room_id' => $roomId, // Use same room_id
                'created_by' => $currentUserId,
                'type' => $callType,
                'status' => 'active',
                'started_at' => now(),
            ]);

            // Add all existing participants
            foreach ($currentParticipants as $participantId) {
                GroupCallParticipant::create([
                    'group_call_id' => $newGroupCall->id,
                    'user_id' => $participantId,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);
            }

            // Add the new user
            GroupCallParticipant::create([
                'group_call_id' => $newGroupCall->id,
                'user_id' => $userId,
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            // Create call invitation for the added user (use updateOrCreate to avoid duplicates)
            CallRequest::updateOrCreate(
                [
                    'caller_id' => $currentUserId,
                    'receiver_id' => $userId,
                    'room_id' => $roomId,
                ],
                [
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'User added to call. Call converted to group call.',
                'group_call_id' => $newGroupCall->id,
                'room_id' => $roomId,
            ]);
        }
    }

    /**
     * Get all users for API
     */
    public function getUsers()
    {
        $users = User::where('id', '!=', Auth::id())
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        
        return response()->json(['users' => $users]);
    }

    /**
     * Get user info for WebRTC
     */
    public function getUserInfo()
    {
        return response()->json([
            'user' => [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $imageName = 'profile_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/profiles'), $imageName);
            
            // Delete old profile picture if exists
            if ($user->profile_picture && file_exists(public_path('storage/profiles/' . $user->profile_picture))) {
                @unlink(public_path('storage/profiles/' . $user->profile_picture));
            }
            
            $validated['profile_picture'] = $imageName;
        } else {
            unset($validated['profile_picture']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'name' => $user->name,
                'profile_picture' => $user->profile_picture,
            ]
        ]);
    }

    /**
     * Get messages between current user and another user
     */
    public function getMessages($userId)
    {
        $otherUser = User::findOrFail($userId);
        
        $messages = Message::where(function($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', Auth::id());
        })
        ->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'profile_picture' => $otherUser->profile_picture_url ?? null,
            ]
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:5000',
            'type' => 'nullable|in:text,image,file,audio,video,location,contact',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,heic,heif,pdf,doc,docx,txt,xls,xlsx,ppt,pptx,mp4,avi,mov,wmv,flv,webm,mp3,wav,ogg,m4a|max:51200', // Max 50MB
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_url' => 'nullable|string|max:500',
            'contact_user_id' => 'nullable|exists:users,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
        ], [
            'file.max' => 'File size must not exceed 50MB',
            'file.mimes' => 'File type not supported. Please select a valid image, video, audio, or document file.',
        ]);

        $filePath = null;
        $messageType = $request->type ?? 'text';
        
        // Handle location type
        if ($messageType === 'location') {
            if (!$request->latitude || !$request->longitude) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location coordinates are required'
                ], 400);
            }
            
            // Store location data in message text
            $locationData = [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'url' => $request->location_url ?? "https://www.google.com/maps?q={$request->latitude},{$request->longitude}"
            ];
            
            $messageText = $request->message ?? "📍 Location\nLatitude: {$request->latitude}\nLongitude: {$request->longitude}";
            
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $messageText,
                'type' => 'location',
                'file_path' => json_encode($locationData), // Store location data in file_path as JSON
            ]);

            $message->load(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture']);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }
        
        // Handle contact type
        if ($messageType === 'contact') {
            if (!$request->contact_user_id || !$request->contact_name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact information is required'
                ], 400);
            }
            
            // Store contact data in file_path as JSON (similar to location)
            $contactData = [
                'user_id' => $request->contact_user_id,
                'name' => $request->contact_name,
                'email' => $request->contact_email ?? '',
            ];
            
            $messageText = $request->message ?? "👤 Contact\nName: {$request->contact_name}\nEmail: " . ($request->contact_email ?? 'N/A');
            
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message' => $messageText,
                'type' => 'contact',
                'file_path' => json_encode($contactData), // Store contact data in file_path as JSON
            ]);

            $message->load(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture']);

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            try {
                $file = $request->file('file');
                
                // Check if file is valid
                if (!$file->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file. Error: ' . $file->getError()
                    ], 400);
                }
                
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                // Determine file type
                $mimeType = $file->getMimeType();
                if (strpos($mimeType, 'image/') === 0) {
                    $messageType = 'image';
                    $folder = 'messages/images';
                } elseif (strpos($mimeType, 'video/') === 0) {
                    $messageType = 'video';
                    $folder = 'messages/videos';
                } elseif (strpos($mimeType, 'audio/') === 0) {
                    $messageType = 'audio';
                    $folder = 'messages/audio';
                } else {
                    $messageType = 'file';
                    $folder = 'messages/files';
                }

                // Create directory if it doesn't exist
                $uploadPath = public_path('storage/' . $folder);
                if (!file_exists($uploadPath)) {
                    if (!mkdir($uploadPath, 0755, true)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create upload directory. Please check permissions.'
                        ], 500);
                    }
                }

                // Check if directory is writable
                if (!is_writable($uploadPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Upload directory is not writable. Please check permissions.'
                    ], 500);
                }

                // Generate unique filename
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                
                // Move file with error handling
                if (!$file->move($uploadPath, $fileName)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save file. Please try again.'
                    ], 500);
                }
                
                $filePath = $folder . '/' . $fileName;
            } catch (\Exception $e) {
                \Log::error('File upload error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $e->getMessage()
                ], 500);
            }
        } elseif ($request->file_path) {
            // If file_path is provided directly (for already uploaded files)
            $filePath = $request->file_path;
        }

        // Message text is optional for media messages
        $messageText = $request->message;
        if (empty($messageText) && $filePath) {
            // If no message text, use file name or default
            $messageText = $request->hasFile('file') 
                ? $request->file('file')->getClientOriginalName() 
                : 'Media';
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $messageText,
            'type' => $messageType,
            'file_path' => $filePath,
        ]);

        $message->load(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture']);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
        ]);

        Message::where('sender_id', $request->sender_id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get all conversations with last message
     */
    public function getConversations()
    {
        $conversations = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender:id,name,profile_picture', 'receiver:id,name,profile_picture'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($message) {
                return $message->sender_id == Auth::id() 
                    ? $message->receiver_id 
                    : $message->sender_id;
            })
            ->map(function($messages, $userId) {
                $lastMessage = $messages->first();
                $otherUser = $lastMessage->sender_id == Auth::id() 
                    ? $lastMessage->receiver 
                    : $lastMessage->sender;
                
                $unreadCount = Message::where('sender_id', $userId)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                return [
                    'user_id' => $userId,
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'profile_picture' => $otherUser->profile_picture_url ?? null,
                    ],
                    'last_message_time' => $lastMessage->created_at->toIso8601String(),
                    'last_message' => [
                        'message' => $lastMessage->message,
                        'type' => $lastMessage->type,
                        'created_at' => $lastMessage->created_at,
                    ],
                    'unread_count' => $unreadCount,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Initiate audio call
     */
    public function initiateAudioCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $receiver = User::findOrFail($request->receiver_id);
        
        // Check if already has pending call
        $existingCall = CallRequest::where('receiver_id', $request->receiver_id)
            ->where('status', 'pending')
            ->where('caller_id', Auth::id())
            ->where('room_id', 'like', 'audio_%')
            ->first();

        if ($existingCall) {
            return response()->json([
                'success' => true,
                'room_id' => $existingCall->room_id,
                'message' => 'Audio call already initiated'
            ]);
        }

        // Create call request
        $roomId = 'audio_' . uniqid();
        $callRequest = CallRequest::create([
            'caller_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'room_id' => $roomId,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audio call initiated',
            'room_id' => $roomId,
            'call_request_id' => $callRequest->id,
        ]);
    }

    /**
     * Delete multiple calls
     */
    public function deleteCalls(Request $request)
    {
        // Validate call_ids array
        $request->validate([
            'call_ids' => 'required|array|min:1',
        ], [
            'call_ids.required' => 'Please select at least one call to delete.',
            'call_ids.array' => 'Invalid call selection.',
            'call_ids.min' => 'Please select at least one call to delete.',
        ]);

        $currentUserId = Auth::id();
        $deletedIndividual = 0;
        $deletedGroup = 0;

        // Separate individual calls and group calls
        $individualCallIds = [];
        $groupCallIds = [];

        foreach ($request->call_ids as $callId) {
            // Check if it's a group call ID (starts with "group_")
            if (is_string($callId) && strpos($callId, 'group_') === 0) {
                // Extract the numeric ID after "group_"
                $groupId = str_replace('group_', '', $callId);
                if (is_numeric($groupId) && $groupId > 0) {
                    $groupCallIds[] = (int)$groupId;
                }
            } elseif (is_numeric($callId) && $callId > 0) {
                // Regular individual call ID
                $individualCallIds[] = (int)$callId;
            }
        }

        // Delete individual calls
        if (!empty($individualCallIds)) {
            $deletedIndividual = CallRequest::whereIn('id', $individualCallIds)
                ->where(function($query) use ($currentUserId) {
                    $query->where('caller_id', $currentUserId)
                          ->orWhere('receiver_id', $currentUserId);
                })
                ->delete();
        }

        // Delete group calls (only if user is a participant)
        if (!empty($groupCallIds)) {
            $groupCallsToDelete = GroupCall::whereIn('id', $groupCallIds)
                ->whereHas('participants', function($query) use ($currentUserId) {
                    $query->where('user_id', $currentUserId);
                })
                ->get();

            foreach ($groupCallsToDelete as $groupCall) {
                // Remove user's participation record
                GroupCallParticipant::where('group_call_id', $groupCall->id)
                    ->where('user_id', $currentUserId)
                    ->delete();
                
                $deletedGroup++;
            }
        }

        $totalDeleted = $deletedIndividual + $deletedGroup;

        if ($totalDeleted === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No valid calls selected for deletion or you do not have permission to delete these calls.'
            ], 400);
        }

        $message = '';
        if ($deletedIndividual > 0 && $deletedGroup > 0) {
            $message = $totalDeleted . ' call(s) deleted successfully (' . $deletedIndividual . ' individual, ' . $deletedGroup . ' group)';
        } elseif ($deletedIndividual > 0) {
            $message = $deletedIndividual . ' call(s) deleted successfully';
        } else {
            $message = $deletedGroup . ' group call(s) deleted successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'deleted_count' => $totalDeleted,
            'deleted_individual' => $deletedIndividual,
            'deleted_group' => $deletedGroup
        ]);
    }

    /**
     * Delete multiple messages
     */
    public function deleteMessages(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'required|integer|exists:messages,id'
        ]);

        $messageIds = $request->message_ids;
        $currentUserId = Auth::id();

        // Only delete messages where user is sender or receiver
        $deleted = Message::whereIn('id', $messageIds)
            ->where(function($query) use ($currentUserId) {
                $query->where('sender_id', $currentUserId)
                      ->orWhere('receiver_id', $currentUserId);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' message(s) deleted successfully',
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Create a group call (video or audio)
     */
    public function createGroupCall(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|in:video,audio',
        ]);

        $userIds = $request->user_ids;
        $currentUserId = Auth::id();
        
        // Add current user to participants
        $allUserIds = array_unique(array_merge([$currentUserId], $userIds));
        
        // Generate unique room ID
        $roomId = 'group_' . $request->type . '_' . uniqid();
        
        // Create group call
        $groupCall = GroupCall::create([
            'room_id' => $roomId,
            'created_by' => $currentUserId,
            'type' => $request->type,
            'status' => 'active',
            'started_at' => now(),
        ]);
        
        // Add all participants
        foreach ($allUserIds as $userId) {
            GroupCallParticipant::create([
                'group_call_id' => $groupCall->id,
                'user_id' => $userId,
                'status' => 'joined',
                'joined_at' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'group_call_id' => $groupCall->id,
            'type' => $request->type,
            'participants' => $groupCall->activeParticipants()->with('user:id,name,profile_picture')->get()->map(function($p) {
                return [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'profile_picture' => $p->user->profile_picture_url ?? null,
                ];
            }),
        ]);
    }

    /**
     * Get group call details
     */
    public function getGroupCall(Request $request)
    {
        $roomId = $request->get('room_id');
        
        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Room ID required'], 400);
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->first();
        
        if (!$groupCall) {
            return response()->json(['success' => false, 'message' => 'Group call not found'], 404);
        }
        
        // Check if user is a participant
        $isParticipant = $groupCall->participants()->where('user_id', Auth::id())->exists();
        
        if (!$isParticipant) {
            return response()->json(['success' => false, 'message' => 'Not a participant'], 403);
        }
        
        // Get all participants (both joined and left) for history
        $allParticipants = $groupCall->participants()
            ->with('user:id,name,profile_picture')
            ->get()
            ->map(function($p) {
                $hasPic = $p->user->profile_picture && file_exists(public_path('storage/profiles/' . $p->user->profile_picture));
                return [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'profile_picture' => $hasPic ? asset('storage/profiles/' . $p->user->profile_picture) : null,
                    'status' => $p->status,
                    'joined_at' => $p->joined_at,
                    'left_at' => $p->left_at,
                ];
            });
        
        // Calculate call duration - use actual call start and end times
        $duration = null;
        $durationText = '0:00';
        
        // Only calculate duration for ended calls
        if ($groupCall->status === 'ended') {
            // Get actual call start time - earliest joined_at or started_at
            $firstParticipant = $groupCall->participants()
                ->whereNotNull('joined_at')
                ->orderBy('joined_at', 'asc')
                ->first();
            
            // Use first participant's joined_at if available, otherwise use started_at
            if ($firstParticipant && $firstParticipant->joined_at) {
                $actualStartTime = $firstParticipant->joined_at;
            } elseif ($groupCall->started_at) {
                $actualStartTime = $groupCall->started_at;
            } else {
                // If no start time available, can't calculate duration
                $actualStartTime = null;
            }
            
            // Get actual call end time
            if ($groupCall->ended_at) {
                // Use ended_at if it's set
                $endTime = $groupCall->ended_at;
            } else {
                // If call is ended but ended_at is null, use last participant's left_at
                $lastParticipant = $groupCall->participants()
                    ->whereNotNull('left_at')
                    ->orderBy('left_at', 'desc')
                    ->first();
                
                if ($lastParticipant && $lastParticipant->left_at) {
                    $endTime = $lastParticipant->left_at;
                } else {
                    // Fallback: use updated_at only if it's after started_at
                    if ($groupCall->updated_at && $actualStartTime && $groupCall->updated_at > $actualStartTime) {
                        $endTime = $groupCall->updated_at;
                    } else {
                        $endTime = $actualStartTime; // Can't determine end time
                    }
                }
            }
            
            // Calculate duration only if we have valid start and end times
            if ($actualStartTime && $endTime && $endTime >= $actualStartTime) {
                $duration = $actualStartTime->diffInSeconds($endTime);
                
                // Calculate duration text
                $hours = floor($duration / 3600);
                $minutes = floor(($duration % 3600) / 60);
                $seconds = $duration % 60;
                
                if ($hours > 0) {
                    $durationText = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    $durationText = sprintf('%d:%02d', $minutes, $seconds);
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'group_call' => [
                'id' => $groupCall->id,
                'room_id' => $groupCall->room_id,
                'type' => $groupCall->type,
                'status' => $groupCall->status,
                'created_by' => $groupCall->created_by,
                'started_at' => $groupCall->started_at,
                'ended_at' => $groupCall->ended_at,
                'duration' => $duration,
                'duration_text' => $durationText,
            ],
            'participants' => $allParticipants,
        ]);
    }

    /**
     * Join a group call
     */
    public function joinGroupCall(Request $request)
    {
        $roomId = $request->get('room_id');
        
        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Room ID required'], 400);
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->where('status', 'active')->first();
        
        if (!$groupCall) {
            return response()->json(['success' => false, 'message' => 'Group call not found or ended'], 404);
        }
        
        $currentUserId = Auth::id();
        
        // Check if already a participant
        $participant = $groupCall->participants()->where('user_id', $currentUserId)->first();
        
        if ($participant) {
            // Update status if left before
            if ($participant->status === 'left') {
                $participant->update([
                    'status' => 'joined',
                    'joined_at' => now(),
                    'left_at' => null,
                ]);
            }
        } else {
            // Add as new participant
            GroupCallParticipant::create([
                'group_call_id' => $groupCall->id,
                'user_id' => $currentUserId,
                'status' => 'joined',
                'joined_at' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Joined group call successfully',
        ]);
    }

    /**
     * Leave a group call
     */
    public function leaveGroupCall(Request $request)
    {
        $roomId = $request->get('room_id');
        
        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Room ID required'], 400);
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->first();
        
        if (!$groupCall) {
            return response()->json(['success' => false, 'message' => 'Group call not found'], 404);
        }
        
        $participant = $groupCall->participants()->where('user_id', Auth::id())->first();
        
        if ($participant && $participant->status === 'joined') {
            $participant->update([
                'status' => 'left',
                'left_at' => now(),
            ]);
        }
        
        // Check if no active participants left, end the call
        $activeCount = $groupCall->activeParticipants()->count();
        if ($activeCount === 0) {
            $groupCall->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Left group call successfully',
        ]);
    }

    /**
     * Get individual call details
     */
    public function getCallDetails(Request $request)
    {
        $callId = $request->get('call_id');
        
        if (!$callId) {
            return response()->json(['success' => false, 'message' => 'Call ID required'], 400);
        }
        
        $userId = Auth::id();
        $call = CallRequest::where('id', $callId)
            ->where(function($query) use ($userId) {
                $query->where('caller_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->with(['caller:id,name,profile_picture', 'receiver:id,name,profile_picture'])
            ->first();
        
        if (!$call) {
            return response()->json(['success' => false, 'message' => 'Call not found'], 404);
        }
        
        // Get other user
        $otherUser = $call->caller_id == $userId 
            ? $call->receiver 
            : $call->caller;
        
        // Determine call type
        $callType = strpos($call->room_id, 'audio_') === 0 ? 'audio' : 'video';
        
        // Determine call direction
        $isOutgoing = $call->caller_id == $userId;
        
        // Calculate call duration
        // For consistency between users, use ended_at for ended calls
        // For active calls, use updated_at (last activity time) instead of now()
        // This ensures both users see the same duration
        $duration = null;
        $durationText = '0:00';
        if ($call->answered_at) {
            // Use ended_at if call is ended, otherwise use updated_at for active calls
            // This ensures both users see the same duration
            if ($call->ended_at) {
                // Call is ended - use ended_at
                $endTime = $call->ended_at;
            } else {
                // Call is active - use updated_at (last activity time) instead of now()
                // This ensures both users see the same duration
                $endTime = $call->updated_at ?? $call->answered_at;
            }
            
            $duration = $call->answered_at->diffInSeconds($endTime);
            
            // Ensure duration is not negative
            if ($duration < 0) {
                $duration = 0;
            }
            
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $seconds = $duration % 60;
            
            if ($hours > 0) {
                $durationText = sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
            } else {
                $durationText = sprintf('%d:%02d', $minutes, $seconds);
            }
        }
        
        // Get call history count (total calls between these two users)
        $callHistoryCount = CallRequest::where(function($query) use ($userId, $otherUser) {
                $query->where(function($q) use ($userId, $otherUser) {
                    $q->where('caller_id', $userId)
                      ->where('receiver_id', $otherUser->id);
                })->orWhere(function($q) use ($userId, $otherUser) {
                    $q->where('caller_id', $otherUser->id)
                      ->where('receiver_id', $userId);
                });
            })
            ->where(function($query) {
                // Exclude group calls
                $query->where('room_id', 'not like', 'group_%');
            })
            ->whereIn('status', ['accepted', 'ended'])
            ->count();
        
        return response()->json([
            'success' => true,
            'call' => [
                'id' => $call->id,
                'room_id' => $call->room_id,
                'type' => $callType,
                'status' => $call->status,
                'is_outgoing' => $isOutgoing,
                'created_at' => $call->created_at,
                'answered_at' => $call->answered_at,
                'ended_at' => $call->ended_at,
                'duration' => $duration,
                'duration_text' => $durationText,
            ],
            'other_user' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'profile_picture' => $otherUser->profile_picture && file_exists(public_path('storage/profiles/' . $otherUser->profile_picture))
                    ? asset('storage/profiles/' . $otherUser->profile_picture)
                    : null,
            ],
            'call_history' => [
                'total_calls' => $callHistoryCount,
            ],
        ]);
    }

    /**
     * Get group call participants (only those who have accepted the call)
     */
    public function getGroupCallParticipants(Request $request)
    {
        $roomId = $request->get('room_id');
        
        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Room ID required'], 400);
        }
        
        $groupCall = GroupCall::where('room_id', $roomId)->first();
        
        if (!$groupCall) {
            return response()->json(['success' => false, 'message' => 'Group call not found'], 404);
        }
        
        // Get all active participants (status = 'joined')
        $allParticipants = $groupCall->activeParticipants()
            ->with('user:id,name,profile_picture')
            ->get();
        
        $createdBy = $groupCall->created_by;
        
        // Get initial call participants (caller and receiver from original call request)
        $initialCallRequest = CallRequest::where('room_id', $roomId)
            ->whereIn('status', ['accepted', 'pending'])
            ->first();
        
        $initialParticipants = [];
        if ($initialCallRequest) {
            $initialParticipants = [$initialCallRequest->caller_id, $initialCallRequest->receiver_id];
        }
        
        // Filter to only include participants who have accepted the call
        // Check if there's an accepted CallRequest for each participant
        $acceptedParticipants = $allParticipants->filter(function($participant) use ($roomId, $createdBy, $initialParticipants) {
            $userId = $participant->user_id;
            
            // If user is the creator of the group call, they're automatically accepted
            if ($createdBy == $userId) {
                return true;
            }
            
            // If user is part of initial call (caller or receiver), they're automatically accepted
            if (in_array($userId, $initialParticipants)) {
                return true;
            }
            
            // Check if user has an accepted call request for this room
            $acceptedCallRequest = CallRequest::where('room_id', $roomId)
                ->where(function($query) use ($userId) {
                    $query->where('caller_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })
                ->where('status', 'accepted')
                ->exists();
            
            return $acceptedCallRequest;
        })->map(function($p) {
            return [
                'id' => $p->user->id,
                'name' => $p->user->name,
                'profile_picture' => $p->user->profile_picture_url ?? null,
                'joined_at' => $p->joined_at,
            ];
        });
        
        return response()->json([
            'success' => true,
            'participants' => $acceptedParticipants->values(),
        ]);
    }
}
