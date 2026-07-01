class PayoutModel {
  final int id;
  final double amount;
  final String bankName;
  final String bankAccountNumber;
  final String bankAccountName;
  final String status;
  final String? transactionId;
  final DateTime createdAt;

  PayoutModel({
    required this.id,
    required this.amount,
    required this.bankName,
    required this.bankAccountNumber,
    required this.bankAccountName,
    required this.status,
    this.transactionId,
    required this.createdAt,
  });

  factory PayoutModel.fromJson(Map<String, dynamic> json) {
    return PayoutModel(
      id: json['id'],
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      bankName: json['bank_name'] ?? '',
      bankAccountNumber: json['bank_account_number'] ?? '',
      bankAccountName: json['bank_account_name'] ?? '',
      status: json['status'] ?? 'pending',
      transactionId: json['transaction_id'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

class PayoutBalances {
  final double totalEarnings;
  final double withdrawnAmount;
  final double availableBalance;

  PayoutBalances({
    required this.totalEarnings,
    required this.withdrawnAmount,
    required this.availableBalance,
  });

  factory PayoutBalances.fromJson(Map<String, dynamic> json) {
    return PayoutBalances(
      totalEarnings: double.tryParse(json['total_earnings'].toString()) ?? 0.0,
      withdrawnAmount: double.tryParse(json['withdrawn_amount'].toString()) ?? 0.0,
      availableBalance: double.tryParse(json['available_balance'].toString()) ?? 0.0,
    );
  }
}
