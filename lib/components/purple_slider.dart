import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/slider.dart';
import '../services/session_service.dart';
import 'full_screen_image.dart';

class PurpleSlider extends StatefulWidget {
  final Duration autoPlayInterval;
  const PurpleSlider(
      {Key? key, this.autoPlayInterval = const Duration(seconds: 4)})
      : super(key: key);

  @override
  State<PurpleSlider> createState() => _PurpleSliderState();
}

class _PurpleSliderState extends State<PurpleSlider> {
  late final PageController _controller;
  int _page = 0;
  Timer? _autoTimer;
  List<SliderItem> _sliders = [];
  bool _isLoading = true;
  String? _errorMessage;
  bool _isLightboxVisible = false;
  int _lightboxIndex = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController(viewportFraction: 0.98);
    _controller.addListener(() {
      final p = _controller.page?.round() ?? 0;
      if (p != _page && mounted) setState(() => _page = p);
    });
    _fetchSliders();
  }

  Future<void> _fetchSliders() async {
    if (!mounted) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final sessionService = SessionService.instance;
      final masyarakatId = await sessionService.getUserId() ?? '1';
      final fcmToken = await sessionService.getFcmToken() ?? 'token999';
      final deviceId = await sessionService.getDeviceId() ?? 'token999';

      final response = await ApiService.instance.getActiveSliders(
        masyarakatId: masyarakatId,
        fcmToken: fcmToken,
        deviceId: deviceId,
      );

      if (!mounted) return;

      if (response.success && response.data != null) {
        setState(() {
          _sliders = response.data!.sliders;
          _isLoading = false;
        });

        // Start auto-play after data is loaded
        _startAutoPlay();
      } else {
        setState(() {
          _errorMessage = response.error ?? 'Failed to load sliders';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = 'Error loading sliders: $e';
        _isLoading = false;
      });
    }
  }

  void _startAutoPlay() {
    if (_sliders.isEmpty || !mounted) return;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      _autoTimer = Timer.periodic(widget.autoPlayInterval, (_) {
        if (!mounted || _sliders.isEmpty || !_controller.hasClients) {
          _autoTimer?.cancel();
          return;
        }
        if (mounted) {
          final next = (_page + 1) % _sliders.length;
          _controller.animateToPage(next,
              duration: const Duration(milliseconds: 400),
              curve: Curves.easeInOut);
        }
      });
    });
  }

  @override
  void dispose() {
    _autoTimer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  Widget _buildSlide(BuildContext context, int index) {
    if (_sliders.isEmpty) return const SizedBox.shrink();

    final slider = _sliders[index];
    final themes = [
      BoxDecoration(
        gradient: const LinearGradient(
            colors: [Color(0xFF1C3FAA), Color(0xFF2563EB)]), // biru gradasi
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 8,
              offset: const Offset(0, 4))
        ],
      ),
      BoxDecoration(
        gradient: const LinearGradient(
            colors: [Color(0xFF4E8DF5), Color(0xFF8A2BE2)]),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 6,
              offset: const Offset(0, 4))
        ],
      ),
      BoxDecoration(
        gradient: const LinearGradient(
            colors: [Color(0xFF2BD6C5), Color(0xFF4E9AF5)]),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 6,
              offset: const Offset(0, 4))
        ],
      ),
    ];

    final idx = index % themes.length;

    return Container(
      margin: const EdgeInsets.only(right: 8),
      decoration: themes[idx],
      child: Stack(
        children: [
          // Background image that fills the entire container
          if (slider.fileUrl.isNotEmpty)
            GestureDetector(
              onTap: () {
                // Filter sliders with non-empty fileUrls
                final validSliders =
                    _sliders.where((s) => s.fileUrl.isNotEmpty).toList();
                if (validSliders.isEmpty) return;

                final imageUrls = validSliders.map((s) => s.fileUrl).toList();
                final titles = validSliders.map((s) => s.title).toList();

                // Find index of current slider in filtered list
                final currentIndex =
                    validSliders.indexWhere((s) => s.fileUrl == slider.fileUrl);
                if (currentIndex == -1) return;

                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (context) => FullScreenImage(
                      imageUrls: imageUrls,
                      titles: titles,
                      initialIndex: currentIndex,
                    ),
                  ),
                );
              },
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Hero(
                  tag: 'slider_${slider.fileUrl}',
                  child: Image.network(
                    slider.fileUrl,
                    width: double.infinity,
                    height: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) {
                      return Container(
                        width: double.infinity,
                        height: double.infinity,
                        decoration: BoxDecoration(
                          gradient: themes[idx].gradient,
                          borderRadius: BorderRadius.circular(12),
                        ),
                      );
                    },
                  ),
                ),
              ),
            )
          else
            Container(
              width: double.infinity,
              height: double.infinity,
              decoration: BoxDecoration(
                gradient: themes[idx].gradient,
                borderRadius: BorderRadius.circular(12),
              ),
            ),

          // Overlay content
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(12),
                  bottomRight: Radius.circular(12),
                ),
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withOpacity(0.3),
                    Colors.black.withOpacity(0.7),
                  ],
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    slider.title,
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      shadows: [
                        Shadow(
                          offset: Offset(0, 1),
                          blurRadius: 2,
                          color: Colors.black.withOpacity(0.5),
                        ),
                      ],
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  if (slider.description.isNotEmpty)
                    Text(
                      slider.description,
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 12,
                        shadows: [
                          Shadow(
                            offset: Offset(0, 1),
                            blurRadius: 2,
                            color: Colors.black.withOpacity(0.5),
                          ),
                        ],
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  const SizedBox(height: 6),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadingIndicator() {
    return AspectRatio(
      aspectRatio: 16 / 9,
      child: const Center(
        child: CircularProgressIndicator(color: Colors.blueAccent),
      ),
    );
  }

  Widget _buildErrorMessage() {
    return Container(
      height: MediaQuery.of(context).size.width * 9 / 16, // Match aspect ratio
      margin: const EdgeInsets.only(right: 8),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
            colors: [Color(0xFF1C3FAA), Color(0xFF2563EB)]),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 8,
              offset: const Offset(0, 4))
        ],
      ),
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.error_outline, color: Colors.white, size: 32),
            SizedBox(height: 8),
            Text(
              'Gagal memuat slider',
              style: TextStyle(color: Colors.white, fontSize: 14),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return _buildLoadingIndicator();
    }

    if (_errorMessage != null) {
      return Column(
        children: [
          _buildErrorMessage(),
          const SizedBox(height: 8),
        ],
      );
    }

    if (_sliders.isEmpty) {
      return _buildErrorMessage();
    }

    return Stack(
      children: [
        Column(
          children: [
            AspectRatio(
              aspectRatio: 16 / 9, // Standard widescreen ratio
              child: PageView.builder(
                controller: _controller,
                itemCount: _sliders.length,
                itemBuilder: _buildSlide,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_sliders.length, (i) {
                final active = i == _page;
                return GestureDetector(
                  onTap: () {
                    if (mounted && _controller.hasClients) {
                      _controller.animateToPage(i,
                          duration: const Duration(milliseconds: 300),
                          curve: Curves.easeInOut);
                    }
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    margin: const EdgeInsets.symmetric(horizontal: 6),
                    width: active ? 14 : 8,
                    height: active ? 14 : 8,
                    decoration: BoxDecoration(
                        color: active
                            ? Colors.blueAccent
                            : Colors.blueAccent.withOpacity(0.35),
                        shape: BoxShape.circle),
                  ),
                );
              }),
            ),
          ],
        ),

        // Lightbox overlay
        if (_isLightboxVisible) _buildLightbox(),
      ],
    );
  }

  Widget _buildLightbox() {
    if (_sliders.isEmpty || _lightboxIndex >= _sliders.length) {
      return const SizedBox.shrink();
    }

    final slider = _sliders[_lightboxIndex];

    return GestureDetector(
      onTap: () {
        if (mounted) {
          setState(() {
            _isLightboxVisible = false;
          });
        }
      },
      child: Container(
        color: Colors.black.withOpacity(0.9),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Close button
              Align(
                alignment: Alignment.topRight,
                child: Padding(
                  padding: const EdgeInsets.only(top: 40, right: 20),
                  child: GestureDetector(
                    onTap: () {
                      if (mounted) {
                        setState(() {
                          _isLightboxVisible = false;
                        });
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.6),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.close,
                        color: Colors.white,
                        size: 24,
                      ),
                    ),
                  ),
                ),
              ),

              // Image
              if (slider.fileUrl.isNotEmpty)
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 20),
                  constraints: BoxConstraints(
                    maxHeight: MediaQuery.of(context).size.height * 0.7,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      slider.fileUrl,
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          height: 200,
                          decoration: BoxDecoration(
                            color: Colors.grey[300],
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Center(
                            child: Icon(
                              Icons.error_outline,
                              color: Colors.grey,
                              size: 48,
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                ),

              const SizedBox(height: 20),

              // Navigation buttons
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Previous button
                  if (_lightboxIndex > 0)
                    GestureDetector(
                      onTap: () {
                        if (mounted) {
                          setState(() {
                            _lightboxIndex = _lightboxIndex - 1;
                          });
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.6),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.arrow_back,
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                    ),

                  const SizedBox(width: 40),

                  // Next button
                  if (_lightboxIndex < _sliders.length - 1)
                    GestureDetector(
                      onTap: () {
                        if (mounted) {
                          setState(() {
                            _lightboxIndex = _lightboxIndex + 1;
                          });
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.6),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.arrow_forward,
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                    ),
                ],
              ),

              const SizedBox(height: 20),

              // Title and description
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: Column(
                  children: [
                    Text(
                      slider.title,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 8),
                    if (slider.description.isNotEmpty)
                      Text(
                        slider.description,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 14,
                        ),
                        textAlign: TextAlign.center,
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis,
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
