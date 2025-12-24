// removed unused imports after slider extraction
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
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
import 'pengumuman_page.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import 'package:quickalert/quickalert.dart';
import '../services/fcm_handler.dart';

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
  static const int notificationCount = 0;
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

class _DashboardPageState extends State<DashboardPage>
    with WidgetsBindingObserver {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  int _selectedIndex = 0;
  String? _userName;
  bool _isRefreshing = false;
  int _notificationCount = 0;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadUserNameFromSession();

    // Initialize with current value
    _notificationCount = FCMHandler.notificationCount.value;

    // Listen to notification count changes
    FCMHandler.notificationCount.addListener(_updateNotificationCount);
    // Initial fetch
    FCMHandler.updateNotificationCount();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      debugPrint('App resumed, refreshing notification count...');
      FCMHandler.updateNotificationCount();
    }
  }

  void _updateNotificationCount() {
    if (mounted) {
      setState(() {
        _notificationCount = FCMHandler.notificationCount.value;
      });
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    FCMHandler.notificationCount.removeListener(_updateNotificationCount);
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

  /// Refresh all API data
  Future<void> _refreshAllData() async {
    if (_isRefreshing) return; // Prevent multiple refreshes

    setState(() {
      _isRefreshing = true;
    });

    try {
      // Show loading indicator
      if (!mounted) return;

      // Refresh pengaturan data
      await ApiService.instance.getPengaturanAndSaveToSession();

      // Refresh user profile data
      await ApiService.instance.getUserProfile();

      // Refresh notification count
      await FCMHandler.updateNotificationCount();

      // Refresh other data as needed
      // You can add more API calls here based on what needs to be refreshed

      // Show success message
      if (mounted) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.success,
          title: "Berhasil",
          text: "Data berhasil diperbarui",
          autoCloseDuration: const Duration(seconds: 2),
        );
      }
    } catch (e) {
      // Show error message
      if (mounted) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Error",
          text: "Gagal memperbarui data: $e",
          autoCloseDuration: const Duration(seconds: 3),
        );
      }
    } finally {
      // Hide loading indicator
      if (mounted) {
        setState(() {
          _isRefreshing = false;
        });
      }
    }
  }

  void _onItemTapped(int index) {
    if (!mounted) return;
    setState(() {
      _selectedIndex = index;
    });
  }

  String _getTitle() {
    switch (_selectedIndex) {
      case 0:
        return "Lapor Sang Juara";
      case 1:
        return "Histori";
      case 2:
        return "Pengumuman";
      case 3:
        return "Profile";
      case 4:
        return "Pengaduan";
      default:
        return "Lapor Sang Juara";
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

    return PopScope(
      canPop: _selectedIndex == 0,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        if (mounted) {
          setState(() {
            _selectedIndex = 0;
          });
        }
      },
      child: Scaffold(
        key: _scaffoldKey,
        resizeToAvoidBottomInset: false,
        appBar: Navbar(
          title: _getTitle(),
          notificationCount: _notificationCount,
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
                _buildTabItem(Icons.notifications, "Info", 2),
                _buildTabItem(Icons.person, "Profile", 3),
              ],
            ),
          ),
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        floatingActionButton: FloatingActionButton(
          onPressed: _isRefreshing ? null : _refreshAllData,
          backgroundColor:
              _isRefreshing ? Colors.grey : DashboardConstants.primaryColor,
          shape: const CircleBorder(),
          child: _isRefreshing
              ? const SizedBox(
                  width: DashboardConstants.fabSize,
                  height: DashboardConstants.fabSize,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.0,
                    valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                  ),
                )
              : const Icon(Icons.refresh,
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
        children: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              // Background Header Extension
              Container(
                height: 120, // Increased height for better proportion
                width: double.infinity,
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      DashboardConstants.primaryColor,
                      DashboardConstants.secondaryColor,
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.vertical(
                    bottom: Radius.circular(15),
                  ),
                ),
                child: Stack(
                  children: [],
                ),
              ),
              // Banner Card Overlapping
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 20, 16, 0),
                child:
                    BannerCard(name: _userName ?? DashboardConstants.userName),
              ),
            ],
          ),

          const SizedBox(height: 24),

          // Purple info card replaced by reusable PurpleSlider component
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.0),
            child: PurpleSlider(),
          ),

          const SizedBox(height: 24),

          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Text(
              "Layanan Utama",
              style: GoogleFonts.poppins(
                fontSize: 18,
                fontWeight: FontWeight.w600,
                color: Colors.black87,
              ),
            ),
          ),

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
                    onViewAll: () {
                      if (mounted) {
                        setState(() => _selectedIndex = 1);
                      }
                    },
                  ),
                  const SizedBox(height: 10),
                  const PengaduanList(),
                ],
              )),
          // const Padding(
          //     padding: EdgeInsets.symmetric(horizontal: 16.0),
          //     child: Column(
          //       children: [
          //         const SizedBox(height: 10),
          //         HeaderPengaduan(
          //           title: "Berita Terbaru",
          //           subtitle: "Informasi dan berita terkini dari pemerintah",
          //         ),
          //         const SizedBox(height: 10),
          //         PengaduanList(),
          //       ],
          //     )),
        ],
      ),
    );
  }

  Widget _buildHistoryContent() {
    return const Padding(
      padding: EdgeInsets.all(16.0),
      child: HistoryPage(),
    );
  }

  Widget _buildServicesContent() {
    return PengumumanPage(
      onBack: () {
        if (mounted) {
          setState(() {
            _selectedIndex = 0; // Navigate to home tab
          });
        }
      },
    );
  }

  Widget _buildProfileContent() {
    return const ProfilePage();
  }

  Widget _buildAddComplaintContent() {
    return const AddComplaintPage(showAppBar: false);
  }
}
