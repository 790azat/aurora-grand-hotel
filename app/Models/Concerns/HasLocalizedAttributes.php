<?php

namespace App\Models\Concerns;

/**
 * Columns stored as `{field}_en` / `{field}_ru` are readable as `$model->field`
 * in the current locale, falling back to English.
 */
trait HasLocalizedAttributes
{
    public function getAttribute($key)
    {
        if (in_array($key, $this->localized ?? [], true)) {
            $locale = app()->getLocale();

            return parent::getAttribute("{$key}_{$locale}") ?: parent::getAttribute("{$key}_en");
        }

        return parent::getAttribute($key);
    }
}
