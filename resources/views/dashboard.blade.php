@extends('layouts.app')

@section('title', 'Dashboard - Video Call App')

@section('content')
<style>
/* User profile icon with first letter - Force styling */
.users-list .rounded-circle[style*="linear-gradient"] {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    min-width: 45px !important;
    min-height: 45px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.users-list .rounded-circle[style*="linear-gradient"] span {
    color: #ffffff !important;
    font-weight: bold !important;
    font-size: 1.1rem !important;
    line-height: 1 !important;
    text-shadow: none !important;
}

/* Chat messages area scrollbar styling - Global */
#chatMessagesArea {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f0f0f0;
    height: 100% !important;
    max-height: calc(100vh - 200px) !important;
    position: relative !important;
}

/* Chat sidebar height fix */
.chat-sidebar {
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Welcome sidebar visibility control - Desktop */
@media (min-width: 768px) {
    .welcome-sidebar {
        display: flex !important;
    }
    
    .chat-sidebar[style*="display: flex"] ~ .welcome-sidebar,
    #chatSidebar[style*="display: flex"] ~ #welcomeSidebar {
        display: none !important;
    }
}

/* Webkit scrollbar styling (Chrome, Safari, Edge) */
#chatMessagesArea::-webkit-scrollbar {
    width: 8px;
}

#chatMessagesArea::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

#chatMessagesArea::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

