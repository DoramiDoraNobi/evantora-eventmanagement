import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class DatabaseHelper {
  static final DatabaseHelper instance = DatabaseHelper._init();
  static Database? _database;

  DatabaseHelper._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('eventora_offline.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE offline_tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id INTEGER NOT NULL,
        ticket_code TEXT NOT NULL,
        buyer_name TEXT NOT NULL,
        status TEXT NOT NULL,
        sync_status INTEGER NOT NULL DEFAULT 0,
        scanned_at TEXT
      )
    ''');
    
    // Add index for fast querying
    await db.execute('CREATE INDEX idx_ticket_code ON offline_tickets (ticket_code)');
  }

  Future<void> insertOrUpdateTickets(int eventId, List<dynamic> tickets) async {
    final db = await instance.database;
    final batch = db.batch();

    // Optionally clear existing synced tickets for this event
    batch.delete('offline_tickets', where: 'event_id = ? AND sync_status = 0', whereArgs: [eventId]);

    for (final ticket in tickets) {
      batch.insert('offline_tickets', {
        'event_id': eventId,
        'ticket_code': ticket['qr_code'],
        'buyer_name': ticket['name'],
        'status': ticket['status'],
        'sync_status': 0, // 0 = fully synced with server, not yet scanned locally
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }

    await batch.commit(noResult: true);
  }

  Future<Map<String, dynamic>?> checkTicketOffline(String qrCode, int eventId) async {
    final db = await instance.database;
    final maps = await db.query(
      'offline_tickets',
      where: 'ticket_code = ? AND event_id = ?',
      whereArgs: [qrCode, eventId],
    );

    if (maps.isNotEmpty) {
      return maps.first;
    } else {
      return null;
    }
  }

  Future<void> markTicketAsScanned(int id) async {
    final db = await instance.database;
    await db.update(
      'offline_tickets',
      {
        'status': 'checked_in',
        'sync_status': 1, // 1 = needs to be synced to server
        'scanned_at': DateTime.now().toIso8601String(),
      },
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  Future<List<Map<String, dynamic>>> getPendingSyncTickets(int eventId) async {
    final db = await instance.database;
    return await db.query(
      'offline_tickets',
      where: 'event_id = ? AND sync_status = 1',
      whereArgs: [eventId],
    );
  }

  Future<void> markTicketsAsSynced(List<int> ids) async {
    final db = await instance.database;
    final batch = db.batch();
    for (final id in ids) {
      batch.update(
        'offline_tickets',
        {'sync_status': 0},
        where: 'id = ?',
        whereArgs: [id],
      );
    }
    await batch.commit(noResult: true);
  }

  Future<int> getPendingSyncCount(int eventId) async {
    final db = await instance.database;
    final result = await db.rawQuery('SELECT COUNT(*) as count FROM offline_tickets WHERE event_id = ? AND sync_status = 1', [eventId]);
    return Sqflite.firstIntValue(result) ?? 0;
  }
}
