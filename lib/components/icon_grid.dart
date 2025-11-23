import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../utils/icons_all.dart';
import 'icon_tile.dart';
import '../pages/add_complaint_page.dart';
import '../pages/emergency_page.dart';
import '../pages/news_page.dart';
import '../pages/cctv_list_page.dart';
import '../pages/webview_page.dart';
import '../services/api_service.dart';
import '../models/menu_grid.dart';

class IconGrid extends StatefulWidget {
  final bool isDesktop;
  const IconGrid({Key? key, required this.isDesktop}) : super(key: key);

  @override
  State<IconGrid> createState() => _IconGridState();
}

class _IconGridState extends State<IconGrid> {
  List<MenuGrid> apiMenuItems = [];
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    _fetchMenuItems();
  }

  Future<void> _fetchMenuItems() async {
    setState(() {
      isLoading = true;
    });

    try {
      final response = await ApiService.instance.getActiveMenuGrid();
      if (response.success && response.data != null) {
        setState(() {
          apiMenuItems = response.data!.menuGrid;
          isLoading = false;
        });
      } else {
        setState(() {
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final crossAxisCount = widget.isDesktop ? 6 : 4;
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
        children: _buildMenuItems(context),
      ),
    );
  }

  List<Widget> _buildMenuItems(BuildContext context) {
    List<Widget> menuItems = [];

    // Add static menu items
    menuItems.addAll([
      IconTile(
        icon: Icons.report_problem,
        label: 'Darurat',
        gradient: LinearGradient(
          colors: [Color(0xFFE53935), Color(0xFFD32F2F)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        accentColor: Colors.red,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const EmergencyPage(),
            ),
          );
        },
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
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const AddComplaintPage(),
            ),
          );
        },
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
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const NewsPage(),
            ),
          );
        },
      ),
      IconTile(
        icon: Icons.videocam,
        label: 'CCTV',
        gradient: LinearGradient(
          colors: [Color(0xFF66BB6A), Color(0xFF43A047)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        accentColor: Colors.green,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => const CctvListPage(),
            ),
          );
        },
      ),
    ]);

    // Add API menu items
    for (var menuItem in apiMenuItems) {
      menuItems.add(_buildApiMenuItem(context, menuItem));
    }

    return menuItems;
  }

  Widget _buildApiMenuItem(BuildContext context, MenuGrid menuItem) {
    // Convert FontAwesome icon string to IconData
    IconData iconData = _getIconData(menuItem.iconDisplay);

    // Parse colors
    Color accentColor = _parseColor(menuItem.accentColorDisplay);
    Color primaryColor = _parseColor(menuItem.colorDisplay);

    // Debug logging untuk memastikan warna ter-parse dengan benar
    debugPrint('Menu: ${menuItem.menuTitle}');
    debugPrint('Accent Color: ${menuItem.accentColorDisplay} -> $accentColor');
    debugPrint('Primary Color: ${menuItem.colorDisplay} -> $primaryColor');
    return IconTile(
      icon: iconData,
      label: menuItem.menuTitle,
      gradient: LinearGradient(
        colors: [primaryColor, primaryColor.withOpacity(0.8)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ),
      accentColor: accentColor,
      onTap: () {
        _handleMenuTap(context, menuItem);
      },
    );
  }

  IconData _getIconData(String iconString) {
    // Map common FontAwesome icon strings to Flutter Icons
    return allIcons[iconString] ?? Icons.help_outline;
  }

  Color _parseColor(String? hexColor) {
    if (hexColor == null || hexColor.trim().isEmpty) {
      debugPrint('Warning: Empty color provided, using transparent');
      return Colors.blue; // fallback ke biru agar lebih terlihat
    }

    // Bersihkan spasi dan "#"
    String cleanHex = hexColor.trim().toUpperCase().replaceAll("#", "");
    debugPrint('Parsing color: $hexColor -> $cleanHex');

    // Validasi panjang HEX (harus 6 atau 8)
    if (cleanHex.length == 6) {
      cleanHex = "FF$cleanHex"; // tambah opacity jika tidak ada
    } else if (cleanHex.length != 8) {
      debugPrint('Warning: Invalid hex length: ${cleanHex.length}, using blue');
      return Colors.blue; // fallback jika hex tidak valid
    }

    // Validasi karakter HEX (0-9 dan A-F)
    final validHex = RegExp(r'^[0-9A-F]+$');
    if (!validHex.hasMatch(cleanHex)) {
      debugPrint('Warning: Invalid hex characters in: $cleanHex, using blue');
      return Colors.blue;
    }

    try {
      final color = Color(int.parse(cleanHex, radix: 16));
      debugPrint('Successfully parsed color: $color');
      return color;
    } catch (e) {
      debugPrint('Error parsing color $cleanHex: $e, using blue');
      return Colors.blue;
    }
  }

  void _handleMenuTap(BuildContext context, MenuGrid menuItem) async {
    final menuType = menuItem.menuTipe.toLowerCase();
    final url = menuItem.menuUrl;

    if (menuType == 'url') {
      // Open URL in external browser
      try {
        // Ensure URL has proper scheme
        String formattedUrl = url;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
          formattedUrl = 'https://$url';
        }

        final uri = Uri.parse(formattedUrl);
        debugPrint('Attempting to launch URL: $uri');

        if (await canLaunchUrl(uri)) {
          bool launched = await launchUrl(
            uri,
            mode: LaunchMode.externalApplication,
          );
          if (!launched) {
            _showErrorSnackBar(
                context, 'Tidak dapat membuka URL: $formattedUrl');
          }
        } else {
          // Try with in-app browser as fallback
          if (await canLaunchUrl(uri)) {
            bool launched = await launchUrl(
              uri,
              mode: LaunchMode.inAppWebView,
            );
            if (!launched) {
              _showErrorSnackBar(
                  context, 'Tidak dapat membuka URL: $formattedUrl');
            }
          } else {
            _showErrorSnackBar(
                context, 'Tidak dapat membuka URL: $formattedUrl');
          }
        }
      } catch (e) {
        debugPrint('Error launching URL: $e');
        _showErrorSnackBar(context, 'Error membuka URL: $e');
      }
    } else if (menuType == 'webview') {
      // Open URL in WebView
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => WebViewPage(
            url: url,
            title: menuItem.menuTitle,
          ),
        ),
      );
    } else {
      // Handle other menu types
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Menu ${menuItem.menuTitle} tapped'),
          duration: Duration(seconds: 2),
        ),
      );
    }
  }

  void _showErrorSnackBar(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        duration: const Duration(seconds: 2),
        backgroundColor: Colors.red,
        action: SnackBarAction(
          label: 'Tutup',
          textColor: Colors.white,
          onPressed: () {
            ScaffoldMessenger.of(context).hideCurrentSnackBar();
          },
        ),
      ),
    );
  }
}
