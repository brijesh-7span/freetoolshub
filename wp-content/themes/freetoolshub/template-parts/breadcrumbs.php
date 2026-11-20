<?php

/**
 * Breadcrumb trail (Home › Tools › Category › Tool).
 * Uses the same trail as the BreadcrumbList structured data from the FreeToolsHub Tools plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

$fth_crumbs = class_exists('FTH_SEO') ? FTH_SEO::get_breadcrumbs() : array();

if (count($fth_crumbs) < 2) {
    return;
}

$fth_last = count($fth_crumbs) - 1;
?>

<nav class="breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumb', 'freetoolshub'); ?>">
    <ol>
        <?php foreach ($fth_crumbs as $fth_i => $fth_crumb) : ?>
            <li>
                <?php if ($fth_i === $fth_last) : ?>
                    <span aria-current="page"><?php echo esc_html($fth_crumb['name']); ?></span>
                <?php else : ?>
                    <a href="<?php echo esc_url($fth_crumb['url']); ?>"><?php echo esc_html($fth_crumb['name']); ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
