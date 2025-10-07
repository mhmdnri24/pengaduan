import 'dart:async';
import 'dart:math';
import '../models/complaint.dart';
import 'bubble_overlay_service.dart';

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
  int get pendingComplaintsCount => 
      _complaints.where((c) => c.status == 'pending').length;

  /// Initialize the service
  Future<void> initialize() async {
    // Start the bubble overlay service
    await BubbleOverlayService.instance.startService();
    
    // Listen for new complaints and show bubble
    _newComplaintController.stream.listen((complaint) {
      _showBubbleForNewComplaint();
    });
  }

  /// Add a new complaint (simulate receiving from server)
  Future<void> addComplaint(Complaint complaint) async {
    _complaints.add(complaint);
    _complaintsController.add(List.unmodifiable(_complaints));
    _newComplaintController.add(complaint);
  }

  /// Simulate receiving a new complaint (for testing)
  Future<void> simulateNewComplaint() async {
    final random = Random();
    final categories = ['Technical', 'Service', 'Billing', 'General'];
    final priorities = ['Low', 'Medium', 'High', 'Urgent'];
    
    final complaint = Complaint(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      title: 'New Complaint #${_complaints.length + 1}',
      description: 'This is a simulated complaint for testing the bubble overlay feature.',
      status: 'pending',
      createdAt: DateTime.now(),
      category: categories[random.nextInt(categories.length)],
      priority: priorities[random.nextInt(priorities.length)],
    );

    await addComplaint(complaint);
  }

  /// Show bubble for new complaint
  Future<void> _showBubbleForNewComplaint() async {
    final pendingCount = pendingComplaintsCount;
    await BubbleOverlayService.instance.showBubble(complaintCount: pendingCount);
  }

  /// Update complaint status
  Future<void> updateComplaintStatus(String complaintId, String newStatus) async {
    final index = _complaints.indexWhere((c) => c.id == complaintId);
    if (index != -1) {
      final updatedComplaint = Complaint(
        id: _complaints[index].id,
        title: _complaints[index].title,
        description: _complaints[index].description,
        status: newStatus,
        createdAt: _complaints[index].createdAt,
        category: _complaints[index].category,
        priority: _complaints[index].priority,
      );
      
      _complaints[index] = updatedComplaint;
      _complaintsController.add(List.unmodifiable(_complaints));
      
      // Update bubble count
      await BubbleOverlayService.instance.updateComplaintCount(pendingComplaintsCount);
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
