/* =========================================================
   UKM Fotografi Universitas Pancasila — Main Script
   Konten halaman dirender oleh Laravel (resources/views/home.blade.php).
   ========================================================= */

const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

/* ---------- GALERI: FILTER + LIGHTBOX ---------- */
function initGallery() {
  const items = $$(".gallery-item");
  if (!items.length) return;

  const lightbox = $("#lightbox");
  const csrf = $('meta[name="csrf-token"]').content;
  const viewed = new Set();
  let visible = items;
  let current = 0;

  $("#filterBar").addEventListener("click", (e) => {
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

/* ---------- BAGIAN HALAMAN (HP: daftar tombol buka-tutup) ---------- */
function initSections() {
  const sections = $$(".section").filter((s) => $(".section-toggle", s));

  const setOpen = (section, open) => {
    section.classList.toggle("is-open", open);
    $(".section-toggle", section).setAttribute("aria-expanded", String(open));
  };

  sections.forEach((section) => {
    setOpen(section, section.classList.contains("is-open"));
    $(".section-toggle", section).addEventListener("click", () => {
      setOpen(section, !section.classList.contains("is-open"));
    });
  });

  // link ke suatu bagian (menu, tombol hero, #alamat di URL) ikut membuka bagian itu
  const openFromHash = (hash) => {
    const target = hash && sections.find((s) => `#${s.id}` === hash);
    if (target) setOpen(target, true);
  };

  $$('a[href^="#"]').forEach((a) => a.addEventListener("click", () => openFromHash(a.getAttribute("href"))));
  openFromHash(location.hash);
}

/* ---------- HEADER: bayangan saat di-scroll + menu aktif ---------- */
function initTopbar() {
  const topbar = $(".topbar");
  const onScroll = () => topbar.classList.toggle("scrolled", window.scrollY > 8);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });

  const links = $$(".nav a");
  if (!("IntersectionObserver" in window) || !links.length) return;
  const spy = new IntersectionObserver(
    (entries) => entries.forEach((e) => {
      if (e.isIntersecting) links.forEach((a) => a.classList.toggle("active", a.getAttribute("href") === `#${e.target.id}`));
    }),
    { rootMargin: "-45% 0px -50% 0px" }
  );
  links.forEach((a) => {
    const target = $(a.getAttribute("href"));
    if (target) spy.observe(target);
  });
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

/* ---------- FORM PENDAFTARAN ---------- */
function initForm() {
  const form = $("#registerForm");
  if (!form) return;

  form.addEventListener("submit", () => {
    const btn = $("button[type=submit]", form);
    btn.disabled = true;
    btn.textContent = "Mengirim...";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initSections();
  initTopbar();
  initReveal();
  initGallery();
  initForm();
});
