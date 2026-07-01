import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/ticket_repository.dart';
import '../domain/ticket_model.dart';

final myTicketsProvider = FutureProvider<List<Ticket>>((ref) async {
  final repository = ref.watch(ticketRepositoryProvider);
  return await repository.getMyTickets();
});

final purchaseTicketProvider = AsyncNotifierProvider<PurchaseTicketController, Ticket?>(() {
  return PurchaseTicketController();
});

class PurchaseTicketController extends AsyncNotifier<Ticket?> {
  @override
  FutureOr<Ticket?> build() {
    return null;
  }

  Future<bool> purchase(int eventId, Map<int, int> selectedTickets, String paymentMethod) async {
    state = const AsyncValue.loading();
    try {
      final repository = ref.read(ticketRepositoryProvider);
      final ticket = await repository.purchaseTicket(eventId, selectedTickets, paymentMethod);
      state = AsyncValue.data(ticket);
      ref.invalidate(myTicketsProvider);
      return true;
    } catch (e, st) {
      state = AsyncValue.error(e, st);
      return false;
    }
  }
}
