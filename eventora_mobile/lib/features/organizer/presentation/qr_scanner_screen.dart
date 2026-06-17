import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'organizer_controller.dart';
import '../data/organizer_repository.dart';

class QRScannerScreen extends ConsumerStatefulWidget {
  const QRScannerScreen({super.key});

  @override
  ConsumerState<QRScannerScreen> createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends ConsumerState<QRScannerScreen> {
  final MobileScannerController _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );
  bool _isProcessing = false;

  @override
  void dispose() {
    _scannerController.dispose();
    super.dispose();
  }

  Future<void> _processBarcode(BarcodeCapture capture) async {
    if (_isProcessing) return;

    final List<Barcode> barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final barcode = barcodes.first;
    if (barcode.rawValue == null) return;

    final ticketCode = barcode.rawValue!;

    setState(() {
      _isProcessing = true;
    });

    try {
      final orgId = ref.read(selectedOrgIdProvider);
      final repo = ref.read(organizerRepositoryProvider);

      final message = await repo.checkinTicket(orgId, ticketCode);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(message),
            backgroundColor: Colors.green,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().replaceAll('Exception: ', '')),
            backgroundColor: Theme.of(context).colorScheme.error,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    } finally {
      // Refresh dashboard stats after a check-in attempt (whether success or fail)
      ref.invalidate(dashboardProvider);
      
      // Allow scanning again after 3 seconds
      await Future.delayed(const Duration(seconds: 3));
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan Ticket'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/organizer'),
        ),
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _scannerController,
            onDetect: _processBarcode,
          ),
          // Overlay to make scanning area clear
          CustomPaint(
            painter: ScannerOverlayPainter(
              borderColor: Theme.of(context).colorScheme.primary,
              borderRadius: 16.0,
            ),
            child: Container(),
          ),
          if (_isProcessing)
            Container(
              color: Colors.black54,
              child: const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: Colors.white),
                    SizedBox(height: 16),
                    Text(
                      'Processing Ticket...',
                      style: TextStyle(color: Colors.white, fontSize: 18),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// Custom painter to draw a scanner overlay box
class ScannerOverlayPainter extends CustomPainter {
  final Color borderColor;
  final double borderRadius;

  ScannerOverlayPainter({
    required this.borderColor,
    this.borderRadius = 10,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.black54
      ..style = PaintingStyle.fill;

    // The clear area in the middle
    final scanAreaSize = size.width * 0.7;
    final scanAreaRect = Rect.fromCenter(
      center: Offset(size.width / 2, size.height / 2),
      width: scanAreaSize,
      height: scanAreaSize,
    );

    // Draw the dark overlay
    final path = Path()
      ..addRect(Rect.fromLTWH(0, 0, size.width, size.height))
      ..addRRect(RRect.fromRectAndRadius(
          scanAreaRect, Radius.circular(borderRadius)))
      ..fillType = PathFillType.evenOdd;

    canvas.drawPath(path, paint);

    // Draw the borders of the scan area
    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 4.0;

    final double lineLength = scanAreaSize * 0.1;
    
    // Top Left
    canvas.drawLine(
        scanAreaRect.topLeft,
        scanAreaRect.topLeft + Offset(lineLength, 0),
        borderPaint);
    canvas.drawLine(
        scanAreaRect.topLeft,
        scanAreaRect.topLeft + Offset(0, lineLength),
        borderPaint);

    // Top Right
    canvas.drawLine(
        scanAreaRect.topRight,
        scanAreaRect.topRight - Offset(lineLength, 0),
        borderPaint);
    canvas.drawLine(
        scanAreaRect.topRight,
        scanAreaRect.topRight + Offset(0, lineLength),
        borderPaint);

    // Bottom Left
    canvas.drawLine(
        scanAreaRect.bottomLeft,
        scanAreaRect.bottomLeft + Offset(lineLength, 0),
        borderPaint);
    canvas.drawLine(
        scanAreaRect.bottomLeft,
        scanAreaRect.bottomLeft - Offset(0, lineLength),
        borderPaint);

    // Bottom Right
    canvas.drawLine(
        scanAreaRect.bottomRight,
        scanAreaRect.bottomRight - Offset(lineLength, 0),
        borderPaint);
    canvas.drawLine(
        scanAreaRect.bottomRight,
        scanAreaRect.bottomRight - Offset(0, lineLength),
        borderPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
