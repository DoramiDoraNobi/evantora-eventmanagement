import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/event_repository.dart';
import '../domain/event_model.dart';

final eventsProvider = FutureProvider<List<Event>>((ref) async {
  final repository = ref.watch(eventRepositoryProvider);
  return await repository.getEvents();
});

final eventDetailProvider = FutureProvider.family<Event, int>((ref, id) async {
  final repository = ref.watch(eventRepositoryProvider);
  return await repository.getEventById(id);
});
