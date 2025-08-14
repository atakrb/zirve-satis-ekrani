<?php

/******************** Yardımcı Fonksiyonlar ********************/
function sıkıştır_tamsayı($değer, $min, $max): int
{
  $temiz = filter_var($değer, FILTER_VALIDATE_INT);
  if ($temiz === false) $temiz = $min;
  return max($min, min($max, $temiz));
}
function birim_fiyat_kullanici(int $adet): float
{
  if ($adet >= 101) return 24.90;
  if ($adet >= 51)  return 29.90;
  if ($adet >= 21)  return 34.90;
  if ($adet >= 11)  return 39.90;
  return 49.90;
}
function birim_fiyat_dosya(int $adet): float
{
  if ($adet >= 5001) return 0.04;
  if ($adet >= 2001) return 0.06;
  if ($adet >= 501)  return 0.08;
  return 0.10;
}
function tl(float $n): string
{
  return number_format($n, 2, ',', '.');
}

/******************** Varsayılanlar + POST ********************/
$kullanici_sayisi = 0;
$dosya_sayisi     = 0;
$siparis_notu     = '';
$siparis_alindi   = false;
$siparis_no       = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kullanici_sayisi = sıkıştır_tamsayı($_POST['kullanici_sayisi'] ?? 1, 1, 5000);
  $dosya_sayisi     = sıkıştır_tamsayı($_POST['dosya_sayisi'] ?? 1, 1, 5000);
  $siparis_notu     = trim((string)($_POST['siparis_notu'] ?? ''));
  $siparis_alindi   = true;
  $siparis_no       = 'SP-' . strtoupper(bin2hex(random_bytes(3)));
}

/******************** Fiyatlandırma (ilk yükleme) ********************/
$kullanici_birim  = birim_fiyat_kullanici($kullanici_sayisi);
$dosya_birim      = birim_fiyat_dosya($dosya_sayisi);

$tutar_kullanicilar = $kullanici_sayisi * $kullanici_birim;
$tutar_dosyalar     = $dosya_sayisi     * $dosya_birim;

$ara_toplam   = $tutar_kullanicilar + $tutar_dosyalar;
$kdv_orani    = 0.20;
$kdv_tutar    = $ara_toplam * $kdv_orani;
$genel_toplam = $ara_toplam + $kdv_tutar;

/******************** Görsel İçerik Dizileri (foreach için) ********************/
$kullanici_kademeleri = [
  '1–10'   => '49,90 ₺',
  '11–20'  => '39,90 ₺',
  '21–50'  => '34,90 ₺',
  '51–100' => '29,90 ₺',
  '100+'   => '24,90 ₺',
];
$dosya_kademeleri = [
  '0–500'     => '0,10 ₺',
  '501–2000'  => '0,08 ₺',
  '2001–5000' => '0,06 ₺',
  '5000+'     => '0,04 ₺',
];
$sepet_kalemleri = [
  'Kullanıcı birim fiyat' => tl($kullanici_birim) . ' ₺',
  'Dosya başı fiyat'      => tl($dosya_birim)     . ' ₺',
  'Kullanıcılar'          => "{$kullanici_sayisi} × " . tl($kullanici_birim) . " = " . tl($tutar_kullanicilar) . " ₺",
  'Dosyalar'              => "{$dosya_sayisi} × " . tl($dosya_birim)     . " = " . tl($tutar_dosyalar)     . " ₺",
  'Ara toplam'            => tl($ara_toplam)      . ' ₺',
  'KDV (%20)'             => tl($kdv_tutar)       . ' ₺',
  'Toplam'                => tl($genel_toplam)    . ' ₺',
];

/******************** Üst bar (logo/menü) verileri ********************/
$sayfa_baslik = 'İcraProWeb (yeni)';
$breadcrumb   = [
  'Zirve Bilgisayar' => '/',
  'icraProWeb (Yeni)' => null,
];
$sepet_adet   = 0;
$oturum_acik  = false;
?>
<!doctype html>
<html lang="tr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Satış & Fiyatlandırma</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(180deg, #fff 0, #f7f9fc 100%);
    }

    .kart {
      background: #fff;
      border: 1px solid #e8ecf3;
      box-shadow: 0 10px 20px rgba(16, 24, 40, .06);
      border-radius: 16px;
    }

    .mini {
      font-size: .9rem;
      color: #6b7280
    }

    .dash {
      border-top: 1px dashed #d9dee8
    }

    .yapiskan {
      position: sticky;
      top: 24px;
    }

    .site-navbar {
      background: #fff;
      border-bottom: 1px solid #eef1f5;
    }

    .top-hero {
      background: #f4f7fb;
      border-top: 1px solid #eef1f5;
      border-bottom: 1px solid #eef1f5;
    }

    /* Menü aralıkları */
    .navbar-nav {
      gap: 1.25rem;
    }

    .navbar-nav .nav-link {
      padding: .5rem 1rem;
    }
  </style>
</head>

