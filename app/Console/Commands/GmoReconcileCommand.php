<?php

namespace App\Console\Commands;

use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\Payment\GmoPaymentService;
use Illuminate\Console\Command;

class GmoReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmo:reconcile {--limit=50 : Number of payments to reconcile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile pending GMO payments';

    /**
     * Execute the console command.
     */
    public function handle(GmoPaymentService $service, PaymentRepositoryInterface $payments): void
    {
        $limit = (int) $this->option('limit');
        $pendingPayments = $payments->getPendingGmoPayments($limit);

        foreach ($pendingPayments as $payment) {
            $service->reconcileOne($payment->order_id);
        }

        $this->info('Reconcile completed');
    }
}
