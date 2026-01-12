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
                         data-room-id="{{ $call['room_id'] }}"
                         data-call-type="{{ $call['type'] }}"
                         data-is-active="{{ $call['is_active'] ?? false ? 'true' : 'false' }}"
                         style="transition: background 0.2s; margin-bottom: 0;">
                        <div class="d-flex align-items-center">
                            <!-- Selection Checkbox -->
                            <div class="flex-shrink-0 me-3 d-none selection-checkbox-container" style="width: 24px;">
                                <input type="checkbox" class="form-check-input call-checkbox" 
                                       value="{{ $call['id'] }}"
                                       onchange="updateDeleteButton()"
                                       style="width: 20px; height: 20px; cursor: pointer;">
                            </div>
                            
                            <!-- Group Profile Picture (Composite) - Click to show details -->
                            <div class="flex-shrink-0" style="margin-right: 12px; position: relative; width: 48px; height: 48px; cursor: pointer;" 
                                 onclick="event.stopPropagation(); showGroupCallDetails('{{ $call['room_id'] }}', '{{ $call['type'] }}')">
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

                            <!-- Group Call Info - Click to show details -->
                            <div class="flex-grow-1 min-w-0" style="padding-right: 12px; cursor: pointer;" 
                                 onclick="event.stopPropagation(); showGroupCallDetails('{{ $call['room_id'] }}', '{{ $call['type'] }}')">
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
                            
                            <div class="d-flex align-items-center gap-3 flex-shrink-0" style="cursor: pointer;" 
                                 onclick="event.stopPropagation(); showGroupCallDetails('{{ $call['room_id'] }}', '{{ $call['type'] }}')">
                                <span class="call-time">{{ formatCallDate($call['created_at'] ?? $call['started_at'] ?? now()) }}</span>
                            </div>
                            
                            <!-- Group Call Icon - Only this triggers join if active -->
                            <div class="flex-shrink-0" style="margin-left: 8px; padding: 8px; cursor: pointer;" 
                                 onclick="event.stopPropagation(); handleGroupCallClick('{{ $call['room_id'] }}', '{{ $call['type'] }}', {{ $call['is_active'] ?? false ? 'true' : 'false' }})"
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
                         data-room-id="{{ $call['room_id'] }}"
                         data-call-type="{{ $call['type'] }}"
                         data-call-status="{{ $call['status'] }}"
                         style="transition: background 0.2s; margin-bottom: 0;">
                        <div class="d-flex align-items-center">
                            <!-- Selection Checkbox -->
                            <div class="flex-shrink-0 me-3 d-none selection-checkbox-container" style="width: 24px;">
                                <input type="checkbox" class="form-check-input call-checkbox" 
                                       value="{{ $call['id'] }}"
                                       onchange="updateDeleteButton()"
                                       style="width: 20px; height: 20px; cursor: pointer;">
                            </div>
                            <!-- User Profile - Clickable for Call Details (always show modal) -->
                            <div class="flex-shrink-0" style="margin-right: 12px; cursor: pointer;" 
                                 onclick="showCallDetails({{ $call['id'] }})">
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

                            <!-- Call Info - Clickable for Call Details (always show modal) -->
                            <div class="flex-grow-1 min-w-0 d-flex align-items-center justify-content-between" 
                                 onclick="showCallDetails({{ $call['id'] }})" 
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
                            
                            <!-- Call Icon - Always start new call with correct type based on icon clicked -->
                            <div class="flex-shrink-0" style="margin-left: 8px; cursor: pointer; padding: 8px;" 
                                 onclick="event.stopPropagation(); handleCallIconClick({{ $call['other_user']->id }}, event)"
                                 title="Click to call">
                                @if($call['type'] == 'audio')
                                    <i class="bi bi-telephone-fill call-type-icon" style="font-size: 24px; color: #25d366;" data-call-type="audio"></i>
                                @else
                                    <i class="bi bi-camera-video-fill call-type-icon" style="font-size: 24px; color: #25d366;" data-call-type="video"></i>
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

