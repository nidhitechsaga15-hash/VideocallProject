@extends('layouts.app')

@section('title', 'Audio Call - Video Call App')

@section('content')
<style>
/* Hide navbar on video call page for mobile */
@media (max-width: 768px) {
    .navbar {
        display: none !important;
    }
    body {
        padding-top: 0 !important;
    }
}
</style>
<div class="container-fluid p-0" style="height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: fixed; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; z-index: 1;">
    <div class="position-relative w-100 h-100" style="height: 100vh;">
        <!-- Single User View (when 1-2 users) -->
        <div id="singleUserView" class="position-relative w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="height: 100vh;">
            <!-- User Avatar/Profile -->
            @if(isset($otherUser))
            <div class="text-center mb-4">
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center mx-auto mb-3 shadow-lg" 
                     style="width: 150px; height: 150px; background: rgba(255,255,255,0.2) !important;">
                    @if($otherUser->profile_picture && file_exists(public_path('storage/profiles/' . $otherUser->profile_picture)))
                        <img src="{{ asset('storage/profiles/' . $otherUser->profile_picture) }}" 
                             alt="{{ $otherUser->name }}" 
                             class="rounded-circle w-100 h-100" 
                             style="object-fit: cover;">
                    @else
                        <span class="fw-bold text-white" style="font-size: 4rem;">{{ strtoupper(substr($otherUser->name, 0, 1)) }}</span>
                    @endif
                </div>
                <h3 class="text-white mb-1" id="otherUserName">{{ $otherUser->name }}</h3>
                <p class="text-white-50 mb-0" id="callStatus">Connecting...</p>
                <div id="callTimer" style="font-size: 0.75rem; display: none; margin-top: 2px; color: rgba(255,255,255,0.8);">
                    <i class="bi bi-clock me-1"></i><span id="callTimerText">00:00</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Multiple Users Grid View (when 3+ users) -->
        <div id="multiUserView" class="position-relative w-100 h-100 d-none" style="height: 100vh; padding: 15px; padding-bottom: 120px;">
            <!-- Call Timer for Multi-User View -->
            <div class="position-absolute top-0 start-0 p-2 p-md-3 text-white" style="background: rgba(0,0,0,0.7); border-radius: 0 0 10px 0; z-index: 10; max-width: 80%;">
                <div id="callTimerMulti" style="font-size: 0.9rem; display: none; color: rgba(255,255,255,0.9); font-weight: 500;">
                    <i class="bi bi-clock me-1"></i><span id="callTimerTextMulti">00:00</span>
                </div>
            </div>
            <div class="row g-2 h-100" id="participantsGrid" style="max-height: calc(100vh - 150px);">
                <!-- Participants will be dynamically added here -->
            </div>
        </div>
        
        <!-- Audio Only - No Video -->
        <audio id="remoteAudio" autoplay></audio>
        <audio id="localAudio" autoplay muted></audio>
        <!-- Multiple remote audio elements for group calls -->
        <div id="remoteAudiosContainer"></div>

        <!-- Call Info - Mobile Responsive -->
        @if(isset($otherUser))
        <div class="position-absolute top-0 start-0 p-2 p-md-3 text-white" style="background: rgba(0,0,0,0.7); border-radius: 0 0 10px 0; z-index: 10; max-width: 80%;">
            <h6 class="mb-0" style="font-size: 0.9rem;">
                <i class="bi bi-person-circle me-1"></i><span id="otherUserName">{{ $otherUser->name }}</span>
            </h6>
            <small id="callStatus" style="font-size: 0.75rem;">Connecting...</small>
            <div id="callTimer" style="font-size: 0.75rem; display: none; margin-top: 2px; color: rgba(255,255,255,0.8);">
                <i class="bi bi-clock me-1"></i><span id="callTimerText">00:00</span>
            </div>
        </div>
        @endif

        <!-- Call Disconnected Overlay -->
        <div id="callDisconnectedOverlay" class="position-absolute top-0 start-0 end-0 bottom-0 d-none align-items-center justify-content-center" 
             style="background: rgba(0,0,0,0.9); z-index: 1000;">
            <div class="text-center text-white p-5">
                <div class="mb-4">
                    <i class="bi bi-telephone-x-fill" style="font-size: 80px; color: #dc3545;"></i>
                </div>
                <h3 class="mb-3">Call Disconnected</h3>
                <p class="mb-4" id="disconnectMessage">{{ isset($otherUser) ? $otherUser->name : 'Other user' }} ended the call</p>
                <button class="btn btn-primary btn-lg" onclick="goToDashboard()">
                    <i class="bi bi-house-door me-2"></i>Return to Dashboard
                </button>
            </div>
        </div>

        <!-- Call Controls - Mobile Responsive -->
        <div class="position-absolute bottom-0 start-0 end-0 p-3 p-md-4" id="callControlsContainer" style="background: rgba(0,0,0,0.8); z-index: 10; padding-bottom: calc(15px + env(safe-area-inset-bottom)) !important;">
            <div class="d-flex justify-content-center align-items-center gap-3 gap-md-4" style="width: 100%;">
                <!-- Mute/Unmute Audio -->
                <button id="toggleAudio" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                    <i class="bi bi-mic-fill" id="micIcon" style="font-size: 1.3rem; color: #333;"></i>
                    <i class="bi bi-mic-mute-fill d-none" id="micOffIcon" style="font-size: 1.3rem; color: #dc3545;"></i>
                </button>

                <!-- Speaker Toggle -->
                <button id="toggleSpeaker" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 12px rgba(0,0,0,0.4); transition: all 0.3s ease; background: #f0f0f0;" title="Speaker: OFF">
                    <i class="fa fa-volume-up d-none" id="speakerIcon" style="font-size: 1.4rem; color: white; display: none; font-weight: bold; text-shadow: 0 1px 3px rgba(0,0,0,0.3);"></i>
                    <i class="fa fa-volume-off" id="speakerOffIcon" style="font-size: 1.4rem; color: #666; display: block; font-weight: bold;"></i>
                </button>

                <!-- Add User Button -->
                <button id="addUserBtn" class="btn btn-success rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.3);" title="Add User">
                    <i class="bi bi-person-plus-fill" style="font-size: 1.3rem; color: white;"></i>
                </button>

                <!-- End Call -->
                <button id="endCall" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                    <i class="bi bi-telephone-fill" style="font-size: 1.3rem; color: white;"></i>
                </button>
            </div>
        </div>
        
        <!-- Error Message -->
        <div id="errorMessage" class="position-absolute top-50 start-50 translate-middle text-white text-center p-4 d-none" 
             style="background: rgba(220, 53, 69, 0.95); border-radius: 15px; z-index: 1000; max-width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block"></i>
            <h5 id="errorTitle" class="mb-3">Microphone Access Error</h5>
            <p id="errorText" class="mb-4" style="white-space: pre-line; line-height: 1.6;">Please allow microphone permissions</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-light btn-sm" onclick="retryMicrophoneAccess()" style="min-width: 100px;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Retry
                </button>
                <button class="btn btn-outline-light btn-sm" onclick="goToDashboard()" style="min-width: 100px;">
                    <i class="bi bi-house-door me-1"></i>Go Back
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="background: #f0f2f5;">
            <div class="modal-header" style="background: #008069; color: white; border-bottom: none;">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>Add User to Call
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 60vh; overflow-y: auto;">
                <div id="addUserList" class="list-group list-group-flush">
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading users...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: white; border-top: 1px solid #e9edef;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
// Setup getUserMedia polyfill FIRST - before anything else
(function() {
    // Check if we need to setup getUserMedia
    if (!navigator.mediaDevices) {
        navigator.mediaDevices = {};
    }
    
    // If getUserMedia already exists, use it
    if (navigator.mediaDevices.getUserMedia && typeof navigator.mediaDevices.getUserMedia === 'function') {
        // Already available, no need to setup
        return;
    }
    
    // Try to get getUserMedia from various sources (check all possible locations)
    let getUserMedia = null;
    
    // Check modern API first
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        getUserMedia = navigator.mediaDevices.getUserMedia;
    }
    // Check legacy APIs
    else if (navigator.getUserMedia) {
        getUserMedia = navigator.getUserMedia;
    }
    else if (navigator.webkitGetUserMedia) {
        getUserMedia = navigator.webkitGetUserMedia;
    }
    else if (navigator.mozGetUserMedia) {
        getUserMedia = navigator.mozGetUserMedia;
    }
    else if (navigator.msGetUserMedia) {
        getUserMedia = navigator.msGetUserMedia;
    }
    
    if (getUserMedia) {
        // If it's already a function (modern API), use it directly
        if (typeof getUserMedia === 'function' && getUserMedia === navigator.mediaDevices.getUserMedia) {
            // Already set, do nothing
            return;
        }
        
        // Create polyfill for navigator.mediaDevices.getUserMedia
        navigator.mediaDevices.getUserMedia = function(constraints) {
            return new Promise(function(resolve, reject) {
                // Handle both modern Promise-based and legacy callback-based APIs
                if (typeof getUserMedia === 'function') {
                    if (getUserMedia.length === 1) {
                        // Modern Promise-based API
                        getUserMedia(constraints).then(resolve).catch(reject);
                    } else {
                        // Legacy callback-based API
                        getUserMedia.call(navigator, constraints, resolve, reject);
                    }
                } else {
                    reject(new Error('getUserMedia function is not available'));
                }
            });
        };
    } else {
        // If getUserMedia is not found, don't create a rejecting function
        // Instead, let the actual call handle the error
        // This allows the browser to show its own error message
        console.warn('getUserMedia not found in any standard location. Browser may not support it.');
    }
})();

