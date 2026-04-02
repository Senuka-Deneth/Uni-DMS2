<?php
$pageTitle = 'Gallery';
$pageStyles = ['css/pages/gallery.css'];
$galleryPhotos = [
    ['src' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=900&q=80', 'caption' => 'Sunrise over the central quadrangle'],
    ['src' => 'https://images.unsplash.com/photo-1472457897821-70d3819a6f17?auto=format&fit=crop&w=900&q=80', 'caption' => 'Students collaborating in the studio'],
    ['src' => 'https://images.unsplash.com/photo-1523050854059-8f0d1f6e2bca?auto=format&fit=crop&w=900&q=80', 'caption' => 'Campus library filled with late-night readers'],
    ['src' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80', 'caption' => 'Laboratory minds exploring future tech'],
    ['src' => 'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=900&q=80', 'caption' => 'Ceremonial march and proud tradition'],
    ['src' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80', 'caption' => 'Creative minds sketching new worlds']
];
include 'includes/header.php';
?>
<section class="page-hero reveal-on-scroll" aria-label="Gallery hero">
    <div class="container hero-content">
        <p class="eyebrow">Gallery</p>
        <h1>Campus stories that bring heritage to life.</h1>
        <p class="page-hero-meta">Explore rituals, lab breakthroughs, and the calm confidence of Sri Lankan university life.</p>
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span>Gallery</span>
        </div>
    </div>
</section>
<section class="section-shell">
    <div class="container gallery-wrapper">
        <div class="gallery-grid">
            <?php foreach ($galleryPhotos as $photo): ?>
                <figure class="gallery-card reveal-on-scroll" data-src="<?php echo $photo['src']; ?>" data-caption="<?php echo htmlspecialchars($photo['caption']); ?>">
                    <img src="<?php echo $photo['src']; ?>" alt="<?php echo htmlspecialchars($photo['caption']); ?>" loading="lazy">
                    <figcaption class="gallery-caption"><?php echo htmlspecialchars($photo['caption']); ?></figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<div class="lightbox-overlay" id="galleryLightbox" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="lightbox-inner">
        <button class="lightbox-close" type="button" aria-label="Close gallery viewer">&times;</button>
        <img src="" alt="" loading="lazy">
        <p class="lightbox-caption"></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
