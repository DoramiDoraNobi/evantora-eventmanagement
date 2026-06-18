import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/organizer_repository.dart';
import '../domain/organizer_models.dart';

/// Tracks the currently selected organization ID.
/// Set during login based on user.organizations[0].id.
final selectedOrgIdProvider = NotifierProvider<SelectedOrgIdNotifier, int>(() {
  return SelectedOrgIdNotifier();
});

class SelectedOrgIdNotifier extends Notifier<int> {
  @override
  int build() => 0;

  void select(int id) {
    state = id;
  }
}

final dashboardProvider = FutureProvider<DashboardStats>((ref) async {
  final orgId = ref.watch(selectedOrgIdProvider);
  if (orgId == 0) throw Exception('No organization selected');
  final repository = ref.watch(organizerRepositoryProvider);
  return await repository.getDashboard(orgId);
});

final organizerEventsProvider = FutureProvider<List<OrganizerEvent>>((ref) async {
  final orgId = ref.watch(selectedOrgIdProvider);
  if (orgId == 0) throw Exception('No organization selected');
  final repository = ref.watch(organizerRepositoryProvider);
  return await repository.getEvents(orgId);
});
