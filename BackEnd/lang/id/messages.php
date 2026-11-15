<?php

return [
    'materials' => [
        'plastic'  => 'plastik',
        'aluminum' => 'aluminium',
        'glass'    => 'kaca',
        'paper'    => 'kertas',
        'unknown'  => 'tidak dikenal',
    ],

    'recycled_item' => 'Mendaur ulang :weight g :material',
    'invalid_item'  => 'Barang tidak valid: dipilih :selected, terdeteksi :detected',

    // AuthController
    'register_success'          => 'Pendaftaran berhasil.',
    'register_success_otp_sent' => 'Pendaftaran berhasil. Kode OTP telah dikirim untuk verifikasi.',
    'too_many_login_attempts'   => 'Terlalu banyak percobaan masuk. Coba lagi dalam :seconds detik.',
    'invalid_credentials'       => 'Email atau kata sandi salah.',
    'account_suspended'         => 'Akun Anda dinonaktifkan sementara. Silakan hubungi administrator.',
    'login_success'             => 'Berhasil masuk.',
    'too_many_otp_requests'     => 'Terlalu banyak permintaan OTP. Coba lagi dalam :seconds detik.',
    'account_not_found'         => 'Akun tidak ditemukan.',
    'otp_sent'                  => 'Kode OTP telah dikirim untuk verifikasi.',
    'too_many_otp_attempts'     => 'Terlalu banyak percobaan. Coba lagi dalam :seconds detik.',
    'invalid_otp'               => 'Kode OTP salah.',
    'otp_expired'               => 'Kode OTP sudah kedaluwarsa.',
    'account_verified'          => 'Akun berhasil diverifikasi.',
    'account_not_verified'      => 'Silakan verifikasi akun Anda terlebih dahulu. Kami telah mengirim kode OTP baru.',
    'invalid_request'           => 'Permintaan tidak valid.',
    'oauth_code_expired'        => 'Tautan masuk ini sudah kedaluwarsa atau sudah pernah dipakai.',
    'logout_success'            => 'Berhasil keluar.',
    'session_expired_idle'      => 'Sesi berakhir karena tidak ada aktivitas.',

    // UserController
    'current_password_incorrect' => 'Kata sandi saat ini salah.',
    'password_updated'           => 'Kata sandi berhasil diperbarui.',
    'account_deleted'            => 'Akun berhasil dihapus.',

    // SessionController
    'machine_not_active'       => 'Mesin ini sedang tidak aktif.',
    'active_session_exists'    => 'Anda sudah memiliki sesi yang aktif.',
    'active_session_elsewhere' => 'Anda masih memiliki sesi aktif di :machine. Akhiri sesi itu terlebih dahulu.',
    'session_started'          => 'Sesi berhasil dimulai.',
    'session_not_found'        => 'Sesi tidak ditemukan.',
    'active_session_not_found' => 'Sesi aktif tidak ditemukan.',
    'session_ended'            => 'Sesi berhasil diakhiri.',

    // QrController
    'machine_not_found'     => 'Mesin tidak ditemukan.',
    'machine_inactive'      => 'Mesin tidak aktif.',
    'qr_token_not_found'    => 'Kode koneksi tidak ditemukan.',
    'qr_invalid_or_expired' => 'Kode QR tidak valid atau sudah kedaluwarsa.',
    'qr_expired'            => 'Kode QR sudah kedaluwarsa. Silakan pindai kode yang baru.',
    'qr_scanned'            => 'Kode QR berhasil dipindai. Sesi siap.',

    // TransactionController
    'bin_full'              => 'Tempat :material sudah penuh. Silakan pilih material lain.',
    'bin_has_space'         => 'Tempat masih cukup. Membuka penutup.',
    'lid_opening'           => 'Penutup sedang dibuka. Mohon tunggu...',
    'item_received'         => 'Barang diterima. Menjalankan konveyor...',
    'conveyor_running'      => 'Konveyor berjalan. Memindahkan barang ke area pemindaian...',
    'image_captured'        => 'Gambar berhasil diambil.',
    'item_classified'       => 'Barang berhasil dikenali.',
    'points_earned_message' => 'Anda mendapatkan :points poin!',
    'no_pending_item'       => 'Belum ada barang yang ditimbang untuk sesi ini. Silakan timbang barang terlebih dahulu.',
    'transaction_completed' => 'Transaksi berhasil diselesaikan!',
    'item_rejected'         => 'Barang ditolak. Poin dikurangi.',
];
