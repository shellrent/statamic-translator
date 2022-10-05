<?php

namespace Aerni\Translator\Data\Concerns;

trait PreparesData {

    /**
     * Get the blueprint fields that are localizable.
     *
     * @return array
     */
    private function localizableFields(): array {
        return $this->entry->blueprint()
            ->fields()
            ->localizable()
            ->all()
            ->toArray();
    }

    /**
     * Filter the fields by supported fieldtypes.
     *
     * @param array $fields
     *
     * @return array
     */
    private function filterSupportedFieldtypes( array $fields ): array {
        return collect( $fields )
            ->map( function( $item ) {
                switch( $item['type'] ?? null ) {
                    case 'replicator':
                        break;
                    case 'bard':
                        $item['sets'] = collect( $item['sets'] ?? [] )
                            ->map( function( $set ) {
                                $set['fields'] = $this->filterSupportedFieldtypes( $set['fields'] );

                                return $set;
                            } )
                            ->filter( function( $set ) {
                                return count( $set['fields'] ) > 0;
                            } )
                            ->toArray();

                        break;
                    case 'grid':
                        $item['fields'] = $this->filterSupportedFieldtypes( $item['fields'] ?? [] );

                        break;
                }

                return $item;
            } )
            ->filter( function( $item ) {
                $supportedFieldtypes = [
                    'array', 'bard', 'grid', 'list', 'markdown', 'replicator',
                    'slug', 'table', 'tags', 'text', 'textarea', 'seo_pro'
                ];

                if( !isset( $item['type'] ) ) {
                    return false;
                }

                $supported = in_array( $item['type'], $supportedFieldtypes );

                if( !$supported ) {
                    return false;
                }

                switch( $item['type'] ?? null ) {
                    case 'replicator':
                        return count( $item['sets'] ?? [] ) > 0;

                        break;
                    case 'grid':
                        return count( $item['fields'] ?? [] ) > 0;

                        break;
                    default:
                        break;
                }

                return true;
            } )->toArray();
    }


    /**
     * Get the keys of translatable fields.
     *
     * @param array $fields
     *
     * @return array
     */
    private function generateTranslatableFieldKeys( array $fields ): array {
        return collect( $fields )->map( function( $item, $key ) {
            switch( $item['type'] ?? null ) {
                case 'bard':
                    return collect( $item['sets'] )
                        ->map( function( $set ) {
                            $set['fields'] = $this->generateTranslatableFieldKeys( $set['fields'] );

                            return $set['fields'];
                        } )->put( 'text', [] );

                    break;

                case 'replicator':
                    return collect( $item['sets'] )
                        ->map( function( $set, $key ) {
                            $replicatorSetFields = [];

                            foreach( $set['fields'] as $replicatorSetField ) {
                                if( !isset( $replicatorSetField['handle'] ) ) {
                                    continue;
                                }

                                $replicatorSetFieldKey = $replicatorSetField['handle'];
                                //chiodato icon per evitare di tradurre icone nei replicator
                                if( $replicatorSetFieldKey == 'icon' ) {
                                    continue;
                                }

                                if( strpos( $replicatorSetFieldKey, 'icon-' ) === 0 ) {
                                    continue;
                                }

                                if( strpos( $replicatorSetFieldKey, 'icon_' ) === 0 ) {
                                    continue;
                                }

                                $replicatorSetFields = array_merge( $replicatorSetFields, $this->generateTranslatableFieldKeys( [
                                    $replicatorSetFieldKey => $replicatorSetField['field']
                                ] ));
                            }

                            return $replicatorSetFields;
                        } );

                    break;

                case 'grid':
                    $item['fields'] = $this->generateTranslatableFieldKeys( $item['fields'] );

                    return $item['fields'];

                    break;

                case 'array':
                    if( array_key_exists( 'keys', $item ) ) {
                        return $item['keys'];
                    }

                    break;
                case 'seo_pro':
                    return '@seo';
            }

            return $key;
        } )->toArray();
    }


    /**
     * Get the translatable fields. A field is considered translatable
     * when 'localizable' is set to 'true' in the blueprint and
     * the type of field is supported by Translator.
     *
     * @return array
     */
    protected function translatableFields(): array {
        return $this->filterSupportedFieldtypes( $this->localizableFields() );
    }


    /**
     * Get the keys of translatable fields.
     *
     * @return array
     */
    protected function getTranslatableFieldKeys(): array {
        return $this->generateTranslatableFieldKeys( $this->translatableFields() );
    }
}
