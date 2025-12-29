<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseOrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public PurchaseOrder $po)
    {
        //
    }

    // Pakai database biar nongol di bell
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'        => 'po_created',
            'title'       => 'PO Baru Dibuat',
            'message'     => "PO {$this->po->po_number} berhasil dibuat.",
            'po_id'       => $this->po->id,
            'po_number'   => $this->po->po_number,
            'status'      => $this->po->status,
            'url'         => route('purchase-orders.show', $this->po->id),
            'created_by'  => $this->po->created_by,
            'created_at'  => $this->po->created_at?->toDateTimeString(),
        ];
    }
}
