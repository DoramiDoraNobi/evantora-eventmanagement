import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'event_controller.dart';
import 'filter_sheet.dart';
import '../../auth/presentation/auth_controller.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsyncValue = ref.watch(eventsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Discover Events'),
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                useSafeArea: true,
                builder: (context) => const FilterSheet(),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              ref.read(authControllerProvider.notifier).logout();
              context.go('/login');
            },
          ),
        ],
      ),
      body: eventsAsyncValue.when(
        data: (events) {
          if (events.isEmpty) {
            return const Center(child: Text('No events found.'));
          }
          return RefreshIndicator(
            onRefresh: () async {
              // Refresh events list
              ref.invalidate(eventsProvider);
            },
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: events.length + 1,
              itemBuilder: (context, index) {
                if (index == 0) {
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Popular Categories',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 40,
                        child: ListView(
                          scrollDirection: Axis.horizontal,
                          children: [
                            _buildCategoryChip(context, 'Music', Icons.music_note),
                            _buildCategoryChip(context, 'Technology', Icons.computer),
                            _buildCategoryChip(context, 'Workshop', Icons.build),
                            _buildCategoryChip(context, 'Business', Icons.business),
                            _buildCategoryChip(context, 'Art', Icons.brush),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'Upcoming Events',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 12),
                    ],
                  );
                }

                final event = events[index - 1];
                return Card(
                  margin: const EdgeInsets.only(bottom: 16),
                  clipBehavior: Clip.antiAlias,
                  child: InkWell(
                    onTap: () => context.go('/event/${event.id}'),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Hero Image
                        Hero(
                          tag: 'event-image-${event.id}',
                          child: CachedNetworkImage(
                            imageUrl: 'https://picsum.photos/seed/${event.id}/800/400',
                            height: 160,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Container(
                              height: 160,
                              color: Theme.of(context).colorScheme.surfaceContainerHighest,
                              child: const Center(child: CircularProgressIndicator()),
                            ),
                            errorWidget: (context, url, error) => Container(
                              height: 160,
                              color: Theme.of(context).colorScheme.primaryContainer,
                              child: Icon(
                                Icons.image,
                                size: 64,
                                color: Theme.of(context).colorScheme.onPrimaryContainer.withValues(alpha: 0.5),
                              ),
                            ),
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                event.title,
                                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                      fontWeight: FontWeight.bold,
                                    ),
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Icon(Icons.calendar_today, size: 16, color: Theme.of(context).colorScheme.secondary),
                                  const SizedBox(width: 8),
                                  Text(
                                    event.date,
                                    style: Theme.of(context).textTheme.bodyMedium,
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Icon(Icons.location_on, size: 16, color: Theme.of(context).colorScheme.secondary),
                                  const SizedBox(width: 8),
                                  Text(
                                    event.venue,
                                    style: Theme.of(context).textTheme.bodyMedium,
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              Text(
                                '\$${event.price.toStringAsFixed(2)}',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                      color: Theme.of(context).colorScheme.primary,
                                      fontWeight: FontWeight.bold,
                                    ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              Text('Error: ${error.toString()}'),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.invalidate(eventsProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCategoryChip(BuildContext context, String label, IconData icon) {
    return Padding(
      padding: const EdgeInsets.only(right: 8.0),
      child: ActionChip(
        avatar: Icon(icon, size: 16, color: Theme.of(context).colorScheme.onSecondaryContainer),
        label: Text(label),
        backgroundColor: Theme.of(context).colorScheme.secondaryContainer,
        onPressed: () {
          // Placeholder for filter action
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Filtered by $label')),
          );
        },
      ),
    );
  }
}
