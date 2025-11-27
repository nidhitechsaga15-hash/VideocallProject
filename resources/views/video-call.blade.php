@extends('layouts.app')

@section('title', 'Video Call - Video Call App')

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
<div class="container-fluid p-0" style="height: 100vh; background: #000; position: fixed; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; z-index: 1;">
    <div class="position-relative w-100 h-100" style="height: 100vh;">
        <!-- Remote Video -->
        <video id="remoteVideo" autoplay playsinline class="w-100 h-100" style="object-fit: cover; width: 100%; height: 100%;"></video>
        
        <!-- Local Video (Picture-in-Picture) - Mobile Responsive -->
        <div class="position-absolute" id="localVideoContainer" style="top: 10px; right: 10px; width: 120px; max-width: 30%; z-index: 10;">
            <video id="localVideo" autoplay playsinline muted class="w-100 rounded shadow-lg border border-white" style="background: #000; width: 100%; height: auto;"></video>
        </div>

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
        <div id="callControls" class="position-fixed bottom-0 start-0 end-0" 
             style="background: rgba(0,0,0,0.6); z-index: 9999; padding: 20px 15px; padding-bottom: calc(20px + env(safe-area-inset-bottom));">
            <div class="d-flex justify-content-center align-items-center gap-3 gap-md-4 flex-wrap" style="max-width: 100%;">
                <!-- Mute/Unmute Audio -->
                <button id="toggleAudio" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center position-relative" 
                        style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border: none;">
                    <i class="bi bi-mic-fill" id="micIcon" style="font-size: 1.2rem; color: #333; position: absolute;"></i>
                    <i class="bi bi-mic-mute-fill d-none" id="micOffIcon" style="font-size: 1.2rem; color: #dc3545; position: absolute;"></i>
                </button>

                <!-- Mute/Unmute Video -->
                <button id="toggleVideo" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center position-relative" 
                        style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border: none;">
                    <i class="bi bi-camera-video-fill" id="videoIcon" style="font-size: 1.2rem; color: #333; position: absolute;"></i>
                    <i class="bi bi-camera-video-off-fill d-none" id="videoOffIcon" style="font-size: 1.2rem; color: #dc3545; position: absolute;"></i>
                </button>

                <!-- Speaker Toggle -->
                <button id="toggleSpeaker" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center position-relative" 
                        style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 12px rgba(0,0,0,0.4); transition: all 0.3s ease; background: #f0f0f0;" title="Speaker: OFF">
                    <i class="fa fa-volume-up" id="speakerIcon" style="font-size: 1.3rem; color: white; display: block; font-weight: bold; text-shadow: 0 1px 3px rgba(0,0,0,0.3); position: absolute; opacity: 0.3;"></i>
                    <i class="fa fa-volume-off" id="speakerOffIcon" style="font-size: 1.3rem; color: #666; display: block; font-weight: bold; position: absolute;"></i>
                </button>

                <!-- Switch Camera (Front/Back) -->
                <button id="switchCamera" class="btn btn-light rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border: none;" title="Switch Camera">
                    <i class="bi bi-arrow-repeat" style="font-size: 1.3rem; color: #333; font-weight: bold;"></i>
                </button>

                <!-- End Call -->
                <button id="endCall" class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center" 
                        style="width: 50px; height: 50px; min-width: 50px; min-height: 50px; border: none;">
                    <i class="bi bi-telephone-fill" style="font-size: 1.2rem; color: white;"></i>
                </button>
            </div>
        </div>
        
        <!-- Error Message -->
        <div id="errorMessage" class="position-absolute top-50 start-50 translate-middle text-white text-center p-4 d-none" 
             style="background: rgba(220, 53, 69, 0.95); border-radius: 15px; z-index: 1000; max-width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <i class="bi bi-exclamation-triangle-fill fs-1 mb-3 d-block"></i>
            <h5 id="errorTitle" class="mb-3">Camera Access Error</h5>
            <p id="errorText" class="mb-4" style="white-space: pre-line; line-height: 1.6;">Please allow camera and microphone permissions</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn btn-light btn-sm" onclick="retryCameraAccess()" style="min-width: 100px;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Retry
                </button>
                <button class="btn btn-outline-light btn-sm" onclick="goToDashboard()" style="min-width: 100px;">
                    <i class="bi bi-house-door me-1"></i>Go Back
                </button>
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
const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const toggleAudio = document.getElementById('toggleAudio');
const toggleVideo = document.getElementById('toggleVideo');
const toggleSpeaker = document.getElementById('toggleSpeaker');
const endCall = document.getElementById('endCall');
const callStatus = document.getElementById('callStatus');
const errorMessage = document.getElementById('errorMessage');
const errorTitle = document.getElementById('errorTitle');
const errorText = document.getElementById('errorText');