// Check if elements exist
const localAudio = document.getElementById('localAudio');
const remoteAudio = document.getElementById('remoteAudio');
const toggleAudio = document.getElementById('toggleAudio');
const toggleSpeaker = document.getElementById('toggleSpeaker');
// Video toggle removed for audio call
const endCall = document.getElementById('endCall');
const callStatus = document.getElementById('callStatus');
const errorMessage = document.getElementById('errorMessage');
const errorTitle = document.getElementById('errorTitle');
const errorText = document.getElementById('errorText');

let localStream = null;
let peerConnection = null;
let peerConnections = new Map(); // For multiple users
let remoteAudioElements = new Map(); // Map<userId, audioElement>
let isAudioEnabled = true;
let isVideoEnabled = true;
let isSpeakerEnabled = true; // Default to speaker mode
let callStatusInterval = null;
let callTimerInterval = null;
let callStartTime = null;
let isCallActive = false;
let allParticipants = []; // Store all participants data
let previousParticipants = []; // Store previous participants for comparison
const roomId = new URLSearchParams(window.location.search).get('room') || '{{ $roomId ?? "" }}';
const otherUserName = '{{ isset($otherUser) ? $otherUser->name : "" }}';
const currentUserId = {{ Auth::id() }};

// WebRTC Configuration
const configuration = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ]
};

// Check if getUserMedia is supported
function checkMediaSupport() {
    // First check if it's mobile - mobile browsers should always be allowed to try
    const userAgent = navigator.userAgent || navigator.vendor || window.opera || '';
    const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile/i.test(userAgent.toLowerCase());
    
    // For mobile devices, always allow - let getUserMedia handle the actual error
    if (isMobile) {
        // Try to setup getUserMedia if not available
        if (!navigator.mediaDevices) {
            navigator.mediaDevices = {};
        }
        
        if (!navigator.mediaDevices.getUserMedia) {
            // Try to get fallback getUserMedia
            const getUserMedia = navigator.getUserMedia || 
                                navigator.webkitGetUserMedia || 
                                navigator.mozGetUserMedia || 
                                navigator.msGetUserMedia;
            
            if (getUserMedia) {
                // Create polyfill
                navigator.mediaDevices.getUserMedia = function(constraints) {
                    return new Promise(function(resolve, reject) {
                        getUserMedia.call(navigator, constraints, resolve, reject);
                    });
                };
            } else {
                // Even if not found, return true for mobile - let init() handle the error
                // Mobile browsers might need HTTPS or have different API
                return true;
            }
        }
        
        // Mobile browser - always allow
        return true;
    }
    
    // For desktop, check properly
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        return true;
    }
    
    // Fallback for older desktop browsers
    const getUserMedia = navigator.getUserMedia || 
                        navigator.webkitGetUserMedia || 
                        navigator.mozGetUserMedia || 
                        navigator.msGetUserMedia;
    
    if (getUserMedia) {
        // Polyfill for older browsers
        if (!navigator.mediaDevices) {
            navigator.mediaDevices = {};
        }
        navigator.mediaDevices.getUserMedia = function(constraints) {
            return new Promise(function(resolve, reject) {
                getUserMedia.call(navigator, constraints, resolve, reject);
            });
        };
        return true;
    }
    
    // Desktop browser not supported (audio call)
    showError('Browser Not Supported', 'Your browser does not support audio calling. Please use Chrome, Firefox, or Safari.');
    return false;
}

// Show error message
function showError(title, message) {
    if (errorMessage && errorTitle && errorText) {
        errorTitle.textContent = title;
        errorText.textContent = message;
        errorMessage.classList.remove('d-none');
    } else {
        alert(title + ': ' + message);
    }
}

// Hide error message
function hideError() {
    if (errorMessage) {
        errorMessage.classList.add('d-none');
    }
}

// Retry microphone access
function retryMicrophoneAccess() {
    hideError();
    // Stop any existing stream
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
    }
    // Retry initialization
    setTimeout(() => {
        init();
    }, 500);
}

