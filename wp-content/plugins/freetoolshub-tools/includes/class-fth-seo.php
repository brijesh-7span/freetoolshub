<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lightweight SEO for FreeToolsHub: titles, meta descriptions, canonical URLs,
 * Open Graph / Twitter tags, JSON-LD structured data and sitemap tweaks.
 *
 * Steps aside automatically when a full SEO plugin (Yoast, Rank Math, AIOSEO,
 * SEOPress) is active, so tags are never output twice.
 */
class FTH_SEO
{

    const TITLE_MAX       = 60;
    const DESCRIPTION_MAX = 160;

    public function __construct()
    {

        // Breadcrumbs are used by the theme even when another SEO plugin is active.
        if (self::seo_plugin_active()) {
            return;
        }

        add_filter('pre_get_document_title', array($this, 'filter_title'), 20);
        add_action('wp_head', array($this, 'output_head_tags'), 1);
        add_filter('wp_robots', array($this, 'filter_robots'));

        // XML sitemap (WordPress core /wp-sitemap.xml)
        add_filter('wp_sitemaps_add_provider', array($this, 'filter_sitemap_providers'), 10, 2);
        add_filter('wp_sitemaps_posts_entry', array($this, 'filter_sitemap_entry'), 10, 2);

        // Admin: per-tool SEO title / description
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post_tool', array($this, 'save_meta_box'));
    }


    /**
     * Whether a dedicated SEO plugin is handling titles, meta and schema.
     */
    public static function seo_plugin_active()
    {

        return defined('WPSEO_VERSION')
            || defined('RANK_MATH_VERSION')
            || defined('AIOSEO_VERSION')
            || defined('SEOPRESS_VERSION');
    }


    /* --------------------------------------------------------------------
     * Titles + descriptions
     * ----------------------------------------------------------------- */

    /**
     * Append " | Site Name" when it still fits within ~60 characters.
     */
    private function with_site_name($title)
    {

        $full = $title . ' | ' . get_bloginfo('name');

        return mb_strlen($full) <= self::TITLE_MAX ? $full : $title;
    }