let localStream = null;
let peerConnection = null;
let isAudioEnabled = true;
let isVideoEnabled = true;
let isSpeakerEnabled = true; // Default to speaker mode
let callStatusInterval = null;
let currentFacingMode = 'user'; // 'user' for front camera, 'environment' for back camera
let callTimerInterval = null;
let callStartTime = null;
let isCallActive = false;
const roomId = new URLSearchParams(window.location.search).get('room') || '{{ $roomId ?? "" }}';
const otherUserName = '{{ isset($otherUser) ? $otherUser->name : "" }}';

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
    
    // Desktop browser not supported
    showError('Browser Not Supported', 'Your browser does not support video calling. Please use Chrome, Firefox, or Safari.');
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

// Retry camera access
function retryCameraAccess() {
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
            showError('Browser Not Supported', 'Your browser does not support camera access. Please use Chrome, Firefox, or Safari.');
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
                console.error('getUserMedia not found. Browser may not support camera access.');
            }
        }
        
        // Request camera and microphone permissions with simpler constraints for mobile
        const constraints = {
            video: {
                facingMode: currentFacingMode // Front camera by default
            },
            audio: true
        };
        
        // Try to get user media
        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        
        // Check if stream was received
        if (!localStream) {
            throw new Error('Failed to get media stream');
        }
        
        // Check if stream has tracks
        if (!localStream.getTracks || localStream.getTracks().length === 0) {
            throw new Error('No media tracks available');
        }
        
        // Set video source
        if (localVideo) {
            try {
                localVideo.srcObject = localStream;
                await localVideo.play();
            } catch (playError) {
                console.error('Video play error:', playError);
                // Try autoplay attribute
                localVideo.setAttribute('autoplay', 'true');
                localVideo.setAttribute('playsinline', 'true');
                localVideo.setAttribute('muted', 'true');
            }
        }
        
        if (callStatus) callStatus.textContent = 'Connected';
        hideError();
        
        // Create peer connection
        createPeerConnection();
        
        // Add local stream tracks to peer connection
        if (localStream && peerConnection) {
            try {
                localStream.getTracks().forEach(track => {
                    if (track && peerConnection) {
                        peerConnection.addTrack(track, localStream);
                    }
                });
            } catch (trackError) {
                console.error('Error adding tracks:', trackError);
            }
        }
        
        // For demo: simulate receiving remote stream
        simulateRemoteConnection();
        
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
        
        let errorTitle = 'Camera Access Error';
        let errorMsg = 'Please allow camera and microphone permissions to use video calling.';
        
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
                        retryCameraAccess();
                    }, 500);
                    return;
                } else {
                    // getUserMedia not available - check if it's HTTPS issue
                    if (!isHTTPS && !isLocalhost && isMobile) {
                        errorTitle = 'HTTPS Required';
                        errorMsg = 'Camera access requires HTTPS on mobile browsers.\n\nSolutions:\n1. Use HTTPS (recommended)\n2. Use localhost instead of IP\n3. Enable "Insecure origins" in Chrome flags\n\nFor testing, you can:\n- Use http://localhost:8000 instead\n- Or setup HTTPS certificate';
                    } else {
                        errorTitle = 'Browser Not Supported';
                        errorMsg = 'Your browser does not support camera access.\n\nPlease:\n1. Use Chrome, Firefox, or Safari\n2. Make sure browser is up to date\n3. Try refreshing the page\n4. Check browser permissions';
                    }
                }
            } else if (errorName === 'NotAllowedError' || errorName === 'PermissionDeniedError') {
                errorTitle = 'Permission Denied';
                errorMsg = 'Camera and microphone permissions were denied. Please:\n\n1. Click on the lock/info icon in address bar\n2. Allow camera and microphone\n3. Refresh the page';
            } else if (errorName === 'NotFoundError' || errorName === 'DevicesNotFoundError') {
                errorTitle = 'Device Not Found';
                errorMsg = 'No camera or microphone found on your device. Please connect a camera and microphone.';
            } else if (errorName === 'NotReadableError' || errorName === 'TrackStartError') {
                errorTitle = 'Device Busy';
                errorMsg = 'Camera or microphone is already in use by another application. Please close other apps using camera.';
            } else if (errorName === 'OverconstrainedError') {
                errorTitle = 'Camera Settings Error';
                errorMsg = 'Requested camera settings are not available. Trying with default settings...';
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
        
        const simpleConstraints = {
            video: true,
            audio: true
        };
        
        localStream = await navigator.mediaDevices.getUserMedia(simpleConstraints);
        
        if (localVideo && localStream) {
            localVideo.srcObject = localStream;
            await localVideo.play();
        }
        
        if (callStatus) callStatus.textContent = 'Connected';
        hideError();
        
        createPeerConnection();
        
        if (localStream && peerConnection) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }
        
        simulateRemoteConnection();
    } catch (retryError) {
        console.error('Retry error:', retryError);
        showError('Camera Access Error', 'Failed to access camera. Please check browser permissions and try again.');
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
            if (remoteVideo && event.streams && event.streams[0]) {
                remoteVideo.srcObject = event.streams[0];
                remoteVideo.play().catch(e => console.error('Remote video play error:', e));
                if (callStatus) callStatus.textContent = 'In Call';
                // Timer only for audio call, not video call
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
                        // Timer only for audio call, not video call
                    } else if (peerConnection.iceConnectionState === 'checking') {
                        callStatus.textContent = 'Connecting...';
                    } else if (peerConnection.iceConnectionState === 'failed') {
                        callStatus.textContent = 'Connection Failed';
                        // Timer only for audio call, not video call
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
                if (micIcon && micOffIcon) {
                    if (isAudioEnabled) {
                        micIcon.classList.remove('d-none');
                        micOffIcon.classList.add('d-none');
                    } else {
                        micIcon.classList.add('d-none');
                        micOffIcon.classList.remove('d-none');
                    }
                }
            }
        }
    });
}

