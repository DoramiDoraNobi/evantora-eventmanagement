class DashboardStats {
  final int totalEvents;
  final int totalTicketsSold;
  final double totalRevenue;
  final int upcomingEvents;

  DashboardStats({
    required this.totalEvents,
    required this.totalTicketsSold,
    required this.totalRevenue,
    required this.upcomingEvents,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) {
    return DashboardStats(
      totalEvents: json['total_events'] ?? 0,
      totalTicketsSold: json['total_tickets_sold'] ?? 0,
      totalRevenue: (json['total_revenue'] as num?)?.toDouble() ?? 0.0,
      upcomingEvents: json['upcoming_events'] ?? 0,
    );
  }
}

class OrganizerEvent {
  final int id;
  final String title;
  final String slug;
  final String date;
  final String venue;
  final int capacity;
  final int ticketsSold;
  final String status;

  OrganizerEvent({
    required this.id,
    required this.title,
    required this.slug,
    required this.date,
    required this.venue,
    required this.capacity,
    required this.ticketsSold,
    required this.status,
  });

  factory OrganizerEvent.fromJson(Map<String, dynamic> json) {
    return OrganizerEvent(
      id: json['id'],
      title: json['title'],
      slug: json['slug'] ?? '',
      date: json['date'],
      venue: json['venue'],
      capacity: json['capacity'] ?? 0,
      ticketsSold: json['tickets_sold'] ?? json['tickets_count'] ?? 0,
      status: json['status'] ?? 'active',
    );
  }

  double get occupancyRate =>
      capacity > 0 ? (ticketsSold / capacity) * 100 : 0.0;
}
