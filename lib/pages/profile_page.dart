import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../components/edit_profile_page.dart';
import 'landing_page.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({Key? key}) : super(key: key);

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  String? id;
  String? userName;
  String? userNik;
  String? userPhone;
  String? userPhotoUrl;
  String? token;
  String? deviceId;

  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();

    setState(() {
      // token and userId intentionally omitted from UI for privacy
      id = prefs.getString('user_id');
      userName = prefs.getString('user_name');
      userNik = prefs.getString('user_nik');
      userPhone = prefs.getString('user_phone');
      userPhotoUrl = prefs.getString('user_photo_url');
      token = prefs.getString('fcm_token');
      deviceId = prefs.getString('device_id');
      isLoading = false;
      print(token);
      print(deviceId);
    });
  }

  Future<void> _logout() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    const FlutterSecureStorage secureStorage = FlutterSecureStorage();
    await secureStorage.deleteAll();
    if (!mounted) return;
    Navigator.pushReplacement(
        context, MaterialPageRoute(builder: (context) => const LandingPage()));
  }

  Widget _infoItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(
          icon,
          size: 20,
          color: Colors.grey[600],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8FB),
      // appBar: AppBar(
      //   title: const Text(
      //     'Profil Saya',
      //     style: TextStyle(
      //       fontWeight: FontWeight.w600,
      //       fontSize: 18,
      //       color: Colors.white,
      //     ),
      //   ),
      //   centerTitle: true,
      //   elevation: 0,
      //   backgroundColor: const Color(0xFF1C3FAA),
      //   foregroundColor: Colors.white,
      // ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadUserData,
              child: ListView(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                children: [
                  // Header with gradient and avatar
                  Container(
                    height: 200,
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF2255EE), Color(0xFF4285F4)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(20),
                        bottomRight: Radius.circular(20),
                      ),
                    ),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16.0),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 4),
                            ),
                            child: CircleAvatar(
                              radius: 44,
                              backgroundImage: userPhotoUrl != null &&
                                      userPhotoUrl!.isNotEmpty
                                  ? NetworkImage(userPhotoUrl!)
                                  : const AssetImage(
                                          'assets/images/profile.jpeg')
                                      as ImageProvider,
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            userName ?? 'Nama Tidak Diketahui',
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 18,
                                fontWeight: FontWeight.w700),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'NIK: ${userNik ?? '-'}',
                            style: const TextStyle(
                                color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),

                  // Overlapping card (without userId/token)
                  Transform.translate(
                    offset: const Offset(0, -40),
                    child: Card(
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 8),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16)),
                      elevation: 0.5,
                      shadowColor: Colors.black12,
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: const [
                                Icon(Icons.person_outline,
                                    color: Color(0xFF2255EE), size: 20),
                                SizedBox(width: 8),
                                Text('Informasi Akun',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w600,
                                      fontSize: 16,
                                    )),
                              ],
                            ),
                            const SizedBox(height: 16),
                            _infoItem(
                              icon: Icons.badge_outlined,
                              label: 'Nama Lengkap',
                              value: userName ?? '-',
                            ),
                            const Divider(height: 16),
                            _infoItem(
                              icon: Icons.credit_card_outlined,
                              label: 'NIK',
                              value: userNik ?? '-',
                            ),
                            const Divider(height: 16),
                            _infoItem(
                              icon: Icons.phone_outlined,
                              label: 'No. Telepon',
                              value: userPhone ?? '-',
                            ),
                            if (deviceId != null) ...[
                              const Divider(height: 16),
                              _infoItem(
                                icon: Icons.phone_android_outlined,
                                label: 'ID Perangkat',
                                value: deviceId!,
                              ),
                            ],
                          ],
                        ),
                      ),
                    ),
                  ),

                  // Action tiles
                  Row(
                    children: [
                      Expanded(
                        child: Card(
                          color: Colors.white,
                          elevation: 0.5,
                          shadowColor: Colors.black12,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                          child: InkWell(
                            onTap: () {},
                            borderRadius: BorderRadius.circular(12),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              child: Column(
                                children: const [
                                  Icon(Icons.report, color: Colors.blue),
                                  SizedBox(height: 8),
                                  Text('Pengaduan',
                                      style: TextStyle(fontSize: 12)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Card(
                          color: Colors.white,
                          elevation: 0.5,
                          shadowColor: Colors.black12,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                          child: InkWell(
                            onTap: () {},
                            borderRadius: BorderRadius.circular(12),
                            child: Padding(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              child: Column(
                                children: const [
                                  Icon(Icons.favorite, color: Colors.pink),
                                  SizedBox(height: 8),
                                  Text('Favorit',
                                      style: TextStyle(fontSize: 12)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 16),
                  // Action Buttons - Horizontal Layout
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      children: [
                        // Edit Profile Button
                        Expanded(
                          child: SizedBox(
                            height: 50,
                            child: ElevatedButton.icon(
                              icon: const Icon(Icons.edit, size: 20),
                              label: const Text('Edit Profil',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  )),
                              onPressed: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                      builder: (context) =>
                                          const EditProfilePage()),
                                );
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF2255EE),
                                foregroundColor: Colors.white,
                                elevation: 2,
                                shadowColor:
                                    const Color(0xFF2255EE).withOpacity(0.3),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ),
                        ),

                        const SizedBox(width: 12),

                        // Logout Button
                        Expanded(
                          child: SizedBox(
                            height: 50,
                            child: OutlinedButton.icon(
                              icon: const Icon(Icons.logout, size: 20),
                              label: const Text('Logout',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  )),
                              onPressed: () async {
                                // Show confirmation dialog before logout
                                bool? confirm = await showDialog<bool>(
                                  context: context,
                                  builder: (context) => AlertDialog(
                                    title: const Text('Konfirmasi Logout'),
                                    content: const Text(
                                      'Apakah Anda yakin ingin keluar dari akun?',
                                    ),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    actions: [
                                      TextButton(
                                        onPressed: () =>
                                            Navigator.pop(context, false),
                                        child: const Text('Batal'),
                                      ),
                                      ElevatedButton(
                                        onPressed: () =>
                                            Navigator.pop(context, true),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: Colors.red,
                                          foregroundColor: Colors.white,
                                          shape: RoundedRectangleBorder(
                                            borderRadius:
                                                BorderRadius.circular(8),
                                          ),
                                        ),
                                        child: const Text('Ya, Keluar'),
                                      ),
                                    ],
                                  ),
                                );

                                if (confirm == true) {
                                  _logout();
                                }
                              },
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.red,
                                side: const BorderSide(
                                    color: Colors.red, width: 1.5),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12)),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }
}