// Toggle Video
if (toggleVideo) {
    toggleVideo.addEventListener('click', () => {
        if (localStream) {
            const videoTrack = localStream.getVideoTracks()[0];
            if (videoTrack) {
                videoTrack.enabled = !videoTrack.enabled;
                isVideoEnabled = videoTrack.enabled;
                
                const videoIcon = document.getElementById('videoIcon');
                const videoOffIcon = document.getElementById('videoOffIcon');
                if (videoIcon && videoOffIcon) {
                    if (isVideoEnabled) {
                        videoIcon.classList.remove('d-none');
                        videoOffIcon.classList.add('d-none');
                    } else {
                        videoIcon.classList.add('d-none');
                        videoOffIcon.classList.remove('d-none');
                    }
                }
                
                if (localVideo) {
                    localVideo.style.opacity = isVideoEnabled ? '1' : '0.5';
                }
            }
        }
    });
}

// Initialize Speaker Button
function initializeSpeakerButton() {
    const toggleSpeakerBtn = document.getElementById('toggleSpeaker');
    const speakerIcon = document.getElementById('speakerIcon');
    const speakerOffIcon = document.getElementById('speakerOffIcon');
    
    if (toggleSpeakerBtn && speakerIcon && speakerOffIcon) {
        // Set initial state to OFF (earpiece mode) - both icons visible
        speakerIcon.style.display = 'block';
        speakerIcon.style.color = 'white';
        speakerIcon.style.textShadow = '0 1px 3px rgba(0,0,0,0.3)';
        speakerIcon.style.opacity = '0.3'; // Gray/faded when OFF
        speakerOffIcon.style.display = 'block';
        speakerOffIcon.style.color = '#666';
        speakerOffIcon.style.opacity = '1'; // Clear when OFF
        toggleSpeakerBtn.style.background = '#f0f0f0'; // White/gray background when OFF
        toggleSpeakerBtn.style.border = '2px solid rgba(255,255,255,0.3)';
        toggleSpeakerBtn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.4)';
        toggleSpeakerBtn.title = 'Speaker: OFF';
        isSpeakerEnabled = false; // Start with OFF state
    }
}

