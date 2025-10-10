import 'package:flutter/material.dart';
import 'dart:async';
import '../pages/landing_page.dart';
import '../controllers/landing_controller.dart';
import '../pages/dashboard_page.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAuthAndNavigate();
  }

  Future<void> _checkAuthAndNavigate() async {
    // Show splash for at least 1.5-2.5s while checking auth
    final start = DateTime.now();
    try {
      final controller = LandingController();
      final session = await controller.getSession();
      final elapsed = DateTime.now().difference(start);
      final remaining = const Duration(seconds: 2) - elapsed;
      if (remaining.isNegative) {
        // nothing
      } else {
        await Future.delayed(remaining);
      }

      if (!mounted) return;

      if (session != null) {
        Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const DashboardPage()));
      } else {
        Navigator.of(context).pushReplacement(
            MaterialPageRoute(builder: (_) => const LandingPage()));
      }
    } catch (e) {
      // on any error, fall back to landing page after a short delay
      final elapsed = DateTime.now().difference(start);
      final remaining = const Duration(seconds: 2) - elapsed;
      if (!remaining.isNegative) await Future.delayed(remaining);
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LandingPage()));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF2258DA),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.account_balance,
              size: 64,
              color: Colors.white,
            ),
            const SizedBox(height: 16),
            const Text(
              'Lapor Pak Wali',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Memuat halaman',
              style: TextStyle(
                fontSize: 18,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 32),
            const CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
            ),
          ],
        ),
      ),
    );
  }
}