// Initialize
async function init() {
    // Check media support (mobile browsers will always pass this check)
    if (!checkMediaSupport()) {
        return;
    }
    
    // For mobile, don't check again - just try to use getUserMedia
    const userAgent = navigator.userAgent || navigator.vendor || window.opera || '';
    const isMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile/i.test(userAgent.toLowerCase());
    
    // Only check for desktop browsers
    if (!isMobile) {
        // Double check navigator.mediaDevices exists for desktop
        if (!navigator || !navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            showError('Browser Not Supported', 'Your browser does not support microphone access. Please use Chrome, Firefox, or Safari.');
            return;
        }
    }
    
    try {
        // Ensure navigator.mediaDevices exists (should be set by polyfill above)
        if (!navigator || !navigator.mediaDevices) {
            navigator.mediaDevices = {};
        }
        
        // Check if getUserMedia function exists
        if (typeof navigator.mediaDevices.getUserMedia !== 'function') {
            // Try to setup again - check all possible locations
            let getUserMedia = null;
            
            // Check modern API
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                getUserMedia = navigator.mediaDevices.getUserMedia;
            }
            // Check legacy APIs
            else if (navigator.getUserMedia) {
                getUserMedia = navigator.getUserMedia;
            }
            else if (navigator.webkitGetUserMedia) {
                getUserMedia = navigator.webkitGetUserMedia;
            }
            else if (navigator.mozGetUserMedia) {
                getUserMedia = navigator.mozGetUserMedia;
            }
            else if (navigator.msGetUserMedia) {
                getUserMedia = navigator.msGetUserMedia;
            }
            
            if (getUserMedia && typeof getUserMedia === 'function') {
                navigator.mediaDevices.getUserMedia = function(constraints) {
                    return new Promise(function(resolve, reject) {
                        if (getUserMedia.length === 1) {
                            // Modern Promise-based API
                            getUserMedia(constraints).then(resolve).catch(reject);
                        } else {
                            // Legacy callback-based API
                            getUserMedia.call(navigator, constraints, resolve, reject);
                        }
                    });
                };
            } else {
                // If still not found, try to use it directly and let browser handle error
                // Don't throw error here - let the actual getUserMedia call handle it
                console.error('getUserMedia not found. Browser may not support microphone access.');
            }
        }
        
        // Audio only - no video (explicitly set to false)
        const constraints = {
            video: false,
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        };
        
        // Ensure video is explicitly false
        if (constraints.video !== false) {
            constraints.video = false;
        }
        
        console.log('Audio call constraints:', constraints);
        
        // Try to get user media (audio only)
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        
        // Verify no video tracks
        const videoTracks = localStream.getVideoTracks();
        if (videoTracks && videoTracks.length > 0) {
            console.warn('Video tracks found in audio call, stopping them');
            videoTracks.forEach(track => track.stop());
        }
        
        // Check if stream was received
        if (!localStream) {
            throw new Error('Failed to get audio stream');
        }
        
        // Check if stream has tracks
        if (!localStream.getTracks || localStream.getTracks().length === 0) {
            throw new Error('No audio tracks available');
        }
        
        // Set audio source (no video for audio call)
        if (localAudio) {
            try {
                localAudio.srcObject = localStream;
                await localAudio.play();
            } catch (playError) {
                console.error('Audio play error:', playError);
                localAudio.setAttribute('autoplay', 'true');
            }
        }
        
        if (callStatus) callStatus.textContent = 'Connected';
        hideError();
        
        // Create peer connection
        createPeerConnection();
        
        // Add only audio tracks to peer connection (no video)
        if (localStream && peerConnection) {
            try {
                const audioTracks = localStream.getAudioTracks();
                audioTracks.forEach(track => {
                    if (track && peerConnection) {
                        peerConnection.addTrack(track, localStream);
                        console.log('Audio track added:', track.kind, track.enabled);
                    }
                });
                
                // Ensure no video tracks are added
                const videoTracks = localStream.getVideoTracks();
                if (videoTracks && videoTracks.length > 0) {
                    console.warn('Video tracks found, removing them');
                    videoTracks.forEach(track => track.stop());
                }
            } catch (trackError) {
                console.error('Error adding tracks:', trackError);
            }
        }
        
        // Check call status before starting connection
        // Only start connection if call is accepted (for receiver) or if user is caller
        checkCallStatusBeforeConnect();
        
    } catch (error) {
        console.error('Error accessing media devices:', error);
        console.error('Error details:', {
            name: error?.name,
            message: error?.message,
            stack: error?.stack,
            navigator: !!navigator,
            mediaDevices: !!navigator?.mediaDevices,
            getUserMedia: typeof navigator?.mediaDevices?.getUserMedia
        });
        
        let errorTitle = 'Microphone Access Error';
        let errorMsg = 'Please allow microphone permissions to use audio calling.';
        
        // Handle different error types
        if (error) {
            const errorName = error.name || '';
            const errorMessage = error.message || '';
            
            // Check for TypeError or undefined errors
            if (errorName === 'TypeError' || errorMessage.includes('undefined') || errorMessage.includes('is not a function') || errorMessage.includes('getUserMedia is not available')) {
                // Check if it's an HTTPS issue
                const isHTTPS = window.location.protocol === 'https:';
                const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
                const isLocalNetwork = /^192\.168\.|^10\.|^172\.(1[6-9]|2[0-9]|3[0-1])\./.test(window.location.hostname);
                
                // Try to setup getUserMedia again
                const getUserMedia = navigator.getUserMedia || 
                                    navigator.webkitGetUserMedia || 
                                    navigator.mozGetUserMedia ||
                                    navigator.msGetUserMedia;
                
                if (getUserMedia) {
                    // Setup polyfill
                    if (!navigator.mediaDevices) {
                        navigator.mediaDevices = {};
                    }
                    navigator.mediaDevices.getUserMedia = function(constraints) {
                        return new Promise(function(resolve, reject) {
                            if (getUserMedia.length === 1) {
                                getUserMedia(constraints).then(resolve).catch(reject);
                            } else {
                                getUserMedia.call(navigator, constraints, resolve, reject);
                            }
                        });
                    };
                    // Retry after setup
                    setTimeout(() => {
                        retryMicrophoneAccess();
                    }, 500);
                    return;
                } else {
                    // getUserMedia not available - check if it's HTTPS issue
                    if (!isHTTPS && !isLocalhost && isMobile) {
                        errorTitle = 'HTTPS Required';
                        errorMsg = 'Microphone access requires HTTPS on mobile browsers.\n\nSolutions:\n1. Use HTTPS (recommended)\n2. Use localhost instead of IP\n3. Enable "Insecure origins" in Chrome flags\n\nFor testing, you can:\n- Use http://localhost:8000 instead\n- Or setup HTTPS certificate';
                    } else {
                        errorTitle = 'Browser Not Supported';
                        errorMsg = 'Your browser does not support microphone access.\n\nPlease:\n1. Use Chrome, Firefox, or Safari\n2. Make sure browser is up to date\n3. Try refreshing the page\n4. Check browser permissions';
                    }
                }
            } else if (errorName === 'NotAllowedError' || errorName === 'PermissionDeniedError') {
                errorTitle = 'Permission Denied';
                errorMsg = 'Microphone permission was denied. Please:\n\n1. Click on the lock/info icon in address bar\n2. Allow microphone\n3. Refresh the page';
            } else if (errorName === 'NotFoundError' || errorName === 'DevicesNotFoundError') {
                errorTitle = 'Device Not Found';
                errorMsg = 'No microphone found on your device. Please connect a microphone.';
            } else if (errorName === 'NotReadableError' || errorName === 'TrackStartError') {
                errorTitle = 'Device Busy';
                errorMsg = 'Microphone is already in use by another application. Please close other apps using microphone.';
            } else if (errorName === 'OverconstrainedError') {
                errorTitle = 'Microphone Settings Error';
                errorMsg = 'Requested microphone settings are not available. Trying with default settings...';
                // Retry with simpler constraints
                setTimeout(() => {
                    retryWithSimpleConstraints();
                }, 1000);
                return;
            } else if (errorMessage) {
                errorMsg = errorMessage;
            }
        }
        
        if (callStatus) {
            callStatus.textContent = 'Error: ' + (error.name || 'Unknown');
        }
        showError(errorTitle, errorMsg);
    }
}

