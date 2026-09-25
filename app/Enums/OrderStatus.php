<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::SHIPPED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [], // لا يمكن تغيير الحالة بعدها

        };
    }
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }
}
