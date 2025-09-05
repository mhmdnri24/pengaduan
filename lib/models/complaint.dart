class Complaint {
  final String id;
  final String title;
  final String description;
  final String status;
  final DateTime createdAt;
  final String? category;
  final String? priority;

  Complaint({
    required this.id,
    required this.title,
    required this.description,
    required this.status,
    required this.createdAt,
    this.category,
    this.priority,
  });

  factory Complaint.fromJson(Map<String, dynamic> json) {
    return Complaint(
      id: json['id'] ?? '',
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      status: json['status'] ?? 'pending',
      createdAt: DateTime.parse(json['createdAt'] ?? DateTime.now().toIso8601String()),
      category: json['category'],
      priority: json['priority'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'status': status,
      'createdAt': createdAt.toIso8601String(),
      'category': category,
      'priority': priority,
    };
  }
}
