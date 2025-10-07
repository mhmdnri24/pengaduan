// lib/controllers/landing_controller.dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
class LandingController {
  final TextEditingController phoneController = TextEditingController();
  String countryCode = '+62';
  bool isSending = false;

  Future<void> sendOtp(BuildContext context, VoidCallback onStart, VoidCallback onDone) async {
    final phone = phoneController.text.trim();
    // if (phone.isEmpty) {
    //   ScaffoldMessenger.of(context).showSnackBar(
    //     const SnackBar(content: Text('Masukkan nomor terlebih dahulu1')),
    //   );
    //   return;
    // }
  
    onStart(); // ubah state ke "loading"
 

try {
  print('Sending OTP to $countryCode $phone');

  final uri = Uri.parse('https://dashboard.nusakoding.com/api/v1/masyarakat/login');
  final resp = await http.post(
    uri,
    headers: {
      'X-API-Key': 'API_HIJ973D4Nmgdbhy42',
      'Origin': 'https://dashboard.nusakoding.com',
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: {
      'nik': '1234567890123455', // pastikan nama field sesuai API
    },
    encoding: Encoding.getByName('utf-8'),
  );

  print('Status: ${resp.statusCode}');
  print('Body: ${resp.body}');

  if (resp.statusCode == 200 || resp.statusCode == 201) {
    Navigator.of(context).pop();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('OTP dikirim ke $countryCode $phone')),
    );
  } else {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Gagal mengirim OTP: ${resp.statusCode} - ${resp.body}')),
    );
  }
} catch (e) {
  ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(content: Text('Terjadi kesalahan: $e')),
  );
} finally {
  onDone();
}

  }
}
