import 'package:flutter/material.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import '../pages/landing_page.dart';
import '../controllers/landing_controller.dart';
import '../pages/dashboard_page.dart';
import '../pages/detail_pengaduan_page.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  bool _isImageCached = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_isImageCached) {
      precacheImage(const AssetImage('assets/images/splash.jpg'), context);
      _isImageCached = true;
    }
  }

  @override
  void initState() {
    super.initState();
    _checkAuthAndNavigate();
  }

  Future<void> _checkAuthAndNavigate() async {
    // Check if we should skip splash and go directly to detail
    final prefs = await SharedPreferences.getInstance();
    final skipSplashToDetail = prefs.getBool('skip_splash_to_detail') ?? false;
    final pendingComplaintId = prefs.getString('pending_complaint_id');

    // Clear the flags immediately after reading
    await prefs.remove('skip_splash_to_detail');
    await prefs.remove('pending_complaint_id');

    if (skipSplashToDetail &&
        pendingComplaintId != null &&
        pendingComplaintId.isNotEmpty) {
      // Skip splash and go directly to detail page
      if (!mounted) return;

      // Minimal delay to ensure app is properly initialized
      await Future.delayed(const Duration(milliseconds: 500));

      if (!mounted) return;

      Navigator.of(context).pushReplacement(MaterialPageRoute(
          builder: (_) =>
              DetailPengaduanPage(complaintId: pendingComplaintId)));
      return;
    }

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
      body: Stack(
        children: [
          // Background image with fallback to gradient
          Container(
            width: double.infinity,
            height: double.infinity,
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF2258DA), Color(0xFF2F80ED)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            child: Image.asset(
              'assets/images/splash.webp',
              // 'https://dashboard.nusakoding.com/uploads/pengaturan/splash.jpg',
              width: double.infinity,
              height: double.infinity,
              fit: BoxFit.cover,
              // errorBuilder: (context, error, stackTrace) {
              //   // Return empty container on error to show gradient background
              //   return const SizedBox.shrink();
              // },
              // loadingBuilder: (context, child, loadingProgress) {
              //   if (loadingProgress == null) return child;
              //   // Show gradient while loading
              //   return const SizedBox.shrink();
              // },
            ),
          ),
          // Semi-transparent overlay and content
          Container(
            width: double.infinity,
            height: double.infinity,
            decoration: BoxDecoration(
              color: Colors.black.withOpacity(0.3),
            ),
            child: const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.account_balance,
                    size: 64,
                    color: Colors.white,
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Lapor Pak Wali',
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Memuat halaman',
                    style: TextStyle(
                      fontSize: 18,
                      color: Colors.white,
                    ),
                  ),
                  SizedBox(height: 32),
                  CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
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