#chatMessagesArea::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    /* Mobile: Full width container */
    .container-fluid.p-0 {
        height: calc(100vh - 60px) !important;
        overflow: hidden !important;
    }
    
    .users-sidebar {
        width: 100% !important;
        height: 100% !important;
        padding-bottom: 70px !important; /* Space for bottom nav */
        background: #f0f2f5 !important; /* WhatsApp background color */
        overflow-y: auto !important;
        display: block !important;
        position: relative !important;
    }
    
    .welcome-sidebar {
        display: none !important;
    }
    
    /* Mobile: Ensure users list is visible by default */
    .row.g-0.h-100 {
        height: 100% !important;
    }
    
    .col-lg-4.col-md-5.users-sidebar {
        display: block !important;
        width: 100% !important;
        position: relative !important;
        left: 0 !important;
    }
    .user-item {
        padding: 12px 16px !important;
        background: white !important;
        border-bottom: 1px solid #e9edef !important;
        margin-bottom: 1px !important;
    }
    .user-item:hover,
    .user-item:active {
        background: #f5f6f6 !important;
    }
    
    /* Show call button in user list on mobile (for direct call) */
    .user-item .btn-primary {
        display: flex !important;
    }
    
    /* Bottom navigation styles */
    .fixed-bottom {
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1) !important;
        background: white !important;
    }
    
    /* WhatsApp style search bar */
    .input-group {
        background: #f0f2f5 !important;
        border-radius: 20px !important;
    }
    
    .input-group:focus-within {
        background: white !important;
        box-shadow: 0 0 0 1px #25d366;
    }
    
    /* Header background */
    .bg-white.border-bottom:first-of-type {
        /* background: #008069 !important; */
        border-bottom: none !important;
    }
    
    /* Header text color */
    .bg-white.border-bottom:first-of-type h6,
    .bg-white.border-bottom:first-of-type small {
        /* color: white !important; */
    }
    
    /* Header buttons */
    .bg-white.border-bottom:first-of-type .btn {
        /* background: rgba(255,255,255,0.1) !important; */
        border-color: rgba(255,255,255,0.2) !important;
        color: white !important;
    }
    
    /* Mobile: Dropdown menu fix */
    .dropdown-menu {
        z-index: 9999 !important;
        position: absolute !important;
    }
    
    /* Search bar container - Always visible, below header */
    /* .users-sidebar > .p-2.p-md-3.bg-white.border-bottom:nth-of-type(2),
    .users-sidebar > div:nth-of-type(2) {
        background: #008069 !important;
        border-bottom: none !important;
        padding: 10px 12px !important;
        position: sticky !important;
        top: 56px !important;
        z-index: 100 !important;
        display: block !important;
    } */
    
    /* Search input styling */
    #searchUsers {
        background: transparent !important;
        border: none !important;
        color: #54656f !important;
    }
    
    #searchUsers::placeholder {
        color: #8696a0 !important;
    }
    
    #searchUsers:focus {
        outline: none !important;
        box-shadow: none !important;
        color: #111b21 !important;
    }
    
    /* Ensure search bar is visible */
    .input-group {
        display: flex !important;
        visibility: visible !important;
    }
    
    /* Users list styling */
    .users-list {
        padding: 0 !important;
        background: #f0f2f5 !important;
    }
    
    /* Mobile: Hide header (user profile section) */
    .users-sidebar > .bg-white.border-bottom:first-of-type {
        display: none !important;
    }
    
    /* Mobile: Ensure search bar is visible and at top */
    /* .users-sidebar > .p-2.p-md-3.bg-white.border-bottom {
        position: sticky !important;
        top: 0 !important;
        z-index: 9 !important;
        display: block !important;
        background: #008069 !important;
        margin-top: 0 !important;
    } */
    
    /* Mobile: User items styling */
    .user-item {
        background: white !important;
        margin: 1px 0 !important;
    }
    
    /* Mobile: Show call button always */
    .user-item .btn-primary {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* Mobile: Chat sidebar full screen */
    .chat-sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100vh !important;
        z-index: 2000 !important;
        display: none !important;
    }
    
    .chat-sidebar.show,
    .chat-sidebar.d-md-flex {
        display: flex !important;
    }
    
    /* Mobile: Ensure users sidebar is visible when Chats button clicked */
    .users-sidebar {
        display: block !important;
        z-index: 1 !important;
    }
    
    /* Mobile: Show chat sidebar when active */
    @media (max-width: 768px) {
        .chat-sidebar.d-md-flex {
            display: flex !important;
        }
    }
    
    /* Mobile: Chat messages styling */
    .message-bubble {
        max-width: 75% !important;
        word-wrap: break-word;
    }
    
    .message-bubble.sent {
        background: #dcf8c6 !important;
        margin-left: auto;
    }
    
    .message-bubble.received {
        background: white !important;
    }
    
    /* Mobile: Chat input area fixed at bottom */
    .chat-sidebar #chatInputArea {
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 2001 !important;
        background: white !important;
        border-top: 1px solid #e9edef !important;
        padding: 10px 12px !important;
        padding-bottom: calc(10px + env(safe-area-inset-bottom)) !important;
    }
    
    /* Mobile: Chat messages area padding for input */
    .chat-sidebar #chatMessagesArea {
        padding-bottom: 80px !important;
        max-height: calc(100vh - 150px) !important;
        height: auto !important;
        overflow-y: auto !important;
    }
    
    /* Mobile: Chat sidebar height */
    .chat-sidebar {
        height: 100vh !important;
        max-height: 100vh !important;
    }
    
    /* Emoji picker styling */
    #emojiPickerContainer {
        border: 1px solid #e0e0e0;
    }
    
    #emojiPicker button:hover {
        background: #f0f0f0 !important;
        border-radius: 8px;
    }
    
    /* Mobile emoji picker positioning */
    #emojiPickerContainer {
        max-width: 100% !important;
        left: 0 !important;
        right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    /* Mobile location modal styling */
    #locationOptionsModal .modal-dialog {
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    #locationOptionsModal .modal-content {
        width: 90% !important;
        max-width: 400px !important;
        border-radius: 12px !important;
        margin: auto !important;
    }
    
    #locationOptionsModal .modal-backdrop {
        z-index: 9999 !important;
    }
    
    #locationOptionsModal {
        z-index: 10000 !important;
    }
}
</style>
<div class="container-fluid p-0" style="height: calc(100vh - 60px);">
    <div class="row g-0 h-100">
        <!-- Left Sidebar - Users List (WhatsApp Style) -->
        <div class="col-lg-4 col-md-5 border-end bg-light users-sidebar" style="height: 100%; overflow-y: auto;">
            <!-- Header - Mobile Responsive (WhatsApp Style) -->
            <div class="bg-white border-bottom p-2 p-md-3 sticky-top" style="background: #008069 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-grow-1 min-w-0">
                        <h6 class="mb-0 fw-bold text-white" style="font-size: 1.1rem;">Chats</h6>
                    </div>
                    <div class="d-flex gap-1 gap-md-2 flex-shrink-0">
                        <!-- Camera Icon -->
                        <button class="btn btn-sm rounded-circle" 
                                style="width: 35px; height: 35px; min-width: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                                onclick="openCamera()" title="Camera">
                            <i class="bi bi-camera-fill" style="font-size: 0.9rem;"></i>
                        </button>
                        <!-- Scanner/QR Icon -->
                        <button class="btn btn-sm rounded-circle" 
                                style="width: 35px; height: 35px; min-width: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                                onclick="openScanner()" title="QR Scanner">
                            <i class="bi bi-qr-code-scan" style="font-size: 0.9rem;"></i>
                        </button>
                        <!-- 3 Dots Menu -->
                        <div class="dropdown">
                            <button class="btn btn-sm rounded-circle" 
                                    type="button" 
                                    id="headerMenuDropdown" 
                                    data-bs-toggle="dropdown" 
                                    data-bs-auto-close="true"
                                    aria-expanded="false"
                                    style="width: 35px; height: 35px; min-width: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                                <i class="bi bi-three-dots-vertical" style="font-size: 0.9rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="headerMenuDropdown" style="min-width: 200px; z-index: 9999 !important; position: absolute !important;">
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('headerMenuDropdown')); if(dropdown) dropdown.hide(); openProfileModal();">
                                        <i class="bi bi-person-circle me-2"></i>Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline w-100 m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger w-100 text-start border-0 bg-transparent" style="padding: 0.5rem 1rem;">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Bar - Mobile Responsive (WhatsApp Style) -->
            <div class="p-2 p-md-3 bg-white border-bottom">
                <div class="position-relative">
                    <div class="input-group" style="background: #f0f2f5; border-radius: 20px; overflow: hidden; display: flex !important;">
                        <span class="input-group-text bg-transparent border-0" style="padding: 0.5rem 0.75rem 0.5rem 1rem;">
                            <i class="bi bi-search text-muted" style="font-size: 1rem;"></i>
                        </span>
                        <input type="text" class="form-control border-0 bg-transparent" id="searchUsers" 
                               placeholder="Search or start new call" 
                               autocomplete="off"
                               style="font-size: 0.9rem; padding: 0.5rem 0.75rem; color: #54656f; flex: 1;">
                        <button class="btn bg-transparent border-0" type="button" id="clearSearch" style="display: none; padding: 0.5rem 0.75rem 0.5rem 0;">
                            <i class="bi bi-x-circle text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="users-list" id="usersList">
                @if($users->count() > 0)
                    @foreach($users as $user)
                        <div class="user-item border-bottom bg-white p-2 p-md-3 cursor-pointer user-card" 
                             data-user-id="{{ $user->id }}"
                             data-user-name="{{ strtolower($user->name) }}"
                             data-user-email="{{ strtolower($user->email) }}"
                             style="cursor: pointer; transition: background 0.2s;"
                             onclick="openChatWithUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->profile_picture_url ?? '' }}')"
                             onmouseover="this.style.background='#f8f9fa'" 
                             onmouseout="this.style.background='white'"
                             ontouchstart="this.style.background='#f8f9fa'"
                             ontouchend="this.style.background='white'">
                            <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center flex-grow-1 min-w-0">
                                    @php
                                        // Check if user has profile picture file
                                        $hasProfilePic = false;
                                        if ($user->profile_picture && trim($user->profile_picture) != '') {
                                            $picPath = public_path('storage/profiles/' . $user->profile_picture);
                                            $hasProfilePic = file_exists($picPath) && is_file($picPath);
                                        }
                                    @endphp
                                    
                                    @if($hasProfilePic)
                                        {{-- USER HAS PROFILE PICTURE - SHOW IMAGE --}}
                                        <img src="{{ asset('storage/profiles/' . $user->profile_picture) }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle me-2 flex-shrink-0" 
                                             style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #667eea;">
                                    @else
                                        {{-- USER DOES NOT HAVE PROFILE PICTURE - SHOW FIRST LETTER --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" 
                                             style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; min-width: 45px; min-height: 45px;">
                                            <span class="fw-bold text-white" style="font-size: 1.1rem; color: #ffffff !important;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <h6 class="mb-1 fw-semibold text-truncate" style="font-size: 0.95rem;">{{ $user->name }}</h6>
                                        <small class="text-muted d-block text-truncate" style="font-size: 0.8rem; color: #6c757d !important;">
                                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                        </small>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ms-2 position-relative">
                                    @php
                                        $unreadCount = isset($unreadCounts[$user->id]) ? $unreadCounts[$user->id] : 0;
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="badge bg-primary rounded-pill d-flex align-items-center justify-content-center" 
                                              style="min-width: 22px; height: 22px; font-size: 0.7rem; font-weight: 600; padding: 0 6px;">
                                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                        <p class="mb-0">No other users found</p>
                        <small>Share the app with friends to start video calling!</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side - Chat View / Welcome State -->
        <div class="col-lg-8 col-md-7 d-none d-md-flex flex-column bg-white chat-sidebar" id="chatSidebar" style="display: none !important; height: 100%;">
            <!-- Chat Header -->
            <div class="bg-white border-bottom p-3 d-flex align-items-center justify-content-between sticky-top" style="background: #008069 !important; z-index: 10;">
                <div class="d-flex align-items-center flex-grow-1 min-w-0">
                    <button class="btn btn-sm text-white me-2 d-md-none" onclick="goToDashboard()" style="background: transparent; border: none; padding: 0.25rem 0.5rem;">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </button>
                    <div id="chatUserInfo" class="d-flex align-items-center flex-grow-1 min-w-0">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-2 flex-shrink-0" 
                             style="width: 40px; height: 40px; background: rgba(255,255,255,0.2) !important;">
                            <span class="fw-bold text-white" style="font-size: 0.9rem;" id="chatUserInitial">U</span>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <h6 class="mb-0 fw-bold text-white text-truncate" id="chatUserName">Select a user</h6>
                            <small class="text-white-50" style="font-size: 0.75rem; opacity: 0.9;" id="chatUserStatus">Online</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-shrink-0" id="chatHeaderActions">
                    <button class="btn btn-sm rounded-circle" onclick="initiateVideoCallFromChat()" 
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                            title="Video Call" id="videoCallBtn">
                        <i class="bi bi-camera-video-fill"></i>
                    </button>
                    <button class="btn btn-sm rounded-circle" onclick="initiateAudioCallFromChat()" 
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                            title="Audio Call" id="audioCallBtn">
                        <i class="bi bi-telephone-fill"></i>
                    </button>
                    <button class="btn btn-sm rounded-circle" onclick="toggleMessageSelectionMode()" 
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                            title="Select Messages" id="selectMessagesBtn">
                        <i class="bi bi-check-square"></i>
                    </button>
                    <button class="btn btn-sm rounded-circle d-none" onclick="deleteSelectedMessages()" 
                            style="width: 35px; height: 35px; background: rgba(220,53,69,0.8); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                            title="Delete Selected" id="deleteMessagesBtn">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-sm rounded-circle" onclick="openChatMenu()" 
                            style="width: 35px; height: 35px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" 
                            title="More" id="chatMenuBtn">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                </div>
            </div>
            
            <!-- Chat Messages Area -->
            <div class="flex-grow-1 p-3" id="chatMessagesArea" style="background: #efeae2; background-image: url('data:image/svg+xml,%3Csvg width=\"100\" height=\"100\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cdefs%3E%3Cpattern id=\"grid\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"%3E%3Cpath d=\"M 100 0 L 0 0 0 100\" fill=\"none\" stroke=\"%23e5ddd5\" stroke-width=\"1\" opacity=\"0.3\"/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=\"100\" height=\"100\" fill=\"url(%23grid)\" /%3E%3C/svg%3E'); overflow-y: auto; overflow-x: hidden; height: 100%; min-height: 0;">
                <div id="chatMessages" class="d-flex flex-column gap-2" style="min-height: 100%;">
                    <!-- Messages will be loaded here -->
                    <div class="text-center text-muted py-5" id="noMessages">
                        <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                        <p class="mb-0">No messages yet</p>
                        <small>Start a conversation!</small>
                    </div>
                </div>
            </div>
            
            <!-- Chat Input Area -->
            <div class="bg-white border-top p-2" id="chatInputArea" style="display: none; position: relative;">
                <!-- Media Preview Area (Hidden by default) -->
                <div id="mediaPreviewArea" class="d-none mb-2" style="position: relative; border-radius: 8px; overflow: visible !important; background: #f0f2f5; padding: 8px;">
                    <button type="button" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center position-absolute" 
                            onclick="clearMediaPreview()" 
                            style="z-index: 1050 !important; top: 8px !important; right: 8px !important; width: 40px !important; height: 40px !important; padding: 0 !important; line-height: 1 !important; border: 3px solid white !important; box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important; background-color: #dc3545 !important; display: flex !important;">
                        <i class="bi bi-x-lg" style="font-size: 1.2rem !important; font-weight: bold !important; color: white !important; line-height: 1;"></i>
                    </button>
                    <div id="mediaPreviewContent" style="position: relative; z-index: 1;"></div>
                    <input type="text" class="form-control mt-2" id="mediaCaptionInput" 
                           placeholder="Add a caption..." 
                           style="background: white; border: 1px solid #e0e0e0; border-radius: 6px; padding: 0.5rem;">
                </div>
                
                <!-- Attachment Menu (Hidden by default) -->
                <div id="attachmentMenu" class="d-none" style="position: absolute; bottom: 100%; left: 0; margin-bottom: 8px; z-index: 1001; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 16px; width: 300px;">
                    <div class="row g-3">
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('document')" 
                                    style="width: 60px; height: 60px; border: none; background: #e3f2fd;">
                                <i class="bi bi-file-earmark-text fs-4" style="color: #2196f3;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Document</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('camera')" 
                                    style="width: 60px; height: 60px; border: none; background: #ffebee;">
                                <i class="bi bi-camera-fill fs-4" style="color: #f44336;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Camera</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('gallery')" 
                                    style="width: 60px; height: 60px; border: none; background: #f3e5f5;">
                                <i class="bi bi-images fs-4" style="color: #9c27b0;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Gallery</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('audio')" 
                                    style="width: 60px; height: 60px; border: none; background: #fff3e0;">
                                <i class="bi bi-headphones fs-4" style="color: #ff9800;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Audio</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('video')" 
                                    style="width: 60px; height: 60px; border: none; background: #e8f5e9;">
                                <i class="bi bi-camera-video-fill fs-4" style="color: #4caf50;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Video</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('location')" 
                                    style="width: 60px; height: 60px; border: none; background: #e0f2f1;">
                                <i class="bi bi-geo-alt-fill fs-4" style="color: #009688;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Location</small>
                        </div>
                        <div class="col-4 text-center">
                            <button class="btn btn-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" 
                                    onclick="openFileInput('contact')" 
                                    style="width: 60px; height: 60px; border: none; background: #fff9e6;">
                                <i class="bi bi-person-vcard-fill fs-4" style="color: #ffc107;"></i>
                            </button>
                            <small class="d-block" style="font-size: 0.75rem; color: #54656f;">Contact</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm rounded-circle" id="attachmentBtn" onclick="toggleAttachmentMenu(event)" 
                            style="width: 40px; height: 40px; background: #f0f2f5; border: none; color: #54656f;">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <button class="btn btn-sm rounded-circle" id="emojiPickerBtn" onclick="toggleEmojiPicker(event)" 
                            style="width: 40px; height: 40px; background: #f0f2f5; border: none; color: #54656f;">
                        <i class="bi bi-emoji-smile"></i>
                    </button>
                    <input type="text" class="form-control rounded-pill border-0" id="chatMessageInput" 
                           placeholder="Type a message" 
                           style="background: #f0f2f5; padding: 0.5rem 1rem;"
                           onkeypress="handleChatKeyPress(event)">
                    <button class="btn btn-sm rounded-circle" id="sendButton" onclick="sendChatMessage()" 
                            style="width: 40px; height: 40px; background: #008069; border: none; color: white;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                
                <!-- Hidden File Inputs -->
                <input type="file" id="fileInputDocument" accept=".pdf,.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx" style="display: none;" onchange="handleFileSelect(event, 'file')">
                <input type="file" id="fileInputCamera" accept="image/*" capture="environment" style="display: none;" onchange="handleFileSelect(event, 'image')">
                <input type="file" id="fileInputGallery" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/heic,image/heif" style="display: none;" onchange="handleFileSelect(event, 'image')">
                <input type="file" id="fileInputAudio" accept="audio/*" style="display: none;" onchange="handleFileSelect(event, 'audio')">
                <input type="file" id="fileInputVideo" accept="video/*" style="display: none;" onchange="handleFileSelect(event, 'video')">
                
                <!-- Emoji Picker Container -->
                <div id="emojiPickerContainer" style="display: none; position: absolute; bottom: 100%; left: 0; margin-bottom: 8px; z-index: 1000; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 12px; max-width: 320px; max-height: 300px; overflow-y: auto;">
                    <div id="emojiPicker" style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px;">
                        <!-- Emojis will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Welcome/Empty State (Desktop) -->
        <div class="col-lg-8 col-md-7 d-none d-md-flex align-items-center justify-content-center bg-white welcome-sidebar" id="welcomeSidebar" style="display: flex !important;">
            <div class="text-center p-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-gradient text-white mb-3" 
                         style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-camera-video fs-1"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-3">Welcome to Video Call App</h3>
                <p class="text-muted mb-4">Select a user from the list to start chatting or video calling</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <div class="text-center">
                        <i class="bi bi-chat-dots-fill fs-2 text-primary d-block mb-2"></i>
                        <small class="text-muted">Chat</small>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-camera-video-fill fs-2 text-success d-block mb-2"></i>
                        <small class="text-muted">Video Call</small>
                    </div>
                    <div class="text-center">
                        <i class="bi bi-telephone-fill fs-2 text-info d-block mb-2"></i>
                        <small class="text-muted">Audio Call</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation Bar (WhatsApp Style) -->
<div class="d-md-none fixed-bottom bg-white border-top shadow-lg" style="z-index: 1050; padding-bottom: env(safe-area-inset-bottom);">
    <div class="container-fluid px-0">
        <div class="row g-0">
            <!-- Users List Button -->
            <div class="col-4 text-center py-2 position-relative" onclick="showUsersList()" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center position-relative" style="width: 100%;">
                    <div class="position-relative d-inline-block">
                        <i class="bi bi-chat-square-fill fs-5 text-primary mb-1" id="usersNavIcon"></i>
                        <!-- Unread Count Badge -->
                        <span class="badge bg-danger rounded-pill position-absolute d-flex align-items-center justify-content-center" 
                              id="footerUnreadBadge" 
                              style="display: none; min-width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700; padding: 0 5px; top: -8px; right: -8px; z-index: 10; color: white !important; background-color: #dc3545 !important; border: 2px solid white !important; box-shadow: 0 2px 6px rgba(0,0,0,0.3) !important; line-height: 1.2;">
                        </span>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;" id="usersNavText">Chats</small>
                </div>
            </div>
            
            <!-- Calls Button (Floating Action Button Style) -->
            <div class="col-4 text-center py-2 position-relative" onclick="goToCalls()" style="cursor: pointer;">
                <button class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-lg" 
                        style="width: 56px; height: 56px; margin-top: -20px; border: 4px solid white;">
                    <i class="bi bi-telephone-fill fs-4 text-white"></i>
                </button>
                <small class="d-block mt-2 text-muted" style="font-size: 0.7rem;">Calls</small>
            </div>
            
            <!-- Profile Button -->
            <div class="col-4 text-center py-2" onclick="openProfileModal()" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center">
                    <i class="bi bi-person-circle fs-5 text-primary mb-1"></i>
                    <small class="text-primary" style="font-size: 0.7rem;">Profile</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile User Selection Modal for Call -->
<div class="modal fade" id="mobileCallModal" tabindex="-1" aria-labelledby="mobileCallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mobileCallModalLabel">
                    <i class="bi bi-camera-video-fill me-2 text-primary"></i>Select User to Call
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    @if($users->count() > 0)
                        @foreach($users as $user)
                            <div class="list-group-item list-group-item-action p-3" 
                                 onclick="initiateCall({{ $user->id }}, '{{ $user->name }}'); bootstrap.Modal.getInstance(document.getElementById('mobileCallModal')).hide();"
                                 style="cursor: pointer;">
                                <div class="d-flex align-items-center">
                                    @php
                                        // Check if user has profile picture file
                                        $hasProfilePic2 = false;
                                        if ($user->profile_picture && trim($user->profile_picture) != '') {
                                            $picPath2 = public_path('storage/profiles/' . $user->profile_picture);
                                            $hasProfilePic2 = file_exists($picPath2) && is_file($picPath2);
                                        }
                                    @endphp
                                    
                                    @if($hasProfilePic2)
                                        {{-- USER HAS PROFILE PICTURE - SHOW IMAGE --}}
                                        <img src="{{ asset('storage/profiles/' . $user->profile_picture) }}" 
                                             alt="{{ $user->name }}" 
                                             class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #667eea;">
                                    @else
                                        {{-- USER DOES NOT HAVE PROFILE PICTURE - SHOW FIRST LETTER --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; min-width: 50px; min-height: 50px;">
                                            <span class="fw-bold text-white" style="font-size: 1.2rem; color: #ffffff !important;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $user->name }}</h6>
                                        <small class="text-muted">
                                            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                                        </small>
                                    </div>
                                    <i class="bi bi-camera-video-fill text-primary fs-5"></i>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="list-group-item text-center p-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-3"></i>
                            <p class="mb-0">No users found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Edit Modal -->
<div class="modal fade" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileEditModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="profileEditForm" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            @php
                                // Check if user has profile picture file
                                $hasAuthProfilePic = false;
                                if (Auth::user()->profile_picture && trim(Auth::user()->profile_picture) != '') {
                                    $authPicPath = public_path('storage/profiles/' . Auth::user()->profile_picture);
                                    $hasAuthProfilePic = file_exists($authPicPath) && is_file($authPicPath);
                                }
                            @endphp
                            
                            @if($hasAuthProfilePic)
                                {{-- USER HAS PROFILE PICTURE - SHOW IMAGE --}}
                                <img src="{{ asset('storage/profiles/' . Auth::user()->profile_picture) }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     id="profilePreview" 
                                     class="rounded-circle" 
                                     style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #667eea;">
                            @else
                                {{-- USER DOES NOT HAVE PROFILE PICTURE - SHOW FIRST LETTER --}}
                                <div class="rounded-circle bg-gradient d-flex align-items-center justify-content-center text-white" 
                                     id="profilePreviewInitial" 
                                     style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; margin: 0 auto;">
                                    <span class="fw-bold text-white" style="font-size: 2rem; color: #ffffff !important;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                                <img src="" id="profilePreview" 
                                     class="rounded-circle" 
                                     style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #667eea; display: none;">
                            @endif
                            <label for="profilePictureInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                   style="width: 35px; height: 35px; cursor: pointer; border: 2px solid white;">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" id="profilePictureInput" name="profile_picture" accept="image/*" style="display: none;" onchange="previewProfilePicture(this)">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="profileName" class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control" id="profileName" name="name" 
                               value="{{ Auth::user()->name }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control" value="{{ Auth::user()->email }}" disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Camera Modal -->
<div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #000;">
            <div class="modal-header border-0" style="background: rgba(0,0,0,0.8);">
                <h5 class="modal-title text-white" id="cameraModalLabel">
                    <i class="bi bi-camera-fill me-2"></i>Camera
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="closeCamera()"></button>
            </div>
            <div class="modal-body p-0 text-center" style="background: #000;">
                <video id="cameraVideo" autoplay playsinline style="width: 100%; max-height: 70vh; object-fit: contain; display: none;"></video>
                <canvas id="cameraCanvas" style="display: none;"></canvas>
                <div id="cameraError" class="text-white p-5" style="display: none;">
                    <i class="bi bi-camera-video-off fs-1 d-block mb-3"></i>
                    <p id="cameraErrorMessage">Camera access denied or not available.</p>
                </div>
                <div id="cameraLoading" class="text-white p-5">
                    <div class="spinner-border text-light mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Accessing camera...</p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center" style="background: rgba(0,0,0,0.8);">
                <button type="button" class="btn btn-secondary rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 60px; height: 60px;" onclick="closeCamera()" title="Close">
                    <i class="bi bi-x-lg fs-4"></i>
                </button>
                <button type="button" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" 
                        id="capturePhotoBtn" style="width: 70px; height: 70px; display: none;" onclick="capturePhoto()" title="Capture Photo">
                    <i class="bi bi-camera-fill fs-3 text-dark"></i>
                </button>
                <button type="button" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center" 
                        id="switchCameraBtn" style="width: 60px; height: 60px; display: none;" onclick="switchCamera()" title="Switch Camera">
                    <i class="bi bi-arrow-repeat fs-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Location Options Modal -->
<div class="modal fade" id="locationOptionsModal" tabindex="-1" aria-labelledby="locationOptionsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10000 !important;">
    <div class="modal-dialog modal-dialog-centered" style="margin: 0 !important; max-width: 100% !important; width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important;">
        <div class="modal-content" style="width: 90% !important; max-width: 400px !important; border-radius: 12px !important; margin: auto !important;">
            <div class="modal-header border-0 pb-0" style="padding: 20px 20px 10px 20px !important;">
                <h5 class="modal-title" id="locationOptionsModalLabel" style="font-size: 1.1rem !important; font-weight: 600 !important;">
                    <i class="bi bi-geo-alt-fill me-2 text-primary"></i>Share Location
                </h5>
                <button type="button" id="closeLocationModalBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 1.2rem !important; pointer-events: auto !important; touch-action: manipulation; -webkit-tap-highlight-color: transparent;"></button>
            </div>
            <div class="modal-body p-4" style="padding: 20px !important;">
                <div class="d-flex flex-column gap-3">
                    <!-- Current Location Option -->
                    <button type="button" class="btn btn-outline-primary d-flex align-items-center p-3 rounded-3" 
                            onclick="shareCurrentLocation()" 
                            style="border: 2px solid #008069 !important; background: white !important; text-align: left !important; width: 100% !important; min-height: 70px !important; transition: all 0.2s;">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                             style="width: 50px !important; height: 50px !important; background: #008069 !important;">
                            <i class="bi bi-geo-alt-fill text-white" style="font-size: 1.5rem !important;"></i>
                        </div>
                        <div class="flex-grow-1 text-start">
                            <div class="fw-bold" style="color: #111b21 !important; font-size: 1rem !important;">Share Current Location</div>
                            <small class="text-muted" style="font-size: 0.85rem !important;">Share your current location</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted flex-shrink-0" style="font-size: 1.2rem !important;"></i>
                    </button>
                    
                    <!-- Live Location Option -->
                    <button type="button" class="btn btn-outline-success d-flex align-items-center p-3 rounded-3" 
                            onclick="shareLiveLocation()" 
                            style="border: 2px solid #25d366 !important; background: white !important; text-align: left !important; width: 100% !important; min-height: 70px !important; transition: all 0.2s;">
                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-3 flex-shrink-0" 
                             style="width: 50px !important; height: 50px !important; background: #25d366 !important;">
                            <i class="bi bi-broadcast-pin text-white" style="font-size: 1.5rem !important;"></i>
                        </div>
                        <div class="flex-grow-1 text-start">
                            <div class="fw-bold" style="color: #111b21 !important; font-size: 1rem !important;">Share Live Location</div>
                            <small class="text-muted" style="font-size: 0.85rem !important;">Share your real-time location</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted flex-shrink-0" style="font-size: 1.2rem !important;"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Selector Modal -->
<div class="modal fade" id="contactSelectorModal" tabindex="-1" aria-labelledby="contactSelectorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10000 !important;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="margin: 0 !important; max-width: 100% !important; width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important;">
        <div class="modal-content" style="width: 90% !important; max-width: 400px !important; border-radius: 12px !important; margin: auto !important; max-height: 80vh;">
            <div class="modal-header border-0 pb-0" style="padding: 20px 20px 10px 20px !important;">
                <h5 class="modal-title" id="contactSelectorModalLabel" style="font-size: 1.1rem !important; font-weight: 600 !important;">
                    <i class="bi bi-person-vcard-fill me-2 text-warning"></i>Share Contact
                </h5>
                <button type="button" id="closeContactModalBtn" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 1.2rem !important; pointer-events: auto !important; touch-action: manipulation; -webkit-tap-highlight-color: transparent;"></button>
            </div>
            <div class="modal-body p-3" style="padding: 15px !important; max-height: calc(80vh - 100px); overflow-y: auto;">
                <div id="contactSelectorList" class="list-group list-group-flush">
                    <!-- Contacts will be loaded here -->
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

<style>
.cursor-pointer {
    cursor: pointer;
}

.user-card:hover {
    background-color: #f8f9fa !important;
}

.min-w-0 {
    min-width: 0;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 768px) {
    .col-md-5 {
        position: fixed;
        left: -100%;
        width: 100% !important;
        z-index: 1000;
        transition: left 0.3s;
    }
    
    .col-md-5.show {
        left: 0;
    }
}
</style>

<script>
// Global variables for call management
let currentCallRequestId = null;
let callCheckInterval = null;
let incomingCallModal = null;

// Initialize call notification system
document.addEventListener('DOMContentLoaded', function() {
    // Stop any active live location sessions (in case of page refresh)
    stopLiveLocation();
    
    // Initialize Bootstrap modals
    incomingCallModal = new bootstrap.Modal(document.getElementById('incomingCallModal'));
    window.profileEditModal = new bootstrap.Modal(document.getElementById('profileEditModal'));
    
    // Initialize emoji picker
    initEmojiPicker();
    
    // Mobile: Ensure users sidebar is visible by default
    if (window.innerWidth <= 768) {
        const usersSidebar = document.querySelector('.users-sidebar');
        if (usersSidebar) {
            usersSidebar.style.display = 'block';
            usersSidebar.style.width = '100%';
            usersSidebar.style.position = 'relative';
            usersSidebar.style.left = '0';
        }
        
        // Hide welcome sidebar on mobile
        const welcomeSidebar = document.querySelector('.welcome-sidebar');
        if (welcomeSidebar) {
            welcomeSidebar.style.display = 'none';
        }
    }
    
    // Initialize page state - Ensure welcome screen shows on refresh
    initializePageState();
    
    // Start checking for incoming calls
    startCallChecking();
    
    // Calculate initial total unread count from server data
    @php
        $totalUnreadCount = 0;
        if (isset($unreadCounts)) {
            $totalUnreadCount = array_sum($unreadCounts);
        }
    @endphp
    const initialTotalUnread = {{ $totalUnreadCount }};
    const footerBadge = document.getElementById('footerUnreadBadge');
    if (footerBadge) {
        if (initialTotalUnread > 0) {
            footerBadge.textContent = initialTotalUnread > 99 ? '99+' : initialTotalUnread;
            footerBadge.style.setProperty('display', 'block', 'important');
        } else {
            // Hide badge completely if no unread messages
            footerBadge.style.setProperty('display', 'none', 'important');
            footerBadge.textContent = '';
        }
    }
    
    // Update unread counts on page load
    updateUnreadCounts();
    
    // Update unread counts periodically
    setInterval(updateUnreadCounts, 2000); // Every 2 seconds for faster updates
    
    // Profile form submission
    document.getElementById('profileEditForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (callCheckInterval) {
            clearInterval(callCheckInterval);
        }
    });
});

// Open profile edit modal
function openProfileModal() {
    if (window.profileEditModal) {
        window.profileEditModal.show();
    }
}

// Preview profile picture
function previewProfilePicture(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profilePreview');
            const previewInitial = document.getElementById('profilePreviewInitial');
            
            // Show image preview and hide initial letter
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            
            if (previewInitial) {
                previewInitial.style.display = 'none';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update profile
function updateProfile() {
    const form = document.getElementById('profileEditForm');
    const formData = new FormData(form);
    
    fetch('{{ route("api.profile.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            if (window.profileEditModal) {
                window.profileEditModal.hide();
            }
            // Reload page to show updated profile
            window.location.reload();
        } else {
            alert('Error updating profile');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating profile. Please try again.');
    });
}

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
        // Debug log - check API response
        console.log('Incoming Calls API Response:', {
            count: data.count,
            calls: data.calls,
            firstCall: data.calls && data.calls.length > 0 ? data.calls[0] : null
        });
        
        // Show modal for incoming calls automatically (badge removed, but modal still works)
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
                
                if (!isModalShown && incomingCallModal && firstCall) {
                    showIncomingCallModal(firstCall);
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
    
    // Debug log
    console.log('Incoming Call Type:', {
        originalType: call.type,
        roomId: call.room_id,
        roomIdStartsWithAudio: call.room_id ? call.room_id.startsWith('audio_') : false,
        finalType: callType,
        willShowAudio: callType === 'audio'
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
    
    // Auto ringtone or notification sound (optional)
    // playNotificationSound();
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
            incomingCallModal.hide();
            // Stop call checking
            if (callCheckInterval) {
                clearInterval(callCheckInterval);
            }
            // Check if it's an audio call (room_id starts with "audio_")
            const isAudioCall = data.room_id && data.room_id.startsWith('audio_');
            // Redirect to appropriate call page
            if (isAudioCall) {
                window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(data.room_id);
            } else {
                window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(data.room_id);
            }
        } else {
            alert('Failed to accept call. Please try again.');
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
        incomingCallModal.hide();
        currentCallRequestId = null;
    })
    .catch(error => {
        console.error('Error rejecting call:', error);
    });
}

// Show all incoming calls
function showIncomingCalls() {
    checkIncomingCalls();
}

// Mobile specific functions
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showUsersList() {
    // Mobile view: Show users list, hide chat
    if (window.innerWidth <= 768) {
        // Show users sidebar - FORCE display
        const usersSidebar = document.querySelector('.users-sidebar');
        if (usersSidebar) {
            usersSidebar.style.display = 'block !important';
            usersSidebar.style.setProperty('display', 'block', 'important');
            usersSidebar.style.width = '100%';
            usersSidebar.style.position = 'relative';
            usersSidebar.style.left = '0';
            usersSidebar.style.zIndex = '1';
        }
        
        // Hide chat sidebar - FORCE hide
        const chatSidebar = document.getElementById('chatSidebar');
        if (chatSidebar) {
            chatSidebar.style.display = 'none !important';
            chatSidebar.style.setProperty('display', 'none', 'important');
            chatSidebar.classList.remove('show', 'd-md-flex');
        }
        
        // Hide chat input area
        const chatInputArea = document.getElementById('chatInputArea');
        if (chatInputArea) {
            chatInputArea.style.display = 'none';
        }
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Reset current chat
        currentChatUserId = null;
        
        // Stop message polling
        if (chatMessagesInterval) {
            clearInterval(chatMessagesInterval);
            chatMessagesInterval = null;
        }
        
        // Update active state
        const usersNavIcon = document.getElementById('usersNavIcon');
        const usersNavText = document.getElementById('usersNavText');
        if (usersNavIcon && usersNavText) {
            usersNavIcon.classList.remove('text-muted');
            usersNavIcon.classList.add('text-primary');
            usersNavText.classList.remove('text-muted');
            usersNavText.classList.add('text-primary');
        }
    } else {
        // Desktop view: Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Show welcome sidebar, hide chat sidebar
        const welcomeSidebar = document.getElementById('welcomeSidebar');
        const chatSidebar = document.getElementById('chatSidebar');
        
        if (welcomeSidebar) {
            welcomeSidebar.style.display = 'flex';
        }
        
        if (chatSidebar) {
            chatSidebar.style.display = 'none';
        }
        
        // Reset current chat
        currentChatUserId = null;
        
        // Stop message polling
        if (chatMessagesInterval) {
            clearInterval(chatMessagesInterval);
            chatMessagesInterval = null;
        }
    }
    
    if (chatSidebar) {
        chatSidebar.style.display = 'flex';
    }
    
    // Focus on search bar after scroll
    setTimeout(() => {
        const searchInput = document.getElementById('searchUsers');
        if (searchInput) {
            searchInput.focus();
            // Scroll search bar into view if needed
            searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, 500);
    
    // Update active state
    const usersNavIcon = document.getElementById('usersNavIcon');
    const usersNavText = document.getElementById('usersNavText');
    if (usersNavIcon && usersNavText) {
        usersNavIcon.classList.remove('text-muted');
        usersNavIcon.classList.add('text-primary');
        usersNavText.classList.remove('text-muted');
        usersNavText.classList.add('text-primary');
    }
}

function openUserListForCall() {
    const modal = new bootstrap.Modal(document.getElementById('mobileCallModal'));
    modal.show();
}

// Open user list for audio call
function openUserListForAudioCall() {
    const modal = new bootstrap.Modal(document.getElementById('mobileAudioCallModal'));
    modal.show();
}

// Initiate call to a user
function initiateCall(userId, userName) {
    // Mobile par confirm skip karein
    const shouldCall = window.innerWidth > 768 ? confirm('Call ' + userName + '?') : true;
    if (!shouldCall) return;
    
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
            // Stop call checking
            if (callCheckInterval) {
                clearInterval(callCheckInterval);
            }
            // Redirect to video call page with room_id
            window.location.href = '{{ route("video.call") }}?room=' + encodeURIComponent(data.room_id);
        } else {
            alert(data.message || 'Failed to initiate call');
        }
    })
    .catch(error => {
        console.error('Error initiating call:', error);
        alert('Error starting call. Please try again.');
    });
}

// Search functionality
(function() {
    'use strict';
    
    function initSearch() {
        const searchInput = document.getElementById('searchUsers');
        const clearSearchBtn = document.getElementById('clearSearch');
        const usersList = document.getElementById('usersList');
        
        if (!searchInput) {
            console.error('Search input not found');
            return;
        }
        
        console.log('Search initialized');
        
        // Search functionality - Real-time search
        function performSearch() {
            const searchTerm = searchInput.value.trim().toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            let visibleCount = 0;
            
            console.log('Searching for:', searchTerm);
            console.log('Total cards:', userCards.length);
            
            // Show/hide clear button
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchTerm.length > 0 ? 'block' : 'none';
            }
            
            // Search through users - Priority: Name first, then email
            userCards.forEach(function(card) {
                // Get data attributes first (most reliable)
                const dataName = (card.getAttribute('data-user-name') || '').toLowerCase().trim();
                const dataEmail = (card.getAttribute('data-user-email') || '').toLowerCase().trim();
                
                // Also get from DOM elements as fallback
                const nameElement = card.querySelector('h6');
                const emailElement = card.querySelector('small');
                
                let userName = dataName;
                let userEmail = dataEmail;
                
                // Extract from DOM if data attributes are empty
                if (!userName && nameElement) {
                    userName = nameElement.textContent.trim().toLowerCase();
                }
                
                if (!userEmail && emailElement) {
                    // Get email text, remove icon
                    let emailText = emailElement.textContent.trim().toLowerCase();
                    // Remove any non-email characters at start (like icon text)
                    emailText = emailText.replace(/^[^\w@]+/, '').trim();
                    userEmail = emailText;
                }
                
                // Priority: Check name first, then email
                let matches = false;
                if (searchTerm === '') {
                    matches = true;
                } else {
                    // First check if name starts with or contains search term
                    if (userName && (userName.startsWith(searchTerm) || userName.includes(searchTerm))) {
                        matches = true;
                    }
                    // If name doesn't match, check email
                    else if (userEmail && (userEmail.startsWith(searchTerm) || userEmail.includes(searchTerm))) {
                        matches = true;
                    }
                }
                
                if (matches) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show "No results" message if no users found
            let noResultsMsg = document.getElementById('noResultsMessage');
            if (searchTerm.length > 0 && visibleCount === 0) {
                if (!noResultsMsg && usersList) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'text-center p-5 text-muted';
                    noResultsMsg.innerHTML = '<i class="bi bi-search fs-1 d-block mb-3"></i><p class="mb-0">No users found</p><small>Try searching with a different name or email</small>';
                    usersList.appendChild(noResultsMsg);
                }
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'block';
                }
            } else {
                if (noResultsMsg) {
                    noResultsMsg.style.display = 'none';
                }
            }
            
            console.log('Visible cards:', visibleCount);
        }
        
        // Add event listeners
        searchInput.addEventListener('input', performSearch);
        searchInput.addEventListener('keyup', performSearch);
        
        // Clear search
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                performSearch();
                searchInput.focus();
            });
        }
        
        // Enter key to select first result
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const visibleCards = Array.from(document.querySelectorAll('.user-card')).filter(function(card) {
                    return card.style.display !== 'none';
                });
                if (visibleCards.length > 0) {
                    const videoBtn = visibleCards[0].querySelector('a[href*="video-call"]');
                    if (videoBtn) {
                        videoBtn.click();
                    }
                }
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSearch);
    } else {
        initSearch();
    }
})();

