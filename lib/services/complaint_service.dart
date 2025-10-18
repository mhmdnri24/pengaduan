import 'dart:async';
import 'dart:math';
import 'dart:io';
import '../models/complaint.dart';
import 'bubble_overlay_service.dart';
import 'api_service.dart';

class ComplaintService {
  static ComplaintService? _instance;
  static final List<Complaint> _complaints = [];
  static final StreamController<List<Complaint>> _complaintsController =
      StreamController<List<Complaint>>.broadcast();
  static final StreamController<Complaint> _newComplaintController =
      StreamController<Complaint>.broadcast();

  ComplaintService._internal();

  static ComplaintService get instance {
    _instance ??= ComplaintService._internal();
    return _instance!;
  }

  /// Stream of all complaints
  Stream<List<Complaint>> get complaintsStream => _complaintsController.stream;

  /// Stream of new complaints
  Stream<Complaint> get newComplaintStream => _newComplaintController.stream;

  /// Get all complaints
  List<Complaint> get complaints => List.unmodifiable(_complaints);

  /// Get pending complaints count
  int get pendingComplaintsCount => _complaints.length;

  /// Initialize the service
  Future<void> initialize() async {
    // Start the bubble overlay service
    await BubbleOverlayService.instance.startService();

    // Listen for new complaints and show bubble
    _newComplaintController.stream.listen((complaint) {
      // _showBubbleForNewComplaint();
    });
  }

  /// Add a new complaint (simulate receiving from server)
  Future<void> addComplaint(Complaint complaint) async {
    _complaints.add(complaint);
    _complaintsController.add(List.unmodifiable(_complaints));
    _newComplaintController.add(complaint);
  }

  /// Fetch complaints from API with pagination, filtering, and complete metadata
  Future<ApiResponse<ComplaintListResponse>> fetchComplaints({
    int page = 1,
    int limit = 10,
    String? status,
    String? kategori,
    String? search,
  }) async {
    try {
      var apiResponse = await ApiService.instance.getComplaints(
        page: page,
        limit: limit,
        status: status,
        kategori: kategori,
        search: search,
      );

     

      if (apiResponse.success && apiResponse.data != null) {
        return ApiResponse(success: true, data: apiResponse.data);
      } else {
        return ApiResponse(success: false, error: apiResponse.error);
      }
    } catch (e) {
      return ApiResponse(
          success: false, error: 'Error fetching complaints: $e');
    }
  }

  /// Submit complaint to API
  Future<ApiResponse<Complaint>> submitComplaint({
    required String judul,
    required String deskripsi,
    required String alamat,
    required String kategori,
    required String pelaporNama,
    required String pelaporTelepon,
    required String pelaporNik,
    required String pelaporAlamat,
    List<File>? foto,
  }) async {

    print('Foto: 1');
    try {
      // Submit to API
      var apiResponse = await ApiService.instance.postComplaint(
        judul: judul,
        deskripsi: deskripsi,
        alamat: alamat,
        kategori: kategori,
        pelaporNama: pelaporNama,
        pelaporTelepon: pelaporTelepon,
        pelaporNik: pelaporNik,
        pelaporAlamat: pelaporAlamat,
        foto: foto,
      );
 print('API Response: ${apiResponse.data}');
      if (apiResponse.success && apiResponse.data != null) {
        // Create complaint object from API response
        var complaint = Complaint(
          id: apiResponse.data!['id'] ??
              DateTime.now().millisecondsSinceEpoch.toString(),
          kodeLaporan: apiResponse.data!['kode_laporan'] ??
              'LP${DateTime.now().millisecondsSinceEpoch}',
          title: judul,
          description: deskripsi,
          kategori: kategori,
          alamat: alamat,
          status: 'LAPOR',
          prioritas: 'SEDANG',
          pelaporNama: pelaporNama,
          pelaporTelepon: pelaporTelepon,
          pelaporNik: pelaporNik,
          pelaporAlamat: pelaporAlamat,
          createdAt: DateTime.now(),
          updatedAt: DateTime.now(),
          namaKategori: kategori,
          createdAtFormatted: DateTime.now().toString(),
          updatedAtFormatted: DateTime.now().toString(),
          filesCount: foto?.length ?? 0,
        );

        // Add to local list and notify listeners
        // await addComplaint(complaint);

        return ApiResponse(success: true, data: complaint);
      } else {
        return ApiResponse(success: false, error: apiResponse.error);
      }
    } catch (e) {
      return ApiResponse(
          success: false, error: 'Error submitting complaint: $e');
    }
  }

  /// Simulate receiving a new complaint (for testing)
  Future<void> simulateNewComplaint() async {
    final random = Random();
    final categories = ['Technical', 'Service', 'Billing', 'General'];
    final priorities = ['Low', 'Medium', 'High', 'Urgent'];

    final complaint = Complaint(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      kodeLaporan: 'LP${DateTime.now().millisecondsSinceEpoch}',
      title: 'New Complaint #${_complaints.length + 1}',
      description:
          'This is a simulated complaint for testing the bubble overlay feature.',
      kategori: categories[random.nextInt(categories.length)],
      alamat: 'Simulated Address',
      status: 'LAPOR',
      prioritas: priorities[random.nextInt(priorities.length)],
      pelaporNama: 'Simulated User',
      pelaporTelepon: '081234567890',
      pelaporNik: '1234567890123456',
      pelaporAlamat: 'Simulated Address',
      createdAt: DateTime.now(),
      updatedAt: DateTime.now(),
      namaKategori: categories[random.nextInt(categories.length)],
      createdAtFormatted: DateTime.now().toString(),
      updatedAtFormatted: DateTime.now().toString(),
      filesCount: 0,
    );

    await addComplaint(complaint);
  }

  /// Show bubble for new complaint
  Future<void> _showBubbleForNewComplaint() async {
    final pendingCount = pendingComplaintsCount;
    await BubbleOverlayService.instance
        .showBubble(complaintCount: pendingCount);
  }

  /// Update complaint status
  Future<void> updateComplaintStatus(
      String complaintId, String newStatus) async {
    final index = _complaints.indexWhere((c) => c.id == complaintId);
    if (index != -1) {
      final updatedComplaint = Complaint(
        id: _complaints[index].id,
        kodeLaporan: _complaints[index].kodeLaporan,
        title: _complaints[index].title,
        description: _complaints[index].description,
        kategori: _complaints[index].kategori,
        alamat: _complaints[index].alamat,
        status: newStatus,
        prioritas: _complaints[index].prioritas,
        pelaporNama: _complaints[index].pelaporNama,
        pelaporTelepon: _complaints[index].pelaporTelepon,
        pelaporNik: _complaints[index].pelaporNik,
        pelaporAlamat: _complaints[index].pelaporAlamat,
        createdAt: _complaints[index].createdAt,
        updatedAt: DateTime.now(),
        namaKategori: _complaints[index].namaKategori,
        createdAtFormatted: _complaints[index].createdAtFormatted,
        updatedAtFormatted: DateTime.now().toString(),
        filesCount: _complaints[index].filesCount,
      );

      _complaints[index] = updatedComplaint;
      _complaintsController.add(List.unmodifiable(_complaints));

      // Update bubble count
      await BubbleOverlayService.instance
          .updateComplaintCount(pendingComplaintsCount);
    }
  }

  /// Hide bubble overlay
  Future<void> hideBubble() async {
    await BubbleOverlayService.instance.hideBubble();
  }

  /// Dispose resources
  void dispose() {
    _complaintsController.close();
    _newComplaintController.close();
  }
}
