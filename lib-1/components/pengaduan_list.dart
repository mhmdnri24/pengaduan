import 'package:flutter/material.dart';
 
class HeaderPengaduan extends StatelessWidget {
  final String title;
  final String subtitle;

  const HeaderPengaduan({super.key, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    // This is a lightweight header widget meant to be embedded inside pages.
    // Don't create a new MaterialApp/Scaffold here.
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
        children: [
           Expanded(
            child: Text(
              title,
              style: TextStyle(color: Colors.black, fontWeight: FontWeight.bold, fontSize: 18),
            ),
          ),
          TextButton.icon(
            style: TextButton.styleFrom(
              foregroundColor: Colors.white,
              backgroundColor: Colors.blue,
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            onPressed: () {},
            icon: const Icon(Icons.remove_red_eye, size: 16, color: Colors.white),
            label: const Text("Lihat semua"),
          ),
        ],
      ),
      SizedBox(height: 10),
      Text(subtitle),
      SizedBox(height: 10),
        ],
      ),
    );
  }
}

class PengaduanList extends StatelessWidget {
  const PengaduanList({super.key});

  @override
  Widget build(BuildContext context) {
    final List<Map<String, dynamic>> data = [
      {
        "status": "BARU",
        "statusColor": Colors.green,
        "title": "Jalan Rusak di Depan Sekolah",
        "desc": "Jalan berlubang besar yang membahayakan pengendara...",
        "time": "2 jam lalu",
        "image": null,
      },
      {
        "status": "PROSES",
        "statusColor": Colors.orange,
        "title": "Lampu Jalan Mati",
        "desc": "Lampu penerangan jalan sudah mati selama 3 hari...",
        "time": "1 hari lalu",
        "image":
            "https://images.unsplash.com/photo-1581091870622-6c80b56f8b91?auto=format&fit=crop&w=400&q=80",
      },
      {
        "status": "SELESAI",
        "statusColor": Colors.teal,
        "title": "Saluran Air Tersumbat",
        "desc": "Saluran pembuangan air hujan tersumbat sampah...",
        "time": "2 hari lalu",
        "image":
            "https://images.unsplash.com/photo-1508780709619-79562169bc64?auto=format&fit=crop&w=400&q=80",
      },
    ];

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: data.length,
      itemBuilder: (context, index) {
        final item = data[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (item["image"] != null)
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      item["image"],
                      width: 60,
                      height: 60,
                      fit: BoxFit.cover,
                      // show a small progress indicator while loading
                      loadingBuilder: (context, child, loadingProgress) {
                        if (loadingProgress == null) return child;
                        return Container(
                          width: 60,
                          height: 60,
                          color: Colors.grey.shade200,
                          child: const Center(
                            child: SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          ),
                        );
                      },
                      // show a fallback if the image fails to load
                      errorBuilder: (context, error, stackTrace) {
                        return Container(
                          width: 60,
                          height: 60,
                          color: Colors.grey.shade300,
                          child: const Icon(Icons.broken_image, color: Colors.white70),
                        );
                      },
                    ),
                  )
                else
                  Container(
                    width: 40,
                    height: 40,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      color: Colors.grey,
                    ),
                    child: const Icon(Icons.person, color: Colors.white),
                  ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: item["statusColor"].withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              item["status"],
                              style: TextStyle(
                                color: item["statusColor"],
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                          const Spacer(),
                          Text(
                            item["time"],
                            style: const TextStyle(
                                fontSize: 12, color: Colors.grey),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        item["title"],
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item["desc"],
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.black54,
                        ),
                      ),
                    ],
                  ),
                )
              ],
            ),
          ),
        );
      },
    );
  }
}
