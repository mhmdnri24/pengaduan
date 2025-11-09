import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:http/http.dart' as http;
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:pengaduan/config/api_config.dart';
import 'package:pengaduan/components/progress_timeline.dart';
import 'package:pengaduan/services/api_service.dart';
import 'package:pengaduan/services/session_service.dart';
import 'package:quickalert/quickalert.dart';

class DetailPengaduanPage extends StatefulWidget {
  final String complaintId;
  const DetailPengaduanPage({super.key, required this.complaintId});

  @override
  State<DetailPengaduanPage> createState() => _DetailPengaduanPageState();
}

class _DetailPengaduanPageState extends State<DetailPengaduanPage> {
  bool _loading = true;
  String? _error;
  Map<String, dynamic>? _data;

  // Progress timeline data
  bool _historyLoading = false;
  String? _historyError;
  List<Map<String, dynamic>> _history = [];

  // Comments data
  List<Map<String, dynamic>> _comments = [];

  // Rating functionality
  int _selectedRating = 0;
  final TextEditingController _commentController = TextEditingController();
  bool _isSubmittingRating = false;

  // Image slider functionality
  late PageController _pageController;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _fetchDetail();
    _fetchHistory();
  }

  @override
  void dispose() {
    _commentController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _fetchDetail() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    final url =
        Uri.parse('${ApiConfig.baseUrl}/pelaporan/${widget.complaintId}');
    try {
      final resp = await http.get(url, headers: {
        'X-API-Key': ApiConfig.apiKey,
        'Origin': ApiConfig.origin,
      });

      if (resp.statusCode == 200) {
        final body = json.decode(resp.body) as Map<String, dynamic>;
        if (body['status'] == 'success' && body['data'] != null) {
          final data = body['data'] as Map<String, dynamic>;

          // Extract comments from response
          final commentsList = data['comments'] as List<dynamic>? ?? [];

          setState(() {
            _data = data;
            _comments = commentsList.cast<Map<String, dynamic>>();
            _loading = false;
          });
          return;
        } else {
          setState(() {
            _error = body['message']?.toString() ?? 'Unknown error';
            _loading = false;
          });
          return;
        }
      } else {
        setState(() {
          _error = 'Network error: ${resp.statusCode}';
          _loading = false;
        });
        return;
      }
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  Future<void> _fetchHistory() async {
    setState(() {
      _historyLoading = true;
      _historyError = null;
    });

    try {
      final response =
          await ApiService.instance.getComplaintHistory(widget.complaintId);

      if (response.success && response.data != null) {
        final data = response.data!;
        if (data['status'] == 'success' && data['data'] != null) {
          final historyData = data['data'] as Map<String, dynamic>;
          final historyList = historyData['history'] as List<dynamic>? ?? [];
          print(historyData);

          setState(() {
            _history = historyList.cast<Map<String, dynamic>>();
            _historyLoading = false;
          });
        } else {
          setState(() {
            _historyError =
                data['message']?.toString() ?? 'Failed to load history';
            _historyLoading = false;
          });
        }
      } else {
        setState(() {
          _historyError = response.error ?? 'Failed to load history';
          _historyLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _historyError = e.toString();
        _historyLoading = false;
      });
    }
  }

  Future<void> _submitRating() async {
    if (_selectedRating == 0) {
      QuickAlert.show(
        context: context,
        type: QuickAlertType.warning,
        title: "Perhatian",
        text: 'Silakan pilih rating terlebih dahulu',
      );
      return;
    }

    setState(() {
      _isSubmittingRating = true;
    });

    try {
      // Get user_id from session
      final userId = await SessionService.instance.getUserId();

      if (userId == null) {
        if (mounted) {
          QuickAlert.show(
            context: context,
            type: QuickAlertType.error,
            title: "Session Tidak Valid",
            text: 'Silakan login kembali.',
          );
        }
        setState(() {
          _isSubmittingRating = false;
        });
        return;
      }

      final response = await ApiService.instance.createComment(
        pelaporanId: widget.complaintId,
        comment: _commentController.text.trim().isNotEmpty
            ? _commentController.text.trim()
            : 'Rating: $_selectedRating bintang',
        rating: _selectedRating,
        createdBy: userId,
      );

      if (response.success) {
        if (mounted) {
          QuickAlert.show(
            context: context,
            type: QuickAlertType.success,
            title: "Berhasil",
            text: 'Rating berhasil dikirim!',
            autoCloseDuration: const Duration(seconds: 2),
            showConfirmBtn: false,
          );
        }

        // Reset form and refresh data
        if (mounted) {
          setState(() {
            _selectedRating = 0;
            _commentController.clear();
          });
          // Refresh the detail data to get updated comments
          _fetchDetail();
        }
      } else {
        if (mounted) {
          QuickAlert.show(
            context: context,
            type: QuickAlertType.error,
            title: "Gagal",
            text: 'Gagal mengirim rating: ${response.error ?? 'Unknown error'}',
          );
        }
      }
    } catch (e) {
      if (mounted) {
        QuickAlert.show(
          context: context,
          type: QuickAlertType.error,
          title: "Error",
          text: 'Error: $e',
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isSubmittingRating = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF4F6F9),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1C3FAA),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Detail Pengaduan ',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Colors.white),
            onPressed: () {
              _fetchDetail();
              _fetchHistory();
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(10),
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) {
      return SizedBox(
        height: 300,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: const [CircularProgressIndicator(), SizedBox(height: 8)],
          ),
        ),
      );
    }

    if (_error != null) {
      return SizedBox(
        height: 200,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Error: $_error'),
              const SizedBox(height: 8),
              ElevatedButton(
                  onPressed: _fetchDetail, child: const Text('Retry')),
            ],
          ),
        ),
      );
    }

    final data = _data ?? {};

    // Use provided fields with fallbacks
    final title = data['judul']?.toString() ?? 'Tidak ada judul';
    final description = data['deskripsi']?.toString() ?? '-';
    final tanggal = data['created_at_formatted']?.toString() ?? '-';
    final lokasi = data['alamat']?.toString() ?? '-';
    final kode = data['kode_laporan']?.toString() ?? widget.complaintId;
    final kategori = data['nama_kategori']?.toString() ??
        (data['kategori']?.toString() ?? '-');
    final fotoUrl = data['foto_url']?.toString();
    final filesList = (data['files'] as List?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Status Chip
        Align(
          alignment: Alignment.centerLeft,
          child: Chip(
            backgroundColor: const Color(0xFFFFE0B2),
            label: Text(
              data['status']?.toString() ?? 'DALAM PROSES',
              style: GoogleFonts.poppins(
                color: Colors.orange[800],
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),

        // Card laporan utama
        Card(
          color: Colors.white,
          elevation: 1,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (fotoUrl != null) ...[
                  ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      fotoUrl,
                      height: 200,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (c, e, s) => Container(
                        height: 200,
                        color: Colors.grey[200],
                        child: const Center(child: Icon(Icons.broken_image)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                ] else if (filesList.isNotEmpty) ...[
                  // Image slider for multiple files
                  _buildImageSlider(filesList),
                  const SizedBox(height: 12),
                ],
                Text(
                  title,
                  style: GoogleFonts.poppins(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  description,
                  style: GoogleFonts.poppins(
                    color: Colors.black87,
                    fontSize: 12.5,
                  ),
                ),
                const Divider(height: 24),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(child: _infoText('Tanggal Laporan', tanggal)),
                        const SizedBox(width: 12),
                        Expanded(
                          child: InkWell(
                            onTap: () {},
                            child: Row(
                              children: [
                                Expanded(child: _infoText('Lokasi', lokasi)),
                                // const Icon(Icons.map,
                                //     size: 20, color: Color(0xFF1C3FAA)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                            child: _infoText('ID Pengaduan', '#$kode',
                                link: true)),
                        const SizedBox(width: 12),
                        Expanded(child: _infoText('Kategori', kategori)),
                      ],
                    ),
                  ],
                )
              ],
            ),
          ),
        ),
        const SizedBox(height: 20),
        // Improved Maps Button
        Container(
          width: double.infinity,
          margin: const EdgeInsets.symmetric(horizontal: 16),
          child: ElevatedButton.icon(
            icon: const Icon(Icons.map_outlined, size: 20),
            label: const Text(
              'Lihat Lokasi di Peta',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
            onPressed: () => _showLocationMap(data),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1C3FAA),
              foregroundColor: Colors.white,
              elevation: 2,
              shadowColor: const Color(0xFF1C3FAA).withOpacity(0.3),
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ),

        // Progress Timeline - only show if there's data or loading/error state
        if ((_history.isNotEmpty || _historyLoading || _historyError != null) &&
            _history.length > 1)
          ProgressTimeline(
            history: _history,
            isLoading: _historyLoading,
            error: _historyError,
          ),

        const SizedBox(height: 20),

        // Komentar
        Text(
          'Komentar',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        const SizedBox(height: 10),
        if (_comments.isEmpty)
          Card(
            color: Colors.grey[50],
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Center(
                child: Column(
                  children: [
                    Icon(
                      Icons.comment_outlined,
                      size: 48,
                      color: Colors.grey[400],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Belum ada komentar',
                      style: GoogleFonts.poppins(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          )
        else
          ..._comments.map((comment) => _commentCard(
                userName: comment['created_by_name']?.toString() ?? 'Anonim',
                date: comment['created_at_formatted']?.toString() ?? '',
                message: comment['comment']?.toString() ?? '',
                rating: comment['rating'] != null
                    ? int.tryParse(comment['rating'].toString())
                    : null,
              )),
        const SizedBox(height: 20),

        // Berikan Rating
        Text(
          'Berikan Rating',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
            fontSize: 16,
          ),
        ),
        const SizedBox(height: 10),
        Card(
          color: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Bagaimana penilaian Anda terhadap penanganan pengaduan ini?',
                  style: GoogleFonts.poppins(fontSize: 12.5),
                ),
                if (_selectedRating > 0) ...[
                  const SizedBox(height: 6),
                  Text(
                    'Rating: $_selectedRating bintang',
                    style: GoogleFonts.poppins(
                      fontSize: 12,
                      color: Colors.amber[700],
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
                const SizedBox(height: 10),
                Row(
                  children: List.generate(
                    5,
                    (index) => GestureDetector(
                      onTap: () {
                        setState(() {
                          _selectedRating = index + 1;
                        });
                      },
                      child: Icon(
                        index < _selectedRating
                            ? Icons.star_rounded
                            : Icons.star_border_rounded,
                        color: Colors.amber,
                        size: 28,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                TextField(
                  controller: _commentController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    hintText: 'Komentar (Opsional)',
                    hintStyle: GoogleFonts.poppins(fontSize: 12.5),
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: const BorderSide(color: Color(0xFFD0D0D0)),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0D47A1),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                    onPressed: _isSubmittingRating ? null : _submitRating,
                    child: _isSubmittingRating
                        ? Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(
                                'Mengirim...',
                                style: GoogleFonts.poppins(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          )
                        : Text(
                            'Kirim Rating',
                            style: GoogleFonts.poppins(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  void _showLocationMap(Map<String, dynamic> data) {
    final double? lat = double.tryParse(data['lokasi_lat']?.toString() ?? '');
    final double? lng = double.tryParse(data['lokasi_lng']?.toString() ?? '');

    if (lat == null || lng == null) {
      QuickAlert.show(
        context: context,
        type: QuickAlertType.warning,
        title: "Lokasi Tidak Tersedia",
        text: 'Lokasi tidak tersedia',
      );
      return;
    }

    showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Lokasi Pengaduan',
                    style: GoogleFonts.poppins(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close),
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
            ),
            SizedBox(
              width: MediaQuery.of(context).size.width * 0.8,
              height: MediaQuery.of(context).size.height * 0.5,
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  bottom: Radius.circular(16),
                ),
                child: GoogleMap(
                  initialCameraPosition: CameraPosition(
                    target: LatLng(lat, lng),
                    zoom: 15,
                  ),
                  markers: {
                    Marker(
                      markerId: const MarkerId('complaint_location'),
                      position: LatLng(lat, lng),
                      infoWindow: InfoWindow(
                        title: data['judul']?.toString() ?? 'Lokasi Pengaduan',
                        snippet: data['alamat']?.toString(),
                      ),
                    ),
                  },
                  zoomControlsEnabled: true,
                  mapType: MapType.normal,
                  myLocationEnabled: true,
                  myLocationButtonEnabled: true,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _infoText(String label, String value, {bool link = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: GoogleFonts.poppins(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: GoogleFonts.poppins(
            fontSize: 13,
            color: link ? Colors.blue : Colors.black,
            fontWeight: link ? FontWeight.w500 : FontWeight.w400,
          ),
        ),
      ],
    );
  }

  Widget _commentCard({
    required String userName,
    required String date,
    required String message,
    required int? rating,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    userName,
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      fontSize: 12.5,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  date,
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
            if (rating != null) ...[
              const SizedBox(height: 6),
              Row(
                children: [
                  Text(
                    'Rating: ',
                    style: GoogleFonts.poppins(
                      fontSize: 11,
                      color: Colors.grey[600],
                    ),
                  ),
                  ...List.generate(5, (index) {
                    return Icon(
                      index < rating
                          ? Icons.star_rounded
                          : Icons.star_border_rounded,
                      color: Colors.amber,
                      size: 14,
                    );
                  }),
                ],
              ),
            ],
            const SizedBox(height: 6),
            Text(
              message,
              style: GoogleFonts.poppins(fontSize: 12.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _tanggapanCard({
    required String role,
    required String date,
    required String message,
    required Color color,
  }) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: color,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    role,
                    style: GoogleFonts.poppins(
                      fontWeight: FontWeight.w600,
                      fontSize: 12.5,
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  date,
                  style: GoogleFonts.poppins(
                    fontSize: 12,
                    color: Colors.grey.shade600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              message,
              style: GoogleFonts.poppins(fontSize: 12.5),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildImageSlider(List filesList) {
    if (filesList.isEmpty) {
      return Container(
        width: double.infinity,
        height: 200,
        decoration: BoxDecoration(
          color: Colors.grey[200],
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Center(
          child: Icon(Icons.broken_image, color: Colors.grey),
        ),
      );
    }

    return Column(
      children: [
        Container(
          height: 200,
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (int page) {
              setState(() {
                _currentPage = page;
              });
            },
            itemCount: filesList.length,
            itemBuilder: (context, index) {
              final fileUrl = filesList[index]['file_url']?.toString();
              return Padding(
                padding: const EdgeInsets.symmetric(horizontal: 4.0),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: fileUrl != null && fileUrl.isNotEmpty
                      ? Image.network(
                          fileUrl,
                          width: double.infinity,
                          height: 200,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) {
                            return Container(
                              width: double.infinity,
                              height: 200,
                              color: Colors.grey[200],
                              child: const Center(
                                child: Icon(Icons.broken_image,
                                    color: Colors.grey),
                              ),
                            );
                          },
                        )
                      : Container(
                          width: double.infinity,
                          height: 200,
                          color: Colors.grey[200],
                          child: const Center(
                            child: Icon(Icons.broken_image, color: Colors.grey),
                          ),
                        ),
                ),
              );
            },
          ),
        ),
        const SizedBox(height: 8),
        // Page indicators
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(
            filesList.length,
            (index) => AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              margin: const EdgeInsets.symmetric(horizontal: 4.0),
              height: 8.0,
              width: _currentPage == index ? 24.0 : 8.0,
              decoration: BoxDecoration(
                color: _currentPage == index
                    ? const Color(0xFF1C3FAA)
                    : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(4.0),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
