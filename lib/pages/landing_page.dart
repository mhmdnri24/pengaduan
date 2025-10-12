import 'package:flutter/material.dart';
import '../controllers/landing_controller.dart';
import 'package:pin_code_fields/pin_code_fields.dart';

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

  // TODO: Logic lama tetap ada di sini
  // Misalnya: controller, fungsi navigasi ke login/daftar dll
  void _goToRegister() {
    // logika lama ke halaman register
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
        backgroundColor: Colors.transparent,
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
            colors: [Color(0xFF2F80ED), Color(0xFF56CCF2)],
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
                  onPressed: _goToRegister,
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
