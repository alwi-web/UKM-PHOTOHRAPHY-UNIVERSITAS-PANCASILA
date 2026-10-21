/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Ubah data di bagian DATA untuk memperbarui konten website.
   ========================================================= */

/* ---------- DATA ---------- */
const divisi = [
  { icon: "🏞️", nama: "Landscape", desc: "Menangkap keindahan alam, arsitektur, dan lanskap kota dengan komposisi yang kuat." },
  { icon: "🧑‍🎨", nama: "Portrait", desc: "Belajar pencahayaan, posing, dan menangkap karakter subjek dalam satu bingkai." },
  { icon: "🎥", nama: "Videografi", desc: "Produksi video dokumenter, aftermovie, dan konten kreatif untuk media sosial." },
  { icon: "📰", nama: "Jurnalistik Foto", desc: "Bercerita lewat foto: liputan acara kampus, human interest, dan foto esai." },
  { icon: "💻", nama: "Editing & Digital Imaging", desc: "Mengolah foto dengan Lightroom & Photoshop, color grading, dan retouching." },
];

const galeri = [
  { seed: "up-g1", w: 600, h: 800, judul: "Senja di Danau Kampus", fotografer: "Rizky A.", kategori: "landscape" },
  { seed: "up-g2", w: 600, h: 450, judul: "Tatapan", fotografer: "Nadia P.", kategori: "portrait" },
  { seed: "up-g3", w: 600, h: 700, judul: "Pedagang Pagi", fotografer: "Fajar M.", kategori: "human-interest" },
  { seed: "up-g4", w: 600, h: 420, judul: "Ritme Kota", fotografer: "Dimas S.", kategori: "street" },
  { seed: "up-g5", w: 600, h: 600, judul: "Kabut Puncak", fotografer: "Salsa R.", kategori: "landscape" },
  { seed: "up-g6", w: 600, h: 820, judul: "Senyum Wisuda", fotografer: "Aldi K.", kategori: "portrait" },
  { seed: "up-g7", w: 600, h: 460, judul: "Penjaga Palang", fotografer: "Intan L.", kategori: "human-interest" },
  { seed: "up-g8", w: 600, h: 740, judul: "Bayangan Halte", fotografer: "Bagas T.", kategori: "street" },
  { seed: "up-g9", w: 600, h: 500, judul: "Pantai Selatan", fotografer: "Rizky A.", kategori: "landscape" },
];

const kegiatan = [
  { tanggal: "2026-11-08", judul: "Kelas Fotografi Dasar", desc: "Pengenalan kamera, segitiga eksposur, dan komposisi untuk anggota baru.", lokasi: "Aula Gedung Rektorat", waktu: "09.00 – 15.00", jenis: "Kelas" },
  { tanggal: "2026-11-22", judul: "Hunting Foto Kota Tua", desc: "Hunting bersama bertema street & human interest di kawasan Kota Tua Jakarta.", lokasi: "Kota Tua, Jakarta Barat", waktu: "06.00 – 12.00", jenis: "Hunting" },
  { tanggal: "2026-12-13", judul: "Pameran Foto \"Lensa Pancasila\"", desc: "Pameran karya terbaik anggota selama satu tahun kepengurusan.", lokasi: "Lobby Fakultas Teknik", waktu: "10.00 – 17.00", jenis: "Pameran" },
  { tanggal: "2027-01-17", judul: "Workshop Editing Lightroom", desc: "Belajar alur editing, color grading, dan preset bersama fotografer profesional.", lokasi: "Lab Komputer FTI", waktu: "13.00 – 16.00", jenis: "Workshop" },
];

const pengurus = [
  { nama: "Ahmad Fauzan", jabatan: "Ketua Umum", seed: "up-t1" },
  { nama: "Putri Ramadhani", jabatan: "Wakil Ketua", seed: "up-t2" },
  { nama: "Dewi Lestari", jabatan: "Sekretaris", seed: "up-t3" },
  { nama: "Rangga Saputra", jabatan: "Bendahara", seed: "up-t4" },
];

/* ---------- HELPERS ---------- */
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
const BULAN = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
const photo = (seed, w, h) => `https://picsum.photos/seed/${seed}/${w}/${h}`;

