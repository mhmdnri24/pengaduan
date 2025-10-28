import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/emergency_service.dart';
import 'package:quickalert/quickalert.dart';

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

  // User data from session
  String? userName;
  String? userNik;
  String? masId;
  String? userPhone;
  String? userPhotoUrl;
  String? lat;
  String? lng;

  // Loading and error states
  bool isLoading = false;
  bool isLoadingUserData = true;
  String? errorMessage;

  // Emergency category selection
  String? selectedEmergencyCategory;

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _getCurrentLocation();
  }

  Future<void> _loadUserData() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();

      if (mounted) {
        setState(() {
          userName = prefs.getString('user_name');
          userNik = prefs.getString('user_nik');
          masId = prefs.getString('user_id');
          userPhone = prefs.getString('user_phone');
          userPhotoUrl = prefs.getString('user_photo_url');
          isLoadingUserData = false;

          print(masId);
          print(userPhone);
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          errorMessage = 'Gagal memuat data pengguna';
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
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
                'Izin lokasi ditolak permanen. Silakan aktifkan di pengaturan.'),
            backgroundColor: Colors.red,
          ),
        );
        return;
      }

      if (permission == LocationPermission.denied) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Izin lokasi ditolak.'),
            backgroundColor: Colors.red,
          ),
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

      // Get address from coordinates
      await _getAddressFromCoordinates(position.latitude, position.longitude);
    } catch (e) {
      print('Error getting location: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal mendapatkan lokasi: $e'),
          backgroundColor: Colors.red,
        ),
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
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Lokasi belum terdeteksi. Mohon tunggu...'),
          backgroundColor: Colors.orange,
        ),
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
            child: const Text('Kirim Darurat'),
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
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
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

            // Grid Categories
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              mainAxisSpacing: 16,
              crossAxisSpacing: 16,
              childAspectRatio: 1.3,
              children: [
                _buildEmergencyCard(
                  key: 'medis',
                  icon: Icons.medical_services,
                  title: 'Medis',
                  subtitle: 'Kecelakaan, serangan jantung, stroke',
                  color: Colors.red,
                  borderColor: Colors.red.shade100,
                  backgroundColor: Colors.red.shade50,
                ),
                _buildEmergencyCard(
                  key: 'kebakaran',
                  icon: Icons.local_fire_department,
                  title: 'Kebakaran',
                  subtitle: 'Kebakaran rumah, gedung, hutan',
                  color: Colors.orange,
                  borderColor: Colors.orange.shade100,
                  backgroundColor: Colors.orange.shade50,
                ),
                _buildEmergencyCard(
                  key: 'keamanan',
                  icon: Icons.person,
                  title: 'Keamanan',
                  subtitle: 'Pencurian, perampokan, kekerasan',
                  color: Colors.blue,
                  borderColor: Colors.blue.shade100,
                  backgroundColor: Colors.blue.shade50,
                ),
                _buildEmergencyCard(
                  key: 'bencana',
                  icon: Icons.warning,
                  title: 'Bencana Alam',
                  subtitle: 'Banjir, gempa, tanah longsor',
                  color: Colors.green,
                  borderColor: Colors.green.shade100,
                  backgroundColor: Colors.green.shade50,
                ),
              ],
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
                          ? 'Kategori terpilih: ${_getCategoryDisplayName(selectedEmergencyCategory!)}'
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
                          child: Text(
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

            // User data loading or info card
            if (isLoadingUserData)
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
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.blue,
                      ),
                    ),
                    SizedBox(width: 12),
                    Text(
                      'Memuat data pengguna...',
                      style: TextStyle(color: Colors.grey),
                    ),
                  ],
                ),
              )
            else if (userName != null)
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
                        const Icon(Icons.person, color: Colors.blue),
                        const SizedBox(width: 8),
                        const Text(
                          'Informasi Pelapor',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.black87,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade50,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: Colors.grey.shade200),
                      ),
                      child: Column(
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.person_outline,
                                  size: 20, color: Colors.grey),
                              const SizedBox(width: 8),
                              Text(
                                userName!,
                                style: const TextStyle(
                                    fontWeight: FontWeight.w500),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(Icons.phone,
                                  size: 20, color: Colors.grey),
                              const SizedBox(width: 8),
                              Text(userPhone!),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(Icons.credit_card,
                                  size: 20, color: Colors.grey),
                              const SizedBox(width: 8),
                              Text(userNik!),
                            ],
                          ),
                        ],
                      ),
                    ),
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

  Widget _buildEmergencyCard({
    required String key,
    required IconData icon,
    required String title,
    required String subtitle,
    required Color color,
    required Color borderColor,
    required Color backgroundColor,
  }) {
    final bool isSelected = selectedEmergencyCategory == key;

    return GestureDetector(
      onTap: () {
        setState(() {
          selectedEmergencyCategory = key;
        });
      },
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? color.withOpacity(0.1) : backgroundColor,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? color : borderColor,
            width: isSelected ? 2.5 : 1.5,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: color.withOpacity(0.3),
                    blurRadius: 8,
                    spreadRadius: 1,
                  ),
                ]
              : null,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: isSelected ? color : color,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                icon,
                color: Colors.white,
                size: 24,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: isSelected ? color : Colors.black87,
              ),
            ),
            const SizedBox(height: 2),
            Flexible(
              child: Text(
                subtitle,
                style: TextStyle(
                  fontSize: 10,
                  color: isSelected ? color.withOpacity(0.8) : Colors.grey[700],
                  height: 1.2,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
            if (isSelected) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  Icon(
                    Icons.check_circle,
                    size: 12,
                    color: color,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    'Dipilih',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: color,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}
