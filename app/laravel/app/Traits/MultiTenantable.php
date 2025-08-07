<?php

namespace App\Traits;

use App\Scopes\CompanyScope;

trait MultiTenantable
{
    protected static function bootMultiTenantable()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