<body>

  <!-- ======= NAVBAR ======= -->
  <header class="site-navbar sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2 py-3" href="/">
          <img class="ms-5"src="https://zirve-bilgisayar.com/images/zirve-logo.png" alt="logo" width="150" height="55">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#anaMenu" aria-controls="anaMenu" aria-expanded="false" aria-label="Menüyü Aç/Kapat">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="anaMenu">
          <ul class="navbar-nav mx-auto my-3">
            <li class="nav-item"><a class="nav-link text-danger" href="#">Canlı Destek</a></li>
            <li class="nav-item"><a class="nav-link" href="/">Anasayfa</a></li>
            <li class="nav-item"><a class="nav-link" href="/hakkimizda">Hakkımızda</a></li>
            <li class="nav-item"><a class="nav-link" href="/kampanyalar">Kampanyalar</a></li>
            <li class="nav-item"><a class="nav-link" href="/blog">Blog Yazıları</a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Ürünler</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/urunler/icraproweb">İcraProWeb</a></li>
                <li><a class="dropdown-item" href="/urunler/muhasebe">Muhasebe</a></li>
                <li><a class="dropdown-item" href="/urunler/e-donusum">e-Dönüşüm</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Destek</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/destek/sss">SSS</a></li>
                <li><a class="dropdown-item" href="/destek/dokumanlar">Dokümanlar</a></li>
                <li><a class="dropdown-item" href="/destek/indirilebilir">İndirilebilir</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="/iletisim">İletişim</a></li>
          </ul>

          <div class="d-flex align-items-center gap-5">
            <a href="/sepet" class="text-decoration-none text-dark">
              <i class="bi bi-bag me-1"></i> Sepetim
              <span class="badge text-bg-success align-middle"><?= (int)$sepet_adet ?></span>
            </a>
            <?php if ($oturum_acik): ?>
              <div class="dropdown">
                <a class="text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown" href="#">
                  <i class="bi bi-person-circle me-1"></i> Hesabım
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="/profil">Profil</a></li>
                  <li><a class="dropdown-item" href="/siparisler">Siparişlerim</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="/cikis">Çıkış</a></li>
                </ul>
              </div>
            <?php else: ?>
              <a href="/giris" class="text-decoration-none text-dark d-flex flex-column lh-1 pe-2">
                <span><i class="bi bi-person me-1"></i> Giriş yap</span>
                <small class="text-muted">veya kayıt ol</small>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </nav>
  </header>
  
    <?php if ($siparis_alindi): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <strong>Teşekkürler!</strong> Lütfen e-postanızı kontrol ediniz. Sipariş No:
        <code><?= htmlspecialchars($siparis_no) ?></code>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>


  <!-- ======= HERO / BREADCRUMB ======= -->
<section class="top-hero py-2 ">
  <div class="container">
    <h3 class="d-flex justify-content-center"><?= htmlspecialchars($sayfa_baslik) ?></h3>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 d-flex justify-content-center">
        <?php foreach ($breadcrumb as $ad => $url): ?>
          <?php if ($url): ?>
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($ad) ?></a></li>
          <?php else: ?>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($ad) ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ol>
    </nav>
  </div>
</section>

  <!-- ======= İÇERİK: Satış & Fiyatlandırma ======= -->
  <div class="container py-5">




    

    <div class="row g-4">
      <!-- SOL: Form -->
      <div class="col-12 col-lg-7">
        <div class="kart p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Paketinizi Oluşturun</h4>
            <span class="badge text-bg-light">Hesaplama sunucuda yapılır</span>
          </div>

          <form method="post" id="siparisFormu">
            <!-- Kullanıcı sayısı -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-end">
                <div>
                  <div class="mini">Adım 1</div>
                  <label class="form-label h5 mb-1">Kullanıcı Sayısı</label>
                  <div class="text-muted">Kullanıcı arttıkça birim fiyat düşer</div>
                </div>
                <div class="text-end">
                  <div class="mini">Birim fiyat</div>
                  <div class="fw-bold"><span id="bfKullanici"><?= tl($kullanici_birim) ?></span> ₺</div>
                </div>
              </div>

              <div class="row gy-2 align-items-center mt-2">
                <div class="col-12 col-md-9">
                  <input id="rngKullanici" type="range" class="form-range" min="1" max="100" step="1" value="<?= $kullanici_sayisi ?>">
                </div>
                <div class="col-12 col-md-3">
                  <input id="inpKullanici" type="number" class="form-control" name="kullanici_sayisi" min="1" max="1000"
                    value="<?= $kullanici_sayisi ?>" required>
                </div>
              </div>
            </div>


            <!-- Dosya sayısı -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-end">
                <div>
                  <div class="mini">Adım 2</div>
                  <label class="form-label h5 mb-1">Dosya Sayısı</label>
                  <div class="text-muted">Depolama & bakım için mikro ücret</div>
                </div>
                <div class="text-end">
                  <div class="mini">Dosya başı</div>
                  <div class="fw-bold"><span id="bfDosya"><?= tl($dosya_birim) ?></span> ₺</div>
                </div>
              </div>

