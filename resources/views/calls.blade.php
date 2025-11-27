@extends('layouts.app')

@section('title', 'Calls - Video Call App')

@section('content')
@php
    function formatCallDate($date) {
        // Convert to India timezone (IST - UTC+5:30)
        $callDate = \Carbon\Carbon::parse($date)->setTimezone('Asia/Kolkata');
        
        // Get current time in India timezone for comparison
        $now = \Carbon\Carbon::now('Asia/Kolkata');
        
        // Check if today in India timezone
        if ($callDate->isSameDay($now)) {
            return 'Today, ' . $callDate->format('g:i A');
        } elseif ($callDate->isSameDay($now->copy()->subDay())) {
            return 'Yesterday, ' . $callDate->format('g:i A');
        } elseif ($callDate->isCurrentYear()) {
            return $callDate->format('d M, g:i A');
        } else {
            return $callDate->format('d M Y, g:i A');
        }
    }
@endphp
<style>
/* Mobile responsive styles */
@media (max-width: 768px) {
    .navbar {
        margin-bottom: 0 !important;
    }
    .calls-container {
        padding-bottom: calc(70px + env(safe-area-inset-bottom)) !important; /* Space for bottom nav + safe area */
        background: #f0f2f5 !important;
        min-height: calc(100vh - 60px) !important; /* Full height minus header */
    }
    .calls-list {
        padding-bottom: 20px !important; /* Extra padding for last item */
    }
    .call-item {
        padding: 12px 16px !important;
        background: white !important;
        border-bottom: 1px solid #e9edef !important;
        cursor: pointer;
    }
    .call-item:active {
        background: #f5f6f6 !important;
    }
    .call-item:hover {
        background: #f5f6f6 !important;
    }
    .call-profile-img {
        width: 48px !important;
        height: 48px !important;
    }
    /* Force styling for profile icons with first letter */
    .call-profile-img[style*="linear-gradient"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .call-profile-img[style*="linear-gradient"] span {
        color: #ffffff !important;
        font-weight: bold !important;
        font-size: 18px !important;
    }
    .call-name {
        font-size: 17px !important;
        font-weight: 500 !important;
        color: #111b21 !important;
        margin-bottom: 2px !important;
    }
    .call-details {
        font-size: 14px !important;
        color: #667781 !important;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .call-time {
        font-size: 13px !important;
        color: #667781 !important;
        white-space: nowrap;
    }
    .call-type-icon {
        font-size: 20px !important;
        color: #25d366 !important;
    }
}
</style>

<div class="container-fluid p-0 calls-container" style="min-height: calc(100vh - 60px); background: #f0f2f5;">
    <!-- Header -->
    <div class="bg-white border-bottom p-3 sticky-top" style="background: #008069 !important; z-index: 10;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm text-white me-2" onclick="goToDashboard()" id="backButton" style="background: transparent; border: none; padding: 0.25rem 0.5rem;">
                    <i class="bi bi-arrow-left fs-5"></i>
                </button>
                <h5 class="mb-0 text-white fw-bold" id="headerTitle">Calls</h5>
            </div>
            <div class="d-flex align-items-center gap-2" id="headerActions">
                <button class="btn btn-sm text-white" onclick="toggleSelectionMode()" id="selectButton" style="background: transparent; border: none; padding: 0.25rem 0.5rem;">
                    <i class="bi bi-check-square fs-5"></i>
                </button>
                <button class="btn btn-sm text-white d-none" onclick="deleteSelectedCalls()" id="deleteButton" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); padding: 0.25rem 0.75rem; border-radius: 20px;">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Calls List -->
    <div class="calls-list" style="background: #f0f2f5; padding-bottom: 20px;">
        @if($calls->count() > 0)
            @foreach($calls as $call)
                @if(isset($call['is_group']) && $call['is_group'])
                    {{-- GROUP CALL --}}
                    <div class="call-item border-bottom bg-white" 
                         data-call-id="{{ $call['id'] }}"
                         style="transition: background 0.2s; margin-bottom: 0;"
                         onclick="joinGroupCall('{{ $call['room_id'] }}', '{{ $call['type'] }}')">
                        <div class="d-flex align-items-center">
                            <!-- Selection Checkbox -->
                            <div class="flex-shrink-0 me-3 d-none selection-checkbox-container" style="width: 24px;">
                                <input type="checkbox" class="form-check-input call-checkbox" 
                                       value="{{ $call['id'] }}"
                                       onchange="updateDeleteButton()"
                                       style="width: 20px; height: 20px; cursor: pointer;">
                            </div>
                            
                            <!-- Group Profile Picture (Composite) -->
                            <div class="flex-shrink-0" style="margin-right: 12px; position: relative; width: 48px; height: 48px;">
                                @php
                                    $participants = $call['participants'] ?? collect();
                                    $firstTwo = $participants->take(2);
                                @endphp
                                @if($firstTwo->count() >= 2)
                                    {{-- Show 2 overlapping circles --}}
                                    <div style="position: relative; width: 48px; height: 48px;">
                                        @foreach($firstTwo as $index => $participant)
                                            @php
                                                $hasPic = $participant->profile_picture && file_exists(public_path('storage/profiles/' . $participant->profile_picture));
                                                $left = $index * 24;
                                            @endphp
                                            <div style="position: absolute; left: {{ $left }}px; width: 32px; height: 32px; border-radius: 50%; border: 2px solid white; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                @if($hasPic)
                                                    <img src="{{ asset('storage/profiles/' . $participant->profile_picture) }}" 
                                                         alt="{{ $participant->name }}" 
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center text-white" style="width: 100%; height: 100%;">
                                                        <span style="font-size: 12px; font-weight: bold;">{{ strtoupper(substr($participant->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($firstTwo->count() == 1)
                                    @php
                                        $participant = $firstTwo->first();
                                        $hasPic = $participant->profile_picture && file_exists(public_path('storage/profiles/' . $participant->profile_picture));
                                    @endphp
                                    @if($hasPic)
                                        <img src="{{ asset('storage/profiles/' . $participant->profile_picture) }}" 
                                             alt="{{ $participant->name }}" 
                                             class="rounded-circle" 
                                             style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white" 
                                             style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                                            <span class="fw-bold text-white" style="font-size: 18px;">{{ strtoupper(substr($participant->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white" 
                                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                                        <i class="bi bi-people-fill" style="font-size: 20px;"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Group Call Info -->
                            <div class="flex-grow-1 min-w-0" style="padding-right: 12px;">
                                <div class="call-name text-truncate">{{ $call['display_name'] ?? 'Group Call' }}</div>
                                <div class="call-details">
                                    @if($call['is_active'] ?? false)
                                        <span style="color: #25d366; font-weight: 500;">Tap to join</span>
                                    @else
                                        @if($call['is_outgoing'])
                                            <i class="bi bi-arrow-up-right" style="font-size: 12px; color: #667781;"></i>
                                            <span>You called</span>
                                        @else
                                            <i class="bi bi-arrow-down-left" style="font-size: 12px; color: #667781;"></i>
                                            <span>Group call</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                <span class="call-time">{{ formatCallDate($call['created_at'] ?? $call['started_at'] ?? now()) }}</span>
                            </div>
                            
                            <!-- Group Call Icon -->
                            <div class="flex-shrink-0" style="margin-left: 8px; padding: 8px;" 
                                 onclick="event.stopPropagation(); joinGroupCall('{{ $call['room_id'] }}', '{{ $call['type'] }}')"
                                 title="{{ $call['type'] == 'audio' ? 'Audio Group Call' : 'Video Group Call' }}">
                                @if($call['is_active'] ?? false)
                                    @if($call['type'] == 'audio')
                                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-telephone-fill text-white" style="font-size: 18px;"></i>
                                        </div>
                                    @else
                                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-camera-video-fill text-white" style="font-size: 18px;"></i>
                                        </div>
                                    @endif
                                @else
                                    @if($call['type'] == 'audio')
                                        <i class="bi bi-telephone-fill call-type-icon" style="font-size: 24px; color: #dc3545;"></i>
                                    @else
                                        <i class="bi bi-camera-video-fill call-type-icon" style="font-size: 24px; color: #dc3545;"></i>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- INDIVIDUAL CALL --}}
                    <div class="call-item border-bottom bg-white" 
                         data-call-id="{{ $call['id'] }}"
                         style="transition: background 0.2s; margin-bottom: 0;">
                        <div class="d-flex align-items-center">
                            <!-- Selection Checkbox -->
                            <div class="flex-shrink-0 me-3 d-none selection-checkbox-container" style="width: 24px;">
                                <input type="checkbox" class="form-check-input call-checkbox" 
                                       value="{{ $call['id'] }}"
                                       onchange="updateDeleteButton()"
                                       style="width: 20px; height: 20px; cursor: pointer;">
                            </div>
                            <!-- User Profile - Clickable for Profile -->
                            <div class="flex-shrink-0" style="margin-right: 12px; cursor: pointer;" 
                                 onclick="openUserProfile({{ $call['other_user']->id }})">
                                @php
                                    // Check if user has profile picture file
                                    $hasCallProfilePic = false;
                                    if ($call['other_user']->profile_picture && trim($call['other_user']->profile_picture) != '') {
                                        $callPicPath = public_path('storage/profiles/' . $call['other_user']->profile_picture);
                                        $hasCallProfilePic = file_exists($callPicPath) && is_file($callPicPath);
                                    }
                                @endphp
                                
                                @if($hasCallProfilePic)
                                    {{-- USER HAS PROFILE PICTURE - SHOW IMAGE --}}
                                    <img src="{{ asset('storage/profiles/' . $call['other_user']->profile_picture) }}" 
                                         alt="{{ $call['other_user']->name }}" 
                                         class="rounded-circle call-profile-img" 
                                         style="width: 48px; height: 48px; object-fit: cover;">
                                @else
                                    {{-- USER DOES NOT HAVE PROFILE PICTURE - SHOW FIRST LETTER --}}
                                    <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white call-profile-img" 
                                         style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; min-width: 48px; min-height: 48px;">
                                        <span class="fw-bold text-white" style="font-size: 18px; color: #ffffff !important;">{{ strtoupper(substr($call['other_user']->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Call Info - Clickable for Chat/Profile -->
                            <div class="flex-grow-1 min-w-0 d-flex align-items-center justify-content-between" 
                                 onclick="openUserChat({{ $call['other_user']->id }})" 
                                 style="cursor: pointer; flex: 1;">
                                <div class="flex-grow-1 min-w-0" style="padding-right: 12px;">
                                    <div class="call-name text-truncate">{{ $call['other_user']->name }}</div>
                                    <div class="call-details">
                                        @if($call['is_outgoing'])
                                            <i class="bi bi-arrow-up-right" style="font-size: 12px; color: #667781;"></i>
                                        @else
                                            <i class="bi bi-arrow-down-left" style="font-size: 12px; color: #667781;"></i>
                                        @endif
                                        <span>
                                            @if($call['is_outgoing'])
                                                You called
                                            @else
                                                {{ $call['other_user']->name }} called
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <span class="call-time">{{ formatCallDate($call['created_at']) }}</span>
                                </div>
                            </div>
                            
                            <!-- Call Icon - Only this triggers call -->
                            <div class="flex-shrink-0" style="margin-left: 8px; cursor: pointer; padding: 8px;" 
                                 onclick="event.stopPropagation(); openCallWithUser({{ $call['other_user']->id }}, '{{ $call['type'] }}')"
                                 title="{{ $call['type'] == 'audio' ? 'Audio Call' : 'Video Call' }}">
                                @if($call['type'] == 'audio')
                                    <i class="bi bi-telephone-fill call-type-icon" style="font-size: 24px; color: #25d366;"></i>
                                @else
                                    <i class="bi bi-camera-video-fill call-type-icon" style="font-size: 24px; color: #25d366;"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="text-center p-5 text-muted" style="background: white; margin: 20px; border-radius: 8px;">
                <i class="bi bi-telephone-x fs-1 d-block mb-3" style="color: #667781;"></i>
                <p class="mb-0" style="color: #111b21; font-size: 16px;">No call history</p>
                <small style="color: #667781;">Start calling to see your call history here</small>
            </div>
        @endif
    </div>
</div>

<!-- Mobile Bottom Navigation Bar -->
<div class="d-md-none fixed-bottom bg-white border-top shadow-lg" style="z-index: 1050; padding-bottom: calc(10px + env(safe-area-inset-bottom)); height: 60px;">
    <div class="container-fluid px-0">
        <div class="row g-0">
            <!-- Users List Button -->
            <div class="col-4 text-center py-2" onclick="goToDashboard()" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-chat-square-dots-fill fs-5 text-muted mb-1"></i>
                    <small class="text-muted" style="font-size: 0.7rem;">Chats</small>
                </div>
            </div>
            
            <!-- Calls Button (Active) -->
            <div class="col-4 text-center py-2 position-relative" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-telephone-fill fs-5 text-primary mb-1"></i>
                    <small class="text-primary" style="font-size: 0.7rem;">Calls</small>
                    <div class="position-absolute bottom-0 start-50 translate-middle-x" style="width: 40px; height: 2px; background: #008069; border-radius: 2px;"></div>
                </div>
            </div>
            
            <!-- Profile Button -->
            <div class="col-4 text-center py-2" onclick="openProfileModal()" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-person-circle fs-5 text-secondary mb-1"></i>
                    <small class="text-muted" style="font-size: 0.7rem;">Profile</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incoming Call Modal -->
<div class="modal fade" id="incomingCallModal" tabindex="-1" aria-labelledby="incomingCallModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <div class="rounded-circle bg-gradient d-inline-flex align-items-center justify-content-center text-white mb-3" 
                         style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-telephone-fill fs-2" id="callTypeIcon"></i>
                    </div>
                </div>
                <h5 class="mb-2" id="callerName">Incoming Call</h5>
                <p class="text-muted mb-2" id="callerEmail"></p>
                <p class="text-primary fw-semibold mb-4" id="callTypeText"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center" 
                            style="width: 60px; height: 60px;" onclick="rejectIncomingCall()">
                        <i class="bi bi-telephone-x-fill fs-4"></i>
                    </button>
                    <button class="btn btn-success rounded-circle d-flex align-items-center justify-content-center" 
                            style="width: 60px; height: 60px;" onclick="acceptIncomingCall()">
                        <i class="bi bi-telephone-fill fs-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh calls list every 5 seconds
let callsRefreshInterval = null;
let callCheckInterval = null;
let incomingCallModal = null;
let currentCallRequestId = null;

function refreshCallsList() {
    // Reload the page to get updated calls
    window.location.reload();
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap modal for incoming calls
    const modalElement = document.getElementById('incomingCallModal');
    if (modalElement) {
        incomingCallModal = new bootstrap.Modal(modalElement);
    }
    
    // Start checking for incoming calls
    startCallChecking();
    
    // Refresh calls list every 5 seconds
    callsRefreshInterval = setInterval(refreshCallsList, 5000);
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (callsRefreshInterval) {
            clearInterval(callsRefreshInterval);
        }
        if (callCheckInterval) {
            clearInterval(callCheckInterval);
        }
    });
});

// Start checking for incoming calls every 2 seconds
function startCallChecking() {
    checkIncomingCalls();
    callCheckInterval = setInterval(checkIncomingCalls, 2000); // Check every 2 seconds
}

// Check for incoming calls
function checkIncomingCalls() {
    fetch('{{ route("api.call.incoming") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Show modal for incoming calls automatically
        if (data.count > 0 && data.calls.length > 0) {
            // Show modal for first incoming call automatically
            const modalElement = document.getElementById('incomingCallModal');
            if (modalElement) {
                const isModalShown = modalElement.classList.contains('show');
                
                if (!isModalShown && incomingCallModal && data.calls[0]) {
                    showIncomingCallModal(data.calls[0]);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error checking calls:', error);
    });
}

// Show incoming call modal
function showIncomingCallModal(call) {
    if (!call || !call.caller) {
        console.error('Invalid call data:', call);
        return;
    }
    
    currentCallRequestId = call.id;
    
    // Update caller information
    const callerNameEl = document.getElementById('callerName');
    const callerEmailEl = document.getElementById('callerEmail');
    
    if (callerNameEl) {
        callerNameEl.textContent = call.caller.name + ' is calling...';
    }
    if (callerEmailEl) {
        callerEmailEl.textContent = call.caller.email || '';
    }
    
    // Show call type (video or audio)
    const callTypeIcon = document.getElementById('callTypeIcon');
    const callTypeText = document.getElementById('callTypeText');
    
    if (call.type === 'audio') {
        if (callTypeIcon) {
            callTypeIcon.className = 'bi bi-telephone-fill fs-2';
        }
        if (callTypeText) {
            callTypeText.textContent = 'Audio Call';
            callTypeText.className = 'text-info fw-semibold mb-4';
        }
    } else {
        if (callTypeIcon) {
            callTypeIcon.className = 'bi bi-camera-video-fill fs-2';
        }
        if (callTypeText) {
            callTypeText.textContent = 'Video Call';
            callTypeText.className = 'text-success fw-semibold mb-4';
        }
    }
    
    // Show modal
    if (incomingCallModal) {
        try {
            incomingCallModal.show();
        } catch (error) {
            console.error('Error showing modal:', error);
            // Fallback: show modal directly
            const modalElement = document.getElementById('incomingCallModal');
            if (modalElement) {
                const bsModal = new bootstrap.Modal(modalElement);
                bsModal.show();
            }
        }
    }
}

// Accept incoming call
function acceptIncomingCall() {
    if (!currentCallRequestId) return;
    
    fetch('{{ route("api.call.accept") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            call_request_id: currentCallRequestId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            if (incomingCallModal) {
                incomingCallModal.hide();
            }
            
            // Redirect to appropriate call page
            if (data.call_type === 'audio') {
                window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(data.room_id);
            } else {
                window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(data.room_id);
            }
        } else {
            alert(data.message || 'Failed to accept call');
        }
    })
    .catch(error => {
        console.error('Error accepting call:', error);
        alert('Error accepting call. Please try again.');
    });
}

// Reject incoming call
function rejectIncomingCall() {
    if (!currentCallRequestId) return;
    
    fetch('{{ route("api.call.reject") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            call_request_id: currentCallRequestId
        })
    })
    .then(response => response.json())
    .then(data => {
        // Hide modal regardless of response
        if (incomingCallModal) {
            incomingCallModal.hide();
        }
        
        // Reset current call request ID
        currentCallRequestId = null;
        
        // Refresh calls list to show updated status
        refreshCallsList();
    })
    .catch(error => {
        console.error('Error rejecting call:', error);
        // Hide modal even on error
        if (incomingCallModal) {
            incomingCallModal.hide();
        }
        currentCallRequestId = null;
    });
}

// Go to dashboard
function goToDashboard() {
    window.location.href = '{{ route("dashboard") }}';
}

// Open user profile (from profile picture click)
function openUserProfile(userId) {
    // Navigate to dashboard and open chat with user (which shows profile option)
    window.location.href = '{{ route("dashboard") }}?user=' + userId;
}

// Open user chat (from name/info click)
function openUserChat(userId) {
    // Navigate to dashboard and open chat with user
    window.location.href = '{{ route("dashboard") }}?user=' + userId;
}

// Join group call
function joinGroupCall(roomId, callType) {
    if (!roomId) {
        alert('Invalid call room');
        return;
    }
    
    // First, join the group call
    fetch('{{ route("api.group.call.join") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            room_id: roomId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to appropriate call page
            if (callType === 'audio') {
                window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(roomId);
            } else {
                window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(roomId);
            }
        } else {
            alert(data.message || 'Failed to join group call');
        }
    })
    .catch(error => {
        console.error('Error joining group call:', error);
        alert('Error joining group call');
    });
}

// Open call with user (only from icon click)
function openCallWithUser(userId, callType) {
    if (callType === 'audio') {
        // For audio call, we need to create a new call request
        fetch('{{ route("api.call.audio.initiate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(data.room_id);
            } else {
                alert('Failed to start audio call');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error starting call');
        });
    } else {
        // For video call
        fetch('{{ route("api.call.initiate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(data.room_id);
            } else {
                alert('Failed to start video call');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error starting call');
        });
    }
}

