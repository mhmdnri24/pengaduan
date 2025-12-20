import 'package:flutter/material.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import '../pages/landing_page.dart';
import '../controllers/landing_controller.dart';
import '../pages/dashboard_page.dart';
import '../pages/detail_pengaduan_page.dart';
import '../services/session_service.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  bool _isImageCached = false;
  String? _splashImageUrl;

  @override
  void initState() {
    super.initState();
    _loadSplashImage();
    _checkAuthAndNavigate();
  }

  Future<void> _loadSplashImage() async {
    print('Loading splash image from session...');
    final sessionService = SessionService.instance;
    final splashImageUrl =
        await sessionService.getFromSession('splashscreen_image');

    print('Splash image URL from session: $splashImageUrl');

    // Always update state to ensure UI refreshes
    setState(() {
      if (splashImageUrl != null && splashImageUrl.toString().isNotEmpty) {
        _splashImageUrl = splashImageUrl.toString();
        print('Splash image loaded successfully: $_splashImageUrl');
      } else {
        _splashImageUrl = null;
        print('No splash image found in session, using default asset');
      }
    });

    // Check if we need to trigger a fetch of pengaturan data
    if (_splashImageUrl == null) {
      final pengaturanData =
          await sessionService.getFromSession('pengaturan_data');
      if (pengaturanData == null) {
        print(
            'No pengaturan data in session, splash image will be available on next app start');
      }
    }
    print('_splashImageUrl $_splashImageUrl');
    // Preache network image after state is updated with proper error handling
    if (_splashImageUrl != null && _splashImageUrl!.isNotEmpty) {
      // Try to precache but don't block if it fails
      precacheImage(
              NetworkImage(
                _splashImageUrl!,
                headers: {
                  'User-Agent': 'Mozilla/5.0 (compatible; Flutter)',
                },
              ),
              context)
          .catchError((e) {
        print('Error precaching network image (this is OK): $e');
        // Don't treat this as critical error
      });
    }
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_isImageCached) {
      // Try to precache both splash images
      precacheImage(const AssetImage('assets/images/splash.jpg'), context)
          .catchError((e) {
        print('Error precaching splash.jpg: $e');
      });
      precacheImage(const AssetImage('assets/images/splash.webp'), context)
          .catchError((e) {
        print('Error precaching splash.webp: $e');
      });

      _isImageCached = true;
    }
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

    // Show splash for at least 8 seconds while checking auth
    final start = DateTime.now();
    try {
      final controller = LandingController();
      final session = await controller.getSession();
      final elapsed = DateTime.now().difference(start);
      final remaining = const Duration(seconds: 8) - elapsed;
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
      final remaining = const Duration(seconds: 8) - elapsed;
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
                colors: [Color(0xFF2258DA), Color(0xFF2258DA)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            child: _splashImageUrl != null && _splashImageUrl!.isNotEmpty
                ? Stack(
                    children: [
                      // Network image
                      Image.network(
                        _splashImageUrl!,
                        width: double.infinity,
                        height: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          print('Error loading network image: $error');
                          print('Stack trace: $stackTrace');
                          // Fallback to asset on error
                          return Container(
                            width: double.infinity,
                            height: double.infinity,
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(
                                colors: [Color(0xFF2258DA), Color(0xFF2258DA)],
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                              ),
                            ),
                          );
                        },
                        loadingBuilder: (context, child, loadingProgress) {
                          print(
                              'Loading network image progress: $loadingProgress');
                          if (loadingProgress == null) return child;
                          // Show gradient while loading
                          return Container(
                            width: double.infinity,
                            height: double.infinity,
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(
                                colors: [Color(0xFF2258DA), Color(0xFF2258DA)],
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                              ),
                            ),
                          );
                        },
                      ),
                      // Debug overlay
                      // Positioned(
                      //   top: 50,
                      //   left: 10,
                      //   child: Container(
                      //     padding: const EdgeInsets.all(8),
                      //     color: Colors.black.withOpacity(0.7),
                      //     child: Text(
                      //       'URL: ${_splashImageUrl!.length > 30 ? _splashImageUrl!.substring(0, 30) + '...' : _splashImageUrl!}',
                      //       style: const TextStyle(
                      //         color: Colors.white,
                      //         fontSize: 10,
                      //       ),
                      //     ),
                      //   ),
                      // ),
                    ],
                  )
                : Container(
                    width: double.infinity,
                    height: double.infinity,
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF2258DA), Color(0xFF2258DA)],
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                      ),
                    ),
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
                  // Icon(
                  //   Icons.account_balance,
                  //   size: 64,
                  //   color: Colors.white,
                  // ),
                  SizedBox(height: 16),
                  Text(
                    '',
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  SizedBox(height: 16),
                  Text(
                    '',
                    style: TextStyle(
                      fontSize: 18,
                      color: Colors.white,
                    ),
                  ),
                  SizedBox(height: 32),
                  // CircularProgressIndicator(
                  //   valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  // ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