// Chat functionality
let currentChatUserId = null;
let chatMessagesInterval = null;

// Initialize page state on load/refresh
function initializePageState() {
    // Check if user parameter in URL (from calls page)
    const urlParams = new URLSearchParams(window.location.search);
    const userIdParam = urlParams.get('user');
    
    if (userIdParam) {
        // Find user and open chat
        const userItem = document.querySelector(`[data-user-id="${userIdParam}"]`);
        if (userItem) {
            const userName = userItem.dataset.userName || '';
            const userEmail = userItem.dataset.userEmail || '';
            const userProfilePicture = userItem.querySelector('img')?.src || '';
            // Open chat with user
            setTimeout(() => {
                openChatWithUser(parseInt(userIdParam), userName, userEmail, userProfilePicture);
            }, 100);
            // Remove parameter from URL
            window.history.replaceState({}, '', window.location.pathname);
            return; // Don't reset state if opening specific user
        }
    }
    
    // Reset chat state
    currentChatUserId = null;
    
    // Stop any existing message polling
    if (chatMessagesInterval) {
        clearInterval(chatMessagesInterval);
        chatMessagesInterval = null;
    }
    
    // Ensure chat sidebar is hidden
    const chatSidebar = document.getElementById('chatSidebar');
    if (chatSidebar) {
        chatSidebar.style.display = 'none';
        chatSidebar.style.setProperty('display', 'none', 'important');
    }
    
    // Ensure welcome sidebar is shown (desktop only)
    const welcomeSidebar = document.getElementById('welcomeSidebar');
    if (welcomeSidebar && window.innerWidth > 768) {
        welcomeSidebar.style.display = 'flex';
        welcomeSidebar.style.setProperty('display', 'flex', 'important');
    }
    
    // Hide chat input area
    const chatInputArea = document.getElementById('chatInputArea');
    if (chatInputArea) {
        chatInputArea.style.display = 'none';
    }
    
    // Clear chat messages
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.innerHTML = `
            <div class="text-center text-muted py-5" id="noMessages">
                <i class="bi bi-chat-dots fs-1 d-block mb-2"></i>
                <p class="mb-0">No messages yet</p>
                <small>Start a conversation!</small>
            </div>
        `;
    }
    
    // Reset chat header
    const chatUserName = document.getElementById('chatUserName');
    const chatUserInitial = document.getElementById('chatUserInitial');
    const chatUserStatus = document.getElementById('chatUserStatus');
    if (chatUserName) chatUserName.textContent = 'Select a user';
    if (chatUserInitial) chatUserInitial.textContent = 'U';
    if (chatUserStatus) chatUserStatus.textContent = 'Online';
}

