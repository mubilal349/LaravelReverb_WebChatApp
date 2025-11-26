<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real WebRTC Voice & Video Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Previous styles remain the same */
        :root {
            --user1-color: #4a6cf7;
            --user2-color: #ff6b6b;
            --bg-color: #f5f7fa;
            --border-color: #e0e6ed;
            --text-color: #333;
            --message-bg-sent: #4a6cf7;
            --message-bg-received: #f1f3f5;
            --online-color: #4caf50;
            --typing-color: #ff9800;
            --call-bg: #1a1a1a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            height: 100vh;
            overflow: hidden;
        }

        .app-container {
            display: flex;
            height: 100vh;
            position: relative;
        }

        .divider {
            width: 4px;
            background: linear-gradient(to bottom, var(--user1-color), var(--user2-color));
            position: relative;
            z-index: 10;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .divider::before {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 30px;
            height: 30px;
            background-color: white;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: white;
            position: relative;
        }

        .chat-panel.user1 {
            border-right: 1px solid var(--border-color);
        }

        .chat-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-color);
        }

        .user1 .chat-header {
            background: linear-gradient(135deg, var(--user1-color), #6b8cff);
            color: white;
        }

        .user2 .chat-header {
            background: linear-gradient(135deg, var(--user2-color), #ff8787);
            color: white;
        }

        .chat-user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
            background-color: white;
            color: var(--text-color);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid white;
            background-color: var(--online-color);
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .user-status {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .chat-actions {
            display: flex;
            gap: 15px;
        }

        .chat-actions button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.1rem;
            transition: transform 0.2s;
            position: relative;
        }

        .chat-actions button:hover {
            transform: scale(1.1);
        }

        .chat-actions button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .permission-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #f44336;
            color: white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #f9fafb;
            display: flex;
            flex-direction: column;
        }

        .date-divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }

        .date-divider span {
            background-color: #f9fafb;
            padding: 0 15px;
            color: #666;
            font-size: 0.8rem;
            position: relative;
            z-index: 1;
        }

        .date-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: var(--border-color);
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            max-width: 75%;
            animation: messageSlide 0.3s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.received {
            align-self: flex-start;
        }

        .user1 .message.sent .message-bubble {
            background-color: var(--user1-color);
            color: white;
        }

        .user1 .message.received .message-bubble {
            background-color: var(--message-bg-received);
            color: var(--text-color);
        }

        .user2 .message.sent .message-bubble {
            background-color: var(--user2-color);
            color: white;
        }

        .user2 .message.received .message-bubble {
            background-color: var(--message-bg-received);
            color: var(--text-color);
        }

        .message-avatar {
            width: 52px;
            height: 50px;
            border-radius: 50%;
            margin: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user1 .message-avatar {
            background-color: var(--user1-color);
            color: white;
        }

        .user2 .message-avatar {
            background-color: var(--user2-color);
            color: white;
        }

        .message-content {
            display: flex;
            flex-direction: column;
            max-width: 100%;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.1);
        }

        .message.sent .message-bubble {
            border-top-right-radius: 5px;
        }

        .message.received .message-bubble {
            border-top-left-radius: 5px;
        }

        .message-text {
            margin-bottom: 5px;
        }

        .message-image {
            max-width: 300px;
            max-height: 300px;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-bottom: 5px;
        }

        .message-image:hover {
            transform: scale(1.02);
        }

        .message-file {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-bottom: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .message-file:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .message-file-icon {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .message-file-info {
            flex: 1;
        }

        .message-file-name {
            font-weight: 500;
            margin-bottom: 2px;
        }

        .message-file-size {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .message-info {
            display: flex;
            align-items: center;
            margin-top: 5px;
            font-size: 0.75rem;
            color: #666;
        }

        .message.sent .message-info {
            flex-direction: row-reverse;
        }

        .message-time {
            margin: 0 5px;
        }

        .message-status {
            color: var(--user1-color);
        }

        .user2 .message-status {
            color: var(--user2-color);
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #666;
            font-style: italic;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .typing-indicator.show {
            opacity: 1;
        }

        .typing-dots {
            display: flex;
            margin-left: 10px;
        }

        .typing-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #666;
            margin: 0 2px;
            animation: typing 1.4s infinite;
        }

        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }

        .message-input-container {
            padding: 15px 20px;
            background-color: white;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }

        .message-input-form {
            display: flex;
            width: 100%;
            align-items: center;
        }

        .input-actions {
            display: flex;
            gap: 15px;
            margin-right: 15px;
        }

        .input-actions button {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s;
            position: relative;
        }

        .input-actions button:hover {
            color: var(--user1-color);
        }

        .user2 .input-actions button:hover {
            color: var(--user2-color);
        }

        .file-input {
            display: none;
        }

        .send-button {
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            color: white;
        }

        .send-button:hover {
            transform: scale(1.05);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }

        .user1 .send-button {
            background-color: var(--user1-color);
        }

        .user2 .send-button {
            background-color: var(--user2-color);
        }

        .message-input-wrapper {
            flex: 1;
            position: relative;
        }

        .message-input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 25px;
            border: 1px solid var(--border-color);
            outline: none;
            resize: none;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }

        .user1 .message-input:focus {
            border-color: var(--user1-color);
        }

        .user2 .message-input:focus {
            border-color: var(--user2-color);
        }

        .connection-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .connection-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--online-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }

        /* Image Preview Modal */
        .image-preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 3000;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .image-preview-modal.active {
            display: flex;
        }

        .image-preview-container {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .image-preview-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .image-preview-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .image-preview-close:hover {
            transform: scale(1.1);
        }

        /* WebRTC Call Modal Styles */
        .webrtc-call-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .webrtc-call-modal.active {
            display: flex;
        }

        .webrtc-call-container {
            width: 90%;
            max-width: 1200px;
            height: 80%;
            display: flex;
            flex-direction: column;
            background-color: var(--call-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .webrtc-call-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
        }

        .webrtc-call-timer {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .webrtc-call-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .webrtc-call-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #4caf50;
            animation: pulse 2s infinite;
        }

        .webrtc-call-status.connecting {
            background-color: #ff9800;
        }

        .webrtc-call-status.error {
            background-color: #f44336;
        }

        .webrtc-call-body {
            flex: 1;
            display: flex;
            position: relative;
            background-color: #000;
        }

        .webrtc-remote-video {
            flex: 1;
            position: relative;
            background-color: #111;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .webrtc-remote-video video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .webrtc-remote-video-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
        }

        .webrtc-remote-video-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .webrtc-remote-video-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user1 .webrtc-remote-video-avatar {
            background-color: var(--user1-color);
        }

        .user2 .webrtc-remote-video-avatar {
            background-color: var(--user2-color);
        }

        .webrtc-remote-video-name {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .webrtc-remote-video-status {
            font-size: 1rem;
            opacity: 0.8;
        }

        .webrtc-local-video {
            position: absolute;
            width: 200px;
            height: 150px;
            bottom: 20px;
            right: 20px;
            background-color: #333;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .webrtc-local-video video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        .webrtc-local-video-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .user1 .webrtc-local-video-placeholder {
            background-color: var(--user1-color);
        }

        .user2 .webrtc-local-video-placeholder {
            background-color: var(--user2-color);
        }

        .webrtc-call-controls {
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .webrtc-call-control-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            color: white;
        }

        .webrtc-call-control-btn:hover {
            transform: scale(1.05);
        }

        .webrtc-mic-btn {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .webrtc-mic-btn.muted {
            background-color: #f44336;
        }

        .webrtc-video-btn {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .webrtc-video-btn.disabled {
            background-color: #f44336;
        }

        .webrtc-screen-btn {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .webrtc-screen-btn.active {
            background-color: #4caf50;
        }

        .webrtc-end-call-btn {
            background-color: #f44336;
        }

        .webrtc-voice-call-container {
            width: 90%;
            max-width: 500px;
            height: 60%;
            display: flex;
            flex-direction: column;
            background-color: var(--call-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .webrtc-voice-call-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .webrtc-voice-call-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: white;
            overflow: hidden;
        }

        .webrtc-voice-call-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user1 .webrtc-voice-call-avatar {
            background-color: var(--user1-color);
        }

        .user2 .webrtc-voice-call-avatar {
            background-color: var(--user2-color);
        }

        .webrtc-voice-call-name {
            font-size: 2rem;
            color: white;
            margin-bottom: 10px;
        }

        .webrtc-voice-call-status {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
        }

        .webrtc-audio-visualizer {
            display: flex;
            align-items: center;
            gap: 3px;
            height: 40px;
            margin-bottom: 30px;
        }

        .webrtc-audio-bar {
            width: 4px;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 2px;
            animation: audioWave 1s infinite ease-in-out;
        }

        .webrtc-audio-bar:nth-child(1) { animation-delay: 0s; height: 10px; }
        .webrtc-audio-bar:nth-child(2) { animation-delay: 0.1s; height: 20px; }
        .webrtc-audio-bar:nth-child(3) { animation-delay: 0.2s; height: 15px; }
        .webrtc-audio-bar:nth-child(4) { animation-delay: 0.3s; height: 25px; }
        .webrtc-audio-bar:nth-child(5) { animation-delay: 0.4s; height: 18px; }
        .webrtc-audio-bar:nth-child(6) { animation-delay: 0.5s; height: 22px; }
        .webrtc-audio-bar:nth-child(7) { animation-delay: 0.6s; height: 12px; }

        @keyframes audioWave {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(1.5); }
        }

        .webrtc-incoming-call-container {
            width: 90%;
            max-width: 400px;
            height: auto;
            display: flex;
            flex-direction: column;
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .webrtc-incoming-call-header {
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, var(--user1-color), #6b8cff);
            color: white;
        }

        .user2 .webrtc-incoming-call-header {
            background: linear-gradient(135deg, var(--user2-color), #ff8787);
        }

        .webrtc-incoming-call-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            background-color: white;
            color: var(--text-color);
            overflow: hidden;
        }

        .webrtc-incoming-call-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .webrtc-incoming-call-name {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .webrtc-incoming-call-type {
            font-size: 1rem;
            opacity: 0.9;
        }

        .webrtc-incoming-call-controls {
            padding: 30px;
            display: flex;
            justify-content: space-around;
        }

        .webrtc-incoming-call-btn {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            color: white;
        }

        .webrtc-incoming-call-btn:hover {
            transform: scale(1.05);
        }

        .webrtc-decline-call-btn {
            background-color: #f44336;
        }

        .webrtc-accept-call-btn {
            background-color: #4caf50;
        }

        .permission-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .permission-modal.active {
            display: flex;
        }

        .permission-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 400px;
            text-align: center;
        }

        .permission-icon {
            font-size: 3rem;
            color: var(--user1-color);
            margin-bottom: 20px;
        }

        .permission-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .permission-message {
            color: #666;
            margin-bottom: 20px;
        }

        .permission-button {
            background-color: var(--user1-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }

        .permission-button:hover {
            background-color: #3a5bd9;
        }

        /* Profile Edit Modal Styles */
        .profile-edit-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .profile-edit-modal.active {
            display: flex;
        }

        .profile-edit-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .profile-edit-header {
            margin-bottom: 25px;
        }

        .profile-edit-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .profile-edit-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .profile-avatar-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
        }

        .profile-avatar-preview {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--user1-color);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
        }

        .profile-avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user2 .profile-avatar-preview {
            border-color: var(--user2-color);
        }

        .profile-avatar-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--user1-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s;
        }

        .profile-avatar-upload-btn:hover {
            transform: scale(1.1);
        }

        .user2 .profile-avatar-upload-btn {
            background-color: var(--user2-color);
        }

        .profile-avatar-upload-input {
            display: none;
        }

        .profile-edit-form {
            margin-bottom: 25px;
        }

        .profile-edit-field {
            margin-bottom: 20px;
            text-align: left;
        }

        .profile-edit-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .profile-edit-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .profile-edit-input:focus {
            outline: none;
            border-color: var(--user1-color);
        }

        .user2 .profile-edit-input:focus {
            border-color: var(--user2-color);
        }

        .profile-edit-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .profile-edit-btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
        }

        .profile-edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-edit-cancel {
            background-color: #f5f5f5;
            color: var(--text-color);
        }

        .profile-edit-save {
            background-color: var(--user1-color);
            color: white;
        }

        .user2 .profile-edit-save {
            background-color: var(--user2-color);
        }

        .profile-edit-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .profile-option-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid var(--border-color);
            background-color: white;
            cursor: pointer;
            transition: transform 0.2s, border-color 0.2s;
            overflow: hidden;
        }

        .profile-option-btn:hover {
            transform: scale(1.05);
            border-color: var(--user1-color);
        }

        .user2 .profile-option-btn:hover {
            border-color: var(--user2-color);
        }

        .profile-option-btn img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-option-btn.selected {
            border-color: var(--user1-color);
            border-width: 3px;
        }

        .user2 .profile-option-btn.selected {
            border-color: var(--user2-color);
        }

        @media (max-width: 768px) {
            .app-container {
                flex-direction: column;
            }
            
            .divider {
                width: 100%;
                height: 4px;
            }
            
            .chat-panel {
                height: 50vh;
            }
            
            .chat-panel.user1 {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }
            
            .webrtc-local-video {
                width: 120px;
                height: 90px;
                bottom: 10px;
                right: 10px;
            }
            
            .webrtc-call-control-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- User 1 Panel (Left) -->
        <div class="chat-panel user1">
            <div class="connection-status">
                <span class="connection-dot"></span>
                <span id="connectionStatus1">Connected</span>
            </div>
            
            <div class="chat-header">
                <div class="chat-user-info">
                    <div class="user-avatar" id="user1Avatar" onclick="openProfileEditModal('user1')">
                        <span id="user1AvatarText">U1</span>
                        <span class="status-indicator"></span>
                    </div>
                    <div class="user-details">
                        <div class="user-name" id="user1Name">User 1</div>
                        <div class="user-status">Chatting with User 2</div>
                    </div>
                </div>
                <div class="chat-actions">
                    <button id="voiceCallBtn1" onclick="initiateWebRTCCall('user1', 'user2', 'voice')">
                        <i class="fas fa-phone"></i>
                        <span class="permission-badge" id="micPermission1" style="display: none;">!</span>
                    </button>
                    <button id="videoCallBtn1" onclick="initiateWebRTCCall('user1', 'user2', 'video')">
                        <i class="fas fa-video"></i>
                        <span class="permission-badge" id="cameraPermission1" style="display: none;">!</span>
                    </button>
                    <button><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>
            
            <div class="messages-container" id="messages1">
                <div class="date-divider">
                    <span>Today</span>
                </div>
            </div>
            
            <div class="typing-indicator" id="typing1">
                User 2 is typing
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            
            <div class="message-input-container">
                <form class="message-input-form" id="form1">
                    <div class="input-actions">
                        <button type="button">
                            <i class="fas fa-smile"></i>
                        </button>
                        <button type="button" onclick="document.getElementById('fileInput1').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <input type="file" id="fileInput1" class="file-input" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt" onchange="handleFileSelect(event, 'user1')">
                    </div>

                    <div class="message-input-wrapper">
                        <input type="text" class="message-input" id="input1" placeholder="Type a message..." required>
                    </div>

                    <button type="submit" class="send-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Divider -->
        <div class="divider"></div>
        
        <!-- User 2 Panel (Right) -->
        <div class="chat-panel user2">
            <div class="connection-status">
                <span class="connection-dot"></span>
                <span id="connectionStatus2">Connected</span>
            </div>
            
            <div class="chat-header">
                <div class="chat-user-info">
                    <div class="user-avatar" id="user2Avatar" onclick="openProfileEditModal('user2')">
                        <span id="user2AvatarText">U2</span>
                        <span class="status-indicator"></span>
                    </div>
                    <div class="user-details">
                        <div class="user-name" id="user2Name">User 2</div>
                        <div class="user-status">Chatting with User 1</div>
                    </div>
                </div>
                <div class="chat-actions">
                    <button id="voiceCallBtn2" onclick="initiateWebRTCCall('user2', 'user1', 'voice')">
                        <i class="fas fa-phone"></i>
                        <span class="permission-badge" id="micPermission2" style="display: none;">!</span>
                    </button>
                    <button id="videoCallBtn2" onclick="initiateWebRTCCall('user2', 'user1', 'video')">
                        <i class="fas fa-video"></i>
                        <span class="permission-badge" id="cameraPermission2" style="display: none;">!</span>
                    </button>
                    <button><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>
            
            <div class="messages-container" id="messages2">
                <div class="date-divider">
                    <span>Today</span>
                </div>
            </div>
            
            <div class="typing-indicator" id="typing2">
                User 1 is typing
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            
            <div class="message-input-container">
                <form class="message-input-form" id="form2">
                    <div class="input-actions">
                        <button type="button">
                            <i class="fas fa-smile"></i>
                        </button>
                        <button type="button" onclick="document.getElementById('fileInput2').click()">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <input type="file" id="fileInput2" class="file-input" accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt" onchange="handleFileSelect(event, 'user2')">
                    </div>

                    <div class="message-input-wrapper">
                        <input type="text" class="message-input" id="input2" placeholder="Type a message..." required>
                    </div>

                    <button type="submit" class="send-button">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="image-preview-modal" id="imagePreviewModal" onclick="closeImagePreview()">
        <div class="image-preview-container">
            <span class="image-preview-close">&times;</span>
            <img id="previewImage" class="image-preview-img" src="" alt="Preview">
        </div>
    </div>

    <!-- WebRTC Voice Call Modal -->
    <div class="webrtc-call-modal" id="webrtcVoiceCallModal">
        <div class="webrtc-voice-call-container">
            <div class="webrtc-call-header">
                <div class="webrtc-call-timer" id="webrtcVoiceCallTimer">00:00</div>
                <div class="webrtc-call-status">
                    <span class="webrtc-call-status-dot" id="webrtcVoiceCallStatus"></span>
                    <span id="webrtcVoiceCallStatusText">Connecting...</span>
                </div>
            </div>
            <div class="webrtc-voice-call-body">
                <div class="webrtc-voice-call-avatar" id="webrtcVoiceCallAvatar">
                    <span id="webrtcVoiceCallAvatarText">U2</span>
                </div>
                <div class="webrtc-voice-call-name" id="webrtcVoiceCallName">User 2</div>
                <div class="webrtc-voice-call-status" id="webrtcVoiceCallStatus">Connected</div>
                <div class="webrtc-audio-visualizer" id="webrtcAudioVisualizer">
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                    <div class="webrtc-audio-bar"></div>
                </div>
            </div>
            <div class="webrtc-call-controls">
                <button class="webrtc-call-control-btn webrtc-mic-btn" id="webrtcVoiceMicBtn" onclick="toggleWebRTCMic('voice')">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="webrtc-call-control-btn webrtc-end-call-btn" onclick="endWebRTCCall('voice')">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- WebRTC Video Call Modal -->
    <div class="webrtc-call-modal" id="webrtcVideoCallModal">
        <div class="webrtc-call-container">
            <div class="webrtc-call-header">
                <div class="webrtc-call-timer" id="webrtcVideoCallTimer">00:00</div>
                <div class="webrtc-call-status">
                    <span class="webrtc-call-status-dot" id="webrtcVideoCallStatus"></span>
                    <span id="webrtcVideoCallStatusText">Connecting...</span>
                </div>
            </div>
            <div class="webrtc-call-body">
                <div class="webrtc-remote-video">
                    <video id="remoteVideo" autoplay playsinline></video>
                    <div class="webrtc-remote-video-placeholder" id="remoteVideoPlaceholder">
                        <div class="webrtc-remote-video-avatar" id="remoteVideoAvatar">
                            <span id="remoteVideoAvatarText">U2</span>
                        </div>
                        <div class="webrtc-remote-video-name" id="remoteVideoName">User 2</div>
                        <div class="webrtc-remote-video-status" id="remoteVideoStatus">Waiting for video...</div>
                    </div>
                </div>
                <div class="webrtc-local-video">
                    <video id="localVideo" autoplay muted playsinline></video>
                    <div class="webrtc-local-video-placeholder" id="localVideoPlaceholder">U1</div>
                </div>
            </div>
            <div class="webrtc-call-controls">
                <button class="webrtc-call-control-btn webrtc-mic-btn" id="webrtcVideoMicBtn" onclick="toggleWebRTCMic('video')">
                    <i class="fas fa-microphone"></i>
                </button>
                <button class="webrtc-call-control-btn webrtc-video-btn" id="webrtcVideoBtn" onclick="toggleWebRTCVideo()">
                    <i class="fas fa-video"></i>
                </button>
                <button class="webrtc-call-control-btn webrtc-screen-btn" id="webrtcScreenBtn" onclick="toggleWebRTCScreen()">
                    <i class="fas fa-desktop"></i>
                </button>
                <button class="webrtc-call-control-btn webrtc-end-call-btn" onclick="endWebRTCCall('video')">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- WebRTC Incoming Call Modal -->
    <div class="webrtc-call-modal" id="webrtcIncomingCallModal">
        <div class="webrtc-incoming-call-container" id="webrtcIncomingCallContainer">
            <div class="webrtc-incoming-call-header" id="webrtcIncomingCallHeader">
                <div class="webrtc-incoming-call-avatar" id="webrtcIncomingCallAvatar">
                    <span id="webrtcIncomingCallAvatarText">U1</span>
                </div>
                <div class="webrtc-incoming-call-name" id="webrtcIncomingCallName">User 1</div>
                <div class="webrtc-incoming-call-type" id="webrtcIncomingCallType">Incoming Voice Call</div>
            </div>
            <div class="webrtc-incoming-call-controls">
                <button class="webrtc-incoming-call-btn webrtc-decline-call-btn" onclick="declineWebRTCCall()">
                    <i class="fas fa-phone-slash"></i>
                </button>
                <button class="webrtc-incoming-call-btn webrtc-accept-call-btn" onclick="acceptWebRTCCall()">
                    <i class="fas fa-phone"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Permission Modal -->
    <div class="permission-modal" id="permissionModal">
        <div class="permission-container">
            <div class="permission-icon">
                <i class="fas fa-microphone"></i>
            </div>
            <div class="permission-title">Microphone & Camera Access</div>
            <div class="permission-message">
                Please allow access to your microphone and camera to make calls. This is required for WebRTC functionality.
            </div>
            <button class="permission-button" onclick="requestPermissions()">Grant Permissions</button>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div class="profile-edit-modal" id="profileEditModal">
        <div class="profile-edit-container">
            <div class="profile-edit-header">
                <div class="profile-edit-title" id="profileEditTitle">Edit Profile</div>
                <div class="profile-edit-subtitle">Customize your profile information</div>
            </div>
            
            <div class="profile-avatar-container">
                <div class="profile-avatar-preview" id="profileAvatarPreview">
                    <span id="profileAvatarPreviewText">U1</span>
                </div>
                <label class="profile-avatar-upload-btn" for="profileAvatarUpload">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="profileAvatarUpload" class="profile-avatar-upload-input" accept="image/*">
            </div>
            
            <div class="profile-edit-options">
                <button class="profile-option-btn" onclick="selectDefaultAvatar('https://picsum.photos/seed/user1/200/200.jpg')">
                    <img src="https://picsum.photos/seed/user1/200/200.jpg" alt="Avatar 1">
                </button>
                <button class="profile-option-btn" onclick="selectDefaultAvatar('https://picsum.photos/seed/user2/200/200.jpg')">
                    <img src="https://picsum.photos/seed/user2/200/200.jpg" alt="Avatar 2">
                </button>
                <button class="profile-option-btn" onclick="selectDefaultAvatar('https://picsum.photos/seed/user3/200/200.jpg')">
                    <img src="https://picsum.photos/seed/user3/200/200.jpg" alt="Avatar 3">
                </button>
                <button class="profile-option-btn" onclick="selectDefaultAvatar('https://picsum.photos/seed/user4/200/200.jpg')">
                    <img src="https://picsum.photos/seed/user4/200/200.jpg" alt="Avatar 4">
                </button>
            </div>
            
            <div class="profile-edit-form">
                <div class="profile-edit-field">
                    <label class="profile-edit-label" for="profileName">Display Name</label>
                    <input type="text" id="profileName" class="profile-edit-input" placeholder="Enter your name">
                </div>
            </div>
            
            <div class="profile-edit-buttons">
                <button class="profile-edit-btn profile-edit-cancel" onclick="closeProfileEditModal()">Cancel</button>
                <button class="profile-edit-btn profile-edit-save" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        // WebRTC Global Variables
        let localStream = null;
        let remoteStream = null;
        let peerConnection = null;
        let webRTCTimer = null;
        let webRTCSeconds = 0;
        let currentWebRTCCallType = null;
        let webRTCCallInitiator = null;
        let webRTCCallReceiver = null;
        let webRTCMicEnabled = true;
        let webRTCVideoEnabled = true;
        let webRTCScreenEnabled = false;

        // Profile Edit Variables
        let currentEditingUser = null;
        let selectedAvatarUrl = null;
        let profileData = {
            user1: {
                name: 'User 1',
                avatar: null
            },
            user2: {
                name: 'User 2',
                avatar: null
            }
        };

        // File Upload Variables
        let selectedFiles = {
            user1: null,
            user2: null
        };

        // WebRTC Configuration
        const configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' },
                { urls: 'stun:stun4.l.google.com:19302' }
            ]
        };

        // Initialize the chat
        document.addEventListener('DOMContentLoaded', function() {
            // Check for media permissions on load
            checkMediaPermissions();
            
            // Load profile data from localStorage
            loadProfileData();
            
            // Load messages from localStorage
            loadMessages();
            
            // Setup form listeners
            document.getElementById('form1').addEventListener('submit', function(e) {
                e.preventDefault();
                sendMessage('input1', 'User 1', 'User 2', 'messages1', 'messages2', 'typing2', 'user1');
            });
            
            document.getElementById('form2').addEventListener('submit', function(e) {
                e.preventDefault();
                sendMessage('input2', 'User 2', 'User 1', 'messages2', 'messages1', 'typing1', 'user2');
            });
            
            // Setup typing indicators
            setupTypingIndicator('input1', 'typing2');
            setupTypingIndicator('input2', 'typing1');
            
            // Setup profile image upload
            document.getElementById('profileAvatarUpload').addEventListener('change', handleAvatarUpload);
        });

        // File Upload Functions
        function handleFileSelect(event, userId) {
            const file = event.target.files[0];
            if (file) {
                selectedFiles[userId] = file;
                
                // Auto-send the file
                const sender = userId === 'user1' ? 'User 1' : 'User 2';
                const receiver = userId === 'user1' ? 'User 2' : 'User 1';
                const senderMessages = userId === 'user1' ? 'messages1' : 'messages2';
                const receiverMessages = userId === 'user1' ? 'messages2' : 'messages1';
                const typingId = userId === 'user1' ? 'typing2' : 'typing1';
                
                sendFile(file, sender, receiver, senderMessages, receiverMessages, typingId, userId);
                
                // Clear the file input
                event.target.value = '';
            }
        }

        function sendFile(file, sender, receiver, senderMessages, receiverMessages, typingId, userId) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const fileData = e.target.result;
                const timestamp = new Date().toISOString();
                
                // Create message object for storage
                const messageObj = {
                    sender: sender,
                    receiver: receiver,
                    type: file.type.startsWith('image/') ? 'image' : 'file',
                    fileName: file.name,
                    fileSize: formatFileSize(file.size),
                    data: fileData,
                    timestamp: timestamp
                };
                
                // Save message to localStorage
                saveMessage(messageObj);
                
                // Add message to sender's view
                addMessageToDOM(senderMessages, 'sent', null, sender, timestamp, messageObj);
                
                // Add message to receiver's view
                addMessageToDOM(receiverMessages, 'received', null, sender, timestamp, messageObj);
                
                // Hide typing indicator
                document.getElementById(typingId).classList.remove('show');
                
                // Simulate read receipt after 1 second
                setTimeout(() => {
                    updateMessageStatus(senderMessages, 'read');
                }, 1000);
            };
            
            reader.readAsDataURL(file);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function openImagePreview(imageSrc) {
            const modal = document.getElementById('imagePreviewModal');
            const previewImg = document.getElementById('previewImage');
            previewImg.src = imageSrc;
            modal.classList.add('active');
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewModal').classList.remove('active');
        }

        // Message Storage Functions
        function saveMessage(message) {
            // Get existing messages from localStorage
            let messages = JSON.parse(localStorage.getItem('chatMessages') || '[]');
            
            // Add new message
            messages.push(message);
            
            // Save back to localStorage
            localStorage.setItem('chatMessages', JSON.stringify(messages));
        }

        function loadMessages() {
            // Get messages from localStorage
            const messages = JSON.parse(localStorage.getItem('chatMessages') || '[]');
            
            // Clear existing messages
            document.getElementById('messages1').innerHTML = '<div class="date-divider"><span>Today</span></div>';
            document.getElementById('messages2').innerHTML = '<div class="date-divider"><span>Today</span></div>';
            
            // Add each message to appropriate container
            messages.forEach(message => {
                if (message.sender === 'User 1') {
                    addMessageToDOM('messages1', 'sent', message.text, message.sender, message.timestamp, message);
                    addMessageToDOM('messages2', 'received', message.text, message.sender, message.timestamp, message);
                } else {
                    addMessageToDOM('messages1', 'received', message.text, message.sender, message.timestamp, message);
                    addMessageToDOM('messages2', 'sent', message.text, message.sender, message.timestamp, message);
                }
            });
            
            // If no messages, add initial messages
            if (messages.length === 0) {
                addMessage('messages1', 'sent', 'Hey! How are you doing?', 'User 1');
                addMessage('messages2', 'received', 'Hey! How are you doing?', 'User 1');
                
                setTimeout(() => {
                    addMessage('messages2', 'sent', "I'm doing great! Just working on some projects.", 'User 2');
                    addMessage('messages1', 'received', "I'm doing great! Just working on some projects.", 'User 2');
                }, 1000);
            }
        }

        function loadProfileData() {
            // Try to load profile data from localStorage
            const savedData = localStorage.getItem('chatProfileData');
            if (savedData) {
                profileData = JSON.parse(savedData);
            }
            
            // Update UI with loaded data
            updateProfileUI('user1');
            updateProfileUI('user2');
        }

        function saveProfileData() {
            // Save profile data to localStorage
            localStorage.setItem('chatProfileData', JSON.stringify(profileData));
        }

        function updateProfileUI(userId) {
            const userData = profileData[userId];
            const userNumber = userId.replace('user', '');
            
            // Update avatar
            const avatarElement = document.getElementById(`${userId}Avatar`);
            const avatarTextElement = document.getElementById(`${userId}AvatarText`);
            
            if (userData.avatar) {
                avatarElement.innerHTML = `
                    <img src="${userData.avatar}" alt="User ${userNumber}">
                    <span class="status-indicator"></span>
                `;
            } else {
                avatarElement.innerHTML = `
                    <span id="${userId}AvatarText">U${userNumber}</span>
                    <span class="status-indicator"></span>
                `;
            }
            
            // Update name
            document.getElementById(`${userId}Name`).textContent = userData.name;
        }

        function openProfileEditModal(userId) {
            currentEditingUser = userId;
            const userData = profileData[userId];
            const userNumber = userId.replace('user', '');
            
            // Update modal title
            document.getElementById('profileEditTitle').textContent = `Edit User ${userNumber} Profile`;
            
            // Update avatar preview
            const avatarPreview = document.getElementById('profileAvatarPreview');
            const avatarPreviewText = document.getElementById('profileAvatarPreviewText');
            
            if (userData.avatar) {
                avatarPreview.innerHTML = `<img src="${userData.avatar}" alt="User ${userNumber}">`;
            } else {
                avatarPreview.innerHTML = `<span id="profileAvatarPreviewText">U${userNumber}</span>`;
            }
            
            // Update name input
            document.getElementById('profileName').value = userData.name;
            
            // Reset selected avatar
            selectedAvatarUrl = userData.avatar;
            
            // Update modal styling based on user
            const modal = document.getElementById('profileEditModal');
            modal.classList.remove('user1', 'user2');
            modal.classList.add(userId);
            
            // Show modal
            modal.classList.add('active');
        }

        function closeProfileEditModal() {
            document.getElementById('profileEditModal').classList.remove('active');
            currentEditingUser = null;
            selectedAvatarUrl = null;
        }

        function handleAvatarUpload(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    selectedAvatarUrl = e.target.result;
                    
                    // Update preview
                    const avatarPreview = document.getElementById('profileAvatarPreview');
                    avatarPreview.innerHTML = `<img src="${selectedAvatarUrl}" alt="Profile">`;
                };
                reader.readAsDataURL(file);
            }
        }

        function selectDefaultAvatar(url) {
            selectedAvatarUrl = url;
            
            // Update preview
            const avatarPreview = document.getElementById('profileAvatarPreview');
            avatarPreview.innerHTML = `<img src="${selectedAvatarUrl}" alt="Profile">`;
            
            // Update selected state
            document.querySelectorAll('.profile-option-btn').forEach(btn => {
                btn.classList.remove('selected');
            });
            event.target.closest('.profile-option-btn').classList.add('selected');
        }

        function saveProfile() {
            if (!currentEditingUser) return;
            
            // Update profile data
            profileData[currentEditingUser].name = document.getElementById('profileName').value;
            profileData[currentEditingUser].avatar = selectedAvatarUrl;
            
            // Save to localStorage
            saveProfileData();
            
            // Update UI
            updateProfileUI(currentEditingUser);
            
            // Close modal
            closeProfileEditModal();
        }

        function sendMessage(inputId, sender, receiver, senderMessages, receiverMessages, typingId, userId) {
            const input = document.getElementById(inputId);
            const message = input.value.trim();
            
            if (!message && !selectedFiles[userId]) return;
            
            // Get current timestamp
            const timestamp = new Date().toISOString();
            
            // Create message object for storage
            const messageObj = {
                sender: sender,
                receiver: receiver,
                text: message || null,
                type: 'text',
                timestamp: timestamp
            };
            
            // Save message to localStorage
            saveMessage(messageObj);
            
            // Add message to sender's view
            addMessageToDOM(senderMessages, 'sent', message, sender, timestamp, messageObj);
            
            // Add message to receiver's view
            addMessageToDOM(receiverMessages, 'received', message, sender, timestamp, messageObj);
            
            // Clear input
            input.value = '';
            
            // Hide typing indicator
            document.getElementById(typingId).classList.remove('show');
            
            // Simulate read receipt after 1 second
            setTimeout(() => {
                updateMessageStatus(senderMessages, 'read');
            }, 1000);
        }

        function addMessage(containerId, type, text, sender) {
            // Create a timestamp for new messages
            const timestamp = new Date().toISOString();
            
            // Add to DOM and save to localStorage
            addMessageToDOM(containerId, type, text, sender, timestamp);
            
            // Create message object for storage
            const messageObj = {
                sender: sender,
                text: text,
                type: 'text',
                timestamp: timestamp
            };
            
            // Save to localStorage
            saveMessage(messageObj);
        }

        function addMessageToDOM(containerId, type, text, sender, timestamp, messageObj = null) {
            const container = document.getElementById(containerId);
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            // Parse timestamp or use current time
            const messageTime = timestamp ? new Date(timestamp) : new Date();
            const timeString = messageTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            // Determine avatar based on sender and type
            let avatarHtml;
            if (type === 'sent') {
                // For sent messages, use current user's avatar
                const userId = containerId === 'messages1' ? 'user1' : 'user2';
                const userData = profileData[userId];
                
                if (userData.avatar) {
                    avatarHtml = `<img src="${userData.avatar}" alt="${sender}">`;
                } else {
                    avatarHtml = sender === 'User 1' ? 'U1' : 'U2';
                }
            } else {
                // For received messages, use the other user's avatar
                const userId = containerId === 'messages1' ? 'user2' : 'user1';
                const userData = profileData[userId];
                
                if (userData.avatar) {
                    avatarHtml = `<img src="${userData.avatar}" alt="${sender}">`;
                } else {
                    avatarHtml = sender === 'User 1' ? 'U1' : 'U2';
                }
            }
            
            // Build message content based on type
            let messageContent = '';
            if (messageObj) {
                if (messageObj.type === 'image') {
                    messageContent = `
                        <img src="${messageObj.data}" alt="${messageObj.fileName}" class="message-image" onclick="openImagePreview('${messageObj.data}')">
                    `;
                } else if (messageObj.type === 'file') {
                    messageContent = `
                        <div class="message-file" onclick="downloadFile('${messageObj.data}', '${messageObj.fileName}')">
                            <i class="fas fa-file message-file-icon"></i>
                            <div class="message-file-info">
                                <div class="message-file-name">${messageObj.fileName}</div>
                                <div class="message-file-size">${messageObj.fileSize}</div>
                            </div>
                        </div>
                    `;
                } else {
                    messageContent = `<div class="message-text">${messageObj.text}</div>`;
                }
            } else {
                messageContent = `<div class="message-text">${text}</div>`;
            }
            
            messageDiv.innerHTML = `
                <div class="message-avatar">${avatarHtml}</div>
                <div class="message-content">
                    <div class="message-bubble">
                        ${messageContent}
                    </div>
                    <div class="message-info">
                        <span class="message-time">${timeString}</span>
                        ${type === 'sent' ? '<span class="message-status"><i class="fas fa-check"></i></span>' : ''}
                    </div>
                </div>
            `;
            
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        function downloadFile(data, fileName) {
            const link = document.createElement('a');
            link.href = data;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function updateMessageStatus(containerId, status) {
            const container = document.getElementById(containerId);
            const lastMessage = container.querySelector('.message.sent:last-child .message-status i');
            if (lastMessage) {
                if (status === 'delivered') {
                    lastMessage.className = 'fas fa-check-double';
                } else if (status === 'read') {
                    lastMessage.className = 'fas fa-check-double';
                    lastMessage.style.color = '#4a6cf7';
                }
            }
        }

        function setupTypingIndicator(inputId, typingId) {
            let typingTimer;
            const input = document.getElementById(inputId);
            
            input.addEventListener('input', function() {
                document.getElementById(typingId).classList.add('show');
                
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    document.getElementById(typingId).classList.remove('show');
                }, 1000);
            });
        }

        // WebRTC Functions
        async function checkMediaPermissions() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                stream.getTracks().forEach(track => track.stop());
                
                // Hide permission badges
                document.getElementById('micPermission1').style.display = 'none';
                document.getElementById('micPermission2').style.display = 'none';
                document.getElementById('cameraPermission1').style.display = 'none';
                document.getElementById('cameraPermission2').style.display = 'none';
                
                // Enable call buttons
                document.getElementById('voiceCallBtn1').disabled = false;
                document.getElementById('voiceCallBtn2').disabled = false;
                document.getElementById('videoCallBtn1').disabled = false;
                document.getElementById('videoCallBtn2').disabled = false;
            } catch (error) {
                console.error('Media permissions not granted:', error);
                
                // Show permission badges
                document.getElementById('micPermission1').style.display = 'flex';
                document.getElementById('micPermission2').style.display = 'flex';
                document.getElementById('cameraPermission1').style.display = 'flex';
                document.getElementById('cameraPermission2').style.display = 'flex';
                
                // Disable call buttons
                document.getElementById('voiceCallBtn1').disabled = true;
                document.getElementById('voiceCallBtn2').disabled = true;
                document.getElementById('videoCallBtn1').disabled = true;
                document.getElementById('videoCallBtn2').disabled = true;
            }
        }

        async function requestPermissions() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                stream.getTracks().forEach(track => track.stop());
                
                document.getElementById('permissionModal').classList.remove('active');
                await checkMediaPermissions();
            } catch (error) {
                console.error('Permission denied:', error);
                alert('Please allow microphone and camera access to make calls.');
            }
        }

        async function initiateWebRTCCall(initiator, receiver, callType) {
            // Check permissions first
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('WebRTC is not supported in your browser');
                return;
            }

            try {
                // Request media permissions
                const constraints = callType === 'video' 
                    ? { audio: true, video: true }
                    : { audio: true, video: false };
                
                localStream = await navigator.mediaDevices.getUserMedia(constraints);
                
                webRTCCallInitiator = initiator;
                webRTCCallReceiver = receiver;
                currentWebRTCCallType = callType;
                
                // Show incoming call to receiver (simulated)
                showWebRTCIncomingCall(initiator, receiver, callType);
                
                // Simulate call acceptance after 2 seconds
                setTimeout(() => {
                    acceptWebRTCCall();
                }, 2000);
                
            } catch (error) {
                console.error('Error accessing media devices:', error);
                document.getElementById('permissionModal').classList.add('active');
            }
        }

        function showWebRTCIncomingCall(initiator, receiver, callType) {
            const modal = document.getElementById('webrtcIncomingCallModal');
            const avatar = document.getElementById('webrtcIncomingCallAvatar');
            const name = document.getElementById('webrtcIncomingCallName');
            const type = document.getElementById('webrtcIncomingCallType');
            const header = document.getElementById('webrtcIncomingCallHeader');
            
            // Set caller info
            const initiatorData = profileData[initiator];
            const initiatorNumber = initiator.replace('user', '');
            
            if (initiatorData.avatar) {
                avatar.innerHTML = `<img src="${initiatorData.avatar}" alt="User ${initiatorNumber}">`;
            } else {
                avatar.innerHTML = `<span id="webrtcIncomingCallAvatarText">U${initiatorNumber}</span>`;
            }
            
            name.textContent = initiatorData.name;
            type.textContent = `Incoming ${callType} Call`;
            
            // Set header color based on caller
            if (initiator === 'user1') {
                header.style.background = 'linear-gradient(135deg, var(--user1-color), #6b8cff)';
            } else {
                header.style.background = 'linear-gradient(135deg, var(--user2-color), #ff8787)';
            }
            
            modal.classList.add('active');
        }

        async function acceptWebRTCCall() {
            // Hide incoming call modal
            document.getElementById('webrtcIncomingCallModal').classList.remove('active');
            
            // Start the appropriate WebRTC call
            if (currentWebRTCCallType === 'voice') {
                await startWebRTCVoiceCall();
            } else if (currentWebRTCCallType === 'video') {
                await startWebRTCVideoCall();
            }
        }

        function declineWebRTCCall() {
            // Hide incoming call modal
            document.getElementById('webrtcIncomingCallModal').classList.remove('active');
            
            // Stop local stream if exists
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            
            // Add call declined message
            const caller = webRTCCallInitiator === 'user1' ? profileData.user1.name : profileData.user2.name;
            const receiver = webRTCCallReceiver === 'user1' ? profileData.user1.name : profileData.user2.name;
            const callType = currentWebRTCCallType === 'voice' ? 'voice' : 'video';
            
            addMessage('messages1', 'received', `${caller} declined your ${callType} call`, receiver);
            addMessage('messages2', 'sent', `You declined ${caller}'s ${callType} call`, receiver);
            
            // Reset call state
            currentWebRTCCallType = null;
            webRTCCallInitiator = null;
            webRTCCallReceiver = null;
        }

        async function startWebRTCVoiceCall() {
            const modal = document.getElementById('webrtcVoiceCallModal');
            const avatar = document.getElementById('webrtcVoiceCallAvatar');
            const name = document.getElementById('webrtcVoiceCallName');
            const status = document.getElementById('webrtcVoiceCallStatus');
            const statusText = document.getElementById('webrtcVoiceCallStatusText');
            const statusDot = document.getElementById('webrtcVoiceCallStatus');
            
            // Set call info
            const receiverData = profileData[webRTCCallReceiver];
            const receiverNumber = webRTCCallReceiver.replace('user', '');
            
            if (receiverData.avatar) {
                avatar.innerHTML = `<img src="${receiverData.avatar}" alt="User ${receiverNumber}">`;
            } else {
                avatar.innerHTML = `<span id="webrtcVoiceCallAvatarText">U${receiverNumber}</span>`;
            }
            
            name.textContent = receiverData.name;
            status.textContent = 'Connected';
            statusText.textContent = 'Connected';
            statusDot.classList.remove('connecting', 'error');
            statusDot.classList.add('connected');
            
            // Show modal
            modal.classList.add('active');
            
            // Create peer connection
            await createPeerConnection();
            
            // Add local stream to peer connection
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });
            }
            
            // Start timer
            webRTCSeconds = 0;
            updateWebRTCTimer('voice');
            webRTCTimer = setInterval(() => updateWebRTCTimer('voice'), 1000);
            
            // Add call started message
            const caller = webRTCCallInitiator === 'user1' ? profileData.user1.name : profileData.user2.name;
            const receiver = webRTCCallReceiver === 'user1' ? profileData.user1.name : profileData.user2.name;
            
            addMessage('messages1', 'sent', `📞 You started a voice call with ${receiver}`, caller);
            addMessage('messages2', 'received', `📞 ${caller} started a voice call with you`, receiver);
            
            // Simulate remote stream (in real app, this would come from signaling)
            setTimeout(() => {
                simulateRemoteStream('voice');
            }, 1000);
        }

        async function startWebRTCVideoCall() {
            const modal = document.getElementById('webrtcVideoCallModal');
            const remoteAvatar = document.getElementById('remoteVideoAvatar');
            const remoteName = document.getElementById('remoteVideoName');
            const remoteStatus = document.getElementById('remoteVideoStatus');
            const statusText = document.getElementById('webrtcVideoCallStatusText');
            const statusDot = document.getElementById('webrtcVideoCallStatus');
            
            // Set call info
            const receiverData = profileData[webRTCCallReceiver];
            const receiverNumber = webRTCCallReceiver.replace('user', '');
            
            if (receiverData.avatar) {
                remoteAvatar.innerHTML = `<img src="${receiverData.avatar}" alt="User ${receiverNumber}">`;
            } else {
                remoteAvatar.innerHTML = `<span id="remoteVideoAvatarText">U${receiverNumber}</span>`;
            }
            
            remoteName.textContent = receiverData.name;
            statusText.textContent = 'Connected';
            statusDot.classList.remove('connecting', 'error');
            statusDot.classList.add('connected');
            
            // Show modal
            modal.classList.add('active');
            
            // Create peer connection
            await createPeerConnection();
            
            // Add local stream to peer connection and display local video
            if (localStream) {
                localStream.getTracks().forEach(track => {
                    peerConnection.addTrack(track, localStream);
                });
                
                const localVideo = document.getElementById('localVideo');
                localVideo.srcObject = localStream;
                document.getElementById('localVideoPlaceholder').style.display = 'none';
            }
            
            // Start timer
            webRTCSeconds = 0;
            updateWebRTCTimer('video');
            webRTCTimer = setInterval(() => updateWebRTCTimer('video'), 1000);
            
            // Add call started message
            const caller = webRTCCallInitiator === 'user1' ? profileData.user1.name : profileData.user2.name;
            const receiver = webRTCCallReceiver === 'user1' ? profileData.user1.name : profileData.user2.name;
            
            addMessage('messages1', 'sent', `📹 You started a video call with ${receiver}`, caller);
            addMessage('messages2', 'received', `📹 ${caller} started a video call with you`, receiver);
            
            // Simulate remote stream (in real app, this would come from signaling)
            setTimeout(() => {
                simulateRemoteStream('video');
            }, 1000);
        }

        async function createPeerConnection() {
            try {
                peerConnection = new RTCPeerConnection(configuration);
                
                // Handle remote stream
                peerConnection.ontrack = (event) => {
                    remoteStream = event.streams[0];
                    
                    if (currentWebRTCCallType === 'video') {
                        const remoteVideo = document.getElementById('remoteVideo');
                        remoteVideo.srcObject = remoteStream;
                        document.getElementById('remoteVideoPlaceholder').style.display = 'none';
                    }
                };
                
                // Handle ICE candidates
                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        // In a real app, send this to the other peer via signaling server
                        console.log('ICE candidate:', event.candidate);
                    }
                };
                
                // Create offer (for demonstration)
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);
                
                // In a real app, send offer to remote peer via signaling server
                console.log('Created offer:', offer);
                
            } catch (error) {
                console.error('Error creating peer connection:', error);
            }
        }

        function simulateRemoteStream(callType) {
            // This simulates receiving a remote stream
            // In a real app, this would come from the WebRTC signaling process
            
            if (callType === 'video') {
                // Hide placeholder and show connected status
                document.getElementById('remoteVideoPlaceholder').style.display = 'none';
                document.getElementById('remoteVideoStatus').textContent = 'Connected';
            }
        }

        function endWebRTCCall(callType) {
            // Clear timer
            clearInterval(webRTCTimer);
            
            // Stop local stream
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            
            // Stop remote stream
            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
                remoteStream = null;
            }
            
            // Close peer connection
            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }
            
            // Hide modal
            if (callType === 'voice') {
                document.getElementById('webrtcVoiceCallModal').classList.remove('active');
            } else if (callType === 'video') {
                document.getElementById('webrtcVideoCallModal').classList.remove('active');
                
                // Reset video elements
                document.getElementById('localVideo').srcObject = null;
                document.getElementById('remoteVideo').srcObject = null;
                document.getElementById('localVideoPlaceholder').style.display = 'flex';
                document.getElementById('remoteVideoPlaceholder').style.display = 'flex';
            }
            
            // Add call ended message
            const caller = webRTCCallInitiator === 'user1' ? profileData.user1.name : profileData.user2.name;
            const receiver = webRTCCallReceiver === 'user1' ? profileData.user1.name : profileData.user2.name;
            const duration = formatTime(webRTCSeconds);
            const callIcon = callType === 'voice' ? '📞' : '📹';
            
            addMessage('messages1', 'sent', `${callIcon} Call ended. Duration: ${duration}`, caller);
            addMessage('messages2', 'received', `${callIcon} Call ended. Duration: ${duration}`, receiver);
            
            // Reset call state
            currentWebRTCCallType = null;
            webRTCCallInitiator = null;
            webRTCCallReceiver = null;
            webRTCMicEnabled = true;
            webRTCVideoEnabled = true;
            webRTCScreenEnabled = false;
        }

        function updateWebRTCTimer(callType) {
            webRTCSeconds++;
            const timerId = callType === 'voice' ? 'webrtcVoiceCallTimer' : 'webrtcVideoCallTimer';
            document.getElementById(timerId).textContent = formatTime(webRTCSeconds);
        }

        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }

        function toggleWebRTCMic(callType) {
            webRTCMicEnabled = !webRTCMicEnabled;
            
            if (localStream) {
                const audioTrack = localStream.getAudioTracks()[0];
                if (audioTrack) {
                    audioTrack.enabled = webRTCMicEnabled;
                }
            }
            
            const btnId = callType === 'voice' ? 'webrtcVoiceMicBtn' : 'webrtcVideoMicBtn';
            const btn = document.getElementById(btnId);
            const icon = btn.querySelector('i');
            
            if (webRTCMicEnabled) {
                btn.classList.remove('muted');
                icon.className = 'fas fa-microphone';
            } else {
                btn.classList.add('muted');
                icon.className = 'fas fa-microphone-slash';
            }
        }

        function toggleWebRTCVideo() {
            webRTCVideoEnabled = !webRTCVideoEnabled;
            
            if (localStream) {
                const videoTrack = localStream.getVideoTracks()[0];
                if (videoTrack) {
                    videoTrack.enabled = webRTCVideoEnabled;
                }
            }
            
            const btn = document.getElementById('webrtcVideoBtn');
            const icon = btn.querySelector('i');
            const localVideo = document.getElementById('localVideo');
            
            if (webRTCVideoEnabled) {
                btn.classList.remove('disabled');
                icon.className = 'fas fa-video';
                localVideo.style.display = 'block';
            } else {
                btn.classList.add('disabled');
                icon.className = 'fas fa-video-slash';
                localVideo.style.display = 'none';
            }
        }

        async function toggleWebRTCScreen() {
            webRTCScreenEnabled = !webRTCScreenEnabled;
            
            const btn = document.getElementById('webrtcScreenBtn');
            const icon = btn.querySelector('i');
            
            if (webRTCScreenEnabled) {
                try {
                    const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
                    
                    // Replace video track with screen share
                    if (localStream && peerConnection) {
                        const videoTrack = localStream.getVideoTracks()[0];
                        const screenTrack = screenStream.getVideoTracks()[0];
                        
                        // Replace the track in the peer connection
                        const sender = peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
                        if (sender) {
                            await sender.replaceTrack(screenTrack);
                        }
                        
                        // Update local video
                        const localVideo = document.getElementById('localVideo');
                        localVideo.srcObject = screenStream;
                        
                        // Handle screen share end
                        screenTrack.onended = () => {
                            toggleWebRTCScreen();
                        };
                    }
                    
                    btn.classList.add('active');
                    icon.className = 'fas fa-stop';
                } catch (error) {
                    console.error('Error sharing screen:', error);
                    webRTCScreenEnabled = false;
                }
            } else {
                // Restore camera
                if (localStream) {
                    const localVideo = document.getElementById('localVideo');
                    localVideo.srcObject = localStream;
                }
                
                btn.classList.remove('active');
                icon.className = 'fas fa-desktop';
            }
        }
    </script>
</body>
</html>