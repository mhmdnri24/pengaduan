// Example usage of the Complaint List API
// This demonstrates how to use the new API implementation with pagination, filtering, and rate limiting

import 'package:flutter/material.dart';
import '../models/complaint.dart';
import '../services/complaint_service.dart';

class ComplaintListExample extends StatefulWidget {
  const ComplaintListExample({super.key});

  @override
  State<ComplaintListExample> createState() => _ComplaintListExampleState();
}

class _ComplaintListExampleState extends State<ComplaintListExample> {
  final ComplaintService _complaintService = ComplaintService.instance;

  ComplaintListResponse? _response;
  bool _isLoading = false;
  String? _error;

  // Pagination state
  int _currentPage = 1;
  final int _itemsPerPage = 10;

  // Filter state
  String? _selectedStatus;
  String? _selectedKategori;
  String? _searchQuery;

  @override
  void initState() {
    super.initState();
    _fetchComplaints();
  }

  // Basic usage - Fetch complaints with default parameters
  Future<void> _fetchComplaints() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final result = await _complaintService.fetchComplaints(
      page: _currentPage,
      limit: _itemsPerPage,
      status: _selectedStatus,
      kategori: _selectedKategori,
      search: _searchQuery,
    );

    setState(() {
      _isLoading = false;
      if (result.success && result.data != null) {
        _response = result.data;
      } else {
        _error = result.error;
      }
    });
  }

  // Example 1: Fetch with status filter
  Future<void> _fetchByStatus(String status) async {
    setState(() {
      _selectedStatus = status;
      _currentPage = 1; // Reset to first page
    });
    await _fetchComplaints();
  }

  // Example 2: Fetch with category filter
  Future<void> _fetchByCategory(String kategori) async {
    setState(() {
      _selectedKategori = kategori;
      _currentPage = 1;
    });
    await _fetchComplaints();
  }

  // Example 3: Search complaints
  Future<void> _searchComplaints(String query) async {
    setState(() {
      _searchQuery = query;
      _currentPage = 1;
    });
    await _fetchComplaints();
  }

  // Example 4: Load next page
  Future<void> _loadNextPage() async {
    if (_response?.pagination.hasNext ?? false) {
      setState(() {
        _currentPage++;
      });
      await _fetchComplaints();
    }
  }

  // Example 5: Load previous page
  Future<void> _loadPreviousPage() async {
    if (_response?.pagination.hasPrev ?? false) {
      setState(() {
        _currentPage--;
      });
      await _fetchComplaints();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Complaint List API Example'),
      ),
      body: Column(
        children: [
          // Filters Section
          _buildFiltersSection(),

          // Rate Limit Info
          if (_response?.rateLimit != null) _buildRateLimitInfo(),

          // Pagination Info
          if (_response?.pagination != null) _buildPaginationInfo(),

          // Complaints List
          Expanded(
            child: _buildComplaintsList(),
          ),

          // Pagination Controls
          if (_response?.pagination != null) _buildPaginationControls(),
        ],
      ),
    );
  }

  Widget _buildFiltersSection() {
    return Card(
      margin: const EdgeInsets.all(8),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Filters:',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              children: [
                ChoiceChip(
                  label: const Text('All'),
                  selected: _selectedStatus == null,
                  onSelected: (_) {
                    setState(() => _selectedStatus = null);
                    _fetchComplaints();
                  },
                ),
                ChoiceChip(
                  label: const Text('LAPOR'),
                  selected: _selectedStatus == 'LAPOR',
                  onSelected: (_) => _fetchByStatus('LAPOR'),
                ),
                ChoiceChip(
                  label: const Text('PROSES'),
                  selected: _selectedStatus == 'PROSES',
                  onSelected: (_) => _fetchByStatus('PROSES'),
                ),
                ChoiceChip(
                  label: const Text('SELESAI'),
                  selected: _selectedStatus == 'SELESAI',
                  onSelected: (_) => _fetchByStatus('SELESAI'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRateLimitInfo() {
    final rateLimit = _response!.rateLimit;
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 8),
      color: Colors.blue.shade50,
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceAround,
          children: [
            Text('Rate Limit - Minute: ${rateLimit.remainingMinute}'),
            Text('Hour: ${rateLimit.remainingHour}'),
          ],
        ),
      ),
    );
  }

  Widget _buildPaginationInfo() {
    final pagination = _response!.pagination;
    return Card(
      margin: const EdgeInsets.all(8),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text('Page ${pagination.currentPage} of ${pagination.totalPages}'),
            Text('Total: ${pagination.totalRecords} records'),
          ],
        ),
      ),
    );
  }

  Widget _buildComplaintsList() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(child: Text('Error: $_error'));
    }

    if (_response == null || _response!.pelaporan.isEmpty) {
      return const Center(child: Text('No complaints found'));
    }

    return ListView.builder(
      itemCount: _response!.pelaporan.length,
      itemBuilder: (context, index) {
        final complaint = _response!.pelaporan[index];
        return ListTile(
          title: Text(complaint.title),
          subtitle: Text(complaint.description),
          trailing: Chip(
            label: Text(
              complaint.status,
              style: const TextStyle(fontSize: 10),
            ),
          ),
        );
      },
    );
  }

  Widget _buildPaginationControls() {
    final pagination = _response!.pagination;
    return Card(
      margin: const EdgeInsets.all(8),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            ElevatedButton(
              onPressed: pagination.hasPrev ? _loadPreviousPage : null,
              child: const Text('Previous'),
            ),
            Text('${pagination.currentPage}/${pagination.totalPages}'),
            ElevatedButton(
              onPressed: pagination.hasNext ? _loadNextPage : null,
              child: const Text('Next'),
            ),
          ],
        ),
      ),
    );
  }
}

