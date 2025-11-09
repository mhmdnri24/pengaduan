import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'dart:io';
import '../services/complaint_service.dart';
import '../services/api_service.dart';
import '../components/emergency_card.dart';
import 'package:quickalert/quickalert.dart';

class AddComplaintPage extends StatefulWidget {
  final bool showAppBar;
  const AddComplaintPage({Key? key, this.showAppBar = true}) : super(key: key);

  @override
  State<AddComplaintPage> createState() => _AddComplaintPageState();
}

class _AddComplaintPageState extends State<AddComplaintPage> {
  String? selectedCategory;
  List<Map<String, dynamic>> _categories = [];
  bool _isLoadingCategories = false;
  final TextEditingController titleController = TextEditingController();
  final TextEditingController descriptionController = TextEditingController();
  final TextEditingController locationController = TextEditingController();
  int descriptionCount = 0;
  String? selectedUrgency; // 'low','medium','high'
  bool anonymous = false;
  List<XFile> selectedImages = [];

  // User data from session
  String? userName;
  String? userNik;
  String? userPhone;
  String? userPhotoUrl;
  String? lat;
  String? lng;
  String? masId;

  // Loading and error states
  bool isLoading = false;
  bool isLoadingUserData = true;
  bool isLoadingLocation = false;
  String? errorMessage;

  @override
  void dispose() {
    titleController.dispose();
    descriptionController.dispose();
    locationController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _fetchCategories();
    _getCurrentLocation();
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

  Future<void> _loadUserData() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();

      if (mounted) {
        setState(() {
          userName = prefs.getString('user_name');
          userNik = prefs.getString('user_nik');
          userPhone = prefs.getString('user_phone');
          masId = prefs.getString('user_id');
          userPhotoUrl = prefs.getString('user_photo_url');
          isLoadingUserData = false;
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

  Widget _photoPlaceholder() {
    return GestureDetector(
      onTap: _pickImage,
      child: Container(
        height: 80,
        width: 100,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: const [
              Icon(Icons.add_a_photo, color: Colors.grey, size: 24),
              SizedBox(height: 4),
              Text('Tambah',
                  style: TextStyle(color: Colors.grey, fontSize: 11)),
              Text('Foto', style: TextStyle(color: Colors.grey, fontSize: 11))
            ],
          ),
        ),
      ),
    );
  }

  Widget _imageWidget(XFile file) {
    return Stack(
      children: [
        Container(
          height: 80,
          width: 100,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(8),
            image: DecorationImage(
              image: FileImage(File(file.path)),
              fit: BoxFit.cover,
            ),
          ),
        ),
        Positioned(
          top: 4,
          right: 4,
          child: GestureDetector(
            onTap: () => setState(() => selectedImages.remove(file)),
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(
                color: Colors.black54,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.close, color: Colors.white, size: 14),
            ),
          ),
        ),
      ],
    );
  }

  List<Widget> _buildPhotoWidgets() {
    List<Widget> widgets =
        selectedImages.map((img) => _imageWidget(img)).toList();
    if (widgets.length < 3) {
      widgets.add(_photoPlaceholder());
    }
    return widgets
        .expand((widget) => [widget, const SizedBox(width: 12)])
        .toList()
      ..removeLast();
  }

  Future<void> _pickImage() async {
    final ImagePicker picker = ImagePicker();

    // Show options dialog
    await showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Pilih Sumber Foto'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                leading: const Icon(Icons.camera_alt, color: Colors.blue),
                title: const Text('Kamera'),
                subtitle: const Text('Ambil foto langsung'),
                onTap: () async {
                  Navigator.pop(context);
                  await _pickImageFromCamera(picker);
                },
              ),
              const Divider(),
              ListTile(
                leading: const Icon(Icons.photo_library, color: Colors.green),
                title: const Text('Galeri'),
                subtitle: const Text('Pilih dari galeri'),
                onTap: () async {
                  Navigator.pop(context);
                  await _pickImageFromGallery(picker);
                },
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _pickImageFromCamera(ImagePicker picker) async {
    try {
      // Request camera permission
      var cameraStatus = await Permission.camera.request();
      if (cameraStatus.isDenied) {
        _showError('Izin kamera diperlukan untuk mengambil foto');
        return;
      }

      if (cameraStatus.isPermanentlyDenied) {
        _showPermissionDeniedDialog('kamera');
        return;
      }

      final XFile? image = await picker.pickImage(
        source: ImageSource.camera,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 85,
      );

      if (image != null) {
        await _validateAndAddImage(image);
      }
    } catch (e) {
      _showError('Gagal mengakses kamera: $e');
    }
  }

  Future<void> _pickImageFromGallery(ImagePicker picker) async {
    try {
      // Request storage permission for gallery access
      var storageStatus = await Permission.storage.request();
      if (storageStatus.isDenied) {
        _showError('Izin akses galeri diperlukan untuk memilih foto');
        return;
      }

      if (storageStatus.isPermanentlyDenied) {
        _showPermissionDeniedDialog('akses galeri');
        return;
      }

      final XFile? image = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 85,
      );

      if (image != null) {
        await _validateAndAddImage(image);
      }
    } catch (e) {
      _showError('Gagal mengakses galeri: $e');
    }
  }