// Retry with simpler constraints
async function retryWithSimpleConstraints() {
    try {
        hideError();
        
        // Ensure navigator.mediaDevices exists
        if (!navigator || !navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            throw new Error('getUserMedia is not available');
        }
        
        // Audio only - no video
        // Audio only - no video
        const simpleConstraints = {
            video: false,
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            }
        };
        
        localStream = await navigator.mediaDevices.getUserMedia(simpleConstraints);
        
        // Verify no video tracks
        const videoTracks = localStream.getVideoTracks();
        if (videoTracks && videoTracks.length > 0) {
            console.warn('Video tracks found in audio call, stopping them');
            videoTracks.forEach(track => track.stop());
        }
        
        // For audio call, use audio element instead of video
        if (localAudio && localStream) {
            localAudio.srcObject = localStream;
            await localAudio.play();
        }
        
        if (callStatus) callStatus.textContent = 'Connected';
        hideError();
        
        createPeerConnection();
        
        // Add only audio tracks (no video)
        if (localStream && peerConnection) {
            const audioTracks = localStream.getAudioTracks();
            audioTracks.forEach(track => {
                if (track && track.kind === 'audio') {
                    peerConnection.addTrack(track, localStream);
                }
            });
        }
        
        simulateRemoteConnection();
    } catch (retryError) {
        console.error('Retry error:', retryError);
        showError('Microphone Access Error', 'Failed to access microphone. Please check browser permissions and try again.');
    }
}

function createPeerConnection() {
    if (!window.RTCPeerConnection) {
        showError('WebRTC Not Supported', 'Your browser does not support WebRTC. Please use a modern browser.');
        return;
    }
    
    try {
        peerConnection = new RTCPeerConnection(configuration);
        
        // Handle remote stream
        peerConnection.ontrack = (event) => {
            if (remoteAudio && event.streams && event.streams[0]) {
                remoteAudio.srcObject = event.streams[0];
                remoteAudio.play().catch(e => console.error('Remote audio play error:', e));
                if (callStatus) callStatus.textContent = 'In Call';
                // Start call timer when call is connected
                setTimeout(() => startCallTimer(), 500); // Small delay to ensure elements are ready
            }
        };
        
        // Handle ICE candidates
        peerConnection.onicecandidate = async (event) => {
            if (event.candidate) {
                console.log('ICE candidate:', event.candidate);
                // Send ICE candidate to server
                try {
                    await fetch('{{ route("api.webrtc.ice") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            room_id: roomId,
                            candidate: JSON.stringify(event.candidate)
                        })
                    });
                } catch (error) {
                    console.error('Error sending ICE candidate:', error);
                }
            } else {
                console.log('ICE gathering complete');
            }
        };
        
        // Handle ICE connection state
        peerConnection.oniceconnectionstatechange = () => {
            if (peerConnection) {
                console.log('ICE connection state:', peerConnection.iceConnectionState);
                if (callStatus) {
                    if (peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed') {
                        callStatus.textContent = 'Connected';
                        // Start call timer when connection is established
                        if (!isCallActive || !callTimerInterval) {
                            setTimeout(() => startCallTimer(), 500); // Small delay to ensure elements are ready
                        }
                    } else if (peerConnection.iceConnectionState === 'checking') {
                        callStatus.textContent = 'Connecting...';
                    } else if (peerConnection.iceConnectionState === 'failed') {
                        callStatus.textContent = 'Connection Failed';
                        stopCallTimer();
                    }
                }
            }
        };
        
        // Handle connection state changes
        peerConnection.onconnectionstatechange = () => {
            if (callStatus && peerConnection) {
                const state = peerConnection.connectionState;
                callStatus.textContent = state.charAt(0).toUpperCase() + state.slice(1);
                
                if (state === 'failed' || state === 'disconnected') {
                    showError('Connection Lost', 'The connection was lost. Please try again.');
                }
            }
        };
    } catch (error) {
        console.error('Error creating peer connection:', error);
        showError('Connection Error', 'Failed to establish connection. Please try again.');
    }
}

// WebRTC Signaling - Exchange offers and answers
let signalingInterval = null;
let iceCheckInterval = null;
let isOfferer = false;
let isAnswerer = false;
let offerSent = false;
let answerSent = false;
let processedIceCandidates = new Set();

// Start signaling process
async function startSignaling() {
    if (!roomId || !peerConnection || !localStream) return;
    
    // Wait a bit for both users to be ready
    setTimeout(async () => {
        await initiateWebRTCConnection();
    }, 2000);
    
    // Start polling for offers/answers
    signalingInterval = setInterval(async () => {
        await checkAndExchangeSignals();
    }, 2000);
}

// Initiate WebRTC connection - create offer
async function initiateWebRTCConnection() {
    if (!peerConnection || !localStream || isOfferer || isAnswerer) return;
    
    try {
        // Determine who should be offerer (caller creates offer)
        const callRequest = await fetch('{{ route("api.call.status") }}?room_id=' + roomId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(r => r.json());
        
        // For simplicity, first user to create offer becomes offerer
        // In production, you'd use a more sophisticated approach
        if (!offerSent && !answerSent) {
            // Create offer
            const offer = await peerConnection.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: true
            });
            await peerConnection.setLocalDescription(offer);
            
            // Send offer to server
            await fetch('{{ route("api.webrtc.offer") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    room_id: roomId,
                    offer: JSON.stringify(offer)
                })
            });
            
            isOfferer = true;
            offerSent = true;
            if (callStatus) callStatus.textContent = 'Connecting...';
            console.log('Offer created and sent');
        }
    } catch (error) {
        console.error('Error creating offer:', error);
    }
}

// Check for offers/answers and exchange signals
async function checkAndExchangeSignals() {
    if (!peerConnection || !roomId) return;
    
    try {
        // If we're offerer, check for answer
        if (isOfferer && !answerSent) {
            const response = await fetch('{{ route("api.webrtc.getAnswer") }}?room_id=' + roomId, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.answer) {
                const answer = JSON.parse(data.answer);
                await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                answerSent = true;
                console.log('Answer received and set');
                if (callStatus) callStatus.textContent = 'Connecting...';
                
                // Start checking for ICE candidates
                if (!iceCheckInterval) {
                    iceCheckInterval = setInterval(checkIceCandidates, 2000);
                }
            }
        }
        // If we're not offerer yet, check for offer
        else if (!isOfferer && !offerSent) {
            const response = await fetch('{{ route("api.webrtc.getOffer") }}?room_id=' + roomId, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            if (data.offer) {
                const offer = JSON.parse(data.offer);
                await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
                
                // Create answer
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                
                // Send answer to server
                await fetch('{{ route("api.webrtc.answer") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        room_id: roomId,
                        answer: JSON.stringify(answer)
                    })
                });
                
                isAnswerer = true;
                offerSent = true;
                answerSent = true;
                console.log('Offer received, answer created and sent');
                if (callStatus) callStatus.textContent = 'Connecting...';
                
                // Start checking for ICE candidates
                if (!iceCheckInterval) {
                    iceCheckInterval = setInterval(checkIceCandidates, 2000);
                }
            }
        }
    } catch (error) {
        console.error('Error in signaling:', error);
    }
}

