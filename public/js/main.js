/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Halaman dirender oleh Laravel (resources/views/layouts/site.blade.php,
   home, profile, gallery).
   ========================================================= */

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
const reduceMotion = matchMedia("(prefers-reduced-motion: reduce)").matches;

/* ---------- GALERI: FILTER + LIGHTBOX ---------- */
function initGallery() {
  const items = $$(".gallery-item");
  const lightbox = $("#lightbox");
  if (!items.length || !lightbox) return;

  const csrf = $('meta[name="csrf-token"]').content;
  const viewed = new Set();
  let visible = items;
  let current = 0;

  $("#filterBar")?.addEventListener("click", (e) => {
    const chip = e.target.closest(".chip");
    if (!chip) return;
    $$(".chip").forEach((c) => c.classList.toggle("active", c === chip));
    const f = chip.dataset.filter;
    items.forEach((it) => it.classList.toggle("hide", f !== "semua" && it.dataset.kategori !== f));
    visible = items.filter((it) => !it.classList.contains("hide"));
  });

  const setLink = (el, url) => {
    el.hidden = !url;
    if (url) el.href = url;
  };

  // catat jumlah dilihat (sekali per foto per kunjungan) untuk laporan insight karya
  const recordView = (item) => {
    if (viewed.has(item) || !item.dataset.viewUrl) return;
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
    $("#lbExposure").textContent = d.exposure || "";
    $("#lbExposureNotes").textContent = d.exposureNotes || "";
    setLink($("#lbInstagram"), d.instagram);
    setLink($("#lbTiktok"), d.tiktok);
    recordView(visible[current]);
  };

  const open = (item) => {
    show(visible.indexOf(item));
    lightbox.classList.add("open");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    $("#lbClose").focus();
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

/* ---------- SLIDER FOTO DI ATAS: berganti halus (memudar + zoom pelan) ---------- */
function initSlider() {
  const root = $("[data-slider]");
  if (!root) return;
  const slides = $$(".slide", root);
  const dots = $$(".slider-dot", root);
  if (slides.length < 2) return;

  const delay = 6000;
  let index = 0;
  let timer = null;
  let paused = false;

  const schedule = () => {
    clearTimeout(timer);
    if (!reduceMotion && !paused && !document.hidden) timer = setTimeout(() => go(index + 1), delay);
  };

  const go = (n) => {
    index = (n + slides.length) % slides.length;
    slides.forEach((s, i) => {
      s.classList.toggle("is-active", i === index);
      s.setAttribute("aria-hidden", String(i !== index));
    });
    dots.forEach((d, i) => {
      d.classList.toggle("active", i === index);
      d.setAttribute("aria-current", String(i === index));
    });
    schedule();
  };

  $("[data-prev]", root).addEventListener("click", () => go(index - 1));
  $("[data-next]", root).addEventListener("click", () => go(index + 1));
  dots.forEach((d) => d.addEventListener("click", () => go(Number(d.dataset.dot))));

  // berhenti sebentar saat diarahkan kursor / difokuskan / tab tidak terlihat
  const setPaused = (v) => { paused = v; v ? clearTimeout(timer) : schedule(); };
  root.addEventListener("mouseenter", () => setPaused(true));
  root.addEventListener("mouseleave", () => setPaused(root.contains(document.activeElement)));
  root.addEventListener("focusin", () => setPaused(true));
  root.addEventListener("focusout", (e) => !root.contains(e.relatedTarget) && setPaused(root.matches(":hover")));
  document.addEventListener("visibilitychange", schedule);

  root.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") go(index - 1);
    if (e.key === "ArrowRight") go(index + 1);
  });

  // geser dengan jari
  let startX = null;
  root.addEventListener("touchstart", (e) => { startX = e.touches[0].clientX; }, { passive: true });
  root.addEventListener("touchend", (e) => {
    if (startX === null) return;
    const dx = e.changedTouches[0].clientX - startX;
    if (Math.abs(dx) > 40) go(index - Math.sign(dx));
    startX = null;
  });

  go(0);
}

/* ---------- PENCARIAN KARYA (ikon kaca pembesar di header) ---------- */
function initSearch() {
  const toggle = $("#searchToggle");
  const bar = $("#searchBar");
  if (!toggle || !bar) return;
  const setOpen = (open) => {
    bar.hidden = !open;
    toggle.setAttribute("aria-expanded", String(open));
    if (open) $("input", bar).focus();
  };
  toggle.addEventListener("click", () => setOpen(bar.hidden));
  document.addEventListener("keydown", (e) => e.key === "Escape" && !bar.hidden && setOpen(false));
}

/* ---------- HEADER: bayangan saat di-scroll ---------- */
function initHeader() {
  const header = $(".site-header");
  if (!header) return;
  const onScroll = () => header.classList.toggle("scrolled", window.scrollY > 8);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
}

/* ---------- ANIMASI MUNCUL SAAT DI-SCROLL ---------- */
function initReveal() {
  const items = $$(".reveal");
  if (!("IntersectionObserver" in window)) {
    items.forEach((el) => el.classList.add("in"));
    return;
  }
  const io = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (!e.isIntersecting) return;
      e.target.classList.add("in");
      io.unobserve(e.target);
    }),
    { threshold: 0.12 }
  );
  items.forEach((el) => io.observe(el));
}

document.addEventListener("DOMContentLoaded", () => {
  initHeader();
  initSearch();
  initSlider();
  initReveal();
  initGallery();
});
