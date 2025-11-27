# Video Call Project - Technology Documentation
# वीडियो कॉल प्रोजेक्ट - टेक्नोलॉजी डॉक्यूमेंटेशन

---

## 📋 Table of Contents / विषय सूची

1. [Overview / अवलोकन](#overview)
2. [Backend Technologies / बैकएंड टेक्नोलॉजी](#backend-technologies)
3. [Frontend Technologies / फ्रंटएंड टेक्नोलॉजी](#frontend-technologies)
4. [WebRTC Implementation / वेबआरटीसी इम्प्लीमेंटेशन](#webrtc-implementation)
5. [Real-time Communication / रियल-टाइम कम्युनिकेशन](#real-time-communication)
6. [Database / डेटाबेस](#database)
7. [Build Tools / बिल्ड टूल्स](#build-tools)
8. [Architecture Overview / आर्किटेक्चर अवलोकन](#architecture-overview)

---

## Overview / अवलोकन

यह एक **Real-time Video & Audio Calling Application** है जो **WebRTC** का उपयोग करके peer-to-peer video और audio calls enable करता है। साथ ही इसमें **Real-time Chat** functionality भी है।

**Main Features:**
- ✅ Video Calling (वीडियो कॉलिंग)
- ✅ Audio Calling (ऑडियो कॉलिंग)
- ✅ Real-time Chat (रियल-टाइम चैट)
- ✅ User Authentication with OTP (OTP के साथ यूजर ऑथेंटिकेशन)
- ✅ Profile Management (प्रोफाइल मैनेजमेंट)
- ✅ Call History (कॉल हिस्ट्री)
- ✅ Mobile Responsive (मोबाइल रेस्पॉन्सिव)

---

## Backend Technologies / बैकएंड टेक्नोलॉजी

### 1. **Laravel Framework (PHP)**
   - **Version:** Laravel 12.0
   - **PHP Version:** PHP 8.2+
   - **Location:** `composer.json`
   
   **क्या काम करता है:**
   - Server-side logic handle करता है
   - API endpoints provide करता है
   - Database operations manage करता है
   - User authentication और authorization
   - File uploads (profile pictures)
   - Email sending (OTP)

   **Key Files:**
   - `app/Http/Controllers/VideoCallController.php` - Main controller
   - `app/Http/Controllers/AuthController.php` - Authentication controller
   - `routes/web.php` - All routes defined here

### 2. **Laravel Features Used:**
   - **Authentication System** - Built-in Laravel Auth
   - **Eloquent ORM** - Database operations
   - **Blade Templates** - Server-side rendering
   - **Mail System** - OTP emails
   - **File Storage** - Profile pictures storage
   - **CSRF Protection** - Security
   - **Middleware** - Route protection

### 3. **Database: SQLite**
   - **Location:** `database/database.sqlite`
   - **ORM:** Laravel Eloquent
   
   **Tables:**
   - `users` - User information, OTP codes
   - `call_requests` - Call history and status
   - `messages` - Chat messages
   - `cache` - Laravel cache
   - `jobs` - Queue jobs

---

## Frontend Technologies / फ्रंटएंड टेक्नोलॉजी

### 1. **Blade Templates (Laravel)**
   - **Files:**
     - `resources/views/dashboard.blade.php` - Main dashboard
     - `resources/views/video-call.blade.php` - Video call page
     - `resources/views/audio-call.blade.php` - Audio call page
     - `resources/views/auth/*.blade.php` - Login/Register pages

### 2. **JavaScript (Vanilla JS)**
   - **No Framework Used** - Pure JavaScript
   - **Location:** Inline in Blade templates
   
   **क्या काम करता है:**
   - WebRTC connection management
   - API calls (fetch API)
   - Real-time polling
   - DOM manipulation
   - Event handling

### 3. **Tailwind CSS 4**
   - **Version:** 4.0.0
   - **Location:** `package.json`
   - **Config:** `vite.config.js`
   
   **क्या काम करता है:**
   - Modern, responsive UI
   - Utility-first CSS framework
   - Mobile-first design
   - Custom styling

### 4. **Bootstrap Icons**
   - Icon library for UI elements
   - Used throughout the application

### 5. **Axios**
   - **Version:** 1.11.0
   - HTTP client library
   - Used for API requests (though fetch is also used)

---

## WebRTC Implementation / वेबआरटीसी इम्प्लीमेंटेशन

### 1. **WebRTC API (Native Browser API)**
   - **Technology:** Native WebRTC (no external library)
   - **Location:** `resources/views/video-call.blade.php` और `audio-call.blade.php`
   
   **Key Components:**
   ```javascript
   // RTCPeerConnection - Main WebRTC object
   peerConnection = new RTCPeerConnection(configuration);
   
   // getUserMedia - Camera/Microphone access
   navigator.mediaDevices.getUserMedia({ video: true, audio: true })
   
   // ICE Candidates - Network connection
   peerConnection.onicecandidate
   
   // Remote Stream - Receive video/audio
   peerConnection.ontrack
   ```

### 2. **STUN Servers**
   - **Google STUN Servers:**
     - `stun:stun.l.google.com:19302`
     - `stun:stun1.l.google.com:19302`
   
   **क्या काम करता है:**
   - NAT traversal के लिए
   - Public IP address find करने में help करता है
   - Direct peer-to-peer connection establish करने में

### 3. **Signaling Mechanism (HTTP Polling)**
   - **Important:** यह project **WebSocket/Socket.io का उपयोग नहीं करता**
   - **Method:** HTTP Polling (setInterval)
   
   **How it Works:**
   ```
   Client 1 → Laravel API → Database → Client 2 (Polling)
   ```
   
   **Signaling Routes:**
   - `POST /api/webrtc/offer` - Offer store करने के लिए
   - `GET /api/webrtc/offer` - Offer retrieve करने के लिए
   - `POST /api/webrtc/answer` - Answer store करने के लिए
   - `GET /api/webrtc/answer` - Answer retrieve करने के लिए
   - `POST /api/webrtc/ice` - ICE candidates store करने के लिए
   - `GET /api/webrtc/ice` - ICE candidates retrieve करने के लिए

### 4. **WebRTC Flow:**
   1. **Call Initiation:**
      - User A calls User B
      - Call request database में save होता है
      - User B को notification (polling से)
   
   2. **Offer Creation:**
      - User A creates RTCPeerConnection
      - getUserMedia से local stream capture करता है
      - createOffer() call करता है
      - Offer Laravel API को send करता है
   
   3. **Answer Creation:**
      - User B polling से offer receive करता है
      - createAnswer() call करता है
      - Answer Laravel API को send करता है
   
   4. **ICE Candidates Exchange:**
      - दोनों users ICE candidates generate करते हैं
      - Candidates database में store होते हैं
      - Polling से exchange होते हैं
   
   5. **Connection Established:**
      - Peer-to-peer connection establish हो जाता है
      - Video/Audio stream directly transfer होता है

---

## Real-time Communication / रियल-टाइम कम्युनिकेशन

### 1. **HTTP Polling (setInterval)**
   - **WebSocket नहीं है** - यह important है!
   - **Method:** JavaScript setInterval
   
   **Polling Intervals:**
   ```javascript
   // Incoming calls check - हर 2 seconds
   setInterval(checkIncomingCalls, 2000);
   
   // Unread message counts - हर 2 seconds
   setInterval(updateUnreadCounts, 2000);
   
   // Chat messages - हर 2 seconds
   setInterval(() => {
       loadChatMessages(userId);
   }, 2000);
   ```

### 2. **API Endpoints for Real-time:**
   - `GET /api/call/incoming` - Incoming calls check
   - `GET /api/call/status` - Call status check
   - `GET /api/chat/messages/{userId}` - Get messages
   - `GET /api/chat/conversations` - Get conversations
   - `GET /api/users` - Get users list

### 3. **Why Polling Instead of WebSocket?**
   - **Simpler Implementation** - No need for separate WebSocket server
   - **Laravel Only** - No Node.js server required
   - **Easier Deployment** - Single server setup
   - **Trade-off:** Slightly higher server load, but acceptable for small scale

---

## Database / डेटाबेस

### 1. **SQLite Database**
   - **File:** `database/database.sqlite`
   - **ORM:** Laravel Eloquent

### 2. **Main Tables:**

   **users:**
   - `id` - Primary key
   - `name` - User name
   - `email` - Email address
   - `password` - Hashed password
   - `otp_code` - OTP for verification
   - `otp_expires_at` - OTP expiry
   - `profile_picture` - Profile picture filename
   - `email_verified_at` - Email verification

   **call_requests:**
   - `id` - Primary key
   - `caller_id` - Who initiated call
   - `receiver_id` - Who receives call
   - `room_id` - Unique room identifier
   - `status` - pending/accepted/rejected/ended
   - `answered_at` - When call was answered
   - `ended_at` - When call ended

   **messages:**
   - `id` - Primary key
   - `sender_id` - Who sent message
   - `receiver_id` - Who receives message
   - `message` - Message text
   - `type` - Message type
   - `is_read` - Read status
   - `read_at` - When read

### 3. **Migrations:**
   - `database/migrations/` folder में सभी migrations हैं
   - Laravel migration system use होता है

---

## Build Tools / बिल्ड टूल्स

### 1. **Vite**
   - **Version:** 7.0.7
   - **Location:** `vite.config.js`
   - **Purpose:** Modern build tool
   
   **क्या काम करता है:**
   - CSS और JS files को bundle करता है
   - Hot Module Replacement (HMR) development में
   - Fast builds
   - Asset optimization

### 2. **Laravel Vite Plugin**
   - **Version:** 2.0.0
   - Laravel के साथ Vite integrate करता है
   - Blade templates में assets load करता है

### 3. **NPM Scripts:**
   ```json
   "build": "vite build"  // Production build
   "dev": "vite"          // Development server
   ```

---

## Architecture Overview / आर्किटेक्चर अवलोकन

### 1. **System Architecture:**

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Blade      │  │  JavaScript  │  │   WebRTC     │ │
│  │  Templates   │  │  (Vanilla)   │  │   API        │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│         │                  │                  │         │
│         └──────────────────┼──────────────────┘         │
│                            │                            │
│                    HTTP Requests                         │
│                    (Polling every 2s)                    │
└────────────────────────────┼────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────┐
│              LARAVEL BACKEND (PHP)                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ Controllers  │  │   Models     │  │   Routes     │ │
│  │              │  │   (Eloquent) │  │              │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│         │                  │                  │         │
│         └──────────────────┼──────────────────┘         │
│                            │                            │
│                    Database Operations                   │
└────────────────────────────┼────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────┐
│              SQLite DATABASE                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │  users   │  │  calls   │  │ messages │              │
│  └──────────┘  └──────────┘  └──────────┘              │
└─────────────────────────────────────────────────────────┘
```

### 2. **WebRTC Signaling Flow:**

```
User A                          Laravel API                    User B
  │                                │                             │
  │─── Create Offer ──────────────▶│                             │
  │                                │─── Store in DB ────────────▶│
  │                                │                             │
  │                                │◀─── Polling (every 2s) ──────│
  │                                │                             │
  │                                │─── Return Offer ───────────▶│
  │                                │                             │
  │                                │◀─── Create Answer ──────────│
  │                                │                             │
  │◀─── Polling for Answer ────────│                             │
  │                                │                             │
  │─── Exchange ICE Candidates ────▶│◀─── Exchange ICE ──────────│
  │                                │                             │
  │══════════════════════════════════════════════════════════════│
  │                    P2P Connection Established                │
  │══════════════════════════════════════════════════════════════│
```

### 3. **Technology Stack Summary:**

| Category | Technology | Version | Purpose |
|----------|-----------|---------|---------|
| **Backend** | Laravel | 12.0 | Server-side framework |
| **Backend** | PHP | 8.2+ | Programming language |
| **Database** | SQLite | - | Data storage |
| **Frontend** | Blade | - | Template engine |
| **Frontend** | JavaScript | ES6+ | Client-side logic |
| **Frontend** | Tailwind CSS | 4.0 | Styling |
| **Frontend** | Bootstrap Icons | - | Icons |
| **WebRTC** | Native WebRTC | - | Video/Audio calls |
| **Signaling** | HTTP Polling | - | WebRTC signaling |
| **Build Tool** | Vite | 7.0.7 | Asset bundling |
| **HTTP Client** | Axios | 1.11.0 | API requests |

---

## Key Points / मुख्य बातें

### ✅ **क्या Use किया गया है:**

1. **Laravel 12** - Backend framework
2. **PHP 8.2+** - Server-side language
3. **SQLite** - Database
4. **Native WebRTC API** - Video/Audio calls
5. **HTTP Polling** - Real-time updates (WebSocket नहीं)
6. **Tailwind CSS 4** - Styling
7. **Vite** - Build tool
8. **Blade Templates** - Server-side rendering
9. **Vanilla JavaScript** - No framework (React/Vue/Angular नहीं)

### ❌ **क्या Use नहीं किया गया:**

1. **Node.js Server** - No separate Node.js server
2. **WebSocket/Socket.io** - HTTP Polling use होता है
3. **React/Vue/Angular** - Vanilla JavaScript
4. **STUN/TURN Server** - Only Google STUN servers
5. **Redis** - Not used
6. **MySQL/PostgreSQL** - SQLite use होता है

---

## File Structure / फाइल स्ट्रक्चर

```
VideocallProject/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php      # Authentication
│   │       └── VideoCallController.php  # Main controller
│   └── Models/
│       ├── User.php                     # User model
│       ├── CallRequest.php             # Call model
│       └── Message.php                 # Message model
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php         # Main dashboard
│   │   ├── video-call.blade.php        # Video call page
│   │   ├── audio-call.blade.php        # Audio call page
│   │   └── auth/                       # Auth pages
│   ├── css/
│   │   └── app.css                     # Main CSS
│   └── js/
│       └── app.js                       # Main JS
├── routes/
│   └── web.php                         # All routes
├── database/
│   ├── database.sqlite                 # SQLite database
│   └── migrations/                    # Database migrations
├── public/
│   └── storage/profiles/               # Profile pictures
├── package.json                        # Node dependencies
├── composer.json                       # PHP dependencies
└── vite.config.js                     # Vite configuration
```

---

## API Endpoints / एपीआई एंडपॉइंट्स

### Authentication:
- `POST /register` - User registration
- `POST /login` - User login
- `POST /verify-otp` - OTP verification
- `POST /resend-otp` - Resend OTP

### Users:
- `GET /api/users` - Get all users
- `GET /api/user-info` - Get current user info

### Calls:
- `POST /api/call/initiate` - Start a call
- `GET /api/call/incoming` - Check incoming calls
- `POST /api/call/accept` - Accept call
- `POST /api/call/reject` - Reject call
- `POST /api/call/end` - End call
- `GET /api/call/status` - Check call status

### WebRTC Signaling:
- `POST /api/webrtc/offer` - Store WebRTC offer
- `GET /api/webrtc/offer` - Get WebRTC offer
- `POST /api/webrtc/answer` - Store WebRTC answer
- `GET /api/webrtc/answer` - Get WebRTC answer
- `POST /api/webrtc/ice` - Store ICE candidate
- `GET /api/webrtc/ice` - Get ICE candidates

### Chat:
- `GET /api/chat/messages/{userId}` - Get messages
- `POST /api/chat/send` - Send message
- `POST /api/chat/mark-read` - Mark as read
- `GET /api/chat/conversations` - Get conversations
- `POST /api/chat/delete` - Delete messages

### Profile:
- `POST /api/profile/update` - Update profile

---

## Development Setup / डेवलपमेंट सेटअप

### Requirements:
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite

### Installation:
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development server
php artisan serve
npm run dev
```

---

## Conclusion / निष्कर्ष

यह project एक **modern, full-stack video calling application** है जो:
- **Laravel** को backend के रूप में use करता है
- **Native WebRTC** को video/audio calls के लिए use करता है
- **HTTP Polling** को real-time communication के लिए use करता है (WebSocket नहीं)
- **Tailwind CSS** को modern UI के लिए use करता है
- **SQLite** को database के रूप में use करता है

यह architecture **simple और scalable** है, और **single server deployment** के लिए perfect है।

---

**Documentation Created:** $(date)
**Project:** Video Call Application
**Version:** 1.0