// Speaker Toggle
if (toggleSpeaker) {
    toggleSpeaker.addEventListener('click', async () => {
        try {
            isSpeakerEnabled = !isSpeakerEnabled;
            
            const speakerIcon = document.getElementById('speakerIcon');
            const speakerOffIcon = document.getElementById('speakerOffIcon');
            const toggleSpeakerBtn = document.getElementById('toggleSpeaker');
            
            if (isSpeakerEnabled) {
                // Enable speaker (default output) - ON
                if (remoteVideo && remoteVideo.setSinkId) {
                    try {
                        await remoteVideo.setSinkId('');
                    } catch (e) {
                        console.log('setSinkId not supported, using default');
                    }
                }
                // Show both icons - volume-up clear (ON), volume-off gray (OFF) - like screenshot
                if (speakerIcon) {
                    speakerIcon.style.display = 'block';
                    speakerIcon.style.color = 'white'; // White icon when ON
                    speakerIcon.style.textShadow = '0 1px 3px rgba(0,0,0,0.3)';
                    speakerIcon.style.fontWeight = 'bold';
                    speakerIcon.style.opacity = '1'; // Clear/visible when ON
                }
                if (speakerOffIcon) {
                    speakerOffIcon.style.display = 'block';
                    speakerOffIcon.style.color = '#666'; // Gray icon when OFF
                    speakerOffIcon.style.opacity = '0.3'; // Faded when ON
                }
                // Change button background to show active state - light green like 2nd screenshot
                if (toggleSpeakerBtn) {
                    toggleSpeakerBtn.style.background = '#dcf8c6'; // Light green background when ON
                    toggleSpeakerBtn.style.border = '2px solid rgba(37, 211, 102, 0.5)';
                    toggleSpeakerBtn.style.boxShadow = '0 4px 16px rgba(37, 211, 102, 0.4)';
                    toggleSpeakerBtn.title = 'Speaker: ON';
                }
            } else {
                // Disable speaker (earpiece mode) - OFF - like 2nd screenshot
                if (remoteVideo && remoteVideo.setSinkId) {
                    try {
                        // Try to get available audio devices
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        const earpieceDevice = devices.find(d => d.kind === 'audiooutput' && d.label.toLowerCase().includes('earpiece'));
                        if (earpieceDevice) {
                            await remoteVideo.setSinkId(earpieceDevice.deviceId);
                        }
                    } catch (e) {
                        console.log('setSinkId not supported or earpiece not found');
                    }
                }
                // Show both icons - volume-up gray (OFF), volume-off clear (OFF) - like screenshot
                if (speakerIcon) {
                    speakerIcon.style.display = 'block';
                    speakerIcon.style.color = 'white';
                    speakerIcon.style.textShadow = '0 1px 3px rgba(0,0,0,0.3)';
                    speakerIcon.style.opacity = '0.3'; // Faded/gray when OFF
                }
                if (speakerOffIcon) {
                    speakerOffIcon.style.display = 'block';
                    speakerOffIcon.style.color = '#666'; // Gray icon when OFF
                    speakerOffIcon.style.fontWeight = 'bold';
                    speakerOffIcon.style.opacity = '1'; // Clear/visible when OFF
                }
                // Change button background to show inactive state - white/gray like 1st screenshot
                if (toggleSpeakerBtn) {
                    toggleSpeakerBtn.style.background = '#f0f0f0'; // White/gray background when OFF
                    toggleSpeakerBtn.style.border = '2px solid rgba(255,255,255,0.3)';
                    toggleSpeakerBtn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.4)';
                    toggleSpeakerBtn.title = 'Speaker: OFF';
                }
            }
        } catch (error) {
            console.error('Error toggling speaker:', error);
        }
    });
}

