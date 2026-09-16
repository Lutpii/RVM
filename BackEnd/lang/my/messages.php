<?php

return [
    'materials' => [
        'plastic'  => 'plastik',
        'aluminum' => 'aluminium',
        'glass'    => 'kaca',
        'paper'    => 'kertas',
        'unknown'  => 'tidak diketahui',
    ],

    'recycled_item' => 'Mengitar semula :weight g :material',
    'invalid_item'  => 'Item tidak sah: dipilih :selected, dikesan :detected',

    // AuthController
    'register_success'          => 'Pendaftaran berjaya.',
    'register_success_otp_sent' => 'Pendaftaran berjaya. OTP telah dihantar ke WhatsApp.',
    'too_many_login_attempts'   => 'Terlalu banyak percubaan log masuk. Cuba lagi dalam :seconds saat.',
    'invalid_credentials'       => 'Kelayakan tidak sah.',
    'login_success'             => 'Log masuk berjaya.',
    'too_many_otp_requests'     => 'Terlalu banyak permintaan OTP. Cuba lagi dalam :seconds saat.',
    'phone_not_found'           => 'Nombor telefon tidak dijumpai.',
    'otp_sent'                  => 'OTP telah dihantar ke WhatsApp anda.',
    'too_many_otp_attempts'     => 'Terlalu banyak percubaan. Cuba lagi dalam :seconds saat.',
    'invalid_otp'               => 'OTP tidak sah.',
    'otp_expired'                => 'OTP telah tamat tempoh.',
    'phone_verified'            => 'Nombor telefon berjaya disahkan.',
    'invalid_request'           => 'Permintaan tidak sah.',
    'oauth_code_expired'        => 'Pautan log masuk ini telah tamat tempoh atau sudah digunakan.',
    'logout_success'            => 'Log keluar berjaya.',
    'session_expired_idle'      => 'Sesi tamat tempoh kerana tidak aktif.',

    // UserController
    'current_password_incorrect' => 'Kata laluan semasa tidak sah.',
    'password_updated'           => 'Kata laluan berjaya dikemas kini.',

    // SessionController
    'machine_not_active'       => 'Mesin ini tidak aktif buat masa ini.',
    'active_session_exists'    => 'Anda sudah mempunyai sesi yang aktif.',
    'session_started'          => 'Sesi berjaya dimulakan.',
    'session_not_found'        => 'Sesi tidak dijumpai.',
    'active_session_not_found' => 'Sesi aktif tidak dijumpai.',
    'session_ended'            => 'Sesi berjaya ditamatkan.',

    // QrController
    'machine_not_found'     => 'Mesin tidak dijumpai.',
    'machine_inactive'      => 'Mesin tidak aktif.',
    'qr_token_not_found'    => 'Token QR tidak dijumpai.',
    'qr_invalid_or_expired' => 'Kod QR tidak sah atau telah tamat tempoh.',
    'qr_expired'            => 'Kod QR telah tamat tempoh. Sila imbas kod baharu.',
    'qr_scanned'            => 'Kod QR berjaya diimbas. Sesi sedia digunakan.',

    // TransactionController
    'bin_full'              => 'Tong :material sudah penuh. Sila pilih bahan lain.',
    'bin_has_space'         => 'Tong masih ada ruang. Meneruskan untuk membuka penutup.',
    'lid_opening'           => 'Penutup sedang dibuka. Sila tunggu...',
    'item_received'         => 'Item diterima. Memulakan penghantar...',
    'conveyor_running'      => 'Penghantar sedang berjalan. Menghantar item ke stesen pengimbasan...',
    'image_captured'        => 'Imej berjaya dirakam.',
    'item_classified'       => 'Item berjaya dikelaskan.',
    'points_earned_message' => 'Anda memperoleh :points mata!',
    'no_pending_item'       => 'Tiada item yang telah ditimbang untuk sesi ini. Sila timbang item dahulu.',
    'transaction_completed' => 'Transaksi berjaya diselesaikan!',
    'item_rejected'         => 'Item ditolak. Mata telah ditolak.',
];