// Check and add ICE candidates
async function checkIceCandidates() {
    if (!peerConnection || !roomId) return;
    
    try {
        const response = await fetch('{{ route("api.webrtc.getIce") }}?room_id=' + roomId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.candidates && data.candidates.length > 0) {
            for (const candidateStr of data.candidates) {
                // Avoid processing same candidate twice
                if (processedIceCandidates.has(candidateStr)) {
                    continue;
                }
                
                try {
                    const candidate = JSON.parse(candidateStr);
                    await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                    processedIceCandidates.add(candidateStr);
                    console.log('ICE candidate added');
                } catch (error) {
                    // Candidate might already be added, ignore
                    if (!error.message.includes('already been added')) {
                        console.error('Error adding ICE candidate:', error);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error checking ICE candidates:', error);
    }
}

// Check call status before connecting
async function checkCallStatusBeforeConnect() {
    if (!roomId) {
        console.log('No room ID, starting connection anyway');
        simulateRemoteConnection();
        return;
    }
    
    try {
        const response = await fetch('{{ route("api.call.status") }}?room_id=' + roomId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        console.log('Call Status Check:', {
            status: data.status,
            other_user_ready: data.other_user_ready,
            roomId: roomId
        });
        
        // Only start connection if:
        // 1. Call is accepted (status === 'accepted')
        // 2. Or if other user is ready (for caller)
        // 3. Or if status is not 'pending' (call already in progress)
        if (data.status === 'accepted' || data.other_user_ready || data.status !== 'pending') {
            simulateRemoteConnection();
        } else {
            // Call is pending - wait for acceptance
            if (callStatus) callStatus.textContent = 'Waiting for call acceptance...';
            console.log('Call is pending, waiting for acceptance');
            
            // Check again after 2 seconds
            setTimeout(checkCallStatusBeforeConnect, 2000);
        }
    } catch (error) {
        console.error('Error checking call status:', error);
        // If error, start connection anyway (fallback)
        simulateRemoteConnection();
    }
}

// Simulate remote connection - start signaling
function simulateRemoteConnection() {
    if (callStatus) callStatus.textContent = 'Waiting for other participant...';
    
    // Start signaling check
    startSignaling();
}

// Toggle Audio
if (toggleAudio) {
    toggleAudio.addEventListener('click', () => {
        if (localStream) {
            const audioTrack = localStream.getAudioTracks()[0];
            if (audioTrack) {
                audioTrack.enabled = !audioTrack.enabled;
                isAudioEnabled = audioTrack.enabled;
                
                const micIcon = document.getElementById('micIcon');
                const micOffIcon = document.getElementById('micOffIcon');
                if (micIcon) micIcon.classList.toggle('d-none', !isAudioEnabled);
                if (micOffIcon) micOffIcon.classList.toggle('d-none', isAudioEnabled);
            }
        }
    });
}

// Initialize Speaker Button State
function initializeSpeakerButton() {
    const toggleSpeaker = document.getElementById('toggleSpeaker');
    const speakerIcon = document.getElementById('speakerIcon');
    const speakerOffIcon = document.getElementById('speakerOffIcon');
    
    if (toggleSpeaker && speakerIcon && speakerOffIcon) {
        // Set initial state to OFF (earpiece mode) - like 1st screenshot
        speakerIcon.classList.add('d-none');
        speakerIcon.style.display = 'none';
        speakerIcon.style.color = 'white';
        speakerIcon.style.textShadow = '0 1px 3px rgba(0,0,0,0.3)';
        speakerOffIcon.classList.remove('d-none');
        speakerOffIcon.style.display = 'block';
        speakerOffIcon.style.color = '#666';
        toggleSpeaker.style.background = '#f0f0f0'; // White/gray background when OFF
        toggleSpeaker.style.border = '2px solid rgba(255,255,255,0.3)';
        toggleSpeaker.style.boxShadow = '0 4px 12px rgba(0,0,0,0.4)';
        toggleSpeaker.title = 'Speaker: OFF';
        isSpeakerEnabled = false; // Start with OFF state
    }
}

// Initialize on page load
initializeSpeakerButton();

// Speaker Toggle
if (toggleSpeaker && remoteAudio) {
    toggleSpeaker.addEventListener('click', async () => {
        try {
            isSpeakerEnabled = !isSpeakerEnabled;
            
            const speakerIcon = document.getElementById('speakerIcon');
            const speakerOffIcon = document.getElementById('speakerOffIcon');
            
            if (isSpeakerEnabled) {
                // Enable speaker (default output) - ON
                if (remoteAudio.setSinkId) {
                    try {
                        await remoteAudio.setSinkId('');
                    } catch (e) {
                        console.log('setSinkId not supported, using default');
                    }
                }
                // Show volume-up icon (ON state - white icon on green background) - like 2nd screenshot
                if (speakerIcon) {
                    speakerIcon.classList.remove('d-none');
                    speakerIcon.style.display = 'block';
                    speakerIcon.style.color = 'white'; // White icon when ON
                    speakerIcon.style.textShadow = '0 1px 3px rgba(0,0,0,0.3)';
                    speakerIcon.style.fontWeight = 'bold';
                }
                if (speakerOffIcon) {
                    speakerOffIcon.classList.add('d-none');
                    speakerOffIcon.style.display = 'none';
                }
                // Change button background to show active state - light green like 2nd screenshot
                toggleSpeaker.style.background = '#dcf8c6'; // Light green background when ON
                toggleSpeaker.style.border = '2px solid rgba(37, 211, 102, 0.5)';
                toggleSpeaker.style.boxShadow = '0 4px 16px rgba(37, 211, 102, 0.4)';
                toggleSpeaker.title = 'Speaker: ON';
            } else {
                // Disable speaker (earpiece mode) - OFF
                if (remoteAudio.setSinkId) {
                    try {
                        // Try to get available audio devices
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        const earpieceDevice = devices.find(d => d.kind === 'audiooutput' && d.label.toLowerCase().includes('earpiece'));
                        if (earpieceDevice) {
                            await remoteAudio.setSinkId(earpieceDevice.deviceId);
                        }
                    } catch (e) {
                        console.log('Could not set earpiece, using default');
                    }
                }
                // Show volume-off icon (OFF state - gray icon on white/gray background) - like 1st screenshot
                if (speakerIcon) {
                    speakerIcon.classList.add('d-none');
                    speakerIcon.style.display = 'none';
                }
                if (speakerOffIcon) {
                    speakerOffIcon.classList.remove('d-none');
                    speakerOffIcon.style.display = 'block';
                    speakerOffIcon.style.color = '#666'; // Gray icon when OFF
                    speakerOffIcon.style.fontWeight = 'bold';
                }
                // Change button background to show inactive state - white/gray like 1st screenshot
                toggleSpeaker.style.background = '#f0f0f0'; // White/gray background when OFF
                toggleSpeaker.style.border = '2px solid rgba(255,255,255,0.3)';
                toggleSpeaker.style.boxShadow = '0 4px 12px rgba(0,0,0,0.4)';
                toggleSpeaker.title = 'Speaker: OFF';
            }
        } catch (error) {
            console.error('Error toggling speaker:', error);
        }
    });
}

// Video toggle removed for audio call - audio only

// End Call
if (endCall) {
    endCall.addEventListener('click', () => {
        // Mobile par confirm dialog skip karein, directly end
        const shouldEnd = window.innerWidth > 768 ? confirm('End the call?') : true;
        if (!shouldEnd) return;
        
        // End call in database
        if (roomId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                // If it's a group call, also call leaveGroupCall
                const promises = [];
                if (roomId.startsWith('group_')) {
                    promises.push(
                        fetch('{{ route("api.group.call.leave") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken.content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                room_id: roomId
                            })
                        }).catch(err => console.error('Error leaving group call:', err))
                    );
                }
                
                promises.push(
                    fetch('{{ route("api.call.end") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken.content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            room_id: roomId
                        })
                    })
                );
                
                Promise.all(promises).then((responses) => {
                    // Check if it's a group call and if there are still active participants
                    if (roomId.startsWith('group_') && responses[0]) {
                        responses[0].json().then(data => {
                            if (data.success && data.active_participants > 0) {
                                // Other participants still in call, just redirect to dashboard
                                window.location.href = '{{ route("dashboard") }}';
                            } else {
                                // No active participants, show disconnected
                                showCallDisconnected('You');
                            }
                        }).catch(() => {
                            // On error, just redirect
                            window.location.href = '{{ route("dashboard") }}';
                        });
                    } else {
                        // One-to-one call or error, show disconnected
                        showCallDisconnected('You');
                    }
                }).catch(err => {
                    console.error('Error ending call:', err);
                    // For group calls, just redirect to dashboard
                    if (roomId && roomId.startsWith('group_')) {
                        window.location.href = '{{ route("dashboard") }}';
                    } else {
                        showCallDisconnected('You');
                    }
                });
            } else {
                showCallDisconnected('You');
            }
        } else {
            // If no room ID, just disconnect
            showCallDisconnected('You');
        }
    });
}

