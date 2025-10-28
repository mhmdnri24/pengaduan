import 'dart:async';
import 'dart:io';
import '../models/complaint.dart';
import 'api_service.dart';

class EmergencyService {
  static EmergencyService? _instance;
  static final List<Complaint> _emergencies = [];
  static final StreamController<List<Complaint>> _emergenciesController =
      StreamController<List<Complaint>>.broadcast();
  static final StreamController<Complaint> _newEmergencyController =
      StreamController<Complaint>.broadcast();

  EmergencyService._internal();

  static EmergencyService get instance {
    _instance ??= EmergencyService._internal();
    return _instance!;
  }

  /// Stream of all emergencies
  Stream<List<Complaint>> get emergenciesStream => _emergenciesController.stream;

  /// Stream of new emergencies
  Stream<Complaint> get newEmergencyStream => _newEmergencyController.stream;

  /// Get all emergencies
  List<Complaint> get emergencies => List.unmodifiable(_emergencies);

  /// Get pending emergencies count
  int get pendingEmergenciesCount => _emergencies.length;

  /// Initialize the service
  Future<void> initialize() async {
    // Listen for new emergencies
    _newEmergencyController.stream.listen((emergency) {
      // Handle new emergency notifications
    });
  }

  /// Add a new emergency (simulate receiving from server)
  Future<void> addEmergency(Complaint emergency) async {
    _emergencies.add(emergency);
    _emergenciesController.add(List.unmodifiable(_emergencies));
    _newEmergencyController.add(emergency);
  }

  /// Submit emergency report to API
  Future<ApiResponse<Complaint>> submitEmergencyReport({
    required String kategori,
    required String alamat,
    required String pelaporNama,
    required String pelaporTelepon,
    required String pelaporNik,
    required String pelaporAlamat,
    required String lat,
    required String lng,
    List<File>? foto,
  }) async {
    try {
      // Submit to API with emergency-specific parameters
      var apiResponse = await ApiService.instance.postEmergencyReport(
        kategori: kategori,
        alamat: alamat,
        pelaporNama: pelaporNama,
        pelaporTelepon: pelaporTelepon,
        pelaporNik: pelaporNik,
        pelaporAlamat: pelaporAlamat,
        foto: foto,
        latitude: lat,
        longitude: lng,
      );

      if (apiResponse.success && apiResponse.data != null) {
        // Create emergency complaint object from API response
        var emergency = Complaint(
          id: apiResponse.data!['id'] ??
              DateTime.now().millisecondsSinceEpoch.toString(),
          kodeLaporan: apiResponse.data!['kode_laporan'] ??
              'EM${DateTime.now().millisecondsSinceEpoch}',
          title: 'Darurat',
          description: 'Laporan darurat - ${kategori}',
          kategori: kategori,
          alamat: alamat,
          status: 'DARURAT',
          prioritas: 'TINGGI',
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
        await addEmergency(emergency);

        return ApiResponse(success: true, data: emergency);
      } else {
        return ApiResponse(success: false, error: apiResponse.error);
      }
    } catch (e) {
      return ApiResponse(
          success: false, error: 'Error submitting emergency report: $e');
    }
  }
}
