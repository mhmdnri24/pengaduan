import 'package:flutter/material.dart';
import '../services/complaint_service.dart';

class AddComplaintPage extends StatelessWidget {
  const AddComplaintPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tambah Pengaduan'),
        backgroundColor: const Color(0xFF1C3FAA),
        foregroundColor: Colors.white,
      ),
      body: Center(
        child: ElevatedButton.icon(
          onPressed: () async {
            final messenger = ScaffoldMessenger.of(context);
            await ComplaintService.instance.simulateNewComplaint();
            messenger.showSnackBar(
              const SnackBar(
                content: Text('Pengaduan baru disimulasikan! Periksa bubble overlay.'),
                duration: Duration(seconds: 2),
              ),
            );
          },
          icon: const Icon(Icons.add),
          label: const Text('Simulasi Pengaduan Baru'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF1C3FAA),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          ),
        ),
      ),
    );
  }
}