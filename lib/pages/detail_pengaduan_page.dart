import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class DetailPengaduanPage extends StatelessWidget {
  const DetailPengaduanPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F9),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0D47A1),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Detail Pengaduan',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status Chip
            Align(
              alignment: Alignment.centerLeft,
              child: Chip(
                backgroundColor: const Color(0xFFFFE0B2),
                label: Text(
                  'DALAM PROSES',
                  style: GoogleFonts.poppins(
                    color: Colors.orange[800],
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Card laporan utama
            Card(
              color: Colors.white,
              elevation: 1,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.network(
                        'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a',
                        height: 160,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Lampu Jalan Mati',
                      style: GoogleFonts.poppins(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Lampu penerangan jalan di depan Jalan Sudirman No. 45 sudah mati selama 3 hari. Kondisi ini membahayakan pengendara terutama pada malam hari.',
                      style: GoogleFonts.poppins(
                        color: Colors.black87,
                        fontSize: 12.5,
                      ),
                    ),
                    const Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _infoText('Tanggal Laporan', '22 Agustus 2024'),
                        _infoText('Lokasi', 'Jl. Sudirman No. 45'),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        _infoText('ID Pengaduan', '#LPW-2024-001', link: true),
                        _infoText('Kategori', 'Infrastruktur'),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),

            // Progress section
            Text(
              'Progress Penanganan',
              style: GoogleFonts.poppins(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 10),
            _progressItem(
              icon: Icons.check_circle,
              title: 'Laporan Diterima',
              desc: 'Pengaduan Anda telah diterima dan dicatat dalam sistem',
              date: '22 Agustus 2024, 14:30',
              active: true,
            ),
            _progressItem(
              icon: Icons.verified,
              title: 'Verifikasi Laporan',
              desc:
                  'Tim verifikasi telah mengonfirmasi kebenaran laporan Anda.',
              date: '22 Agustus 2024, 16:45',
              active: true,
            ),
            _progressItem(
              icon: Icons.build_circle,
              title: 'Dalam Perbaikan',
              desc: 'Tim teknis sedang melakukan perbaikan lampu jalan.',
              date: '23 Agustus 2024, 09:00',
              active: true,
              color: Colors.blue,
            ),
            _progressItem(
              icon: Icons.flag_circle,
              title: 'Selesai',
              desc: 'Pengaduan akan ditandai selesai setelah perbaikan.',
              date: 'Menunggu',
              active: false,
            ),
            const SizedBox(height: 20),

            // Tanggapan Petugas
            Text(
              'Tanggapan Petugas',
              style: GoogleFonts.poppins(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 10),
            _tanggapanCard(
              role: 'Petugas Teknis - Ahmad Wijaya',
              date: '23 Agt 2024, 10:30',
              message:
                  'Tim teknis telah tiba di lokasi dan sedang melakukan penggantian lampu yang rusak. Estimasi selesai hari ini sebelum maghrib.',
              color: const Color(0xFFE3F2FD),
            ),
            _tanggapanCard(
              role: 'Koordinator Lapangan - Budi Santoso',
              date: '22 Agt 2024, 17:00',
              message:
                  'Laporan telah diverifikasi. Tim teknis akan ditugaskan untuk perbaikan besok pagi.',
              color: const Color(0xFFE8F5E9),
            ),
            const SizedBox(height: 20),

            // Berikan Rating
            Text(
              'Berikan Rating',
              style: GoogleFonts.poppins(
                fontWeight: FontWeight.w600,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 10),
            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bagaimana penilaian Anda terhadap penanganan pengaduan ini?',
                      style: GoogleFonts.poppins(fontSize: 12.5),
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: List.generate(
                        5,
                        (index) => const Icon(
                          Icons.star_border_rounded,
                          color: Colors.amber,
                          size: 28,
                        ),
                      ),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Komentar (Opsional)',
                        hintStyle: GoogleFonts.poppins(fontSize: 12.5),
                        filled: true,
                        fillColor: Colors.grey.shade100,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0)),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0)),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0D47A1),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                        onPressed: () {},
                        child: Text(
                          'Kirim Rating',
                          style: GoogleFonts.poppins(
                            color: Colors.white,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoText(String label, String value, {bool link = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.poppins(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: GoogleFonts.poppins(
            fontSize: 13,
            color: link ? Colors.blue : Colors.black,
            fontWeight: link ? FontWeight.w500 : FontWeight.w400,
          ),
        ),
      ],
    );
  }

  Widget _progressItem({
    required IconData icon,
    required String title,
    required String desc,
    required String date,
    bool active = false,
    Color color = Colors.green,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Icon(icon, color: active ? color : Colors.grey.shade400, size: 26),
            Container(
              width: 2,
              height: 50,
              color: Colors.grey.shade300,
            ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(top: 2),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.poppins(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: active ? Colors.black : Colors.grey.shade600,
                  ),
                ),
                Text(
                  desc,
                  style: GoogleFonts.poppins(
                    fontSize: 13,
                    color: Colors.grey.shade700,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  date,
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: Colors.grey.shade500,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _tanggapanCard({
    required String role,
    required String date,
    required String message,
    required Color color,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: color,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    role,
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      fontSize: 12.5,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  date,
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              message,
              style: GoogleFonts.poppins(fontSize: 12.5),
            ),
          ],
        ),
      ),
    );
  }
}
