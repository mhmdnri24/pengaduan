import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter/services.dart';
import 'package:quickalert/quickalert.dart';

import '../config/api_config.dart';

class EditProfilePage extends StatefulWidget {
  const EditProfilePage({Key? key}) : super(key: key);

  @override
  State<EditProfilePage> createState() => _EditProfilePageState();
}

class _EditProfilePageState extends State<EditProfilePage> {
  String? selectedKecamatan;
  String? selectedKelurahan;
  String? selectedKecamatanId;
  String? selectedKelurahanId;
  final TextEditingController alamatController = TextEditingController();
  final TextEditingController namaController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController tempatController = TextEditingController();
  DateTime? selectedDate;
  String? userNik;
  final _secureStorage = const FlutterSecureStorage();
  bool isSaving = false;
  bool isLoadingProfile = false;

  // store kecamatan as list of maps with id + name to preserve API ids
  List<Map<String, String>> kecamatanList = [
    {'id': '16.73.01', 'name': 'Cimahi Selatan'},
    {'id': '16.73.02', 'name': 'Cimahi Tengah'},
    {'id': '16.73.03', 'name': 'Cimahi Utara'},
  ];
  final Map<String, List<String>> kelurahanList = {
    'Cimahi Selatan': ['Cibeber', 'Leuwigajah', 'Melong'],
    'Cimahi Tengah': ['Baros', 'Padasuka', 'Cigugur Tengah'],
    'Cimahi Utara': ['Cipageran', 'Cibabat'],
  };
  // dynamic kelurahan map keyed by kecamatan id -> list of {id,name}
  final Map<String, List<Map<String, String>>> kelurahanMap = {};
  bool isLoadingKelurahan = false;
  bool isLoadingKecamatan = false;

  Future<void> _loadUserData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();

