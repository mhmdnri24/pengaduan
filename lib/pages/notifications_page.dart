import 'package:flutter/material.dart';

class NotificationsPage extends StatefulWidget {
  const NotificationsPage({super.key});

  @override
  State<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends State<NotificationsPage> {
  int selectedTab = 0;
  final List<String> tabs = ['Semua', 'Belum Dibaca', 'Penting'];

  // Sample notification data
  final List<Map<String, dynamic>> notifications = [
    {
      'id': 1,
      'title': 'SINYAL DARURAT AKTIF!',
      'description': 'Keadaan darurat: Medis di Jl. Sudirman No. 45',
      'time': '5 menit lalu',
      'type': 'emergency',
      'isUnread': true,
      'isImportant': true,
    },
    {
      'id': 2,
      'title': 'Pengaduan Selesai',
      'description': 'Pengaduan #LPW-2024-003 tentang "Saluran Air Tersumbat" telah diselesaikan',
      'time': '2 jam lalu',
      'type': 'completed',
      'isUnread': true,
      'isImportant': false,
    },
    {
      'id': 3,
      'title': 'Update Pengaduan',
      'description': 'Pengaduan #LPW-2024-001 sedang dalam proses perbaikan',
      'time': '4 jam lalu',
      'type': 'update',
      'isUnread': true,
      'isImportant': false,
    },
    {
      'id': 4,
      'title': 'Berita Terbaru',
      'description': 'Program Bantuan Sosial Diperluas - Pemerintah memperluas cakupan bantuan',
      'time': '6 jam lalu',
      'type': 'news',
      'isUnread': true,
      'isImportant': false,
    },
    {
      'id': 5,
      'title': 'Pengaduan Diterima',
      'description': 'Pengaduan baru #LPW-2024-004 tentang "Lampu Jalan Mati" telah diterima',
      'time': '1 hari lalu',
      'type': 'received',
      'isUnread': true,
      'isImportant': false,
    },
    {
      'id': 6,
      'title': 'Maintenance Sistem',
      'description': 'Sistem akan mengalami maintenance pada 15 Januari 2024 pukul 02:00-04:00 WIB',
      'time': '1 hari lalu',
      'type': 'maintenance',
      'isUnread': true,
      'isImportant': true,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Column(
        children: [
          _buildHeader(),
          _buildTabBar(),
          Expanded(
            child: _buildNotificationList(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // Refresh notifications
          setState(() {});
        },
        backgroundColor: const Color(0xFF1C3FAA),
        child: const Icon(Icons.refresh, color: Colors.white),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF1C3FAA), Color(0xFF2D62F2)],
        ),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.arrow_back, color: Colors.white),
                    style: IconButton.styleFrom(
                      backgroundColor: Colors.white.withOpacity(0.2),
                      shape: const CircleBorder(),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.check, color: Colors.white, size: 16),
                        const SizedBox(width: 4),
                        const Text(
                          'Tandai Semua',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              const Text(
                'Notifikasi',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Informasi terkini untuk Anda',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 24),
              _buildSummaryCards(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryCards() {
    final totalCount = notifications.length;
    final unreadCount = notifications.where((n) => n['isUnread']).length;
    final todayCount = 4; // Mock data for today's count

    return Row(
      children: [
        Expanded(
          child: _buildSummaryCard('$totalCount', 'Total', Colors.white),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildSummaryCard('$unreadCount', 'Dibaca', const Color(0xFFFFD700)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _buildSummaryCard('$todayCount', 'Hari Ini', Colors.white),
        ),
      ],
    );
  }

  Widget _buildSummaryCard(String count, String label, Color countColor) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        children: [
          Text(
            count,
            style: TextStyle(
              color: countColor,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      color: Colors.grey.shade100,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        child: Row(
          children: List.generate(tabs.length, (index) {
            final isSelected = selectedTab == index;
            return Expanded(
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    selectedTab = index;
                  });
                },
                child: Container(
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  decoration: BoxDecoration(
                    color: isSelected ? Colors.white : Colors.transparent,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    tabs[index],
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: isSelected ? Colors.grey.shade800 : Colors.grey.shade600,
                      fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
                      fontSize: 14,
                    ),
                  ),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }

  Widget _buildNotificationList() {
    return ListView.builder(
      padding: const EdgeInsets.all(20),
      itemCount: notifications.length,
      itemBuilder: (context, index) {
        final notification = notifications[index];
        return _buildNotificationCard(notification);
      },
    );
  }

  Widget _buildNotificationCard(Map<String, dynamic> notification) {
    final type = notification['type'] as String;
    final isUnread = notification['isUnread'] as bool;
    final isImportant = notification['isImportant'] as bool;

    Color backgroundColor;
    Color iconColor;
    IconData iconData;

    switch (type) {
      case 'emergency':
        backgroundColor = Colors.red.shade50;
        iconColor = Colors.red;
        iconData = Icons.report_problem;
        break;
      case 'completed':
        backgroundColor = Colors.green.shade50;
        iconColor = Colors.green;
        iconData = Icons.check_circle;
        break;
      case 'update':
      case 'news':
      case 'received':
      case 'maintenance':
      default:
        backgroundColor = Colors.blue.shade50;
        iconColor = Colors.blue;
        iconData = Icons.info;
        break;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: iconColor,
              shape: BoxShape.circle,
            ),
            child: Icon(
              iconData,
              color: Colors.white,
              size: 20,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        notification['title'],
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                          color: iconColor,
                        ),
                      ),
                    ),
                    if (isImportant)
                      const Icon(
                        Icons.star,
                        color: Color(0xFFFFD700),
                        size: 16,
                      ),
                  ],
                ),
                const SizedBox(height: 4),
                Text(
                  notification['description'],
                  style: const TextStyle(
                    fontSize: 12,
                    color: Colors.black87,
                    height: 1.3,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                notification['time'],
                style: TextStyle(
                  fontSize: 10,
                  color: Colors.grey.shade600,
                ),
              ),
              const SizedBox(height: 8),
              if (isUnread)
                Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: Color(0xFF1C3FAA),
                    shape: BoxShape.circle,
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
