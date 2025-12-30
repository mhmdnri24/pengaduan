import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class IconTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final Gradient? gradient;
  final Color? accentColor;
  final Color? color;
  final VoidCallback? onTap;

  const IconTile({
    super.key,
    required this.icon,
    required this.label,
    this.gradient,
    this.accentColor,
    this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
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
                borderRadius: BorderRadius.circular(20), // More rounded
                boxShadow: [
                  BoxShadow(
                    color: (accentColor ?? Colors.black).withValues(alpha: 0.2),
                    blurRadius: 12,
                    offset: const Offset(0, 6),
                    spreadRadius: -2,
                  ),
                ],
              ),
              child: Icon(icon, color: Colors.white, size: boxSize * 0.45),
            ),

            const SizedBox(height: 10),

            // Batasi tinggi area teks agar tidak memaksa overflow
            SizedBox(
              height: 36,
              child: Text(
                label,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.poppins(
                  fontSize: 12,
                  fontWeight: FontWeight.w500,
                  color: Colors.black87,
                ),
              ),
            ),
            
            // Removed the bottom indicator line for a cleaner look
          ],
        ),
      );
    });
  }
}
