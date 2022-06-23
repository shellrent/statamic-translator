<?php

namespace Aerni\Translator\Data\Concerns;

use Aerni\Translator\Support\Utils;
use Facades\Aerni\Translator\Contracts\TranslationService;
use Statamic\Facades\Site;

trait TranslatesData {

    private function isTranslatableValue( $value ) {
        if( empty( $value ) ) {
            return false;
        }

        if( is_numeric( $value ) ) {
            return false;
        }

        if( is_bool( $value ) ) {
            return false;
        }

        if( is_array( $value ) ) {
            return false;
        }

        if( TranslationService::detectLanguage( $value ) === $this->targetLanguage() ) {
            return false;
        }

        return true;
    }

    /**
     * Get the data to translate.
     *
     * @return array
     */
    protected function dataToTranslate(): array {
        return $this
            ->rootData()
            ->intersectByKeys( $this->translatableFields() )
            ->except( [ 'updated_by', 'updated_at' ] )
            ->toArray();
    }

    protected function translatedData(): array {
        $translatedFieldKeys = $this->getTranslatableFieldKeys();
        $data = $this->dataToTranslate();

        return $this->translateSetDataRecursive( $translatedFieldKeys, $data );
    }

    private function translateSetDataRecursive( array $validKeys, array $data ) {
        foreach( $data as $key => $value ) {
            if( !isset( $validKeys[$key] ) ) {
                continue;
            }

            if( is_array( $value ) and $validKeys[$key] === '@seo' ) {
                foreach( $value as $seoKey => $seoValue ) {
                    if( strpos( $seoValue, '@seo:' ) === 0 ) {
                        continue;
                    }

                    $data[$key][$seoKey] = $this->translateValue( $seoValue );
                }

                continue;
            }

            if( is_array( $value ) ) {
                foreach( $value as $itemKey => $item ) {
                    if( isset( $item['type'] ) ) {
                        if( isset( $validKeys[$key][$item['type']] ) ) {
                            $data[$key][$itemKey] = $this->translateSetDataRecursive( $validKeys[$key][$item['type']], $item );

                        } else {
                            $data[$key][$itemKey] = $this->translateSetDataRecursive( $validKeys[$key], $item );
                        }

                    } else {
                        $data[$key][$itemKey] = $this->translateSetDataRecursive( $validKeys[$key], $item );
                    }
                }

                continue;
            }

            $translated = $this->translateValue( $value );

            $data[$key] = $translated;
        }

        return $data;
    }


    /**
     * Get the language for translation.
     *
     * @return string
     */
    protected function targetLanguage(): string {
        return Site::get( $this->site )->shortLocale();
    }

    /**
     * Translate a given string value.
     *
     * @param mixed $value
     * @param string $key
     *
     * @return string
     */
    protected function translateValue( $value ) {
        if( !$this->isTranslatableValue( $value ) ) {
            return $value;
        }

        if( Utils::isHtml( $value ) ) {
            return TranslationService::translateText( $value, $this->targetLanguage(), 'html' );
        }

        return TranslationService::translateText( $value, $this->targetLanguage(), 'text' );
    }
}
