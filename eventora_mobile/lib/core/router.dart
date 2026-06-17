import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../features/auth/presentation/login_screen.dart';
import '../features/auth/presentation/register_screen.dart';
import '../features/discovery/presentation/home_screen.dart';
import '../features/discovery/presentation/event_detail_screen.dart';
import '../features/tickets/presentation/checkout_screen.dart';
import '../features/tickets/presentation/my_tickets_screen.dart';
import '../features/tickets/presentation/ticket_detail_screen.dart';
import '../features/tickets/domain/ticket_model.dart';
import '../features/organizer/presentation/organizer_dashboard_screen.dart';
import '../features/organizer/presentation/organizer_events_screen.dart';
import '../features/organizer/presentation/qr_scanner_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  return GoRouter(
    initialLocation: '/login',
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) => const HomeScreen(),
      ),
      GoRoute(
        path: '/event/:id',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return EventDetailScreen(eventId: id);
        },
      ),
      GoRoute(
        path: '/checkout/:eventId',
        builder: (context, state) {
          final eventId = int.parse(state.pathParameters['eventId']!);
          return CheckoutScreen(eventId: eventId);
        },
      ),
      GoRoute(
        path: '/my-tickets',
        builder: (context, state) => const MyTicketsScreen(),
      ),
      GoRoute(
        path: '/ticket/:id',
        builder: (context, state) {
          final ticket = state.extra as Ticket;
          return TicketDetailScreen(ticket: ticket);
        },
      ),
      GoRoute(
        path: '/organizer',
        builder: (context, state) => const OrganizerDashboardScreen(),
      ),
      GoRoute(
        path: '/organizer/events',
        builder: (context, state) => const OrganizerEventsScreen(),
      ),
      GoRoute(
        path: '/scanner',
        builder: (context, state) => const QRScannerScreen(),
      ),
    ],
  );
});
