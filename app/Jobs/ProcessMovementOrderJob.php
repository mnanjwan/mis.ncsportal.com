<?php

namespace App\Jobs;

use App\Models\MovementOrder;
use App\Services\PostingWorkflowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMovementOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $officerIds;

    /**
     * Create a new job instance.
     */
    public function __construct(MovementOrder $order, array $officerIds = [])
    {
        $this->order = $order;
        $this->officerIds = $officerIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Reload the order to ensure we have fresh data
            $order = MovementOrder::find($this->order->id);
            
            if (!$order) {
                Log::error("Movement Order not found: {$this->order->id}");
                return;
            }

            // Get postings to process
            $postings = $order->postings()->whereNull('documented_at')->get();
            
            if ($postings->isEmpty()) {
                Log::info("No postings to process for Movement Order: {$order->order_number}");
                return;
            }

            // If officerIds specified, filter postings
            $officerIdsToProcess = !empty($this->officerIds) 
                ? $this->officerIds 
                : $postings->pluck('officer_id')->toArray();

            // Process workflow
            $workflowService = new PostingWorkflowService();
            $workflowService->processMovementOrder($order, $officerIdsToProcess);
            
            Log::info("Movement Order processed successfully: {$order->order_number}");
        } catch (\Exception $e) {
            Log::error("Failed to process movement order job: " . $e->getMessage(), [
                'order_id' => $this->order->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

