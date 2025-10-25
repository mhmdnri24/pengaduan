<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('kirim_pesan')) {
    /**
     * Fungsi untuk mengirim pesan WhatsApp
     *
     * @param string $nomor_hp Nomor HP tujuan (format: 08xxxxxxxxxx)
     * @param string $pesan Isi pesan yang akan dikirim
     * @param string $file_path Path file attachment (opsional)
     * @param array $options Opsi tambahan (template_id, nama_tujuan, created_by)
     * @return array Response dari API
     */
    function kirim_pesan($nomor_hp, $pesan, $file_path = null, $options = [])
    {
        $ci = &get_instance();

        // Get sender ID from options
        $sender = ce_opsi('waapi_sender');
        
        // Format nomor HP
        if (substr($nomor_hp, 0, 1) == '0') {
            $nomor_hp = '62' . substr($nomor_hp, 1);
        } elseif (substr($nomor_hp, 0, 2) == '62') {
            $nomor_hp = $nomor_hp;
        } elseif (substr($nomor_hp, 0, 1) == '+') {
            $nomor_hp = substr($nomor_hp, 1);
        }

        // Check if sending file or text message
        if ($file_path && file_exists($file_path)) {
            // Send media message
            $url = ce_opsi('waapi_url_media');
            $filename = basename($file_path);
            $file_url = base_url('uploads/temp/' . $filename);

            $postData = [
                'api_key' => ce_opsi('waapi_key'),
                'sender' => $sender,
                'number' => $nomor_hp,
                'filename' => $filename,
                'url' => $file_url,
                'caption' => $pesan,
                'option' => 'document'
            ];
        } else {
            // Send text message
            $url = ce_opsi('waapi_url');
            $postData = [
                'api_key' => ce_opsi('waapi_key'),
                'sender' => $sender,
                'number' => $nomor_hp,
                'message' => $pesan
            ];
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        // Decode response
        $result = json_decode($response, true);
        
        // Catat ke histori jika options['no_log'] tidak disetel
        if (!isset($options['no_log']) || !$options['no_log']) {
            // Siapkan data histori
            $histori_data = [
                'nomor_tujuan' => $nomor_hp,
                'pesan' => $pesan,
                'response_data' => $response,
                'waktu_kirim' => date('Y-m-d H:i:s'),
                'status' => isset($result['status']) && $result['status'] ? 1 : 0
            ];
            
            // Tambahkan nama_tujuan jika ada
            if (isset($options['nama_tujuan'])) {
                $histori_data['nama_tujuan'] = $options['nama_tujuan'];
            }
            
            // Tambahkan template_id jika ada
            if (isset($options['template_id'])) {
                $histori_data['template_id'] = $options['template_id'];
            }
            
            // Tambahkan created_by jika ada
            if (isset($options['created_by'])) {
                $histori_data['created_by'] = $options['created_by'];
            } else {
                $histori_data['created_by'] = $ci->session->userdata('id_user');
            }
            
            // Simpan ke database
            $ci->db->insert('wa_histori', $histori_data);
        }
        
        return $result;
    }
}

if (!function_exists('kirim_template')) {

    function kirim_template($nomor_hp, $template_kode, $params = [], $file_path = null, $options = [])
    {
        $ci = &get_instance();
        
        // Load model template
        $ci->load->model('whatsapp/wa_template_m');
        
        // Get template
        $template = $ci->wa_template_m->get_by_kode($template_kode);
        
        if (!$template) {
            return [
                'status' => false,
                'message' => 'Template tidak ditemukan',
            ];
        }
        
        // Replace placeholders in template
        $pesan = $template->isi_template;
        foreach ($params as $key => $value) {
            $pesan = str_replace('{'.$key.'}', $value, $pesan);
        }
        
        // Set template_id in options
        $options['template_id'] = $template->id;
        
        // Send message
        $result = kirim_pesan($nomor_hp, $pesan, $file_path, $options);
        
        return $result;
    }
}

if (!function_exists('generate_wa_invoice_message')) {

    function generate_wa_invoice_message($booking, $payment_summary) {
        // Get company info
        $company_name = ce_opsi('nama_situs') ?: 'Booking Gedung System';
        $company_phone = ce_opsi('nomor_kontak') ?: '';

        // Template pesan WhatsApp untuk invoice
        $message = "*🏢 INVOICE BOOKING GEDUNG*\n\n";
        $message .= "Halo " . ($booking->booking_pic_nama ?: $booking->booking_penyelenggara ?: 'Customer') . ",\n\n";
        $message .= "Berikut adalah invoice untuk booking gedung Anda:\n\n";

        $message .= "📋 *Detail Booking:*\n";
        $message .= "• 🏢 Gedung: " . ($booking->gedung_nama ?: '-') . "\n";
        $message .= "• 🎯 Acara: " . ($booking->booking_nama_acara ?: '-') . "\n";
        $message .= "• 📅 Tanggal: " . date('d/m/Y', strtotime($booking->booking_tanggal_mulai));
        if ($booking->booking_tanggal_mulai !== $booking->booking_tanggal_selesai) {
            $message .= " - " . date('d/m/Y', strtotime($booking->booking_tanggal_selesai));
        }
        $message .= "\n";
        $message .= "• ⏰ Jam: " . ($booking->booking_jam_mulai ?: '-') . " - " . ($booking->booking_jam_selesai ?: '-') . "\n";
        $message .= "• 👤 PIC: " . ($booking->booking_pic_nama ?: $booking->booking_penyelenggara ?: '-') . "\n\n";

        $message .= "💰 *Rincian Pembayaran:*\n";
        $message .= "• 💵 Total Tagihan: Rp " . number_format($payment_summary->booking_total_biaya, 0, ',', '.') . "\n";
        $message .= "• ✅ Sudah Dibayar: Rp " . number_format($payment_summary->total_dibayar, 0, ',', '.') . "\n";
        $message .= "• ⚠️ Sisa Tagihan: Rp " . number_format($payment_summary->sisa_tagihan, 0, ',', '.') . "\n";
        $message .= "• 📊 Status: " . ucfirst(str_replace('_', ' ', $payment_summary->booking_status_pembayaran)) . "\n\n";

        $message .= "📄 *Lihat Invoice Lengkap:*\n";
        $message .= base_url('pembayaran_gedung/print_invoice/' . $booking->booking_id) . "\n\n";

        $message .= "📞 *Butuh Bantuan?*\n";
        $message .= "Hubungi kami di: " . $company_phone . "\n\n";

        $message .= "Terima kasih telah menggunakan layanan " . $company_name . "! 🙏\n\n";
        $message .= "_Invoice dibuat pada " . date('d/m/Y H:i') . "_";

        return $message;
    }
}
