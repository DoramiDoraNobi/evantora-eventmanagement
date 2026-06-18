import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../domain/event_model.dart';
import 'event_controller.dart';

class EventDetailScreen extends ConsumerWidget {
  final int eventId;

  const EventDetailScreen({super.key, required this.eventId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventAsyncValue = ref.watch(eventDetailProvider(eventId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Event Details'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/home'),
        ),
      ),
      body: eventAsyncValue.when(
        data: (event) {
          return SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Hero Image
                Hero(
                  tag: 'event-image-${event.id}',
                  child: CachedNetworkImage(
                    imageUrl: 'https://picsum.photos/seed/${event.id}/800/400',
                    height: 250,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      height: 250,
                      color: Theme.of(context).colorScheme.surfaceContainerHighest,
                      child: const Center(child: CircularProgressIndicator()),
                    ),
                    errorWidget: (context, url, error) => Container(
                      height: 250,
                      color: Theme.of(context).colorScheme.secondaryContainer,
                      child: Icon(
                        Icons.event,
                        size: 100,
                        color: Theme.of(context).colorScheme.onSecondaryContainer.withValues(alpha: 0.5),
                      ),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        event.title,
                        style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 16),
                      Card(
                        margin: EdgeInsets.zero,
                        color: Theme.of(context).colorScheme.surfaceContainerHighest,
                        elevation: 0,
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            children: [
                              Row(
                                children: [
                                  Icon(Icons.calendar_today, color: Theme.of(context).colorScheme.primary),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Text(
                                      event.date,
                                      style: Theme.of(context).textTheme.titleMedium,
                                    ),
                                  ),
                                ],
                              ),
                              const Divider(height: 32),
                              Row(
                                children: [
                                  Icon(Icons.location_on, color: Theme.of(context).colorScheme.primary),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: Text(
                                      event.venue,
                                      style: Theme.of(context).textTheme.titleMedium,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'About Event',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        event.description.isEmpty ? 'No description available.' : event.description,
                        style: Theme.of(context).textTheme.bodyLarge?.copyWith(height: 1.5),
                      ),
                    ],
                  ),
                ),
              ],
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
                onPressed: () => ref.invalidate(eventDetailProvider(eventId)),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Container(
          padding: const EdgeInsets.all(16.0),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surface,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, -5),
              ),
            ],
          ),
          child: eventAsyncValue.maybeWhen(
            data: (event) => Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Price',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                    Text(
                      '\$${event.price.toStringAsFixed(2)}',
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            color: Theme.of(context).colorScheme.primary,
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                  ],
                ),
                ElevatedButton(
                  onPressed: () {
                    _showTicketSelectionBottomSheet(context, event);
                  },
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 16),
                  ),
                  child: const Text('Buy Ticket'),
                ),
              ],
            ),
            orElse: () => const SizedBox.shrink(),
          ),
        ),
      ),
    );
  }

  void _showTicketSelectionBottomSheet(BuildContext context, Event event) async {
    final selectedQuantities = await showModalBottomSheet<Map<int, int>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      builder: (context) {
        return TicketSelectionSheet(event: event);
      },
    );

    if (selectedQuantities != null && selectedQuantities.isNotEmpty && context.mounted) {
      context.push('/checkout/${event.id}', extra: selectedQuantities);
    }
  }
}

class TicketSelectionSheet extends StatefulWidget {
  final Event event;

  const TicketSelectionSheet({super.key, required this.event});

  @override
  State<TicketSelectionSheet> createState() => _TicketSelectionSheetState();
}

class _TicketSelectionSheetState extends State<TicketSelectionSheet> {
  final Map<int, int> _selectedQuantities = {};

  void _updateQuantity(int ticketId, int delta, int maxPerOrder) {
    setState(() {
      final current = _selectedQuantities[ticketId] ?? 0;
      final next = current + delta;
      if (next >= 0 && next <= maxPerOrder) {
        if (next == 0) {
          _selectedQuantities.remove(ticketId);
        } else {
          _selectedQuantities[ticketId] = next;
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    double total = 0.0;
    int totalTickets = 0;

    for (final ticket in widget.event.tickets) {
      final qty = _selectedQuantities[ticket.id] ?? 0;
      total += qty * ticket.price;
      totalTickets += qty;
    }

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Select Tickets',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          if (widget.event.tickets.isEmpty)
            const Center(child: Padding(
              padding: EdgeInsets.all(16.0),
              child: Text('No tickets available.'),
            ))
          else
            ...widget.event.tickets.map((ticket) {
              final qty = _selectedQuantities[ticket.id] ?? 0;
              return Padding(
                padding: const EdgeInsets.only(bottom: 16.0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(ticket.name, style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 4),
                          Text(
                            '\$${ticket.price.toStringAsFixed(2)}',
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Theme.of(context).colorScheme.primary,
                                  fontWeight: FontWeight.bold,
                                ),
                          ),
                        ],
                      ),
                    ),
                    Row(
                      children: [
                        IconButton(
                          onPressed: qty > 0 ? () => _updateQuantity(ticket.id, -1, ticket.maxPerOrder) : null,
                          icon: const Icon(Icons.remove_circle_outline),
                        ),
                        Text('$qty', style: Theme.of(context).textTheme.titleMedium),
                        IconButton(
                          onPressed: qty < ticket.maxPerOrder ? () => _updateQuantity(ticket.id, 1, ticket.maxPerOrder) : null,
                          icon: const Icon(Icons.add_circle_outline),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: totalTickets > 0
                ? () {
                    Navigator.pop(context, _selectedQuantities); // Close bottom sheet and return data
                  }
                : null,
            style: ElevatedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
            child: Text('Continue to Checkout (\$${total.toStringAsFixed(2)})'),
          ),
          const SizedBox(height: 16), // Bottom padding for safety
        ],
      ),
    );
  }
}