<!-- Group Call Details Modal -->
<div class="modal fade" id="groupCallDetailsModal" tabindex="-1" aria-labelledby="groupCallDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="groupCallDetailsModalLabel">
                    <i class="bi bi-telephone-fill me-2"></i>Call Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-camera-video-fill fs-1 text-primary" id="groupCallTypeIcon"></i>
                    </div>
                    <h6 id="groupCallTypeText" class="text-muted mb-1">Video Call</h6>
                    <p class="text-muted mb-0" id="groupCallWithText">Group Call</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-clock-history fs-4 text-primary d-block mb-2"></i>
                            <small class="text-muted d-block">Duration</small>
                            <strong id="groupCallDuration">--:--</strong>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="mb-3"><i class="bi bi-people me-2"></i>Participants</h6>
                    <div id="groupCallParticipantsList" style="max-height: 300px; overflow-y: auto;">
                        <!-- Participants will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Individual Call Details Modal -->
<div class="modal fade" id="callDetailsModal" tabindex="-1" aria-labelledby="callDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callDetailsModalLabel">
                    <i class="bi bi-telephone-fill me-2"></i>Call Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-camera-video-fill fs-1 text-primary" id="callTypeIcon"></i>
                    </div>
                    <h6 id="callTypeText" class="text-muted">Video Call</h6>
                    <p class="text-muted mb-0" id="callOtherUserName">With User</p>
                    <p class="mb-0 mt-2">
                        <span id="callDirectionBadge" class="badge"></span>
                    </p>
                </div>
                
                <div class="row mb-3">
                    <div class="col-12 text-center">
                        <div class="p-3 bg-light rounded">
                            <i class="bi bi-clock-history fs-4 text-primary d-block mb-2"></i>
                            <small class="text-muted d-block">Duration</small>
                            <strong id="callDuration">--:--</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
let currentCallRoomId = null;
let currentCallType = null;

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
    
    // Auto-refresh disabled - removed to prevent page reload
    // callsRefreshInterval = setInterval(refreshCallsList, 5000);
    
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
    // Check for incoming calls every 2 seconds (but don't refresh the page)
    callCheckInterval = setInterval(checkIncomingCalls, 2000); // Check every 2 seconds for incoming calls
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
        // Debug log - check API response
        console.log('Incoming Calls API Response:', {
            count: data.count,
            calls: data.calls,
            firstCall: data.calls && data.calls.length > 0 ? data.calls[0] : null
        });
        
        // Check if currently displayed call is still valid
        if (currentCallRequestId) {
            const modalElement = document.getElementById('incomingCallModal');
            const isModalShown = modalElement && modalElement.classList.contains('show');
            
            if (isModalShown) {
                // Check if current call is still in the incoming calls list
                const currentCallStillExists = data.calls && data.calls.some(call => call.id === currentCallRequestId);
                
                if (!currentCallStillExists) {
                    // Current call is no longer in the list - it was cancelled/ended
                    console.log('Call was cancelled/ended by caller, closing modal');
                    if (incomingCallModal) {
                        incomingCallModal.hide();
                    }
                    currentCallRequestId = null;
                    return; // Don't show new call if current one was cancelled
                } else {
                    // Current call still exists, verify its status directly
                    checkCurrentCallStatus();
                }
            }
        }
        
        // Show modal for incoming calls automatically
        if (data.count > 0 && data.calls.length > 0) {
            const firstCall = data.calls[0];
            
            // Debug log - check call type in API response
            console.log('First Incoming Call:', {
                id: firstCall.id,
                room_id: firstCall.room_id,
                type: firstCall.type,
                roomIdStartsWithAudio: firstCall.room_id ? firstCall.room_id.startsWith('audio_') : false
            });
            
            // Show modal for first incoming call automatically
            const modalElement = document.getElementById('incomingCallModal');
            if (modalElement) {
                const isModalShown = modalElement.classList.contains('show');
                
                // Only show if it's a new call (different ID) or modal is not shown
                if (!isModalShown && incomingCallModal && firstCall) {
                    // If there's a different call, update it
                    if (currentCallRequestId && currentCallRequestId !== firstCall.id) {
                        // New call came in, update modal
                        showIncomingCallModal(firstCall);
                    } else if (!currentCallRequestId) {
                        // No current call, show new one
                        showIncomingCallModal(firstCall);
                    }
                }
            }
        }
    })
    .catch(error => {
        console.error('Error checking calls:', error);
    });
}

