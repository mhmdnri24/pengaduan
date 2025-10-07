import 'package:flutter/material.dart';
import '../components/icon_grid.dart';

class ServicesPage extends StatelessWidget {
  const ServicesPage({super.key});

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >= 800.0;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Layanan'),
        backgroundColor: const Color(0xFF1C3FAA),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            IconGrid(isDesktop: isDesktop),
          ],
        ),
      ),
    );
  }
}