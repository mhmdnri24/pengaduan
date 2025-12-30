import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/session_service.dart';
import '../pages/notifications_page.dart';

class Navbar extends StatefulWidget implements PreferredSizeWidget {
  final int notificationCount;
  final String title;
  final IconData iconic;

  const Navbar({
    super.key,
    this.notificationCount = 0,
    this.title = "Lapor Sang Juara",
    this.iconic = Icons.account_balance,
  });

  @override
  State<Navbar> createState() => _NavbarState();

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}

class _NavbarState extends State<Navbar> {
  String _avatarUrl = "assets/images/profile.jpeg"; // Default fallback
  String? _title;

  @override
  void initState() {
    super.initState();
    _loadUserPhoto();
    _loadTitle();
  }

  Future<void> _loadTitle() async {
    try {
      final sessionService = SessionService.instance;
      final namaSitus = await sessionService.getFromSession('nama_situs');
      print('Nama situs: $namaSitus');
      if (namaSitus != null && namaSitus is String && namaSitus.isNotEmpty) {
        if (mounted) {
          setState(() {
            _title = namaSitus;
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading title from session: $e');
    }
  }

  Future<void> _loadUserPhoto() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? photoUrl = prefs.getString('user_photo_url');
      if (photoUrl != null && photoUrl.isNotEmpty && mounted) {
        setState(() {
          _avatarUrl = photoUrl;
        });
      }
    } catch (e) {
      // Keep default avatar if there's an error
      debugPrint('Error loading user photo: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFF1C3FAA),
            Color(0xFF3558D7),
          ],
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: Row(
            children: [
              Icon(widget.iconic, color: Colors.white, size: 26),

              const SizedBox(width: 10),

              // title
              Expanded(
                child: Text(
                  _title ?? widget.title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                  ),
                  overflow: TextOverflow.ellipsis,
                  maxLines: 1,
                ),
              ),

              // notif
              Stack(
                children: [
                  IconButton(
                    icon: const Icon(Icons.notifications_none,
                        color: Colors.white),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const NotificationsPage(),
                        ),
                      );
                    },
                  ),
                  if (widget.notificationCount > 0)
                    Positioned(
                      right: 6,
                      top: 6,
                      child: Container(
                        padding: const EdgeInsets.all(2),
                        decoration: BoxDecoration(
                          color: Colors.red.shade600,
                          shape: BoxShape.circle,
                        ),
                        child: Text(
                          "${widget.notificationCount}",
                          style: const TextStyle(
                              fontSize: 10,
                              color: Colors.white,
                              fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                ],
              ),

              const SizedBox(width: 8),

              // avatar
              CircleAvatar(
                radius: 18,
                backgroundImage: _avatarUrl.startsWith('http')
                    ? NetworkImage(_avatarUrl)
                    : AssetImage(_avatarUrl) as ImageProvider,
              )
            ],
          ),
        ),
      ),
    );
  }
}
