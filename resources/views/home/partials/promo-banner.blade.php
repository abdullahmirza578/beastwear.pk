<section class="hero" style="min-height:45vh">
  <div class="hero-media">
    <img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" loading="lazy">
  </div>
  <div class="wrap">
    <div class="hero-inner" style="padding:64px 0">
      <h2 style="font-size:clamp(36px,6vw,72px)">{{ $promo->title }}</h2>
      @if ($promo->subtitle)
        <p>{{ $promo->subtitle }}</p>
      @endif
      @if ($promo->button_url)
        <div class="hero-btns">
          <a class="btn btn-light" href="{{ $promo->button_url }}">{{ $promo->button_text ?: 'Explore Now' }}</a>
        </div>
      @endif
    </div>
  </div>
</section>
