<?php
require '_base.php';
include '_head.php';
$_title = 'About Me | Boost.do';
?>
<link rel="stylesheet" href="css/about_me.css">
<script type="module" src="js/map.js"></script>

<body>

    <div class="about-container">
        <section class="about-section">
            <h2>About Us</h2>
            <p>At Boots.Do, we specialize in top-quality badminton sports items. From rackets to shuttlecocks, apparel, and accessories, we are dedicated to providing players of all levels with the best gear to enhance their performance. Established in 2022, our mission is to support the global badminton community with products that combine durability, style, and functionality.</p>
        </section>

        <section class="about-section">
            <h2>Our Story</h2>
            <p>Boots.Do was founded by a team of passionate badminton enthusiasts who understand the needs of players. Our journey started with a vision to make premium badminton gear accessible to everyone. Over the years, we have built a reputation for excellence, serving customers worldwide with reliable products and exceptional service.</p>
        </section>

        <section class="about-section">
            <h2>Visit Our Store</h2>
            <p>Located at the heart of the city, our store is the perfect destination to explore our extensive collection in person. Come and experience the Boots.Do difference today!</p>
        </section>

        <div class="map-container">
            <h2>Find Us Here</h2>
            <div id="map"></div>

            <script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyBn9Vqr_chzYC4FQ4_iM-i6ygF6QCEtzhs",
    v: "weekly",
    // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
  });
</script>
        </div>
    </div>
</body>
<?php
include '_foot.php';
?>