    setState(() {
      userNik = prefs.getString('user_nik');
    });
  }

  @override
  void dispose() {
    alamatController.dispose();
    namaController.dispose();
    phoneController.dispose();
    tempatController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _fetchKecamatan();
    _fetchProfile();
  }

  Future<void> _fetchKelurahan(String kecamatanId) async {
    if (kecamatanId.isEmpty) return;
    setState(() => isLoadingKelurahan = true);
    try {
      final uri =
          Uri.parse('${ApiConfig.baseUrl}/masyarakat/kelurahan/$kecamatanId');
      final resp = await http.get(uri, headers: {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': 'https://dashboard.nusakoding.com',
      });

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        final Map<String, dynamic> jsonResp = jsonDecode(resp.body);
        if (jsonResp['status'] == 'success') {
          final kelArr = (jsonResp['data'] is Map)
              ? (jsonResp['data']['kelurahan'] as List<dynamic>?)
              : null;
          if (kelArr != null) {
            final List<Map<String, String>> items = [];
            for (final item in kelArr) {
              if (item is Map<String, dynamic>) {
                final id = item['id_kelurahan']?.toString() ?? '';
                final name = item['nama_kelurahan']?.toString() ?? '';
                if (name.isNotEmpty) items.add({'id': id, 'name': name});
              }
            }
            if (items.isNotEmpty) {
              kelurahanMap[kecamatanId] = items;
              // if profile had a selected kelurahan id, ensure it's selected
              if (selectedKelurahanId != null) {
                final found = items.firstWhere(
                    (e) => e['id'] == selectedKelurahanId,
                    orElse: () => <String, String>{});
                if (found.isNotEmpty) selectedKelurahan = found['name'];
              }
            }
          }
        }
      }
    } catch (e) {
      // ignore
    } finally {
      if (mounted) setState(() => isLoadingKelurahan = false);
    }
  }

  Future<void> _fetchKecamatan() async {
    setState(() => isLoadingKecamatan = true);
    try {
      final uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/kecamatan');
      final resp = await http.get(uri, headers: {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': 'https://dashboard.nusakoding.com',
      });

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        final Map<String, dynamic> jsonResp = jsonDecode(resp.body);
        if (jsonResp['status'] == 'success') {
          // response shape: { data: { kecamatan: [ ... ] } }
          final kecArr = (jsonResp['data'] is Map)
              ? (jsonResp['data']['kecamatan'] as List<dynamic>?)
              : null;
          if (kecArr != null) {
            final List<Map<String, String>> items = [];
            for (final item in kecArr) {
              if (item is Map<String, dynamic>) {
                final id = item['id_kecamatan']?.toString() ?? '';
                final name = item['nama_kecamatan']?.toString() ?? '';
                if (name.isNotEmpty) items.add({'id': id, 'name': name});
              }
            }
            if (items.isNotEmpty) {
              // replace kecamatanList but keep any selected value at top
              final prevSelectedId = selectedKecamatanId;
              kecamatanList = items;
              if (prevSelectedId != null) {
                Map<String, String>? found = kecamatanList.firstWhere(
                    (e) => e['id'] == prevSelectedId,
                    orElse: () => <String, String>{});
                if (found.isNotEmpty) {
                  selectedKecamatan = found['name'];
                  selectedKecamatanId = found['id'];
                }
              }
            }
          }
        }
      }
    } catch (e) {
      // ignore fetch errors; fall back to defaults
    } finally {
      if (mounted) setState(() => isLoadingKecamatan = false);
    }
  }

  Future<void> _fetchProfile() async {
    setState(() => isLoadingProfile = true);
    final token = await _getAuthToken();
    if (token == null || token.isEmpty) {
      setState(() => isLoadingProfile = false);
      return;
    }

    try {
      final uri = Uri.parse('${ApiConfig.baseUrl}/masyarakat/$userNik');
      final resp = await http.get(uri, headers: {
        'Authorization': token,
        'X-API-Key': ApiConfig.apiKey,
        'Origin': 'https://dashboard.nusakoding.com',
      });

      if (resp.statusCode == 200 || resp.statusCode == 201) {
        final Map<String, dynamic> jsonResp = jsonDecode(resp.body);
        if (jsonResp['status'] == 'success') {
          final data = jsonResp['data'] as Map<String, dynamic>?;
          if (data != null) {
            // populate fields
            namaController.text = data['nama_lengkap']?.toString() ?? '';
            phoneController.text = data['no_telpon']?.toString() ?? '';
            tempatController.text = data['tempat_lahir']?.toString() ?? '';
            selectedDate =
                DateTime.tryParse(data['tanggal_lahir']?.toString() ?? '');
            alamatController.text = data['alamat']?.toString() ?? '';

            // kecamatan/kelurahan
            final kec = data['kecamatan'] as Map<String, dynamic>?;
            final kel = data['kelurahan'] as Map<String, dynamic>?;
            if (kec != null) {
              selectedKecamatanId = kec['id_kecamatan']?.toString();
              selectedKecamatan = kec['nama_kecamatan']?.toString();
              // add to local list if missing
              if (selectedKecamatan != null) {
                final exists =
                    kecamatanList.any((e) => e['name'] == selectedKecamatan);
                if (!exists) {
                  kecamatanList.insert(0, {
                    'id': selectedKecamatanId ?? '',
                    'name': selectedKecamatan!
                  });
                }
              }
            }
            if (kel != null) {
              selectedKelurahanId = kel['id_kelurahan']?.toString();
              selectedKelurahan = kel['nama_kelurahan']?.toString();
              if (selectedKecamatan != null) {
                final key = selectedKecamatan!;
                final list = kelurahanList[key] ?? <String>[];
                if (selectedKelurahan != null &&
                    !list.contains(selectedKelurahan)) {
                  kelurahanList[key] = [...list, selectedKelurahan!];
                }
              }
            }
          }
        }
      }
    } catch (e) {
      // ignore errors here, user can still edit manually
    } finally {
      if (mounted) setState(() => isLoadingProfile = false);
    }
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

  Future<void> _saveProfile(BuildContext context) async {
    if (!mounted) return;

    final nama = namaController.text.trim();
    final noTelp = phoneController.text.trim();
    final tempat = tempatController.text.trim();
    final tanggal = selectedDate != null
        ? '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}'
        : '';
    final alamat = alamatController.text.trim();
    // prefer API ids when available, fallback to previously-selected names or a sensible default
    final idKec = selectedKecamatanId ?? selectedKecamatan ?? '16.73.01';
    final idKel = selectedKelurahanId ?? selectedKelurahan ?? '16.73.01.1001';

    if (nama.isEmpty || noTelp.isEmpty) {
      if (!mounted) return;
      await QuickAlert.show(
        context: context,
        type: QuickAlertType.warning,
        title: "Peringatan",
        text: 'Nama dan no. telpon wajib diisi',
      );
      return;
    }

    final token = await _getAuthToken();
    if (token == null || token.isEmpty) {
      if (!mounted) return;
      await QuickAlert.show(
        context: context,
        type: QuickAlertType.error,
        title: "Error",
        text: 'Token tidak ditemukan. Silakan login ulang',
      );
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
        print(resp.statusCode);
        // if (!mounted) return;
        print(1);
        // ScaffoldMessenger.of(context).showSnackBar(
        //     const SnackBar(content: Text('Profil berhasil diperbarui')));
        if (!mounted) return;
        await QuickAlert.show(
          context: context,
          type: QuickAlertType.success,
          title: "Sukses",
          text: 'Profil berhasil diperbarui',
        );
        if (!mounted) return;
        Navigator.pop(context);
        return;
      }

      if (!mounted) return;
      // ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      //     content: Text('Gagal memperbarui profil: ${resp.statusCode}')));

      QuickAlert.show(
        context: context,
        type: QuickAlertType.error,
        title: "Gagal",
        text: 'Gagal memperbarui profil: ${resp.statusCode}',
      );
    } catch (e) {
      if (!mounted) return;
      // ScaffoldMessenger.of(context)
      //     .showSnackBar(SnackBar(content: Text('Terjadi kesalahan: $e')));
      QuickAlert.show(
        context: context,
        type: QuickAlertType.error,
        title: "Error",
        text: 'Terjadi kesalahan: $e',
      );
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
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
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
        padding: const EdgeInsets.all(14),
        children: [
          // === Progress Header ===
          // Row(
          //   mainAxisAlignment: MainAxisAlignment.spaceBetween,
          //   children: const [
          //     Text('Langkah 2 dari 2', style: TextStyle(color: Colors.grey)),
          //     Text('Hampir selesai!', style: TextStyle(color: Colors.blue)),
          //   ],
          // ),
          const SizedBox(height: 8),
          // LinearProgressIndicator(
          //   value: 1,
          //   color: Colors.blue.shade700,
          //   backgroundColor: Colors.blue.shade100,
          // ),
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
            color: Colors.white,
            elevation: 0.3,
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
                        borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
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
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
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
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
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
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text('Tanggal Lahir',
                      style: TextStyle(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 6),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: selectedDate ?? DateTime.now(),
                        firstDate: DateTime(1900),
                        lastDate: DateTime.now(),
                      );
                      if (picked != null) {
                        setState(() => selectedDate = picked);
                      }
                    },
                    child: InputDecorator(
                      decoration: InputDecoration(
                        hintText: 'Pilih tanggal lahir',
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide:
                                const BorderSide(color: Color(0xFFD0D0D0))),
                        enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide:
                                const BorderSide(color: Color(0xFFD0D0D0))),
                        contentPadding: const EdgeInsets.all(12),
                      ),
                      child: Text(selectedDate != null
                          ? '${selectedDate!.year}-${selectedDate!.month.toString().padLeft(2, '0')}-${selectedDate!.day.toString().padLeft(2, '0')}'
                          : 'Pilih tanggal'),
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
                        .map((k) => DropdownMenuItem<String>(
                            value: k['name'], child: Text(k['name'] ?? '')))
                        .toList(),
                    onChanged: (val) => setState(() {
                      selectedKecamatan = val;
                      selectedKelurahan = null;
                      // find the id for the selected name
                      final found = kecamatanList.firstWhere(
                          (e) => e['name'] == val,
                          orElse: () => <String, String>{});
                      selectedKecamatanId =
                          (found.isNotEmpty) ? found['id'] : null;
                      // fetch kelurahan for selected kecamatan id
                      if (selectedKecamatanId != null &&
                          selectedKecamatanId!.isNotEmpty) {
                        _fetchKelurahan(selectedKecamatanId!);
                      }
                    }),
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
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
                    items: (selectedKecamatanId != null &&
                            kelurahanMap.containsKey(selectedKecamatanId))
                        ? kelurahanMap[selectedKecamatanId]!
                            .map((k) => DropdownMenuItem<String>(
                                value: k['name'], child: Text(k['name'] ?? '')))
                            .toList()
                        : (selectedKecamatan != null
                            ? (kelurahanList[selectedKecamatan] ?? [])
                                .map((kel) => DropdownMenuItem<String>(
                                    value: kel, child: Text(kel)))
                                .toList()
                            : []),
                    onChanged: (selectedKecamatan == null)
                        ? null
                        : (val) => setState(() {
                              selectedKelurahan = val;
                              // also try to resolve selectedKelurahanId if possible
                              if (selectedKecamatanId != null &&
                                  kelurahanMap
                                      .containsKey(selectedKecamatanId)) {
                                final found = kelurahanMap[selectedKecamatanId]!
                                    .firstWhere((e) => e['name'] == val,
                                        orElse: () => <String, String>{});
                                selectedKelurahanId =
                                    (found.isNotEmpty) ? found['id'] : null;
                              }
                            }),
                    decoration: InputDecoration(
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
                      enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(color: Color(0xFFD0D0D0))),
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
            color: Colors.white,
            elevation: 0.3,
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

          // === Buttons - Horizontal Layout ===
          Row(
            children: [
              // Simpan & Lanjutkan Button
              Expanded(
                child: ElevatedButton.icon(
                  icon: isSaving
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(
                              strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.save_alt, color: Colors.white),
                  label: const Text(
                    'Simpan',
                    style: TextStyle(color: Colors.white),
                  ),
                  onPressed: isSaving ? null : () => _saveProfile(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),

              const SizedBox(width: 12),

              // Lewati untuk Sekarang Button
              Expanded(
                child: OutlinedButton.icon(
                  icon: const Icon(Icons.arrow_forward),
                  label: const Text(
                    'Lewati',
                    style: TextStyle(color: Colors.black),
                  ),
                  onPressed: () {
                    Navigator.pop(context);
                  },
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }
}
