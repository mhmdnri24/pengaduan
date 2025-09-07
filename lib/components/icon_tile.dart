import 'package:flutter/material.dart';
import 'dart:math' as math;

class IconTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback? onTap;

  const IconTile({super.key, required this.icon, required this.label, required this.color, this.onTap});

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(builder: (context, constraints) {
      // ensure icon box fits available space; leave room for label
      final maxH = constraints.maxHeight.isFinite ? constraints.maxHeight : 80.0;
      final boxSize = math.min(56.0, math.max(40.0, maxH * 0.6));
      return InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: boxSize,
              height: boxSize,
              decoration: BoxDecoration(
                color: color,
                borderRadius: BorderRadius.circular(12),
                boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 6, offset: Offset(0, 3))],
              ),
              child: Icon(icon, color: Colors.white, size: boxSize * 0.45),
            ),
            const SizedBox(height: 6),
            SizedBox(
              width: constraints.maxWidth,
              child: Text(
                label,
                style: Theme.of(context).textTheme.bodySmall,
                textAlign: TextAlign.center,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
      );
    });
  }
}
