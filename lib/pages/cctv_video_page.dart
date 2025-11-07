import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:webview_flutter_wkwebview/webview_flutter_wkwebview.dart';
import '../models/cctv.dart';

class CctvVideoPage extends StatefulWidget {
  final CctvCamera camera;

  const CctvVideoPage({
    super.key,
    required this.camera,
  });

  @override
  State<CctvVideoPage> createState() => _CctvVideoPageState();
}

class _CctvVideoPageState extends State<CctvVideoPage> {
  late final WebViewController _controller;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _initializeWebView();
  }

  void _initializeWebView() {
    late final PlatformWebViewControllerCreationParams params;
    if (WebViewPlatform.instance is WebKitWebViewPlatform) {
      params = WebKitWebViewControllerCreationParams(
        allowsInlineMediaPlayback: true,
        mediaTypesRequiringUserAction: const <PlaybackMediaTypes>{},
      );
    } else {
      params = const PlatformWebViewControllerCreationParams();
    }

    final WebViewController controller =
        WebViewController.fromPlatformCreationParams(params);

    if (controller.platform is AndroidWebViewController) {
      AndroidWebViewController.enableDebugging(true);
      (controller.platform as AndroidWebViewController)
        ..setMediaPlaybackRequiresUserGesture(false)
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(const Color(0x00000000));
    }

    _controller = controller
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (int progress) {
            // Update loading bar if needed
          },
          onPageStarted: (String url) {
            setState(() {
              _isLoading = true;
              _errorMessage = null;
            });
          },
          onPageFinished: (String url) {
            // Enable autoplay after page loads
            _enableAutoplay();
            setState(() {
              _isLoading = false;
            });
          },
          onWebResourceError: (WebResourceError error) {
            setState(() {
              _isLoading = false;
              _errorMessage = 'Gagal memuat video: ${error.description}';
            });
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.camera.embedUrl));
  }

  void _enableAutoplay() {
    // JavaScript to enable autoplay for video elements
    _controller.runJavaScript("""
      // Find all video elements and enable autoplay
      const videos = document.querySelectorAll('video');
      videos.forEach(video => {
        video.muted = false;
        video.autoplay = true;
        video.playsInline = true;
        video.controls = true;
        
        // Try to play the video
        video.play().catch(error => {
          console.log('Autoplay failed:', error);
          // Try muted autoplay as fallback
          video.muted = true;
          video.play().then(() => {
            // Unmute after successful play
            setTimeout(() => {
              video.muted = false;
            }, 1000);
          });
        });
      });
      
      // Also try to find and click play buttons
      const playButtons = document.querySelectorAll('button[title*="play"], .play-button, .play-btn, [aria-label*="play"]');
      playButtons.forEach(button => {
        button.click();
      });
      
      // For iframe videos (like YouTube, etc.)
      const iframes = document.querySelectorAll('iframe');
      iframes.forEach(iframe => {
        try {
          const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
          const iframeVideos = iframeDoc.querySelectorAll('video');
          iframeVideos.forEach(video => {
            video.muted = false;
            video.autoplay = true;
            video.play();
          });
        } catch(e) {
          console.log('Cannot access iframe content:', e);
        }
      });
    """);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.camera.name),
        backgroundColor: const Color(0xFF1C3FAA),
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              _controller.reload();
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // Camera info header

          // Video player
          Expanded(
            child: _buildVideoPlayer(),
          ),
        ],
      ),
    );
  }

  Widget _buildVideoPlayer() {
    if (_errorMessage != null) {
      return Center(
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
              style: const TextStyle(
                fontSize: 16,
                color: Colors.red,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                ElevatedButton(
                  onPressed: () {
                    setState(() {
                      _errorMessage = null;
                      _isLoading = true;
                    });
                    _controller.reload();
                  },
                  child: const Text('Coba Lagi'),
                ),
                const SizedBox(width: 16),
                ElevatedButton(
                  onPressed: () {
                    _openVideoInExternalPlayer();
                  },
                  child: const Text('Buka di Player'),
                ),
              ],
            ),
          ],
        ),
      );
    }

    if (_isLoading) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(),
            SizedBox(height: 16),
            Text('Memuat video...'),
          ],
        ),
      );
    }

    return Stack(
      children: [
        WebViewWidget(
          controller: _controller,
          gestureRecognizers: const {},
        ),
        if (_isLoading)
          Container(
            color: Colors.black,
            child: const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: Colors.white),
                  SizedBox(height: 16),
                  Text(
                    'Memuat video...',
                    style: TextStyle(color: Colors.white),
                  ),
                ],
              ),
            ),
          ),
        // Add fallback button for WebView issues
        Positioned(
          bottom: 16,
          right: 16,
          child: FloatingActionButton.small(
            onPressed: _openVideoInExternalPlayer,
            backgroundColor: Colors.black54,
            child: const Icon(Icons.open_in_new, color: Colors.white),
          ),
        ),
      ],
    );
  }

  void _openVideoInExternalPlayer() {
    // For now, just show a dialog with the URL
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Video URL'),
          content: SingleChildScrollView(
            child: SelectableText(widget.camera.embedUrl),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Tutup'),
            ),
          ],
        );
      },
    );
  }
}
