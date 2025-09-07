import 'package:flutter/material.dart';

class Sidebar extends StatelessWidget {
  final ValueChanged<int>? onItemSelected;
  final int selectedIndex;

  const Sidebar({super.key, this.onItemSelected, this.selectedIndex = 0});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(
        color: Theme.of(context).colorScheme.primary,
        child: SafeArea(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text('Lapor Pak Wali', style: Theme.of(context).textTheme.titleLarge?.copyWith(color: Colors.white)),
              ),
              _buildItem(context, Icons.dashboard, 'Dashboard', 0),
              _buildItem(context, Icons.report, 'Pengaduan', 1),
              _buildItem(context, Icons.settings, 'Settings', 2),
              const Spacer(),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text('v1.0', style: TextStyle(color: Colors.white70)),
              )
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildItem(BuildContext context, IconData icon, String label, int index) {
    final selected = index == selectedIndex;
    return ListTile(
      leading: Icon(icon, color: selected ? Colors.white : Colors.white70),
      title: Text(label, style: TextStyle(color: selected ? Colors.white : Colors.white70)),
      selected: selected,
      onTap: () => onItemSelected?.call(index),
    );
  }
}