// Open profile modal (if exists)
function openProfileModal() {
    window.location.href = '{{ route("dashboard") }}';
}

// Multi-select delete functionality
let selectionMode = false;
let selectedCallIds = new Set();

// Prevent click events on call items when in selection mode
function preventCallItemClicks(event) {
    if (selectionMode) {
        // Only allow checkbox clicks
        if (event.target.type === 'checkbox' || event.target.closest('.call-checkbox') || event.target.closest('.selection-checkbox-container')) {
            return;
        }
        // Prevent all other clicks
        event.preventDefault();
        event.stopPropagation();
        
        // Toggle checkbox if clicking on the call item
        const callItem = event.currentTarget;
        const checkbox = callItem.querySelector('.call-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            updateDeleteButton();
        }
    }
}

// Toggle selection mode
function toggleSelectionMode() {
    selectionMode = !selectionMode;
    const checkboxes = document.querySelectorAll('.selection-checkbox-container');
    const selectButton = document.getElementById('selectButton');
    const deleteButton = document.getElementById('deleteButton');
    const backButton = document.getElementById('backButton');
    const headerTitle = document.getElementById('headerTitle');
    const callItems = document.querySelectorAll('.call-item');
    
    if (selectionMode) {
        // Enter selection mode - Stop auto-refresh
        if (callsRefreshInterval) {
            clearInterval(callsRefreshInterval);
            callsRefreshInterval = null;
        }
        
        checkboxes.forEach(cb => cb.classList.remove('d-none'));
        deleteButton.classList.remove('d-none');
        backButton.style.display = 'none';
        headerTitle.textContent = 'Select Calls';
        selectButton.innerHTML = '<i class="bi bi-x-lg fs-5"></i>';
        selectedCallIds.clear();
        updateDeleteButton();
        
        // Add click prevention to call items and disable inline onclick
        callItems.forEach(item => {
            // Store original onclick if exists
            if (item.onclick) {
                item.dataset.originalOnclick = item.getAttribute('onclick');
                item.removeAttribute('onclick');
            }
            item.addEventListener('click', preventCallItemClicks);
            item.style.cursor = 'pointer';
        });
    } else {
        // Exit selection mode - Resume auto-refresh
        if (!callsRefreshInterval) {
            callsRefreshInterval = setInterval(refreshCallsList, 5000);
        }
        
        checkboxes.forEach(cb => cb.classList.add('d-none'));
        deleteButton.classList.add('d-none');
        backButton.style.display = 'block';
        headerTitle.textContent = 'Calls';
        selectButton.innerHTML = '<i class="bi bi-check-square fs-5"></i>';
        // Uncheck all checkboxes
        document.querySelectorAll('.call-checkbox').forEach(cb => {
            cb.checked = false;
        });
        selectedCallIds.clear();
        
        // Remove click prevention from call items and restore inline onclick
        callItems.forEach(item => {
            item.removeEventListener('click', preventCallItemClicks);
            // Restore original onclick if it was stored
            if (item.dataset.originalOnclick) {
                item.setAttribute('onclick', item.dataset.originalOnclick);
                delete item.dataset.originalOnclick;
            }
        });
    }
}

