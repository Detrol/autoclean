<?php

namespace App\Livewire\Admin;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Station;
use App\Models\StationInventory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryManager extends Component
{
    use WithPagination;

    public $selectedTab = 'items';

    public $selectedStationId = null;

    // Item management
    public $showItemForm = false;

    public $editingItemId = null;

    public $itemName = '';

    public $itemDescription = '';

    public $itemUnit = 'pcs';

    public $itemReorderLevel = 0;

    public $itemActive = true;

    // Station inventory management
    public $showStationInventoryForm = false;

    public $selectedStationInventory = null;

    public $currentQuantity = 0;

    public $minimumQuantity = 0;

    public $notes = '';

    // Bulk item selection
    public $showAddItemsForm = false;

    public $selectedItems = [];

    public $itemQuantities = [];

    // Transaction form
    public $showTransactionForm = false;

    public $transactionType = 'adjust';

    public $transactionQuantity = 0;

    public $transactionReason = '';

    public $transactionNotes = '';

    public $transactionStationId = null;

    public $transactionItemId = null;

    public $unitOptions = [
        'pcs' => 'Stycken (st)',
        'liters' => 'Liter (l)',
        'meters' => 'Meter (m)',
        'kg' => 'Kilogram (kg)',
        'boxes' => 'Lådor',
        'bottles' => 'Flaskor',
        'rolls' => 'Rullar',
    ];

    public function mount()
    {
        $this->selectedStationId = Station::active()->first()?->id;
    }

    public function render()
    {
        $inventoryItems = InventoryItem::when($this->selectedTab === 'items', function ($query) {
            return $query->withCount('stationInventory');
        })->paginate(20);

        $stations = Station::active()->get();

        $stationInventory = collect();
        $lowStockItems = collect();

        if ($this->selectedStationId && $this->selectedTab !== 'items') {
            $stationInventory = StationInventory::where('station_id', $this->selectedStationId)
                ->with(['inventoryItem', 'station'])
                ->get();

            $lowStockItems = $stationInventory->where('is_low_stock', true);
        }

        $recentTransactions = collect();
        if ($this->selectedTab === 'transactions') {
            $recentTransactions = InventoryTransaction::with(['user', 'station', 'inventoryItem'])
                ->when($this->selectedStationId, fn ($q) => $q->where('station_id', $this->selectedStationId))
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get();
        }

        // Available items for bulk selection (not already in station inventory)
        $availableItems = collect();
        if ($this->selectedStationId) {
            $existingItemIds = StationInventory::where('station_id', $this->selectedStationId)
                ->pluck('inventory_item_id');
            $availableItems = InventoryItem::active()
                ->whereNotIn('id', $existingItemIds)
                ->get();
        }

        return view('livewire.admin.inventory-manager', [
            'items' => $inventoryItems,
            'stations' => $stations,
            'stationInventory' => $stationInventory,
            'lowStockItems' => $lowStockItems,
            'recentTransactions' => $recentTransactions,
            'availableItems' => $availableItems,
        ]);
    }

    // Item management methods
    public function openItemForm($itemId = null)
    {
        $this->editingItemId = $itemId;

        if ($itemId) {
            $item = InventoryItem::findOrFail($itemId);
            $this->itemName = $item->name;
            $this->itemDescription = $item->description;
            $this->itemUnit = $item->unit;
            $this->itemReorderLevel = $item->default_reorder_level;
            $this->itemActive = $item->is_active;
        } else {
            $this->resetItemForm();
        }

        $this->showItemForm = true;
    }

    public function saveItem()
    {
        $this->validate([
            'itemName' => 'required|string|max:255',
            'itemDescription' => 'nullable|string',
            'itemUnit' => 'required|string',
            'itemReorderLevel' => 'required|integer|min:0',
        ]);

        $data = [
            'name' => $this->itemName,
            'description' => $this->itemDescription,
            'unit' => $this->itemUnit,
            'default_reorder_level' => $this->itemReorderLevel,
            'is_active' => $this->itemActive,
        ];

        if ($this->editingItemId) {
            InventoryItem::findOrFail($this->editingItemId)->update($data);
            session()->flash('message', 'Artikel uppdaterad!');
        } else {
            InventoryItem::create($data);
            session()->flash('message', 'Artikel skapad!');
        }

        $this->closeItemForm();
    }

    public function closeItemForm()
    {
        $this->showItemForm = false;
        $this->resetItemForm();
    }

    private function resetItemForm()
    {
        $this->editingItemId = null;
        $this->itemName = '';
        $this->itemDescription = '';
        $this->itemUnit = 'pcs';
        $this->itemReorderLevel = 0;
        $this->itemActive = true;
    }

    public function deleteItem($itemId)
    {
        $item = InventoryItem::findOrFail($itemId);
        $item->delete();
        session()->flash('message', 'Artikel borttagen!');
    }

    // Station inventory methods
    public function openStationInventoryForm($stationInventoryId = null)
    {
        if ($stationInventoryId) {
            $this->selectedStationInventory = StationInventory::with('inventoryItem')->findOrFail($stationInventoryId);
            $this->currentQuantity = $this->selectedStationInventory->current_quantity;
            $this->minimumQuantity = $this->selectedStationInventory->minimum_quantity;
            $this->notes = $this->selectedStationInventory->notes ?? '';
        }

        $this->showStationInventoryForm = true;
    }

    public function saveStationInventory()
    {
        $this->validate([
            'currentQuantity' => 'required|numeric|min:0',
            'minimumQuantity' => 'required|numeric|min:0',
        ]);

        if ($this->selectedStationInventory) {
            $oldQuantity = $this->selectedStationInventory->current_quantity;
            $newQuantity = $this->currentQuantity;

            $this->selectedStationInventory->update([
                'current_quantity' => $newQuantity,
                'minimum_quantity' => $this->minimumQuantity,
                'notes' => $this->notes,
                'last_checked' => now(),
            ]);

            // Create transaction record if quantity changed
            if ($oldQuantity != $newQuantity) {
                InventoryTransaction::create([
                    'station_id' => $this->selectedStationInventory->station_id,
                    'inventory_item_id' => $this->selectedStationInventory->inventory_item_id,
                    'user_id' => Auth::id(),
                    'type' => 'adjust',
                    'quantity' => $newQuantity - $oldQuantity,
                    'balance_after' => $newQuantity,
                    'reason' => 'Manuell justering',
                    'notes' => $this->notes,
                ]);
            }

            session()->flash('message', 'Lager uppdaterat!');
        }

        $this->closeStationInventoryForm();
    }

    public function closeStationInventoryForm()
    {
        $this->showStationInventoryForm = false;
        $this->selectedStationInventory = null;
        $this->currentQuantity = 0;
        $this->minimumQuantity = 0;
        $this->notes = '';
    }

    // Tab and station selection
    public function selectTab($tab)
    {
        $this->selectedTab = $tab;
        $this->resetPage();
    }

    public function selectStation($stationId)
    {
        $this->selectedStationId = $stationId;
        $this->resetPage();
    }

    // Bulk item selection methods
    public function openAddItemsForm()
    {
        $this->showAddItemsForm = true;
        $this->selectedItems = [];
        $this->itemQuantities = [];
    }

    public function closeAddItemsForm()
    {
        $this->showAddItemsForm = false;
        $this->selectedItems = [];
        $this->itemQuantities = [];
    }

    public function saveSelectedItems()
    {
        if (empty($this->selectedItems)) {
            session()->flash('error', 'Du måste välja minst en artikel.');

            return;
        }

        $station = Station::findOrFail($this->selectedStationId);
        $created = 0;

        foreach ($this->selectedItems as $itemId) {
            $exists = StationInventory::where('station_id', $this->selectedStationId)
                ->where('inventory_item_id', $itemId)
                ->exists();

            if (! $exists) {
                $item = InventoryItem::find($itemId);
                $quantity = $this->itemQuantities[$itemId] ?? 0;

                StationInventory::create([
                    'station_id' => $this->selectedStationId,
                    'inventory_item_id' => $itemId,
                    'current_quantity' => $quantity,
                    'minimum_quantity' => $item->default_reorder_level,
                ]);
                $created++;
            }
        }

        $this->closeAddItemsForm();
        session()->flash('message', "Lade till {$created} artiklar till {$station->name}");
    }

    // Initialize station inventory for all items (legacy method - keeping for backwards compatibility)
    public function initializeStationInventory($stationId)
    {
        $station = Station::findOrFail($stationId);
        $items = InventoryItem::active()->get();

        $created = 0;
        foreach ($items as $item) {
            $exists = StationInventory::where('station_id', $stationId)
                ->where('inventory_item_id', $item->id)
                ->exists();

            if (! $exists) {
                StationInventory::create([
                    'station_id' => $stationId,
                    'inventory_item_id' => $item->id,
                    'current_quantity' => 0,
                    'minimum_quantity' => $item->default_reorder_level,
                ]);
                $created++;
            }
        }

        session()->flash('message', "Lade till {$created} artiklar till {$station->name}");
    }
}
