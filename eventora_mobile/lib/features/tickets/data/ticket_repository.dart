import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_constants.dart';
import '../domain/ticket_model.dart';

final ticketRepositoryProvider = Provider<TicketRepository>((ref) {
  return TicketRepository(ref.watch(dioProvider));
});

class TicketRepository {
  final Dio _dio;

  TicketRepository(this._dio);

  Future<Ticket> purchaseTicket(int eventId, Map<int, int> selectedTickets) async {
    try {
      if (selectedTickets.isEmpty) {
        throw Exception('No tickets selected');
      }

      // 1. Fetch current user
      final meResponse = await _dio.get('${ApiConstants.baseUrl}/auth/me');
      final user = meResponse.data != null ? meResponse.data['user'] : null;

      if (user == null) {
        throw Exception('User session not found. Please log in again.');
      }

      // 2. Build tickets payload
      final ticketsPayload = selectedTickets.entries.map((e) => {
        'ticket_id': e.key,
        'quantity': e.value
      }).toList();

      // 3. Build attendees payload (one for each ticket purchased)
      final totalTickets = selectedTickets.values.fold(0, (sum, q) => sum + q);
      final attendeesPayload = List.generate(totalTickets, (_) => {
        'name': user['name'] ?? 'Buyer',
        'email': user['email'] ?? 'buyer@example.com'
      });

      // 4. Post to checkout
      final payload = {
        'buyer_name': user['name'] ?? 'Buyer',
        'buyer_email': user['email'] ?? 'buyer@example.com',
        'tickets': ticketsPayload,
        'attendees': attendeesPayload
      };

      final response = await _dio.post('${ApiConstants.events}/$eventId/checkout', data: payload);
      final responseData = response.data;
      
      // If Stripe client_secret is returned, handle the native payment sheet
      if (responseData['client_secret'] != null) {
        
        Stripe.publishableKey = responseData['publishable_key'];
        if (responseData['stripe_account'] != null) {
          Stripe.stripeAccountId = responseData['stripe_account'];
        }

        await Stripe.instance.initPaymentSheet(
          paymentSheetParameters: SetupPaymentSheetParameters(
            paymentIntentClientSecret: responseData['client_secret'],
            merchantDisplayName: 'Eventora',
          ),
        );

        await Stripe.instance.presentPaymentSheet();

        // If presentPaymentSheet doesn't throw, the payment succeeded
        // Verify with the backend
        final orderNumber = responseData['order']['order_number'];
        final verifyPayload = {
           'payment_intent_id': responseData['client_secret'].split('_secret')[0],
        };
        await _dio.post('${ApiConstants.baseUrl}/orders/$orderNumber/verify-payment', data: verifyPayload);
      }

      // Return a dummy ticket or the first attendee's ticket
      final orderData = responseData['order'];
      if (orderData != null && orderData['attendees'] != null && orderData['attendees'].isNotEmpty) {
         final attendee = orderData['attendees'][0];
         return Ticket(
            id: attendee['id'] ?? 0,
            ticketCode: attendee['ticket_number'] ?? '',
            status: attendee['status'] ?? 'confirmed',
            price: 0.0,
         );
      }
      
      return Ticket(id: 0, ticketCode: 'DUMMY', status: 'confirmed', price: 0);
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to purchase ticket');
    }
  }

  Future<List<Ticket>> getMyTickets() async {
    try {
      final response = await _dio.get('${ApiConstants.baseUrl}${ApiConstants.buyerTickets}');
      final data = response.data['data'] as List;
      return data.map((e) => Ticket.fromJson(e)).toList();
    } on DioException catch (e) {
      throw Exception(e.response?.data['message'] ?? 'Failed to load tickets');
    }
  }
}
