import '../../discovery/domain/event_model.dart';

class Ticket {
  final int id;
  final String ticketCode;
  final String status;
  final double price;
  final Event? event;

  Ticket({
    required this.id,
    required this.ticketCode,
    required this.status,
    required this.price,
    this.event,
  });

  factory Ticket.fromJson(Map<String, dynamic> json) {
    return Ticket(
      id: json['id'] ?? 0,
      ticketCode: json['ticket_number'] ?? json['ticket_code'] ?? 'N/A',
      status: json['status'] ?? 'unknown',
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      event: json['event'] != null ? Event.fromJson(json['event']) : null,
    );
  }
}
