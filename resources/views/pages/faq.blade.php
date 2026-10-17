@extends('layouts.layout')

@section('title', 'FAQs — BeastWear.pk')

@section('meta')
    <meta name="description" content="Answers about ordering, Cash on Delivery, delivery times, sizing and exchanges at BeastWear.pk.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>FAQs</span></div>
      <h1>Frequently Asked Questions</h1>
      <p>Everything you need to know about ordering, delivery, sizing and exchanges. Still stuck? Message us on WhatsApp.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap wrap-sm">
      <!-- Accordion group: only one item open at a time (data-single) -->
      <div data-single>

        <div class="acc">
          <button class="acc-head">How can I order?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Three easy ways:</p>
            <ul>
              <li>Add products to your cart and check out on the website (Cash on Delivery).</li>
              <li>Tap <strong>Confirm Order via WhatsApp</strong> at checkout and send us the auto-generated order message.</li>
              <li>Message us directly at +92 300 1234567 with the product name, size and your address.</li>
            </ul>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">Do you offer Cash on Delivery?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Yes. Cash on Delivery is available across Pakistan — you pay the courier when your parcel arrives.
              Please keep the exact amount ready. Bank transfer, JazzCash and Easypaisa are being added soon.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">Do you deliver all over Pakistan?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>We deliver to every city and town in Pakistan, including Punjab, Sindh, Khyber Pakhtunkhwa,
              Balochistan, Islamabad, Azad Kashmir and Gilgit-Baltistan. Remote areas may take an extra day or two.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">How long does delivery take?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <ul>
              <li>Lahore, Karachi, Islamabad, Rawalpindi: 2–3 working days</li>
              <li>Other major cities: 3–4 working days</li>
              <li>Remote / northern areas: 4–6 working days</li>
            </ul>
            <p>Orders placed before 4 PM are dispatched the same day.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">How can I choose my size?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Every product page has a size chart in inches (chest, length, shoulder, sleeve). Measure a t-shirt you
              already own and compare. If you are between sizes, go one size up for a relaxed fit or one down for a
              regular fit. Full charts are on the <a href="size-guide.html" style="text-decoration:underline">Size Guide</a> page.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">What is the exchange policy?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>You can exchange any unworn item with tags attached within 7 days of delivery. The first size exchange is
              free; after that a Rs 199 courier charge applies. Damaged or wrong items are always replaced at our cost.
              See the full <a href="returns.html" style="text-decoration:underline">Returns &amp; Exchange policy</a>.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">How can I track my order?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Use the <a href="track-order.html" style="text-decoration:underline">Track Order</a> page with your order
              number (BW-XXXXXX), or open <a href="orders.html" style="text-decoration:underline">My Orders</a> to see live
              status updates. We also send WhatsApp updates at every stage.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">Is there a delivery charge?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Delivery is Rs 199 flat, and <strong>free</strong> on every order above Rs 3,000. Sale items count towards the total.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">Can I change or cancel my order?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Yes — as long as the order has not been shipped. Message us on WhatsApp with your order number and we will
              update or cancel it. Once it is handed to the courier, you can refuse the parcel on delivery instead.</p>
          </div></div>
        </div>

        <div class="acc">
          <button class="acc-head">How do I wash BeastWear products?<i>+</i></button>
          <div class="acc-body"><div class="acc-body-in">
            <p>Machine wash cold (30°C) inside out with similar colours. Do not bleach, do not tumble dry, and iron on low
              heat. Our fabrics are bio-washed and pre-shrunk, so they hold their shape and colour.</p>
          </div></div>
        </div>

      </div>

      <div class="cta-strip mt-l" style="border-radius:var(--radius-l)">
        <div class="wrap text-center">
          <h2>Still Have a Question?</h2>
          <p>Our support team replies within minutes, 7 days a week.</p>
          <a class="btn btn-light" href="contact.html">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

@endsection

@section('scripts')
<script>
/* FAQ page only: open the first question by default */
document.addEventListener("DOMContentLoaded", function () {
  var first = document.querySelector(".acc");
  if (first) {
    first.classList.add("open");
    var b = first.querySelector(".acc-body");
    b.style.maxHeight = b.scrollHeight + "px";
  }
});
</script>
@endsection
