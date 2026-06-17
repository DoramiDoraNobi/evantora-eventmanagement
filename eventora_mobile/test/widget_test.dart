import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:eventora_mobile/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    // Build our app and trigger a frame.
    await tester.pumpWidget(const ProviderScope(child: EventoraApp()));

    // Verify that the login route loads
    expect(find.text('Welcome back'), findsOneWidget);
  });
}
