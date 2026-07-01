import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_constants.dart';
import '../domain/organizer_models.dart';

final organizerRepositoryProvider = Provider<OrganizerRepository>((ref) {
  return OrganizerRepository(ref.watch(dioProvider));
});

class OrganizerRepository {
  final Dio _dio;

  OrganizerRepository(this._dio);

  Future<DashboardStats> getDashboard(int orgId) async {
    try {
      final response = await _dio.get(ApiConstants.organizerDashboard(orgId));
      return DashboardStats.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to load dashboard');
    }
  }

  Future<List<OrganizerEvent>> getEvents(int orgId) async {
    try {
      final response = await _dio.get(ApiConstants.organizerEvents(orgId));
      final data = response.data['data'] as List;
      return data.map((e) => OrganizerEvent.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to load events');
    }
  }

  Future<String> checkinTicket(int orgId, int eventId, String ticketCode) async {
    try {
      final response = await _dio.post(
        ApiConstants.organizerCheckin(orgId),
        data: {'qr_code': ticketCode, 'event_id': eventId},
      );
      return response.data['message'] ?? 'Check-in successful';
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to check in ticket');
    }
  }

  Future<Map<String, dynamic>> getPayouts(int orgId) async {
    try {
      final response = await _dio.get(ApiConstants.organizerPayouts(orgId));
      return response.data['data'];
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to load payouts');
    }
  }

  Future<void> requestPayout(int orgId, Map<String, dynamic> payload) async {
    try {
      await _dio.post(
        ApiConstants.organizerPayouts(orgId),
        data: payload,
      );
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to request payout');
    }
  }

  Future<List<dynamic>> downloadTicketsForOffline(int orgId, int eventId) async {
    try {
      final response = await _dio.get(ApiConstants.organizerTicketsSync(orgId, eventId));
      return response.data['data'] as List<dynamic>;
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to download tickets');
    }
  }

  Future<void> syncOfflineCheckins(int orgId, int eventId, List<Map<String, dynamic>> scannedTickets) async {
    try {
      await _dio.post(
        ApiConstants.organizerTicketsSync(orgId, eventId),
        data: {'scanned_tickets': scannedTickets},
      );
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to sync offline checkins');
    }
  }
}
