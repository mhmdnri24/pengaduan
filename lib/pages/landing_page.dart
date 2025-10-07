// lib/views/landing_page.dart
import 'package:flutter/material.dart';
import '../controllers/landing_controller.dart';

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
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
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
                          child: Icon(Icons.account_balance, color: Colors.white, size: 18),
                        ),
                        Icon(Icons.menu, color: Colors.white, size: 24),
                      ],
                    ),

                    const SizedBox(height: 40),
                    const CircleAvatar(
                      radius: 36,
                      backgroundColor: Colors.white24,
                      child: Icon(Icons.account_balance, size: 40, color: Colors.white),
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
                                await controller.sendOtp(
                                  context,
                                  () => setState(() => controller.isSending = true),
                                  () => setState(() => controller.isSending = false),
                                );
                              },
                        icon: const Icon(Icons.send, size: 18),
                        label: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 12.0),
                          child: controller.isSending
                              ? const SizedBox(
                                  height: 16,
                                  width: 16,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
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
                          style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w600),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.white,
                          side: const BorderSide(color: Colors.white, width: 1.3),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
    String countryCode = '+62';
    final phoneController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(18.0),
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
                  child: const Icon(Icons.smartphone, color: Colors.white, size: 32),
                ),
                const SizedBox(height: 12),
                const Text('Verifikasi WhatsApp',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                const Text(
                  'Masukkan nomor WhatsApp untuk menerima kode OTP',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.black54),
                ),
                const SizedBox(height: 14),

                Row(
                  children: const [
                    Icon(Icons.phone_iphone, color: Colors.black54, size: 18),
                    SizedBox(width: 8),
                    Text('Nomor WhatsApp', style: TextStyle(fontWeight: FontWeight.w600)),
                  ],
                ),
                const SizedBox(height: 8),

                Row(
                  children: [
                    DropdownButton<String>(
                      value: countryCode,
                      underline: const SizedBox.shrink(),
                      items: const [
                        DropdownMenuItem(value: '+62', child: Text('+62')),
                        DropdownMenuItem(value: '+1', child: Text('+1')),
                        DropdownMenuItem(value: '+44', child: Text('+44')),
                      ],
                      onChanged: (v) => setState(() => countryCode = v ?? '+62'),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: phoneController,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          hintText: '812345678901',
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
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
                                await controller.sendOtp(
                                  context,
                                  () => setState(() => controller.isSending = true),
                                  () => setState(() => controller.isSending = false),
                                );
                              },
                        
                        // () {
                        //   if (phoneController.text.trim().isEmpty) {
                        //     ScaffoldMessenger.of(context).showSnackBar(
                        //       const SnackBar(content: Text('Masukkan nomor terlebih dahulu')),
                        //     );
                        //     return;
                        //   }
                        //   Navigator.of(context).pop();
                        //   ScaffoldMessenger.of(context).showSnackBar(
                        //     SnackBar(content: Text('Mengirim OTP ke $countryCode ${phoneController.text}')),
                        //   );
                        // },
                        icon: const Icon(Icons.send, size: 18, color: Colors.white),
                        label: const Padding(
                          padding: EdgeInsets.symmetric(vertical: 12.0),
                          child: Text('Kirim', style: TextStyle(color: Colors.white)),
                        ),
                        style: ElevatedButton.styleFrom(backgroundColor: blue),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
