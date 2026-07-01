import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/local_db/database_helper.dart';
import '../domain/organizer_models.dart';
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
  bool _isOfflineMode = false;
  OrganizerEvent? _selectedEvent;
  
  int _pendingSyncCount = 0;
  bool _isSyncing = false;

  @override
  void initState() {
    super.initState();
    _refreshPendingSyncCount();
  }

  @override
  void dispose() {
    _scannerController.dispose();
    super.dispose();
  }

  Future<void> _refreshPendingSyncCount() async {
    if (_selectedEvent == null) return;
    final count = await DatabaseHelper.instance.getPendingSyncCount(_selectedEvent!.id);
    if (mounted) {
      setState(() {
        _pendingSyncCount = count;
      });
    }
  }

  Future<void> _downloadOfflineDatabase() async {
    if (_selectedEvent == null) return;
    
    setState(() => _isSyncing = true);
    
    try {
      final orgId = ref.read(selectedOrgIdProvider);
      final repo = ref.read(organizerRepositoryProvider);
      
      final tickets = await repo.downloadTicketsForOffline(orgId, _selectedEvent!.id);
      
      await DatabaseHelper.instance.insertOrUpdateTickets(_selectedEvent!.id, tickets);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Successfully downloaded ${tickets.length} tickets for offline scanning.'), backgroundColor: Colors.green),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to download: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSyncing = false);
      _refreshPendingSyncCount();
    }
  }

  Future<void> _syncToServer() async {
    if (_selectedEvent == null) return;

    setState(() => _isSyncing = true);
    
    try {
      final orgId = ref.read(selectedOrgIdProvider);
      final repo = ref.read(organizerRepositoryProvider);
      
      final pendingTickets = await DatabaseHelper.instance.getPendingSyncTickets(_selectedEvent!.id);
      
      if (pendingTickets.isEmpty) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('No offline check-ins pending sync.')),
          );
        }
        return;
      }

      await repo.syncOfflineCheckins(orgId, _selectedEvent!.id, pendingTickets);
      
      final syncedIds = pendingTickets.map((t) => t['id'] as int).toList();
      await DatabaseHelper.instance.markTicketsAsSynced(syncedIds);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Successfully synced ${pendingTickets.length} tickets to server!'), backgroundColor: Colors.green),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Sync failed: $e'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSyncing = false);
      _refreshPendingSyncCount();
    }
  }

  Future<void> _processBarcode(BarcodeCapture capture) async {
    if (_isProcessing || _isSyncing) return;

    final List<Barcode> barcodes = capture.barcodes;
    if (barcodes.isEmpty) return;

    final barcode = barcodes.first;
    if (barcode.rawValue == null) return;

    final ticketCode = barcode.rawValue!;

    setState(() {
      _isProcessing = true;
    });

    try {
      if (_isOfflineMode) {
        // --- OFFLINE SCANNING LOGIC ---
        if (_selectedEvent == null) throw Exception('Please select an event first for Offline Mode.');
        
        final ticket = await DatabaseHelper.instance.checkTicketOffline(ticketCode, _selectedEvent!.id);
        
        if (ticket == null) {
          throw Exception('Ticket not found in offline database. Make sure you downloaded the latest database.');
        }
        
        if (ticket['status'] == 'checked_in') {
          throw Exception('Ticket Already Used! (Offline check)');
        }
        
        if (ticket['status'] != 'confirmed') {
          throw Exception('Invalid Ticket Status: ${ticket['status']}');
        }

        await DatabaseHelper.instance.markTicketAsScanned(ticket['id']);
        _refreshPendingSyncCount();

        if (mounted) {
          _showScanResult('Offline Check-in Successful!\n${ticket['buyer_name']}', Colors.green);
        }
        
      } else {
        // --- ONLINE SCANNING LOGIC ---
        if (_selectedEvent == null) throw Exception('Please select an event first to scan tickets.');
        
        final orgId = ref.read(selectedOrgIdProvider);
        final repo = ref.read(organizerRepositoryProvider);

        final message = await repo.checkinTicket(orgId, _selectedEvent!.id, ticketCode);

        if (mounted) {
          _showScanResult(message, Colors.green);
        }
      }
    } catch (e) {
      if (mounted) {
        _showScanResult(e.toString().replaceAll('Exception: ', ''), Theme.of(context).colorScheme.error);
      }
    } finally {
      if (!_isOfflineMode) {
        ref.invalidate(dashboardProvider);
      }
      
      await Future.delayed(const Duration(seconds: 3));
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  void _showScanResult(String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: color,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final eventsAsync = ref.watch(organizerEventsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan Ticket'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/organizer'),
        ),
        actions: [
          Row(
            children: [
              Text('Offline Mode', style: Theme.of(context).textTheme.bodySmall),
              Switch(
                value: _isOfflineMode,
                onChanged: (val) {
                  setState(() => _isOfflineMode = val);
                  if (val && _selectedEvent == null) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Please select an event for Offline Mode.'), backgroundColor: Colors.orange),
                    );
                  }
                },
                activeTrackColor: Colors.orange,
              ),
            ],
          ),
        ],
      ),
      body: Stack(
        children: [
          Column(
            children: [
              // Event Selector Header
              Container(
                padding: const EdgeInsets.all(16),
                color: Theme.of(context).colorScheme.surfaceContainerHighest,
                child: eventsAsync.when(
                  data: (events) {
                    final publishedEvents = events.where((e) => e.status == 'published').toList();
                    return DropdownButtonFormField<OrganizerEvent>(
                      decoration: const InputDecoration(
                        labelText: 'Select Event to Scan',
                        border: OutlineInputBorder(),
                        filled: true,
                        fillColor: Colors.white,
                      ),
                      initialValue: _selectedEvent,
                      items: publishedEvents.map((e) => DropdownMenuItem(
                            value: e,
                            child: Text(e.title, overflow: TextOverflow.ellipsis),
                          )).toList(),
                      onChanged: (val) {
                        setState(() {
                          _selectedEvent = val;
                        });
                        _refreshPendingSyncCount();
                      },
                    );
                  },
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (e, st) => Text('Failed to load events: $e'),
                ),
              ),
              
              // Offline Tools
              if (_selectedEvent != null)
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      ElevatedButton.icon(
                        icon: const Icon(Icons.download),
                        label: const Text('Download DB'),
                        onPressed: _isSyncing ? null : _downloadOfflineDatabase,
                      ),
                      Badge(
                        label: Text(_pendingSyncCount.toString()),
                        isLabelVisible: _pendingSyncCount > 0,
                        child: ElevatedButton.icon(
                          icon: const Icon(Icons.sync),
                          label: const Text('Sync to Server'),
                          onPressed: _isSyncing ? null : _syncToServer,
                        ),
                      ),
                    ],
                  ),
                ),

              // Scanner
              Expanded(
                child: Stack(
                  children: [
                    MobileScanner(
                      controller: _scannerController,
                      onDetect: _processBarcode,
                    ),
                    CustomPaint(
                      painter: ScannerOverlayPainter(
                        borderColor: _isOfflineMode ? Colors.orange : Theme.of(context).colorScheme.primary,
                        borderRadius: 16.0,
                      ),
                      child: Container(),
                    ),
                  ],
                ),
              ),
            ],
          ),
          
          if (_isProcessing || _isSyncing)
            Container(
              color: Colors.black54,
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircularProgressIndicator(color: Colors.white),
                    const SizedBox(height: 16),
                    Text(
                      _isSyncing ? 'Syncing...' : 'Processing Ticket...',
                      style: const TextStyle(color: Colors.white, fontSize: 18),
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
