import 'package:flutter/material.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  final _addressController = TextEditingController();
  String? _selectedKecamatan;
  String? _selectedKelurahan;

  // Example options — replace with real API-driven lists later
  final Map<String, List<String>> _kelurahanByKecamatan = {
    'Kec A': ['Kel A1', 'Kel A2', 'Kel A3'],
    'Kec B': ['Kel B1', 'Kel B2'],
    'Kec C': ['Kel C1'],
  };

  @override
  void dispose() {
    _addressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FB),
      // appBar: AppBar(
      //   elevation: 0,
      //   backgroundColor: const Color(0xFF2D62F2),
      //   title: const Text('Lengkapi Profil'),
      //   centerTitle: true,
      // ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Progress header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Langkah 2 dari 2',
                      style: TextStyle(fontWeight: FontWeight.w600)),
                  Text('Hampir selesai!',
                      style: TextStyle(
                          color: theme.primaryColor,
                          fontWeight: FontWeight.w600)),
                ],
              ),
              const SizedBox(height: 8),
              ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: LinearProgressIndicator(
                  minHeight: 8,
                  value: 0.85,
                  valueColor: AlwaysStoppedAnimation(theme.primaryColor),
                  backgroundColor: Colors.grey.shade200,
                ),
              ),

              const SizedBox(height: 22),

              // Welcome card
              Container(
                padding: const EdgeInsets.symmetric(vertical: 26),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                        color: Colors.black.withOpacity(0.04), blurRadius: 8),
                  ],
                ),
                child: Column(
                  children: [
                    Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        color: Colors.green.shade600,
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                              color: Colors.green.shade600.withOpacity(0.3),
                              blurRadius: 6,
                              offset: const Offset(0, 4)),
                        ],
                      ),
                      child: const Icon(Icons.check,
                          color: Colors.white, size: 36),
                    ),
                    const SizedBox(height: 14),
                    const Text('Selamat Datang!',
                        style: TextStyle(
                            fontSize: 20, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    Text('Lengkapi profil Anda untuk menggunakan semua fitur',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey.shade600)),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Informasi Alamat card
              Card(
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0,
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.location_on, color: theme.primaryColor),
                          const SizedBox(width: 8),
                          const Text('Informasi Alamat',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w600)),
                        ],
                      ),
                      const SizedBox(height: 12),
                      const Text('Alamat Lengkap *',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _addressController,
                        maxLines: 4,
                        decoration: InputDecoration(
                          hintText:
                              'Masukkan alamat lengkap (jalan, RT/RW, dll)',
                          filled: true,
                          fillColor: Colors.white,
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
                      ),
                      const SizedBox(height: 12),
                      const Text('Kecamatan *',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        value: _selectedKecamatan,
                        items: _kelurahanByKecamatan.keys
                            .map((k) => DropdownMenuItem(
                                  value: k,
                                  child: Text(k),
                                ))
                            .toList(),
                        onChanged: (v) {
                          setState(() {
                            _selectedKecamatan = v;
                            _selectedKelurahan = null;
                          });
                        },
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
                        hint: const Text('Pilih Kecamatan'),
                      ),
                      const SizedBox(height: 12),
                      const Text('Kelurahan *',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        value: _selectedKelurahan,
                        items: (_selectedKecamatan == null)
                            ? []
                            : _kelurahanByKecamatan[_selectedKecamatan]!
                                .map((k) => DropdownMenuItem(
                                      value: k,
                                      child: Text(k),
                                    ))
                                .toList(),
                        onChanged: (v) =>
                            setState(() => _selectedKelurahan = v),
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10)),
                        ),
                        hint: const Text('Pilih Kelurahan'),
                      ),
                      if (_selectedKecamatan == null)
                        Padding(
                          padding: const EdgeInsets.only(top: 8.0),
                          child: Text('Pilih kecamatan terlebih dahulu',
                              style: TextStyle(color: Colors.grey.shade600)),
                        ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // Foto Profil card (updated layout)
              Card(
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0,
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Foto Profil',
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 12),

                      // dashed circle placeholder
                      Center(
                        child: Column(
                          children: [
                            Container(
                              width: 130,
                              height: 130,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: Colors.grey.shade300,
                                  width: 3,
                                  style: BorderStyle.solid,
                                ),
                              ),
                              child: Center(
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: const [
                                    Icon(Icons.photo_camera_outlined,
                                        size: 36, color: Colors.grey),
                                    SizedBox(height: 8),
                                    Text('Tambah Foto',
                                        style: TextStyle(color: Colors.grey)),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 14),
                            ElevatedButton.icon(
                              onPressed: () {
                                // TODO: open image picker and upload
                              },
                              icon: const Icon(Icons.upload_file),
                              label: const Padding(
                                padding: EdgeInsets.symmetric(vertical: 12.0),
                                child: Text('Upload Foto',
                                    style: TextStyle(fontSize: 16)),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: theme.primaryColor,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 18, vertical: 6),
                              ),
                            ),
                            const SizedBox(height: 10),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.info_outline,
                                    size: 16, color: Colors.grey.shade600),
                                const SizedBox(width: 8),
                                Flexible(
                                  child: Text(
                                    'Foto akan digunakan untuk profil akun Anda\nFormat: JPG, PNG | Maksimal: 2MB',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                        color: Colors.grey.shade600,
                                        fontSize: 13),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 18),

              // Primary action - green full width
              ElevatedButton.icon(
                onPressed: () {
                  // TODO: validate & save profile
                },
                icon: const Icon(Icons.save_alt, color: Colors.white),
                label: const Padding(
                  padding: EdgeInsets.symmetric(vertical: 14.0),
                  child: Text('Simpan & Lanjutkan',
                      style: TextStyle(fontSize: 16, color: Colors.white)),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green.shade600,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  minimumSize: const Size.fromHeight(52),
                ),
              ),

              const SizedBox(height: 12),

              // Secondary action - grey
              OutlinedButton.icon(
                onPressed: () {
                  // Skip for now
                  Navigator.of(context).pop();
                },
                icon: const Icon(Icons.arrow_forward, color: Colors.black87),
                label: const Padding(
                  padding: EdgeInsets.symmetric(vertical: 14.0),
                  child: Text('Lewati untuk Sekarang',
                      style: TextStyle(fontSize: 16, color: Colors.black87)),
                ),
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.grey.shade200,
                  side: BorderSide.none,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  minimumSize: const Size.fromHeight(52),
                ),
              ),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }
}
