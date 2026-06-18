class EventTicket {
  final int id;
  final String name;
  final String description;
  final double price;
  final int quantity;
  final int minPerOrder;
  final int maxPerOrder;

  EventTicket({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.quantity,
    required this.minPerOrder,
    required this.maxPerOrder,
  });

  factory EventTicket.fromJson(Map<String, dynamic> json) {
    return EventTicket(
      id: json['id'] ?? 0,
      name: json['name'] ?? 'Ticket',
      description: json['description'] ?? '',
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      quantity: json['quantity'] ?? 0,
      minPerOrder: json['min_per_order'] ?? 1,
      maxPerOrder: json['max_per_order'] ?? 10,
    );
  }
}

class Event {
  final int id;
  final String title;
  final String slug;
  final String description;
  final String date;
  final String venue;
  final int capacity;
  final double price;
  final List<EventTicket> tickets;

  Event({
    required this.id,
    required this.title,
    required this.slug,
    required this.description,
    required this.date,
    required this.venue,
    required this.capacity,
    required this.price,
    required this.tickets,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    final ticketsList = json['tickets'] as List? ?? [];
    final parsedTickets = ticketsList.map((t) => EventTicket.fromJson(t)).toList();
    
    return Event(
      id: json['id'] ?? 0,
      title: json['title'] ?? 'Untitled',
      slug: json['slug'] ?? '',
      description: json['description'] ?? '',
      date: json['start_date'] ?? json['date'] ?? '',
      venue: json['venue_name'] ?? json['venue'] ?? 'TBA',
      capacity: json['capacity'] ?? 0,
      price: parsedTickets.isNotEmpty ? parsedTickets.first.price : ((json['price'] as num?)?.toDouble() ?? 0.0),
      tickets: parsedTickets,
    );
  }
}