// Update delete button state
function updateDeleteButton() {
    const checkboxes = document.querySelectorAll('.call-checkbox:checked');
    const deleteButton = document.getElementById('deleteButton');
    selectedCallIds.clear();
    
    checkboxes.forEach(cb => {
        if (cb.checked && cb.value) {
            // Accept both numeric IDs and string IDs (for group calls like "group_123")
            const callId = cb.value;
            if (callId) {
                selectedCallIds.add(callId);
            }
        }
    });
    
    if (selectedCallIds.size > 0) {
        deleteButton.innerHTML = `<i class="bi bi-trash me-1"></i>Delete (${selectedCallIds.size})`;
        deleteButton.style.display = 'flex';
    } else {
        deleteButton.style.display = 'none';
    }
}

// Delete selected calls
function deleteSelectedCalls() {
    if (selectedCallIds.size === 0) {
        alert('Please select calls to delete');
        return;
    }
    
    const confirmDelete = confirm(`Are you sure you want to delete ${selectedCallIds.size} call(s)?`);
    if (!confirmDelete) return;
    
    // Store selected IDs before deletion (can be integers or strings like "group_123")
    const idsToDelete = Array.from(selectedCallIds).filter(id => id && id.toString().trim() !== '');
    
    if (idsToDelete.length === 0) {
        alert('No valid calls selected for deletion');
        return;
    }
    
    // Disable delete button to prevent multiple clicks
    const deleteButton = document.getElementById('deleteButton');
    const originalButtonContent = deleteButton.innerHTML;
    deleteButton.disabled = true;
    deleteButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Deleting...';
    
    fetch('{{ route("api.calls.delete") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            call_ids: idsToDelete
        })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to delete calls');
            }).catch(() => {
                throw new Error('Failed to delete calls. Please try again.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove deleted call items from DOM
            idsToDelete.forEach(callId => {
                const callItem = document.querySelector(`[data-call-id="${callId}"]`);
                if (callItem) {
                    callItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    callItem.style.opacity = '0';
                    callItem.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        callItem.remove();
                    }, 300);
                }
            });
            
            // Clear selected IDs
            selectedCallIds.clear();
            
            // Exit selection mode
            toggleSelectionMode();
            
            // Show success message
            alert(data.message || 'Calls deleted successfully');
            
            // Check if no calls left and reload only if needed
            setTimeout(() => {
                const remainingCalls = document.querySelectorAll('.call-item').length;
                if (remainingCalls === 0) {
                    window.location.reload();
                }
            }, 500);
        } else {
            alert(data.message || 'Failed to delete calls');
            // Re-enable button on error
            deleteButton.disabled = false;
            deleteButton.innerHTML = originalButtonContent;
        }
    })
    .catch(error => {
        console.error('Error deleting calls:', error);
        alert(error.message || 'Error deleting calls. Please try again.');
        // Re-enable button on error
        deleteButton.disabled = false;
        deleteButton.innerHTML = originalButtonContent;
    });
}
</script>
@endsection
