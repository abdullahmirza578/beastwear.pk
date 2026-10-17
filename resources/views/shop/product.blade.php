@extends('layouts.layout')

@section('title', $product->name . ' — BeastWear.pk')

@section('meta')
    <meta name="description" content="{{ $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 155) }}">
@endsection

@section('main-class', 'section-sm')

@section('content')

@php
    $primaryProductImage = $product->images->firstWhere('is_primary', true)
        ?? $product->images->first();
    $primaryProductImageUrl = $primaryProductImage?->url;
@endphp

<div class="wrap">
    <div class="crumbs"><a href="{{ route('home') }}">Home</a><i>/</i><a href="{{ route('shop') }}">Shop</a><i>/</i><span id="crumb-product">{{ $product->name }}</span></div>

    <div class="pdp">
      <!-- ============ LEFT: GALLERY ============ -->
      <div class="pdp-gallery">
        <div class="pdp-main" id="pdp-main">
          @if ($primaryProductImageUrl)
            <img id="pdp-main-img" src="{{ $primaryProductImageUrl }}" alt="{{ $product->name }}">
          @else
            <div class="product-image-placeholder" role="img" aria-label="No product image available"></div>
          @endif
          <span class="zoom-hint">Click to zoom</span>
        </div>
        <div class="thumbs" id="pdp-thumbs">
          @foreach ($product->images as $image)
            <button type="button" class="{{ $loop->first ? 'active' : '' }}" data-thumb="{{ $loop->index }}"><img src="{{ $image->url }}" alt="View image {{ $loop->iteration }}"></button>
          @endforeach
        </div>
      </div>

      <!-- ============ RIGHT: INFO ============ -->
      <div class="pdp-info">
        <span class="card-cat" id="pdp-cat">{{ $product->category?->name ?? 'Uncategorised' }}</span>
        <h1 id="pdp-name">{{ $product->name }}</h1>
        <div class="row-center" style="gap:9px">
          <span id="pdp-stars"></span>
          <span class="hint" id="pdp-rating-num"></span>
        </div>

        <div class="pdp-price" id="pdp-price">
          <b>Rs {{ number_format((float) ($product->sale_price ?? $product->price), 0) }}</b>
          @if ($product->sale_price !== null && $product->sale_price < $product->price)
            <s>Rs {{ number_format((float) $product->price, 0) }}</s>
            <span class="off-tag">-{{ (int) round((($product->price - $product->sale_price) / $product->price) * 100) }}% OFF</span>
          @endif
        </div>
        <p class="hint">Inclusive of all taxes · Cash on Delivery available</p>

        @php($availableStock = ($product->size_selection_enabled || $product->color_selection_enabled) && $product->variants->isNotEmpty() ? $product->variants->sum('stock_quantity') : $product->stock_quantity)
        <div class="stock-line" id="pdp-stock">{{ $availableStock > 0 ? $availableStock . ' pieces available' : 'Out of stock' }}</div>

        <p style="margin-top:18px" id="pdp-short">{{ $product->short_description }}</p>

        @if ($product->size_selection_enabled)
          <div class="opt-label">Select Size <span>Select one</span></div>
          <div class="size-list" id="pdp-sizes"></div>
        @endif

        @if ($product->color_selection_enabled)
          <div class="opt-label">Select Color <span id="color-out"></span></div>
          <div class="chips" id="pdp-colors"></div>
        @endif

        @if ($product->design_selection_enabled)
          <div class="opt-label">Select Design <span>Select one</span></div>
          <div class="chips" id="pdp-designs" role="group" aria-label="Select a product design"></div>
        @endif

        <div class="opt-label">Quantity</div>
        <div class="qty-box">
          <button type="button" id="qty-minus" aria-label="Decrease quantity">−</button>
          <input type="text" id="pdp-qty" value="1" readonly aria-label="Quantity">
          <button type="button" id="qty-plus" aria-label="Increase quantity">+</button>
        </div>

        <div class="pdp-actions">
          <button class="btn btn-dark" id="pdp-add">Add to Cart</button>
          <button class="btn btn-outline" id="pdp-buy">Buy Now</button>
        </div>
        <div class="pdp-actions" style="margin-top:12px">
          <button class="btn btn-wa" id="pdp-wa">Confirm via WhatsApp</button>
          <button class="btn btn-outline" id="pdp-wish">Add to Wishlist</button>
        </div>

        <div class="trust">
          <div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z"/><circle cx="6" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/></svg> Delivery in 2–4 days</div>
          <div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.6"/></svg> Cash on Delivery</div>
          <div><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 12a8 8 0 1 1-2.6-5.9"/><path d="M20 4v5h-5"/></svg> 7-day exchange</div>
        </div>
      </div>
    </div>

    <!-- ============ TABS ============ -->
    <div class="mt-l">
      <div class="tabs" id="pdp-tabs">
        <button type="button" class="active" data-tab="desc">Description</button>
        <button type="button" data-tab="fabric">Fabric &amp; Care</button>
        <button type="button" data-tab="size">Size Information</button>
        <button type="button" data-tab="ship">Shipping</button>
        <button type="button" data-tab="exch">Exchange</button>
        <button type="button" data-tab="rev">Reviews</button>
      </div>

      <div class="tab-panel active" id="tab-panel-desc">
        <h3>Product Description</h3>
        <p id="tab-desc-content">{{ $product->description }}</p>
        <ul class="spec-list">
          <li><b>Category</b><span id="tab-cat"></span></li>
          <li><b>Fit</b><span id="tab-fit"></span></li>
          <li><b>Availability</b><span id="tab-stock"></span></li>
          <li><b>Origin</b><span>Designed in Lahore · Made in Pakistan</span></li>
        </ul>
      </div>

      <div class="tab-panel" id="tab-panel-fabric">
        <h3>Fabric &amp; Care Instructions</h3>
        <p id="tab-fabric-content"></p>
        <ul style="list-style:disc;padding-left:20px;color:var(--gray-dark)">
          <li>Machine wash cold (30°C) with similar colours</li>
          <li>Do not bleach · Do not tumble dry</li>
          <li>Iron inside out on low heat</li>
          <li>Wash inside out to protect prints and embroidery</li>
        </ul>
      </div>

      <div class="tab-panel" id="tab-panel-size">
        <h3>Size Information</h3>
        <p>All measurements are in inches and may vary by ±0.5" due to cutting.</p>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Chest</th><th>Length</th><th>Shoulder</th><th>Sleeve</th></tr></thead>
          <tbody>
            @foreach ($product->variants->pluck('size')->filter()->unique() as $size)
              <tr><td class="bold">{{ $size }}</td><td colspan="4">Available in this product</td></tr>
            @endforeach
          </tbody>
        </table></div>
        <a class="btn btn-outline btn-sm mt-m" href="{{ route('size-guide') }}">Full Size Guide</a>
      </div>

      <div class="tab-panel" id="tab-panel-ship">
        <h3>Shipping Information</h3>
        <p>Orders are processed within 24 hours. Delivery across Pakistan takes 2–4 working days (3–5 for remote areas).</p>
        <ul style="list-style:disc;padding-left:20px;color:var(--gray-dark)">
          <li>Delivery charges: Rs 199 — free on orders over Rs 3,000</li>
          <li>Cash on Delivery available nationwide</li>
          <li>You receive a WhatsApp confirmation with tracking details</li>
        </ul>
        <a class="btn btn-outline btn-sm mt-m" href="{{ route('shipping') }}">Shipping Policy</a>
      </div>

      <div class="tab-panel" id="tab-panel-exch">
        <h3>Exchange Information</h3>
        <p>Wrong size or a fault? Exchange within 7 days of delivery — the product must be unworn with tags attached.</p>
        <ul style="list-style:disc;padding-left:20px;color:var(--gray-dark)">
          <li>Size exchanges are free the first time</li>
          <li>Damaged or wrong items are replaced at our cost</li>
          <li>Message us on WhatsApp to start an exchange</li>
        </ul>
        <a class="btn btn-outline btn-sm mt-m" href="{{ route('returns') }}">Exchange Policy</a>
      </div>

      <div class="tab-panel" id="tab-panel-rev">
        <div class="two-col" style="align-items:start;gap:30px">
          <div>
            <div class="info-box text-center" id="pdp-avg"></div>
            @auth
              @if ($reviewOrders->isNotEmpty())
                <div class="info-box review-form-card" style="margin-top:16px">
                  <h3>Write a Review</h3>
                  <p class="hint">Only customers with a delivered order can submit a review. Your review will appear after approval.</p>
                  @if (session('status'))
                    <p class="hint" role="status">{{ session('status') }}</p>
                  @endif
                  @if ($errors->any())
                    <div class="hint" role="alert">{{ $errors->first() }}</div>
                  @endif
                  @php($reviewableOrders = $reviewOrders->reject(fn ($order) => in_array($order->id, $reviewedOrderIds, true)))
                  @if ($reviewableOrders->isNotEmpty())
                    <form class="review-form" method="POST" action="{{ route('reviews.store', $product) }}" style="margin-top:14px">
                      @csrf
                      <div class="form-group">
                        <label class="form-label" for="review-order">Delivered order</label>
                        <select class="form-control" id="review-order" name="order_id" required>
                          @foreach ($reviewableOrders as $order)
                            <option value="{{ $order->id }}" @selected(old('order_id') == $order->id)>
                              {{ $order->order_number }} · Delivered
                            </option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group">
                        <label class="form-label" for="review-rating">Rating</label>
                        <select class="form-control" id="review-rating" name="rating" required>
                          <option value="">Select a rating</option>
                          @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} out of 5 stars</option>
                          @endfor
                        </select>
                      </div>
                      <div class="form-group">
                        <label class="form-label" for="review-comment">Your review</label>
                        <textarea class="form-control" id="review-comment" name="comment" rows="4" maxlength="3000" placeholder="Share your experience with this product">{{ old('comment') }}</textarea>
                      </div>
                      <button class="btn btn-dark" type="submit">Submit Review</button>
                    </form>
                  @else
                    <p class="hint" style="margin-top:12px">You have already reviewed this product for every delivered order.</p>
                  @endif
                </div>
              @else
                <div class="info-box review-form-card" style="margin-top:16px">
                  <h3>Write a Review</h3>
                  <p class="hint">You can review this product after one of your orders has been delivered.</p>
                </div>
              @endif
            @else
              <div class="info-box review-form-card" style="margin-top:16px">
                <h3>Write a Review</h3>
                <p class="hint">Sign in after your order is delivered to write a review.</p>
                <a class="btn btn-outline btn-sm" href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}">Sign in</a>
              </div>
            @endauth
          </div>
          <div id="pdp-reviews">
            @forelse ($product->reviews as $review)
              <article class="review">
                <div class="review-top">
                  <b>{{ $review->user->name }}</b>
                  <span>Verified customer · {{ $review->created_at->format('d M Y') }}</span>
                </div>
                <div aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                @if ($review->comment)
                  <p>{{ $review->comment }}</p>
                @endif
              </article>
            @empty
              <p class="hint">No approved reviews yet. Be the first to share your experience.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <!-- ============ RELATED ============ -->
    <div class="mt-l">
      <div class="sec-head"><div><span class="eyebrow">You may also like</span><h2>Related Products</h2></div>
        <a class="link-arrow" href="{{ route('shop') }}">View All</a></div>
      <div class="product-grid" id="related-grid"></div>
    </div>
  </div>

@endsection

@section('scripts')
<script src="{{ asset('js/product.js') }}"></script>
<script>
/* Product page only: star icons + tab switching */
document.addEventListener("DOMContentLoaded", function () {
  var p = BW_PRODUCTS.find(function (product) { return product.slug === window.BW_PRODUCT_SLUG; });
  if (p) {
    document.getElementById("pdp-stars").outerHTML = bwStars(p.rating);
    document.getElementById("pdp-short").textContent = p.desc;
    var co = document.getElementById("color-out"); if (co) co.textContent = p.colors[0];
  }

  var tabs = document.getElementById("pdp-tabs");
  if (tabs) tabs.addEventListener("click", function (e) {
    var b = e.target.closest("[data-tab]");
    if (!b) return;
    tabs.querySelectorAll("button").forEach(function (x) { x.classList.remove("active"); });
    b.classList.add("active");
    document.querySelectorAll(".tab-panel").forEach(function (pn) { pn.classList.remove("active"); });
    document.getElementById("tab-panel-" + b.getAttribute("data-tab")).classList.add("active");
  });
});
</script>
@endsection
