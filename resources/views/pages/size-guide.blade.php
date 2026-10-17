@extends('layouts.layout')

@section('title', 'Size Guide — BeastWear.pk')

@section('meta')
    <meta name="description" content="BeastWear.pk size charts for t-shirts, oversized tees, hoodies, sweatshirts, trousers and kids clothing — all measurements in inches.">
@endsection

@section('content')

<section class="page-hero">
    <div class="wrap">
      <div class="crumbs"><a href="index.html">Home</a><i>/</i><span>Size Guide</span></div>
      <h1>Size Guide</h1>
      <p>All measurements are in <strong>inches</strong>, taken flat. Allow ±0.5" variance due to cutting and fabric.</p>
    </div>
  </section>

  <section class="section-sm">
    <div class="wrap wrap-sm">
      <!-- How to measure -->
      <div class="panel">
        <h3>How to measure yourself</h3>
        <div class="form-grid">
          <div class="icon-line"><span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 8h16M4 8v8M20 8v8"/></svg></span>
            <div><h4>Chest</h4><p>Measure around the fullest part of your chest, keeping the tape level.</p></div></div>
          <div class="icon-line"><span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v18"/></svg></span>
            <div><h4>Length</h4><p>Measure from the highest shoulder point straight down to the hem.</p></div></div>
          <div class="icon-line"><span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h16M4 18h16"/></svg></span>
            <div><h4>Shoulder</h4><p>Measure from one shoulder seam to the other across the back.</p></div></div>
          <div class="icon-line"><span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 4v16M18 4v16"/></svg></span>
            <div><h4>Waist (trousers)</h4><p>Measure around your natural waistline, relaxed.</p></div></div>
        </div>
      </div>

      <!-- T-Shirts -->
      <div class="mt-l"><span class="eyebrow">Regular fit</span><h2>T-Shirts</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Chest</th><th>Length</th><th>Shoulder</th><th>Sleeve</th><th>Fits (approx.)</th></tr></thead>
          <tbody>
            <tr><td class="bold">S</td><td>38</td><td>27</td><td>17</td><td>8</td><td>5'4"–5'6"</td></tr>
            <tr><td class="bold">M</td><td>40</td><td>28</td><td>18</td><td>8.5</td><td>5'6"–5'8"</td></tr>
            <tr><td class="bold">L</td><td>42</td><td>29</td><td>19</td><td>9</td><td>5'8"–5'10"</td></tr>
            <tr><td class="bold">XL</td><td>44</td><td>30</td><td>20</td><td>9.5</td><td>5'10"–6'0"</td></tr>
            <tr><td class="bold">XXL</td><td>46</td><td>31</td><td>21</td><td>10</td><td>6'0" and above</td></tr>
          </tbody>
        </table></div>
      </div>

      <!-- Oversized -->
      <div class="mt-l"><span class="eyebrow">Boxy / drop shoulder</span><h2>Oversized T-Shirts</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Chest</th><th>Length</th><th>Shoulder</th><th>Sleeve</th><th>Fit note</th></tr></thead>
          <tbody>
            <tr><td class="bold">M</td><td>44</td><td>28</td><td>21</td><td>9</td><td>Relaxed oversized</td></tr>
            <tr><td class="bold">L</td><td>46</td><td>29</td><td>22</td><td>9.5</td><td>True oversized</td></tr>
            <tr><td class="bold">XL</td><td>48</td><td>30</td><td>23</td><td>10</td><td>Extra roomy</td></tr>
            <tr><td class="bold">XXL</td><td>50</td><td>31</td><td>24</td><td>10.5</td><td>Extra roomy</td></tr>
          </tbody>
        </table></div>
        <p class="hint mt-s">Our oversized fits are intentionally boxy. If you prefer a subtle oversized look, take your regular size; for a full drop-shoulder look, size up once.</p>
      </div>

      <!-- Hoodies -->
      <div class="mt-l"><span class="eyebrow">Heavyweight fleece</span><h2>Hoodies</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Chest</th><th>Length</th><th>Shoulder</th><th>Sleeve</th></tr></thead>
          <tbody>
            <tr><td class="bold">M</td><td>44</td><td>27</td><td>20</td><td>24</td></tr>
            <tr><td class="bold">L</td><td>46</td><td>28</td><td>21</td><td>25</td></tr>
            <tr><td class="bold">XL</td><td>48</td><td>29</td><td>22</td><td>26</td></tr>
            <tr><td class="bold">XXL</td><td>50</td><td>30</td><td>23</td><td>27</td></tr>
          </tbody>
        </table></div>
      </div>

      <!-- Sweatshirts -->
      <div class="mt-l"><span class="eyebrow">Crewneck</span><h2>Sweatshirts</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Chest</th><th>Length</th><th>Shoulder</th><th>Sleeve</th></tr></thead>
          <tbody>
            <tr><td class="bold">S</td><td>40</td><td>26</td><td>18</td><td>23</td></tr>
            <tr><td class="bold">M</td><td>42</td><td>27</td><td>19</td><td>24</td></tr>
            <tr><td class="bold">L</td><td>44</td><td>28</td><td>20</td><td>25</td></tr>
            <tr><td class="bold">XL</td><td>46</td><td>29</td><td>21</td><td>26</td></tr>
            <tr><td class="bold">XXL</td><td>48</td><td>30</td><td>22</td><td>27</td></tr>
          </tbody>
        </table></div>
      </div>

      <!-- Trousers -->
      <div class="mt-l"><span class="eyebrow">Baggy / cargo / chino</span><h2>Trousers</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Waist</th><th>Hip</th><th>Length</th><th>Thigh</th><th>Fits waist</th></tr></thead>
          <tbody>
            <tr><td class="bold">28</td><td>28</td><td>40</td><td>40</td><td>24</td><td>27"–28"</td></tr>
            <tr><td class="bold">30</td><td>30</td><td>42</td><td>40.5</td><td>25</td><td>29"–30"</td></tr>
            <tr><td class="bold">32</td><td>32</td><td>44</td><td>41</td><td>26</td><td>31"–32"</td></tr>
            <tr><td class="bold">34</td><td>34</td><td>46</td><td>41.5</td><td>27</td><td>33"–34"</td></tr>
            <tr><td class="bold">36</td><td>36</td><td>48</td><td>42</td><td>28</td><td>35"–36"</td></tr>
          </tbody>
        </table></div>
      </div>

      <!-- Kids -->
      <div class="mt-l"><span class="eyebrow">Boys &amp; girls</span><h2>Kids Collection</h2>
        <div class="table-wrap"><table class="bw">
          <thead><tr><th>Size</th><th>Age</th><th>Chest</th><th>Length</th><th>Shoulder</th></tr></thead>
          <tbody>
            <tr><td class="bold">2-3Y</td><td>2–3 years</td><td>24</td><td>16</td><td>10</td></tr>
            <tr><td class="bold">4-5Y</td><td>4–5 years</td><td>26</td><td>18</td><td>11</td></tr>
            <tr><td class="bold">6-7Y</td><td>6–7 years</td><td>28</td><td>20</td><td>12</td></tr>
            <tr><td class="bold">8-9Y</td><td>8–9 years</td><td>30</td><td>22</td><td>13</td></tr>
            <tr><td class="bold">10-11Y</td><td>10–11 years</td><td>32</td><td>24</td><td>14</td></tr>
          </tbody>
        </table></div>
      </div>

      <div class="info-box mt-l">
        <h4>Still unsure?</h4>
        <p style="margin:0">Send us your height, weight and chest measurement on WhatsApp and we will recommend the right
          size. Remember: the first size exchange is always free.</p>
        <div class="row mt-s"><a class="btn btn-dark btn-sm" href="shop.html">Shop Collection</a>
          <a class="btn btn-outline btn-sm" href="returns.html">Exchange Policy</a></div>
      </div>
    </div>
  </section>

@endsection

