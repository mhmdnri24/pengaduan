// removed unused imports after slider extraction
import 'package:flutter/material.dart';
import '../components/sidebar.dart';
import '../components/navbar.dart';
// removed unused component imports
import '../components/icon_grid.dart';
import '../components/banner.dart';
import '../components/purple_slider.dart';

// Constants for better maintainability
class DashboardConstants {
  static const Color primaryColor = Color(0xFF1C3FAA);
  static const Color secondaryColor = Color(0xFF2D62F2);
  static const double desktopBreakpoint = 800.0;
  static const double sidebarWidth = 250.0;
  static const double bottomNavHeight = 64.0;
  static const double fabSize = 32.0;
  static const double borderRadius = 6.0;
  static const double searchBorderRadius = 12.0;
  static const int fabIndex = 4;
  static const int notificationCount = 3;
  static const String avatarUrl = "assets/images/profile.jpeg";
  static const String userName = "Edy Tama Kusumajaya";
  static const String searchHint = "Cari layanan atau informasi...";
}

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  int _selectedIndex = 0;

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    super.dispose();
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >= DashboardConstants.desktopBreakpoint;

    return Scaffold(
      key: _scaffoldKey,
      appBar: const Navbar(
        notificationCount: DashboardConstants.notificationCount,
        avatarUrl: DashboardConstants.avatarUrl,
      ),
      drawer: isDesktop
          ? null
          : Sidebar(
              onItemSelected: (i) => setState(() => _selectedIndex = i),
              selectedIndex: _selectedIndex),
      body: Row(
        children: [
          if (isDesktop)
            SizedBox(
              width: DashboardConstants.sidebarWidth,
              child: Sidebar(
                onItemSelected: (i) => setState(() => _selectedIndex = i),
                selectedIndex: _selectedIndex,
              ),
            ),
          Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // 🔵 Header biru sampai search bar
                  SizedBox(
                    width: double.infinity,
                    child: DecoratedBox(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [DashboardConstants.primaryColor, DashboardConstants.secondaryColor],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.only(
                          bottomLeft: Radius.circular(DashboardConstants.borderRadius),
                          bottomRight: Radius.circular(DashboardConstants.borderRadius),
                        ),
                      ),
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const SizedBox(height: 10),
                            const BannerCard(name: DashboardConstants.userName),
                            const SizedBox(height: 25),
                            TextField(
                              decoration: InputDecoration(
                                hintText: DashboardConstants.searchHint,
                                prefixIcon: const Icon(Icons.search),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(DashboardConstants.searchBorderRadius),
                                  borderSide: BorderSide.none,
                                ),
                                filled: true,
                                fillColor: Colors.white,
                                contentPadding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
                              ),
                            ),
                            const SizedBox(height: 20),
                          ],
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Purple info card replaced by reusable PurpleSlider component
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16.0),
                    child: PurpleSlider(itemCount: 3),
                  ),

                  const SizedBox(height: 12),

                  // Grid icons (extracted to IconGrid)
                  IconGrid(isDesktop: isDesktop),

                  const SizedBox(height: 80),
                ],
              ),
            ),
          )
        ],
      ),
      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: DashboardConstants.borderRadius,
        child: SizedBox(
          height: DashboardConstants.bottomNavHeight,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildTabItem(Icons.home, "Home", 0),
              _buildTabItem(Icons.history, "Histori", 1),
              const SizedBox(width: 48), // ruang untuk FAB
              _buildTabItem(Icons.grid_view, "Layanan", 2),
              _buildTabItem(Icons.person, "Profile", 3),
            ],
          ),
        ),
      ),
      floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
      floatingActionButton: FloatingActionButton(
        onPressed: () => _onItemTapped(DashboardConstants.fabIndex),
        backgroundColor: DashboardConstants.primaryColor,
        child: Icon(Icons.add, size: DashboardConstants.fabSize, color: Colors.white),
      ),
    );
  }


  
  Widget _buildTabItem(IconData icon, String label, int index) {
    final isSelected = _selectedIndex == index;
    final color = isSelected ? DashboardConstants.primaryColor : Colors.grey;

    return InkWell(
      onTap: () => _onItemTapped(index),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: color,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            ),
          ),
        ],
      ),
    );
  }
}
