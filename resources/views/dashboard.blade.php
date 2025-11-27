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
            <div class="bg-white border-top p-2" id="chatInputArea" style="display: none;">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm rounded-circle" style="width: 40px; height: 40px; background: #f0f2f5; border: none; color: #54656f;">
                        <i class="bi bi-emoji-smile"></i>
                    </button>
                    <input type="text" class="form-control rounded-pill border-0" id="chatMessageInput" 
                           placeholder="Type a message" 
                           style="background: #f0f2f5; padding: 0.5rem 1rem;"
                           onkeypress="handleChatKeyPress(event)">
                    <button class="btn btn-sm rounded-circle" onclick="sendChatMessage()" 
                            style="width: 40px; height: 40px; background: #008069; border: none; color: white;">
                        <i class="bi bi-send-fill"></i>
                    </button>
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
            <div class="col-4 text-center py-2" onclick="showUsersList()" style="cursor: pointer;">
                <div class="d-flex flex-column align-items-center">
                     <i class="bi bi-chat-square-fill fs-5 text-primary mb-1" id="usersNavIcon"></i>
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
    // Initialize Bootstrap modals
    incomingCallModal = new bootstrap.Modal(document.getElementById('incomingCallModal'));
    window.profileEditModal = new bootstrap.Modal(document.getElementById('profileEditModal'));
    
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
        // Show modal for incoming calls automatically (badge removed, but modal still works)
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
                     style="max-width: 70%; padding: 8px 12px; border-radius: 8px; ${isSent ? 'background: #dcf8c6; margin-left: auto;' : 'background: white;'}">
                    <div class="message-text" style="word-wrap: break-word; font-size: 0.9rem;">
                        ${escapeHtml(msg.message)}
                    </div>
                    <div class="d-flex align-items-center justify-content-end mt-1" style="gap: 4px;">
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

// Send chat message
function sendChatMessage() {
    const input = document.getElementById('chatMessageInput');
    if (!input || !input.value.trim() || !currentChatUserId) return;
    
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
            // Move user to top of list (without refresh)
            moveUserToTop(currentChatUserId);
            
            // Reload messages to show tick marks - always scroll when sending
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

// Handle Enter key in chat input
function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
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
    // Stop camera if running
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endsection
