import 'package:flutter/material.dart';
import '../pages/news_page.dart';

class NewsListPage extends StatelessWidget {
  const NewsListPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Dummy news data
    final newsList = [
      {
        'id': '1',
        'title': 'Pemerintah Kota Meluncurkan Program Smart City',
        'description':
            'Program ini bertujuan untuk meningkatkan kualitas hidup masyarakat',
        'date': '2025-01-15',
        'image': 'https://via.placeholder.com/150',
      },
      {
        'id': '2',
        'title': 'Pembangunan Taman Kota Rampung',
        'description': 'Taman kota akan dilengkapi dengan fasilitas modern',
        'date': '2025-01-14',
        'image': 'https://via.placeholder.com/150',
      },
      {
        'id': '3',
        'title': 'Festival Budaya Tahunan',
        'description': 'Festival budaya akan digelar pada bulan depan',
        'date': '2025-01-13',
        'image': 'https://via.placeholder.com/150',
      },
    ];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Berita'),
        backgroundColor: const Color(0xFF1C3FAA),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: newsList.length,
        itemBuilder: (context, index) {
          final news = newsList[index];
          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            elevation: 2,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: InkWell(
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => const NewsPage(),
                  ),
                );
              },
              borderRadius: BorderRadius.circular(12),
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        news['image'] ?? 'https://via.placeholder.com/150',
                        width: 80,
                        height: 80,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Container(
                            width: 80,
                            height: 80,
                            color: Colors.grey[300],
                            child:
                                const Icon(Icons.article, color: Colors.white),
                          );
                        },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            news['title'] ?? 'Tanpa Judul',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1C3FAA),
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            news['description'] ?? 'Tanpa deskripsi',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            news['date'] ?? 'Tanpa tanggal',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[500],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
