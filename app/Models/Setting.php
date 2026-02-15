<?php

namespace App\Models;

/**
 * Alias for Modules\Core\Models\Setting
 *
 * This class exists because many parts of the codebase (views, migrations)
 * reference \App\Models\Setting, but the actual implementation lives in
 * Modules\Core\Models\Setting. This alias ensures backward compatibility.
 */
class Setting extends \Modules\Core\Models\Setting
{
    // Pure alias — all logic lives in Modules\Core\Models\Setting
}
