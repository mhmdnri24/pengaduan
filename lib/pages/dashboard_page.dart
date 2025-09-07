import 'package:flutter/material.dart';
import '../components/sidebar.dart';
import '../components/navbar.dart';
import '../components/dashboard_card.dart';
import '../components/stat_card.dart';
import '../components/chart_widget.dart';
import 'package:fl_chart/fl_chart.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  int selectedIndex = 0;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  Widget build(BuildContext context) {
    final isDesktop = MediaQuery.of(context).size.width >= 800;

    return Scaffold(
      key: _scaffoldKey,
      appBar: Navbar(
        title: 'Dashboard',
        onMenuPressed: () {
          // use scaffold key to open drawer safely from AppBar
          if (!isDesktop) _scaffoldKey.currentState?.openDrawer();
        },
      ),
      drawer: isDesktop ? null : Sidebar(onItemSelected: (i) => setState(() => selectedIndex = i), selectedIndex: selectedIndex),
      body: Row(
        children: [
          if (isDesktop)
            SizedBox(
              width: 250,
              child: Sidebar(onItemSelected: (i) => setState(() => selectedIndex = i), selectedIndex: selectedIndex),
            ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.all(16.0),
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Wrap(
                      spacing: 12,
                      runSpacing: 12,
                      children: const [
                        SizedBox(width: 220, child: StatCard(title: 'Total Reports', value: '124', icon: Icons.report)),
                        SizedBox(width: 220, child: StatCard(title: 'Open', value: '12', icon: Icons.warning, color: Colors.orange)),
                        SizedBox(width: 220, child: StatCard(title: 'Resolved', value: '98', icon: Icons.check_circle, color: Colors.green)),
                        SizedBox(width: 220, child: StatCard(title: 'Users', value: '42', icon: Icons.person, color: Colors.blue)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          flex: 2,
                          child: DashboardCard(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Reports Over Time', style: Theme.of(context).textTheme.titleMedium),
                                const SizedBox(height: 12),
                                ChartWidget(spots: [
                                  FlSpot(0, 3),
                                  FlSpot(1, 4),
                                  FlSpot(2, 2),
                                  FlSpot(3, 5),
                                  FlSpot(4, 3.5),
                                ]),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          flex: 1,
                          child: DashboardCard(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: const [
                                Text('Recent Reports', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                                SizedBox(height: 12),
                                ListTile(title: Text('Report #124'), subtitle: Text('Open')),
                                ListTile(title: Text('Report #123'), subtitle: Text('Resolved')),
                                ListTile(title: Text('Report #122'), subtitle: Text('Open')),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    DashboardCard(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: const [
                          Text('Activity Log', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                          SizedBox(height: 12),
                          Text('No recent activity'),
                        ],
                      ),
                    )
                  ],
                ),
              ),
            ),
          )
        ],
      ),
    );
  }
}
