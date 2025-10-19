import 'package:flutter/material.dart';
import '../controllers/landing_controller.dart';
import 'package:pin_code_fields/pin_code_fields.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

class LandingPage extends StatefulWidget {
  const LandingPage({Key? key}) : super(key: key);

  @override
  State<LandingPage> createState() => _LandingPageState();
}

class _LandingPageState extends State<LandingPage> {
  final controller = LandingController();
  static const blue = Color(0xFF2D62F2);

  @override
  void initState() {
    super.initState();
    _checkSession();
  }

  Future<void> _checkSession() async {
    try {
      final session = await controller.getSession();
      if (session != null && mounted) {
        Navigator.of(context).pushReplacementNamed('/dashboard');
      }
    } catch (_) {
      // ignore
    }
  }

  void _goToRegister(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      builder: (context) {
        return const RegisterStepperModal();
      },
    );
  }

  void _goToLogin(BuildContext context) {
    // No country code anymore — using NIK
    final phoneController = controller.nikController;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      builder: (context) {
        return Padding(
          padding:
              EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(18.0),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.85,
                  minWidth: 280,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const SizedBox(height: 6),
                    Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: LinearGradient(
                          colors: [blue.withOpacity(0.9), blue],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                      ),
                      child: const Icon(Icons.smartphone,
                          color: Colors.white, size: 32),
                    ),
                    const SizedBox(height: 12),
                    const Text('Login dengan NIK',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 6),
                    const Text('Masukkan NIK untuk menerima kode OTP',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 14),
                    Row(
                      children: const [
                        Icon(Icons.badge, color: Colors.black54, size: 18),
                        SizedBox(width: 8),
                        Text('NIK',
                            style: TextStyle(fontWeight: FontWeight.w600)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: phoneController,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              hintText: '1234567890123456',
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 14),
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(
                                      color: Color(0xFFD0D0D0))),
                              enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(8),
                                  borderSide: const BorderSide(
                                      color: Color(0xFFD0D0D0))),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () => Navigator.of(context).pop(),
                            child: const Padding(
                              padding: EdgeInsets.symmetric(vertical: 12.0),
                              child: Text('Batal'),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: controller.isSending
                                ? null
                                : () async {
                                    final localCtx = context;

                                    final result = await controller.sendOtp(
                                      () => setState(
                                          () => controller.isSending = true),
                                      () => setState(
                                          () => controller.isSending = false),
                                    );

                                    if (!mounted) return;

                                    if (mounted) {
                                      ScaffoldMessenger.of(context)
                                          .showSnackBar(
                                        SnackBar(
                                          content: Text(result.message),
                                          behavior: SnackBarBehavior
                                              .floating, // 👈 penting!
                                          margin: const EdgeInsets.only(
                                            bottom:
                                                10.0, // jarak dari bawah (atur sesuai tinggi FAB + BottomAppBar)
                                            right: 16.0,
                                            left: 16.0,
                                          ),
                                        ),
                                      );
                                    }

                                    if (result.success) {
                                      Navigator.of(localCtx).pop();
                                      _showOtpDialog(localCtx);
                                    }
                                  },
                            icon: controller.isSending
                                ? Container(
                                    width: 18,
                                    height: 18,
                                    child: const CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Icon(Icons.send,
                                    size: 18, color: Colors.white),
                            label: Padding(
                                padding:
                                    const EdgeInsets.symmetric(vertical: 12.0),
                                child: controller.isSending
                                    ? const Text('Mengirim...',
                                        style: TextStyle(color: Colors.white))
                                    : const Text('Kirim',
                                        style: TextStyle(color: Colors.white))),
                            style:
                                ElevatedButton.styleFrom(backgroundColor: blue),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  void _showOtpDialog(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      builder: (context) {
        return Padding(
          padding:
              EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(18.0),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  maxHeight: MediaQuery.of(context).size.height * 0.6,
                  minWidth: 280,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const SizedBox(height: 6),
                    const Text('Masukkan kode OTP',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 12),
                    const Text(
                        'Kode OTP telah dikirim. Masukkan 6 digit kode untuk melanjutkan.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.black54)),
                    const SizedBox(height: 18),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24.0),
                      child: LayoutBuilder(builder: (pinCtx, constraints) {
                        const int length = 6;
                        const double gap = 8.0;
                        final double available = constraints.maxWidth;
                        final double totalGaps = (length - 1) * gap;
                        final double rawField =
                            (available - totalGaps) / length;
                        final double fieldWidth = rawField.clamp(28.0, 48.0);

                        bool verifying = false;

                        return StatefulBuilder(builder: (ctx, setState) {
                          return Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              PinCodeTextField(
                                appContext: pinCtx,
                                length: length,
                                keyboardType: TextInputType.number,
                                onChanged: (v) {},
                                onCompleted: (code) async {
                                  if (verifying) return;
                                  setState(() => verifying = true);

                                  final result = await controller.verifyOtp(
                                      code, () {}, () {});

                                  if (!mounted) return;

                                  setState(() => verifying = false);

                                  if (mounted) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(result.message),
                                        behavior: SnackBarBehavior
                                            .floating, // 👈 penting!
                                        margin: const EdgeInsets.only(
                                          bottom:
                                              80.0, // jarak dari bawah (atur sesuai tinggi FAB + BottomAppBar)
                                          right: 16.0,
                                          left: 16.0,
                                        ),
                                      ),
                                    );
                                  }

                                  if (result.success) {
                                    Navigator.of(context).pop();
                                    Navigator.of(context)
                                        .pushReplacementNamed('/dashboard');
                                  }
                                },
                                pinTheme: PinTheme(
                                  borderRadius: BorderRadius.circular(8),
                                  fieldHeight: fieldWidth,
                                  fieldWidth: fieldWidth,
                                  activeFillColor: blue.withOpacity(0.1),
                                  inactiveFillColor: Colors.grey.shade100,
                                  selectedFillColor: blue.withOpacity(0.2),
                                  activeColor: blue,
                                  inactiveColor: Colors.grey.shade400,
                                  selectedColor: blue,
                                ),
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                              ),
                              const SizedBox(height: 12),
                              if (verifying)
                                const Center(
                                  child: SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(
                                          strokeWidth: 2)),
                                ),
                            ],
                          );
                        });
                      }),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () => Navigator.of(context).pop(),
                            child: const Padding(
                              padding: EdgeInsets.symmetric(vertical: 12.0),
                              child: Text('Batal'),
                            ),
                          ),
                        ),
                      ],
                    )
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: const Color(0xFF1C3FAA),
        elevation: 0,
        leading: Padding(
          padding: const EdgeInsets.only(left: 12),
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            padding: const EdgeInsets.all(6),
            child: const Icon(Icons.account_balance, color: Colors.white),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.menu, color: Colors.white),
            onPressed: () {
              // logika lama menu atau drawer
            },
          ),
        ],
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF2258DA), Color(0xFF2F80ED)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const SizedBox(height: 40),

              // Ikon gedung di tengah
              Container(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white.withOpacity(0.5)),
                ),
                padding: const EdgeInsets.all(24),
                child: const Icon(
                  Icons.account_balance,
                  size: 50,
                  color: Colors.white,
                ),
              ),

              const SizedBox(height: 30),

              // Judul
              const Text(
                'Lapor Pak Wali',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: 16),

              // Deskripsi
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 24),
                child: Text(
                  'Platform Digital Pemerintah Daerah untuk Melayani Aspirasi dan Keluhan Masyarakat. Transparansi, Responsif, dan Terpercaya.',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    height: 1.5,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),

              const SizedBox(height: 40),

              // Tombol Daftar
              SizedBox(
                width: 250,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () => _goToRegister(context),
                  icon: const Icon(Icons.person_add_alt, color: Colors.white),
                  label: const Text(
                    'Daftar Sekarang',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white.withOpacity(0.15),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Tombol Masuk (border putih)
              SizedBox(
                width: 250,
                height: 48,
                child: OutlinedButton.icon(
                  onPressed: () => _goToLogin(context),
                  icon: const Icon(Icons.login, color: Colors.white),
                  label: const Text(
                    'Masuk',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.white),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    foregroundColor: Colors.white,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class RegisterStepperModal extends StatefulWidget {
  const RegisterStepperModal({Key? key}) : super(key: key);

  @override
  State<RegisterStepperModal> createState() => _RegisterStepperModalState();
}

class _RegisterStepperModalState extends State<RegisterStepperModal> {
  int currentStep = 0;
  final PageController _pageController = PageController();
  
  // Form controllers
  final TextEditingController _namaController = TextEditingController();
  final TextEditingController _nikController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  bool _agreeToTerms = false;
  
  // Photo variables
  File? _selfiePhoto;
  File? _idCardPhoto;
  final ImagePicker _picker = ImagePicker();
  
  static const blue = Color(0xFF2D62F2);

  @override
  void initState() {
    super.initState();
    _namaController.addListener(() {
      setState(() {});
    });
    _nikController.addListener(() {
      setState(() {});
    });
    _phoneController.addListener(() {
      setState(() {});
    });
  }

  @override
  void dispose() {
    _namaController.dispose();
    _nikController.dispose();
    _phoneController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  void _nextStep() {
    if (currentStep < 3) {
      setState(() {
        currentStep++;
      });
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }
  }

  void _previousStep() {
    if (currentStep > 0) {
      setState(() {
        currentStep--;
      });
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }
  }

  Future<void> _pickSelfie() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _selfiePhoto = File(image.path);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error mengambil foto: $e')),
      );
    }
  }

  Future<void> _pickIdCard() async {
    try {
      final XFile? image = await _picker.pickImage(
        source: ImageSource.camera,
        imageQuality: 80,
      );
      if (image != null) {
        setState(() {
          _idCardPhoto = File(image.path);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error mengambil foto: $e')),
      );
    }
  }

  void _register() {
    if (_agreeToTerms && _selfiePhoto != null && _idCardPhoto != null) {
      // TODO: Implement registration logic
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Registrasi berhasil!'),
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: Container(
        height: MediaQuery.of(context).size.height * 0.9,
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(12)),
        ),
        child: Column(
          children: [
            // Progress indicator
            Container(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Langkah ${currentStep + 1} dari 4',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Colors.grey,
                        ),
                      ),
                      Text(
                        '${((currentStep + 1) / 4 * 100).round()}%',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  LinearProgressIndicator(
                    value: (currentStep + 1) / 4,
                    backgroundColor: Colors.grey.shade200,
                    valueColor: const AlwaysStoppedAnimation<Color>(blue),
                  ),
                ],
              ),
            ),
            
            // Page content
            Expanded(
              child: PageView(
                controller: _pageController,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  _buildStep1(),
                  _buildStep2(),
                  _buildStep3(), // Photo upload
                  _buildStep4(), // Final agreement
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStep1() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Informasi Pribadi',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Pastikan data sesuai dengan KTP Anda.',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 32),
          
          // Nama Lengkap field
          const Text(
            'Nama Lengkap',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _namaController,
            decoration: InputDecoration(
              hintText: 'Masukkan nama lengkap',
              prefixIcon: const Icon(Icons.person, color: Colors.grey),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: blue),
              ),
            ),
          ),
          const SizedBox(height: 24),
          
          // NIK field
          const Text(
            '16 Digit NIK KTP',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _nikController,
            keyboardType: TextInputType.number,
            maxLength: 16,
            decoration: InputDecoration(
              hintText: '1234567890123456',
              prefixIcon: const Icon(Icons.badge, color: Colors.grey),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: blue),
              ),
            ),
          ),
          const SizedBox(height: 40),
          
          // Continue button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _namaController.text.isNotEmpty && _nikController.text.length == 16
                  ? _nextStep
                  : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: blue,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Text(
                'Lanjutkan',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                ),
              ),
            ),
          ),
          const SizedBox(height: 20),
          
          // Login link
          Center(
            child: RichText(
              text: const TextSpan(
                style: TextStyle(color: Colors.grey),
                children: [
                  TextSpan(text: 'Sudah punya akun? '),
                  TextSpan(
                    text: 'Masuk di sini',
                    style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStep2() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Informasi Kontak',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Digunakan untuk verifikasi dan notifikasi.',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 32),
          
          // Phone number field
          const Text(
            'Nomor WhatsApp',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _phoneController,
            keyboardType: TextInputType.phone,
            decoration: InputDecoration(
              hintText: '812 3456 7890',
              prefixIcon: const Icon(Icons.phone_android, color: Colors.grey),
              prefixText: '+62 ',
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: Colors.grey),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: const BorderSide(color: blue),
              ),
            ),
          ),
          const SizedBox(height: 40),
          
          // Navigation buttons
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: _previousStep,
                  child: const Text(
                    'Kembali',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _phoneController.text.isNotEmpty
                        ? _nextStep
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: blue,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Lanjutkan',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          
          // Login link
          Center(
            child: RichText(
              text: const TextSpan(
                style: TextStyle(color: Colors.grey),
                children: [
                  TextSpan(text: 'Sudah punya akun? '),
                  TextSpan(
                    text: 'Masuk di sini',
                    style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStep3() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Upload Foto',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Ambil foto selfie dan foto KTP untuk verifikasi.',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 32),
          
          // Selfie Photo Section
          const Text(
            'Foto Selfie',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Ambil foto selfie yang jelas untuk verifikasi identitas.',
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: _pickSelfie,
            child: Container(
              width: double.infinity,
              height: 200,
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _selfiePhoto != null ? blue : Colors.grey.shade300,
                  width: 2,
                ),
              ),
              child: _selfiePhoto != null
                  ? ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: Image.file(
                        _selfiePhoto!,
                        fit: BoxFit.cover,
                      ),
                    )
                  : Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.camera_alt,
                          size: 48,
                          color: Colors.grey.shade400,
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Tap untuk mengambil foto selfie',
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
            ),
          ),
          const SizedBox(height: 32),
          
          // ID Card Photo Section
          const Text(
            'Foto KTP',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Ambil foto KTP yang jelas dan sesuai dengan frame.',
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: _pickIdCard,
            child: Container(
              width: double.infinity,
              height: 200,
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: _idCardPhoto != null ? blue : Colors.grey.shade300,
                  width: 2,
                ),
              ),
              child: _idCardPhoto != null
                  ? Stack(
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: Image.file(
                            _idCardPhoto!,
                            fit: BoxFit.cover,
                            width: double.infinity,
                            height: double.infinity,
                          ),
                        ),
                        // Card frame overlay
                        Positioned.fill(
                          child: Container(
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                color: Colors.white,
                                width: 3,
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.3),
                                  blurRadius: 4,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    )
                  : Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          width: 120,
                          height: 80,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: Colors.grey.shade400,
                              width: 2,
                            ),
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.credit_card,
                                size: 32,
                                color: Colors.grey.shade400,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'KTP',
                                style: TextStyle(
                                  color: Colors.grey.shade600,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Tap untuk mengambil foto KTP',
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 14,
                          ),
                        ),
                      ],
                    ),
            ),
          ),
          const SizedBox(height: 40),
          
          // Navigation buttons
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: _previousStep,
                  child: const Text(
                    'Kembali',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _selfiePhoto != null && _idCardPhoto != null
                        ? _nextStep
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: blue,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Lanjutkan',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          
          // Login link
          Center(
            child: RichText(
              text: const TextSpan(
                style: TextStyle(color: Colors.grey),
                children: [
                  TextSpan(text: 'Sudah punya akun? '),
                  TextSpan(
                    text: 'Masuk di sini',
                    style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStep4() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Persetujuan Akhir',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.black87,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Satu langkah lagi untuk menyelesaikan.',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 32),
          
          // Agreement checkbox
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: blue.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Checkbox(
                  value: _agreeToTerms,
                  onChanged: (value) {
                    setState(() {
                      _agreeToTerms = value ?? false;
                    });
                  },
                  activeColor: blue,
                ),
                Expanded(
                  child: RichText(
                    text: const TextSpan(
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.black87,
                        height: 1.4,
                      ),
                      children: [
                        TextSpan(text: 'Saya menyatakan bahwa data yang saya isi adalah benar dan saya telah membaca serta setuju dengan '),
                        TextSpan(
                          text: 'Syarat & Ketentuan',
                          style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                        ),
                        TextSpan(text: ' dan '),
                        TextSpan(
                          text: 'Kebijakan Privasi',
                          style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                        ),
                        TextSpan(text: '.'),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          
          // Security message
          Row(
            children: [
              Icon(Icons.security, color: Colors.green, size: 16),
              const SizedBox(width: 8),
              const Text(
                'Data Anda dijamin aman dan terenkripsi.',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.green,
                ),
              ),
            ],
          ),
          const SizedBox(height: 40),
          
          // Navigation buttons
          Row(
            children: [
              Expanded(
                child: TextButton(
                  onPressed: _previousStep,
                  child: const Text(
                    'Kembali',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _agreeToTerms ? _register : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.green,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                    child: const Text(
                      'Daftar Akun Saya',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          
          // Login link
          Center(
            child: RichText(
              text: const TextSpan(
                style: TextStyle(color: Colors.grey),
                children: [
                  TextSpan(text: 'Sudah punya akun? '),
                  TextSpan(
                    text: 'Masuk di sini',
                    style: TextStyle(color: blue, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
