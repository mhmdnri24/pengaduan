import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:geolocator/geolocator.dart';
import 'package:geocoding/geocoding.dart';
import 'dart:io';
import '../services/complaint_service.dart';
import 'package:quickalert/quickalert.dart';

class AddComplaintPage extends StatefulWidget {
  final bool showAppBar;
  const AddComplaintPage({Key? key, this.showAppBar = true}) : super(key: key);

  @override
  State<AddComplaintPage> createState() => _AddComplaintPageState();
}

class _AddComplaintPageState extends State<AddComplaintPage> {
  String? selectedCategory;
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
    _getCurrentLocation();
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

  Widget _buildCategoryTile(
      String key, IconData icon, Color iconBg, String label) {
    final bool active = selectedCategory == key;
    return GestureDetector(
      onTap: () => setState(() => selectedCategory = key),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: active ? Colors.blue.shade700 : Colors.grey.shade200),
          boxShadow: [
            BoxShadow(
                color: const Color.fromARGB(31, 177, 174, 174),
                blurRadius: 6,
                offset: Offset(0, 2))
          ],
        ),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              height: 48,
              width: 48,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: Colors.white),
            ),
            const SizedBox(height: 8),
            Text(label,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
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
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.green),
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
                icon: const Icon(Icons.arrow_back_ios),
                style: IconButton.styleFrom(
                  backgroundColor: Colors.white.withOpacity(0.2),
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
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                            'Bantuan: Isi semua field yang diperlukan untuk membuat laporan'),
                        duration: Duration(seconds: 3),
                      ),
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
                      children: [
                        _buildCategoryTile(
                            'infrastruktur',
                            Icons.account_balance,
                            Colors.green,
                            'Infrastruktur'),
                        _buildCategoryTile('lingkungan', Icons.eco,
                            Colors.green.shade700, 'Lingkungan'),
                        _buildCategoryTile('keamanan', Icons.security,
                            Colors.orange, 'Keamanan'),
                        _buildCategoryTile(
                            'layanan', Icons.group, Colors.blue, 'Layanan'),
                      ],
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

            // User data loading or info card
            if (isLoadingUserData)
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 0.3,
                child: const Padding(
                  padding: EdgeInsets.all(20),
                  child: Row(
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
                ),
              )
            else if (userName != null)
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
                      Row(
                        children: [
                          const Icon(Icons.person, color: Colors.blue),
                          const SizedBox(width: 8),
                          const Text('Informasi Pelapor',
                              style: TextStyle(fontWeight: FontWeight.w600)),
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
              ),

            // Card(
            //   color: Colors.white,
            //   shape:
            //       RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            //   elevation: 1,
            //   child: Padding(
            //     padding: const EdgeInsets.all(14),
            //     child: Row(children: [
            //       Checkbox(
            //           value: anonymous,
            //           onChanged: (v) => setState(() => anonymous = v ?? false)),
            //       const SizedBox(width: 8),
            //       const Expanded(
            //           child: Text(
            //               'Laporan Anonim\nIdentitas Anda akan disembunyikan dari publik',
            //               style: TextStyle(color: Colors.black87))),
            //     ]),
            //   ),
            // ),

            const SizedBox(height: 12),

            const SizedBox(height: 18),

            // Buttons
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 0),
              child: Column(children: [
                // Gradient submit button
                InkWell(
                  onTap: isLoading
                      ? null
                      : () {
                          if (_validateForm()) {
                            _submitComplaint();
                          }
                        },
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    height: 54,
                    decoration: BoxDecoration(
                      gradient: isLoading
                          ? LinearGradient(colors: [
                              Colors.grey.shade400,
                              Colors.grey.shade600
                            ])
                          : const LinearGradient(
                              colors: [Color(0xFF2255EE), Color(0xFF4285F4)]),
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                            color: Colors.black12,
                            blurRadius: 8,
                            offset: Offset(0, 4))
                      ],
                    ),
                    child: Center(
                        child: isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2))
                            : const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                    Icon(Icons.send, color: Colors.white),
                                    SizedBox(width: 8),
                                    Text('Kirim Laporan',
                                        style: TextStyle(
                                            color: Colors.white,
                                            fontWeight: FontWeight.bold))
                                  ])),
                  ),
                ),
                const SizedBox(height: 12),
                OutlinedButton(
                  onPressed: () {},
                  style: OutlinedButton.styleFrom(
                    backgroundColor: Colors.grey.shade200,
                    padding: const EdgeInsets.symmetric(
                        vertical: 16, horizontal: 20),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const SizedBox(
                      width: double.infinity,
                      child: Center(
                          child: Text('Simpan Sebagai Draft',
                              style: TextStyle(color: Colors.black54)))),
                ),
                const SizedBox(height: 24),
              ]),
            ),
          ],
        ),
      ),
    );
  }
}
