import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_constants.dart';
import '../domain/event_model.dart';

final eventRepositoryProvider = Provider<EventRepository>((ref) {
  return EventRepository(ref.watch(dioProvider));
});

class EventRepository {
  final Dio _dio;

  EventRepository(this._dio);

  Future<List<Event>> getEvents({Map<String, dynamic>? filters}) async {
    try {
      // Use the generic /events endpoint without auth for public discovery
      final response = await _dio.get(ApiConstants.events, queryParameters: filters);
      
      // Assuming Laravel API pagination wrapper, data is in ['data']
      final data = response.data['data'] as List;
      return data.map((e) => Event.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to load events');
    }
  }

  Future<Event> getEventById(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.events}/$id');
      return Event.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to load event');
    }
  }
}
