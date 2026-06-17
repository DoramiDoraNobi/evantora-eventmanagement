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

  Future<String> checkinTicket(int orgId, String ticketCode) async {
    try {
      final response = await _dio.post(
        ApiConstants.organizerCheckin(orgId),
        data: {'ticket_code': ticketCode},
      );
      return response.data['message'] ?? 'Check-in successful';
    } on DioException catch (e) {
      throw Exception(
          e.response?.data['message'] ?? 'Failed to check in ticket');
    }
  }
}
