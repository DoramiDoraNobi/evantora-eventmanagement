import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../data/auth_repository.dart';
import '../domain/user_model.dart';

final authControllerProvider = AsyncNotifierProvider<AuthController, User?>(() {
  return AuthController();
});

class AuthController extends AsyncNotifier<User?> {
  @override
  FutureOr<User?> build() {
    return null; // Initial state
  }

  Future<void> login(String email, String password) async {
    state = const AsyncValue.loading();
    state = await AsyncValue.guard(() async {
      final repository = ref.read(authRepositoryProvider);
      return await repository.login(email, password);
    });
  }

  Future<void> logout() async {
    final repository = ref.read(authRepositoryProvider);
    await repository.logout();
    state = const AsyncValue.data(null);
  }
}
