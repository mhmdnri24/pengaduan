import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/emergency_service.dart';
import 'package:quickalert/quickalert.dart';
import 'package:pengaduan/services/api_service.dart';
import '../components/emergency_card.dart';

class EmergencyPage extends StatefulWidget {
  const EmergencyPage({Key? key}) : super(key: key);

  @override
  State<EmergencyPage> createState() => _EmergencyPageState();
}

class _EmergencyPageState extends State<EmergencyPage> {
  Position? _currentPosition;
  bool _isLoadingLocation = false;
  String _detectedLocation = '';
  String _address = '';
  String _alamat = '';

  // User data from session
  String? userName;
  String? userNik;
  String? masId;
  String? userPhone;
  String? userPhotoUrl;
  String? userAddress; // Added to store user's address from profile
  String? lat;
  String? lng;

  // Loading and error states
  bool isLoading = false;
  bool isLoadingUserData = true;
  String? errorMessage;

  // Emergency category selection
  String? selectedEmergencyCategory;
  // Categories loaded from API
  List<Map<String, dynamic>> _categories = [];
  bool _isLoadingCategories = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _getCurrentLocation();
    _fetchCategories();
  }

  Future<void> _fetchCategories() async {
    setState(() => _isLoadingCategories = true);
    try {
      final res = await ApiService.instance.getCategories();
      if (res.success && res.data != null) {
        final list = res.data!;
        if (mounted) {
          setState(() {
            _categories = list;
            _isLoadingCategories = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoadingCategories = false);
      }
    } catch (e) {
      if (mounted) setState(() => _isLoadingCategories = false);
    }
  }

  Future<void> _loadUserData() async {
    try {
      // First load from cache for immediate display
      SharedPreferences prefs = await SharedPreferences.getInstance();

      if (mounted) {
        setState(() {
          userName = prefs.getString('user_name');
          userNik = prefs.getString('user_nik');
          masId = prefs.getString('user_id');
          userPhone = prefs.getString('user_phone');
          userPhotoUrl = prefs.getString('user_photo_url');
          userAddress = prefs.getString('user_alamat');
          _alamat = prefs.getString('user_alamat') ?? '';
        });

        // _showError(_alamat ?? 'Alamat tidak tersedia');
      }

      // Then fetch fresh data from API to verify completeness
      await _fetchUserDataFromAPI();
    } catch (e) {
      if (mounted) {
        setState(() {
          errorMessage = 'Gagal memuat data pengguna';
          isLoadingUserData = false;
        });
      }
    }
  }

  Future<void> _fetchUserDataFromAPI() async {
    try {
      final response = await ApiService.instance.getUserProfile();

      if (response.success && response.data != null) {
        final userData = response.data!;

        // Update cache with fresh data
        SharedPreferences prefs = await SharedPreferences.getInstance();
        await prefs.setString(
            'user_name', userData['nama_lengkap'] ?? userName);
        await prefs.setString('user_nik', userData['nik'] ?? userNik);
        await prefs.setString('user_id', userData['id'] ?? masId);
        await prefs.setString('user_phone', userData['no_telpon'] ?? userPhone);
        await prefs.setString(
            'user_photo_url', userData['foto_profil_url'] ?? userPhotoUrl);
        await prefs.setString('user_alamat', userData['alamat'] ?? userAddress);

        if (mounted) {
          setState(() {
            userName = userData['nama_lengkap'] ?? userName;
            userNik = userData['nik'] ?? userNik;
            masId = userData['id'] ?? masId;
            userPhone = userData['no_telpon'] ?? userPhone;
            userPhotoUrl = userData['foto_profil_url'] ?? userPhotoUrl;
            userAddress = userData['alamat'] ?? userAddress;

            // Set _address to use user's address instead of geocoded address
            if (userAddress != null && userAddress!.isNotEmpty) {
              _alamat = userAddress!;
            }
            isLoadingUserData = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            isLoadingUserData = false;
          });
        }
      }
    } catch (e) {
      debugPrint('Error fetching user data from API: $e');
      if (mounted) {
        setState(() {
          isLoadingUserData = false;
        });
      }
    }
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      _isLoadingLocation = true;
    });

    try {
      // Check permission
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.deniedForever) {
        // Permissions are denied forever
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Izin Ditolak",
          text: 'Izin lokasi ditolak permanen. Silakan aktifkan di pengaturan.',
        );
        return;
      }

      if (permission == LocationPermission.denied) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Izin Ditolak",
          text: 'Izin lokasi ditolak.',
        );
        return;
      }

      // Get current position
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      setState(() {
        _currentPosition = position;
        lat = position.latitude.toString();
        lng = position.longitude.toString();
        _detectedLocation = '${position.latitude}, ${position.longitude}';
      });

      // Only get address from coordinates if user address is not available
      if (userAddress == null || userAddress!.isEmpty) {
        await _getAddressFromCoordinates(position.latitude, position.longitude);
      }
    } catch (e) {
      print('Error getting location: $e');
      QuickAlert.show(
        context: context,
        type: QuickAlertType.error,
        title: "Error",
        text: 'Gagal mendapatkan lokasi: $e',
      );
    } finally {
      setState(() {
        _isLoadingLocation = false;
      });
    }
  }

  Future<void> _getAddressFromCoordinates(
      double latitude, double longitude) async {
    try {
      List<Placemark> placemarks =
          await placemarkFromCoordinates(latitude, longitude);
      if (placemarks.isNotEmpty) {
        Placemark place = placemarks[0];
        String address = '';

        // Build address from placemark
        if (place.street != null && place.street!.isNotEmpty) {
          address += place.street!;
        }
        if (place.subLocality != null && place.subLocality!.isNotEmpty) {
          if (address.isNotEmpty) address += ', ';
          address += place.subLocality!;
        }
        if (place.locality != null && place.locality!.isNotEmpty) {
          if (address.isNotEmpty) address += ', ';
          address += place.locality!;
        }
        if (place.administrativeArea != null &&
            place.administrativeArea!.isNotEmpty) {
          if (address.isNotEmpty) address += ', ';
          address += place.administrativeArea!;
        }
        if (place.country != null && place.country!.isNotEmpty) {
          if (address.isNotEmpty) address += ', ';
          address += place.country!;
        }

        setState(() {
          _address =
              address.isNotEmpty ? address : 'Alamat tidak dapat dideteksi';
        });
      } else {
        setState(() {
          _address = 'Alamat tidak dapat dideteksi';
        });
      }
    } catch (e) {
      print('Error getting address: $e');
      setState(() {
        _address = 'Gagal mendapatkan alamat';
      });
    }
  }

  Future<void> _sendEmergencyReport() async {
    if (_currentPosition == null) {
      QuickAlert.show(
        context: context,
        type: QuickAlertType.warning,
        title: "Lokasi belum siap",
        text: 'Lokasi belum terdeteksi. Mohon tunggu...',
      );
      return;
    }

    // Validate user data
    if (userName == null || userName!.isEmpty) {
      _showError('Data pengguna tidak tersedia. Silakan login kembali.');
      return;
    }
    if (userPhone == null || userPhone!.isEmpty) {
      _showError('Nomor telepon pengguna tidak tersedia');
      return;
    }
    if (userNik == null || userNik!.isEmpty) {
      _showError('NIK pengguna tidak tersedia');
      return;
    }

    // Check if user profile is complete
    if (masId == null || masId!.isEmpty) {
      _showError('Lengkapi terlebih dahulu identitas anda');
      return;
    }

    // _showError(_alamat);

    // Validate address completeness
    if (_alamat.isEmpty ||
        _alamat == 'Alamat tidak dapat dideteksi' ||
        _alamat == 'Gagal mendapatkan alamat') {
      _showError(
          'Alamat lengkap harus diisi untuk laporan darurat. Silakan lengkapi profil Anda terlebih dahulu.');
      return;
    }

    if (selectedEmergencyCategory == null) {
      _showError('Pilih jenis keadaan darurat terlebih dahulu');
      return;
    }

    // Show confirmation dialog
    bool? confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Konfirmasi Darurat'),
        content: const Text(
          'Anda akan mengirim sinyal darurat. Tim akan segera diberitahu. Lanjutkan?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text(
              'Kirim Darurat',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    // Send emergency report
    await _submitEmergencyReport();
  }

  Future<void> _submitEmergencyReport() async {
    if (!mounted) return;

    setState(() {
      isLoading = true;
      errorMessage = null;
    });
    print("Kat: ${selectedEmergencyCategory ?? 'null'}");
    try {
      // Submit emergency report
      var response = await EmergencyService.instance.submitEmergencyReport(
        kategori: selectedEmergencyCategory!,
        alamat: _address.isNotEmpty ? _address : 'Lokasi tidak dapat dideteksi',
        pelaporNama: userName!,
        pelaporTelepon: userPhone!,
        masId: masId!,
        pelaporNik: userNik!,
        pelaporAlamat:
            _address.isNotEmpty ? _address : 'Lokasi tidak dapat dideteksi',
        lat: lat ?? '',
        lng: lng ?? '',
      );

      if (!mounted) return;

      print(response);

      if (response.success && response.data != null) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.success,
          title: "Sukses",
          text: 'Sinyal darurat telah dikirim! Tim akan segera diberitahu.',
        );
      } else {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Error",
          text: response.error,
        );
        // _showError(response.error ?? 'Gagal mengirim sinyal darurat');
      }
    } catch (e) {
      if (mounted) {
        _showError('Terjadi kesalahan: $e');
      }
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  void _showError(String message) {
    setState(() {
      errorMessage = message;
    });
    QuickAlert.show(
      context: context,
      type: QuickAlertType.error,
      title: "Error",
      text: message,
    );
  }

  void _showSuccess(String message) {
    QuickAlert.show(
      context: context,
      type: QuickAlertType.success,
      title: "Berhasil",
      text: message,
      autoCloseDuration: const Duration(seconds: 2),
      showConfirmBtn: false,
    );
  }

  String _getCategoryDisplayName(String categoryKey) {
    switch (categoryKey) {
      case 'medis':
        return 'Medis';
      case 'kebakaran':
        return 'Kebakaran';
      case 'keamanan':
        return 'Keamanan';
      case 'bencana':
        return 'Bencana Alam';
      default:
        return 'Tidak Diketahui';
    }
  }

  String _getSelectedCategoryName(String id) {
    try {
      final found = _categories.firstWhere(
          (c) => c['pelaporan_id']?.toString() == id,
          orElse: () => {});
      if (found.isNotEmpty) return found['pelaporan_nama']?.toString() ?? id;
    } catch (_) {}
    // fallback to legacy mapping
    return _getCategoryDisplayName(id);
  }

  // Return a style (color/icon) for known categories, otherwise pick from palette by index
  Map<String, dynamic> _getCategoryStyle(String name, int index) {
    final lower = name.toLowerCase();
    if (lower.contains('infrastruktur')) {
      return {'color': Colors.green.shade700, 'icon': Icons.account_balance};
    }
    if (lower.contains('lingkungan')) {
      return {'color': Colors.green, 'icon': Icons.eco};
    }
    if (lower.contains('keamanan')) {
      return {'color': Colors.orange, 'icon': Icons.security};
    }
    if (lower.contains('layanan')) {
      return {'color': Colors.blue, 'icon': Icons.group};
    }

    // palette fallback
    final palette = [
      Colors.indigo,
      Colors.teal,
      Colors.purple,
      Colors.cyan,
      Colors.amber,
      Colors.brown,
      Colors.pink,
    ];
    final color = palette[index % palette.length];
    final icons = [
      Icons.report_problem,
      Icons.home_repair_service,
      Icons.water,
      Icons.local_fire_department,
      Icons.health_and_safety,
      Icons.build,
      Icons.support_agent,
    ];
    final icon = icons[index % icons.length];
    return {'color': color, 'icon': icon};
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7FA),
      appBar: AppBar(
        backgroundColor: const Color(0xFF2E5C9A),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Layanan Darurat',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        actions: [
          Container(
            margin: const EdgeInsets.only(right: 16),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              children: [
                Icon(Icons.access_time, size: 16, color: Colors.white),
                SizedBox(width: 4),
                Text(
                  'SIAGA',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Error message display
            if (errorMessage != null)
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.error_outline, color: Colors.red),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        errorMessage!,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                    IconButton(
                      onPressed: () => setState(() => errorMessage = null),
                      icon:
                          const Icon(Icons.close, color: Colors.red, size: 20),
                    ),
                  ],
                ),
              ),
            // Status Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF2E5C9A).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.shield_outlined,
                      color: Color(0xFF2E5C9A),
                      size: 32,
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Status Keamanan',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Sistem darurat aktif dan siap melayani',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.green,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'NORMAL',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Jenis Keadaan Darurat
            Row(
              children: [
                const Text(
                  'Jenis Keadaan Darurat',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: const Text(
                    'WAJIB',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: Colors.red,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Grid Categories (loaded from API)
            GridView.count(
              crossAxisCount: 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.4,
              children: _isLoadingCategories
                  ? [
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 20),
                        alignment: Alignment.center,
                        child: const SizedBox(
                            height: 24,
                            width: 24,
                            child: CircularProgressIndicator()),
                      )
                    ]
                  : (_categories.isNotEmpty
                      ? _categories.asMap().entries.map((entry) {
                          final idx = entry.key;
                          final item = entry.value;
                          final id = (item['pelaporan_id'] ?? item['id'] ?? '')
                              .toString();
                          final title =
                              (item['pelaporan_nama'] ?? item['nama'] ?? '')
                                  .toString();
                          final subtitle =
                              (item['created_at_formatted'] ?? '').toString();
                          final style = _getCategoryStyle(title, idx);
                          final Color color = style['color'] as Color;
                          final IconData iconData = style['icon'] as IconData;
                          return EmergencyCard(
                            id: id,
                            title: title,
                            subtitle: subtitle,
                            icon: iconData,
                            color: color,
                            borderColor: color.withOpacity(0.35),
                            backgroundColor: color.withOpacity(0.06),
                            selected: selectedEmergencyCategory == title,
                            onTap: () => setState(
                                () => selectedEmergencyCategory = title),
                          );
                        }).toList()
                      : [
                          EmergencyCard(
                            id: 'medis',
                            title: 'Medis',
                            subtitle: 'Kecelakaan, serangan jantung, stroke',
                            icon: Icons.medical_services,
                            color: Colors.red,
                            borderColor: Colors.red.withOpacity(0.35),
                            backgroundColor: Colors.red.withOpacity(0.06),
                            selected: selectedEmergencyCategory == 'medis',
                            onTap: () => setState(
                                () => selectedEmergencyCategory = 'medis'),
                          ),
                          EmergencyCard(
                            id: 'kebakaran',
                            title: 'Kebakaran',
                            subtitle: 'Kebakaran rumah, gedung, hutan',
                            icon: Icons.local_fire_department,
                            color: Colors.orange,
                            borderColor: Colors.orange.withOpacity(0.35),
                            backgroundColor: Colors.orange.withOpacity(0.06),
                            selected: selectedEmergencyCategory == 'kebakaran',
                            onTap: () => setState(
                                () => selectedEmergencyCategory = 'kebakaran'),
                          ),
                          EmergencyCard(
                            id: 'keamanan',
                            title: 'Keamanan',
                            subtitle: 'Pencurian, perampokan, kekerasan',
                            icon: Icons.security,
                            color: Colors.blue,
                            borderColor: Colors.blue.withOpacity(0.35),
                            backgroundColor: Colors.blue.withOpacity(0.06),
                            selected: selectedEmergencyCategory == 'keamanan',
                            onTap: () => setState(
                                () => selectedEmergencyCategory = 'keamanan'),
                          ),
                          EmergencyCard(
                            id: 'bencana',
                            title: 'Bencana Alam',
                            subtitle: 'Banjir, gempa, tanah longsor',
                            icon: Icons.warning,
                            color: Colors.green,
                            borderColor: Colors.green.withOpacity(0.35),
                            backgroundColor: Colors.green.withOpacity(0.06),
                            selected: selectedEmergencyCategory == 'bencana',
                            onTap: () => setState(
                                () => selectedEmergencyCategory = 'bencana'),
                          ),
                        ]),
            ),

            const SizedBox(height: 12),

            // Helper text
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.blue.shade100),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline,
                      size: 16, color: Colors.blue.shade700),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      selectedEmergencyCategory != null
                          ? 'Kategori terpilih: ${_getSelectedCategoryName(selectedEmergencyCategory!)}'
                          : 'Pilih salah satu jenis keadaan darurat di atas',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.blue.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Lokasi Darurat
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on,
                        color: Colors.red,
                        size: 24,
                      ),
                      const SizedBox(width: 8),
                      const Text(
                        'Lokasi Darurat',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Colors.black87,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'Alamat/Lokasi',
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.gps_fixed,
                            size: 16, color: Colors.grey),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _address.isNotEmpty
                                    ? _address
                                    : _currentPosition != null
                                        ? 'GPS: ${_currentPosition!.latitude}, ${_currentPosition!.longitude}'
                                        : 'Mendeteksi lokasi...',
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: Colors.black87,
                                ),
                              ),
                              if (userAddress != null &&
                                  userAddress!.isNotEmpty)
                                Container(
                                  margin: const EdgeInsets.only(top: 4),
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 6, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: Colors.green.shade100,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(
                                    'Dari Profil',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.green.shade700,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed:
                          _isLoadingLocation ? null : _getCurrentLocation,
                      icon: _isLoadingLocation
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.my_location),
                      label: Text(
                          _isLoadingLocation
                              ? 'Memuat...'
                              : 'Gunakan Lokasi Saat Ini',
                          style: TextStyle(
                            // Ubah warna teks menjadi putih sesuai permintaan
                            color: Colors.white,
                          )),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF2E5C9A),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),
                  ),
                  if (_detectedLocation.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.blue.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.blue.shade100),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.location_on,
                              size: 16, color: Colors.blue),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Lokasi Terdeteksi:',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                    color: Colors.blue,
                                  ),
                                ),
                                Text(
                                  _detectedLocation,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: Colors.blue,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),

            const SizedBox(height: 24),
            // Tombol Darurat
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 10,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                children: [
                  const Text(
                    'Tombol Darurat',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Tekan tombol ini dalam keadaan darurat. Lokasi Anda akan terdeteksi otomatis dan sinyal darurat akan dikirim ke petugas terdekat.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey[600],
                      height: 1.5,
                    ),
                  ),
                  const SizedBox(height: 24),
                  GestureDetector(
                    onTap: isLoading ? null : _sendEmergencyReport,
                    child: Container(
                      width: 150,
                      height: 150,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: isLoading ? Colors.grey : Colors.red,
                        boxShadow: [
                          BoxShadow(
                            color: (isLoading ? Colors.grey : Colors.red)
                                .withOpacity(0.4),
                            blurRadius: 20,
                            spreadRadius: 5,
                          ),
                        ],
                      ),
                      child: Center(
                        child: isLoading
                            ? const SizedBox(
                                width: 40,
                                height: 40,
                                child: CircularProgressIndicator(
                                  strokeWidth: 3,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(
                                Icons.warning,
                                size: 80,
                                color: Colors.white,
                              ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'TEKAN UNTUK DARURAT',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.red,
                      letterSpacing: 1,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}
