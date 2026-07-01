import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'event_controller.dart';

class FilterSheet extends ConsumerStatefulWidget {
  const FilterSheet({super.key});

  @override
  ConsumerState<FilterSheet> createState() => _FilterSheetState();
}

class _FilterSheetState extends ConsumerState<FilterSheet> {
  final TextEditingController _locationController = TextEditingController();
  RangeValues _priceRange = const RangeValues(0, 1000);
  
  String _selectedDateFilter = '';
  String _selectedType = '';
  String _sort = '';

  @override
  void initState() {
    super.initState();
    // Load existing filters
    final filters = ref.read(eventFilterProvider);
    if (filters['location'] != null) {
      _locationController.text = filters['location'];
    }
    if (filters['filter'] != null) {
      _selectedDateFilter = filters['filter'];
    }
    if (filters['type'] != null) {
      _selectedType = filters['type'];
    }
    if (filters['sort'] != null) {
      _sort = filters['sort'];
    }
    double minPrice = filters['min_price'] != null ? (filters['min_price'] as num).toDouble() : 0;
    double maxPrice = filters['max_price'] != null ? (filters['max_price'] as num).toDouble() : 1000;
    _priceRange = RangeValues(minPrice, maxPrice);
  }

  @override
  void dispose() {
    _locationController.dispose();
    super.dispose();
  }

  void _applyFilters() {
    final filters = <String, dynamic>{};
    
    if (_locationController.text.trim().isNotEmpty) {
      filters['location'] = _locationController.text.trim();
    }
    
    if (_selectedDateFilter.isNotEmpty) {
      filters['filter'] = _selectedDateFilter;
    }

    if (_selectedType.isNotEmpty) {
      filters['type'] = _selectedType;
    }

    if (_sort.isNotEmpty) {
      filters['sort'] = _sort;
    }

    if (_priceRange.start > 0) {
      filters['min_price'] = _priceRange.start.toInt();
    }
    if (_priceRange.end < 1000) {
      filters['max_price'] = _priceRange.end.toInt();
    }

    ref.read(eventFilterProvider.notifier).updateFilter(filters);
    Navigator.pop(context);
  }

  void _resetFilters() {
    ref.read(eventFilterProvider.notifier).updateFilter({});
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
      ),
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Filter Events',
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                ),
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.close),
                ),
              ],
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _locationController,
              decoration: const InputDecoration(
                labelText: 'Location',
                hintText: 'e.g., Jakarta, Bandung, or Venue',
                prefixIcon: Icon(Icons.location_on_outlined),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Sort By',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              initialValue: _sort.isEmpty ? null : _sort,
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              hint: const Text('Relevance'),
              items: const [
                DropdownMenuItem(value: '', child: Text('Relevance')),
                DropdownMenuItem(value: 'latest', child: Text('Latest Events (Terbaru)')),
              ],
              onChanged: (value) {
                setState(() {
                  _sort = value ?? '';
                });
              },
            ),
            const SizedBox(height: 16),
            Text(
              'Date',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              initialValue: _selectedDateFilter.isEmpty ? null : _selectedDateFilter,
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              hint: const Text('All Dates'),
              items: const [
                DropdownMenuItem(value: '', child: Text('All Dates')),
                DropdownMenuItem(value: 'today', child: Text('Today')),
                DropdownMenuItem(value: '14_days', child: Text('Next 14 Days')),
                DropdownMenuItem(value: '30_days', child: Text('Next 30 Days')),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedDateFilter = value ?? '';
                });
              },
            ),
            const SizedBox(height: 16),
            Text(
              'Type',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            DropdownButtonFormField<String>(
              initialValue: _selectedType.isEmpty ? null : _selectedType,
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              hint: const Text('All Types'),
              items: const [
                DropdownMenuItem(value: '', child: Text('All Types')),
                DropdownMenuItem(value: 'offline', child: Text('Offline / In-person')),
                DropdownMenuItem(value: 'online', child: Text('Online / Virtual')),
                DropdownMenuItem(value: 'hybrid', child: Text('Hybrid')),
              ],
              onChanged: (value) {
                setState(() {
                  _selectedType = value ?? '';
                });
              },
            ),
            const SizedBox(height: 24),
            Text(
              'Price Range',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            RangeSlider(
              values: _priceRange,
              min: 0,
              max: 1000,
              divisions: 20,
              labels: RangeLabels(
                '\$${_priceRange.start.round()}',
                _priceRange.end >= 1000 ? '\$1000+' : '\$${_priceRange.end.round()}',
              ),
              onChanged: (RangeValues values) {
                setState(() {
                  _priceRange = values;
                });
              },
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('\$${_priceRange.start.round()}'),
                Text(_priceRange.end >= 1000 ? '\$1000+' : '\$${_priceRange.end.round()}'),
              ],
            ),
            const SizedBox(height: 32),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _resetFilters,
                    style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                    child: const Text('Reset'),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: _applyFilters,
                    style: ElevatedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                    child: const Text('Apply Filters'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