// Check call status periodically
function startCallStatusCheck() {
    if (!roomId) return;
    
    callStatusInterval = setInterval(() => {
        fetch('{{ route("api.call.status") }}?room_id=' + roomId, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ended' || data.status === 'rejected') {
                showCallDisconnected(data.other_user?.name || otherUserName);
            }
        })
        .catch(error => {
            console.error('Error checking call status:', error);
        });
    }, 2000); // Check every 2 seconds
}

// Start call timer
function startCallTimer() {
    if (isCallActive && callTimerInterval) return; // Already started
    
    isCallActive = true;
    isCallInProgress = true; // Mark call as in progress
    if (window.setRefreshBlocked) {
        window.setRefreshBlocked(true); // Block refresh
    }
    callStartTime = Date.now();
    
    // Show timer - check all possible timer elements
    const callTimer = document.getElementById('callTimer');
    const callTimerText = document.getElementById('callTimerText');
    const callTimerMulti = document.getElementById('callTimerMulti');
    const callTimerTextMulti = document.getElementById('callTimerTextMulti');
    
    console.log('Starting call timer...', { callTimer, callTimerText, callTimerMulti, callTimerTextMulti });
    
    if (callTimer) {
        callTimer.style.display = 'block';
        callTimer.style.visibility = 'visible';
    }
    if (callTimerMulti) {
        callTimerMulti.style.display = 'block';
        callTimerMulti.style.visibility = 'visible';
    }
    
    // Clear any existing interval
    if (callTimerInterval) {
        clearInterval(callTimerInterval);
    }
    
    // Update timer every second
    callTimerInterval = setInterval(() => {
        if (callStartTime) {
            const elapsed = Math.floor((Date.now() - callStartTime) / 1000);
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = elapsed % 60;
            
            let timeString;
            if (hours > 0) {
                timeString = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            } else {
                timeString = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            
            if (callTimerText) {
                callTimerText.textContent = timeString;
            }
            if (callTimerTextMulti) {
                callTimerTextMulti.textContent = timeString;
            }
        }
    }, 1000);
    
    // Force initial update
    if (callStartTime && callTimerText) {
        callTimerText.textContent = '00:00';
    }
    if (callStartTime && callTimerTextMulti) {
        callTimerTextMulti.textContent = '00:00';
    }
}

// Stop call timer
function stopCallTimer() {
    isCallActive = false;
    isCallInProgress = false; // Mark call as ended
    if (window.setRefreshBlocked) {
        window.setRefreshBlocked(false); // Allow refresh
    }
    callStartTime = null;
    
    if (callTimerInterval) {
        clearInterval(callTimerInterval);
        callTimerInterval = null;
    }
    
    const callTimer = document.getElementById('callTimer');
    const callTimerMulti = document.getElementById('callTimerMulti');
    if (callTimer) {
        callTimer.style.display = 'none';
    }
    if (callTimerMulti) {
        callTimerMulti.style.display = 'none';
    }
    
    const callTimerText = document.getElementById('callTimerText');
    const callTimerTextMulti = document.getElementById('callTimerTextMulti');
    if (callTimerText) {
        callTimerText.textContent = '00:00';
    }
    if (callTimerTextMulti) {
        callTimerTextMulti.textContent = '00:00';
    }
}

// Show call disconnected message
function showCallDisconnected(userName) {
    // Stop call timer
    stopCallTimer();
    
    // Stop all media
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    if (peerConnection) {
        peerConnection.close();
    }
    
    // Stop status checking
    if (callStatusInterval) {
        clearInterval(callStatusInterval);
    }
    
    // Hide videos
    // Audio call - no video elements to hide
    
    // Show disconnected overlay
    const overlay = document.getElementById('callDisconnectedOverlay');
    const message = document.getElementById('disconnectMessage');
    if (overlay) {
        if (message) {
            message.textContent = userName + ' ended the call';
        }
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');
    }
}

// Go to dashboard
function goToDashboard() {
    window.location.href = '{{ route("dashboard") }}';
}

// Mobile viewport fix
function fixMobileViewport() {
    // Set viewport height for mobile
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
    
    // Audio call - no video container to adjust
}

// Add User to Call functionality
let addUserModal = null;
let availableUsers = [];
let currentParticipants = []; // Track current participants

// Load users for Add User modal
async function loadUsersForAdd() {
    try {
        const response = await fetch('{{ route("api.users") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        availableUsers = data.users || [];
        renderUserList();
    } catch (error) {
        console.error('Error loading users:', error);
        document.getElementById('addUserList').innerHTML = '<div class="text-center p-4 text-danger">Error loading users. Please try again.</div>';
    }
}

// Render user list in modal
function renderUserList() {
    const userList = document.getElementById('addUserList');
    
    if (availableUsers.length === 0) {
        userList.innerHTML = '<div class="text-center p-4 text-muted">No users available to add.</div>';
        return;
    }
    
    // Show all users, but mark added ones
    const filteredUsers = availableUsers.filter(user => user.id !== {{ Auth::id() }});
    
    if (filteredUsers.length === 0) {
        userList.innerHTML = '<div class="text-center p-4 text-muted">No users available to add.</div>';
        return;
    }
    
    userList.innerHTML = filteredUsers.map(user => {
        const isAdded = currentParticipants.includes(user.id);
        return `
        <div class="list-group-item p-3 user-item-add ${isAdded ? 'user-added' : ''}" 
             ${!isAdded ? `onclick="addUserToCall(${user.id}, '${user.name.replace(/'/g, "\\'")}')" style="cursor: pointer;"` : 'style="opacity: 0.6; cursor: not-allowed;"'}
             data-user-id="${user.id}"
             style="background: white; border-bottom: 1px solid #e9edef; ${isAdded ? 'opacity: 0.6;' : ''}">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                     style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                    <span class="text-white fw-bold" style="font-size: 1.1rem;">${user.name.charAt(0).toUpperCase()}</span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0" style="color: #111b21; font-weight: 500;">${user.name}</h6>
                    <small class="text-muted">${user.email}</small>
                </div>
                ${isAdded 
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Added</span>' 
                    : '<i class="bi bi-plus-circle-fill text-success" style="font-size: 1.5rem;"></i>'
                }
            </div>
        </div>
    `;
    }).join('');
}

// Add user to call
async function addUserToCall(userId, userName) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        // Show loading
        const userItem = event.target.closest('.user-item-add');
        if (userItem) {
            userItem.innerHTML = '<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-primary"></div> <span class="ms-2">Adding...</span></div>';
        }
        
        // Call API to add user
        const response = await fetch('{{ route("api.call.addUser") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                room_id: roomId,
                user_id: userId,
                call_type: 'audio' // Audio call
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add to current participants (if not already added)
            if (!currentParticipants.includes(userId)) {
                currentParticipants.push(userId);
            }
            
            // Update the user item to show "Added" status
            if (userItem) {
                userItem.classList.add('user-added');
                userItem.style.opacity = '0.6';
                userItem.style.cursor = 'not-allowed';
                userItem.onclick = null; // Remove click handler
                
                // Update the content to show added status
                const userData = availableUsers.find(u => u.id === userId);
                if (userData) {
                    userItem.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" 
                                 style="width: 45px; height: 45px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                                <span class="text-white fw-bold" style="font-size: 1.1rem;">${userData.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0" style="color: #111b21; font-weight: 500;">${userData.name}</h6>
                                <small class="text-muted">${userData.email}</small>
                            </div>
                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Added</span>
                        </div>
                    `;
                }
                
                // Show success message (even if already joined)
                const message = data.already_joined 
                    ? `${userName} is already in the call.`
                    : `${userName} added to call! They will receive a notification.`;
                
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success alert-dismissible fade show position-fixed';
                successMsg.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 250px;';
                successMsg.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(successMsg);
                setTimeout(() => {
                    successMsg.remove();
                }, 3000);
            }
            
        } else {
            throw new Error(data.message || 'Failed to add user');
        }
    } catch (error) {
        console.error('Error adding user:', error);
        alert('Error adding user to call: ' + error.message);
        // Reload user list
        loadUsersForAdd();
    }
}

