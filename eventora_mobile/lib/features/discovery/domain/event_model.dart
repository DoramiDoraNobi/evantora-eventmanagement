class Event {
  final int id;
  final String title;
  final String slug;
  final String description;
  final String date;
  final String venue;
  final int capacity;
  final double price;

  Event({
    required this.id,
    required this.title,
    required this.slug,
    required this.description,
    required this.date,
    required this.venue,
    required this.capacity,
    required this.price,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    return Event(
      id: json['id'],
      title: json['title'],
      slug: json['slug'],
      description: json['description'] ?? '',
      date: json['date'],
      venue: json['venue'],
      capacity: json['capacity'] ?? 0,
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
    );
  }
}
