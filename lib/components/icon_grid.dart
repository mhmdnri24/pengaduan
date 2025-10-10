import 'package:flutter/material.dart';
import 'icon_tile.dart';

class IconGrid extends StatelessWidget {
  final bool isDesktop;
  const IconGrid({Key? key, required this.isDesktop}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final crossAxisCount = isDesktop ? 6 : 4;
    const horizontalOuterPadding = 32.0;
    final screenWidth = MediaQuery.of(context).size.width;
    final totalSpacing = (crossAxisCount - 1) * 12;
    final availableWidth = screenWidth - horizontalOuterPadding - totalSpacing;
    final itemWidth = availableWidth / crossAxisCount;

    final desiredItemHeight = 120.0;
    final childAspectRatio = itemWidth / desiredItemHeight;

    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: GridView.count(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
        childAspectRatio: childAspectRatio,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        children: const [
          IconTile(
            icon: Icons.report_problem,
            label: 'Darurat',
            gradient: LinearGradient(
              colors: [Color(0xFFE53935), Color(0xFFD32F2F)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            accentColor: Colors.red,
          ),
          IconTile(
            icon: Icons.send,
            label: 'Pengaduan',
            gradient: LinearGradient(
              colors: [Color(0xFF42A5F5), Color(0xFF1E88E5)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            accentColor: Colors.blue,
          ),
          IconTile(
            icon: Icons.article,
            label: 'Berita',
            gradient: LinearGradient(
              colors: [Color(0xFF26C6DA), Color(0xFF00ACC1)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            accentColor: Colors.teal,
          ),
          IconTile(
            icon: Icons.qr_code,
            label: 'H.Komoditas',
            gradient: LinearGradient(
              colors: [Color(0xFFFFA726), Color(0xFFFB8C00)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            accentColor: Colors.orange,
          ),
          IconTile(
            icon: Icons.group,
            label: 'Fas.Umum',
            gradient: LinearGradient(
              colors: [Color(0xFF66BB6A), Color(0xFF43A047)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            accentColor: Colors.green,
          ),
        ],
      ),
    );
  }
}
