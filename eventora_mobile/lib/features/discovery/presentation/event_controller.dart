import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/event_repository.dart';
import '../domain/event_model.dart';

class EventFilterNotifier extends Notifier<Map<String, dynamic>> {
  @override
  Map<String, dynamic> build() => {};

  void updateFilter(Map<String, dynamic> newFilter) {
    state = newFilter;
  }
}

final eventFilterProvider = NotifierProvider<EventFilterNotifier, Map<String, dynamic>>(() {
  return EventFilterNotifier();
});

final eventsProvider = FutureProvider<List<Event>>((ref) async {
  final repository = ref.watch(eventRepositoryProvider);
  final filters = ref.watch(eventFilterProvider);
  return await repository.getEvents(filters: filters);
});

final eventDetailProvider = FutureProvider.family<Event, int>((ref, id) async {
  final repository = ref.watch(eventRepositoryProvider);
  return await repository.getEventById(id);
});
