<?php
/**
 * ACF Gutenberg functions for creating ACF Gutenber blocks and dynamic templates/css/JS for this theme.
 * Requires ACF V5.8 or greater.
 * @package WordPress
 * @subpackage Open_Web_Office
 * @since Open Web Office 1.0
*/

if ( !defined( 'JS_PATH' ) ) {
    define("JS_PATH", get_template_directory_uri().'/assets/js/acf-blocks-js' );
}
if ( !defined( 'CSS_PATH' ) ) {
    define("CSS_PATH", get_template_directory_uri().'/assets/css/acf-blocks-css' );
}
if ( !defined( 'THEME_PATH' ) ) {
    define("THEME_PATH", get_template_directory().'/' );
}

if ( ! class_exists( 'OpenWebOffice_ACF_Gutenberg_Functions' ) ) {

    /**
	 * ACF Gutenberg blocks class
	 */
	class OpenWebOffice_ACF_Gutenberg_Functions {

        public function __construct() {

            add_filter( 'block_categories', array( $this,'openweboffice_add_acf_block_category'), 10, 2 );
            
            add_action( 'acf/init', array( $this,'openweboffice_register_dynamic_acf_blocks') );

            add_action( 'acf/render_field_group_settings', array( $this,'openweboffice_add_acf_fieldgroup_fields_for_blocks'), 10, 1 );

            add_filter( 'acf/validate_field_group', array( $this,'openweboffice_acf_fieldgroup_save_as_block'), 10, 1 );

            add_action( 'manage_edit-acf-field-group_columns', array( $this,'openweboffice_debug_acf_add_admin_column_block_template'), 35, 1 );

            add_action( 'manage_acf-field-group_posts_custom_column', array( $this,'openweboffice_debug_acf_output_block_template_column'), 10, 2 );

    
        }  
        
        /**
         * ACF register the Gutenberg blocks custom categories
        */
        public function openweboffice_add_acf_block_category( $categories, $post ) {
            return array_merge(
                $categories,
                array(
                    array(
                        'slug'  => 'acf-blocks',
                        'title' => __( 'Advanced Blocks' ),
                        'icon'  => '',
                    ),
                )
            );
        }

        /**
         * ACF Parsing of field groups that have the block logic enabled.
        */        
        public function openweboffice_register_dynamic_acf_blocks() {
            if ( function_exists( 'acf_register_block' ) ) {
                $field_groups = acf_get_field_groups();
        
                foreach ( $field_groups as $field_group ) {
                    if ( ! empty( $field_group['as_block'] ) && 1 === $field_group['as_block'] ) {
                        // Support for namespacing blocks with "Block: " and avoid naming the block with it.
                        $block_title = str_replace( 'Block: ', '', $field_group['title'] );
                        $block_slug  = sanitize_title( $block_title );                
                        acf_register_block(
                            array(
                                'name'            => $block_slug,
                                'title'           => $block_title,
                                'description'     => $field_group['description'] ?? '',
                                'render_callback' => array($this, 'openweboffice_render_dynamic_acf_block_partial'),
                                'category'        => ! empty( $field_group['block_category'] ) ? $field_group['block_category'] : 'acf-blocks',
                                'icon'            => ! empty( $field_group['block_icon'] ) ? $field_group['block_icon'] : 'welcome-widgets-menus',
                                'align'           => ! empty( $field_group['block_align'] ) ? $field_group['block_align'] : '',
                                'keywords'        => explode( ' ', $field_group['block_keywords'] ),
                                'post_types'      => $field_group['block_post_types'] ? $field_group['block_post_types'] : [],
                                'enqueue_assets'	=> array($this,'openweboffice_render_dynamic_acf_block_enqueue'),
                                'mode'            => ! empty( $field_group['block_mode'] ) ? $field_group['block_mode'] : 'preview',
                                'supports'        => [
                                    'align'            => ! empty( $field_group['block_alignments'] ) ? $field_group['block_alignments'] : false,
                                    'anchor'           => $field_group['block_anchor'] ?? true,
                                    'customClassNames' => $field_group['block_customClassNames'] ?? true,
                                    'multiple'         => $field_group['block_multiple'] ?? true,
                                    'reusable'         => $field_group['block_reusable'] ?? true,
                                ],
                            )
                        );
                    }
                }
            }
        }

        /**
         * Global render helper to parse to the proper css for each block type
         *
         * @param array $block the block being parsed.
         * @return void
        */
        public function openweboffice_render_dynamic_acf_block_enqueue( $block ){
            // Convert name ("acf/block_name") into path friendly slug.
            $slug  = str_replace( 'acf/', '', $block['name'] );
            $block_js_path = $slug.'.js';
            $block_css_path = $slug.'.css';
            wp_enqueue_script( $slug.'-js' , JS_PATH.'/'.$block_js_path, array('jquery'), '', true );
            wp_enqueue_style( $slug.'-css', CSS_PATH.'/'.$block_css_path );			
        }

        /**
         * Global render helper to parse to the proper template partial for each block type
         *
         * @param array $block the block being parsed.
         * @return void
        */
        public function openweboffice_render_dynamic_acf_block_partial( $block ) {
            // Convert name ("acf/block_name") into path friendly slug.
            $slug  = str_replace( 'acf/', '', $block['name'] );
            $title = $block['title'];

            // Allow partial's location to be filtered.
            $template_root  = THEME_PATH.'template-parts/acf-blocks';
            $block_template = "{$template_root}/{$slug}.php";
            
            
            // Allow partial's location to be filtered.
            $template_root_css  = THEME_PATH.'assets/css/acf-blocks-css';
            $block_template_css = "{$template_root_css}/{$slug}.css";
            
            // Allow partial's location to be filtered.
            $template_root_js  = THEME_PATH.'assets/js/acf-blocks-js';
            $block_template_js = "{$template_root_js}/{$slug}.js";
            
            // Attempt to include a template part from within the defined templates folder.
            if ( file_exists( $block_template ) ) {
                include $block_template;
            } else {
                // Template not found, add recomendation.
                $block_file = fopen( $block_template, "w") or die("Unable to open file!");
                $txt = '<section id="'.$slug.'">Add HTML code Here</section>';
                fwrite($block_file, $txt);
                fclose($block_file);
                //error_log( "[WARNING] Create {$block_template} to complete registration for the \"{$title}\" acf block." );
            }
            
            // Attempt to include a template part from within the defined templates folder.
            if ( !file_exists( $block_template_css ) ) {
                    
                // Template not found, add recomendation.
                $block_file_css = fopen( $block_template_css, "w") or die("Unable to open file!");
                $txt = '/* Add Block CSS Here */';
                fwrite($block_file_css, $txt);
                fclose($block_file_css);
                //error_log( "[WARNING] Create {$block_template} to complete registration for the \"{$title}\" acf block." );
            }
            
            // Attempt to include a template part from within the defined templates folder.
            if ( !file_exists( $block_template_js ) ) {
                
                // Template not found, add recomendation.
                $block_file_js = fopen( $block_template_js, "w") or die("Unable to open file!");
                $txt = '/* Add Block JS Here */
                        (function($){
                            /**
                             * initializeBlock
                             *
                             * Adds custom JavaScript to the block HTML.
                             *
                             * @since   1.0.0
                             *
                             * @param   object $block The block jQuery element.
                             * @param   object attributes The block attributes (only available when editing).
                             * @return  void
                             */
                            var initializeBlock = function( ) {
                                // Add your code here
                            }
                            // Initialize each block on page load (front end).
                            $(document).ready(function(){
                                initializeBlock(); 
                            });
                        
                            // Initialize dynamic block preview (editor).
                            if( window.acf ) {
                                window.acf.addAction( "render_block_preview/type=add_block_slug_here", initializeBlock );        
                            }
                        })(jQuery);';
                fwrite($block_file_js, $txt);
                fclose($block_file_js);
                //error_log( "[WARNING] Create {$block_template} to complete registration for the \"{$title}\" acf block." );
            }
        }


        /**
         * Add field groupoptions to convert field group to a gutenberg block.
         *
         * @param array $field_group Field group data.
         * @return void
        */
        public function openweboffice_add_acf_fieldgroup_fields_for_blocks( $field_group ) {
            acf_render_field_wrap(
                array(
                    'label'        => __( 'Gutenberg Block' ),
                    'instructions' => 'Turn this field group into a gutenberg block. (Will overwrite any location rules!)',
                    'type'         => 'true_false',
                    'name'         => 'as_block',
                    'key'          => 'as_block', // for conditional logic.
                    'prefix'       => 'acf_field_group',
                    'value'        => $field_group['as_block'] ?? 0,
                    'ui'           => 1,
                )
            );

            $when_as_block_is_enabled = [
                'field'    => 'as_block',
                'operator' => '==',
                'value'    => 1,
            ];

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Icon' ),
                    'instructions'      => 'Can be any of <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">WordPress’ Dashicons</a>, or a custom svg element.',
                    'type'              => 'text',
                    'placeholder'       => 'welcome-widgets-menus',
                    'name'              => 'block_icon',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_icon'] ?? 'welcome-widgets-menus',
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            $category_chocies = array(
                'common'     => 'Common',
                'formatting' => 'Formatting',
                'layout'     => 'Layout',
                'widgets'    => 'Widgets',
                'embed'      => 'Embed',
                'acf-blocks' => 'Advanced Blocks',
            );

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Category' ),
                    'instructions'      => 'Blocks are grouped into categories to help users browse and discover them.',
                    'type'              => 'select',
                    'name'              => 'block_category',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_category'] ?? 'acf-blocks',
                    'default'           => 'acf-blocks',
                    'choices'           => $category_chocies,
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Keywords' ),
                    'instructions'      => 'A block may have aliases that help users discover it while searching.',
                    'type'              => 'text',
                    'name'              => 'block_keywords',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_keywords'] ?? '',
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            $_post_types       = get_post_types( null, 'objects' );
            $exclude_acf       = [ 'acf-field', 'acf-field-group' ];
            $post_type_chocies = array_reduce(
                $_post_types,
                function( $carry, $item ) use ( $exclude_acf ) {
                    if ( in_array( $item->name, $exclude_acf, true ) || $item->_builtin && ! $item->public ) {
                        return $carry;
                    }
                    $carry[ $item->name ] = $item->label;
                    return $carry;
                },
                []
            );

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Supported Post Types' ),
                    'instructions'      => 'Post types that can use this block. (Leave blank to for all post types)',
                    'type'              => 'select',
                    'multiple'          => 1,
                    'ui'                => 1,
                    'name'              => 'block_post_types',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_post_types'] ?? 'acf-blocks',
                    'choices'           => $post_type_chocies,
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            $alignment_choices = [
                'left'   => 'Left',
                'center' => 'Center',
                'right'  => 'Right',
            ];

            if ( get_theme_support( 'align-wide' ) ) {
                $alignment_choices['wide'] = 'Wide';
                $alignment_choices['full'] = 'Full';
            }

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Alignment Options' ),
                    'instructions'      => 'Alignment options to allow.',
                    'type'              => 'checkbox',
                    'name'              => 'block_alignments',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_alignments'] ?? [ 'left', 'center', 'right' ],
                    'choices'           => $alignment_choices,
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            $default_alignment_choices = [
                null => '-- Choose Alignment Options First --',
            ];
            if ( ! empty( $field_group['block_alignments'] ) ) {
                $default_alignment_choices = wp_array_slice_assoc( $alignment_choices, $field_group['block_alignments'] );
            }
            acf_render_field_wrap(
                array(
                    'label'             => __( 'Default Alignment' ),
                    'instructions'      => 'Select a default from the chosen Alignmet Options values. (save to update available options)',
                    'type'              => 'select',
                    'name'              => 'block_align',
                    'prefix'            => 'acf_field_group',
                    'value'             => $field_group['block_align'] ?? [ 'left' ],
                    'choices'           => $default_alignment_choices,
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            acf_render_field_wrap(
                array(
                    'label'             => __( 'Default Mode' ),
                    'instructions'      => 'What to display when adding this block.',
                    'type'              => 'radio',
                    'name'              => 'block_mode',
                    'value'             => $field_group['block_mode'] ?? 'preview',
                    'prefix'            => 'acf_field_group',
                    'choices'           => [
                        'preview' => 'Preview',
                        'edit'    => 'Editor',
                    ],
                    'conditional_logic' => $when_as_block_is_enabled,
                )
            );

            $misc_supports_options = [
                [
                    'name'         => 'block_anchor',
                    'label'        => 'Anchor',
                    'instructions' => 'Support for an anchor link to the specific block.',
                    'default'      => 1,
                ],
                [
                    'name'         => 'block_customClassNames',
                    'label'        => 'Custom Class Names',
                    'instructions' => 'Support for the custom class names input.',
                    'default'      => 1,
                ],
                [
                    'name'         => 'block_multiple',
                    'label'        => 'Multiple in a post',
                    'instructions' => 'Allow multiple instances of this block in a post',
                    'default'      => 1,
                ],
                [
                    'name'         => 'block_reusable',
                    'label'        => 'Reusable block',
                    'instructions' => 'Allow this block to be converted to a reusable block.',
                    'default'      => 1,
                ],
            ];

            foreach ( $misc_supports_options as $supports ) {
                acf_render_field_wrap(
                    array(
                        'label'             => $supports['label'],
                        'instructions'      => $supports['instructions'],
                        'type'              => 'true_false',
                        'name'              => $supports['name'],
                        'prefix'            => 'acf_field_group',
                        'value'             => $field_group[ $supports['name'] ] ?? $supports['default'],
                        'default'           => $supports['default'],
                        'conditional_logic' => $when_as_block_is_enabled,
                        'ui'                => 1,
                    )
                );
            }
        }

        /**
         * Force field to be saved as a block location
         *
         * @param array $field_group field group.
         * @return array $field_group
        */
        public function openweboffice_acf_fieldgroup_save_as_block( $field_group ) {
            if ( ! empty( $field_group['as_block'] ) && 1 === $field_group['as_block'] ) {
                $title = str_replace( 'Block: ', '', $field_group['title'] );
                $slug  = sanitize_title( $title );
                
                $field_group['location'] = [
                    [
                        [
                            'param'    => 'block',
                            'operator' => '==',
                            'value'    => 'acf/' . $slug,
                        ],
                    ],
                ];
            }
            
            return $field_group;
        }

        
        /**
         * DEBUG: Add block template column to ACF field Groups
         *
         * @param array $defaults existing columns.
         * @return array
        */
        public function openweboffice_debug_acf_add_admin_column_block_template( $defaults ) {
            $defaults['block_template'] = 'Block Template Path';

            return $defaults;
        }

        /**
         * DEBUG: Output location of block template
         *
         * @param string  $column current column.
         * @param integer $post_id current post id.
         * @return void
        */
        public function openweboffice_debug_acf_output_block_template_column( $column, $post_id ) {
            if ( 'block_template' === $column ) {
                global $post;
                $field_group = (array) maybe_unserialize( $post->post_content );

                if ( ! empty( $field_group['as_block'] ) && $field_group['as_block'] ) {
                    $title = str_replace( 'Block: ', '', $post->post_title );
                    $slug  = sanitize_title( $title );
                    $template_name = 'template-parts/acf-blocks/'.$slug.'.php';
                    $template_root  = THEME_PATH.'template-parts/acf-blocks';
                    $template      = "{$template_root}/{$slug}.php";

                    echo '<code>' . $template_name . '</code>';
                    if (!file_exists( $template )) {
                        echo '<br/><strong style="color: red;">TEMPLATE MISSING</strong>';
                    }
                }
            }
        }        

    }
    
}
new OpenWebOffice_ACF_Gutenberg_Functions();