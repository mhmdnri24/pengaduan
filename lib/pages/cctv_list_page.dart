import 'package:flutter/material.dart';
import 'package:quickalert/quickalert.dart';
import '../models/cctv.dart';
import '../services/cctv_service.dart';
import 'cctv_video_page.dart';

class CctvListPage extends StatefulWidget {
  const CctvListPage({super.key});

  @override
  State<CctvListPage> createState() => _CctvListPageState();
}

class _CctvListPageState extends State<CctvListPage> {
  final CctvService _cctvService = CctvService.instance;
  final ScrollController _scrollController = ScrollController();
  List<CctvCamera> _cameras = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _errorMessage;
  int _currentPage = 1;
  int _totalPages = 1;
  final int _pageSize = 10;
  bool _hasMore = true;

  @override
  void initState() {
    super.initState();
    _loadCctvCameras();
    _scrollController.addListener(_scrollListener);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollListener() {
    if (_scrollController.position.pixels ==
        _scrollController.position.maxScrollExtent) {
      if (!_isLoadingMore && _hasMore) {
        _loadMoreCameras();
      }
    }
  }

  Future<void> _loadCctvCameras({bool isRefresh = false}) async {
    if (isRefresh) {
      setState(() {
        _currentPage = 1;
        _hasMore = true;
        _isLoading = true;
        _errorMessage = null;
      });
    } else {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      final response = await _cctvService.getCctvCameras(
        domain: 'lubuklinggaukota.go.id',
        page: _currentPage,
        limit: _pageSize,
      );

      if (response.success) {
        setState(() {
          if (isRefresh) {
            _cameras = response.cameras;
          } else {
            _cameras.addAll(response.cameras);
          }
          _totalPages = response.maxPage ?? 1;
          _hasMore = _currentPage < _totalPages;
          _isLoading = false;
          _isLoadingMore = false;
        });
      } else {
        setState(() {
          _errorMessage = response.message ?? 'Tidak ada kamera CCTV tersedia';
          _isLoading = false;
          _isLoadingMore = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat data CCTV: $e';
        _isLoading = false;
        _isLoadingMore = false;
      });
    }
  }

  Future<void> _loadMoreCameras() async {
    if (!_hasMore || _isLoadingMore) return;

    setState(() {
      _isLoadingMore = true;
      _currentPage++;
    });

    await _loadCctvCameras();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('CCTV Monitoring (${_cameras.length})'),
        backgroundColor: const Color(0xFF1C3FAA),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => _loadCctvCameras(isRefresh: true),
          ),
        ],
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Memuat data CCTV...'),
          ],
        ),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.error_outline,
              size: 64,
              color: Colors.grey,
            ),
            const SizedBox(height: 16),
            Text(
              _errorMessage!,
              style: const TextStyle(
                fontSize: 16,
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loadCctvCameras,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    if (_cameras.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.videocam_off,
              size: 64,
              color: Colors.grey,
            ),
            SizedBox(height: 16),
            Text(
              'Tidak ada kamera CCTV tersedia',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey,
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _loadCctvCameras(isRefresh: true),
      child: ListView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.all(16),
        itemCount: _cameras.length + (_hasMore ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == _cameras.length && _hasMore) {
            return _buildLoadingMoreIndicator();
          }
          final camera = _cameras[index];
          return _buildCameraCard(camera);
        },
      ),
    );
  }

  Widget _buildCameraCard(CctvCamera camera) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: () {
          // if (camera.isActive) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => CctvVideoPage(camera: camera),
            ),
          );
          // } else {
          //   QuickAlert.show(
          //     context: context,
          //     type: QuickAlertType.warning,
          //     title: "Camera Tidak Aktif",
          //     text: 'Kamera CCTV ini sedang tidak aktif',
          //     autoCloseDuration: const Duration(seconds: 2),
          //     showConfirmBtn: false,
          //   );
          // }
        },
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Camera thumbnail or placeholder
            Container(
              width: double.infinity,
              height: 180,
              decoration: BoxDecoration(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(12),
                ),
                color: Colors.grey[300],
              ),
              child: Stack(
                children: [
                  if (camera.thumbnailUrl != null)
                    ClipRRect(
                      borderRadius: const BorderRadius.vertical(
                        top: Radius.circular(12),
                      ),
                      child: Image.network(
                        camera.thumbnailUrl!,
                        width: double.infinity,
                        height: 180,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return _buildCameraPlaceholder();
                        },
                      ),
                    )
                  else
                    _buildCameraPlaceholder(),

                  // Play button overlay
                  if (camera.isActive)
                    Positioned.fill(
                      child: Container(
                        decoration: BoxDecoration(
                          borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(12),
                          ),
                          color: Colors.black.withOpacity(0.3),
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.play_circle_filled,
                            size: 48,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ),

            // Camera info
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    camera.name,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(
                        Icons.location_on,
                        size: 16,
                        color: Colors.grey,
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          camera.location,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
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
      ),
    );
  }

  Widget _buildCameraPlaceholder() {
    return Container(
      width: double.infinity,
      height: 180,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [Color(0xFF1C3FAA), Color(0xFF2D62F2)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.videocam,
              size: 48,
              color: Colors.white,
            ),
            SizedBox(height: 8),
            Text(
              'CCTV Camera',
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLoadingMoreIndicator() {
    return Container(
      padding: const EdgeInsets.all(16),
      child: Center(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
            const SizedBox(width: 16),
            Text(
              'Memuat lebih banyak...',
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
