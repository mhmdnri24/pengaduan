import 'package:flutter/material.dart';
import '../components/sidebar.dart';
import '../components/navbar.dart';
import '../components/dashboard_card.dart';
import '../components/stat_card.dart';
import '../components/chart_widget.dart';
import '../components/icon_tile.dart';
import '../components/banner.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  int selectedIndex = 0;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >= 800;

    return Scaffold(
      key: _scaffoldKey,
      appBar:   const Navbar(
  notificationCount: 3, // contoh badge notifikasi
  avatarUrl: "assets/images/profile.jpeg", // ganti sesuai path gambar
),
      drawer: isDesktop
          ? null
          : Sidebar(
              onItemSelected: (i) => setState(() => selectedIndex = i),
              selectedIndex: selectedIndex),
      body: Row(
        children: [
          if (isDesktop)
            SizedBox(
              width: 250,
              child: Sidebar(
                  onItemSelected: (i) => setState(() => selectedIndex = i),
                  selectedIndex: selectedIndex),
            ),
          Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 🔵 Header biru sampai search bar
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF1C3FAA), Color(0xFF2D62F2)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(6),
                        bottomRight: Radius.circular(6),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const BannerCard(name: "Edy Tama Kusumajaya"),
                        const SizedBox(height: 16),
                        TextField(
                          decoration: InputDecoration(
                            hintText: "Cari layanan atau informasi...",
                            prefixIcon: const Icon(Icons.search),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide.none,
                            ),
                            filled: true,
                            fillColor: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Purple info card
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Column(
                      children: [
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF6A11CB), Color(0xFF9546FF)],
                            ),
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.08),
                                blurRadius: 8,
                                offset: const Offset(0, 4),
                              )
                            ],
                          ),
                          child: const Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Layanan Online 24/7',
                                  style: TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold)),
                              SizedBox(height: 8),
                              Text(
                                  'Akses semua layanan kapan saja, dimana saja',
                                  style: TextStyle(color: Colors.white70)),
                              SizedBox(height: 12),
                              Row(children: [
                                Icon(Icons.circle,
                                    size: 10, color: Colors.lightBlue),
                                SizedBox(width: 8),
                                Text('ONLINE',
                                    style: TextStyle(color: Colors.white))
                              ]),
                            ],
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                                width: 8,
                                height: 8,
                                decoration: const BoxDecoration(
                                    color: Colors.white,
                                    shape: BoxShape.circle)),
                            const SizedBox(width: 8),
                            Container(
                                width: 8,
                                height: 8,
                                decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.5),
                                    shape: BoxShape.circle)),
                            const SizedBox(width: 8),
                            Container(
                                width: 8,
                                height: 8,
                                decoration: BoxDecoration(
                                    color: Colors.white.withValues(alpha: 0.5),
                                    shape: BoxShape.circle)),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Grid icons
                  Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: GridView.count(
                      crossAxisCount: isDesktop ? 6 : 4,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      children: const [
                        IconTile(
                            icon: Icons.report_problem,
                            label: 'Darurat',
                            color: Color(0xFFE86A6A)),
                        IconTile(
                            icon: Icons.note_add,
                            label: 'Pengaduan',
                            color: Color(0xFF5EA3FF)),
                        IconTile(
                            icon: Icons.article,
                            label: 'Berita',
                            color: Color(0xFF6EE7B7)),
                        IconTile(
                            icon: Icons.qr_code_scanner,
                            label: 'Scan JSS',
                            color: Color(0xFFF7A94B)),
                        IconTile(
                            icon: Icons.group,
                            label: 'Layanan',
                            color: Color(0xFF9B8CFF)),
                        IconTile(
                            icon: Icons.person,
                            label: 'Profile',
                            color: Color(0xFF66C5FF)),
                      ],
                    ),
                  ),

                  const SizedBox(height: 80),
                ],
              ),
            ),
          )
        ],
      ),
      bottomNavigationBar: const BottomAppBar(
        shape: CircularNotchedRectangle(),
        child: SizedBox(
          height: 64,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              Icon(Icons.home),
              Icon(Icons.history),
              SizedBox(width: 48),
              Icon(Icons.grid_view),
              Icon(Icons.person),
            ],
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      floatingActionButton: FloatingActionButton(
        onPressed: () {},
        child: const Icon(Icons.add),
      ),
    );
  }
}
