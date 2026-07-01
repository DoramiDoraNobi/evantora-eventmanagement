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
  final bool isTaxable;
  final double taxRate;
  final String taxName;
  final double platformFeePercent;
  final double platformFeeFixed;

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
    this.isTaxable = false,
    this.taxRate = 0.0,
    this.taxName = 'Tax',
    this.platformFeePercent = 0.0,
    this.platformFeeFixed = 0.0,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    final ticketsList = json['tickets'] as List? ?? [];
    final parsedTickets = ticketsList.map((t) => EventTicket.fromJson(t)).toList();
    
    final platformFees = json['platform_fees'] as Map<String, dynamic>? ?? {};

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
      isTaxable: json['is_taxable'] ?? false,
      taxRate: (json['tax_rate'] as num?)?.toDouble() ?? 0.0,
      taxName: json['tax_name'] ?? 'Tax',
      platformFeePercent: (platformFees['percent'] as num?)?.toDouble() ?? 5.0,
      platformFeeFixed: (platformFees['fixed'] as num?)?.toDouble() ?? 0.0,
    );
  }
}