// Open chat with a user
function openChatWithUser(userId, userName, userEmail, userProfilePicture) {
    currentChatUserId = userId;
    
    // Update chat header
    document.getElementById('chatUserName').textContent = userName;
    document.getElementById('chatUserInitial').textContent = userName.charAt(0).toUpperCase();
    document.getElementById('chatUserStatus').textContent = 'Online';
    
    // Show chat sidebar, hide welcome sidebar
    const chatSidebar = document.getElementById('chatSidebar');
    const welcomeSidebar = document.getElementById('welcomeSidebar');
    const chatInputArea = document.getElementById('chatInputArea');
    
    if (window.innerWidth > 768) {
        // Desktop view
        if (chatSidebar) {
            chatSidebar.style.display = 'flex';
            chatSidebar.style.setProperty('display', 'flex', 'important');
        }
        if (welcomeSidebar) {
            welcomeSidebar.style.display = 'none';
            welcomeSidebar.style.setProperty('display', 'none', 'important');
        }
    } else {
        // Mobile view - show chat full screen
        if (chatSidebar) {
            chatSidebar.style.display = 'flex';
            chatSidebar.classList.add('d-md-flex');
            chatSidebar.style.position = 'fixed';
            chatSidebar.style.top = '0';
            chatSidebar.style.left = '0';
            chatSidebar.style.width = '100%';
            chatSidebar.style.height = '100vh';
            chatSidebar.style.zIndex = '2000';
            chatSidebar.style.background = 'white';
        }
        // Hide users list on mobile
        const usersSidebar = document.querySelector('.users-sidebar');
        if (usersSidebar) usersSidebar.style.display = 'none';
    }
    
    if (chatInputArea) chatInputArea.style.display = 'block';
    
    // Load messages
    loadChatMessages(userId);
    
    // Start polling for new messages
    if (chatMessagesInterval) clearInterval(chatMessagesInterval);
    chatMessagesInterval = setInterval(() => {
        loadChatMessages(userId, true);
        updateUnreadCounts(); // Update unread counts
    }, 2000);
}

// Move user to top of list (without refresh) - Works on mobile and desktop
function moveUserToTop(userId) {
    const usersList = document.getElementById('usersList');
    if (!usersList) return;
    
    const userItem = document.querySelector(`[data-user-id="${userId}"]`);
    if (!userItem) return;
    
    // Store the user item's HTML to preserve state
    const userItemHTML = userItem.outerHTML;
    
    // Remove user from current position
    userItem.remove();
    
    // Insert at the top of the list
    const firstItem = usersList.querySelector('.user-item');
    if (firstItem) {
        usersList.insertBefore(userItem, firstItem);
    } else {
        usersList.appendChild(userItem);
    }
    
    // Ensure users list is visible on mobile
    if (window.innerWidth <= 768) {
        const usersSidebar = document.querySelector('.users-sidebar');
        if (usersSidebar) {
            usersSidebar.style.display = 'block';
        }
    }
}

// Update unread message counts and reorder list
function updateUnreadCounts() {
    fetch('{{ route("api.chat.conversations") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.conversations) {
            // Sort conversations by last message time (most recent first)
            const sortedConversations = [...data.conversations].sort((a, b) => {
                const timeA = a.last_message_time ? new Date(a.last_message_time).getTime() : 0;
                const timeB = b.last_message_time ? new Date(b.last_message_time).getTime() : 0;
                return timeB - timeA; // Descending order (most recent first)
            });
            
            // Reorder user list based on sorted conversations (only if they have messages)
            // Build a complete reordered list to avoid multiple DOM manipulations
            const usersList = document.getElementById('usersList');
            if (usersList && sortedConversations.length > 0) {
                // Create a map of all existing user items
                const allUserItems = new Map();
                Array.from(usersList.children).forEach(item => {
                    const userId = item.dataset.userId;
                    if (userId) {
                        allUserItems.set(parseInt(userId), item);
                    }
                });
                
                // Create ordered list: conversations with messages first, then others
                const orderedItems = [];
                
                // Add conversations with messages in sorted order (most recent first)
                sortedConversations.forEach(conv => {
                    if (conv.last_message_time && allUserItems.has(conv.user_id)) {
                        orderedItems.push(allUserItems.get(conv.user_id));
                        allUserItems.delete(conv.user_id); // Remove from map
                    }
                });
                
                // Add remaining users (without recent messages) sorted by name
                const remainingItems = Array.from(allUserItems.values()).sort((a, b) => {
                    const nameA = a.dataset.userName || '';
                    const nameB = b.dataset.userName || '';
                    return nameA.localeCompare(nameB);
                });
                orderedItems.push(...remainingItems);
                
                // Clear and rebuild list in correct order
                usersList.innerHTML = '';
                orderedItems.forEach(item => usersList.appendChild(item));
            }
            
            // Calculate total unread count for footer badge
            let totalUnreadCount = 0;
            data.conversations.forEach(conv => {
                totalUnreadCount += conv.unread_count || 0;
            });
            
            // Update footer badge - only show if there are unread messages
            const footerBadge = document.getElementById('footerUnreadBadge');
            if (footerBadge) {
                if (totalUnreadCount > 0) {
                    footerBadge.textContent = totalUnreadCount > 99 ? '99+' : totalUnreadCount;
                    footerBadge.style.setProperty('display', 'block', 'important');
                } else {
                    // Hide badge completely if no unread messages
                    footerBadge.style.setProperty('display', 'none', 'important');
                    footerBadge.textContent = '';
                }
            }
            
            // Update badges for each user
            data.conversations.forEach(conv => {
                const userItem = document.querySelector(`[data-user-id="${conv.user_id}"]`);
                if (userItem) {
                    let badge = userItem.querySelector('.badge');
                    if (conv.unread_count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'badge bg-primary rounded-pill d-flex align-items-center justify-content-center';
                            badge.style.cssText = 'min-width: 22px; height: 22px; font-size: 0.7rem; font-weight: 600; padding: 0 6px;';
                            const flexContainer = userItem.querySelector('.flex-shrink-0.ms-2.position-relative');
                            if (flexContainer) {
                                flexContainer.appendChild(badge);
                            }
                        }
                        badge.textContent = conv.unread_count > 99 ? '99+' : conv.unread_count;
                        badge.style.display = 'flex';
                    } else if (badge && conv.unread_count === 0) {
                        badge.style.display = 'none';
                    }
                }
            });
        }
    })
    .catch(error => {
        console.error('Error updating unread counts:', error);
    });
}

// Close chat view (mobile) - redirect to dashboard
function closeChatView() {
    goToDashboard();
}

