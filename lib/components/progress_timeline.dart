import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class ProgressTimeline extends StatelessWidget {
  final List<Map<String, dynamic>> history;
  final bool isLoading;
  final String? error;

  const ProgressTimeline({
    super.key,
    required this.history,
    this.isLoading = false,
    this.error,
  });

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (error != null) {
      return Card(
        color: Colors.red.shade50,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text(
            'Error loading progress: $error',
            style: GoogleFonts.poppins(color: Colors.red.shade700),
          ),
        ),
      );
    }

    if (history.isEmpty) {
      return Card(
        color: Colors.grey.shade50,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text(
            'Belum ada progress penanganan',
            style: GoogleFonts.poppins(color: Colors.grey.shade600),
          ),
        ),
      );
    }

    return Card(
      color: Colors.white,
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(
                  Icons.history,
                  color: Colors.blue.shade600,
                  size: 20,
                ),
                const SizedBox(width: 8),
                Text(
                  'Timeline',
                  style: GoogleFonts.poppins(
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            ...history.asMap().entries.map((entry) {
              final index = entry.key;
              final item = entry.value;
              final isLast = index == history.length - 1;
              
              return _buildTimelineItem(
                item: item,
                isLast: isLast,
                isCompleted: true, // All history items are completed
              );
            }),
          ],
        ),
      ),
    );
  }

  Widget _buildTimelineItem({
    required Map<String, dynamic> item,
    required bool isLast,
    required bool isCompleted,
  }) {
    final keterangan = item['keterangan']?.toString() ?? '';
    final statusBaru = item['status_baru']?.toString() ?? '';
    final updatedBy = item['updated_by']?.toString() ?? '';
    final updatedAt = item['updated_at_formatted']?.toString() ?? '';
    
    // Map status to appropriate icons and colors
    final statusInfo = _getStatusInfo(statusBaru);
    
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Timeline indicator
        Column(
          children: [
            Container(
              width: 24,
              height: 24,
              decoration: BoxDecoration(
                color: isCompleted ? statusInfo['color'] : Colors.grey.shade300,
                shape: BoxShape.circle,
              ),
              child: Icon(
                statusInfo['icon'],
                color: Colors.white,
                size: 14,
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 40,
                color: isCompleted ? statusInfo['color'] : Colors.grey.shade300,
              ),
          ],
        ),
        const SizedBox(width: 12),
        // Content
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                statusBaru,
                style: GoogleFonts.poppins(
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                  color: Colors.black87,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                keterangan,
                style: GoogleFonts.poppins(
                  fontSize: 12,
                  color: Colors.black54,
                ),
              ),
              const SizedBox(height: 4),
              Row(
                children: [
                  if (updatedBy.isNotEmpty) ...[
                    Text(
                      updatedBy,
                      style: GoogleFonts.poppins(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      '•',
                      style: GoogleFonts.poppins(
                        fontSize: 11,
                        color: Colors.grey.shade400,
                      ),
                    ),
                    const SizedBox(width: 8),
                  ],
                  Text(
                    updatedAt,
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      color: Colors.grey.shade600,
                    ),
                  ),
                ],
              ),
              if (!isLast) const SizedBox(height: 16),
            ],
          ),
        ),
      ],
    );
  }

  Map<String, dynamic> _getStatusInfo(String status) {
    switch (status.toUpperCase()) {
      case 'DITERIMA':
        return {
          'icon': Icons.check,
          'color': Colors.green,
        };
      case 'VERIFIKASI':
      case 'DIVERIFIKASI':
        return {
          'icon': Icons.visibility,
          'color': Colors.green,
        };
      case 'DALAM PERBAIKAN':
      case 'PERBAIKAN':
        return {
          'icon': Icons.build,
          'color': Colors.blue,
        };
      case 'SELESAI':
      case 'COMPLETED':
        return {
          'icon': Icons.flag,
          'color': Colors.green,
        };
      case 'LAPOR':
      case 'LAPORAN':
        return {
          'icon': Icons.report,
          'color': Colors.orange,
        };
      default:
        return {
          'icon': Icons.circle,
          'color': Colors.grey,
        };
    }
  }
}
