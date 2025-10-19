import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../pages/notifications_page.dart';

class Navbar extends StatefulWidget implements PreferredSizeWidget {
  final int notificationCount;
  final String title;
  final IconData iconic;

  const Navbar({
    super.key,
    this.notificationCount = 0,
    this.title = "Lapor Pak Wali",
    this.iconic = Icons.account_balance,
  });

  @override
  State<Navbar> createState() => _NavbarState();

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);
}

class _NavbarState extends State<Navbar> {
  String _avatarUrl = "assets/images/profile.jpeg"; // Default fallback

  @override
  void initState() {
    super.initState();
    _loadUserPhoto();
  }

  Future<void> _loadUserPhoto() async {
    try {
      SharedPreferences prefs = await SharedPreferences.getInstance();
      String? photoUrl = prefs.getString('user_photo_url');
      if (photoUrl != null && photoUrl.isNotEmpty) {
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
    return AppBar(
      elevation: 0,
        backgroundColor: const Color(0xFF1C3FAA), // Dominant blue color
        foregroundColor: Colors.white,

      // ❌ leading dihapus, jadi icon garis 3 tidak muncul
      automaticallyImplyLeading: false,

      title: Row(
        children: [
          Icon(widget.iconic, color: Colors.white),
          const SizedBox(width: 8),
          Text(
            widget.title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w600,
              fontSize: 18,
            ),
          ),
        ],
      ),
      actions: [
        if (widget.notificationCount > 0)
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications, color: Colors.white),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => const NotificationsPage(),
                    ),
                  );
                },
                style: IconButton.styleFrom(
                  backgroundColor: Colors.white.withOpacity(0.2),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
              Positioned(
                right: 8,
                top: 8,
                child: Container(
                  padding: const EdgeInsets.all(2),
                  decoration: const BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                  ),
                  constraints: const BoxConstraints(
                    minWidth: 16,
                    minHeight: 16,
                  ),
                  child: Text(
                    "${widget.notificationCount}",
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ),
            ],
          ),
        const SizedBox(width: 8),
        Padding(
          padding: const EdgeInsets.only(right: 12),
          child: CircleAvatar(
            radius: 16,
            backgroundImage: _avatarUrl.startsWith('http') 
                ? NetworkImage(_avatarUrl)
                : AssetImage(_avatarUrl) as ImageProvider,
            onBackgroundImageError: (exception, stackTrace) {
              // Fallback to default asset if network image fails
            },
            child: _avatarUrl.startsWith('http') && _avatarUrl.isEmpty
                ? const Icon(Icons.person, color: Colors.white)
                : null,
          ),
        ),
      ],
    );
  }
}