    /**
     * Shorten to a meta-description length at a word boundary.
     */
    private function trim_description($text)
    {

        $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($text)));

        if (mb_strlen($text) <= self::DESCRIPTION_MAX) {
            return $text;
        }

        $cut = mb_substr($text, 0, self::DESCRIPTION_MAX - 1);
        $cut = preg_replace('/\s+\S*$/u', '', $cut);

        return rtrim($cut, ' ,.;:–-') . '…';
    }

    /**
     * Published tools, optionally limited to one category.
     *
     * @return WP_Post[]
     */
    private function get_tools($term_id = 0)
    {

        $args = array(
            'post_type'      => 'tool',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => array('menu_order' => 'ASC', 'title' => 'ASC'),
            'no_found_rows'  => true,
        );

        if ($term_id) {
            $args['tax_query'] = array(array('taxonomy' => 'tool_category', 'terms' => $term_id));
        }

        return get_posts($args);
    }

    /**
     * SEO title for the current page, or '' to keep WordPress's default.
     */
    public function get_title()
    {

        if (is_singular('tool')) {

            $post_id  = get_queried_object_id();
            $override = get_post_meta($post_id, '_fth_seo_title', true);

            if ($override !== '') {
                return $override;
            }

            $content = FTH_Tool_Content::get_for_post($post_id);

            return $this->with_site_name($content['seo_title'] !== ''
                /* translators: %s: tool name */
                ? $content['seo_title']
                : sprintf(__('%s – Free Online Tool', 'freetoolshub-tools'), get_the_title($post_id)));
        }

        if (is_post_type_archive('tool')) {
            return $this->with_site_name(__('All Free Online Tools', 'freetoolshub-tools'));
        }

        if (is_tax('tool_category')) {
            /* translators: %s: category name, e.g. Image Tools */
            return $this->with_site_name(sprintf(__('Free Online %s', 'freetoolshub-tools'), single_term_title('', false)));
        }

        if (is_front_page()) {
            $tagline = get_bloginfo('description');

            return get_bloginfo('name') . ' – ' . ($tagline !== ''
                ? $tagline
                : __('Free Online Tools to Make Your Work Easier', 'freetoolshub-tools'));
        }

        return '';
    }

    /**
     * Meta description for the current page, or '' for none.
     */
    public function get_description()
    {

        if (is_singular('tool')) {

            $post_id  = get_queried_object_id();
            $override = get_post_meta($post_id, '_fth_seo_description', true);

            if ($override !== '') {
                return $this->trim_description($override);
            }

            $content = FTH_Tool_Content::get_for_post($post_id);

            return $this->trim_description($content['seo_description'] !== ''
                ? $content['seo_description']
                : get_the_excerpt($post_id));
        }

        if (is_post_type_archive('tool')) {

            $names = wp_list_pluck(array_slice($this->get_tools(), 0, 4), 'post_title');

            return $this->trim_description(sprintf(
                /* translators: 1: number of tools, 2: list of tool names */
                __('Browse %1$d free online tools, including %2$s. Fast, private and no sign-up needed.', 'freetoolshub-tools'),
                count($this->get_tools()),
                implode(', ', $names)
            ));
        }

        if (is_tax('tool_category')) {

            $term = get_queried_object();

            if ($term->description !== '') {
                return $this->trim_description($term->description);
            }

            return $this->trim_description(sprintf(
                /* translators: 1: category name, 2: list of tool names */
                __('Free online %1$s: %2$s. Fast, private tools that work in your browser with no sign-up.', 'freetoolshub-tools'),
                mb_strtolower($term->name),
                implode(', ', wp_list_pluck($this->get_tools($term->term_id), 'post_title'))
            ));
        }

        if (is_front_page()) {
            return __('Free online tools for text, images, developers and everyday calculations. Fast and private: everything runs in your browser, no sign-up needed.', 'freetoolshub-tools');
        }

        return '';
    }

    /**
     * Canonical URL for pages WordPress doesn't add one to (it already handles single posts).
     * Query strings such as ?q= are dropped so filtered views point at the main page.
     */
    private function get_canonical()
    {

        if (is_front_page() && !is_paged()) {
            return home_url('/');
        }

        if (is_post_type_archive('tool')) {
            return get_post_type_archive_link('tool');
        }

        if (is_tax('tool_category')) {
            return get_term_link(get_queried_object());
        }

        return '';
    }

    public function filter_title($title)
    {

        $seo_title = $this->get_title();

        return $seo_title !== '' ? $seo_title : $title;
    }


    /* --------------------------------------------------------------------
     * Robots
     * ----------------------------------------------------------------- */

    /**
     * Keep filtered/searched directory views (/tools/?q=…, ?category=…) out of
     * search results; the unfiltered pages are the ones to index.
     */
    public function filter_robots($robots)
    {

        $is_directory = is_post_type_archive('tool') || is_tax('tool_category');

        if ($is_directory && (isset($_GET['q']) || isset($_GET['category']))) {
            $robots['noindex'] = true;

            if (empty($robots['nofollow'])) {
                $robots['follow'] = true;
            }
        }

        return $robots;
    }


    /* --------------------------------------------------------------------
     * <head> output
     * ----------------------------------------------------------------- */

    public function output_head_tags()
    {

        $title       = $this->get_title();
        $description = $this->get_description();
        $canonical   = $this->get_canonical();
        $url         = is_singular() ? get_permalink() : $canonical;
        $image       = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'large') : '';

        /**
         * Filter the fallback social sharing image (e.g. a branded 1200×630 image).
         */
        $image = $image ? $image : apply_filters('fth_seo_default_image', '');

        echo "\n<!-- FreeToolsHub SEO -->\n";

        if ($description !== '') {
            printf('<meta name="description" content="%s">' . "\n", esc_attr($description));
        }

        if ($canonical !== '') {
            printf('<link rel="canonical" href="%s">' . "\n", esc_url($canonical));
        }

        if ($title !== '' && $url) {
            $og = array(
                'og:locale'      => get_locale(),
                'og:type'        => 'website',
                'og:site_name'   => get_bloginfo('name'),
                'og:title'       => $title,
                'og:description' => $description,
                'og:url'         => $url,
                'og:image'       => $image,
            );

            foreach (array_filter($og) as $property => $value) {
                printf('<meta property="%s" content="%s">' . "\n", esc_attr($property), esc_attr($value));
            }

            printf('<meta name="twitter:card" content="%s">' . "\n", $image ? 'summary_large_image' : 'summary');
        }

        $schema = $this->get_schema();

        if ($schema) {
            echo '<script type="application/ld+json">' .
                wp_json_encode(array('@context' => 'https://schema.org', '@graph' => $schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) .
                "</script>\n";
        }

        echo "<!-- / FreeToolsHub SEO -->\n\n";
    }


    /* --------------------------------------------------------------------
     * Breadcrumbs (shared by the visible trail in the theme and the schema)
     * ----------------------------------------------------------------- */

    /**
     * Breadcrumb trail for the current page: array of array('name' => '', 'url' => '').
     * The last item is the current page. Empty on pages without a trail.
     */
    public static function get_breadcrumbs()
    {

        $home  = array('name' => __('Home', 'freetoolshub-tools'), 'url' => home_url('/'));
        $tools = array('name' => __('Tools', 'freetoolshub-tools'), 'url' => get_post_type_archive_link('tool'));

        if (is_singular('tool')) {

            $trail = array($home, $tools);
            $terms = get_the_terms(get_queried_object_id(), 'tool_category');

            if ($terms && !is_wp_error($terms)) {
                $trail[] = array('name' => $terms[0]->name, 'url' => get_term_link($terms[0]));
            }

            $trail[] = array('name' => get_the_title(get_queried_object_id()), 'url' => get_permalink(get_queried_object_id()));

            return $trail;
        }

        if (is_tax('tool_category')) {
            $term = get_queried_object();

            return array($home, $tools, array('name' => $term->name, 'url' => get_term_link($term)));
        }

        if (is_post_type_archive('tool')) {
            return array($home, $tools);
        }

        // Blog: Home › Blog (› Post)
        $blog_page = (int) get_option('page_for_posts');
        $blog      = $blog_page ? array('name' => get_the_title($blog_page), 'url' => get_permalink($blog_page)) : null;

        if (is_home() && $blog) {
            return array($home, $blog);
        }

        if (is_singular('post')) {
            $trail   = $blog ? array($home, $blog) : array($home);
            $trail[] = array('name' => get_the_title(get_queried_object_id()), 'url' => get_permalink(get_queried_object_id()));

            return $trail;
        }

        if ((is_category() || is_tag()) && $blog) {
            $term = get_queried_object();

            return array($home, $blog, array('name' => $term->name, 'url' => get_term_link($term)));
        }

        if (is_page() && !is_front_page()) {
            return array($home, array('name' => get_the_title(get_queried_object_id()), 'url' => get_permalink(get_queried_object_id())));
        }

        return array();
    }


    /* --------------------------------------------------------------------
     * Structured data (JSON-LD)
     * ----------------------------------------------------------------- */

    private function get_schema()
    {

        $home_url     = home_url('/');
        $organization = array(
            '@type' => 'Organization',
            '@id'   => $home_url . '#organization',
            'name'  => get_bloginfo('name'),
            'url'   => $home_url,
        );

        $logo_id = get_theme_mod('custom_logo');

        if ($logo_id) {
            $organization['logo'] = wp_get_attachment_image_url($logo_id, 'full');
        }

        $graph = array($organization);

        if (is_front_page()) {
            $graph[] = array(
                '@type'       => 'WebSite',
                '@id'         => $home_url . '#website',
                'name'        => get_bloginfo('name'),
                'url'         => $home_url,
                'description' => $this->get_description(),
                'publisher'   => array('@id' => $home_url . '#organization'),
            );

            return $graph;
        }

        if (is_singular('tool')) {
            return array_merge($graph, $this->get_tool_schema(get_queried_object_id()));
        }

        if (is_post_type_archive('tool') || is_tax('tool_category')) {

            $term  = is_tax('tool_category') ? get_queried_object() : null;
            $items = array();

            foreach ($this->get_tools($term ? $term->term_id : 0) as $i => $tool) {
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $i + 1,
                    'url'      => get_permalink($tool),
                    'name'     => get_the_title($tool),
                );
            }

            $graph[] = array(
                '@type'       => 'CollectionPage',
                'name'        => $term ? $term->name : __('All Free Online Tools', 'freetoolshub-tools'),
                'url'         => $this->get_canonical(),
                'description' => $this->get_description(),
                'mainEntity'  => array(
                    '@type'           => 'ItemList',
                    'numberOfItems'   => count($items),
                    'itemListElement' => $items,
                ),
            );

            $graph[] = $this->get_breadcrumb_schema();

            return $graph;
        }

        return array();
    }

    private function get_breadcrumb_schema()
    {

        $items = array();

        foreach (self::get_breadcrumbs() as $i => $crumb) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => $crumb['name'],
                'item'     => $crumb['url'],
            );
        }

        return array('@type' => 'BreadcrumbList', 'itemListElement' => $items);
    }

    private function get_tool_schema($post_id)
    {

        $url     = get_permalink($post_id);
        $content = FTH_Tool_Content::get_for_post($post_id);

        $app = array(
            '@type'               => 'WebApplication',
            '@id'                 => $url . '#app',
            'name'                => get_the_title($post_id),
            'url'                 => $url,
            'description'         => $this->get_description(),
            'applicationCategory' => $content['app_category'],
            'operatingSystem'     => 'Any',
            'browserRequirements' => 'Requires JavaScript. Works in all modern browsers.',
            'isAccessibleForFree' => true,
            'offers'              => array(
                '@type'         => 'Offer',
                'price'         => '0',
                'priceCurrency' => 'USD',
            ),
            'publisher'           => array('@id' => home_url('/') . '#organization'),
        );

        if ($content['features']) {
            $app['featureList'] = array_values($content['features']);
        }

        if (has_post_thumbnail($post_id)) {
            $app['image'] = get_the_post_thumbnail_url($post_id, 'large');
        }

        $schema = array($app, $this->get_breadcrumb_schema());

        // Only describe FAQs that are visible on the page (the theme renders them).
        if ($content['faqs']) {
            $questions = array();

            foreach ($content['faqs'] as $faq) {
                $questions[] = array(
                    '@type'          => 'Question',
                    'name'           => $faq['question'],
                    'acceptedAnswer' => array('@type' => 'Answer', 'text' => $faq['answer']),
                );
            }

            $schema[] = array(
                '@type'      => 'FAQPage',
                '@id'        => $url . '#faq',
                'mainEntity' => $questions,
            );
        }

        return $schema;
    }


    /* --------------------------------------------------------------------
     * XML sitemap
     * ----------------------------------------------------------------- */

    /**
     * Remove the users sitemap — it only lists author archives and exposes usernames.
     */
    public function filter_sitemap_providers($provider, $name)
    {

        return $name === 'users' ? false : $provider;
    }

    /**
     * Add <lastmod> so search engines can see when a tool page changed.
     */
    public function filter_sitemap_entry($entry, $post)
    {

        $entry['lastmod'] = get_post_modified_time('c', true, $post);

        return $entry;
    }


    /* --------------------------------------------------------------------
     * Admin: SEO meta box on tools
     * ----------------------------------------------------------------- */

    public function add_meta_box()
    {

        add_meta_box(
            'fth_tool_seo',
            __('SEO', 'freetoolshub-tools'),
            array($this, 'render_meta_box'),
            'tool',
            'normal',
            'default'
        );
    }

    public function render_meta_box($post)
    {

        wp_nonce_field('fth_save_tool_seo', 'fth_tool_seo_nonce');

        $content     = FTH_Tool_Content::get_for_post($post->ID);
        $title       = get_post_meta($post->ID, '_fth_seo_title', true);
        $description = get_post_meta($post->ID, '_fth_seo_description', true);

        $default_title = $content['seo_title'] !== ''
            ? $this->with_site_name($content['seo_title'])
            /* translators: %s: tool name */
            : $this->with_site_name(sprintf(__('%s – Free Online Tool', 'freetoolshub-tools'), $post->post_title));

        $default_description = $content['seo_description'] !== '' ? $content['seo_description'] : $post->post_excerpt;
?>

        <p class="description">
            <?php esc_html_e('Leave a field empty to use the default shown in grey. Aim for up to 60 characters for the title and 150–160 for the description.', 'freetoolshub-tools'); ?>
        </p>

        <p>
            <label for="fth_seo_title"><strong><?php esc_html_e('SEO title', 'freetoolshub-tools'); ?></strong></label>
            <input
                type="text"
                id="fth_seo_title"
                name="fth_seo_title"
                class="widefat"
                value="<?php echo esc_attr($title); ?>"
                placeholder="<?php echo esc_attr($default_title); ?>"
                data-fth-counter="<?php echo esc_attr(self::TITLE_MAX); ?>">
        </p>

        <p>
            <label for="fth_seo_description"><strong><?php esc_html_e('Meta description', 'freetoolshub-tools'); ?></strong></label>
            <textarea
                id="fth_seo_description"
                name="fth_seo_description"
                class="widefat"
                rows="3"
                placeholder="<?php echo esc_attr($default_description); ?>"
                data-fth-counter="<?php echo esc_attr(self::DESCRIPTION_MAX); ?>"><?php echo esc_textarea($description); ?></textarea>
        </p>

        <script>
            document.querySelectorAll('#fth_tool_seo [data-fth-counter]').forEach(function (field) {
                var max = parseInt(field.getAttribute('data-fth-counter'), 10);
                var counter = document.createElement('span');
                counter.className = 'description';
                counter.style.display = 'block';
                field.parentNode.appendChild(counter);

                function update() {
                    var length = (field.value || field.placeholder).length;
                    counter.textContent = length + ' / ' + max + (field.value ? '' : ' (default)');
                    counter.style.color = length > max ? '#b32d2e' : '';
                }

                field.addEventListener('input', update);
                update();
            });
        </script>

<?php
    }

    public function save_meta_box($post_id)
    {

        if (
            !isset($_POST['fth_tool_seo_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['fth_tool_seo_nonce'])), 'fth_save_tool_seo')
        ) {
            return;
        }

        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            '_fth_seo_title'       => isset($_POST['fth_seo_title']) ? sanitize_text_field(wp_unslash($_POST['fth_seo_title'])) : '',
            '_fth_seo_description' => isset($_POST['fth_seo_description']) ? sanitize_textarea_field(wp_unslash($_POST['fth_seo_description'])) : '',
        );

        foreach ($fields as $key => $value) {
            if ($value !== '') {
                update_post_meta($post_id, $key, $value);
            } else {
                delete_post_meta($post_id, $key);
            }
        }
    }
}
