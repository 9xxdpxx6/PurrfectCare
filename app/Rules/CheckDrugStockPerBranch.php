<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\OrderItem;
use App\Models\Branch;

class CheckDrugStockPerBranch implements Rule
{
    protected $branchId;
    protected $failedDrugs = [];

    /**
     * Create a new rule instance.
     *
     * @param int $branchId
     * @return void
     */
    public function __construct(int $branchId)
    {
        $this->branchId = $branchId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Правило применяется к массиву items
        if ($attribute !== 'items' || !is_array($value)) {
            return true;
        }

        $drugItems = collect($value)->filter(function($item) {
            return isset($item['item_type']) && $item['item_type'] === 'drug';
        });

        if ($drugItems->isEmpty()) {
            return true;
        }

        $drugIds = $drugItems->pluck('item_id')->toArray();

        // Получаем количество препаратов в филиале
        $branch = Branch::find($this->branchId);
        $branchDrugs = $branch->drugs()
            ->withPivot('quantity')
            ->whereIn('drugs.id', $drugIds)
            ->get()
            ->keyBy('id');

        // При обновлении заказа нужно учесть уже заказанные препараты
        $orderId = request()->route('order');
        $currentOrderQuantities = [];
        
        if ($orderId) {
            // Получаем количество препаратов в текущем заказе
            $currentOrderItems = OrderItem::where('order_id', $orderId)
                ->where('item_type', 'drug')
                ->whereIn('item_id', $drugIds)
                ->get();
            
            foreach ($currentOrderItems as $item) {
                $currentOrderQuantities[$item->item_id] = ($currentOrderQuantities[$item->item_id] ?? 0) + $item->quantity;
            }
        }

        foreach ($drugItems as $item) {
            $drugId = $item['item_id'];
            $requestedQuantity = $item['quantity'] ?? 0;
            
            $branchDrug = $branchDrugs->get($drugId);
            $availableQuantity = $branchDrug ? $branchDrug->pivot->quantity : 0;
            
            // При обновлении добавляем уже заказанное количество
            if ($orderId && isset($currentOrderQuantities[$drugId])) {
                $availableQuantity += $currentOrderQuantities[$drugId];
            }
            
            if ($availableQuantity < $requestedQuantity) {
                $this->failedDrugs[] = [
                    'drug_id' => $drugId,
                    'requested' => $requestedQuantity,
                    'available' => $availableQuantity,
                    'branch_available' => $branchDrug ? $branchDrug->pivot->quantity : 0,
                    'current_order' => $currentOrderQuantities[$drugId] ?? 0
                ];
            }
        }

        $result = empty($this->failedDrugs);
        
        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (empty($this->failedDrugs)) {
            return 'Недостаточно препаратов на складе филиала.';
        }

        $messages = [];
        foreach ($this->failedDrugs as $failed) {
            $branchAvailable = $failed['branch_available'];
            $currentOrder = $failed['current_order'];
            
            if ($currentOrder > 0) {
                $messages[] = "Препарат ID {$failed['drug_id']}: запрошено {$failed['requested']}, доступно в филиале {$branchAvailable} + уже в заказе {$currentOrder} = {$failed['available']}";
            } else {
                $messages[] = "Препарат ID {$failed['drug_id']}: запрошено {$failed['requested']}, доступно в филиале {$failed['available']}";
            }
        }

        return 'Недостаточно препаратов на складе филиала: ' . implode('; ', $messages);
    }

    /**
     * Get the failed drugs for additional processing
     *
     * @return array
     */
    public function getFailedDrugs()
    {
        return $this->failedDrugs;
    }
}