/* ---------- RENDER ---------- */
function renderDivisi() {
  $("#divisiGrid").innerHTML = divisi
    .map(
      (d) => `
      <article class="card reveal">
        <div class="card-icon">${d.icon}</div>
        <h3>${d.nama}</h3>
        <p>${d.desc}</p>
      </article>`
    )
    .join("");

  $("#divisiSelect").insertAdjacentHTML(
    "beforeend",
    divisi.map((d) => `<option>${d.nama}</option>`).join("")
  );
}

function renderGaleri() {
  $("#gallery").innerHTML = galeri
    .map(
      (g, i) => `
      <figure class="gallery-item" data-kategori="${g.kategori}" data-index="${i}" tabindex="0">
        <img src="${photo(g.seed, g.w, g.h)}" alt="${g.judul}" width="${g.w}" height="${g.h}" loading="lazy" />
        <figcaption class="gallery-caption">
          <h4>${g.judul}</h4>
          <span>oleh ${g.fotografer}</span>
        </figcaption>
      </figure>`
    )
    .join("");
}

function renderKegiatan() {
  $("#eventList").innerHTML = kegiatan
    .map((k) => {
      const d = new Date(k.tanggal + "T00:00:00");
      return `
      <article class="event reveal">
        <div class="event-date">
          <strong>${String(d.getDate()).padStart(2, "0")}</strong>
          <span>${BULAN[d.getMonth()]} ${d.getFullYear()}</span>
        </div>
        <div class="event-body">
          <h3>${k.judul}</h3>
          <p>${k.desc}</p>
          <div class="event-meta">
            <span>📍 ${k.lokasi}</span>
            <span>🕘 ${k.waktu}</span>
          </div>
        </div>
        <span class="tag">${k.jenis}</span>
      </article>`;
    })
    .join("");
}

function renderPengurus() {
  $("#teamGrid").innerHTML = pengurus
    .map(
      (p) => `
      <article class="member reveal">
        <div class="member-photo">
          <img src="${photo(p.seed, 400, 400)}" alt="${p.nama}" loading="lazy" />
        </div>
        <h3>${p.nama}</h3>
        <p>${p.jabatan}</p>
      </article>`
    )
    .join("");
}

/* ---------- NAVBAR ---------- */
function initNavbar() {
  const navbar = $("#navbar");
  const toggle = $("#navToggle");
  const menu = $("#navMenu");
  const links = $$(".nav-link");
  const sections = links.map((l) => $(l.getAttribute("href")));
  const backToTop = $("#backToTop");

  const closeMenu = () => {
    menu.classList.remove("open");
    toggle.classList.remove("open");
    toggle.setAttribute("aria-expanded", "false");
  };

  toggle.addEventListener("click", () => {
    const open = menu.classList.toggle("open");
    toggle.classList.toggle("open", open);
    toggle.setAttribute("aria-expanded", String(open));
  });

  $$("a", menu).forEach((a) => a.addEventListener("click", closeMenu));

  const onScroll = () => {
    const y = window.scrollY;
    navbar.classList.toggle("scrolled", y > 50);
    backToTop.classList.toggle("show", y > 600);

    // tandai menu sesuai section yang sedang terlihat
    let current = 0;
    sections.forEach((s, i) => {
      if (s && s.offsetTop - 120 <= y) current = i;
    });
    links.forEach((l, i) => l.classList.toggle("active", i === current));
  };

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
}

