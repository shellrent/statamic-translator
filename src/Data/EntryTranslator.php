<?php

namespace Aerni\Translator\Data;

use Aerni\Translator\Support\RequestValidator;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry;
use Statamic\Support\Str;
use Facades\Aerni\Translator\Contracts\TranslationService;

/**
 * @property Entry $entry
 */
class EntryTranslator extends BasicTranslator {
    protected function ensureCanProcess(): self {
        RequestValidator::canProcessEntry( $this->entry, $this->site );

        return $this;
    }

    protected function slug(): string {
        if( !array_key_exists( 'slug', $this->translatableFields() ) ) {
            return $this->entry->slug();
        }

        $slug = $this->entry->root()->slug();
        return TranslationService::translateText( Str::deslugify( $slug ), $this->targetLanguage(), 'text' );
    }

    protected function translate(): void {
        $this->entry->data( $this->translatedData() )
            ->slug( $this->slug() );
    }

    protected function rootData(): Collection {
        return $this->entry->root()->data();
    }
}