// Switch Camera (Front/Back)
const switchCamera = document.getElementById('switchCamera');
if (switchCamera) {
    switchCamera.addEventListener('click', async () => {
        if (!localStream) return;
        
        try {
            // Get current audio track to preserve it
            const audioTrack = localStream.getAudioTracks()[0];
            const audioEnabled = audioTrack ? audioTrack.enabled : true;
            
            // Stop current video track
            const oldVideoTrack = localStream.getVideoTracks()[0];
            if (oldVideoTrack) {
                oldVideoTrack.stop();
            }
            
            // Switch facing mode
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            
            // Get new video stream with switched camera
            const newVideoStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: currentFacingMode
                },
                audio: false // We'll add audio track separately
            });
            
            // Get new video track
            const newVideoTrack = newVideoStream.getVideoTracks()[0];
            if (newVideoTrack) {
                // Replace video track in local stream
                localStream.removeTrack(oldVideoTrack);
                localStream.addTrack(newVideoTrack);
                
                // Update local video element
                if (localVideo) {
                    localVideo.srcObject = localStream;
                }
                
                // Update peer connection with new track
                if (peerConnection) {
                    const sender = peerConnection.getSenders().find(s => 
                        s.track && s.track.kind === 'video'
                    );
                    if (sender) {
                        await sender.replaceTrack(newVideoTrack);
                    } else {
                        // If no sender found, add track
                        peerConnection.addTrack(newVideoTrack, localStream);
                    }
                }
                
                console.log('Camera switched to:', currentFacingMode === 'user' ? 'Front' : 'Back');
            }
            
            // Stop the temporary stream (we only needed the track)
            newVideoStream.getTracks().forEach(track => {
                if (track.kind === 'video') {
                    // Track is now in localStream, don't stop it
                } else {
                    track.stop();
                }
            });
            
        } catch (error) {
            console.error('Error switching camera:', error);
            // If switching fails, try to restore old camera
            try {
                currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
                const restoreStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: currentFacingMode
                    },
                    audio: false
                });
                const restoreTrack = restoreStream.getVideoTracks()[0];
                if (restoreTrack && localStream) {
                    const currentVideoTrack = localStream.getVideoTracks()[0];
                    if (currentVideoTrack) {
                        localStream.removeTrack(currentVideoTrack);
                        currentVideoTrack.stop();
                    }
                    localStream.addTrack(restoreTrack);
                    if (localVideo) {
                        localVideo.srcObject = localStream;
                    }
                }
            } catch (restoreError) {
                console.error('Error restoring camera:', restoreError);
            }
        }
    });
}

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

// Start call timer - Disabled for video call, only for audio call
function startCallTimer() {
    // Timer only for audio call, not video call
    return;
}

// Stop call timer - Disabled for video call, only for audio call
function stopCallTimer() {
    // Timer only for audio call, not video call
    return;
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
    if (localVideo) localVideo.style.display = 'none';
    if (remoteVideo) remoteVideo.style.display = 'none';
    
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
    
    // Adjust local video position on mobile
    const localVideoContainer = document.getElementById('localVideoContainer');
    if (localVideoContainer && window.innerWidth < 768) {
        localVideoContainer.style.width = '100px';
        localVideoContainer.style.top = '10px';
    }
    
    // Ensure call controls are visible
    const callControls = document.getElementById('callControls');
    if (callControls) {
        callControls.style.display = 'flex';
        callControls.style.visibility = 'visible';
        callControls.style.opacity = '1';
        callControls.style.zIndex = '9999';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    fixMobileViewport();
    initializeSpeakerButton();
    init();
    startCallStatusCheck();
    
    // Timer disabled for video call - only for audio call
});

// Fix viewport on resize
window.addEventListener('resize', fixMobileViewport);
window.addEventListener('orientationchange', function() {
    setTimeout(fixMobileViewport, 100);
});

// Completely block page refresh during active call - NO DIALOG AT ALL (Mobile + Web)
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
    
    // Block immediately when page loads (for video call page)
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
        top: 10px !important;
        right: 10px !important;
    }
    
    #callControls {
        padding: 15px 10px !important;
        padding-bottom: calc(15px + env(safe-area-inset-bottom)) !important;
    }
    
    #toggleAudio, #toggleVideo, #toggleSpeaker, #switchCamera, #endCall {
        width: 45px !important;
        height: 45px !important;
        min-width: 45px !important;
        min-height: 45px !important;
    }
    
    #toggleAudio i, #toggleVideo i, #toggleSpeaker i, #switchCamera i, #endCall i {
        font-size: 1.1rem !important;
    }
    
    #switchCamera i {
        font-size: 1.2rem !important;
    }
}

/* Fix double icon issue */
#toggleAudio i, #toggleVideo i {
    display: block !important;
}

#toggleAudio .d-none, #toggleVideo .d-none {
    display: none !important;
}

/* Ensure buttons are always visible */
#callControls {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#callControls button {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#callControls button i {
    display: inline-block !important;
    visibility: visible !important;
    opacity: 1 !important;
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
</style>
@endsection