/* ---------- GALLERY FILTER + LIGHTBOX ---------- */
function initGallery() {
  const items = $$(".gallery-item");
  const lightbox = $("#lightbox");
  const lbImg = $("#lbImg");
  const lbCaption = $("#lbCaption");
  let visible = items;
  let current = 0;

  $("#filterBar").addEventListener("click", (e) => {
    const btn = e.target.closest(".filter-btn");
    if (!btn) return;
    $$(".filter-btn").forEach((b) => b.classList.toggle("active", b === btn));
    const f = btn.dataset.filter;
    items.forEach((it) => it.classList.toggle("hide", f !== "semua" && it.dataset.kategori !== f));
    visible = items.filter((it) => !it.classList.contains("hide"));
  });

  const show = (i) => {
    current = (i + visible.length) % visible.length;
    const g = galeri[visible[current].dataset.index];
    lbImg.src = photo(g.seed, g.w * 2, g.h * 2);
    lbImg.alt = g.judul;
    lbCaption.textContent = `${g.judul} — ${g.fotografer}`;
  };

  const open = (item) => {
    show(visible.indexOf(item));
    lightbox.classList.add("open");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  };

  const close = () => {
    lightbox.classList.remove("open");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  };

  items.forEach((it) => {
    it.addEventListener("click", () => open(it));
    it.addEventListener("keydown", (e) => e.key === "Enter" && open(it));
  });

  $("#lbClose").addEventListener("click", close);
  $("#lbPrev").addEventListener("click", () => show(current - 1));
  $("#lbNext").addEventListener("click", () => show(current + 1));
  lightbox.addEventListener("click", (e) => e.target === lightbox && close());

  document.addEventListener("keydown", (e) => {
    if (!lightbox.classList.contains("open")) return;
    if (e.key === "Escape") close();
    if (e.key === "ArrowLeft") show(current - 1);
    if (e.key === "ArrowRight") show(current + 1);
  });
}

/* ---------- COUNTER ---------- */
function initCounters() {
  const counters = $$("[data-count]");
  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = +el.dataset.count;
        const start = performance.now();
        const step = (now) => {
          const p = Math.min((now - start) / 1500, 1);
          el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3)));
          if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
        obs.unobserve(el);
      });
    },
    { threshold: 0.5 }
  );
  counters.forEach((c) => obs.observe(c));
}

/* ---------- REVEAL ON SCROLL ---------- */
function initReveal() {
  const obs = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          obs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );
  $$(".reveal").forEach((el) => obs.observe(el));
}

/* ---------- FORM PENDAFTARAN ---------- */
function initForm() {
  const form = $("#registerForm");
  const success = $("#formSuccess");

  const rules = {
    nama: (v) => (v.trim().length >= 3 ? "" : "Nama minimal 3 karakter."),
    npm: (v) => (/^\d{6,15}$/.test(v.trim()) ? "" : "NPM harus berupa 6–15 digit angka."),
    fakultas: (v) => (v ? "" : "Silakan pilih fakultas."),
    angkatan: (v) => (/^(19|20)\d{2}$/.test(v) ? "" : "Tahun angkatan tidak valid."),
    email: (v) => (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? "" : "Format email tidak valid."),
    wa: (v) => (/^(\+62|62|0)8\d{7,12}$/.test(v.replace(/[\s-]/g, "")) ? "" : "Nomor WhatsApp tidak valid."),
    divisi: (v) => (v ? "" : "Silakan pilih divisi."),
  };

  const validate = (field) => {
    const rule = rules[field.name];
    if (!rule) return true;
    const msg = rule(field.value);
    const group = field.closest(".form-group");
    group.classList.toggle("invalid", !!msg);
    $(".error", group).textContent = msg;
    return !msg;
  };

  $$("input, select", form).forEach((f) => {
    f.addEventListener("blur", () => validate(f));
    f.addEventListener("input", () => f.closest(".form-group").classList.contains("invalid") && validate(f));
  });

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const fields = $$("input, select", form);
    const ok = fields.map(validate).every(Boolean);
    if (!ok) {
      fields.find((f) => f.closest(".form-group").classList.contains("invalid"))?.focus();
      return;
    }

    const data = Object.fromEntries(new FormData(form));
    data.waktuDaftar = new Date().toISOString();

    // Simpan sementara di browser. Ganti bagian ini dengan request ke backend /
    // Google Apps Script / Formspree bila ingin data tersimpan di server.
    try {
      const list = JSON.parse(localStorage.getItem("pendaftar") || "[]");
      list.push(data);
      localStorage.setItem("pendaftar", JSON.stringify(list));
    } catch (_) {
      /* localStorage tidak tersedia — abaikan */
    }

    form.reset();
    success.hidden = false;
    setTimeout(() => (success.hidden = true), 6000);
  });
}

/* ---------- INIT ---------- */
document.addEventListener("DOMContentLoaded", () => {
  renderDivisi();
  renderGaleri();
  renderKegiatan();
  renderPengurus();

  initNavbar();
  initGallery();
  initCounters();
  initReveal();
  initForm();

  $("#year").textContent = new Date().getFullYear();
});