// Check current call status directly
function checkCurrentCallStatus() {
    if (!currentCallRequestId) return;
    
    // Use getCallDetails API to check call status
    fetch('{{ route("api.call.details") }}?call_id=' + encodeURIComponent(currentCallRequestId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // If call status is ended, rejected, or cancelled, close the modal
        if (data.success && data.call) {
            const status = data.call.status;
            if (status === 'ended' || status === 'rejected' || status === 'cancelled') {
                console.log('Call status changed to:', status, '- closing modal');
                if (incomingCallModal) {
                    incomingCallModal.hide();
                }
                currentCallRequestId = null;
            }
        } else if (!data.success) {
            // Call not found or error - close modal
            console.log('Call not found or error - closing modal');
            if (incomingCallModal) {
                incomingCallModal.hide();
            }
            currentCallRequestId = null;
        }
    })
    .catch(error => {
        console.error('Error checking call status:', error);
        // On error, don't close modal - might be network issue
    });
}

// Show incoming call modal
function showIncomingCallModal(call) {
    if (!call || !call.caller) {
        console.error('Invalid call data:', call);
        return;
    }
    
    currentCallRequestId = call.id;
    currentCallRoomId = call.room_id || null;
    
    // Update caller information
    const callerNameEl = document.getElementById('callerName');
    const callerEmailEl = document.getElementById('callerEmail');
    
    if (callerNameEl) {
        callerNameEl.textContent = call.caller.name + ' is calling...';
    }
    if (callerEmailEl) {
        callerEmailEl.textContent = call.caller.email || '';
    }
    
    // CRITICAL: ALWAYS check room_id FIRST - this is the source of truth
    // Room ID determines call type, NOT API response
    let callType = 'video'; // Default
    
    if (call.room_id) {
        // Check room_id prefix - this is the most reliable way
        if (call.room_id.startsWith('audio_')) {
            callType = 'audio';
        } else if (call.room_id.startsWith('group_')) {
            // For group calls, use API response type if available
            callType = (call.type || 'video').toLowerCase();
        } else {
            // Regular video calls (room_ prefix or no prefix)
            callType = 'video';
        }
    } else if (call.type) {
        // Fallback: use API response only if room_id not available
        callType = call.type.toLowerCase();
    }
    
    // Final verification - ALWAYS trust room_id for audio calls
    if (call.room_id && call.room_id.startsWith('audio_')) {
        callType = 'audio';
    }
    
    // Ensure callType is lowercase
    callType = callType.toLowerCase();
    
    // Store current call type for use in accept function
    currentCallType = callType;
    
    // Debug log
    console.log('Incoming Call Type:', {
        originalType: call.type,
        roomId: call.room_id,
        roomIdStartsWithAudio: call.room_id ? call.room_id.startsWith('audio_') : false,
        finalType: callType,
        willShowAudio: callType === 'audio',
        storedCallType: currentCallType
    });
    
    // Show call type (video or audio) - FORCE UPDATE
    const callTypeIcon = document.getElementById('callTypeIcon');
    const callTypeText = document.getElementById('callTypeText');
    
    // Final check - ALWAYS trust room_id
    if (call.room_id && String(call.room_id).trim().startsWith('audio_')) {
        callType = 'audio';
    }
    
    console.log('Setting Incoming Call Icon:', {
        callType: callType,
        roomId: call.room_id,
        willShowAudio: callType === 'audio'
    });
    
    if (callType === 'audio') {
        // Audio call - show telephone icon and "Audio Call" text
        if (callTypeIcon) {
            callTypeIcon.className = 'bi bi-telephone-fill fs-2';
            callTypeIcon.setAttribute('class', 'bi bi-telephone-fill fs-2');
        }
        if (callTypeText) {
            callTypeText.textContent = 'Audio Call';
            callTypeText.innerText = 'Audio Call';
            callTypeText.className = 'text-info fw-semibold mb-4';
        }
        console.log('✓ Incoming Call: Set to Audio Call (telephone icon)');
    } else {
        // Video call - show camera icon and "Video Call" text
        if (callTypeIcon) {
            callTypeIcon.className = 'bi bi-camera-video-fill fs-2';
            callTypeIcon.setAttribute('class', 'bi bi-camera-video-fill fs-2');
        }
        if (callTypeText) {
            callTypeText.textContent = 'Video Call';
            callTypeText.innerText = 'Video Call';
            callTypeText.className = 'text-success fw-semibold mb-4';
        }
        console.log('✓ Incoming Call: Set to Video Call (camera icon)');
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
            
            // Determine call type from room_id - this is the source of truth
            let callType = 'video'; // Default to video
            const roomId = data.room_id || currentCallRoomId;
            
            // Check room_id prefix to determine call type
            if (roomId) {
                if (roomId.startsWith('audio_')) {
                    callType = 'audio';
                } else {
                    callType = 'video';
                }
            } else if (currentCallType) {
                // Fallback: use stored call type
                callType = currentCallType;
            } else if (data.call_type) {
                // Fallback: use API response if available
                callType = data.call_type.toLowerCase();
            }
            
            // Debug log
            console.log('Accept Call Redirect:', {
                roomId: roomId,
                storedCallType: currentCallType,
                apiCallType: data.call_type,
                finalCallType: callType,
                willRedirectToAudio: callType === 'audio'
            });
            
            // Redirect to appropriate call page based on room_id
            if (callType === 'audio') {
                window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(roomId);
            } else {
                window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(roomId);
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
        
        // Reset current call request ID and related variables
        currentCallRequestId = null;
        currentCallRoomId = null;
        currentCallType = null;
        
        // Refresh calls list to show updated status
        refreshCallsList();
    })
    .catch(error => {
        console.error('Error rejecting call:', error);
        // Hide modal even on error
        if (incomingCallModal) {
            incomingCallModal.hide();
        }
        // Reset all call-related variables
        currentCallRequestId = null;
        currentCallRoomId = null;
        currentCallType = null;
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

// Handle group call click - show modal if inactive, join if active
function handleGroupCallClick(roomId, callType, isActive) {
    if (isActive) {
        // If call is active, join it
        joinGroupCall(roomId, callType);
    } else {
        // If call is inactive, show details modal
        showGroupCallDetails(roomId, callType);
    }
}

// Show group call details modal
function showGroupCallDetails(roomId, callType) {
    // Show loading state
    document.getElementById('groupCallDuration').textContent = 'Loading...';
    document.getElementById('groupCallWithText').textContent = 'Loading...';
    document.getElementById('groupCallParticipantsList').innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"></div></div>';
    
    // Determine call type from room_id - this is the source of truth
    // Check: audio call (audio_ prefix) or video call
    let detectedCallType = 'video'; // Default to video
    
    if (roomId) {
        const roomIdStr = String(roomId).trim();
        // Check if it's an audio call
        if (roomIdStr.startsWith('audio_')) {
            detectedCallType = 'audio';
        } else {
            // Video call (group_ prefix or room_ prefix)
            detectedCallType = 'video';
        }
    } else if (callType) {
        // Fallback: use parameter if room_id not available
        detectedCallType = String(callType).toLowerCase();
    }
    
    // Final check - ALWAYS trust room_id for audio calls
    if (roomId && String(roomId).trim().startsWith('audio_')) {
        detectedCallType = 'audio';
    }
    
    detectedCallType = detectedCallType.toLowerCase();
    
    // Set call type icon
    const iconElement = document.getElementById('groupCallTypeIcon');
    const typeText = document.getElementById('groupCallTypeText');
    const withText = document.getElementById('groupCallWithText');
    
    // Debug log
    console.log('Group Call Details:', {
        roomId: roomId,
        parameterCallType: callType,
        detectedCallType: detectedCallType,
        isAudio: detectedCallType === 'audio',
        isVideo: detectedCallType === 'video'
    });
    
    // Set icon and text based on detected call type
    if (detectedCallType === 'audio') {
        // Audio call - show telephone icon and "Audio Call" text
        iconElement.className = 'bi bi-telephone-fill fs-1 text-primary';
        typeText.textContent = 'Audio Call';
        console.log('✓ Group Call: Set to Audio Call (telephone icon)');
    } else {
        // Video call - show camera icon and "Video Call" text
        iconElement.className = 'bi bi-camera-video-fill fs-1 text-primary';
        typeText.textContent = 'Video Call';
        console.log('✓ Group Call: Set to Video Call (camera icon)');
    }
    
    // Fetch group call details
    fetch('{{ route("api.group.call.get") }}?room_id=' + encodeURIComponent(roomId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Determine call type from room_id - this is the source of truth
            // Check: audio call (audio_ prefix) or video call
            let finalCallType = 'video'; // Default to video
            
            if (data.group_call.room_id) {
                const roomIdStr = String(data.group_call.room_id).trim();
                // Check if it's an audio call
                if (roomIdStr.startsWith('audio_')) {
                    finalCallType = 'audio';
                } else {
                    // Video call (group_ prefix or room_ prefix)
                    finalCallType = 'video';
                }
            } else if (data.group_call.type) {
                // Fallback: use API response type if room_id not available
                finalCallType = String(data.group_call.type).toLowerCase();
            }
            
            // Final check - ALWAYS trust room_id for audio calls
            if (data.group_call.room_id && String(data.group_call.room_id).trim().startsWith('audio_')) {
                finalCallType = 'audio';
            }
            
            finalCallType = finalCallType.toLowerCase();
            
            // Debug log
            console.log('Group Call API Response:', {
                roomId: data.group_call.room_id,
                apiType: data.group_call.type,
                finalCallType: finalCallType,
                isAudio: finalCallType === 'audio',
                isVideo: finalCallType === 'video'
            });
            
            // Set icon and text based on final call type
            if (finalCallType === 'audio') {
                // Audio call - show telephone icon and "Audio Call" text
                iconElement.className = 'bi bi-telephone-fill fs-1 text-primary';
                typeText.textContent = 'Audio Call';
                console.log('✓ Group Call API: Set to Audio Call (telephone icon)');
            } else {
                // Video call - show camera icon and "Video Call" text
                iconElement.className = 'bi bi-camera-video-fill fs-1 text-primary';
                typeText.textContent = 'Video Call';
                console.log('✓ Group Call API: Set to Video Call (camera icon)');
            }
            
            // Set duration
            document.getElementById('groupCallDuration').textContent = data.group_call.duration_text || '0:00';
            
            // Set "With" text - show first participant name or "Group Call"
            if (data.participants && data.participants.length > 0) {
                const firstParticipant = data.participants[0];
                if (data.participants.length === 1) {
                    withText.textContent = 'With ' + firstParticipant.name;
                } else if (data.participants.length === 2) {
                    withText.textContent = 'With ' + firstParticipant.name + ' & ' + data.participants[1].name;
                } else {
                    withText.textContent = 'With ' + firstParticipant.name + ' & ' + (data.participants.length - 1) + ' others';
                }
            } else {
                withText.textContent = 'Group Call';
            }
            
            // Display participants
            const participantsList = document.getElementById('groupCallParticipantsList');
            if (data.participants.length > 0) {
                participantsList.innerHTML = data.participants.map(participant => {
                    const hasPic = participant.profile_picture;
                    const initial = participant.name.charAt(0).toUpperCase();
                    const statusBadge = participant.status === 'joined' 
                        ? '<span class="badge bg-primary ms-2">Joined</span>' 
                        : '<span class="badge bg-info ms-2">Left</span>';
                    
                    return `
                        <div class="d-flex align-items-center mb-3 p-2 border rounded">
                            <div class="flex-shrink-0 me-3">
                                ${hasPic 
                                    ? `<img src="${participant.profile_picture}" alt="${participant.name}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">`
                                    : `<div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><span style="font-size: 16px; font-weight: bold;">${initial}</span></div>`
                                }
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${participant.name} ${statusBadge}</div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                participantsList.innerHTML = '<p class="text-muted text-center">No participants found</p>';
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('groupCallDetailsModal'));
            modal.show();
        } else {
            alert(data.message || 'Failed to load group call details');
        }
    })
    .catch(error => {
        console.error('Error loading group call details:', error);
        alert('Error loading group call details');
    });
}

// Show individual call details
function showCallDetails(callId) {
    // Show loading state
    document.getElementById('callDuration').textContent = 'Loading...';
    
    // Fetch call details
    fetch('{{ route("api.call.details") }}?call_id=' + encodeURIComponent(callId), {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Set call type icon and text
            const iconElement = document.getElementById('callTypeIcon');
            const typeText = document.getElementById('callTypeText');
            const otherUserName = document.getElementById('callOtherUserName');
            const directionBadge = document.getElementById('callDirectionBadge');
            
            // Determine call type from room_id - this is the source of truth
            // Check: audio call (audio_ prefix) or video call (room_ prefix or no prefix)
            let callType = 'video'; // Default to video
            
            if (data.call.room_id) {
                const roomId = String(data.call.room_id).trim();
                // Check if it's an audio call
                if (roomId.startsWith('audio_')) {
                    callType = 'audio';
                } else {
                    // Video call (room_ prefix or group_ prefix)
                    callType = 'video';
                }
            } else if (data.call.type) {
                // Fallback: use API response if room_id not available
                callType = String(data.call.type).toLowerCase();
            }
            
            // Final check - ALWAYS trust room_id for audio calls
            if (data.call.room_id && String(data.call.room_id).trim().startsWith('audio_')) {
                callType = 'audio';
            }
            
            callType = callType.toLowerCase();
            
            // Debug log
            console.log('Individual Call Details:', {
                roomId: data.call.room_id,
                apiType: data.call.type,
                detectedCallType: callType,
                isAudio: callType === 'audio',
                isVideo: callType === 'video'
            });
            
            // Set icon and text based on call type
            if (callType === 'audio') {
                // Audio call - show telephone icon and "Audio Call" text
                iconElement.className = 'bi bi-telephone-fill fs-1 text-primary';
                typeText.textContent = 'Audio Call';
                console.log('✓ Set to Audio Call (telephone icon)');
            } else {
                // Video call - show camera icon and "Video Call" text
                iconElement.className = 'bi bi-camera-video-fill fs-1 text-primary';
                typeText.textContent = 'Video Call';
                console.log('✓ Set to Video Call (camera icon)');
            }
            
            otherUserName.textContent = 'With ' + data.other_user.name;
            
            // Set call direction badge
            if (data.call.is_outgoing) {
                directionBadge.className = 'badge bg-primary';
                directionBadge.textContent = 'Outgoing';
            } else {
                directionBadge.className = 'badge bg-info';
                directionBadge.textContent = 'Incoming';
            }
            
            // Set duration (current call only)
            document.getElementById('callDuration').textContent = data.call.duration_text || '0:00';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('callDetailsModal'));
            modal.show();
        } else {
            alert(data.message || 'Failed to load call details');
        }
    })
    .catch(error => {
        console.error('Error loading call details:', error);
        alert('Error loading call details');
    });
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

// Handle call icon click - determine call type from icon
function handleCallIconClick(userId, event) {
    // Get call type from the icon's data attribute or from the clicked element
    let callType = 'video'; // Default to video
    const clickedElement = event.target;
    
    // Check if clicked element or its parent has data-call-type attribute
    if (clickedElement.getAttribute('data-call-type')) {
        callType = clickedElement.getAttribute('data-call-type');
    } else if (clickedElement.closest('[data-call-type]')) {
        callType = clickedElement.closest('[data-call-type]').getAttribute('data-call-type');
    } else if (clickedElement.classList.contains('bi-telephone-fill')) {
        // If phone icon was clicked, it's audio call
        callType = 'audio';
    } else if (clickedElement.classList.contains('bi-camera-video-fill')) {
        // If video icon was clicked, it's video call
        callType = 'video';
    }
    
    // Normalize call type - ensure it's lowercase
    callType = String(callType || '').toLowerCase().trim();
    
    // Debug log
    console.log('handleCallIconClick:', {
        userId: userId,
        callType: callType,
        clickedElement: clickedElement,
        willInitiateAudio: callType === 'audio'
    });
    
    // Call the openCallWithUser function
    openCallWithUser(userId, callType);
}

// Open call with user (only from icon click)
function openCallWithUser(userId, callType) {
    // Normalize call type - ensure it's lowercase
    callType = String(callType || '').toLowerCase().trim();
    
    // Debug log
    console.log('openCallWithUser:', {
        userId: userId,
        callType: callType,
        willInitiateAudio: callType === 'audio'
    });
    
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
            console.log('Audio Call Initiate Response:', data);
            if (data.success) {
                // Verify room_id has audio_ prefix
                if (data.room_id && !data.room_id.startsWith('audio_')) {
                    console.error('ERROR: Audio call room_id does not start with audio_', data.room_id);
                }
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
            console.log('Video Call Initiate Response:', data);
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
        // Exit selection mode - Auto-refresh disabled
        // if (!callsRefreshInterval) {
        //     callsRefreshInterval = setInterval(refreshCallsList, 5000);
        // }
        
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
