import 'package:flutter/material.dart';

class Navbar extends StatelessWidget implements PreferredSizeWidget {
  final int notificationCount;
  final String avatarUrl;

  const Navbar({
    super.key,
    this.notificationCount = 0,
    this.avatarUrl = "assets/images/profile.jpeg",
  });

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight);

  @override
  Widget build(BuildContext context) {
    return AppBar(
      backgroundColor: const Color(0xFF1C3FAA),
      elevation: 0,

      // ❌ leading dihapus, jadi icon garis 3 tidak muncul
      automaticallyImplyLeading: false,

      title: const Row(
        children: [
          Icon(Icons.account_balance, color: Colors.white),
          SizedBox(width: 8),
          Text(
            "Lapor Pak Wali",
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
      actions: [
        if (notificationCount > 0)
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications, color: Colors.white),
                onPressed: () {},
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
                    "$notificationCount",
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
            backgroundImage: AssetImage(avatarUrl),
          ),
        ),
      ],
    );
  }
}
