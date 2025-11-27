# Video Call App - Laravel

A complete video and audio calling application built with Laravel and WebRTC, featuring one-to-one calls, group calls, real-time chat, user registration with OTP email verification, and fully mobile-responsive design.

## ✨ Features

### Core Features
- ✅ **User Registration** with Email OTP Verification
- ✅ **Secure Authentication** System with session management
- ✅ **One-to-One Video Calls** (WebRTC)
- ✅ **One-to-One Audio Calls** (WebRTC)
- ✅ **Group Video Calls** - Multiple users simultaneously
- ✅ **Group Audio Calls** - Multiple users simultaneously
- ✅ **Real-time Chat** - Send and receive messages
- ✅ **Call History** - View past calls
- ✅ **Profile Management** - Update profile picture and information

### Call Features
- ✅ **Real-time Call Controls**:
  - Mute/Unmute Audio
  - Mute/Unmute Video
  - Speaker Toggle (ON/OFF with visual indication)
  - Switch Camera (Front/Back)
  - End Call
- ✅ **Call Timer** - Track call duration
- ✅ **Call Status** - Real-time connection status
- ✅ **Mobile Optimized** - Full screen call interface

### UI/UX Features
- ✅ **Mobile Responsive Design** - Works on all devices
- ✅ **Modern UI** - WhatsApp-like interface
- ✅ **Dark Mode Support** - Optimized for low-light environments
- ✅ **Smooth Animations** - Enhanced user experience

## 🛠 Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Blade Templates, Bootstrap 5, Font Awesome
- **Video/Audio Calling**: WebRTC API
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **Real-time**: Polling-based signaling (can be upgraded to WebSockets)

## 📦 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and npm
- SQLite (default) or MySQL/PostgreSQL

### Step-by-Step Setup

1. **Navigate to project directory**
   ```bash
   cd /var/www/html/VideocallProject
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Mail Settings**
   
   Edit `.env` file and configure your mail settings:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```
   
   **For Gmail:**
   - Enable 2-factor authentication
   - Generate an App Password (not your regular password)
   - Use the App Password in `MAIL_PASSWORD`

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```
   
   This will create the following tables:
   - `users` - User accounts
   - `call_requests` - One-to-one call requests
   - `group_calls` - Group call rooms
   - `group_call_participants` - Group call participants
   - `messages` - Chat messages

7. **Build Assets**
   ```bash
   npm run build
   ```

8. **Start Development Server**
   
   **For localhost only:**
   ```bash
   php artisan serve
   ```
   
   **For network access (mobile devices):**
   ```bash
   ./start-network-server.sh
   ```
   
   Or manually:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

9. **Access the Application**
   - Desktop: `http://localhost:8000`
   - Mobile (same WiFi): `http://YOUR_IP:8000` (e.g., `http://192.168.1.27:8000`)

## 📱 Mobile Access Setup

### Quick Setup
1. Make sure laptop and phone are on the **same WiFi network**
2. Find your network IP:
   ```bash
   hostname -I | awk '{print $1}'
   ```
3. Start server on network:
   ```bash
   ./start-network-server.sh
   ```
4. Open on mobile browser: `http://YOUR_IP:8000`

### Troubleshooting
- **Connection Refused**: Server not running on `0.0.0.0`
- **Can't Reach**: Check firewall settings
- **Different Networks**: Ensure both devices on same WiFi

See `PHONE_ACCESS_FIXED.md` for detailed instructions.

## 🚀 Usage

### User Registration & Login

1. **Register a New Account**
   - Go to `/register`
   - Fill in name, email, and password
   - You'll receive an OTP via email

2. **Verify Email**
   - Check your email for the 6-digit OTP
   - Enter OTP on verification page
   - OTP expires in 10 minutes

3. **Login**
   - Go to `/login`
   - Use verified email and password

### One-to-One Calls

1. **Start Video Call**
   - Go to Dashboard
   - Select a user
   - Click video call button
   - Allow camera and microphone permissions

2. **Start Audio Call**
   - Select a user
   - Click audio call button
   - Allow microphone permissions

3. **During Call**
   - Use controls to mute/unmute audio/video
   - Toggle speaker (green = ON, gray = OFF)
   - Switch camera (video calls only)
   - End call when done

### Group Calls

1. **Create Group Call**
   - Select multiple users (UI coming soon)
   - Choose video or audio call
   - All participants join the same room

2. **Join Group Call**
   - Receive invitation
   - Click join link
   - Automatically connect to all participants

### Chat

1. **Send Messages**
   - Select a user from dashboard
   - Type message in chat input
   - Press send or Enter

2. **View Messages**
   - Messages appear in real-time
   - Unread count shown on user list
   - Mark as read automatically

## 📁 Project Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php          # Registration, login, OTP
│   └── VideoCallController.php      # Calls, chat, group calls
├── Mail/
│   └── OtpMail.php                  # OTP email template
└── Models/
    ├── User.php                      # User model
    ├── CallRequest.php               # One-to-one call requests
    ├── GroupCall.php                 # Group call rooms
    ├── GroupCallParticipant.php      # Group call participants
    └── Message.php                   # Chat messages

database/migrations/
├── create_users_table.php
├── create_call_requests_table.php
├── create_group_calls_table.php
├── create_group_call_participants_table.php
└── create_messages_table.php

resources/views/
├── auth/
│   ├── register.blade.php
│   ├── login.blade.php
│   └── verify-otp.blade.php
├── dashboard.blade.php               # Main dashboard with users list
├── video-call.blade.php              # One-to-one video call
├── audio-call.blade.php              # One-to-one audio call
├── calls.blade.php                   # Call history
└── layouts/
    └── app.blade.php                 # Main layout
