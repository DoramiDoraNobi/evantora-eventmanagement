import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
// We will import screens here once we build them
// import '../../features/auth/presentation/login_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/',
    routes: [
      GoRoute(
        path: '/',
        builder: (context, state) => const Scaffold(
          body: Center(child: Text('Eventora Loading...')),
        ),
      ),
      // Auth
      // GoRoute(
      //   path: '/login',
      //   builder: (context, state) => const LoginScreen(),
      // ),
    ],
  );
});
