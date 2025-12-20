<?php

namespace App\Http\Controllers\Api;

use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends ApiBaseController
{
    // GET /api/v1/admin/refunds
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = Refund::query();
        
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $refunds = $query->orderBy('created_at', 'desc')->get();
        return $this->success($refunds, 'Refunds retrieved');
    }

    // POST /api/v1/admin/refunds
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'status' => 'required|in:pending,approved,processed,rejected'
        ]);

        try {
            $refund = Refund::create($validated);
            return $this->success($refund, 'Refund created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create refund: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/refunds/{id}
    public function show($id)
    {
        $refund = Refund::find($id);
        
        if (!$refund) {
            return $this->error('Refund not found', null, 404);
        }

        return $this->success($refund, 'Refund retrieved');
    }

    // PUT /api/v1/admin/refunds/{id}
    public function update(Request $request, $id)
    {
        $refund = Refund::find($id);
        
        if (!$refund) {
            return $this->error('Refund not found', null, 404);
        }

        $validated = $request->validate([
            'order_id' => 'sometimes|required|string',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'reason' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:pending,approved,processed,rejected'
        ]);

        try {
            $refund->update($validated);
            return $this->success($refund, 'Refund updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update refund: ' . $e->getMessage(), null, 400);
        }
    }

    // PATCH /api/v1/admin/refunds/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $refund = Refund::find($id);
        
        if (!$refund) {
            return $this->error('Refund not found', null, 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,processed,rejected'
        ]);

        try {
            $refund->update($validated);
            return $this->success($refund, 'Refund status updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update refund status: ' . $e->getMessage(), null, 400);
        }
    }

    // DELETE /api/v1/admin/refunds/{id}
    public function destroy($id)
    {
        $refund = Refund::find($id);
        
        if (!$refund) {
            return $this->error('Refund not found', null, 404);
        }

        try {
            $refund->delete();
            return $this->success(null, 'Refund deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete refund: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/refunds/stats
    public function getStats()
    {
        $stats = [
            'pending' => Refund::where('status', 'pending')->count(),
            'approved' => Refund::where('status', 'approved')->count(),
            'processed' => Refund::where('status', 'processed')->count(),
            'rejected' => Refund::where('status', 'rejected')->count(),
            'total_amount_pending' => Refund::where('status', 'pending')->sum('amount'),
            'total_amount_processed' => Refund::where('status', 'processed')->sum('amount'),
        ];

        return $this->success($stats, 'Refund statistics retrieved');
    }
}
