<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VideoCallController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/verify-otp', [AuthController::class, 'showOtpVerify'])->name('otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [VideoCallController::class, 'dashboard'])->name('dashboard');
    Route::get('/calls', [VideoCallController::class, 'calls'])->name('calls');
    Route::get('/video-call/{userId?}', [VideoCallController::class, 'showCall'])->name('video.call');
    Route::get('/audio-call/{userId?}', [VideoCallController::class, 'showAudioCall'])->name('audio.call');
    Route::get('/api/users', [VideoCallController::class, 'getUsers'])->name('api.users');
    Route::get('/api/user-info', [VideoCallController::class, 'getUserInfo'])->name('api.user.info');
    
    // Call request routeso
    Route::post('/api/call/initiate', [VideoCallController::class, 'initiateCall'])->name('api.call.initiate');
    Route::get('/api/call/incoming', [VideoCallController::class, 'checkIncomingCalls'])->name('api.call.incoming');
    Route::post('/api/call/accept', [VideoCallController::class, 'acceptCall'])->name('api.call.accept');
    Route::post('/api/call/reject', [VideoCallController::class, 'rejectCall'])->name('api.call.reject');
    Route::post('/api/call/end', [VideoCallController::class, 'endCall'])->name('api.call.end');
    Route::get('/api/call/status', [VideoCallController::class, 'checkCallStatus'])->name('api.call.status');
    Route::post('/api/call/add-user', [VideoCallController::class, 'addUserToCall'])->name('api.call.addUser');
    // WebRTC Signaling routes
    Route::post('/api/webrtc/offer', [VideoCallController::class, 'storeOffer'])->name('api.webrtc.offer');
    Route::get('/api/webrtc/offer', [VideoCallController::class, 'getOffer'])->name('api.webrtc.getOffer');
    Route::post('/api/webrtc/answer', [VideoCallController::class, 'storeAnswer'])->name('api.webrtc.answer');
    Route::get('/api/webrtc/answer', [VideoCallController::class, 'getAnswer'])->name('api.webrtc.getAnswer');
    Route::post('/api/webrtc/ice', [VideoCallController::class, 'storeIceCandidate'])->name('api.webrtc.ice');
    Route::get('/api/webrtc/ice', [VideoCallController::class, 'getIceCandidates'])->name('api.webrtc.getIce');
    
    // Profile routes
    Route::post('/api/profile/update', [VideoCallController::class, 'updateProfile'])->name('api.profile.update');
    
    // Chat routes
    Route::get('/api/chat/messages/{userId}', [VideoCallController::class, 'getMessages'])->name('api.chat.messages');
    Route::post('/api/chat/send', [VideoCallController::class, 'sendMessage'])->name('api.chat.send');
    Route::post('/api/chat/mark-read', [VideoCallController::class, 'markMessagesAsRead'])->name('api.chat.markRead');
    Route::get('/api/chat/conversations', [VideoCallController::class, 'getConversations'])->name('api.chat.conversations');
    Route::post('/api/chat/delete', [VideoCallController::class, 'deleteMessages'])->name('api.chat.delete');
    
    // Audio call routes
    Route::post('/api/call/audio/initiate', [VideoCallController::class, 'initiateAudioCall'])->name('api.call.audio.initiate');
    
    // Group call routes
    Route::post('/api/group-call/create', [VideoCallController::class, 'createGroupCall'])->name('api.group.call.create');
    Route::get('/api/group-call', [VideoCallController::class, 'getGroupCall'])->name('api.group.call.get');
    Route::post('/api/group-call/join', [VideoCallController::class, 'joinGroupCall'])->name('api.group.call.join');
    Route::post('/api/group-call/leave', [VideoCallController::class, 'leaveGroupCall'])->name('api.group.call.leave');
    Route::get('/api/group-call/participants', [VideoCallController::class, 'getGroupCallParticipants'])->name('api.group.call.participants');
    
    // Group call pages
    Route::get('/group-video-call', [VideoCallController::class, 'showGroupVideoCall'])->name('group.video.call');
    Route::get('/group-audio-call', [VideoCallController::class, 'showGroupAudioCall'])->name('group.audio.call');
    
    // Delete routes
    Route::post('/api/calls/delete', [VideoCallController::class, 'deleteCalls'])->name('api.calls.delete');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
