<?php

namespace App\Http\Controllers;

use App\Http\Resources\WholesaleRequisitionItemResource;
use Illuminate\Http\JsonResponse;
use Webkul\B2BSuite\Http\Controllers\Shop\RequisitionListController as BaseRequisitionListController;

class WholesaleRequisitionListController extends BaseRequisitionListController
{
    /**
     * Get the requisition items.
     */
    public function getItems(): JsonResponse
    {
        $requisition = $this->customerRequisitionRepository->findOneWhere([
            'id'          => request()->id,
            'customer_id' => auth()->guard('customer')->user()->id,
        ]);

        if (! $requisition) {
            abort(404);
        }

        return new JsonResponse([
            'data' => WholesaleRequisitionItemResource::collection($requisition->items),
        ]);
    }

    /**
     * Updates the quantity of the items present in the requisition list.
     */
    public function updateItems(): JsonResponse
    {
        $this->validate(request(), [
            'requisition_id' => 'required|exists:b2b_customer_requisition_lists,id',
            'qty'            => 'array',
        ]);

        try {
            $data = request()->all();

            $requisition = $this->customerRequisitionRepository->with(['items'])->findOneWhere([
                'id'          => $data['requisition_id'],
                'customer_id' => auth()->guard('customer')->user()->id,
            ]);

            if (! $requisition) {
                abort(404);
            }

            $qtyArray = $data['qty'] ?? [];

            $requisitionItems = $qtyArray ? $requisition->items->whereIn('id', array_keys($qtyArray)) : $requisition->items;

            foreach ($requisitionItems as $item) {
                $qty = $qtyArray[$item->id] ?? 1;

                $additional = $item->additional ? json_decode($item->additional, true) : [];

                $item->update([
                    'qty'        => $qty,
                    'total'      => $item->price * $qty,
                    'base_total' => $item->base_price * $qty,
                    'additional' => $additional ? json_encode(array_merge($additional, ['quantity' => $qty])) : null,
                ]);
            }

            /**
             * Reload updated items
             */
            $updatedItems = $requisition->items()->get();

            return new JsonResponse([
                'data'    => WholesaleRequisitionItemResource::collection($updatedItems),
                'message' => trans('b2b::app.shop.customers.account.requisitions.item-updated'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Removes the item from the requisition if it exists.
     */
    public function destroyItems(): JsonResponse
    {
        $this->validate(request(), [
            'requisition_id'       => 'required|exists:b2b_customer_requisition_lists,id',
            'requisition_item_ids' => 'required|array',
        ]);

        $requisition = $this->customerRequisitionRepository->findOneWhere([
            'id'          => request()->input('requisition_id'),
            'customer_id' => auth()->guard('customer')->user()->id,
        ]);

        if (! $requisition) {
            abort(404);
        }

        $requisitionItems = $requisition->items->whereIn('id', request()->input('requisition_item_ids'));

        foreach ($requisitionItems as $item) {
            $item->delete();
        }

        /**
         * Reload updated items
         */
        $updatedItems = $requisition->items()->get();

        return new JsonResponse([
            'data'    => WholesaleRequisitionItemResource::collection($updatedItems),
            'message' => trans('b2b::app.shop.customers.account.requisitions.success-remove'),
        ]);
    }
}

