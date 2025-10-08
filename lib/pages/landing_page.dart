// lib/views/landing_page.dart
import 'package:flutter/material.dart';
import '../controllers/landing_controller.dart';
import 'package:pin_code_fields/pin_code_fields.dart';

class LandingPage extends StatefulWidget {
  const LandingPage({super.key});

  @override
  State<LandingPage> createState() => _LandingPageState();
}

class _LandingPageState extends State<LandingPage> {
  final controller = LandingController();
  static const blue = Color(0xFF2D62F2);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: Container(
                width: double.infinity,
                padding:
                    const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                decoration: const BoxDecoration(color: blue),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // HEADER BAR
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: Colors.white24,
                          child: Icon(Icons.account_balance,
                              color: Colors.white, size: 18),
                        ),
                        Icon(Icons.menu, color: Colors.white, size: 24),
                      ],
                    ),

                    const SizedBox(height: 40),
                    const CircleAvatar(
                      radius: 36,
                      backgroundColor: Colors.white24,
                      child: Icon(Icons.account_balance,
                          size: 40, color: Colors.white),
                    ),
                    const SizedBox(height: 46),

                    const Text(
                      'Lapor Pak Wali',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        height: 1.3,
                      ),
                    ),

                    const SizedBox(height: 20),
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16.0),
                      child: Text(
                        'Platform Digital Pemerintah Daerah untuk Melayani Aspirasi dan Keluhan Masyarakat. Transparansi, Responsif, dan Terpercaya.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 13.5,
                          height: 1.5,
                        ),
                      ),
                    ),

                    const SizedBox(height: 60),

                    // Tombol Kirim OTP
                    StatefulBuilder(builder: (context, setState) {
                      return ElevatedButton.icon(
                        onPressed: controller.isSending
                            ? null
                            : () async {
                                // capture builder context before async gap
                                final localCtx = context;

                                final result = await controller.sendOtp(
                                  () => setState(
                                      () => controller.isSending = true),
                                  () => setState(
                                      () => controller.isSending = false),
                                );

                                if (!mounted) return;

                                ScaffoldMessenger.of(localCtx).showSnackBar(
                                  SnackBar(content: Text(result.message)),
                                );

                                if (result.success) {
                                  // show OTP entry dialog
                                  _showOtpDialog(localCtx);
                                }
                              },
                        icon: const Icon(Icons.send, size: 18),
                        label: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 12.0),
                          child: controller.isSending
                              ? const SizedBox(
                                  height: 16,
                                  width: 16,
                                  child: CircularProgressIndicator(
                                      color: Colors.white, strokeWidth: 2),
                                )
                              : const Text('Kirim OTP'),
                        ),
                        style: ElevatedButton.styleFrom(backgroundColor: blue),
                      );
                    }),

                    const SizedBox(height: 14),

                    // Tombol Masuk
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: OutlinedButton.icon(
                        onPressed: () => _showLoginDialog(context),
                        icon: const Icon(Icons.login, size: 20),
                        label: const Text(
                          'Masuk',
                          style: TextStyle(
                              fontSize: 15.5, fontWeight: FontWeight.w600),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side:
                              const BorderSide(color: Colors.white, width: 1.3),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }

  void _showLoginDialog(BuildContext context) {
    // No country code anymore — using NIK
    final phoneController = controller.nikController;

    showDialog(
      context: context,
      builder: (context) {
        return Dialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: AnimatedPadding(
            // animate dialog movement when keyboard appears
            padding:
                MediaQuery.of(context).viewInsets + const EdgeInsets.all(18.0),
            duration: const Duration(milliseconds: 200),
            curve: Curves.easeOut,
            child: SingleChildScrollView(
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  // limit dialog height so keyboard doesn't force overflow
                  maxHeight: MediaQuery.of(context).size.height * 0.8,
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
                    const Text(
                      'Masukkan NIK untuk menerima kode OTP',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: Colors.black54),
                    ),
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
                                  borderRadius: BorderRadius.circular(8)),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
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

                                    ScaffoldMessenger.of(localCtx).showSnackBar(
                                      SnackBar(content: Text(result.message)),
                                    );

                                    if (result.success) {
                                      Navigator.of(localCtx).pop();
                                      _showOtpDialog(localCtx);
                                    }
                                  },
                            icon: const Icon(Icons.send,
                                size: 18, color: Colors.white),
                            label: const Padding(
                              padding: EdgeInsets.symmetric(vertical: 12.0),
                              child: Text('Kirim',
                                  style: TextStyle(color: Colors.white)),
                            ),
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
    showDialog(
      context: context,
      builder: (context) {
        return Dialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(18.0),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.6,
                minWidth: 280,
              ),
              child: SingleChildScrollView(
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
                      style: TextStyle(color: Colors.black54),
                    ),
                    const SizedBox(height: 18),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 24.0),
                      child: LayoutBuilder(builder: (pinCtx, constraints) {
                        // Compute field width so the total width (fields + gaps)
                        // fits into the available constraints.maxWidth.
                        const int length = 6;
                        const double gap = 8.0; // space between fields
                        final double available = constraints.maxWidth;
                        final double totalGaps = (length - 1) * gap;
                        // Reserve minimal width per field and cap maximum width
                        final double rawField =
                            (available - totalGaps) / length;
                        final double fieldWidth = rawField.clamp(28.0, 48.0);

                        // track local verifying state inside the dialog
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

                                  ScaffoldMessenger.of(ctx).showSnackBar(
                                    SnackBar(content: Text(result.message)),
                                  );

                                  if (result.success) {
                                    // close OTP dialog
                                    Navigator.of(ctx).pop();
                                    // GOTO: Dashboard page
                                    Navigator.of(ctx)
                                        .pushReplacementNamed('/dashboard');
                                    // TODO: navigate to authenticated area or persist token
                                  }
                                },
                                pinTheme: PinTheme(
                                  shape: PinCodeFieldShape.box,
                                  borderRadius: BorderRadius.circular(8),
                                  fieldHeight: fieldWidth,
                                  fieldWidth: fieldWidth,
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
                                        strokeWidth: 2),
                                  ),
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
}
