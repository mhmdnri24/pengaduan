// removed unused imports after slider extraction
import 'package:flutter/material.dart';
import 'package:pengaduan/components/pengaduan_list.dart';
import 'package:pengaduan/pages/add_complaint_page.dart';
import 'package:pengaduan/pages/profile_page.dart';
import '../components/sidebar.dart';
import '../components/navbar.dart';
// removed unused component imports
import '../components/icon_grid.dart';
import '../components/banner.dart';
import '../components/purple_slider.dart';
// import '../services/complaint_service.dart';
import 'history_page.dart';
import 'package:pengaduan/services/session_service.dart';
import 'notifications_page.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
  static const String backIcon = "assets/images/back-arrow.png";
  static const String userName = "Pengguna";
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
  String? _userName;

  @override
  void initState() {
    super.initState();
    _loadUserNameFromSession();
  }

  @override
  void dispose() {
    super.dispose();
  }

  Future<void> _loadUserNameFromSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Try multiple sources and keys to be robust across flows
      String? nameFromPrefs = prefs.getString('user_name');
      nameFromPrefs ??= prefs.getString('nama_lengkap');

      final dynamic nameFromSession =
          await SessionService.instance.getFromSession('user_name');
      String? name = (nameFromSession is String && nameFromSession.isNotEmpty)
          ? nameFromSession
          : nameFromPrefs;

      if (name == null || name.isEmpty) {
        name = DashboardConstants.userName;
      }

      // Debug log to help trace where name was loaded from
      debugPrint(
          'Dashboard: nameFromPrefs=${prefs.getString('user_name')}, nameFromSession=$nameFromSession, resolvedName=$name');
      if (!mounted) return;
      setState(() {
        _userName = name;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _userName = DashboardConstants.userName;
      });
    }
  }

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  String _getTitle() {
    switch (_selectedIndex) {
      case 0:
        return "Lapor Pak Wali";
      case 1:
        return "Histori";
      case 2:
        return "Notif";
      case 3:
        return "Profile";
      case 4:
        return "Pengaduan";
      default:
        return "Lapor Pak Wali";
    }
  }

  IconData _getIcon() {
    switch (_selectedIndex) {
      case 0:
        return Icons.account_balance;
      case 1:
        return Icons.history;
      case 2:
        return Icons.notifications;
      case 3:
        return Icons.person;
      case 4:
        return Icons.report_problem;
      default:
        return Icons.account_balance;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >=
        DashboardConstants.desktopBreakpoint;

    return WillPopScope(
      onWillPop: () async {
        if (_selectedIndex != 0) {
          setState(() {
            _selectedIndex = 0;
          });
          return false;
        }
        return true;
      },
      child: Scaffold(
        key: _scaffoldKey,
        resizeToAvoidBottomInset: false,
        appBar: Navbar(
          title: _getTitle(),
          notificationCount: DashboardConstants.notificationCount,
          iconic: _getIcon(),
        ),
        drawer: isDesktop
            ? null
            : Sidebar(
                onItemSelected: (i) => setState(() => _selectedIndex = i),
                selectedIndex: _selectedIndex,
              ),
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
              child: _getBodyContent(isDesktop),
            )
          ],
        ),
        bottomNavigationBar: BottomAppBar(
          color: DashboardConstants.primaryColor,
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
                _buildTabItem(Icons.notifications, "Notif", 2),
                _buildTabItem(Icons.person, "Profile", 3),
              ],
            ),
          ),
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        floatingActionButton: FloatingActionButton(
          onPressed: () => _onItemTapped(DashboardConstants.fabIndex),
          backgroundColor: DashboardConstants.primaryColor,
          shape: const CircleBorder(),
          child: Icon(Icons.add,
              size: DashboardConstants.fabSize, color: Colors.white),
        ),
      ),
    );
  }

  Widget _buildTabItem(IconData icon, String label, int index) {
    final isSelected = _selectedIndex == index;
    final color = isSelected ? Colors.white : Colors.white70;

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

  Widget _getBodyContent(bool isDesktop) {
    switch (_selectedIndex) {
      case 0:
        return _buildHomeContent(isDesktop);
      case 1:
        return _buildHistoryContent();
      case 2:
        return _buildServicesContent();
      case 3:
        return _buildProfileContent();
      case 4:
        return _buildAddComplaintContent();
      default:
        return _buildHomeContent(isDesktop);
    }
  }

  Widget _buildHomeContent(bool isDesktop) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 🔵 Header biru sampai search bar
          SizedBox(
            width: double.infinity,
            child: DecoratedBox(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    DashboardConstants.primaryColor,
                    DashboardConstants.secondaryColor
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(DashboardConstants.borderRadius),
                  bottomRight: Radius.circular(DashboardConstants.borderRadius),
                ),
              ),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 10),
                    BannerCard(name: _userName ?? DashboardConstants.userName),
                    // const SizedBox(height: 25),
                    // TextField(
                    //   decoration: InputDecoration(
                    //     hintText: DashboardConstants.searchHint,
                    //     prefixIcon: const Icon(Icons.search),
                    //     border: OutlineInputBorder(
                    //       borderRadius: BorderRadius.circular(
                    //           DashboardConstants.searchBorderRadius),
                    //       borderSide: BorderSide.none,
                    //     ),
                    //     filled: true,
                    //     fillColor: Colors.white,
                    //     contentPadding: const EdgeInsets.symmetric(
                    //         vertical: 10, horizontal: 12),
                    //   ),
                    // ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(height: 24),

          // Purple info card replaced by reusable PurpleSlider component
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.0),
            child: PurpleSlider(),
          ),

          const SizedBox(height: 12),

          // Grid icons (extracted to IconGrid)
          IconGrid(isDesktop: isDesktop),

          Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Column(
                children: [
                  const SizedBox(height: 10),
                  HeaderPengaduan(
                    title: "Pengaduan Terbaru",
                    subtitle: "Pantau aduan masyarakat terbaru",
                    onViewAll: () => setState(() => _selectedIndex = 1),
                  ),
                  const SizedBox(height: 10),
                  const PengaduanList(),
                ],
              )),
          // const Padding(
          //     padding: EdgeInsets.symmetric(horizontal: 16.0),
          //     child: Column(
          //       children: [
          //         SizedBox(height: 10),
          //         HeaderPengaduan(
          //           title: "Berita Terbaru",
          //           subtitle: "Informasi dan berita terkini dari pemerintah",
          //         ),
          //         SizedBox(height: 10),
          //         PengaduanList(),
          //       ],
          //     )),
        ],
      ),
    );
  }

  Widget _buildHistoryContent() {
    return const SingleChildScrollView(
      child: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          children: [
            HistoryPage(),
          ],
        ),
      ),
    );
  }

  Widget _buildServicesContent() {
    return SingleChildScrollView(
      child: Column(
        children: [
          NotificationsPage(),
        ],
      ),
    );
  }

  Widget _buildProfileContent() {
    return Center(child: ProfilePage());
  }

  Widget _buildAddComplaintContent() {
    return Center(
      child: AddComplaintPage(showAppBar: false),
    );
  }
}
