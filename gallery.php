<?php
$pageTitle = 'Gallery';
$pageStyles = ['css/pages/gallery.css'];
include 'includes/header.php';

// Map specific filenames to rich content definitions
$static_mapping = [
    'gallery1.jpg'  => ['title' => 'FIT Sixes Cricket', 'caption' => 'Students engaged in the annual FIT Sixes 2k25 cricket match.', 'category' => 'Sports'],
    'gallery2.jpg'  => ['title' => 'FIT Expo Drone Showcase', 'caption' => 'Engineering drone prototype displayed at FIT Expo.', 'category' => 'Science'],
    'gallery3.jpg'  => ['title' => 'Chemical Engineering Night', 'caption' => 'Illuminated archway for the Chemical and Process Engineering social night.', 'category' => 'Events'],
    'gallery4.jpg'  => ['title' => 'Sarasaviye Kokul Nade', 'caption' => 'Students participating in outdoor drama and cultural rehearsals.', 'category' => 'Events'],
    'gallery5.jpg'  => ['title' => 'Thala Wasanthaya', 'caption' => 'Traditional Sri Lankan art presented by the Mass Media Club.', 'category' => 'Events'],
    'gallery6.jpg'  => ['title' => 'Traditional Dance', 'caption' => 'Kandyan and traditional dancers performing at the Media Awards night.', 'category' => 'Events'],
    'gallery7.jpg'  => ['title' => 'Media Awards Ceremony', 'caption' => 'Formal audience gathered for the Mass Media Club\'s award ceremony.', 'category' => 'Academic'],
    'gallery8.jpg'  => ['title' => 'Outdoor Gathering', 'caption' => 'Students gathered at the outdoor amphitheater for Sarasaviye Kokul Nade.', 'category' => 'Events'],
    'gallery9.jpg'  => ['title' => 'Agna \'25 Event', 'caption' => 'Intricate theatrical backdrop created for the ENTC Agna \'25 event.', 'category' => 'Events'],
    'gallery10.jpg' => ['title' => 'Spandana Dance', 'caption' => 'Modern dance performance at the Spandana event by the Faculty of Medicine.', 'category' => 'Events'],
    'gallery11.jpg' => ['title' => 'Coffee with the Dean', 'caption' => 'Live acoustic band performance organized by FIT Tunes.', 'category' => 'Events'],
    'gallery12.jpg' => ['title' => 'Medical Faculty Dance', 'caption' => 'Choreographed outdoor performance at Spandana by the Art Circle.', 'category' => 'Events'],
    'gallery13.jpg' => ['title' => 'Live Concert Drummer', 'caption' => 'High-energy drumming performance proudly presented by Mora Lenz.', 'category' => 'Events'],
    'gallery14.jpg' => ['title' => 'Sunset Graduation', 'caption' => 'Silhouettes of proud graduates raising their caps against the twilight sky.', 'category' => 'Graduation']
];

$images = [];
// Use glob to pull images entirely from the images/gallery folder without hitting any DB
$gallery_files = glob("images/gallery/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}", GLOB_BRACE);

if ($gallery_files) {
    foreach ($gallery_files as $file) {
        $basename = basename($file);
        $file_lower = strtolower($basename);
        
        if (isset($static_mapping[$file_lower])) {
            $images[] = [
                'image_path' => $file,
                'title'      => $static_mapping[$file_lower]['title'],
                'caption'    => $static_mapping[$file_lower]['caption'],
                'category'   => $static_mapping[$file_lower]['category'],
            ];
        } else {
            // Fallback for any extra images found in the directory
            $images[] = [
                'image_path' => $file,
                'title'      => 'Campus Life',
                'caption'    => 'Experiences and memories from university life',
                'category'   => 'Events',
            ];
        }
    }
}
?>

<section class="page-hero reveal-on-scroll" aria-label="Gallery hero" style="background-color: var(--dark-950) !important;">
  <div class="container">
    <p class="eyebrow">Gallery</p>
    <h1>Moments from Campus Life</h1>
    <p class="page-hero-meta">Explore academic highlights, cultural celebrations, sports events, and graduation memories from universities across Sri Lanka.</p>
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <span>/</span>
      <span>Gallery</span>
    </div>
  </div>
</section>

<section class="gallery-section">
  <div class="gallery-wrap">

    <?php if (empty($images)): ?>
      <div class="gallery-empty">
        <div class="gallery-empty__icon">🖼️</div>
        <h3>Gallery Coming Soon</h3>
        <p>Inspiring moments are being added shortly.</p>
      </div>
    <?php else: ?>

      <!-- Filter bar -->
      <div class="gallery-filters">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="Academic">Academic</button>
        <button class="filter-btn" data-filter="Sports">Sports</button>
        <button class="filter-btn" data-filter="Events">Events</button>
        <button class="filter-btn" data-filter="Science">Science</button>
        <button class="filter-btn" data-filter="Graduation">Graduation</button>
      </div>

      <!-- Grid -->
      <div class="gallery-grid" id="galleryGrid">
        <?php foreach ($images as $image): ?>
        <div class="gallery-card"
             data-category="<?= htmlspecialchars($image['category'] ?? '') ?>">

          <!-- Image + effects wrapper -->
          <div class="gallery-card__media">
            <img
              src="<?= htmlspecialchars($image['image_path'] ?? $image['image_url'] ?? '') ?>"
              alt="<?= htmlspecialchars($image['title'] ?? '') ?>"
              loading="lazy"
              onerror="this.closest('.gallery-card').style.display='none'"
            >

            <!-- Reflection element — clones the image via CSS -->
            <div class="gallery-card__reflection"></div>
          </div>

          <!-- Caption bottom-left -->
          <div class="gallery-card__caption">
            <?= htmlspecialchars($image['caption'] ?? $image['title'] ?? '') ?>
          </div>

        </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Gallery category filter
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryCards = document.querySelectorAll('.gallery-card');

    if (filterBtns.length > 0) {
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {

                // Update active button
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;

                galleryCards.forEach(card => {
                    if (filter === 'all' || 
                        card.dataset.category === filter) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
