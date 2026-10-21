/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Konten halaman dirender oleh Laravel (resources/views/home.blade.php).
   ========================================================= */

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* ---------- NAVBAR ---------- */
function initNavbar() {
  const navbar = $("#navbar");
  const toggle = $("#navToggle");
  const menu = $("#navMenu");
  const links = $$(".nav-link").filter((l) => l.getAttribute("href").startsWith("#"));
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
  if (!items.length) return;

  const lightbox = $("#lightbox");
  const csrf = $('meta[name="csrf-token"]').content;
  const viewed = new Set();
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

  const setLink = (el, url) => {
    el.hidden = !url;
    if (url) el.href = url;
  };

  // catat jumlah dilihat (sekali per foto per kunjungan) untuk laporan insight karya
  const recordView = (item) => {
    if (viewed.has(item)) return;
    viewed.add(item);
    fetch(item.dataset.viewUrl, { method: "POST", headers: { "X-CSRF-TOKEN": csrf } }).catch(() => {});
  };

  const show = (i) => {
    current = (i + visible.length) % visible.length;
    const d = visible[current].dataset;
    $("#lbImg").src = d.src;
    $("#lbImg").alt = d.title;
    $("#lbTitle").textContent = d.title;
    $("#lbAuthor").textContent = `oleh ${d.author}`;
    $("#lbCaption").textContent = d.caption || "";
    setLink($("#lbInstagram"), d.instagram);
    setLink($("#lbTiktok"), d.tiktok);
    recordView(visible[current]);
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
  $$("[data-count]").forEach((c) => obs.observe(c));
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
  const role = $("#role");
  const originGroup = $("#originUkmGroup");
  if (!role) return;

  // "Asal UKM" hanya untuk anggota UKM lain
  const sync = () => {
    const other = role.value === "anggota_lain";
    originGroup.hidden = !other;
    $("#origin_ukm").required = other;
  };
  role.addEventListener("change", sync);
  sync();

  $("#registerForm").addEventListener("submit", (e) => {
    const btn = $("button[type=submit]", e.target);
    btn.disabled = true;
    btn.textContent = "Mengirim...";
  });
}

/* ---------- INIT ---------- */
document.addEventListener("DOMContentLoaded", () => {
  initNavbar();
  initGallery();
  initCounters();
  initReveal();
  initForm();
});
