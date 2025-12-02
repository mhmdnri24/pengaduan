import 'package:flutter/material.dart';
import '../models/pengumuman.dart';
import '../services/api_service.dart';
import 'pengumuman_detail_page.dart';

class PengumumanPage extends StatefulWidget {
  const PengumumanPage({super.key, this.onBack});

  final Function()? onBack;

  @override
  State<PengumumanPage> createState() => _PengumumanPageState();
}

class _PengumumanPageState extends State<PengumumanPage> {
  List<Pengumuman> _pengumumanList = [];
  bool _isLoading = true;
  String? _errorMessage;

  // Pagination variables
  int _currentPage = 1;
  int _totalPages = 1;
  int _totalRecords = 0;
  bool _hasNext = false;
  bool _hasPrev = false;
  final int _itemsPerPage = 10;

  @override
  void initState() {
    super.initState();
    _fetchPengumuman();
  }

  Future<void> _fetchPengumuman({int? page}) async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final pageToLoad = page ?? _currentPage;
      print('Fetching pengumuman page $pageToLoad from API...');
      
      final response = await ApiService.instance.getActivePengumuman(
        page: pageToLoad,
        limit: _itemsPerPage,
      );
      
      if (response.success && response.data != null) {
        setState(() {
          _pengumumanList = response.data!.pengumuman;
          if (response.data!.pagination != null) {
            _currentPage = response.data!.pagination!.currentPage;
            _totalPages = response.data!.pagination!.totalPages;
            _totalRecords = response.data!.pagination!.totalRecords;
            _hasNext = response.data!.pagination!.hasNext;
            _hasPrev = response.data!.pagination!.hasPrev;
          }
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = response.error ?? 'Gagal memuat pengumuman';
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

  void _goToNextPage() {
    if (_hasNext) {
      _fetchPengumuman(page: _currentPage + 1);
    }
  }

  void _goToPreviousPage() {
    if (_hasPrev) {
      _fetchPengumuman(page: _currentPage - 1);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF1C3FAA)),
        ),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline,
                size: 64,
                color: Colors.red,
              ),
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 16,
                  color: Colors.black87,
                ),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: () => _fetchPengumuman(),
                icon: const Icon(Icons.refresh),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1C3FAA),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 12,
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_pengumumanList.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.campaign_outlined,
              size: 80,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'Belum ada pengumuman',
              style: TextStyle(
                fontSize: 18,
                color: Colors.grey[600],
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _fetchPengumuman(page: 1),
      color: const Color(0xFF1C3FAA),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          ..._pengumumanList.map((pengumuman) => GestureDetector(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => PengumumanDetailPage(
                    id: pengumuman.id,
                    title: pengumuman.judul,
                  ),
                ),
              );
            },
            child: _buildPengumumanCard(pengumuman),
          )),
          if (_totalPages > 1) ...[
            const SizedBox(height: 16),
            _buildPaginationControls(),
            const SizedBox(height: 16),
          ],
        ],
      ),
    );
  }

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

  Widget _buildPengumumanCard(Pengumuman pengumuman) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          if (pengumuman.gambarUrl.isNotEmpty)
            ClipRRect(
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(16),
              ),
              child: Image.network(
                pengumuman.gambarUrl,
                width: double.infinity,
                height: 200,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) {
                  return Container(
                    width: double.infinity,
                    height: 200,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Colors.grey[200]!, Colors.grey[300]!],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Icon(
                      Icons.image_not_supported,
                      size: 64,
                      color: Colors.grey[500],
                    ),
                  );
                },
                loadingBuilder: (context, child, loadingProgress) {
                  if (loadingProgress == null) return child;
                  return Container(
                    width: double.infinity,
                    height: 200,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Colors.grey[100]!, Colors.grey[200]!],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Center(
                      child: CircularProgressIndicator(
                        value: loadingProgress.expectedTotalBytes != null
                            ? loadingProgress.cumulativeBytesLoaded /
                                loadingProgress.expectedTotalBytes!
                            : null,
                        valueColor: const AlwaysStoppedAnimation<Color>(
                          Color(0xFF1C3FAA),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),

          // Content
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Title
                Text(
                  pengumuman.judul,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w700,
                    color: Colors.black87,
                  ),
                ),
                const SizedBox(height: 8),

                // Description
                Text(
                  pengumuman.deskripsi,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[700],
                    height: 1.5,
                  ),
                ),
                const SizedBox(height: 12),

                // Footer with date and status
                Row(
                  children: [
                    Icon(
                      Icons.access_time_rounded,
                      size: 16,
                      color: Colors.grey[600],
                    ),
                    const SizedBox(width: 4),
                    Text(
                      pengumuman.createdAtFormatted,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                    const Spacer(),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFF1C3FAA).withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        pengumuman.statusFormatted,
                        style: const TextStyle(
                          fontSize: 11,
                          color: Color(0xFF1C3FAA),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
