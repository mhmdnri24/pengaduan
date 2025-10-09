import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter/services.dart';

import '../config/api_config.dart';

class EditProfilePage extends StatefulWidget {
  const EditProfilePage({Key? key}) : super(key: key);

  @override
  State<EditProfilePage> createState() => _EditProfilePageState();
}

class _EditProfilePageState extends State<EditProfilePage> {
  String? selectedKecamatan;
  String? selectedKelurahan;
  final TextEditingController alamatController = TextEditingController();
  final TextEditingController namaController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController tempatController = TextEditingController();
  final TextEditingController tanggalController = TextEditingController();

  final _secureStorage = const FlutterSecureStorage();
  bool isSaving = false;

  final List<String> kecamatanList = [
    'Cimahi Selatan',
    'Cimahi Tengah',
    'Cimahi Utara'
  ];
  final Map<String, List<String>> kelurahanList = {
    'Cimahi Selatan': ['Cibeber', 'Leuwigajah', 'Melong'],
    'Cimahi Tengah': ['Baros', 'Padasuka', 'Cigugur Tengah'],
    'Cimahi Utara': ['Cipageran', 'Cibabat'],
  };

  @override
  void dispose() {
    alamatController.dispose();
    namaController.dispose();
    phoneController.dispose();
    tempatController.dispose();
    tanggalController.dispose();
    super.dispose();
  }

  Future<String?> _getAuthToken() async {
    try {
      final token = await _secureStorage.read(key: 'session_token');
      if (token != null && token.isNotEmpty) return token;
    } on MissingPluginException catch (_) {
      // fallthrough to prefs
    } catch (_) {}

    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('session_token') ?? prefs.getString('token');
  }

