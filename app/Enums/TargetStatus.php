<?php

namespace App\Enums;

enum TargetStatus: string
{
  case PENDING = 'pending';

  case COMPLETED = 'completed';

  case EXPIRED = 'expired';
}
