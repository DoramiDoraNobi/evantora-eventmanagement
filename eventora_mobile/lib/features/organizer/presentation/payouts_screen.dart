import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'organizer_controller.dart';
import '../data/organizer_repository.dart';
import '../domain/models/payout_model.dart';

final payoutsProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final orgId = ref.watch(selectedOrgIdProvider);
  if (orgId == 0) throw Exception('No organization selected');
  final repository = ref.watch(organizerRepositoryProvider);
  
  final rawData = await repository.getPayouts(orgId);
  final balances = PayoutBalances.fromJson(rawData['balances']);
  final payouts = (rawData['payouts'] as List)
      .map((e) => PayoutModel.fromJson(e))
      .toList();
      
  return {
    'balances': balances,
    'payouts': payouts,
  };
});

class PayoutsScreen extends ConsumerWidget {
  const PayoutsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final payoutsAsync = ref.watch(payoutsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Payouts & Balances'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/organizer'),
        ),
      ),
      body: payoutsAsync.when(
        data: (data) {
          final balances = data['balances'] as PayoutBalances;
          final payouts = data['payouts'] as List<PayoutModel>;

          return RefreshIndicator(
            onRefresh: () async => ref.invalidate(payoutsProvider),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _buildBalanceCard(context, ref, balances),
                const SizedBox(height: 24),
                Text(
                  'Payout History',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                ),
                const SizedBox(height: 16),
                if (payouts.isEmpty)
                  const Center(
                    child: Padding(
                      padding: EdgeInsets.all(32.0),
                      child: Text('No payout history found.'),
                    ),
                  )
                else
                  ...payouts.map((payout) => _buildPayoutItem(context, payout)),
              ],
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (e, st) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              Text('Error: ${e.toString()}'),
              ElevatedButton(
                onPressed: () => ref.invalidate(payoutsProvider),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildBalanceCard(BuildContext context, WidgetRef ref, PayoutBalances balances) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(
            colors: [
              Theme.of(context).colorScheme.primary,
              Theme.of(context).colorScheme.tertiary,
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Available Balance',
              style: TextStyle(
                color: Theme.of(context).colorScheme.onPrimary.withValues(alpha: 0.8),
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Rp ${balances.availableBalance.toStringAsFixed(0)}',
              style: TextStyle(
                color: Theme.of(context).colorScheme.onPrimary,
                fontSize: 32,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Total Earnings',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onPrimary.withValues(alpha: 0.8),
                        fontSize: 12,
                      ),
                    ),
                    Text(
                      'Rp ${balances.totalEarnings.toStringAsFixed(0)}',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onPrimary,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Withdrawn',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onPrimary.withValues(alpha: 0.8),
                        fontSize: 12,
                      ),
                    ),
                    Text(
                      'Rp ${balances.withdrawnAmount.toStringAsFixed(0)}',
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.onPrimary,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: balances.availableBalance >= 50000
                    ? () => _showRequestPayoutSheet(context, ref, balances.availableBalance)
                    : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Theme.of(context).colorScheme.onPrimary,
                  foregroundColor: Theme.of(context).colorScheme.primary,
                ),
                child: const Text('Request Payout'),
              ),
            ),
            if (balances.availableBalance < 50000)
              Padding(
                padding: const EdgeInsets.only(top: 8.0),
                child: Center(
                  child: Text(
                    'Minimum payout is Rp 50,000',
                    style: TextStyle(
                      color: Theme.of(context).colorScheme.onPrimary.withValues(alpha: 0.8),
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildPayoutItem(BuildContext context, PayoutModel payout) {
    Color statusColor;
    switch (payout.status.toLowerCase()) {
      case 'paid':
        statusColor = Colors.green;
        break;
      case 'processing':
        statusColor = Colors.blue;
        break;
      case 'rejected':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Colors.orange;
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        contentPadding: const EdgeInsets.all(16),
        title: Text(
          'Rp ${payout.amount.toStringAsFixed(0)}',
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('${payout.bankName} - ${payout.bankAccountNumber}'),
            Text(
              '${payout.createdAt.day}/${payout.createdAt.month}/${payout.createdAt.year}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: statusColor.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            payout.status.toUpperCase(),
            style: TextStyle(
              color: statusColor,
              fontWeight: FontWeight.bold,
              fontSize: 12,
            ),
          ),
        ),
      ),
    );
  }

  void _showRequestPayoutSheet(BuildContext context, WidgetRef ref, double maxAmount) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: _RequestPayoutForm(maxAmount: maxAmount, ref: ref),
      ),
    );
  }
}

class _RequestPayoutForm extends StatefulWidget {
  final double maxAmount;
  final WidgetRef ref;

  const _RequestPayoutForm({required this.maxAmount, required this.ref});

  @override
  State<_RequestPayoutForm> createState() => _RequestPayoutFormState();
}

class _RequestPayoutFormState extends State<_RequestPayoutForm> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _bankNameController = TextEditingController();
  final _bankAccountController = TextEditingController();
  final _bankAccountNameController = TextEditingController();
  String _payoutMethod = 'PayPal';
  bool _isLoading = false;

  @override
  void dispose() {
    _amountController.dispose();
    _bankNameController.dispose();
    _bankAccountController.dispose();
    _bankAccountNameController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true);

    try {
      final orgId = widget.ref.read(selectedOrgIdProvider);
      final repository = widget.ref.read(organizerRepositoryProvider);

      final payload = _payoutMethod == 'PayPal' 
        ? {
            'amount': double.parse(_amountController.text),
            'bank_name': 'PayPal',
            'bank_account_number': _bankAccountController.text, // Email
            'bank_account_name': 'PayPal Account',
          }
        : {
            'amount': double.parse(_amountController.text),
            'bank_name': _bankNameController.text,
            'bank_account_number': _bankAccountController.text,
            'bank_account_name': _bankAccountNameController.text,
          };

      await repository.requestPayout(orgId, payload);

      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Payout requested successfully')),
        );
        widget.ref.invalidate(payoutsProvider);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: ${e.toString()}'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(24.0),
      child: Form(
        key: _formKey,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Request Payout',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Amount (Rp)',
                border: OutlineInputBorder(),
                prefixText: 'Rp ',
              ),
              validator: (value) {
                if (value == null || value.isEmpty) return 'Required';
                final amount = double.tryParse(value);
                if (amount == null) return 'Invalid amount';
                if (amount < 50000) return 'Minimum Rp 50,000';
                if (amount > widget.maxAmount) return 'Exceeds available balance';
                return null;
              },
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              value: _payoutMethod,
              decoration: const InputDecoration(
                labelText: 'Payout Method',
                border: OutlineInputBorder(),
              ),
              items: const [
                DropdownMenuItem(value: 'PayPal', child: Text('PayPal Payouts (Fast)')),
                DropdownMenuItem(value: 'Bank Transfer', child: Text('Manual Bank Transfer')),
              ],
              onChanged: (val) {
                if (val != null) {
                  setState(() {
                    _payoutMethod = val;
                    _bankAccountController.clear();
                    _bankNameController.clear();
                    _bankAccountNameController.clear();
                  });
                }
              },
            ),
            const SizedBox(height: 16),
            if (_payoutMethod == 'PayPal')
              TextFormField(
                controller: _bankAccountController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'PayPal Email Address',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) return 'Required';
                  if (!value.contains('@')) return 'Enter a valid email';
                  return null;
                },
              )
            else ...[
              TextFormField(
                controller: _bankNameController,
                decoration: const InputDecoration(
                  labelText: 'Bank Name (e.g., BCA, Stripe)',
                  border: OutlineInputBorder(),
                ),
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _bankAccountController,
                keyboardType: TextInputType.text,
                decoration: const InputDecoration(
                  labelText: 'Account Number',
                  border: OutlineInputBorder(),
                ),
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _bankAccountNameController,
                decoration: const InputDecoration(
                  labelText: 'Account Holder Name',
                  border: OutlineInputBorder(),
                ),
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
            ],
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _isLoading ? null : _submit,
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: _isLoading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Submit Request'),
            ),
          ],
        ),
      ),
    );
  }
}