// Open Add User modal
const addUserBtn = document.getElementById('addUserBtn');
if (addUserBtn) {
    addUserBtn.addEventListener('click', function() {
        if (!addUserModal) {
            addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        }
        loadUsersForAdd();
        addUserModal.show();
    });
}

// Load and display participants
async function loadAndDisplayParticipants() {
    try {
        const response = await fetch(`{{ route("api.group.call.participants") }}?room_id=${roomId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.participants) {
                // Check if participant count changed
                const previousCount = allParticipants.length;
                const previousIds = previousParticipants.map(p => p.id);
                previousParticipants = [...allParticipants]; // Store previous before updating
                allParticipants = data.participants;
                currentParticipants = data.participants.map(p => p.id);
                
                // Stop waveform animations for removed participants
                previousIds.forEach(prevId => {
                    if (!currentParticipants.includes(prevId)) {
                        stopWaveform(prevId);
                    }
                });
                
                // If participants list is empty or less than 3, show single view
                if (data.participants.length === 0 || data.participants.length < 3) {
                    showSingleUserView();
                } else {
                    // Show multi-user view if 3+ participants
                    showMultiUserView(data.participants);
                }
                return;
            }
        }
    } catch (error) {
        console.log('Not a group call or error:', error);
    }
    
    // Fallback: Single user view
    showSingleUserView();
    currentParticipants = [{{ Auth::id() }}];
    @if(isset($otherUser))
    currentParticipants.push({{ $otherUser->id }});
    @endif
}

// Show single user view (1-2 users)
function showSingleUserView() {
    const singleView = document.getElementById('singleUserView');
    const multiView = document.getElementById('multiUserView');
    if (singleView) singleView.classList.remove('d-none');
    if (multiView) multiView.classList.add('d-none');
}

// Show multi-user grid view (3+ users)
function showMultiUserView(participants) {
    const singleView = document.getElementById('singleUserView');
    const multiView = document.getElementById('multiUserView');
    if (singleView) singleView.classList.add('d-none');
    if (multiView) multiView.classList.remove('d-none');
    
    const grid = document.getElementById('participantsGrid');
    if (!grid) return;
    
    grid.innerHTML = '';
    
    // Color array for borders (yellow, blue, red, green, etc.)
    const colors = ['#FFD700', '#4169E1', '#FF6347', '#32CD32', '#FF1493', '#00CED1', '#FF8C00', '#9370DB'];
    
    participants.forEach((participant, index) => {
        const isCurrentUser = participant.id === currentUserId;
        const color = colors[index % colors.length];
        const colClass = participants.length <= 4 ? 'col-6' : 'col-6 col-md-4';
        
        const participantCard = document.createElement('div');
        participantCard.className = `${colClass} participant-card`;
        participantCard.id = `participant-${participant.id}`;
        participantCard.style.cssText = `
            border: 3px solid ${color};
            border-radius: 12px;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            position: relative;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            margin-bottom: 8px;
        `;
        
        // Label at top
        const label = document.createElement('div');
        label.style.cssText = `color: ${color}; font-weight: bold; font-size: 0.9rem; margin-bottom: auto;`;
        label.textContent = isCurrentUser ? 'You' : participant.name;
        participantCard.appendChild(label);
        
        // Bottom section with avatar and waveform
        const bottomSection = document.createElement('div');
        bottomSection.style.cssText = 'display: flex; align-items: flex-end; gap: 10px; margin-top: auto;';
        
        // Avatar
        const avatar = document.createElement('div');
        avatar.className = 'rounded-circle d-flex align-items-center justify-content-center';
        avatar.style.cssText = `
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            flex-shrink: 0;
        `;
        
        if (participant.profile_picture) {
            const img = document.createElement('img');
            img.src = participant.profile_picture;
            img.className = 'rounded-circle w-100 h-100';
            img.style.objectFit = 'cover';
            avatar.appendChild(img);
        } else {
            const initial = document.createElement('span');
            initial.className = 'text-white fw-bold';
            initial.style.fontSize = '1.5rem';
            initial.textContent = participant.name.charAt(0).toUpperCase();
            avatar.appendChild(initial);
        }
        bottomSection.appendChild(avatar);
        
        // Audio waveform visualization
        const waveform = document.createElement('div');
        waveform.className = 'audio-waveform';
        waveform.id = `waveform-${participant.id}`;
        waveform.style.cssText = `
            flex: 1;
            height: 40px;
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 5px;
        `;
        
        // Create waveform bars
        for (let i = 0; i < 20; i++) {
            const bar = document.createElement('div');
            bar.className = 'waveform-bar';
            bar.style.cssText = `
                width: 3px;
                background: ${color};
                border-radius: 2px;
                transition: height 0.1s ease;
                height: ${Math.random() * 30 + 5}px;
            `;
            waveform.appendChild(bar);
        }
        bottomSection.appendChild(waveform);
        
        participantCard.appendChild(bottomSection);
        grid.appendChild(participantCard);
        
        // Animate waveform
        animateWaveform(participant.id, color);
    });
}

// Animate audio waveform
function animateWaveform(userId, color) {
    const waveform = document.getElementById(`waveform-${userId}`);
    if (!waveform) return;
    
    const bars = waveform.querySelectorAll('.waveform-bar');
    if (bars.length === 0) return;
    
    const interval = setInterval(() => {
        bars.forEach(bar => {
            const height = Math.random() * 35 + 5;
            bar.style.height = height + 'px';
            bar.style.background = color;
        });
    }, 150);
    
    // Store interval to clear later
    if (!window.waveformIntervals) window.waveformIntervals = new Map();
    window.waveformIntervals.set(userId, interval);
}

// Stop waveform animation
function stopWaveform(userId) {
    if (window.waveformIntervals && window.waveformIntervals.has(userId)) {
        clearInterval(window.waveformIntervals.get(userId));
        window.waveformIntervals.delete(userId);
    }
}

// Update participants list periodically
let participantsCheckInterval = null;
function startParticipantsCheck() {
    if (participantsCheckInterval) return;
    
    participantsCheckInterval = setInterval(async () => {
        await loadAndDisplayParticipants();
        
        // Update call status
        if (allParticipants.length >= 3) {
            const callStatus = document.getElementById('callStatus');
            if (callStatus) {
                callStatus.textContent = `Connected (${allParticipants.length} participants)`;
            }
        }
        
        // Fallback: Start timer if call is active but timer not started
        if (peerConnection && (peerConnection.connectionState === 'connected' || peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed')) {
            if (!isCallActive || !callTimerInterval) {
                console.log('Fallback: Starting timer as call seems connected');
                startCallTimer();
            }
        }
    }, 3000); // Check every 3 seconds
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', async function() {
    fixMobileViewport();
    init();
    startCallStatusCheck();
    initializeSpeakerButton(); // Initialize speaker button state
    
    // Load and display participants
    await loadAndDisplayParticipants();
    startParticipantsCheck();
    
    // Fallback: Start timer after 3 seconds if call is active (for cases where connection events might be missed)
    setTimeout(() => {
        if (peerConnection && (peerConnection.connectionState === 'connected' || peerConnection.iceConnectionState === 'connected' || peerConnection.iceConnectionState === 'completed')) {
            if (!isCallActive || !callTimerInterval) {
                console.log('Fallback: Starting timer as call seems connected');
                startCallTimer();
            }
        } else if (localStream && !isCallActive) {
            // If we have local stream but timer not started, start it anyway
            console.log('Fallback: Starting timer as local stream is active');
            startCallTimer();
        }
    }, 3000);
});

// Fix viewport on resize
window.addEventListener('resize', fixMobileViewport);
window.addEventListener('orientationchange', function() {
    setTimeout(fixMobileViewport, 100);
});

// Completely block page refresh during active call - NO DIALOG AT ALL
let isCallInProgress = false;

// Update call status
function updateCallStatus(active) {
    isCallInProgress = active;
}

// Block ALL refresh methods - COMPLETELY SILENT, NO DIALOG (Mobile + Web)
// Block browser refresh button, Ctrl+R, F5, etc.
(function() {
    let refreshBlocked = false;
    
    // Set refresh blocked flag when call starts
    window.setRefreshBlocked = function(blocked) {
        refreshBlocked = blocked;
    };
    
    // Block immediately when page loads (for audio call page)
    refreshBlocked = true;
    
    // Block beforeunload - NO DIALOG AT ALL - Blocks browser refresh button
    // Multiple listeners to ensure it works
    const blockRefresh = function(e) {
        if (refreshBlocked || isCallInProgress || isCallActive || localStream || peerConnection) {
            // CRITICAL: Prevent default and stop all propagation
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Explicitly remove returnValue if it exists
            if (e.returnValue !== undefined) {
                delete e.returnValue;
            }
            
            // Don't set returnValue - this prevents dialog
            e.returnValue = undefined;
            
            // Return nothing - this is the key to prevent dialog
            return;
        }
    };
    
    // Add multiple listeners in different phases
    window.addEventListener('beforeunload', blockRefresh, true); // Capture phase
    window.addEventListener('beforeunload', blockRefresh, false); // Bubble phase
    
    // Also block unload event
    window.addEventListener('unload', function(e) {
        if (refreshBlocked || isCallInProgress || isCallActive || localStream || peerConnection) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        }
    }, true);
    
    // Block pagehide event (mobile browsers)
    window.addEventListener('pagehide', function(e) {
        if (refreshBlocked || isCallInProgress || isCallActive || localStream || peerConnection) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        }
    }, true);
})();

// Block keyboard shortcuts for refresh (F5, Ctrl+R, Ctrl+Shift+R) - COMPLETE BLOCK
// This prevents refresh from even being triggered
document.addEventListener('keydown', function(e) {
    if (isCallInProgress || isCallActive || localStream || peerConnection) {
        // Block F5
        if (e.key === 'F5' || e.keyCode === 116 || e.which === 116) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        // Block Ctrl+R or Cmd+R
        if ((e.ctrlKey || e.metaKey) && (e.key === 'r' || e.key === 'R' || e.keyCode === 82 || e.which === 82)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        // Block Ctrl+Shift+R or Cmd+Shift+R (hard refresh)
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'R' || e.keyCode === 82 || e.which === 82)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
        // Block Ctrl+F5
        if (e.ctrlKey && (e.key === 'F5' || e.keyCode === 116 || e.which === 116)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }
}, { capture: true, passive: false });

// Block mobile refresh gestures (pull to refresh)
let touchStartY = 0;
document.addEventListener('touchstart', function(e) {
    if (isCallInProgress || isCallActive || localStream || peerConnection) {
        touchStartY = e.touches[0].clientY;
    }
}, { passive: true });

document.addEventListener('touchmove', function(e) {
    if (isCallInProgress || isCallActive || localStream || peerConnection) {
        // Block pull-to-refresh gesture
        if (window.scrollY === 0 && e.touches[0].clientY > touchStartY) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }
}, { passive: false });

// Block browser back button during call
window.addEventListener('popstate', function(e) {
    if (isCallInProgress || isCallActive || localStream || peerConnection) {
        // Push current state back immediately
        history.pushState(null, null, window.location.href);
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
}, true);

// Push state to prevent back button
history.pushState(null, null, window.location.href);

// Cleanup on page unload
window.addEventListener('unload', function() {
    stopCallTimer();
    
    if (callStatusInterval) {
        clearInterval(callStatusInterval);
    }
    if (signalingInterval) {
        clearInterval(signalingInterval);
    }
    if (iceCheckInterval) {
        clearInterval(iceCheckInterval);
    }
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    if (peerConnection) {
        peerConnection.close();
    }
});
</script>

<style>
/* Mobile specific styles */
@media (max-width: 768px) {
    #localVideoContainer {
        width: 100px !important;
        bottom: 70px !important;
        right: 10px !important;
    }
    
    /* Mobile: Audio call buttons */
    #toggleAudio, #toggleSpeaker, #endCall {
        width: 56px !important;
        height: 56px !important;
        min-width: 56px !important;
        min-height: 56px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
    }
    
    #callControlsContainer {
        padding: 25px 15px !important;
        padding-bottom: calc(25px + env(safe-area-inset-bottom)) !important;
        background: rgba(0,0,0,0.85) !important;
        bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    
    /* Mobile: Move buttons slightly up */
    @media (max-width: 768px) {
        #callControlsContainer {
            bottom: 20px !important;
            padding-bottom: calc(25px + env(safe-area-inset-bottom)) !important;
        }
    }
    
    #callControlsContainer .d-flex {
        gap: 40px !important;
    }
    
    /* Mobile: Button icons */
    #toggleAudio i,
    #endCall i {
        font-size: 1.3rem !important;
    }
    
    /* Mobile: Ensure buttons are properly centered */
    .position-absolute.bottom-0 {
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
    }
}

/* Prevent zoom on input focus (iOS) */
input, textarea, select {
    font-size: 16px !important;
}

/* Prevent pull-to-refresh on mobile */
body {
    overscroll-behavior-y: none !important;
    overscroll-behavior: none !important;
    touch-action: pan-y !important;
}

html {
    overscroll-behavior-y: none !important;
    overscroll-behavior: none !important;
}

/* Prevent bounce effect on iOS */
@supports (-webkit-touch-callout: none) {
    body {
        position: fixed !important;
        width: 100% !important;
        height: 100% !important;
        overflow: hidden !important;
    }
}

/* Multi-user grid view styles */
#multiUserView {
    overflow-y: auto;
    padding: 15px;
    padding-bottom: 150px;
}

#participantsGrid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.participant-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.participant-card:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.audio-waveform {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.waveform-bar {
    transition: height 0.1s ease;
    border-radius: 2px;
}

/* Mobile responsive for multi-user view */
@media (max-width: 768px) {
    #multiUserView {
        padding: 10px;
        padding-bottom: 140px;
    }
    
    .participant-card {
        min-height: 150px !important;
    }
    
    #participantsGrid .col-6 {
        padding: 4px;
    }
}
</style>
@endsection
