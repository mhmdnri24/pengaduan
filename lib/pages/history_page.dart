import 'package:flutter/material.dart';
// import '../components/pengaduan_list.dart';

class HistoryPage extends StatefulWidget {
  const HistoryPage({super.key});

  @override
  State<HistoryPage> createState() => _HistoryPageState();
}

class _HistoryPageState extends State<HistoryPage> {
  int selectedTab = 0;

  final List<String> tabs = ['Semua', 'Baru', 'Proses', 'Selesai'];

  @override
  Widget build(BuildContext context) {
    // This page is intended to be used as a child of an existing Scaffold
    // (e.g. inside a bottom tab). Do not provide another AppBar or FAB here.
    return Padding(
      padding: const EdgeInsets.all(1.0),
      child: LayoutBuilder(
        builder: (context, constraints) {
          final children = _buildChildren();
          final minHeight = constraints.maxHeight.isFinite
              ? constraints.maxHeight
              : MediaQuery.of(context).size.height;
          return SingleChildScrollView(
            child: ConstrainedBox(
              constraints: BoxConstraints(minHeight: minHeight),
              child: Column(mainAxisSize: MainAxisSize.min, children: children),
            ),
          );
        },
      ),
    );
  }

  List<Widget> _buildChildren() {
    return [
      Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          _summaryCard('12', 'Total', Colors.blue),
          _summaryCard('3', 'Proses', Colors.orange),
          _summaryCard('9', 'Selesai', Colors.green),
        ],
      ),
      const SizedBox(height: 16),
      _tabBar(),
      const SizedBox(height: 16),
      _complaintCard(
        statusChips: [
          _chip('BARU', Colors.pink),
          _chip('TINGGI', Colors.orangeAccent),
        ],
        title: 'Jalan Rusak Parah di Jl. Sudirman',
        description:
            'Jalan berlubang sangat besar yang membahayakan pengendara motor dan mobil...',
        location: 'Jl. Sudirman No. 123',
        time: '2 jam lalu',
        verification: 'Menunggu Verifikasi',
        verificationColor: Colors.red,
        image: 'assets/images/profile.jpeg',
        detailAction: () {},
      ),
      const SizedBox(height: 12),
      _complaintCard(
        statusChips: [
          _chip('PROSES', Colors.orange),
          _chip('SEDANG', Colors.yellow),
        ],
        title: 'Lampu Jalan Mati Total',
        description: 'Lampu penerangan jalan di ...',
        location: 'Jl. Mawar No. 45',
        time: '1 jam lalu',
        verification: 'Sedang Diproses',
        verificationColor: Colors.orange,
        image: 'assets/images/profile.jpeg',
        detailAction: () {},
      ),
      // ...add more cards as needed
    ];
  }

  Widget _summaryCard(String count, String label, Color color) {
    return Container(
      width: 90,
      padding: const EdgeInsets.symmetric(vertical: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(count,
              style: TextStyle(
                  fontSize: 22, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 4),
          Text(label, style: TextStyle(color: color)),
        ],
      ),
    );
  }

  Widget _tabBar() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: List.generate(tabs.length, (index) {
        final isSelected = selectedTab == index;
        return Expanded(
          child: GestureDetector(
            onTap: () => setState(() => selectedTab = index),
            child: Container(
              margin: const EdgeInsets.symmetric(horizontal: 6),
              padding: const EdgeInsets.symmetric(vertical: 8),
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFF1C3FAA) : Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFF1C3FAA)),
              ),
              child: Center(
                child: Text(
                  tabs[index],
                  style: TextStyle(
                    color: isSelected ? Colors.white : const Color(0xFF1C3FAA),
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ),
        );
      }),
    );
  }

  Widget _chip(String label, Color color) {
    return Container(
      margin: const EdgeInsets.only(right: 6),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style:
            TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 12),
      ),
    );
  }

  Widget _complaintCard({
    required List<Widget> statusChips,
    required String title,
    required String description,
    required String location,
    required String time,
    required String verification,
    required Color verificationColor,
    required String image,
    required VoidCallback detailAction,
  }) {
    return Card(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 2,
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: statusChips),
                  const SizedBox(height: 4),
                  Text(title,
                      style: const TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 16)),
                  const SizedBox(height: 2),
                  Text(description,
                      style:
                          const TextStyle(fontSize: 13, color: Colors.black87)),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on,
                          size: 14, color: Colors.grey),
                      const SizedBox(width: 2),
                      Text(location,
                          style: const TextStyle(
                              fontSize: 12, color: Colors.grey)),
                      const SizedBox(width: 12),
                      const Icon(Icons.access_time,
                          size: 14, color: Colors.grey),
                      const SizedBox(width: 2),
                      Text(time,
                          style: const TextStyle(
                              fontSize: 12, color: Colors.grey)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Icon(Icons.circle, size: 10, color: verificationColor),
                      const SizedBox(width: 4),
                      Text(
                        verification,
                        style: TextStyle(
                            color: verificationColor,
                            fontSize: 13,
                            fontWeight: FontWeight.w600),
                      ),
                      const Spacer(),
                      GestureDetector(
                        onTap: detailAction,
                        child: const Text('Detail',
                            style: TextStyle(
                                color: Color(0xFF1C3FAA),
                                fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Image.asset(
                image,
                width: 60,
                height: 60,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) => Container(
                  width: 60,
                  height: 60,
                  color: Colors.grey[200],
                  child: const Center(
                      child:
                          Text('Foto laporan', style: TextStyle(fontSize: 10))),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