  Future<void> _validateAndAddImage(XFile image) async {
    try {
      // Check file size (5MB limit as mentioned in UI)
      final file = File(image.path);
      final fileSize = await file.length();

      if (fileSize > 5 * 1024 * 1024) {
        if (mounted) {
          _showError('Ukuran foto terlalu besar. Maksimal 5MB.');
        }
        return;
      }

      if (mounted) {
        setState(() => selectedImages.add(image));
      }
    } catch (e) {
      if (mounted) {
        _showError('Gagal memproses foto: $e');
      }
    }
  }

  bool _validateForm() {
    if (selectedCategory == null) {
      _showError('Pilih kategori laporan');
      return false;
    }
    if (titleController.text.trim().isEmpty) {
      _showError('Judul laporan tidak boleh kosong');
      return false;
    }
    if (descriptionController.text.trim().isEmpty) {
      _showError('Deskripsi laporan tidak boleh kosong');
      return false;
    }
    if (locationController.text.trim().isEmpty) {
      _showError('Lokasi kejadian tidak boleh kosong');
      return false;
    }
    if (userName == null || userName!.isEmpty) {
      _showError('Data pengguna tidak tersedia. Silakan login kembali.');
      return false;
    }
    if (userPhone == null || userPhone!.isEmpty) {
      _showError('Nomor telepon pengguna tidak tersedia');
      return false;
    }
    if (userNik == null || userNik!.isEmpty) {
      _showError('NIK pengguna tidak tersedia');
      return false;
    }
    return true;
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
    );
  }

  void _showPermissionDeniedDialog(String permissionType) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Izin Diperlukan'),
          content: Text(
            'Aplikasi memerlukan izin $permissionType untuk mengambil foto. '
            'Silakan aktifkan izin di pengaturan aplikasi.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            TextButton(
              onPressed: () {
                Navigator.pop(context);
                openAppSettings();
              },
              child: const Text('Buka Pengaturan'),
            ),
          ],
        );
      },
    );
  }

  Future<void> _getCurrentLocation() async {
    setState(() {
      isLoadingLocation = true;
    });

    try {
      // Check location permission
      LocationPermission permission = await Geolocator.checkPermission();

      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          _showError('Izin lokasi diperlukan untuk menggunakan fitur ini');
          setState(() {
            isLoadingLocation = false;
          });
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        _showPermissionDeniedDialog('lokasi');
        setState(() {
          isLoadingLocation = false;
        });
        return;
      }

      // Check if location services are enabled
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _showError('Layanan lokasi tidak aktif. Mohon aktifkan GPS Anda.');
        setState(() {
          isLoadingLocation = false;
        });
        return;
      }

      // Get current position
      Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high,
      );

      setState(() {
        lat = position.latitude.toString();
        lng = position.longitude.toString();
      });

      // Get address from coordinates
      List<Placemark> placemarks = await placemarkFromCoordinates(
        position.latitude,
        position.longitude,
      );

      if (placemarks.isNotEmpty) {
        Placemark place = placemarks[0];
        String address = '';

        if (place.street != null && place.street!.isNotEmpty) {
          address += place.street!;
        }
        if (place.subLocality != null && place.subLocality!.isNotEmpty) {
          address += address.isNotEmpty
              ? ', ${place.subLocality}'
              : place.subLocality!;
        }
        if (place.locality != null && place.locality!.isNotEmpty) {
          address +=
              address.isNotEmpty ? ', ${place.locality}' : place.locality!;
        }
        if (place.subAdministrativeArea != null &&
            place.subAdministrativeArea!.isNotEmpty) {
          address += address.isNotEmpty
              ? ', ${place.subAdministrativeArea}'
              : place.subAdministrativeArea!;
        }
        if (place.postalCode != null && place.postalCode!.isNotEmpty) {
          address +=
              address.isNotEmpty ? ' ${place.postalCode}' : place.postalCode!;
        }

        if (address.isEmpty) {
          address =
              'Lat: ${position.latitude.toStringAsFixed(6)}, Long: ${position.longitude.toStringAsFixed(6)}';
        }

        setState(() {
          locationController.text = address;
          isLoadingLocation = false;
        });

        _showSuccess('Lokasi berhasil didapatkan');
      }
    } catch (e) {
      setState(() {
        isLoadingLocation = false;
      });
      print('$e');
      _showError('Gagal mendapatkan lokasi: $e');
    }
  }

  Future<void> _submitComplaint() async {
    if (!mounted) return;

    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    try {
      // Convert XFile images to File objects
      List<File> imageFiles =
          selectedImages.map((xfile) => File(xfile.path)).toList();
      print('imageFiles.length: ${imageFiles.length}');

      // Submit complaint
      var response = await ComplaintService.instance.submitComplaint(
        judul: titleController.text.trim(),
        deskripsi: descriptionController.text.trim(),
        alamat: locationController.text.trim(),
        kategori: selectedCategory!,
        pelaporNama: userName!,
        pelaporTelepon: userPhone!,
        pelaporNik: userNik!,
        pelaporAlamat:
            locationController.text.trim(), // Use same address as location
        foto: imageFiles.isNotEmpty ? imageFiles : null,
        lat: lat ?? '',
        lng: lng ?? '',
        masId: masId!,
      );

      if (!mounted) return;

      if (response.success && response.data != null) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.success,
          title: "Sukses",
          text: 'Laporan berhasil dikirim',
        );

        // Clear form
        _clearForm();

        // Navigate back safely with delay to show success message
        Future.delayed(const Duration(milliseconds: 1500), () {
          if (mounted && Navigator.canPop(context)) {
            Navigator.pop(context);
          }
        });
      } else {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Error",
          text: response.error ?? 'Laporan gagal dikirim',
        );
      }
    } catch (e) {
      if (mounted) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Error",
          text: 'Terjadi kesalahan: $e',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  void _clearForm() {
    titleController.clear();
    descriptionController.clear();
    locationController.clear();
    selectedCategory = null;
    selectedUrgency = null;
    selectedImages.clear();
    descriptionCount = 0;
    anonymous = false;
    lat = null;
    lng = null;
  }

  Widget _urgencyButton(String key, String label, Color color) {
    final bool active = selectedUrgency == key;
    return GestureDetector(
      onTap: () => setState(() => selectedUrgency = key),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        decoration: BoxDecoration(
          color: active ? Colors.white : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: active ? color : Colors.grey.shade300,
              width: active ? 2 : 1),
          boxShadow: [
            BoxShadow(
                color: const Color.fromARGB(31, 131, 130, 130),
                blurRadius: 2,
                offset: Offset(0, 2))
          ],
        ),
        child: Column(
          children: [
            Container(
                height: 26,
                width: 26,
                decoration:
                    BoxDecoration(color: color, shape: BoxShape.circle)),
            const SizedBox(height: 6),
            Text(label, style: const TextStyle(fontSize: 12)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8FB),
      resizeToAvoidBottomInset: true,
      extendBody: true,
      appBar: widget.showAppBar
          ? AppBar(
              elevation: 0,
              backgroundColor: const Color(0xFF1C3FAA), // Dominant blue color
              foregroundColor: Colors.white,
              title: const Text(
                'Buat Laporan',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  fontSize: 18,
                  color: Colors.white,
                ),
              ),
              centerTitle: true,
              leading: IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.arrow_back),
                style: IconButton.styleFrom(
                  // backgroundColor: Colors.white.withOpacity(0.2),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              actions: [
                IconButton(
                  onPressed: () {
                    // Add help functionality here
                    QuickAlert.show(
                      context: context,
                      type: QuickAlertType.info,
                      title: "Bantuan",
                      text:
                          'Isi semua field yang diperlukan untuk membuat laporan',
                      autoCloseDuration: const Duration(seconds: 3),
                      showConfirmBtn: false,
                    );
                  },
                  icon: const Icon(Icons.help_outline),
                  style: IconButton.styleFrom(
                    backgroundColor: Colors.white.withOpacity(0.2),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
              ],
            )
          : null,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(10),
        child: Column(
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
            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      const Icon(Icons.label, color: Colors.blue),
                      const SizedBox(width: 8),
                      const Text('Kategori Laporan',
                          style: TextStyle(fontWeight: FontWeight.w600))
                    ]),
                    const SizedBox(height: 12),
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
                                padding:
                                    const EdgeInsets.symmetric(vertical: 20),
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
                                  final id =
                                      (item['pelaporan_id'] ?? item['id'] ?? '')
                                          .toString();
                                  final title = (item['pelaporan_nama'] ??
                                          item['nama'] ??
                                          '')
                                      .toString();
                                  final subtitle =
                                      (item['created_at_formatted'] ?? '')
                                          .toString();
                                  final style = _getCategoryStyle(title, idx);
                                  final Color color = style['color'] as Color;
                                  final IconData iconData =
                                      style['icon'] as IconData;
                                  return EmergencyCard(
                                    id: id,
                                    title: title,
                                    subtitle: subtitle,
                                    icon: iconData,
                                    color: color,
                                    borderColor: color.withOpacity(0.35),
                                    backgroundColor: color.withOpacity(0.06),
                                    selected: selectedCategory == title,
                                    onTap: () => setState(
                                        () => selectedCategory = title),
                                  );
                                }).toList()
                              : [
                                  EmergencyCard(
                                    id: 'infrastruktur',
                                    title: 'Infrastruktur',
                                    subtitle:
                                        'Jalan rusak, lampu mati, drainase',
                                    icon: Icons.account_balance,
                                    color: Colors.green.shade700,
                                    borderColor:
                                        Colors.green.shade700.withOpacity(0.35),
                                    backgroundColor:
                                        Colors.green.shade700.withOpacity(0.06),
                                    selected:
                                        selectedCategory == 'infrastruktur',
                                    onTap: () => setState(() =>
                                        selectedCategory = 'infrastruktur'),
                                  ),
                                  EmergencyCard(
                                    id: 'lingkungan',
                                    title: 'Lingkungan',
                                    subtitle: 'Sampah, pencemaran, taman',
                                    icon: Icons.eco,
                                    color: Colors.green,
                                    borderColor: Colors.green.withOpacity(0.35),
                                    backgroundColor:
                                        Colors.green.withOpacity(0.06),
                                    selected: selectedCategory == 'lingkungan',
                                    onTap: () => setState(
                                        () => selectedCategory = 'lingkungan'),
                                  ),
                                  EmergencyCard(
                                    id: 'keamanan',
                                    title: 'Keamanan',
                                    subtitle: 'Kriminalitas, ketertiban',
                                    icon: Icons.security,
                                    color: Colors.orange,
                                    borderColor:
                                        Colors.orange.withOpacity(0.35),
                                    backgroundColor:
                                        Colors.orange.withOpacity(0.06),
                                    selected: selectedCategory == 'keamanan',
                                    onTap: () => setState(
                                        () => selectedCategory = 'keamanan'),
                                  ),
                                  EmergencyCard(
                                    id: 'layanan',
                                    title: 'Layanan',
                                    subtitle: 'Pelayanan publik, administrasi',
                                    icon: Icons.group,
                                    color: Colors.blue,
                                    borderColor: Colors.blue.withOpacity(0.35),
                                    backgroundColor:
                                        Colors.blue.withOpacity(0.06),
                                    selected: selectedCategory == 'layanan',
                                    onTap: () => setState(
                                        () => selectedCategory = 'layanan'),
                                  ),
                                ]),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 12),

            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Judul Laporan',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      TextField(
                        controller: titleController,
                        textInputAction: TextInputAction.next,
                        enableSuggestions: false,
                        autocorrect: false,
                        textCapitalization: TextCapitalization.sentences,
                        keyboardType: TextInputType.text,
                        toolbarOptions: const ToolbarOptions(
                          copy: false,
                          cut: false,
                          paste: false,
                          selectAll: false,
                        ),
                        decoration: InputDecoration(
                            hintText: 'Masukkan judul laporan yang jelas',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                    color: Color(0xFF2255EE), width: 2))),
                      ),
                    ]),
              ),
            ),

            const SizedBox(height: 12),

            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Deskripsi Lengkap',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      TextField(
                        controller: descriptionController,
                        maxLines: 6,
                        textInputAction: TextInputAction.done,
                        enableSuggestions: false,
                        autocorrect: false,
                        textCapitalization: TextCapitalization.sentences,
                        keyboardType: TextInputType.multiline,
                        toolbarOptions: const ToolbarOptions(
                          copy: false,
                          cut: false,
                          paste: false,
                          selectAll: false,
                        ),
                        onChanged: (v) =>
                            setState(() => descriptionCount = v.length),
                        decoration: InputDecoration(
                            hintText:
                                'Jelaskan detail masalah yang ingin dilaporkan...',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                    color: Color(0xFF2255EE), width: 2))),
                      ),
                      const SizedBox(height: 8),
                      Text('$descriptionCount/500 karakter',
                          style: const TextStyle(
                              color: Colors.grey, fontSize: 12)),
                    ]),
              ),
            ),

            const SizedBox(height: 12),

            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Lokasi Kejadian',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 8),
                      TextField(
                        controller: locationController,
                        textInputAction: TextInputAction.done,
                        enableSuggestions: false,
                        autocorrect: false,
                        textCapitalization: TextCapitalization.sentences,
                        keyboardType: TextInputType.text,
                        toolbarOptions: const ToolbarOptions(
                          copy: false,
                          cut: false,
                          paste: false,
                          selectAll: false,
                        ),
                        decoration: InputDecoration(
                            hintText: 'Masukkan alamat lengkap',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide:
                                    const BorderSide(color: Color(0xFFD0D0D0))),
                            focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                    color: Color(0xFF2255EE), width: 2))),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton.icon(
                        onPressed:
                            isLoadingLocation ? null : _getCurrentLocation,
                        icon: isLoadingLocation
                            ? const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Icon(Icons.my_location),
                        label: Text(
                          isLoadingLocation
                              ? 'Mendapatkan Lokasi...'
                              : 'Gunakan Lokasi Saat Ini',
                        ),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(
                              color: Colors.blue.shade700, width: 1.5),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                          foregroundColor: Colors.blue.shade700,
                          padding: const EdgeInsets.symmetric(
                              vertical: 14, horizontal: 14),
                        ),
                      ),
                    ]),
              ),
            ),

            const SizedBox(height: 12),

            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Foto Pendukung',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 12),
                      Row(children: _buildPhotoWidgets()),
                      const SizedBox(height: 8),
                      const Text(
                          'Maksimal 3 foto, ukuran maksimal 5MB per foto\nTekan untuk memilih dari kamera atau galeri',
                          style: TextStyle(color: Colors.grey, fontSize: 12)),
                    ]),
              ),
            ),

            const SizedBox(height: 12),

            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
              elevation: 0.3,
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Tingkat Urgensi',
                          style: TextStyle(fontWeight: FontWeight.w600)),
                      const SizedBox(height: 12),
                      Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                                child: _urgencyButton(
                                    'low', 'Rendah', Colors.green)),
                            const SizedBox(width: 12),
                            Expanded(
                                child: _urgencyButton(
                                    'medium', 'Sedang', Colors.amber)),
                            const SizedBox(width: 12),
                            Expanded(
                                child: _urgencyButton(
                                    'high', 'Tinggi', Colors.red)),
                          ]),
                    ]),
              ),
            ),

            const SizedBox(height: 12),

            // Buttons
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 0),
              child: Row(
                children: [
                  Expanded(
                    child: SizedBox(
                      height: 48,
                      child: Material(
                        color: Colors.transparent,
                        borderRadius: BorderRadius.circular(12),
                        shadowColor: Colors.black12,
                        elevation: isLoading ? 0 : 3,
                        child: Ink(
                          decoration: BoxDecoration(
                            gradient: isLoading
                                ? LinearGradient(colors: [
                                    Colors.grey.shade400,
                                    Colors.grey.shade600
                                  ])
                                : const LinearGradient(colors: [
                                    Color(0xFF2255EE),
                                    Color(0xFF4285F4)
                                  ]),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: InkWell(
                            onTap: isLoading
                                ? null
                                : () {
                                    if (_validateForm()) {
                                      _submitComplaint();
                                    }
                                  },
                            borderRadius: BorderRadius.circular(12),
                            splashColor: Colors.white24,
                            highlightColor: Colors.white10,
                            child: Padding(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 12),
                              child: isLoading
                                  ? const Center(
                                      child: SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2),
                                      ),
                                    )
                                  : Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: const [
                                        Icon(Icons.send,
                                            size: 18, color: Colors.white),
                                        SizedBox(width: 8),
                                        Text(
                                          'Kirim Laporan',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontWeight: FontWeight.w600,
                                            fontSize: 15,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: SizedBox(
                      height: 48,
                      child: OutlinedButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.save_outlined,
                            size: 18, color: Colors.black54),
                        label: const Text(
                          'Simpan Draft',
                          style: TextStyle(
                            color: Colors.black87,
                            fontWeight: FontWeight.w600,
                            fontSize: 15,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          backgroundColor: Colors.grey.shade100,
                          padding: const EdgeInsets.symmetric(
                              vertical: 10, horizontal: 14),
                          side:
                              BorderSide(color: Colors.grey.shade300, width: 1),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
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
