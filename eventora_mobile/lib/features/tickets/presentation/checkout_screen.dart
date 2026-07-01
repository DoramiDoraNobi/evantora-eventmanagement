import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../discovery/presentation/event_controller.dart';
import 'ticket_controller.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  final int eventId;
  final Map<int, int> selectedTickets;

  const CheckoutScreen({super.key, required this.eventId, required this.selectedTickets});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  String _selectedPaymentMethod = 'midtrans';

  @override
  Widget build(BuildContext context) {
    final eventAsync = ref.watch(eventDetailProvider(widget.eventId));
    final purchaseState = ref.watch(purchaseTicketProvider);

    // Listen for errors
    ref.listen(purchaseTicketProvider, (previous, next) {
      if (next is AsyncError) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(next.error.toString().replaceAll('Exception: ', '')),
            backgroundColor: Theme.of(context).colorScheme.error,
          ),
        );
      }
    });

    return Scaffold(
      appBar: AppBar(
        title: const Text('Checkout'),
      ),
      body: eventAsync.when(
        data: (event) {
          // Calculate total dynamically based on selected tickets
          double subtotal = 0.0;
          final List<Widget> ticketRows = [];

          if (widget.selectedTickets.isEmpty) {
            ticketRows.add(const Text('No tickets selected.'));
          } else {
            for (final entry in widget.selectedTickets.entries) {
              final ticketId = entry.key;
              final qty = entry.value;
              final ticketInfo = event.tickets.firstWhere((t) => t.id == ticketId);
              
              subtotal += qty * ticketInfo.price;

              ticketRows.add(
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 4.0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          ticketInfo.name,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                      ),
                      Text(
                        '${qty}x  Rp ${((qty * ticketInfo.price) * 15000).toStringAsFixed(0)}',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                    ],
                  ),
                ),
              );
            }
          }

          // Assume 0 for discount until Promo Code UI is fully implemented
          double discount = 0.0;
          double afterDiscount = subtotal - discount;
          double taxAmount = event.isTaxable ? (afterDiscount * (event.taxRate / 100)) : 0.0;
          double serviceFee = 0.0;
          if (subtotal > 0) {
            serviceFee = (afterDiscount * (event.platformFeePercent / 100)) + event.platformFeeFixed;
          }
          double total = afterDiscount + taxAmount + serviceFee;

          return Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  'Order Summary',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const SizedBox(height: 24),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      children: [
                        ...ticketRows,
                        const Divider(height: 16),
                        // Subtotal
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Subtotal'),
                            Text('Rp ${(subtotal * 15000).toStringAsFixed(0)}'),
                          ],
                        ),
                        // Discount
                        if (discount > 0)
                          Padding(
                            padding: const EdgeInsets.only(top: 4.0),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Discount', style: TextStyle(color: Colors.green)),
                                Text('- Rp ${(discount * 15000).toStringAsFixed(0)}', style: const TextStyle(color: Colors.green)),
                              ],
                            ),
                          ),
                        // Tax
                        if (taxAmount > 0)
                          Padding(
                            padding: const EdgeInsets.only(top: 4.0),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text('${event.taxName} (${event.taxRate}%)'),
                                Text('Rp ${(taxAmount * 15000).toStringAsFixed(0)}'),
                              ],
                            ),
                          ),
                        // Service Fee
                        if (serviceFee > 0)
                          Padding(
                            padding: const EdgeInsets.only(top: 4.0),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Service Fee'),
                                Text('Rp ${(serviceFee * 15000).toStringAsFixed(0)}'),
                              ],
                            ),
                          ),
                        const Divider(height: 32),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Total',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            Text(
                              'Rp ${(total * 15000).toStringAsFixed(0)}',
                              style: Theme.of(context).textTheme.titleLarge?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: Theme.of(context).colorScheme.primary,
                                  ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Payment Method',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                Card(
                  child: Column(
                    children: [
                      RadioListTile<String>(
                        title: const Text('Midtrans (Khusus Indonesia)'),
                        value: 'midtrans',
                        groupValue: _selectedPaymentMethod,
                        onChanged: (value) => setState(() => _selectedPaymentMethod = value!),
                      ),
                      RadioListTile<String>(
                        title: const Text('Credit Card (Stripe)'),
                        value: 'stripe',
                        groupValue: _selectedPaymentMethod,
                        onChanged: (value) => setState(() => _selectedPaymentMethod = value!),
                      ),
                      RadioListTile<String>(
                        title: const Text('PayPal'),
                        value: 'paypal',
                        groupValue: _selectedPaymentMethod,
                        onChanged: (value) => setState(() => _selectedPaymentMethod = value!),
                      ),
                    ],
                  ),
                ),
                const Spacer(),
                ElevatedButton(
                  onPressed: purchaseState is AsyncLoading || widget.selectedTickets.isEmpty
                      ? null
                      : () async {
                          final success = await ref
                              .read(purchaseTicketProvider.notifier)
                              .purchase(widget.eventId, widget.selectedTickets, _selectedPaymentMethod);
                          if (success && mounted) {
                            final stateVal = ref.read(purchaseTicketProvider).value;
                            if (stateVal != null && stateVal.ticketCode.startsWith('http')) {
                               // Open Midtrans Snap URL
                               final url = Uri.parse(stateVal.ticketCode);
                               if (await canLaunchUrl(url)) {
                                  await launchUrl(url, mode: LaunchMode.externalApplication);
                               }
                            } else {
                               ScaffoldMessenger.of(context).showSnackBar(
                                 const SnackBar(content: Text('Payment Successful!')),
                               );
                            }
                            context.go('/my-tickets'); // Route to tickets page
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.all(16),
                  ),
                  child: purchaseState is AsyncLoading
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Text('Confirm Payment'),
                ),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(child: Text('Error loading event details: $e')),
      ),
    );
  }
}
