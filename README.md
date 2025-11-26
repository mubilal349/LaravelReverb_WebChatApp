Chat overview 
<img width="942" height="413" alt="image" src="https://github.com/user-attachments/assets/e3d73a2d-d029-4eaf-85d4-e0eb484d159c" />

# Laravel Reverb WebChat Application

A real-time web chat application built with Laravel and Reverb, featuring instant messaging, voice/video calls, and persistent message storage.

## 🚀 Features

### Core Features
- **Real-time Messaging**: Instant message delivery using Laravel Reverb
- **Voice & Video Calls**: WebRTC-powered audio/video communication
- **Profile Management**: Custom avatars and display names
- **Message Persistence**: All messages stored in MySQL database
- **Responsive Design**: Works seamlessly on desktop and mobile devices

### Chat Features
- ✅ Send and receive messages in real-time
- ✅ Typing indicators
- ✅ Message status (sent, delivered, read)
- ✅ Message timestamps
- ✅ User presence status (online/offline)
- ✅ Message history persistence

### Call Features
- ✅ Voice calls with audio visualization
- ✅ Video calls with local/remote video streams
- ✅ Screen sharing capability
- ✅ Mute/Unmute microphone
- ✅ Enable/Disable camera
- ✅ Call timer
- ✅ Incoming call interface

### Profile Features
- ✅ Custom profile picture upload
- ✅ Display name customization
- ✅ Preset avatar selection
- ✅ Profile data persistence

## 🛠️ Technology Stack

### Backend
- **Laravel 11** - PHP Framework
- **Laravel Reverb** - Real-time WebSocket communication
- **MySQL** - Database for message storage
- **Laravel Echo** - Client-side WebSocket integration
- **Pusher JS** - Real-time event broadcasting

### Frontend
- **HTML5/CSS3** - Modern UI with animations
- **JavaScript (Vanilla)** - No frameworks required
- **WebRTC** - Peer-to-peer voice/video communication
- **Font Awesome** - Icon library
- **LocalStorage** - Client-side profile persistence

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Node.js & NPM (for asset compilation)
- Laravel CLI
- Git

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/LaravelReverb_WebChatApp.git
cd LaravelReverb_WebChatApp


# Install PHP dependencies
composer install

# Install Node dependencies
npm install


# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate


# Install Reverb
php artisan reverb:install

# Start Reverb server
php artisan reverb:start

LaravelReverb_WebChatApp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ChatController.php
│   │   │   ├── AuthController.php
│   │   │   └── ProfileController.php
│   │   └── ...
│   ├── Models/
│   │   ├── Message.php
│   │   ├── User.php
│   │   └── Conversation.php
│   ├── Events/
│   │   ├── MessageSent.php
│   │   ├── VoiceCallInitiated.php
│   │   └── VideoCallInitiated.php
│   └── Listeners/
│       └── ...
├── database/
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_messages_table.php
│   │   └── create_conversations_table.php
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── chat.blade.php
│   │   ├── welcome.blade.php
│   │   └── layouts/
│   ├── js/
│   │   ├── app.js
│   │   ├── chat.js
│   │   └── webrtc.js
│   └── css/
│       └── app.css
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── config/
│   ├── broadcasting.php
│   ├── reverb.php
│   └── ...
└── public/
    └── assets/


### Start the server ###
php artisan serve
