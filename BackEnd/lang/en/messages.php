<?php

return [
    'materials' => [
        'plastic'  => 'plastic',
        'aluminum' => 'aluminum',
        'glass'    => 'glass',
        'paper'    => 'paper',
        'unknown'  => 'unknown',
    ],

    'recycled_item' => 'Recycled :weight g of :material',
    'invalid_item'  => 'Invalid item: selected :selected, detected :detected',

    // AuthController
    'register_success'          => 'Registration successful.',
    'register_success_otp_sent' => 'Registration successful. OTP sent for verification.',
    'too_many_login_attempts'   => 'Too many login attempts. Try again in :seconds seconds.',
    'invalid_credentials'       => 'Invalid credentials.',
    'login_success'             => 'Login successful.',
    'too_many_otp_requests'     => 'Too many OTP requests. Try again in :seconds seconds.',
    'account_not_found'         => 'Account not found.',
    'otp_sent'                  => 'OTP sent for verification.',
    'too_many_otp_attempts'     => 'Too many attempts. Try again in :seconds seconds.',
    'invalid_otp'               => 'Invalid OTP.',
    'otp_expired'                => 'OTP has expired.',
    'account_verified'          => 'Account verified successfully.',
    'account_not_verified'      => 'Please verify your account first. We\'ve sent you a new OTP.',
    'invalid_request'           => 'Invalid request.',
    'oauth_code_expired'        => 'This sign-in link has expired or was already used.',
    'logout_success'            => 'Logged out successfully.',
    'session_expired_idle'      => 'Session expired due to inactivity.',

    // UserController
    'current_password_incorrect' => 'Current password is incorrect.',
    'password_updated'           => 'Password updated successfully.',
    'account_deleted'            => 'Account deleted successfully.',

    // SessionController
    'machine_not_active'       => 'This machine is not currently active.',
    'active_session_exists'    => 'You already have an active session.',
    'session_started'          => 'Session started successfully.',
    'session_not_found'        => 'Session not found.',
    'active_session_not_found' => 'Active session not found.',
    'session_ended'            => 'Session ended successfully.',

    // QrController
    'machine_not_found'     => 'Machine not found.',
    'machine_inactive'      => 'Machine is not active.',
    'qr_token_not_found'    => 'QR token not found.',
    'qr_invalid_or_expired' => 'Invalid or expired QR code.',
    'qr_expired'            => 'QR code has expired. Please scan a new one.',
    'qr_scanned'            => 'QR scanned successfully. Session ready.',

    // TransactionController
    'bin_full'              => 'The :material bin is full. Please choose a different material.',
    'bin_has_space'         => 'Bin has space. Proceeding to open lid.',
    'lid_opening'           => 'Lid is opening. Please wait...',
    'item_received'         => 'Item received. Starting conveyor...',
    'conveyor_running'      => 'Conveyor running. Moving item to scanning station...',
    'image_captured'        => 'Image captured successfully.',
    'item_classified'       => 'Item classified successfully.',
    'points_earned_message' => 'You earned :points points!',
    'no_pending_item'       => 'No weighed item is pending for this session. Please weigh an item first.',
    'transaction_completed' => 'Transaction completed successfully!',
    'item_rejected'         => 'Item rejected. Points deducted.',
];