// =============================================================================
// USAGE EXAMPLES IN CODE
// =============================================================================

// Example 1: Basic fetch with default parameters
void example1FetchDefault() async {
  final service = ComplaintService.instance;
  final result = await service.fetchComplaints();

  if (result.success && result.data != null) {
    final response = result.data!;
    print('Status: ${response.status}');
    print('Message: ${response.message}');
    print('Total complaints: ${response.pagination.totalRecords}');
    print(
        'Rate limit remaining (minute): ${response.rateLimit.remainingMinute}');

    // Access complaints
    for (var complaint in response.pelaporan) {
      print('Complaint: ${complaint.title} - ${complaint.status}');
    }
  }
}

// Example 2: Fetch with status filter
void example2FetchWithStatus() async {
  final service = ComplaintService.instance;
  final result = await service.fetchComplaints(
    status: 'LAPOR',
    page: 1,
    limit: 10,
  );

  if (result.success && result.data != null) {
    print(
        'Filtered complaints with status LAPOR: ${result.data!.pelaporan.length}');
  }
}

// Example 3: Fetch with multiple filters
void example3FetchWithMultipleFilters() async {
  final service = ComplaintService.instance;
  final result = await service.fetchComplaints(
    status: 'PROSES',
    kategori: 'infrastruktur',
    search: 'jalan',
    page: 1,
    limit: 20,
  );

  if (result.success && result.data != null) {
    final response = result.data!;
    print('Applied filters:');
    print('- Status: ${response.filtersApplied.status}');
    print('- Category: ${response.filtersApplied.kategori}');
    print('- Search: ${response.filtersApplied.search}');
  }
}

// Example 4: Pagination handling
void example4Pagination() async {
  final service = ComplaintService.instance;
  int currentPage = 1;

  while (true) {
    final result = await service.fetchComplaints(
      page: currentPage,
      limit: 10,
    );

    if (result.success && result.data != null) {
      final response = result.data!;
      print('Page $currentPage: ${response.pelaporan.length} complaints');

      // Check if there's a next page
      if (!response.pagination.hasNext) {
        print('No more pages');
        break;
      }

      currentPage++;
    } else {
      print('Error: ${result.error}');
      break;
    }
  }
}

// Example 5: Accessing complaint details with files
void example5ComplaintDetails() async {
  final service = ComplaintService.instance;
  final result = await service.fetchComplaints(page: 1, limit: 1);

  if (result.success &&
      result.data != null &&
      result.data!.pelaporan.isNotEmpty) {
    final complaint = result.data!.pelaporan.first;

    print('Complaint Details:');
    print('- ID: ${complaint.id}');
    print('- Code: ${complaint.kodeLaporan}');
    print('- Title: ${complaint.title}');
    print('- Description: ${complaint.description}');
    print('- Status: ${complaint.status}');
    print('- Priority: ${complaint.prioritas}');
    print('- Category: ${complaint.namaKategori}');
    print('- Reporter: ${complaint.pelaporNama}');
    print('- Phone: ${complaint.pelaporTelepon}');
    print('- Created: ${complaint.createdAtFormatted}');
    print('- Files count: ${complaint.filesCount}');

    // Access files if available
    if (complaint.files != null && complaint.files!.isNotEmpty) {
      print('Files:');
      for (var file in complaint.files!) {
        print('  - ${file.fileName}: ${file.fileUrl}');
      }
    }
  }
}

// Example 6: Rate limit monitoring
void example6RateLimitMonitoring() async {
  final service = ComplaintService.instance;
  final result = await service.fetchComplaints();

  if (result.success && result.data != null) {
    final rateLimit = result.data!.rateLimit;

    print('Rate Limit Status:');
    print('- Remaining requests this minute: ${rateLimit.remainingMinute}');
    print('- Remaining requests this hour: ${rateLimit.remainingHour}');

    // Warning if approaching limit
    if (rateLimit.remainingMinute < 10) {
      print('WARNING: Approaching rate limit!');
    }
  }
}
