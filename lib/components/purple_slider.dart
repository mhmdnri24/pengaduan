import 'dart:async';
import 'package:flutter/material.dart';

class PurpleSlider extends StatefulWidget {
  final int itemCount;
  final Duration autoPlayInterval;
  const PurpleSlider({Key? key, this.itemCount = 3, this.autoPlayInterval = const Duration(seconds: 4)}) : super(key: key);

  @override
  State<PurpleSlider> createState() => _PurpleSliderState();
}

class _PurpleSliderState extends State<PurpleSlider> {
  late final PageController _controller;
  int _page = 0;
  Timer? _autoTimer;

  @override
  void initState() {
    super.initState();
    _controller = PageController(viewportFraction: 0.98);
    _controller.addListener(() {
      final p = _controller.page?.round() ?? 0;
      if (p != _page) setState(() => _page = p);
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _autoTimer = Timer.periodic(widget.autoPlayInterval, (_) {
        if (!mounted) return;
        final next = (_page + 1) % widget.itemCount;
        if (_controller.hasClients) {
          _controller.animateToPage(next, duration: const Duration(milliseconds: 400), curve: Curves.easeInOut);
        }
      });
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _autoTimer?.cancel();
    super.dispose();
  }
Widget _buildSlide(BuildContext context, int index) {
  final themes = [
    BoxDecoration(
      gradient: const LinearGradient(colors: [Color(0xFF1C3FAA), Color(0xFF2563EB)]), // biru gradasi
      borderRadius: BorderRadius.circular(12),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, 4))],
    ),
    BoxDecoration(
      gradient: const LinearGradient(colors: [Color(0xFF4E8DF5), Color(0xFF8A2BE2)]),
      borderRadius: BorderRadius.circular(12),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6, offset: const Offset(0, 4))],
    ),
    BoxDecoration(
      gradient: const LinearGradient(colors: [Color(0xFF2BD6C5), Color(0xFF4E9AF5)]),
      borderRadius: BorderRadius.circular(12),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 6, offset: const Offset(0, 4))],
    ),
  ];

  final contents = [
    // 🔵 Layanan Online 24/7
    const Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Text(
                'Layanan Online 24/7',
                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            Icon(Icons.computer, color: Colors.white, size: 28),
          ],
        ),
        SizedBox(height: 8),
        Text(
          'Akses semua layanan kapan saja, dimana saja',
          style: TextStyle(color: Colors.white70),
        ),
        Spacer(),
        Row(
          children: [
            Icon(Icons.circle, size: 10, color: Colors.greenAccent),
            SizedBox(width: 6),
            Text('ONLINE', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ],
        ),
      ],
    ),

    // 🟣 Pengumuman
    const Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Text(
                'Layanan Online 24/7',
                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            Icon(Icons.computer, color: Colors.white, size: 28),
          ],
        ),
        SizedBox(height: 8),
        Text(
          'Akses semua layanan kapan saja, dimana saja',
          style: TextStyle(color: Colors.white70),
        ),
        Spacer(),
        Row(
          children: [
            Icon(Icons.circle, size: 10, color: Colors.greenAccent),
            SizedBox(width: 6),
            Text('ONLINE', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ],
        ),
      ],
    ),

    // 🟢 Tips & Bantuan
   const Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Text(
                'Layanan Online 24/7',
                style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
            Icon(Icons.computer, color: Colors.white, size: 28),
          ],
        ),
        SizedBox(height: 8),
        Text(
          'Akses semua layanan kapan saja, dimana saja',
          style: TextStyle(color: Colors.white70),
        ),
        Spacer(),
        Row(
          children: [
            Icon(Icons.circle, size: 10, color: Colors.greenAccent),
            SizedBox(width: 6),
            Text('ONLINE', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ],
        ),
      ],
    ),
  ];

  final idx = index % themes.length;
  return Container(
    margin: const EdgeInsets.only(right: 8),
    padding: const EdgeInsets.all(16),
    decoration: themes[idx],
    child: contents[idx],
  );
}


  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        SizedBox(
          height: 140,
          child: PageView.builder(
            controller: _controller,
            itemCount: widget.itemCount,
            itemBuilder: _buildSlide,
          ),
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(widget.itemCount, (i) {
            final active = i == _page;
            return GestureDetector(
              onTap: () => _controller.animateToPage(i, duration: const Duration(milliseconds: 300), curve: Curves.easeInOut),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                margin: const EdgeInsets.symmetric(horizontal: 6),
                width: active ? 14 : 8,
                height: active ? 14 : 8,
                decoration: BoxDecoration(color: active ? Colors.blueAccent : Colors.blueAccent.withOpacity(0.35), shape: BoxShape.circle),
              ),
            );
          }),
        ),
      ],
    );
  }
}