// Go to dashboard
function goToDashboard() {
    // Show welcome sidebar and hide chat sidebar (Desktop)
    const welcomeSidebar = document.getElementById('welcomeSidebar');
    const chatSidebar = document.getElementById('chatSidebar');
    
    if (welcomeSidebar && window.innerWidth > 768) {
        welcomeSidebar.style.display = 'flex';
    }
    
    if (chatSidebar && window.innerWidth > 768) {
        chatSidebar.style.display = 'none';
    }
    
    // On mobile, just reload to show users list
    if (window.innerWidth <= 768) {
        window.location.href = '{{ route("dashboard") }}';
    }
}

// Go to calls page
function goToCalls() {
    window.location.href = '{{ route("calls") }}';
}

// Load chat messages
function loadChatMessages(userId, silent = false) {
    fetch(`{{ route('api.chat.messages', ':userId') }}`.replace(':userId', userId), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Messages are automatically marked as read when fetched via getMessages API
            // Update unread counts immediately after loading messages
            updateUnreadCounts();
            
            // Move user to top when new messages arrive (incoming messages)
            if (data.messages && data.messages.length > 0) {
                const lastMessage = data.messages[data.messages.length - 1];
                // If last message is from other user (incoming), move them to top
                if (lastMessage.sender_id != {{ Auth::id() }}) {
                    moveUserToTop(userId);
                }
            }
            
            // Only force scroll on initial load (not silent)
            displayChatMessages(data.messages, !silent);
        }
    })
    .catch(error => {
        console.error('Error loading messages:', error);
    });
}

// Check if user is at bottom of chat
function isAtBottom() {
    const messagesArea = document.getElementById('chatMessagesArea');
    if (!messagesArea) return true;
    
    const threshold = 100; // 100px threshold
    const scrollTop = messagesArea.scrollTop;
    const scrollHeight = messagesArea.scrollHeight;
    const clientHeight = messagesArea.clientHeight;
    
    return (scrollHeight - scrollTop - clientHeight) < threshold;
}

// Display chat messages
// Message selection mode
let messageSelectionMode = false;
let selectedMessageIds = new Set();

// Toggle message selection mode
function toggleMessageSelectionMode() {
    messageSelectionMode = !messageSelectionMode;
    const checkboxes = document.querySelectorAll('.message-checkbox-container');
    const selectBtn = document.getElementById('selectMessagesBtn');
    const deleteBtn = document.getElementById('deleteMessagesBtn');
    const videoBtn = document.getElementById('videoCallBtn');
    const audioBtn = document.getElementById('audioCallBtn');
    const menuBtn = document.getElementById('chatMenuBtn');
    
    if (messageSelectionMode) {
        // Enter selection mode
        checkboxes.forEach(cb => cb.classList.remove('d-none'));
        deleteBtn.classList.remove('d-none');
        videoBtn.style.display = 'none';
        audioBtn.style.display = 'none';
        menuBtn.style.display = 'none';
        selectBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        selectedMessageIds.clear();
        updateMessageDeleteButton();
    } else {
        // Exit selection mode
        checkboxes.forEach(cb => cb.classList.add('d-none'));
        deleteBtn.classList.add('d-none');
        videoBtn.style.display = 'flex';
        audioBtn.style.display = 'flex';
        menuBtn.style.display = 'flex';
        selectBtn.innerHTML = '<i class="bi bi-check-square"></i>';
        // Uncheck all checkboxes
        document.querySelectorAll('.message-checkbox').forEach(cb => {
            cb.checked = false;
        });
        selectedMessageIds.clear();
    }
}

// Update message delete button
function updateMessageDeleteButton() {
    const checkboxes = document.querySelectorAll('.message-checkbox:checked');
    const deleteBtn = document.getElementById('deleteMessagesBtn');
    selectedMessageIds.clear();
    
    checkboxes.forEach(cb => {
        if (cb.checked && cb.value) {
            selectedMessageIds.add(parseInt(cb.value));
        }
    });
    
    if (selectedMessageIds.size > 0) {
        deleteBtn.innerHTML = `<i class="bi bi-trash"></i>`;
        deleteBtn.title = `Delete ${selectedMessageIds.size} message(s)`;
        deleteBtn.style.display = 'flex';
    } else {
        deleteBtn.style.display = 'none';
    }
}

// Delete selected messages
function deleteSelectedMessages() {
    if (selectedMessageIds.size === 0) {
        alert('Please select messages to delete');
        return;
    }
    
    const confirmDelete = confirm(`Are you sure you want to delete ${selectedMessageIds.size} message(s)?`);
    if (!confirmDelete) return;
    
    fetch('{{ route("api.chat.delete") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            message_ids: Array.from(selectedMessageIds)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove deleted message items from DOM
            const deletedCount = selectedMessageIds.size;
            selectedMessageIds.forEach(messageId => {
                const messageItem = document.querySelector(`[data-message-id="${messageId}"]`);
                if (messageItem) {
                    messageItem.style.transition = 'opacity 0.3s, transform 0.3s';
                    messageItem.style.opacity = '0';
                    messageItem.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        messageItem.remove();
                        // Check if no messages left
                        const remainingMessages = document.querySelectorAll('.message-item').length;
                        if (remainingMessages === 0) {
                            const noMessages = document.getElementById('noMessages');
                            if (noMessages) {
                                noMessages.style.display = 'block';
                            }
                        }
                    }, 300);
                }
            });
            
            // Exit selection mode
            toggleMessageSelectionMode();
            
            // Show success message (without blocking)
            setTimeout(() => {
                // Messages are deleted, no need to reload
                // The polling will handle new messages naturally
            }, 100);
        } else {
            alert(data.message || 'Failed to delete messages');
        }
    })
    .catch(error => {
        console.error('Error deleting messages:', error);
        alert('Error deleting messages. Please try again.');
    });
}

