import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:pengaduan/config/api_config.dart';

class DetailPengaduanPage extends StatefulWidget {
  final String complaintId;
  const DetailPengaduanPage({super.key, required this.complaintId});

  @override
  State<DetailPengaduanPage> createState() => _DetailPengaduanPageState();
}

class _DetailPengaduanPageState extends State<DetailPengaduanPage> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    final url =
        Uri.parse('${ApiConfig.baseUrl}/pelaporan/${widget.complaintId}');
    try {
      final resp = await http.get(url, headers: {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': ApiConfig.origin,
      });

      if (resp.statusCode == 200) {
        final body = json.decode(resp.body) as Map<String, dynamic>;
        if (body['status'] == 'success' && body['data'] != null) {
          setState(() {
            _data = body['data'] as Map<String, dynamic>;
            _loading = false;
          });
          return;
        } else {
          setState(() {
            _error = body['message']?.toString() ?? 'Unknown error';
            _loading = false;
          });
          return;
        }
      } else {
        setState(() {
          _error = 'Network error: ${resp.statusCode}';
          _loading = false;
        });
        return;
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

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
          'Detail Pengaduan ',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(10),
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return SizedBox(
        height: 300,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: const [CircularProgressIndicator(), SizedBox(height: 8)],
          ),
        ),
      );
    }

    if (_error != null) {
      return SizedBox(
        height: 200,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Error: $_error'),
              const SizedBox(height: 8),
              ElevatedButton(
                  onPressed: _fetchDetail, child: const Text('Retry')),
            ],
          ),
        ),
      );
    }

    final data = _data ?? {};

    // Use provided fields with fallbacks
    final title = data['judul']?.toString() ?? 'Tidak ada judul';
    final description = data['deskripsi']?.toString() ?? '-';
    final tanggal = data['created_at_formatted']?.toString() ?? '-';
    final lokasi = data['alamat']?.toString() ?? '-';
    final kode = data['kode_laporan']?.toString() ?? widget.complaintId;
    final kategori = data['nama_kategori']?.toString() ??
        (data['kategori']?.toString() ?? '-');
    final fotoUrl = data['foto_url']?.toString();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Status Chip
        Align(
          alignment: Alignment.centerLeft,
          child: Chip(
            backgroundColor: const Color(0xFFFFE0B2),
            label: Text(
              data['status']?.toString() ?? 'DALAM PROSES',
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
                if (fotoUrl != null) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      fotoUrl,
                      height: 160,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (c, e, s) => Container(
                        height: 160,
                        color: Colors.grey[200],
                        child: const Center(child: Icon(Icons.broken_image)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                ] else if ((data['files'] as List?)?.isNotEmpty ?? false) ...[
                  SizedBox(
                    height: 100,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: (data['files'] as List).map<Widget>((f) {
                        final fileUrl = f['file_url']?.toString();
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: fileUrl != null
                                ? Image.network(fileUrl,
                                    width: 140, height: 100, fit: BoxFit.cover)
                                : Container(
                                    width: 140,
                                    height: 100,
                                    color: Colors.grey[200]),
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
                Text(
                  title,
                  style: GoogleFonts.poppins(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  description,
                  style: GoogleFonts.poppins(
                    color: Colors.black87,
                    fontSize: 12.5,
                  ),
                ),
                const Divider(height: 24),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(child: _infoText('Tanggal Laporan', tanggal)),
                        const SizedBox(width: 12),
                        Expanded(child: _infoText('Lokasi', lokasi)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                            child: _infoText('ID Pengaduan', '#$kode',
                                link: true)),
                        const SizedBox(width: 12),
                        Expanded(child: _infoText('Kategori', kategori)),
                      ],
                    ),
                  ],
                )
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Progress section (kept simple)
        Text(
          'Progress Penanganan',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        const SizedBox(height: 10),
        // show files if any

        const SizedBox(height: 20),

        // Tanggapan Petugas (left as static placeholders)
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
                      borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
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
