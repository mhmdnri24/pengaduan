import 'package:flutter/material.dart';
import 'package:pengaduan/models/complaint.dart';
import 'package:pengaduan/pages/detail_pengaduan_page.dart';
import 'package:pengaduan/pages/history_page.dart';
import 'package:pengaduan/services/api_service.dart';

class HeaderPengaduan extends StatelessWidget {
  final String title;
  final String subtitle;
  final VoidCallback? onViewAll;

  const HeaderPengaduan(
      {super.key, required this.title, required this.subtitle, this.onViewAll});

  @override
  Widget build(BuildContext context) {
    // This is a lightweight header widget meant to be embedded inside pages.
    // Don't create a new MaterialApp/Scaffold here.
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  title,
                  style: TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.bold,
                      fontSize: 18),
                ),
              ),
              TextButton.icon(
                style: TextButton.styleFrom(
                  foregroundColor: Colors.white,
                  backgroundColor: Colors.blue,
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
                onPressed: () {
                  if (onViewAll != null) {
                    onViewAll!();
                  } else {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (context) => HistoryPage(),
                      ),
                    );
                  }
                },
                icon: const Icon(Icons.remove_red_eye,
                    size: 16, color: Colors.white),
                label: const Text("Lihat semua"),
              ),
            ],
          ),
          SizedBox(height: 10),
          Text(subtitle),
          SizedBox(height: 10),
        ],
      ),
    );
  }
}

class PengaduanList extends StatefulWidget {
  const PengaduanList({super.key});

  @override
  State<PengaduanList> createState() => _PengaduanListState();
}

class _PengaduanListState extends State<PengaduanList> {
  int selectedTab = 0;
  bool _isLoading = false;
  String? _errorMessage;
  List<Complaint> _complaints = [];
  // ignore: unused_field
  int _totalCount = 0;
  // ignore: unused_field
  int _prosesCount = 0;
  // ignore: unused_field
  int _selesaiCount = 0;

  int _currentPage = 1;
  int _totalPages = 1;
  // ignore: unused_field
  int _totalRecords = 0;
  bool _hasNext = false;
  bool _hasPrev = false;
  final int _itemsPerPage = 5;

  @override
  void initState() {
    super.initState();
    fetchData();
  }

