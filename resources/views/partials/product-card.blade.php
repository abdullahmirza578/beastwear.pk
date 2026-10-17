@php
    $cardImage = ($product->images->firstWhere('is_primary', true) ?? $product->images->first())?->url;
    $cardPrice = (float) ($product->sale_price ?? $product->price);
    $discount = $product->sale_price && $product->price > 0
        ? (int) round((($product->price - $product->sale_price) / $product->price) * 100)
        : 0;
    $cardStock = $product->variants->isNotEmpty()
        ? $product->variants->sum('stock_quantity')
        : $product->stock_quantity;
@endphp
<article class="card">
    <div class="card-media">
        @if ($cardStock < 1)
            <span class="badge out">Sold Out</span>
        @elseif ($discount > 0)
            <span class="badge sale">-{{ $discount }}%</span>
        @endif
        <a href="{{ route('products.show', $product->slug) }}" aria-label="{{ $product->name }}">
            @if ($cardImage)
                <img class="main" src="{{ $cardImage }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <span class="product-image-placeholder" aria-label="No product image available"></span>
            @endif
        </a>
        @auth
            <button class="wish-btn" data-wish="{{ $product->id }}" aria-label="Add {{ $product->name }} to wishlist">♡</button>
        @endauth
    </div>
    <div class="card-body">
        <span class="card-cat">{{ $product->category?->name }}</span>
        <h3 class="card-title"><a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="card-foot">
            <div class="price">
                <b>Rs {{ number_format($cardPrice, 0) }}</b>
                @if ($discount > 0)<s>Rs {{ number_format((float) $product->price, 0) }}</s>@endif
            </div>
            <a class="card-add" href="{{ route('products.show', $product->slug) }}" aria-label="Choose options for {{ $product->name }}">View</a>
        </div>
        <div class="card-sizes">{{ $product->variants->pluck('size')->filter()->unique()->implode(' · ') }}</div>
    </div>
</article>
