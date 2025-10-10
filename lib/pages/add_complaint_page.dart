import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

class AddComplaintPage extends StatefulWidget {
  const AddComplaintPage({Key? key}) : super(key: key);

  @override
  State<AddComplaintPage> createState() => _AddComplaintPageState();
}

class _AddComplaintPageState extends State<AddComplaintPage> {
  String? selectedCategory;
  final TextEditingController titleController = TextEditingController();
  final TextEditingController descriptionController = TextEditingController();
  final TextEditingController locationController = TextEditingController();
  int descriptionCount = 0;
  String? selectedUrgency; // 'low','medium','high'
  bool anonymous = false;
  List<XFile> selectedImages = [];

  @override
  void dispose() {
    titleController.dispose();
    descriptionController.dispose();
    locationController.dispose();
    super.dispose();
  }

  Widget _buildCategoryTile(
      String key, IconData icon, Color iconBg, String label) {
    final bool active = selectedCategory == key;
    return GestureDetector(
      onTap: () => setState(() => selectedCategory = key),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: active ? Colors.blue.shade700 : Colors.grey.shade200),
          boxShadow: [
            BoxShadow(
                color: Colors.black12, blurRadius: 6, offset: Offset(0, 2))
          ],
        ),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              height: 48,
              width: 48,
              decoration: BoxDecoration(
                color: iconBg,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: Colors.white),
            ),
            const SizedBox(height: 8),
            Text(label,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }

  Widget _photoPlaceholder() {
    return GestureDetector(
      onTap: _pickImage,
      child: Container(
        height: 80,
        width: 100,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: const [
              Icon(Icons.add, color: Colors.grey),
              SizedBox(height: 4),
              Text('Tambah Foto',
                  style: TextStyle(color: Colors.grey, fontSize: 12))
            ],
          ),
        ),
      ),
    );
  }

  Widget _imageWidget(XFile file) {
    return Stack(
      children: [
        Container(
          height: 80,
          width: 100,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(8),
            image: DecorationImage(
              image: FileImage(File(file.path)),
              fit: BoxFit.cover,
            ),
          ),
        ),
        Positioned(
          top: 4,
          right: 4,
          child: GestureDetector(
            onTap: () => setState(() => selectedImages.remove(file)),
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(
                color: Colors.black54,
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.close, color: Colors.white, size: 14),
            ),
          ),
        ),
      ],
    );
  }

  List<Widget> _buildPhotoWidgets() {
    List<Widget> widgets =
        selectedImages.map((img) => _imageWidget(img)).toList();
    if (widgets.length < 3) {
      widgets.add(_photoPlaceholder());
    }
    return widgets
        .expand((widget) => [widget, const SizedBox(width: 12)])
        .toList()
      ..removeLast();
  }

  Future<void> _pickImage() async {
    final ImagePicker picker = ImagePicker();
    final XFile? image = await picker.pickImage(source: ImageSource.gallery);
    if (image != null) {
      setState(() => selectedImages.add(image));
    }
  }

  Widget _urgencyButton(String key, String label, Color color) {
    final bool active = selectedUrgency == key;
    return GestureDetector(
      onTap: () => setState(() => selectedUrgency = key),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        decoration: BoxDecoration(
          color: active ? Colors.white : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
              color: active ? color : Colors.grey.shade300,
              width: active ? 2 : 1),
          boxShadow: [
            BoxShadow(
                color: Colors.black12, blurRadius: 4, offset: Offset(0, 2))
          ],
        ),
        child: Column(
          children: [
            Container(
                height: 26,
                width: 26,
                decoration:
                    BoxDecoration(color: color, shape: BoxShape.circle)),
            const SizedBox(height: 6),
            Text(label, style: const TextStyle(fontSize: 12)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8FB),
      // appBar: AppBar(
      //   elevation: 0,
      //   backgroundColor: Colors.transparent,
      //   foregroundColor: Colors.black87,
      //   title: const Text('Buat Laporan'),
      //   centerTitle: true,
      //   leading: IconButton(
      //       onPressed: () => Navigator.pop(context),
      //       icon: const Icon(Icons.arrow_back)),
      //   actions: [
      //     IconButton(onPressed: () {}, icon: const Icon(Icons.help_outline))
      //   ],
      // ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    const Icon(Icons.label, color: Colors.blue),
                    const SizedBox(width: 8),
                    const Text('Kategori Laporan',
                        style: TextStyle(fontWeight: FontWeight.w600))
                  ]),
                  const SizedBox(height: 12),
                  GridView.count(
                    crossAxisCount: 2,
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                    childAspectRatio: 1.4,
                    children: [
                      _buildCategoryTile('infrastruktur', Icons.account_balance,
                          Colors.green, 'Infrastruktur'),
                      _buildCategoryTile('lingkungan', Icons.eco,
                          Colors.green.shade700, 'Lingkungan'),
                      _buildCategoryTile('keamanan', Icons.security,
                          Colors.orange, 'Keamanan'),
                      _buildCategoryTile(
                          'layanan', Icons.group, Colors.blue, 'Layanan'),
                    ],
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Judul Laporan',
                        style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: titleController,
                      decoration: InputDecoration(
                          hintText: 'Masukkan judul laporan yang jelas',
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12))),
                    ),
                  ]),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Deskripsi Lengkap',
                        style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: descriptionController,
                      maxLines: 6,
                      onChanged: (v) =>
                          setState(() => descriptionCount = v.length),
                      decoration: InputDecoration(
                          hintText:
                              'Jelaskan detail masalah yang ingin dilaporkan...',
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12))),
                    ),
                    const SizedBox(height: 8),
                    Text('$descriptionCount/500 karakter',
                        style:
                            const TextStyle(color: Colors.grey, fontSize: 12)),
                  ]),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Lokasi Kejadian',
                        style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: locationController,
                      decoration: InputDecoration(
                          hintText: 'Masukkan alamat lengkap',
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12))),
                    ),
                    const SizedBox(height: 12),
                    OutlinedButton.icon(
                      onPressed: () {},
                      icon: const Icon(Icons.my_location),
                      label: const Text('Gunakan Lokasi Saat Ini'),
                      style: OutlinedButton.styleFrom(
                        side:
                            BorderSide(color: Colors.blue.shade700, width: 1.5),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        foregroundColor: Colors.blue.shade700,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                      ),
                    ),
                  ]),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Foto Pendukung',
                        style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 12),
                    Row(children: _buildPhotoWidgets()),
                    const SizedBox(height: 8),
                    const Text('Maksimal 3 foto, ukuran maksimal 5MB per foto',
                        style: TextStyle(color: Colors.grey, fontSize: 12)),
                  ]),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Tingkat Urgensi',
                        style: TextStyle(fontWeight: FontWeight.w600)),
                    const SizedBox(height: 12),
                    Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                              child: _urgencyButton(
                                  'low', 'Rendah', Colors.green)),
                          const SizedBox(width: 12),
                          Expanded(
                              child: _urgencyButton(
                                  'medium', 'Sedang', Colors.amber)),
                          const SizedBox(width: 12),
                          Expanded(
                              child:
                                  _urgencyButton('high', 'Tinggi', Colors.red)),
                        ]),
                  ]),
            ),
          ),

          const SizedBox(height: 12),

          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 1,
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(children: [
                Checkbox(
                    value: anonymous,
                    onChanged: (v) => setState(() => anonymous = v ?? false)),
                const SizedBox(width: 8),
                const Expanded(
                    child: Text(
                        'Laporan Anonim\nIdentitas Anda akan disembunyikan dari publik',
                        style: TextStyle(color: Colors.black87))),
              ]),
            ),
          ),

          const SizedBox(height: 18),

          // Buttons
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 0),
            child: Column(children: [
              // Gradient submit button
              InkWell(
                onTap: () {},
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  height: 54,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                        colors: [Color(0xFF2255EE), Color(0xFF4285F4)]),
                    borderRadius: BorderRadius.circular(12),
                    boxShadow: [
                      BoxShadow(
                          color: Colors.black12,
                          blurRadius: 8,
                          offset: Offset(0, 4))
                    ],
                  ),
                  child: const Center(
                      child: Row(mainAxisSize: MainAxisSize.min, children: [
                    Icon(Icons.send, color: Colors.white),
                    SizedBox(width: 8),
                    Text('Kirim Laporan',
                        style: TextStyle(
                            color: Colors.white, fontWeight: FontWeight.bold))
                  ])),
                ),
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: () {},
                style: OutlinedButton.styleFrom(
                  backgroundColor: Colors.grey.shade200,
                  padding:
                      const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
                child: const SizedBox(
                    width: double.infinity,
                    child: Center(
                        child: Text('Simpan Sebagai Draft',
                            style: TextStyle(color: Colors.black54)))),
              ),
              const SizedBox(height: 24),
            ]),
          ),
        ],
      ),
    );
  }
}