  Future<void> fetchData({int? page}) async {
    try {
      final pageToLoad = page ?? _currentPage;

      String? statusFilter;
      switch (selectedTab) {
        case 1: // Baru
          statusFilter = 'LAPOR';
          break;
        case 2: // Proses
          statusFilter = 'PROSES';
          break;
        case 3: // Selesai
          statusFilter = 'SELESAI';
          break;
        default: // Semua
          statusFilter = null;
      }

      final response = await ApiService.instance.getComplaints(
        page: pageToLoad,
        limit: _itemsPerPage,
        status: statusFilter,
      );

      if (response.success && response.data != null) {
        setState(() {
          _complaints = response.data!.pelaporan;
          _currentPage = response.data!.pagination.currentPage;
          _totalPages = response.data!.pagination.totalPages;
          _totalRecords = response.data!.pagination.totalRecords;
          _hasNext = response.data!.pagination.hasNext;
          _hasPrev = response.data!.pagination.hasPrev;
          _isLoading = false;
        });
        // Load counts for all statuses
        await _loadCounts();
      } else {
        setState(() {
          _errorMessage = response.error ?? 'Failed to load complaints';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _loadCounts() async {
    try {
      // Get total count
      final totalResponse = await ApiService.instance.getComplaints(
        page: 1,
        limit: 1,
      );

      // Get proses count
      final prosesResponse = await ApiService.instance.getComplaints(
        page: 1,
        limit: 1,
        status: 'PROSES',
      );

      // Get selesai count
      final selesaiResponse = await ApiService.instance.getComplaints(
        page: 1,
        limit: 1,
        status: 'SELESAI',
      );

      setState(() {
        _totalCount = totalResponse.data?.pagination.totalRecords ?? 0;
        _prosesCount = prosesResponse.data?.pagination.totalRecords ?? 0;
        _selesaiCount = selesaiResponse.data?.pagination.totalRecords ?? 0;
      });
    } catch (e) {
      // Silently fail for counts
      debugPrint('Error loading counts: $e');
    }
  }

  void _goToNextPage() {
    if (_hasNext) {
      fetchData(page: _currentPage + 1);
    }
  }

  void _goToPreviousPage() {
    if (_hasPrev) {
      fetchData(page: _currentPage - 1);
    }
  }

  // ignore: unused_element
  void _goToPage(int page) {
    if (page >= 1 && page <= _totalPages) {
      fetchData(page: page);
    }
  }

  @override
  Widget build(BuildContext context) {
    // sample data removed (real data comes from API)

    return Column(
      children: _buildCard(),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toUpperCase()) {
      case 'LAPOR':
        return Colors.pink;
      case 'PROSES':
        return Colors.orange;
      case 'SELESAI':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }

  Color _getPriorityColor(String prioritas) {
    switch (prioritas.toUpperCase()) {
      case 'TINGGI':
        return Colors.red;
      case 'SEDANG':
        return Colors.orange;
      case 'RENDAH':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _getVerificationText(String status) {
    switch (status.toUpperCase()) {
      case 'LAPOR':
        return 'Menunggu Verifikasi';
      case 'PROSES':
        return 'Sedang Diproses';
      case 'SELESAI':
        return 'Selesai';
      default:
        return status;
    }
  }

  String _getTimeAgo(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays > 0) {
      return '${difference.inDays} hari lalu';
    } else if (difference.inHours > 0) {
      return '${difference.inHours} jam lalu';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes} menit lalu';
    } else {
      return 'Baru saja';
    }
  }

  // ignore: unused_element
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
    String? imageUrl,
    required VoidCallback detailAction,
  }) {
    return Card(
      color: Colors.white,
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
                  Text(
                    description.length > 100
                        ? '${description.substring(0, 100)}...'
                        : description,
                    style: const TextStyle(fontSize: 13, color: Colors.black87),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on,
                          size: 14, color: Colors.grey),
                      const SizedBox(width: 2),
                      Expanded(
                        child: Text(location,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                                fontSize: 12, color: Colors.grey)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
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
              child: imageUrl != null
                  ? Image.network(
                      imageUrl,
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => Container(
                        width: 60,
                        height: 60,
                        color: Colors.grey[300],
                        child: const Icon(Icons.image_not_supported,
                            color: Colors.grey),
                      ),
                      loadingBuilder: (context, child, loadingProgress) {
                        if (loadingProgress == null) return child;
                        return Container(
                          width: 60,
                          height: 60,
                          color: Colors.grey[200],
                          child: const Center(
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ),
                        );
                      },
                    )
                  : Container(
                      width: 60,
                      height: 60,
                      color: Colors.grey[300],
                      child: const Icon(Icons.image, color: Colors.grey),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildComplaintCardFromData(Complaint complaint) {
    return _complaintCard(
      statusChips: [
        _chip(complaint.status, _getStatusColor(complaint.status)),
        _chip(complaint.prioritas, _getPriorityColor(complaint.prioritas)),
      ],
      title: complaint.title,
      description: complaint.description,
      location: complaint.alamat,
      time: _getTimeAgo(complaint.createdAt),
      verification: _getVerificationText(complaint.status),
      verificationColor: _getStatusColor(complaint.status),
      imageUrl: complaint.files != null && complaint.files!.isNotEmpty
          ? complaint.files!.first.fileUrl
          : null,
      detailAction: () {
        // Navigate to detail page DetailPengaduanPage
        // Ensure we always pass a non-empty string
        String complaintId;
        if (complaint.id.isNotEmpty) {
          complaintId = complaint.id;
        } else if (complaint.kodeLaporan.isNotEmpty) {
          complaintId = complaint.kodeLaporan;
        } else {
          complaintId = 'unknown';
        }
        print('Navigating to detail with ID: $complaintId');
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => DetailPengaduanPage(complaintId: complaintId),
          ),
        );
        debugPrint('Detail for complaint ${complaint.kodeLaporan}');
      },
    );
  }

  List<Widget> _buildCard() {
    List<Widget> children = [];

    if (_isLoading) {
      children.add(
        const Center(
          child: Padding(
            padding: EdgeInsets.all(32.0),
            child: CircularProgressIndicator(),
          ),
        ),
      );
    } else if (_errorMessage != null) {
      children.add(
        Center(
          child: Padding(
            padding: const EdgeInsets.all(32.0),
            child: Column(
              children: [
                const Icon(Icons.error_outline, size: 48, color: Colors.red),
                const SizedBox(height: 16),
                Text(
                  _errorMessage!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.red),
                ),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: fetchData,
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      );
    } else {
      for (var complaint in _complaints) {
        children.add(_buildComplaintCardFromData(complaint));
        children.add(const SizedBox(height: 12));
      }

      // Add pagination controls
      // if (_totalPages > 1) {
      //   children.add(const SizedBox(height: 8));
      //   children.add(_buildPaginationControls());
      //   children.add(const SizedBox(height: 16));
      // }
    }

    return children;
  }

  // ignore: unused_element
  Widget _buildPaginationControls() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Previous button
          SizedBox(
            width: 80,
            child: ElevatedButton(
              onPressed: _hasPrev ? _goToPreviousPage : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1C3FAA),
                foregroundColor: Colors.white,
                disabledBackgroundColor: Colors.grey[300],
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.arrow_back, size: 16),
                  SizedBox(width: 4),
                  Text('Prev', style: TextStyle(fontSize: 12)),
                ],
              ),
            ),
          ),

          const SizedBox(width: 12),

          // Page indicator
          Flexible(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFF1C3FAA)),
              ),
              child: Text(
                '$_currentPage/$_totalPages',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1C3FAA),
                  fontSize: 13,
                ),
              ),
            ),
          ),

          const SizedBox(width: 12),

          // Next button
          SizedBox(
            width: 80,
            child: ElevatedButton(
              onPressed: _hasNext ? _goToNextPage : null,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1C3FAA),
                foregroundColor: Colors.white,
                disabledBackgroundColor: Colors.grey[300],
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text('Next', style: TextStyle(fontSize: 12)),
                  SizedBox(width: 4),
                  Icon(Icons.arrow_forward, size: 16),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