  Future<void> _saveProfile() async {
    final nama = namaController.text.trim();
    final noTelp = phoneController.text.trim();
    final tempat = tempatController.text.trim();
    final tanggal = tanggalController.text.trim();
    final alamat = alamatController.text.trim();
    final idKec = selectedKecamatan ?? '';
    final idKel = selectedKelurahan ?? '';

    if (nama.isEmpty || noTelp.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Nama dan no. telpon wajib diisi')));
      return;
    }

    final token = await _getAuthToken();
    if (token == null || token.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Token tidak ditemukan. Silakan login ulang')));
      return;
    }

    setState(() => isSaving = true);
    try {
      final uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/profile');
      final body = {
        'nama_lengkap': nama,
        'no_telpon': noTelp,
        'tempat_lahir': tempat,
        'tanggal_lahir': tanggal,
        'id_kecamatan': idKec,
        'id_kelurahan': idKel,
        'alamat': alamat,
      };

      final resp = await http.put(
        uri,
        headers: {
          'Authorization': token,
          'X-API-Key': ApiConfig.apiKey,
          'Origin': 'https://dashboard.nusakoding.com',
          'Content-Type': 'application/json',
        },
        body: jsonEncode(body),
      );

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        // try parse response and update prefs
        try {
          final Map<String, dynamic> jsonResp = jsonDecode(resp.body);
          final data = jsonResp['data'] as Map<String, dynamic>? ?? {};
          final prefs = await SharedPreferences.getInstance();
          if (data.containsKey('nama_lengkap'))
            await prefs.setString(
                'user_name', data['nama_lengkap']?.toString() ?? nama);
          if (data.containsKey('no_telpon'))
            await prefs.setString(
                'user_phone', data['no_telpon']?.toString() ?? noTelp);
          if (data.containsKey('foto_profil_url'))
            await prefs.setString(
                'user_photo_url', data['foto_profil_url']?.toString() ?? '');
        } catch (_) {
          // ignore parse errors
        }

        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Profil berhasil diperbarui')));
        Navigator.pop(context);
        return;
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Gagal memperbarui profil: ${resp.statusCode}')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text('Terjadi kesalahan: $e')));
    } finally {
      if (mounted) setState(() => isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xfff9fafb),

      // === Custom AppBar ===
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(60),
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF2255EE), Color(0xFF4285F4)],
              begin: Alignment.centerLeft,
              end: Alignment.centerRight,
            ),
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: const [
                  CircleAvatar(
                    backgroundColor: Colors.white24,
                    child: Icon(Icons.person_outline, color: Colors.white),
                  ),
                  Text(
                    'Lengkapi Profil',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                    ),
                  ),
                  CircleAvatar(
                    backgroundColor: Colors.white24,
                    child: Icon(Icons.help_outline, color: Colors.white),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),

      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // === Progress Header ===
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              Text('Langkah 2 dari 2', style: TextStyle(color: Colors.grey)),
              Text('Hampir selesai!', style: TextStyle(color: Colors.blue)),
            ],
          ),
          const SizedBox(height: 8),
          LinearProgressIndicator(
            value: 1,
            color: Colors.blue.shade700,
            backgroundColor: Colors.blue.shade100,
          ),
          const SizedBox(height: 25),

          // === Welcome Section ===
          Center(
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.green.shade100,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.check, color: Colors.green, size: 40),
            ),
          ),
          const SizedBox(height: 16),
          const Center(
            child: Text(
              'Selamat Datang!',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),
          const Center(
            child: Text(
              'Lengkapi profil Anda untuk menggunakan semua fitur',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
          ),
          const SizedBox(height: 25),

          // === Card Informasi Alamat ===
          Card(
            elevation: 2,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.location_on, color: Colors.blue),
                      SizedBox(width: 6),
                      Text(
                        'Informasi Alamat',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text('Alamat Lengkap *',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: alamatController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Masukkan alamat lengkap (jalan, RT/RW, dll)',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Nama Lengkap *',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: namaController,
                    decoration: InputDecoration(
                      hintText: 'Nama lengkap',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('No. Telpon *',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      hintText: '08xxxxxxxx',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('Tempat Lahir',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: tempatController,
                    decoration: InputDecoration(
                      hintText: 'Tempat lahir',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('Tanggal Lahir (YYYY-MM-DD)',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: tanggalController,
                    decoration: InputDecoration(
                      hintText: '1990-05-15',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('Kecamatan *',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: selectedKecamatan,
                    hint: const Text('Pilih Kecamatan'),
                    items: kecamatanList
                        .map((k) =>
                            DropdownMenuItem<String>(value: k, child: Text(k)))
                        .toList(),
                    onChanged: (val) => setState(() {
                      selectedKecamatan = val;
                      selectedKelurahan = null;
                    }),
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Kelurahan *',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: selectedKelurahan,
                    hint: const Text('Pilih Kelurahan'),
                    items: (selectedKecamatan != null
                            ? kelurahanList[selectedKecamatan] ?? []
                            : [])
                        .map((kel) => DropdownMenuItem<String>(
                            value: kel, child: Text(kel)))
                        .toList(),
                    onChanged: selectedKecamatan == null
                        ? null
                        : (val) => setState(() => selectedKelurahan = val),
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  if (selectedKecamatan == null)
                    const Padding(
                      padding: EdgeInsets.only(top: 6),
                      child: Text(
                        'Pilih kecamatan terlebih dahulu',
                        style: TextStyle(color: Colors.grey, fontSize: 12),
                      ),
                    ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 25),

          // === Card Foto Profil ===
          Card(
            elevation: 2,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.photo_camera, color: Colors.blue),
                      SizedBox(width: 6),
                      Text(
                        'Foto Profil',
                        style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Center(
                    child: Column(
                      children: [
                        Container(
                          height: 120,
                          width: 120,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                                color: Colors.grey.shade300,
                                width: 2,
                                style: BorderStyle.solid),
                          ),
                          child: const Center(
                            child: Icon(Icons.camera_alt_outlined,
                                color: Colors.grey, size: 40),
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text('Tambah Foto',
                            style: TextStyle(color: Colors.grey)),
                        const SizedBox(height: 12),
                        ElevatedButton.icon(
                          icon: const Icon(
                            Icons.upload,
                            size: 18,
                            color: Colors.white,
                          ),
                          label: const Text(
                            'Upload Foto',
                            style: TextStyle(color: Colors.white),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.blue,
                            padding: const EdgeInsets.symmetric(
                                horizontal: 20, vertical: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                          onPressed: () {},
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Foto akan digunakan untuk profil akun Anda\nFormat: JPG, PNG | Maksimal: 2MB',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 30),

          // === Buttons ===
          ElevatedButton.icon(
            icon: isSaving
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white))
                : const Icon(Icons.save_alt, color: Colors.white),
            label: const Text(
              'Simpan & Lanjutkan',
              style: TextStyle(color: Colors.white),
            ),
            onPressed: isSaving ? null : _saveProfile,
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            icon: const Icon(Icons.arrow_forward),
            label: const Text(
              'Lewati untuk Sekarang',
              style: TextStyle(color: Colors.black),
            ),
            onPressed: () {},
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }
}