```

## 🔌 API Routes

### Authentication
- `GET /register` - Registration form
- `POST /register` - Process registration
- `GET /login` - Login form
- `POST /login` - Process login
- `GET /verify-otp` - OTP verification form
- `POST /verify-otp` - Verify OTP
- `POST /resend-otp` - Resend OTP

### Dashboard & Users
- `GET /dashboard` - User dashboard
- `GET /api/users` - Get all users
- `GET /api/user-info` - Get user information

### One-to-One Calls
- `POST /api/call/initiate` - Initiate call
- `POST /api/call/accept` - Accept call
- `POST /api/call/reject` - Reject call
- `POST /api/call/end` - End call
- `GET /api/call/status` - Check call status
- `GET /video-call` - Video call page
- `GET /audio-call` - Audio call page

### Group Calls
- `POST /api/group-call/create` - Create group call
- `GET /api/group-call` - Get group call details
- `POST /api/group-call/join` - Join group call
- `POST /api/group-call/leave` - Leave group call
- `GET /api/group-call/participants` - Get participants
- `GET /group-video-call` - Group video call page
- `GET /group-audio-call` - Group audio call page

### WebRTC Signaling
- `POST /api/webrtc/offer` - Store WebRTC offer
- `GET /api/webrtc/offer` - Get WebRTC offer
- `POST /api/webrtc/answer` - Store WebRTC answer
- `GET /api/webrtc/answer` - Get WebRTC answer
- `POST /api/webrtc/ice` - Store ICE candidate
- `GET /api/webrtc/ice` - Get ICE candidates

### Chat
- `GET /api/chat/messages/{userId}` - Get messages
- `POST /api/chat/send` - Send message
- `POST /api/chat/mark-read` - Mark messages as read
- `GET /api/chat/conversations` - Get conversations

### Profile
- `POST /api/profile/update` - Update profile

## 🎯 WebRTC Implementation

### Current Setup
- **Signaling**: HTTP polling (can be upgraded to WebSockets)
- **STUN Servers**: Google's free STUN servers
- **Architecture**: Peer-to-peer for one-to-one, Mesh for group calls

### For Production
1. **Signaling Server**: Use WebSocket (Socket.io) or Laravel Broadcasting
2. **TURN Servers**: For NAT traversal (required for some networks)
3. **SFU/MCU**: For better group call scalability

### WebRTC Features
- ✅ Audio/Video streaming
- ✅ Screen sharing (can be added)
- ✅ Multiple participants (group calls)
- ✅ Connection state management
- ✅ ICE candidate handling

## 🔒 Security Features

- ✅ Password hashing (bcrypt)
- ✅ CSRF protection
- ✅ OTP expiration (10 minutes)
- ✅ Email verification required
- ✅ Secure session management
- ✅ SQL injection protection
- ✅ XSS protection

## 📱 Mobile Features

- ✅ Full screen call interface
- ✅ Responsive design
- ✅ Touch-optimized controls
- ✅ Mobile camera support
- ✅ Speaker toggle with visual feedback
- ✅ Network access support

## 🎨 UI Features

- ✅ WhatsApp-like interface
- ✅ Gradient backgrounds
- ✅ Smooth transitions
- ✅ Real-time status updates
- ✅ Unread message indicators
- ✅ Call duration timer
- ✅ Profile pictures with fallback

## 📝 Database Schema

### Users
- id, name, email, password, email_verified_at, otp, otp_expires_at, profile_picture, created_at, updated_at

### Call Requests
- id, caller_id, receiver_id, room_id, status, answered_at, ended_at, created_at, updated_at

### Group Calls
- id, room_id, created_by, type, status, started_at, ended_at, created_at, updated_at

### Group Call Participants
- id, group_call_id, user_id, status, joined_at, left_at, created_at, updated_at

### Messages
- id, sender_id, receiver_id, message, type, is_read, created_at, updated_at

## 🐛 Troubleshooting

### Server Issues
- **Port already in use**: Change port in `start-network-server.sh`
- **Permission denied**: Check file permissions
- **Database error**: Run `php artisan migrate:fresh`

### Call Issues
- **No video/audio**: Check browser permissions
- **Connection failed**: Check network/firewall
- **Mobile not connecting**: Ensure same WiFi network

### Email Issues
- **OTP not received**: Check spam folder, verify SMTP settings
- **Gmail blocking**: Use App Password, not regular password

## 📚 Additional Documentation

- `PHONE_ACCESS_FIXED.md` - Mobile access guide
- `GROUP_CALL_IMPLEMENTATION.md` - Group call setup
- `GMAIL_SETUP.md` - Gmail configuration
- `TECHNOLOGY_DOCUMENTATION.md` - Technical details

## 🚧 Future Enhancements

- [ ] WebSocket-based signaling
- [ ] Screen sharing
- [ ] File sharing in chat
- [ ] Push notifications
- [ ] Call recording
- [ ] Advanced group call features
- [ ] TURN server integration

## License

MIT License

## 👥 Support

For issues and questions:
- Check existing documentation files
- Review troubleshooting section
- Check Laravel logs: `storage/logs/laravel.log`

## 🎉 Credits

Built with:
- Laravel Framework
- WebRTC API
- Bootstrap 5
- Font Awesome
- Google STUN Servers

---

**Version**: 2.0.0  
**Last Updated**: November 2025  
**Status**: Active Development
# VideocallProject
# VideocallProject
