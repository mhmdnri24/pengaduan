 import 'dart:math' as math;
import 'package:flutter/material.dart';

class IconTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final Gradient? gradient;
  final Color? accentColor;
  final Color? color;
  final VoidCallback? onTap;

  const IconTile({
    Key? key,
    required this.icon,
    required this.label,
    this.gradient,
    this.accentColor,
    this.color,
    this.onTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final resolvedAccent = accentColor ?? color;

    final boxGradient = gradient ??
        (color != null
            ? LinearGradient(
                colors: [color!, color!],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              )
            : LinearGradient(
                colors: [Colors.grey.shade400, Colors.grey.shade600],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ));

    return LayoutBuilder(builder: (context, constraints) {
      // ukuran kotak ikon akan menyesuaikan lebar cell
      final maxW = constraints.maxWidth.isFinite ? constraints.maxWidth : 84.0;
      final boxSize = math.min(64.0, maxW * 0.75);

      return GestureDetector(
        onTap: onTap,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
          Container(
            height: boxSize,
            width: boxSize,
            decoration: BoxDecoration(
              gradient: boxGradient,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.08),
                  blurRadius: 6,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Icon(icon, color: Colors.white, size: boxSize * 0.48),
          ),

          const SizedBox(height: 6),

          // Batasi tinggi area teks agar tidak memaksa overflow
          SizedBox(
            height: 36,
            child: Text(
              label,
              textAlign: TextAlign.center,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
            ),
          ),

if (resolvedAccent != null) ...[
//   const SizedBox(height: 2), // lebih rapat
  Container(
    height: 3,
    width: 24,
    decoration: BoxDecoration(
      color: resolvedAccent,
      borderRadius: BorderRadius.circular(2),
    ),
  ),
],



          ],
        ),
      );
    });
  }
}