<div class="row gy-2 align-items-center mt-2">
                <div class="col-12 col-md-9">
                  <input id="rngDosya" type="range" class="form-range" min="0" max="5000" step="10" value="<?= $dosya_sayisi ?>">
                </div>
                <div class="col-12 col-md-3">
                  <input id="inpDosya" type="number" class="form-control" name="dosya_sayisi" min="0" max="5000" step="10"
                    value="<?= $dosya_sayisi ?>" required>
                </div>
              </div>


              <div class="mb-3">
                <label class="form-label">Sipariş notu (opsiyonel)</label>
                <textarea class="form-control" name="siparis_notu" rows="2"><?= htmlspecialchars($siparis_notu) ?></textarea>
              </div>

              <div class="d-grid d-lg-none">
                <button type="submit" class="btn btn-primary btn-lg">Siparişi Onayla</button>
              </div>
          </form>
        </div>
      </div>


    </div>
    <!-- SAĞ: Sepet -->
    <div class="col-12 col-lg-5">
      <div class="kart p-4 yapiskan">
        <h4 class="mb-3">Sepetiniz</h4>
        <ul class="list-group mb-3" id="sepetListe">
          <?php foreach ($sepet_kalemleri as $ad => $deger): ?>
            <li class="list-group-item d-flex justify-content-between" data-key="<?= htmlspecialchars($ad) ?>">
              <span><?= $ad ?></span>
              <span class="val <?= $ad === 'Toplam' ? 'fw-bold fs-5' : '' ?>"><?= $deger ?></span>
            </li>
          <?php endforeach; ?>
        </ul>

        <!-- Masaüstü: ana formu gönderir -->
        <button form="siparisFormu" type="submit" class="btn btn-success btn-lg w-100">Siparişi Onayla</button>
        <div class="mini mt-2 text-muted">Fiyatlara <strong>KDV (%20)</strong> dahildir.</div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Canlı Hesap JS (yalnızca senkronizasyon ve güncelleme) -->
  <script>
    const rngK = document.getElementById('rngKullanici');
    const inpK = document.getElementById('inpKullanici');
    const rngD = document.getElementById('rngDosya');
    const inpD = document.getElementById('inpDosya');

    const bfK = document.getElementById('bfKullanici');
    const bfD = document.getElementById('bfDosya');

    const q = (key) => document.querySelector('li[data-key="' + key + '"] .val');

    function pricePerUser(u) {
      if (u >= 101) return 24.90;
      if (u >= 51) return 29.90;
      if (u >= 21) return 34.90;
      if (u >= 11) return 39.90;
      return 49.90;
    }

    function pricePerFile(f) {
      if (f >= 5001) return 0.04;
      if (f >= 2001) return 0.06;
      if (f >= 501) return 0.08;
      return 0.10;
    }

    function fmt(n) {
      return n.toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    function clamp(v, min, max) {
      v = parseInt(v || 0, 10);
      if (isNaN(v)) v = min;
      return Math.max(min, Math.min(max, v));
    }

    function sync(from, to) {
      to.value = from.value;
      recalc();
    }

    function recalc() {
      const u = clamp(inpK.value, parseInt(inpK.min), parseInt(inpK.max));
      const f = clamp(inpD.value, parseInt(inpD.min), parseInt(inpD.max));

      // inputlara geri yaz (clamp sonrası)
      inpK.value = u;
      rngK.value = u;
      inpD.value = f;
      rngD.value = f;

      const pu = pricePerUser(u);
      const pf = pricePerFile(f);
      const tUsers = u * pu;
      const tFiles = f * pf;
      const subtotal = tUsers + tFiles;
      const vat = subtotal * 0.20;
      const total = subtotal + vat;

      // üst bölümdeki birim fiyatlar
      bfK.textContent = fmt(pu);
      bfD.textContent = fmt(pf);

      // sepet satırları
      q('Kullanıcı birim fiyat').textContent = fmt(pu) + ' ₺';
      q('Dosya başı fiyat').textContent = fmt(pf) + ' ₺';
      q('Kullanıcılar').textContent = u + ' × ' + fmt(pu) + ' = ' + fmt(tUsers) + ' ₺';
      q('Dosyalar').textContent = f + ' × ' + fmt(pf) + ' = ' + fmt(tFiles) + ' ₺';
      q('Ara toplam').textContent = fmt(subtotal) + ' ₺';
      q('KDV (%20)').textContent = fmt(vat) + ' ₺';
      q('Toplam').textContent = fmt(total) + ' ₺';
    }

    // Olaylar
    rngK.addEventListener('input', () => sync(rngK, inpK));
    inpK.addEventListener('input', () => sync(inpK, rngK));
    rngD.addEventListener('input', () => sync(rngD, inpD));
    inpD.addEventListener('input', () => sync(inpD, rngD));

    // İlk hesap
    recalc();
  </script>
</body>

</html>