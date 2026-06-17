import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_constants.dart';
import '../domain/ticket_model.dart';

final ticketRepositoryProvider = Provider<TicketRepository>((ref) {
  return TicketRepository(ref.watch(dioProvider));
});

class TicketRepository {
  final Dio _dio;

  TicketRepository(this._dio);

  Future<Ticket> purchaseTicket(int eventId) async {
    try {
      final response = await _dio.post('${ApiConstants.events}/$eventId/tickets');
      return Ticket.fromJson(response.data['data']);
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to purchase ticket');
    }
  }

  Future<List<Ticket>> getMyTickets() async {
    try {
      final response = await _dio.get('${ApiConstants.baseUrl}/tickets');
      final data = response.data['data'] as List;
      return data.map((e) => Ticket.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to load tickets');
    }
  }
}
