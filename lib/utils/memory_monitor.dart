import 'dart:io';
import 'dart:async';
import 'package:flutter/services.dart';
import 'package:flutter/painting.dart';

class MemoryMonitor {
  static Timer? _timer;
  static const int _memoryThresholdMB = 200; // 200MB threshold

  static void startMonitoring() {
    // Check memory every 30 seconds
    _timer = Timer.periodic(const Duration(seconds: 30), (timer) {
      _checkMemoryUsage();
    });
  }

  static void stopMonitoring() {
    _timer?.cancel();
    _timer = null;
  }

  static Future<void> _checkMemoryUsage() async {
    try {
      final info = ProcessInfo.currentRss;
      final memoryMB = info / (1024 * 1024);

      print('Memory usage: ${memoryMB.toStringAsFixed(2)} MB');

      // If memory usage is too high, clean up resources
      if (memoryMB > _memoryThresholdMB) {
        print('High memory usage detected, cleaning up resources');
        await _cleanupResources();
      }
    } catch (e) {
      print('Error checking memory usage: $e');
    }
  }

  static Future<void> _cleanupResources() async {
    try {
      // Clear image cache
      PaintingBinding.instance.imageCache.clear();
      PaintingBinding.instance.imageCache.clearLiveImages();

      // Force garbage collection
      // Note: This is not guaranteed to run immediately
      print('Memory cleanup completed');
    } catch (e) {
      print('Error during memory cleanup: $e');
    }
  }
}
