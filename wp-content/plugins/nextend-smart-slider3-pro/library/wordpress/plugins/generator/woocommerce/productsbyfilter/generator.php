<?php

N2Loader::import('libraries.slider.generator.abstract', 'smartslider');

class N2GeneratorWooCommerceProductsByFilter extends N2GeneratorAbstract {

    protected function _getData($count, $startIndex) {
        $data = array();

        $categories = explode('||', $this->data->get('categories', ''));

        $tax_query = array();
        if (!in_array(0, $categories)) {
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $categories
            );
        }

        $tags = explode('||', $this->data->get('tags', ''));

        if (!in_array(0, $tags)) {
            $tax_query[] = array(
                'taxonomy' => 'product_tag',
                'field'    => 'term_id',
                'terms'    => $tags
            );
        }
				
        switch ($this->data->get('featured', '0')) {
            case 1:
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
					'operator' => 'IN',
				);
                break;
            case -1:
				$tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
					'operator' => 'NOT IN',
				);
                break;
        }

        $meta_query = array();

        $order = explode("|*|", $this->data->get('categoryorder', 'creation_date|*|desc'));
        if (substr($order[0], 0, 1) == '_') {
            $orderBy      = 'meta_value_num'; //meta_value = strval, meta_value_num = intval
            $meta_query[] = array(
                'key'     => $order[0],
                'compare' => 'LIKE'
            );
        } else {
            $orderBy = $order[0];
        }

        switch ($this->data->get('instock', '1')) {
            case 1:
                $meta_query[] = array(
                    'key'   => '_stock_status',
                    'value' => 'instock'
                );
                break;
            case -1:
                $meta_query[] = array(
                    'key'   => '_stock_status',
                    'value' => 'outofstock'
                );
                break;
        }

        switch ($this->data->get('downloadable', '0')) {
            case 1:
                $meta_query[] = array(
                    'key'   => '_downloadable',
                    'value' => 'yes'
                );
                break;
            case -1:
                $meta_query[] = array(
                    'key'   => '_downloadable',
                    'value' => 'no'
                );
                break;
        }

        switch ($this->data->get('virtual', '0')) {
            case 1:
                $meta_query[] = array(
                    'key'   => '_virtual',
                    'value' => 'yes'
                );
                break;
            case -1:
                $meta_query[] = array(
                    'key'   => '_virtual',
                    'value' => 'no'
                );
                break;
        }

        $args = array(
            'offset'           => $startIndex,
            'posts_per_page'   => $count,
            'orderby'          => $orderBy,
            'order'            => $order[1],
            'post_type'        => 'product',
            'tax_query'        => $tax_query,
            'meta_query'       => $meta_query,
            'suppress_filters' => 0
        );

        $textVars = $this->data->get('textvars', '');

        $productFactory = new WC_Product_Factory();
        $i              = 0;
        $posts          = get_posts($args);
        for ($j = 0; $j < count($posts); $j++) {
            $product = $productFactory->get_product($posts[$j]);
            if ($product && $product->is_visible()) {
                $image     = wp_get_attachment_url(get_post_thumbnail_id($product->id));
                $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product->id, 'thumbnail'));
                if ($thumbnail[0] != null) {
                    $thumbnail = $thumbnail[0];
                } else {
                    $thumbnail = $image;
                }
                $data[$i] = array(
                    'title'                  => $product->get_title(),
                    'url'                    => $product->get_permalink(),
                    'description'            => $product->get_post_data()->post_content,
                    'image'                  => N2ImageHelper::dynamic($image),
                    'thumbnail'              => N2ImageHelper::dynamic($thumbnail),
                    'price'                  => $product->get_price_html(),
                    'price_without_currency' => $product->get_price(),
                    'regular_price'          => wc_price($product->get_regular_price()),
                    'price_with_tax'         => wc_price($product->get_price_including_tax()),
                    'rating'                 => $product->get_average_rating(),
                    'tags'                   => $product->get_tags()
            );

                if ($product->has_child()) {
                    $data[$i] += array(
                        'variation_min_price'                  => $product->get_variation_regular_price('min'),
                        'variation_min_price_currency'         => wc_price($product->get_variation_regular_price('min')),
                        'variation_min_regular_price'          => $product->get_variation_sale_price('min'),
                        'variation_min_regular_price_currency' => wc_price($product->get_variation_sale_price('min')),
                        'variation_min_sale_price'             => $product->get_variation_price('min'),
                        'variation_min_sale_price_currency'    => wc_price($product->get_variation_price('min')),
                        'variation_max_price'                  => $product->get_variation_regular_price('max'),
                        'variation_max_price_currency'         => wc_price($product->get_variation_regular_price('max')),
                        'variation_max_regular_price'          => $product->get_variation_sale_price('max'),
                        'variation_max_regular_price_currency' => wc_price($product->get_variation_sale_price('max')),
                        'variation_max_sale_price'             => $product->get_variation_price('max'),
                        'variation_max_sale_price_currency'    => wc_price($product->get_variation_price('max'))
                    );
                }

                if ($product->is_on_sale()) {
                    $data[$i]['sale_price'] = wc_price($product->get_sale_price());
                } else {
                    $data[$i]['sale_price'] = $data[$j]['price'];
                }

                $product_meta = get_post_meta($product->id);
                foreach ($product_meta as $meta => $value) {
                    if (@unserialize($value[0]) !== false || $value[0] === 'b:0;') {
                        $product_attributes = unserialize($value[0]);
                        if (!empty($product_attributes)) {
                            foreach ($product_attributes as $key => $value2) {
                                if (is_array($value2)) {
                                    foreach ($value2 as $k => $v) {
                                        if (strpos($v, '|') !== false) {
                                            $helper = explode("|", $v);
                                            for ($fv = 0; $fv < count($helper); $fv++) {
                                                $data[$i][$key . '_' . $k . '_' . $fv] = $helper[$fv];
                                            }
                                        } else {
                                            $data[$i][$key . '_' . $k] = $v;
                                        }
                                    }
                                } else {
                                    $data[$i][$meta] = $value2;
                                }
                            }
                        } else {
                            $data[$i][$meta] = '';
                        }
                    } else {
                        if (is_array($value) && count($value) > 1) {
                            for ($fv = 0; $fv < count($value); $fv++) {
                                $v                           = $value[$fv];
                                $data[$i][$meta . "_" . $fv] = $v;
                            }
                        } else {
                            $data[$i][$meta] = $value[0];
                        }
                    }

                }

                $categories = wp_get_post_terms($product->id, 'product_cat');

                if (!empty($categories) && is_array($categories)) {
                    $k = 1;
                    foreach ($categories as $category) {
                        $catId                                     = (int)$category->term_id;
                        $data[$i]['category' . $k]                 = $category->name;
                        $data[$i]['category' . $k . 'link']        = get_term_link($catId, 'product_cat');
                        $data[$i]['category' . $k . 'description'] = $category->description;
                        $data[$i]['category' . $k . 'ID']          = $catId;
                        $k++;
                    }
                }

                $data[$i]['ID'] = $product->id;

                if (!empty($textVars)) {
                    $lines = preg_split('/$\R?^/m', $textVars);
                    foreach ($lines AS $line) {
                        $variables = explode('||', $line);
                        if (count($variables) == 4 && isset($data[$i][$variables[0]])) {
                            if ($data[$i][$variables[0]] == $variables[1]) {
                                $data[$i]['text' . $variables[0]] = $variables[2];
                            } else {
                                $data[$i]['text' . $variables[0]] = $variables[3];
                            }
                        }
                    }
                }
                $i++;
            }
        }

        return $data;
    }

}