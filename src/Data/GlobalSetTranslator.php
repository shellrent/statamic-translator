<?php

namespace Aerni\Translator\Data;

use Aerni\Translator\Support\RequestValidator;
use Illuminate\Support\Collection;

class GlobalSetTranslator extends BasicTranslator {
    protected function ensureCanProcess(): self {
        RequestValidator::canProcessGlobalSet( $this->entry, $this->site );

        return $this;
    }

    protected function translate(): void {
        $this->entry->in( $this->site )
            ->data( $this->translatedData() );
    }

    protected function rootData(): Collection {
        return $this->entry->inDefaultSite()->data();
    }
}