function displayChatMessages(messages, shouldScroll = false) {
    const messagesContainer = document.getElementById('chatMessages');
    const noMessages = document.getElementById('noMessages');
    
    if (!messagesContainer) return;
    
    // Check if user is at bottom before updating
    const wasAtBottom = isAtBottom();
    
    if (messages.length === 0) {
        if (noMessages) noMessages.style.display = 'block';
        // Only clear if not in selection mode
        if (!messageSelectionMode) {
            messagesContainer.innerHTML = '';
        }
        return;
    }
    
    if (noMessages) noMessages.style.display = 'none';
    
    // Get existing message IDs in DOM
    const existingMessageIds = new Set();
    document.querySelectorAll('.message-item[data-message-id]').forEach(item => {
        const msgId = item.getAttribute('data-message-id');
        if (msgId) {
            const id = parseInt(msgId);
            // Only add if element is visible (not being deleted)
            if (item.style.opacity !== '0' && item.style.display !== 'none') {
                existingMessageIds.add(id);
            }
        }
    });
    
    // Get new message IDs from server
    const newMessageIds = new Set(messages.map(msg => msg.id));
    
    // Check if we need to update (only if there are actual changes)
    const hasNewMessages = messages.some(msg => !existingMessageIds.has(msg.id));
    const hasRemovedMessages = Array.from(existingMessageIds).some(id => !newMessageIds.has(id));
    
    // If no changes and we have messages, don't reload (prevents flicker)
    if (!hasNewMessages && !hasRemovedMessages && messagesContainer.children.length > 0 && messages.length > 0) {
        return;
    }
    
    messagesContainer.innerHTML = messages.map(msg => {
        const isSent = msg.sender_id == {{ Auth::id() }};
        const time = new Date(msg.created_at).toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        
        // WhatsApp-style tick marks
        let tickMark = '';
        if (isSent) {
            if (msg.is_read) {
                // Blue double tick (read/seen)
                tickMark = '<i class="bi bi-check2-all" style="color: #53bdeb; font-size: 0.85rem; margin-left: 4px;"></i>';
            } else {
                // Grey double tick (delivered)
                tickMark = '<i class="bi bi-check2-all" style="color: #8696a0; font-size: 0.85rem; margin-left: 4px;"></i>';
            }
        }
        
        // Handle different message types
        let mediaContent = '';
        if (msg.type === 'image' && msg.file_path) {
            const imageUrl = '{{ asset("storage") }}/' + msg.file_path;
            const imageId = 'img_' + msg.id + '_' + Date.now();
            mediaContent = `
                <div class="mb-2 image-container" 
                     id="${imageId}_container"
                     data-image-url="${imageUrl}"
                     style="border-radius: 8px; overflow: hidden; max-width: 300px; cursor: pointer; position: relative; -webkit-tap-highlight-color: rgba(0,0,0,0.1); touch-action: manipulation;"
                     onclick="openMediaViewer('${imageUrl}', 'image')">
                    <img src="${imageUrl}" alt="Image" 
                         id="${imageId}"
                         style="width: 100%; height: auto; display: block; pointer-events: none; user-select: none; -webkit-user-select: none; -webkit-touch-callout: none;">
                </div>
            `;
            
            // Add touch event listener after DOM update for mobile
            setTimeout(() => {
                const container = document.getElementById(imageId + '_container');
                if (container) {
                    let touchStartTime = 0;
                    let touchStartY = 0;
                    
                    container.addEventListener('touchstart', function(e) {
                        touchStartTime = Date.now();
                        touchStartY = e.touches[0].clientY;
                        this.style.opacity = '0.8';
                    }, { passive: true });
                    
                    container.addEventListener('touchend', function(e) {
                        const touchEndTime = Date.now();
                        const touchDuration = touchEndTime - touchStartTime;
                        const touchEndY = e.changedTouches[0].clientY;
                        const touchDistance = Math.abs(touchEndY - touchStartY);
                        
                        this.style.opacity = '1';
                        
                        // Only open if it was a quick tap (not a scroll)
                        if (touchDuration < 300 && touchDistance < 10) {
                            e.preventDefault();
                            e.stopPropagation();
                            const url = this.getAttribute('data-image-url');
                            if (url) {
                                openMediaViewer(url, 'image');
                            }
                        }
                    }, { passive: false });
                }
            }, 100);
        } else if (msg.type === 'video' && msg.file_path) {
            const videoUrl = '{{ asset("storage") }}/' + msg.file_path;
            mediaContent = `
                <div class="mb-2" style="border-radius: 8px; overflow: hidden; max-width: 300px;">
                    <video controls style="width: 100%; height: auto; display: block; max-height: 300px;">
                        <source src="${videoUrl}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            `;
        } else if (msg.type === 'audio' && msg.file_path) {
            const audioUrl = '{{ asset("storage") }}/' + msg.file_path;
            mediaContent = `
                <div class="mb-2 d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.05); padding: 12px; border-radius: 8px;">
                    <i class="bi bi-music-note-beamed fs-4" style="color: #008069;"></i>
                    <audio controls style="flex: 1; max-width: 250px;">
                        <source src="${audioUrl}" type="audio/mpeg">
                        Your browser does not support the audio tag.
                    </audio>
                </div>
            `;
        } else if (msg.type === 'file' && msg.file_path) {
            const fileUrl = '{{ asset("storage") }}/' + msg.file_path;
            const fileName = msg.message || 'File';
            mediaContent = `
                <div class="mb-2 d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.05); padding: 12px; border-radius: 8px; cursor: pointer;" onclick="window.open('${fileUrl}', '_blank')">
                    <i class="bi bi-file-earmark fs-3" style="color: #008069;"></i>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 500; font-size: 0.9rem; word-break: break-word;">${escapeHtml(fileName)}</div>
                        <small style="color: #667781; font-size: 0.75rem;">Tap to download</small>
                    </div>
                    <i class="bi bi-download" style="color: #008069;"></i>
                </div>
            `;
        } else if (msg.type === 'location' && msg.file_path) {
            try {
                const locationData = JSON.parse(msg.file_path);
                const mapUrl = locationData.url || `https://www.google.com/maps?q=${locationData.latitude},${locationData.longitude}`;
                const latitude = locationData.latitude;
                const longitude = locationData.longitude;
                
                // Create static map image URL (using Google Maps Static API or OpenStreetMap)
                const staticMapUrl = `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s-l+dc3545(${longitude},${latitude})/${longitude},${latitude},15,0/300x200@2x?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXV4NTFmZmYycXVndHFkZnppem4ifQ.rJcFIG214AriISLbB6B5aw`;
                // Fallback to OpenStreetMap if Mapbox doesn't work
                const fallbackMapUrl = `https://www.openstreetmap.org/export/embed.html?bbox=${longitude-0.01},${latitude-0.01},${longitude+0.01},${latitude+0.01}&layer=mapnik&marker=${latitude},${longitude}`;
                
                mediaContent = `
                    <div class="mb-2" style="border-radius: 8px; overflow: hidden; max-width: 300px; cursor: pointer; background: white; border: 1px solid #e0e0e0;" onclick="window.open('${mapUrl}', '_blank')">
                        <div style="width: 100%; height: 150px; background: #e3f2fd; position: relative; overflow: hidden;">
                            <iframe src="${fallbackMapUrl}" style="width: 100%; height: 100%; border: none; pointer-events: none;"></iframe>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
                                <i class="bi bi-geo-alt-fill" style="font-size: 2rem; color: #dc3545; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"></i>
                            </div>
                        </div>
                        <div style="padding: 12px; background: white;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-geo-alt-fill" style="color: #008069; font-size: 1.2rem;"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 500; font-size: 0.9rem; color: #111b21;">Location</div>
                                    <small style="color: #667781; font-size: 0.75rem;">Tap to open in maps</small>
                                </div>
                                <i class="bi bi-arrow-up-right" style="color: #008069;"></i>
                            </div>
                        </div>
                    </div>
                `;
            } catch (e) {
                // Fallback if location data is not in JSON format
                const mapUrl = msg.file_path.startsWith('http') ? msg.file_path : `https://www.google.com/maps?q=${msg.file_path}`;
                mediaContent = `
                    <div class="mb-2 d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.05); padding: 12px; border-radius: 8px; cursor: pointer;" onclick="window.open('${mapUrl}', '_blank')">
                        <i class="bi bi-geo-alt-fill fs-3" style="color: #008069;"></i>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; font-size: 0.9rem; word-break: break-word;">📍 Location</div>
                            <small style="color: #667781; font-size: 0.75rem;">Tap to open in maps</small>
                        </div>
                        <i class="bi bi-arrow-up-right" style="color: #008069;"></i>
                    </div>
                `;
            }
        } else if (msg.type === 'contact' && msg.file_path) {
            try {
                const contactData = JSON.parse(msg.file_path);
                const contactName = contactData.name || 'Unknown';
                const contactEmail = contactData.email || '';
                const contactUserId = contactData.user_id || '';
                
                mediaContent = `
                    <div class="mb-2" style="border-radius: 8px; overflow: hidden; max-width: 300px; cursor: pointer; background: white; border: 1px solid #e0e0e0;" onclick="if(${contactUserId}) { openChatWithUser(${contactUserId}, '${contactName.replace(/'/g, "\\'")}', '${contactEmail.replace(/'/g, "\\'")}', ''); }">
                        <div style="padding: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="fw-bold" style="color: #667eea; font-size: 1.2rem;">${contactName.charAt(0).toUpperCase()}</span>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 600; font-size: 1rem; color: white; word-break: break-word;">${escapeHtml(contactName)}</div>
                                    ${contactEmail ? `<small style="color: rgba(255,255,255,0.9); font-size: 0.85rem;">${escapeHtml(contactEmail)}</small>` : ''}
                                </div>
                            </div>
                        </div>
                        <div style="padding: 12px; background: white; display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-person-vcard-fill" style="color: #008069; font-size: 1.2rem;"></i>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 500; font-size: 0.9rem; color: #111b21;">Contact</div>
                                <small style="color: #667781; font-size: 0.75rem;">${contactUserId ? 'Tap to open chat' : 'Contact info'}</small>
                            </div>
                            <i class="bi bi-arrow-up-right" style="color: #008069;"></i>
                        </div>
                    </div>
                `;
            } catch (e) {
                // Fallback if contact data is not in JSON format - show message text
                mediaContent = `
                    <div class="mb-2 d-flex align-items-center gap-2" style="background: rgba(0,0,0,0.05); padding: 12px; border-radius: 8px;">
                        <i class="bi bi-person-vcard-fill fs-3" style="color: #008069;"></i>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; font-size: 0.9rem; word-break: break-word;">👤 Contact</div>
                            <small style="color: #667781; font-size: 0.75rem;">Contact information</small>
                        </div>
                    </div>
                `;
            }
        }
        
        return `
            <div class="d-flex ${isSent ? 'justify-content-end' : 'justify-content-start'} mb-2 message-item" data-message-id="${msg.id}">
                <!-- Selection Checkbox -->
                <div class="flex-shrink-0 d-none message-checkbox-container" style="width: 24px; padding-top: 4px; ${isSent ? 'order: 2;' : ''}">
                    <input type="checkbox" class="form-check-input message-checkbox" 
                           value="${msg.id}"
                           onchange="updateMessageDeleteButton()"
                           style="width: 18px; height: 18px; cursor: pointer;">
                </div>
                <div class="message-bubble ${isSent ? 'sent' : 'received'}" 
                     style="max-width: 70%; padding: ${msg.type !== 'text' ? '4px' : '8px 12px'}; border-radius: 8px; ${isSent ? 'background: #dcf8c6; margin-left: auto;' : 'background: white;'}">
                    ${mediaContent}
                    ${msg.message && msg.type === 'text' ? `
                        <div class="message-text" style="word-wrap: break-word; font-size: 0.9rem;">
                            ${escapeHtml(msg.message)}
                        </div>
                    ` : msg.message && msg.type !== 'text' ? `
                        <div class="message-text mt-2" style="word-wrap: break-word; font-size: 0.85rem; color: #54656f; padding: 0 8px;">
                            ${escapeHtml(msg.message)}
                        </div>
                    ` : ''}
                    <div class="d-flex align-items-center justify-content-end mt-1" style="gap: 4px; padding: 0 4px;">
                        <span class="message-time" style="font-size: 0.7rem; color: #667781;">
                            ${time}
                        </span>
                        ${tickMark}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Only scroll if explicitly requested or if user was at bottom
    if (shouldScroll || wasAtBottom) {
        setTimeout(() => scrollToBottom(), 50);
    }
}

// Store selected file for preview
let selectedFile = null;
let selectedFileType = null;

// Toggle attachment menu
function toggleAttachmentMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('attachmentMenu');
    const emojiPicker = document.getElementById('emojiPickerContainer');
    
    if (emojiPicker && !emojiPicker.classList.contains('d-none')) {
        emojiPicker.style.display = 'none';
    }
    
    if (menu) {
        if (menu.classList.contains('d-none')) {
            menu.classList.remove('d-none');
            setTimeout(() => {
                document.addEventListener('click', closeAttachmentMenu);
            }, 100);
        } else {
            menu.classList.add('d-none');
            document.removeEventListener('click', closeAttachmentMenu);
        }
    }
}

// Close attachment menu
function closeAttachmentMenu(event) {
    const menu = document.getElementById('attachmentMenu');
    const btn = document.getElementById('attachmentBtn');
    
    if (menu && btn && !menu.contains(event.target) && !btn.contains(event.target)) {
        menu.classList.add('d-none');
        document.removeEventListener('click', closeAttachmentMenu);
    }
}

// Open file input based on type
function openFileInput(type) {
    closeAttachmentMenu({target: null});
    
    let inputId = '';
    switch(type) {
        case 'document':
            inputId = 'fileInputDocument';
            break;
        case 'camera':
            inputId = 'fileInputCamera';
            break;
        case 'gallery':
            inputId = 'fileInputGallery';
            break;
        case 'audio':
            inputId = 'fileInputAudio';
            break;
        case 'video':
            inputId = 'fileInputVideo';
            break;
        case 'location':
            showLocationOptions();
            return;
        case 'contact':
            showContactSelector();
            return;
        default:
            return;
    }
    
    const input = document.getElementById(inputId);
    if (input) {
        input.click();
    }
}

// Compress image for mobile
function compressImage(file, maxWidth = 1920, maxHeight = 1920, quality = 0.8) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                if (width > height) {
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(function(blob) {
                    const compressedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', quality);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

// Handle file selection - show preview instead of sending directly
function handleFileSelect(event, fileType) {
    const file = event.target.files[0];
    if (!file || !currentChatUserId) return;
    
    const maxSize = fileType === 'video' ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
    if (file.size > maxSize) {
        alert(`File size is too large. Maximum size: ${fileType === 'video' ? '50MB' : '10MB'}`);
        event.target.value = '';
        return;
    }
    
    if (fileType === 'image' && file.size > 2 * 1024 * 1024) {
        const previewArea = document.getElementById('mediaPreviewArea');
        const previewContent = document.getElementById('mediaPreviewContent');
        if (previewArea && previewContent) {
            previewArea.classList.remove('d-none');
            previewContent.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm" role="status"></div><div class="mt-2"><small>Compressing image...</small></div></div>';
        }
        
        compressImage(file).then(compressedFile => {
            selectedFile = compressedFile;
            selectedFileType = fileType;
            showMediaPreview(compressedFile, fileType);
            event.target.value = '';
        }).catch(error => {
            console.error('Error compressing image:', error);
            selectedFile = file;
            selectedFileType = fileType;
            showMediaPreview(file, fileType);
            event.target.value = '';
        });
    } else {
        selectedFile = file;
        selectedFileType = fileType;
        showMediaPreview(file, fileType);
        event.target.value = '';
    }
}

// Show media preview in typing area
function showMediaPreview(file, fileType) {
    const previewArea = document.getElementById('mediaPreviewArea');
    const previewContent = document.getElementById('mediaPreviewContent');
    const captionInput = document.getElementById('mediaCaptionInput');
    
    if (!previewArea || !previewContent) return;
    
    previewArea.classList.remove('d-none');
    
    let previewHTML = '';
    const fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
    
    if (fileType === 'image') {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewHTML = `
                <div style="max-width: 200px; max-height: 200px; margin: 0 auto; border-radius: 8px; overflow: hidden;">
                    <img src="${e.target.result}" alt="Preview" style="width: 100%; height: auto; display: block;">
                </div>
                <div class="text-center mt-2">
                    <small style="color: #54656f; font-size: 0.75rem;">${file.name}</small>
                    <br><small style="color: #8696a0; font-size: 0.7rem;">${fileSize}</small>
                </div>
            `;
            previewContent.innerHTML = previewHTML;
        };
        reader.readAsDataURL(file);
    } else if (fileType === 'video') {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewHTML = `
                <div style="max-width: 200px; max-height: 200px; margin: 0 auto; border-radius: 8px; overflow: hidden; background: #000;">
                    <video src="${e.target.result}" style="width: 100%; height: auto; max-height: 200px;" controls></video>
                </div>
                <div class="text-center mt-2">
                    <small style="color: #54656f; font-size: 0.75rem;">${file.name}</small>
                    <br><small style="color: #8696a0; font-size: 0.7rem;">${fileSize}</small>
                </div>
            `;
            previewContent.innerHTML = previewHTML;
        };
        reader.readAsDataURL(file);
    } else if (fileType === 'audio') {
        previewHTML = `
            <div class="d-flex align-items-center gap-3 p-3" style="background: white; border-radius: 8px;">
                <i class="bi bi-music-note-beamed fs-2" style="color: #008069;"></i>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 500; font-size: 0.9rem; word-break: break-word;">${file.name}</div>
                    <small style="color: #667781; font-size: 0.75rem;">${fileSize}</small>
                </div>
            </div>
        `;
        previewContent.innerHTML = previewHTML;
    } else if (fileType === 'file') {
        previewHTML = `
            <div class="d-flex align-items-center gap-3 p-3" style="background: white; border-radius: 8px;">
                <i class="bi bi-file-earmark fs-2" style="color: #008069;"></i>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 500; font-size: 0.9rem; word-break: break-word;">${file.name}</div>
                    <small style="color: #667781; font-size: 0.75rem;">${fileSize}</small>
                </div>
            </div>
        `;
        previewContent.innerHTML = previewHTML;
    }
    
    if (captionInput) {
        captionInput.value = '';
    }
}

// Clear media preview
function clearMediaPreview() {
    const previewArea = document.getElementById('mediaPreviewArea');
    const previewContent = document.getElementById('mediaPreviewContent');
    const captionInput = document.getElementById('mediaCaptionInput');
    
    if (previewArea) previewArea.classList.add('d-none');
    if (previewContent) previewContent.innerHTML = '';
    if (captionInput) captionInput.value = '';
    
    selectedFile = null;
    selectedFileType = null;
}

// Send chat message (text or media)
function sendChatMessage() {
    if (!currentChatUserId) return;
    
    // Check if there's a media file to send
    if (selectedFile && selectedFileType) {
        sendMediaMessage();
        return;
    }
    
    // Send text message
    const input = document.getElementById('chatMessageInput');
    if (!input || !input.value.trim()) return;
    
    const message = input.value.trim();
    input.value = '';
    
    fetch('{{ route("api.chat.send") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            receiver_id: currentChatUserId,
            message: message,
            type: 'text'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            moveUserToTop(currentChatUserId);
            loadChatMessages(currentChatUserId, false);
        } else {
            alert('Failed to send message');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message');
    });
}

// Send media message
function sendMediaMessage() {
    if (!selectedFile || !selectedFileType || !currentChatUserId) return;
    
    const captionInput = document.getElementById('mediaCaptionInput');
    const caption = captionInput ? captionInput.value.trim() : '';
    const input = document.getElementById('chatMessageInput');
    
    if (input) {
        input.disabled = true;
        input.placeholder = 'Uploading...';
    }
    
    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('receiver_id', currentChatUserId);
    formData.append('type', selectedFileType);
    formData.append('message', caption);
    
    fetch('{{ route("api.chat.send") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Upload failed');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            moveUserToTop(currentChatUserId);
            clearMediaPreview();
            loadChatMessages(currentChatUserId, true);
        } else {
            alert(data.message || 'Failed to send media');
        }
    })
    .catch(error => {
        console.error('Error sending media:', error);
        alert(error.message || 'Error sending media. Please try again.');
    })
    .finally(() => {
        if (input) {
            input.disabled = false;
            input.placeholder = 'Type a message';
        }
    });
}

// Handle Enter key in chat input
function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}

// Emoji picker state
let emojiPickerVisible = false;
const commonEmojis = [
    '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣',
    '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰',
    '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜',
    '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏',
    '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
    '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠',
    '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨',
    '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥',
    '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧',
    '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐',
    '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑',
    '🤠', '😈', '👿', '👹', '👺', '🤡', '💩', '👻',
    '💀', '☠️', '👽', '👾', '🤖', '🎃', '😺', '😸',
    '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '👋',
    '🤚', '🖐', '✋', '🖖', '👌', '🤏', '✌️', '🤞',
    '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇',
    '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏',
    '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💪', '🦾',
    '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🦷',
    '🦴', '👀', '👁️', '👅', '👄', '💋', '💘', '💝',
    '💖', '💗', '💓', '💞', '💕', '💟', '❣️', '💔',
    '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍',
    '🤎', '💯', '💢', '💥', '💫', '💦', '💨', '🕳️',
    '💣', '💬', '👁️‍🗨️', '🗨️', '🗯️', '💭', '💤'
];

// Initialize emoji picker
function initEmojiPicker() {
    const emojiPicker = document.getElementById('emojiPicker');
    if (!emojiPicker) return;
    
    emojiPicker.innerHTML = commonEmojis.map(emoji => `
        <button type="button" class="btn btn-sm p-2" 
                onclick="insertEmoji('${emoji}')"
                style="font-size: 24px; border: none; background: transparent; cursor: pointer; transition: transform 0.1s;"
                onmouseover="this.style.transform='scale(1.2)'"
                onmouseout="this.style.transform='scale(1)'">
            ${emoji}
        </button>
    `).join('');
}

// Toggle emoji picker
function toggleEmojiPicker(event) {
    event.stopPropagation();
    const container = document.getElementById('emojiPickerContainer');
    if (!container) return;
    
    emojiPickerVisible = !emojiPickerVisible;
    container.style.display = emojiPickerVisible ? 'block' : 'none';
    
    if (emojiPickerVisible) {
        // Close picker when clicking outside
        setTimeout(() => {
            document.addEventListener('click', closeEmojiPickerOnOutsideClick);
        }, 100);
    }
}

// Close emoji picker when clicking outside
function closeEmojiPickerOnOutsideClick(event) {
    const container = document.getElementById('emojiPickerContainer');
    const button = document.getElementById('emojiPickerBtn');
    
    if (container && button && 
        !container.contains(event.target) && 
        !button.contains(event.target)) {
        container.style.display = 'none';
        emojiPickerVisible = false;
        document.removeEventListener('click', closeEmojiPickerOnOutsideClick);
    }
}

// Insert emoji into input
function insertEmoji(emoji) {
    const input = document.getElementById('chatMessageInput');
    if (input) {
        const cursorPos = input.selectionStart || input.value.length;
        const textBefore = input.value.substring(0, cursorPos);
        const textAfter = input.value.substring(cursorPos);
        input.value = textBefore + emoji + textAfter;
        input.focus();
        // Set cursor position after inserted emoji
        input.setSelectionRange(cursorPos + emoji.length, cursorPos + emoji.length);
    }
}

// Scroll to bottom of chat
function scrollToBottom() {
    const messagesArea = document.getElementById('chatMessagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Open media viewer (for images) - Mobile optimized
function openMediaViewer(url, type) {
    if (type === 'image') {
        // Prevent body scroll when modal is open
        document.body.style.overflow = 'hidden';
        
        // Create modal for image viewer
        const modal = document.createElement('div');
        modal.id = 'mediaViewerModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('data-bs-backdrop', 'static');
        modal.setAttribute('data-bs-keyboard', 'true');
        modal.style.zIndex = '9999';
        
        // Mobile-friendly modal
        const isMobile = window.innerWidth <= 768;
        const modalSize = isMobile ? '100vw' : '95vw';
        const buttonSize = isMobile ? '45px' : '40px';
        const buttonTop = isMobile ? '10px' : '15px';
        const buttonRight = isMobile ? '10px' : '15px';
        
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered" style="max-width: ${modalSize}; width: ${modalSize}; margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center;">
                <div class="modal-content" style="background: rgba(0,0,0,0.95); border: none; position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                    <div class="modal-body p-0 text-center" style="position: relative; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 0;">
                        <div style="position: relative; display: inline-block; max-width: 100%; max-height: ${isMobile ? '100vh' : '90vh'};">
                            <button type="button" 
                                    id="closeMediaViewerBtn"
                                    style="position: absolute; top: ${buttonTop}; right: ${buttonRight}; z-index: 10000; 
                                           background: #dc3545 !important; color: white !important; border: 3px solid white !important; 
                                           border-radius: 50% !important; width: ${buttonSize} !important; height: ${buttonSize} !important; 
                                           padding: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; 
                                           cursor: pointer !important; box-shadow: 0 4px 12px rgba(0,0,0,0.7) !important;
                                           font-weight: bold !important; font-size: ${isMobile ? '1.4rem' : '1.2rem'} !important; 
                                           transition: all 0.2s; touch-action: manipulation; -webkit-tap-highlight-color: transparent;
                                           margin: 0 !important; pointer-events: auto !important;"
                                    aria-label="Close">
                                <i class="bi bi-x-lg" style="line-height: 1; color: white !important; pointer-events: none;"></i>
                            </button>
                            <img src="${url}" alt="Image" 
                                 id="viewerImage"
                                 style="max-width: 100%; max-height: ${isMobile ? '100vh' : '90vh'}; width: auto; height: auto; 
                                        object-fit: contain; display: block; margin: 0 auto;
                                        touch-action: pan-x pan-y; -webkit-user-select: none; user-select: none;
                                        -webkit-touch-callout: none;">
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add event listener to close button (mobile-friendly)
        const closeBtn = modal.querySelector('#closeMediaViewerBtn');
        if (closeBtn) {
            // Handle both click and touch events for mobile
            const handleClose = function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Reset visual state
                closeBtn.style.transform = 'scale(1)';
                closeBtn.style.background = '#dc3545';
                closeMediaViewer();
            };
            
            closeBtn.addEventListener('click', handleClose, { passive: false });
            closeBtn.addEventListener('touchend', handleClose, { passive: false });
            
            // Touch feedback
            closeBtn.addEventListener('touchstart', function(e) {
                this.style.transform = 'scale(0.9)';
                this.style.background = '#c82333';
            }, { passive: true });
            
            // Desktop hover effects
            if (!isMobile) {
                closeBtn.addEventListener('mouseover', function() {
                    this.style.transform = 'scale(1.1)';
                    this.style.background = '#c82333';
                });
                closeBtn.addEventListener('mouseout', function() {
                    this.style.transform = 'scale(1)';
                    this.style.background = '#dc3545';
                });
            }
        }
        
        // Show modal immediately for mobile
        if (isMobile) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            modal.setAttribute('aria-modal', 'true');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '9998';
            document.body.appendChild(backdrop);
            modal.backdrop = backdrop;
        } else {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
        
        // Close on ESC key (desktop)
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                closeMediaViewer();
            }
        };
        document.addEventListener('keydown', escHandler);
        modal.escHandler = escHandler;
        
        // Close on backdrop tap/click (mobile friendly)
        modal.addEventListener('click', function(e) {
            // Don't close if clicking on close button or image
            if (e.target.id === 'closeMediaViewerBtn' || e.target.closest('#closeMediaViewerBtn') || 
                e.target.id === 'viewerImage' || e.target.closest('#viewerImage')) {
                return;
            }
            // Close if clicking on backdrop or modal body
            if (e.target === modal || e.target.classList.contains('modal-dialog') || 
                e.target.classList.contains('modal-body')) {
                closeMediaViewer();
            }
        });
        
        // Prevent image drag on mobile
        const img = modal.querySelector('#viewerImage');
        if (img) {
            img.addEventListener('dragstart', function(e) {
                e.preventDefault();
            });
        }
        
        // Remove modal from DOM after closing
        modal.addEventListener('hidden.bs.modal', function() {
            cleanupMediaViewer();
        });
        
        // Store cleanup function
        modal.cleanup = cleanupMediaViewer;
    }
}

// Close media viewer
function closeMediaViewer() {
    const modal = document.getElementById('mediaViewerModal');
    if (modal) {
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile) {
            // Mobile: manual cleanup
            modal.style.display = 'none';
            modal.classList.remove('show');
            if (modal.backdrop) {
                modal.backdrop.remove();
            }
            cleanupMediaViewer();
        } else {
            // Desktop: use Bootstrap modal
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            } else {
                cleanupMediaViewer();
            }
        }
    }
}

// Cleanup media viewer
function cleanupMediaViewer() {
    const modal = document.getElementById('mediaViewerModal');
    if (modal) {
        // Remove event listeners
        if (modal.escHandler) {
            document.removeEventListener('keydown', modal.escHandler);
        }
        
        // Remove backdrop
        if (modal.backdrop) {
            modal.backdrop.remove();
        }
        
        // Remove modal
        if (document.body.contains(modal)) {
            document.body.removeChild(modal);
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
}

// Close location options modal
function closeLocationModal() {
    const modalElement = document.getElementById('locationOptionsModal');
    if (!modalElement) return;
    
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: Manual close
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.setAttribute('aria-modal', 'false');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    } else {
        // Desktop: Use Bootstrap modal
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        } else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
        }
    }
}

// Show location options modal
function showLocationOptions() {
    if (!currentChatUserId) {
        alert('Please select a user to share location');
        return;
    }
    
    const modalElement = document.getElementById('locationOptionsModal');
    if (!modalElement) return;
    
    // Ensure modal is visible on mobile
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: Show modal directly
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        
        // Create backdrop
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '9999';
            document.body.appendChild(backdrop);
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    } else {
        // Desktop: Use Bootstrap modal
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
    
    // Add event listener to close button (mobile-friendly)
    const closeBtn = document.getElementById('closeLocationModalBtn');
    if (closeBtn) {
        // Remove existing listeners to avoid duplicates
        const newCloseBtn = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
        
        // Handle both click and touch events for mobile
        const handleClose = function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeLocationModal();
        };
        
        newCloseBtn.addEventListener('click', handleClose, { passive: false });
        newCloseBtn.addEventListener('touchend', handleClose, { passive: false });
    }
}

// Close contact selector modal
function closeContactModal() {
    const modalElement = document.getElementById('contactSelectorModal');
    if (!modalElement) return;
    
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: Manual close
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.setAttribute('aria-modal', 'false');
        
        // Remove backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    } else {
        // Desktop: Use Bootstrap modal
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        } else {
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
        }
    }
}

// Show contact selector modal
function showContactSelector() {
    if (!currentChatUserId) {
        alert('Please select a user to share contact');
        return;
    }
    
    const modalElement = document.getElementById('contactSelectorModal');
    if (!modalElement) return;
    
    const contactList = document.getElementById('contactSelectorList');
    if (!contactList) return;
    
    // Get all users from the users list
    const userCards = document.querySelectorAll('.user-card');
    if (userCards.length === 0) {
        alert('No contacts available to share');
        return;
    }
    
    // Build contact list HTML
    let contactsHTML = '';
    userCards.forEach(function(card) {
        const userId = card.getAttribute('data-user-id');
        const userName = card.querySelector('h6')?.textContent?.trim() || '';
        const userEmail = card.querySelector('small')?.textContent?.replace(/^[^\w@]+/, '').trim() || '';
        
        if (!userId || !userName) return;
        
        // Get profile picture or first letter
        const profileImg = card.querySelector('img');
        const profilePic = profileImg ? profileImg.src : '';
        const firstLetter = userName.charAt(0).toUpperCase();
        
        contactsHTML += `
            <div class="list-group-item list-group-item-action p-3" 
                 onclick="shareContact(${userId}, '${userName.replace(/'/g, "\\'")}', '${userEmail.replace(/'/g, "\\'")}')"
                 style="cursor: pointer; border: none; border-bottom: 1px solid #e9edef;">
                <div class="d-flex align-items-center">
                    ${profilePic ? 
                        `<img src="${profilePic}" alt="${userName}" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">` :
                        `<div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                            <span class="text-white fw-bold" style="font-size: 1.1rem;">${firstLetter}</span>
                        </div>`
                    }
                    <div class="flex-grow-1">
                        <h6 class="mb-0" style="color: #111b21; font-weight: 500;">${userName}</h6>
                        <small class="text-muted">${userEmail}</small>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </div>
            </div>
        `;
    });
    
    contactList.innerHTML = contactsHTML;
    
    // Ensure modal is visible on mobile
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Mobile: Show modal directly
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        
        // Create backdrop
        let backdrop = document.querySelector('.modal-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '9999';
            document.body.appendChild(backdrop);
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    } else {
        // Desktop: Use Bootstrap modal
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
    
    // Add event listener to close button (mobile-friendly)
    const closeBtn = document.getElementById('closeContactModalBtn');
    if (closeBtn) {
        // Remove existing listeners to avoid duplicates
        const newCloseBtn = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
        
        // Handle both click and touch events for mobile
        const handleClose = function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeContactModal();
        };
        
        newCloseBtn.addEventListener('click', handleClose, { passive: false });
        newCloseBtn.addEventListener('touchend', handleClose, { passive: false });
    }
}

// Share contact
function shareContact(contactUserId, contactName, contactEmail) {
    // Close modal
    closeContactModal();
    
    if (!currentChatUserId) {
        alert('Please select a user to share contact');
        return;
    }
    
    // Create contact message (WhatsApp style)
    const contactMessage = `👤 Contact\nName: ${contactName}\nEmail: ${contactEmail}`;
    
    // Send contact
    fetch('{{ route("api.chat.send") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            receiver_id: currentChatUserId,
            message: contactMessage,
            type: 'contact',
            contact_user_id: contactUserId,
            contact_name: contactName,
            contact_email: contactEmail
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            moveUserToTop(currentChatUserId);
            loadChatMessages(currentChatUserId, false);
            
            // Clear input
            const input = document.getElementById('chatMessageInput');
            if (input) {
                input.value = '';
            }
        } else {
            alert(data.message || 'Failed to share contact');
        }
    })
    .catch(error => {
        console.error('Error sharing contact:', error);
        alert('Error sharing contact. Please try again.');
    });
}

// Share current location
let isCurrentLocationSending = false; // Flag to prevent duplicate sends

function shareCurrentLocation() {
    // Prevent multiple simultaneous calls
    if (isCurrentLocationSending) {
        return;
    }
    
    // Close modal
    closeLocationModal();
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    isCurrentLocationSending = true;
    
    // Show loading
    const input = document.getElementById('chatMessageInput');
    if (input) {
        input.disabled = true;
        input.placeholder = 'Getting location...';
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            
            // Create location message
            const locationMessage = `📍 Location\nLatitude: ${latitude.toFixed(6)}\nLongitude: ${longitude.toFixed(6)}`;
            const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;
            
            // Send location
            fetch('{{ route("api.chat.send") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: currentChatUserId,
                    message: locationMessage,
                    type: 'location',
                    latitude: latitude,
                    longitude: longitude,
                    location_url: googleMapsUrl
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    moveUserToTop(currentChatUserId);
                    loadChatMessages(currentChatUserId, false);
                } else {
                    alert(data.message || 'Failed to send location');
                }
            })
            .catch(error => {
                console.error('Error sending location:', error);
                alert('Error sending location. Please try again.');
            })
            .finally(() => {
                isCurrentLocationSending = false;
                if (input) {
                    input.disabled = false;
                    input.placeholder = 'Type a message';
                }
            });
        },
        function(error) {
            console.error('Geolocation error:', error);
            let errorMessage = 'Failed to get location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += 'Location access denied by user.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += 'Location information unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMessage += 'Location request timed out.';
                    break;
                default:
                    errorMessage += 'Unknown error occurred.';
                    break;
            }
            alert(errorMessage);
            
            isCurrentLocationSending = false;
            if (input) {
                input.disabled = false;
                input.placeholder = 'Type a message';
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

// Share live location (real-time location sharing)
let liveLocationInterval = null;
let liveLocationWatchId = null;
let liveLocationTimeout = null;
let isLocationSending = false; // Flag to prevent duplicate sends
let lastLocationSent = null; // Track last sent location
let liveLocationStartTime = null; // Track when live location started
let liveLocationDuration = null; // Track duration in milliseconds

function shareLiveLocation() {
    // Prevent multiple simultaneous calls
    if (liveLocationWatchId !== null) {
        alert('Live location is already being shared. Please stop it first.');
        return;
    }
    
    // Close modal
    closeLocationModal();
    
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    // Ask for duration
    const duration = prompt('Share live location for how many minutes? (1-60)', '15');
    if (!duration || isNaN(duration) || duration < 1 || duration > 60) {
        return;
    }
    
    const durationMinutes = parseInt(duration);
    const durationMs = durationMinutes * 60 * 1000;
    
    // Store start time and duration
    liveLocationStartTime = Date.now();
    liveLocationDuration = durationMs;
    
    // Show loading
    const input = document.getElementById('chatMessageInput');
    if (input) {
        input.disabled = true;
        input.placeholder = 'Starting live location...';
    }
    
    let locationCount = 0;
    let currentPosition = null;
    let hasSentFirstLocation = false;
    let lastSendTime = 0; // Track last send time to prevent rapid sends
    const MIN_SEND_INTERVAL = 25000; // Minimum 25 seconds between sends (slightly less than 30s interval)
    
    // Function to check if time has expired
    const isTimeExpired = function() {
        if (!liveLocationStartTime || !liveLocationDuration) {
            return false;
        }
        const elapsed = Date.now() - liveLocationStartTime;
        return elapsed >= liveLocationDuration;
    };
    
    // Function to send location
    const sendLocationUpdate = function() {
        // Check if time has expired
        if (isTimeExpired()) {
            stopLiveLocation();
            if (input) {
                input.disabled = false;
                input.placeholder = 'Type a message';
            }
            return;
        }
        
        if (!currentPosition || isLocationSending) {
            return;
        }
        
        // Prevent rapid sends - check minimum interval
        const now = Date.now();
        if (now - lastSendTime < MIN_SEND_INTERVAL && locationCount > 0) {
            return;
        }
        
        const latitude = currentPosition.coords.latitude;
        const longitude = currentPosition.coords.longitude;
        
        // Prevent duplicate sends with same coordinates (within 1 second)
        if (lastLocationSent && 
            Math.abs(lastLocationSent.latitude - latitude) < 0.000001 && 
            Math.abs(lastLocationSent.longitude - longitude) < 0.000001 &&
            (now - lastSendTime) < 1000) {
            return;
        }
        
        locationCount++;
        lastSendTime = now;
        
        // Calculate remaining time
        const elapsed = Date.now() - liveLocationStartTime;
        const remaining = Math.max(0, liveLocationDuration - elapsed);
        const remainingMinutes = Math.ceil(remaining / 60000);
        
        // Create location message
        const locationMessage = `📍 Live Location (${remainingMinutes} min)\nLatitude: ${latitude.toFixed(6)}\nLongitude: ${longitude.toFixed(6)}`;
        const googleMapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;
        
        isLocationSending = true;
        
        // Send location update
        fetch('{{ route("api.chat.send") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: currentChatUserId,
                message: locationMessage,
                type: 'location',
                latitude: latitude,
                longitude: longitude,
                location_url: googleMapsUrl,
                is_live: true
            })
        })
        .then(response => response.json())
        .then(data => {
            isLocationSending = false;
            if (data.success) {
                if (!hasSentFirstLocation) {
                    // First location sent - mark immediately to prevent duplicates
                    hasSentFirstLocation = true;
                    firstLocationSendInitiated = false; // Reset flag
                    moveUserToTop(currentChatUserId);
                    // Don't reload messages immediately to prevent duplicate display
                    setTimeout(() => {
                        loadChatMessages(currentChatUserId, false);
                    }, 1000);
                }
                lastLocationSent = { latitude, longitude };
            } else {
                // Reset flag if send failed
                if (!hasSentFirstLocation) {
                    firstLocationSendInitiated = false;
                }
            }
        })
        .catch(error => {
            console.error('Error sending live location:', error);
            isLocationSending = false;
            // Reset flag if send failed
            if (!hasSentFirstLocation) {
                firstLocationSendInitiated = false;
            }
        });
    };
    
    // Watch position for live updates (only to get current position, not to send)
    let watchPositionFirstCall = true; // Track if this is the first call
    let firstLocationSendInitiated = false; // Track if first send has been initiated
    liveLocationWatchId = navigator.geolocation.watchPosition(
        function(position) {
            // Check if time expired
            if (isTimeExpired()) {
                stopLiveLocation();
                return;
            }
            
            // Store current position but don't send immediately
            currentPosition = position;
            
            // Send first location immediately (only once, on first position update)
            // Use multiple flags to prevent race conditions
            if (watchPositionFirstCall && !hasSentFirstLocation && !isLocationSending && !firstLocationSendInitiated) {
                watchPositionFirstCall = false;
                firstLocationSendInitiated = true; // Set immediately to prevent duplicate calls
                // Use setTimeout to ensure flag is set before async operation
                setTimeout(() => {
                    sendLocationUpdate();
                }, 100);
            }
        },
        function(error) {
            console.error('Geolocation error:', error);
            stopLiveLocation();
            alert('Failed to share live location. Please try again.');
            if (input) {
                input.disabled = false;
                input.placeholder = 'Type a message';
            }
        },
        {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        }
    );
    
    // Update location every 30 seconds (controlled sending)
    liveLocationInterval = setInterval(() => {
        // Check if time expired before sending
        if (isTimeExpired()) {
            stopLiveLocation();
            if (input) {
                input.disabled = false;
                input.placeholder = 'Type a message';
            }
            return;
        }
        
        // Only send if:
        // 1. We have a position
        // 2. Not currently sending
        // 3. First location has been sent
        // 4. Enough time has passed since last send
        const now = Date.now();
        if (currentPosition && !isLocationSending && hasSentFirstLocation && (now - lastSendTime >= MIN_SEND_INTERVAL)) {
            sendLocationUpdate();
        }
    }, 30000);
    
    // Stop after duration (backup timeout)
    liveLocationTimeout = setTimeout(() => {
        stopLiveLocation();
        if (input) {
            input.disabled = false;
            input.placeholder = 'Type a message';
        }
    }, durationMs);
    
    if (input) {
        input.disabled = false;
        input.placeholder = `Sharing live location (${durationMinutes} min)...`;
    }
}

// Stop live location sharing
function stopLiveLocation() {
    if (liveLocationWatchId !== null) {
        navigator.geolocation.clearWatch(liveLocationWatchId);
        liveLocationWatchId = null;
    }
    
    if (liveLocationInterval) {
        clearInterval(liveLocationInterval);
        liveLocationInterval = null;
    }
    
    if (liveLocationTimeout) {
        clearTimeout(liveLocationTimeout);
        liveLocationTimeout = null;
    }
    
    // Reset all flags and variables
    isLocationSending = false;
    lastLocationSent = null;
    liveLocationStartTime = null;
    liveLocationDuration = null;
    
    const input = document.getElementById('chatMessageInput');
    if (input) {
        input.disabled = false;
        input.placeholder = 'Type a message';
    }
}

// Initiate video call from chat
function initiateVideoCallFromChat() {
    if (!currentChatUserId) return;
    const userName = document.getElementById('chatUserName').textContent;
    initiateCall(currentChatUserId, userName);
}

// Initiate audio call
function initiateAudioCall(userId, userName) {
    const shouldCall = window.innerWidth > 768 ? confirm('Audio call ' + userName + '?') : true;
    if (!shouldCall) return;
    
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
            // Stop call checking
            if (callCheckInterval) {
                clearInterval(callCheckInterval);
            }
            // Redirect to audio call page
            window.location.href = '{{ route("audio.call") }}?room=' + encodeURIComponent(data.room_id);
        } else {
            alert(data.message || 'Failed to initiate audio call');
        }
    })
    .catch(error => {
        console.error('Error initiating audio call:', error);
        alert('Error starting audio call. Please try again.');
    });
}

// Initiate audio call from chat
function initiateAudioCallFromChat() {
    if (!currentChatUserId) return;
    const userName = document.getElementById('chatUserName').textContent;
    initiateAudioCall(currentChatUserId, userName);
}

// Open chat menu
function openChatMenu() {
    // Can add more options here
    alert('More options coming soon!');
}

// Camera functionality
let cameraStream = null;
let currentFacingMode = 'user'; // 'user' for front, 'environment' for back

// Override openCamera function from app.blade.php
window.openCamera = function() {
    const cameraModalElement = document.getElementById('cameraModal');
    if (!cameraModalElement) {
        alert('Camera modal not found. Please refresh the page.');
        return;
    }
    
    const cameraModal = new bootstrap.Modal(cameraModalElement);
    cameraModal.show();
    
    // Reset UI
    document.getElementById('cameraLoading').style.display = 'block';
    document.getElementById('cameraError').style.display = 'none';
    document.getElementById('cameraVideo').style.display = 'none';
    document.getElementById('capturePhotoBtn').style.display = 'none';
    document.getElementById('switchCameraBtn').style.display = 'none';
    
    // Start camera
    startCamera();
}

function startCamera() {
    const video = document.getElementById('cameraVideo');
    const constraints = {
        video: {
            facingMode: currentFacingMode,
            width: { ideal: 1280 },
            height: { ideal: 720 }
        },
        audio: false
    };
    
    navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            cameraStream = stream;
            video.srcObject = stream;
            video.play();
            
            // Show video and controls
            document.getElementById('cameraLoading').style.display = 'none';
            document.getElementById('cameraError').style.display = 'none';
            video.style.display = 'block';
            document.getElementById('capturePhotoBtn').style.display = 'flex';
            
            // Show switch camera button only if multiple cameras available
            navigator.mediaDevices.enumerateDevices().then(devices => {
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                if (videoDevices.length > 1) {
                    document.getElementById('switchCameraBtn').style.display = 'flex';
                }
            });
        })
        .catch(error => {
            console.error('Camera error:', error);
            document.getElementById('cameraLoading').style.display = 'none';
            document.getElementById('cameraError').style.display = 'block';
            
            let errorMessage = 'Camera access denied or not available.';
            if (error.name === 'NotAllowedError') {
                errorMessage = 'Camera permission denied. Please allow camera access in your browser settings.';
            } else if (error.name === 'NotFoundError') {
                errorMessage = 'No camera found on this device.';
            } else if (error.name === 'NotReadableError') {
                errorMessage = 'Camera is being used by another application.';
            }
            
            document.getElementById('cameraErrorMessage').textContent = errorMessage;
        });
}

function switchCamera() {
    // Stop current stream
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
    
    // Switch facing mode
    currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    
    // Restart camera
    document.getElementById('cameraLoading').style.display = 'block';
    document.getElementById('cameraVideo').style.display = 'none';
    startCamera();
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const context = canvas.getContext('2d');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Convert to blob and download
    canvas.toBlob(blob => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'photo_' + new Date().getTime() + '.jpg';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        
        // Show success feedback
        const btn = document.getElementById('capturePhotoBtn');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg fs-3 text-dark"></i>';
        btn.style.background = '#25d366';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '';
        }, 500);
    }, 'image/jpeg', 0.95);
}

function closeCamera() {
    // Stop camera stream
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    
    // Hide modal
    const cameraModal = bootstrap.Modal.getInstance(document.getElementById('cameraModal'));
    if (cameraModal) {
        cameraModal.hide();
    }
    
    // Reset video
    const video = document.getElementById('cameraVideo');
    video.srcObject = null;
}

// Open scanner/QR code
function openScanner() {
    alert('QR Scanner feature coming soon!');
    // Can implement QR scanner functionality here
}

// Cleanup camera when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const cameraModal = document.getElementById('cameraModal');
    if (cameraModal) {
        cameraModal.addEventListener('hidden.bs.modal', function() {
            closeCamera();
        });
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (chatMessagesInterval) {
        clearInterval(chatMessagesInterval);
    }
    // Stop live location if running
    stopLiveLocation();
    // Stop camera if running
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endsection
